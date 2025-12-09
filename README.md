# 🥬 Veg Canteen Website

A complete vegetarian canteen management system built with HTML, CSS, JavaScript, and PHP. Features user authentication, role-based access (customer/admin), menu management, and order processing.

## 🌟 Features

### For Customers
- **User Registration & Login** - Secure account creation and authentication
- **Browse Menu** - View categorized vegetarian food items with prices
- **Shopping Cart** - Add items, modify quantities, and manage orders
- **Place Orders** - Checkout with order notes and confirmation
- **Order Tracking** - View order history and real-time status updates
- **Responsive Design** - Works on desktop and mobile devices

### For Admins
- **Admin Dashboard** - Overview of orders, customers, and revenue
- **Food Management** - Add, edit, delete, and toggle availability of menu items
- **Order Management** - Update order status (pending → confirmed → preparing → ready → delivered)
- **User Management** - View registered customers and their details
- **Real-time Updates** - Manage orders with instant status changes

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Styling**: Custom CSS with responsive grid layout
- **Security**: Password hashing, session management, SQL injection protection

## 📁 Project Structure

```
veg-canteen/
├── admin/                  # Admin panel pages
│   ├── dashboard.php      # Admin dashboard with statistics
│   ├── manage-food.php    # Food item management
│   ├── manage-orders.php  # Order status management
│   ├── manage-users.php   # User management
│   └── view-order.php     # Detailed order view
├── auth/                  # Authentication pages
│   ├── login.php         # User login
│   ├── logout.php        # Session logout
│   └── register.php      # User registration
├── config/               # Configuration files
│   ├── database.php      # Database connection
│   └── session.php       # Session management
├── css/                  # Stylesheets
│   └── style.css         # Main stylesheet
├── customer/             # Customer pages
│   ├── checkout.php      # Order checkout
│   ├── order-confirmation.php # Order success page
│   ├── orders.php        # Order history
│   └── view-order-details.php # Detailed order view
├── database/             # Database files
│   └── schema.sql        # Database schema and sample data
├── images/               # Image assets
│   └── default-food.jpg  # Default food image
├── js/                   # JavaScript files
│   └── script.js         # Main JavaScript functionality
└── index.php             # Homepage and menu display
```

## 🚀 Installation & Setup

### Prerequisites
- **XAMPP/WAMP/LAMP** - Local server environment
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database management
- **Web Browser** - Chrome, Firefox, Safari, or Edge

### Step 1: Setup Local Server
1. Download and install [XAMPP](https://www.apachefriends.org/)
2. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `canteen_db`
3. Import the database schema:
   ```sql
   -- Navigate to database/schema.sql and run the SQL commands
   -- Or import the file directly through phpMyAdmin
   ```

### Step 3: Configure Database Connection
1. Open `config/database.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'canteen_db');
   define('DB_USER', 'root');        // Your MySQL username
   define('DB_PASS', '');            // Your MySQL password
   ```

### Step 4: Deploy Files
1. Copy all project files to your web server directory:
   - **XAMPP**: `C:\xampp\htdocs\veg-canteen\`
   - **WAMP**: `C:\wamp64\www\veg-canteen\`
   - **LAMP**: `/var/www/html/veg-canteen/`

### Step 5: Access the Website
1. Open your web browser
2. Navigate to: `http://localhost/veg-canteen/`
3. The homepage should load with the menu display

## 👤 Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: Full admin panel with management capabilities

### Test Customer Account
- Register a new customer account through the registration page
- Or create one manually through the registration form

## 🎯 Usage Guide

### For Customers
1. **Register/Login** - Create an account or login with existing credentials
2. **Browse Menu** - View available vegetarian food items by category
3. **Add to Cart** - Click "Add to Cart" on desired items
4. **Checkout** - Click cart icon, review items, and proceed to checkout
5. **Track Orders** - View order status in "My Orders" section

### For Admins
1. **Login** - Use admin credentials to access admin panel
2. **Dashboard** - View statistics and recent orders
3. **Manage Food** - Add new items, edit existing ones, toggle availability
4. **Manage Orders** - Update order status as food is prepared and delivered
5. **View Users** - Monitor registered customers

## 🔧 Customization

### Adding New Food Categories
1. Insert into `categories` table via phpMyAdmin
2. Categories will automatically appear in admin food management

### Modifying Styles
- Edit `css/style.css` for visual customizations
- Color scheme uses CSS variables for easy theming
- Responsive breakpoints defined for mobile compatibility

### Adding Payment Integration
- Extend `customer/checkout.php` to include payment gateway
- Update database schema to include payment status
- Modify order confirmation flow accordingly

## 🛡️ Security Features

- **Password Hashing** - Uses PHP's `password_hash()` with bcrypt
- **SQL Injection Protection** - Prepared statements throughout
- **Session Management** - Secure session handling with role verification
- **Input Validation** - Server-side validation for all forms
- **XSS Protection** - HTML escaping for user-generated content

## 📱 Mobile Responsive

The website is fully responsive and works seamlessly on:
- **Desktop** - Full-featured experience
- **Tablet** - Optimized layout with touch-friendly interface
- **Mobile** - Compact design with easy navigation

## 🐛 Troubleshooting

### Database Connection Issues
- Verify MySQL service is running
- Check database credentials in `config/database.php`
- Ensure `canteen_db` database exists

### Permission Errors
- Set proper file permissions (755 for directories, 644 for files)
- Ensure web server has read access to all files

### Session Issues
- Check if PHP sessions are enabled
- Verify session save path is writable

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🙏 Acknowledgments

- Icons and emojis used for visual enhancement
- Responsive design principles for mobile compatibility
- Security best practices for web applications

---

**Enjoy your vegetarian canteen experience! 🌱**
