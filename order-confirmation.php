<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';

requireLogin();

$order_id = intval($_GET['order_id'] ?? 0);

if (!$order_id) {
    header('Location: orders.php');
    exit();
}

// Get order details
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verify order belongs to current user
    $order_query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $order_stmt = $db->prepare($order_query);
    $order_stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: orders.php');
        exit();
    }
    
    // Get order items
    $items_query = "SELECT oi.*, f.name FROM order_items oi 
                    JOIN food_items f ON oi.food_item_id = f.id 
                    WHERE oi.order_id = ?";
    $items_stmt = $db->prepare($items_query);
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

$current_user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Veg Canteen</title>
    <link rel="stylesheet" href="style.css">
    <style>
      /* Feedback Modal - minimal, modern, responsive */
      .feedback-modal { display:none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 3000; }
      .feedback-card { background: #fff; width: min(92vw, 560px); margin: 6vh auto; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.15); overflow: hidden; }
      .feedback-header { padding: 1rem 1.25rem; border-bottom: 1px solid #eee; display:flex; justify-content: space-between; align-items:center; }
      .feedback-title { font-size: 1.1rem; font-weight: 700; color: #2c3e50; }
      .feedback-body { padding: 1.25rem; }
      .star-row { display:flex; gap: .5rem; font-size: 1.75rem; margin:.25rem 0 0.5rem; }
      .star { cursor:pointer; color: #ddd; transition: transform .15s ease, color .15s ease; }
      .star.active, .star:hover, .star:hover ~ .star { transform: scale(1.05); }
      .star.active { color: #f1c40f; }
      .feedback-form .form-group { margin-bottom: .9rem; }
      .feedback-form input[type="text"], .feedback-form input[type="email"], .feedback-form textarea { width: 100%; padding: .75rem; border: 1.6px solid #e2e5e9; border-radius: 8px; font-size: .98rem; }
      .feedback-form textarea { resize: vertical; min-height: 84px; }
      .feedback-actions { display:flex; gap:.75rem; justify-content:flex-end; padding: 1rem 1.25rem; border-top: 1px solid #eee; background:#fafbfc; }
      .btn-ghost { background:#fff; border:1.6px solid #e2e5e9; color:#2c3e50; padding:.6rem 1rem; border-radius:8px; cursor:pointer; }
      .btn-primary { background:#27ae60; color:#fff; padding:.6rem 1rem; border:none; border-radius:8px; cursor:pointer; }
      .btn-primary[disabled] { opacity:.7; cursor: not-allowed; }
      @media (max-width: 480px) { .feedback-card{ margin: 10vh 4vw; } .star-row{ font-size:1.6rem; } }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h1><a href="index.php" style="color: #27ae60; text-decoration: none;">🥬 Veg Canteen</a></h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="orders.php" class="nav-link">My Orders</a></li>
                <li><a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($current_user['username']); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; padding: 2rem 0;">
        <div class="admin-content">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 4rem; color: #27ae60; margin-bottom: 1rem;">✅</div>
                <h2>Order Confirmed!</h2>
                <p>Thank you for your order. Your food is being prepared.</p>
            </div>
            
            <div style="background: #f8f9fa; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
                <h3>Order Details</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <strong>Order ID:</strong> #<?php echo $order['id']; ?>
                    </div>
                    <div>
                        <strong>Order Date:</strong> <?php echo date('M j, Y H:i', strtotime($order['order_date'])); ?>
                    </div>
                    <div>
                        <strong>Status:</strong> <span class="status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                    <div>
                        <strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                </div>
                
                <?php if ($order['notes']): ?>
                    <div>
                        <strong>Special Instructions:</strong> <?php echo htmlspecialchars($order['notes']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <h3>Ordered Items</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?> 🟢</td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td colspan="3">Total Amount:</td>
                        <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="orders.php" class="btn btn-primary">View All Orders</a>
                <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="feedback-modal" role="dialog" aria-modal="true" aria-labelledby="feedbackTitle">
        <div class="feedback-card">
            <div class="feedback-header">
                <div class="feedback-title" id="feedbackTitle">Rate your experience</div>
                <button id="fbCloseTop" class="btn-ghost" aria-label="Close">✕</button>
            </div>
            <form id="feedbackForm" class="feedback-form">
                <div class="feedback-body">
                    <div class="form-group">
                        <label>Star rating</label>
                        <div class="star-row" id="starRow" aria-label="Star rating">
                            <span class="star" data-val="1">★</span>
                            <span class="star" data-val="2">★</span>
                            <span class="star" data-val="3">★</span>
                            <span class="star" data-val="4">★</span>
                            <span class="star" data-val="5">★</span>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="0">
                    </div>

                    <div class="form-group">
                        <label for="fbComments">Comments or suggestions</label>
                        <textarea id="fbComments" name="comments" placeholder="Tell us what went well or what to improve (optional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="fbName">Name (optional)</label>
                        <input type="text" id="fbName" name="name" placeholder="Your name">
                    </div>

                    <div class="form-group">
                        <label for="fbEmail">Email (optional)</label>
                        <input type="email" id="fbEmail" name="email" placeholder="you@example.com">
                    </div>
                </div>

                <div class="feedback-actions">
                    <button type="button" id="fbCloseBtn" class="btn-ghost">Close</button>
                    <button type="submit" id="fbSubmitBtn" class="btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Clear cart from localStorage after successful order
        localStorage.removeItem('canteen_cart');

        (function(){
          const modal = document.getElementById('feedbackModal');
          const starRow = document.getElementById('starRow');
          const ratingInput = document.getElementById('ratingInput');
          const fbCloseBtn = document.getElementById('fbCloseBtn');
          const fbCloseTop = document.getElementById('fbCloseTop');
          const fbForm = document.getElementById('feedbackForm');
          const fbSubmitBtn = document.getElementById('fbSubmitBtn');
          const orderId = <?php echo (int)$order['id']; ?>;

          function openModal(){ modal.style.display='block'; }
          function closeModal(){ modal.style.display='none'; }

          function setStars(val){
            ratingInput.value = val;
            document.querySelectorAll('#starRow .star').forEach(st => {
              st.classList.toggle('active', Number(st.getAttribute('data-val')) <= val);
            });
          }

          starRow.addEventListener('click', (e)=>{
            const target = e.target.closest('.star');
            if(!target) return;
            const val = Number(target.getAttribute('data-val'));
            setStars(val);
          });

          fbCloseBtn.addEventListener('click', closeModal);
          fbCloseTop.addEventListener('click', closeModal);
          modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

          fbForm.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const rating = Number(ratingInput.value);
            if(!rating || rating < 1 || rating > 5){
              alert('Please select a rating from 1 to 5 stars.');
              return;
            }

            fbSubmitBtn.disabled = true;
            fbSubmitBtn.textContent = 'Submitting...';

            const formData = new FormData(fbForm);
            formData.append('order_id', orderId);

            try {
              const res = await fetch('feedback_submit.php', { method: 'POST', body: formData });
              const data = await res.json();
              if(!data.success){ throw new Error(data.message || 'Submission failed'); }

              // Thank-you state
              fbForm.innerHTML = '<div class="feedback-body"><h3 style="color:#27ae60; margin-bottom:.5rem;">Thank you!</h3><p>Your feedback helps us improve.</p></div>' +
                                 '<div class="feedback-actions"><button type="button" class="btn-primary" id="fbDone">Close</button></div>';
              document.getElementById('fbDone').addEventListener('click', closeModal);
            } catch(err){
              alert(err.message || 'Something went wrong while submitting feedback.');
              fbSubmitBtn.disabled = false;
              fbSubmitBtn.textContent = 'Submit';
            }
          });

          // Auto-open after page load
          window.addEventListener('DOMContentLoaded', () => {
            // Small delay for a nicer transition
            setTimeout(openModal, 450);
          });
        })();
    </script>
</body>
</html>
