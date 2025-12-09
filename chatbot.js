(function () {
  const el = document.createElement('div');
  el.className = 'chatbot-widget';
  el.innerHTML = `
    <div class="chatbot-header">
      <span class="chatbot-title">PS Canteen Assistant</span>
      <div class="chatbot-actions">
        <button class="chatbot-min" aria-label="Minimize" title="Minimize">−</button>
        <button class="chatbot-close" aria-label="Close" title="Close">×</button>
      </div>
    </div>
    <div class="chatbot-body" role="dialog" aria-label="PS Canteen Assistant">
      <div class="chatbot-messages" id="chatbotMessages"></div>
      <form class="chatbot-input" id="chatbotForm">
        <input type="text" id="chatbotText" placeholder="Ask about menu, prices, hours..." autocomplete="off" />
        <button type="submit" id="chatbotSend">Send</button>
      </form>
    </div>
    <button class="chatbot-fab" id="chatbotFab" aria-label="Open chat" title="Chat">💬</button>
  `;
  document.body.appendChild(el);

  const fab = el.querySelector('#chatbotFab');
  const minBtn = el.querySelector('.chatbot-min');
  const closeBtn = el.querySelector('.chatbot-close');
  const body = el.querySelector('.chatbot-body');
  const form = el.querySelector('#chatbotForm');
  const input = el.querySelector('#chatbotText');
  const msgEl = el.querySelector('#chatbotMessages');

  function setOpen(open) {
    el.classList.toggle('open', open);
    if (open) setTimeout(() => input.focus(), 0);
  }

  // Minimize (collapse to FAB)
  minBtn.addEventListener('click', (e) => { e.stopPropagation(); setOpen(false); });
  fab.addEventListener('click', () => setOpen(true));
  // Close (hide widget until reload)
  closeBtn.addEventListener('click', (e) => { e.stopPropagation(); el.style.display = 'none'; });

  const state = { messages: [] };

  function addMessage(role, content) {
    const div = document.createElement('div');
    div.className = `chat-msg ${role}`;
    div.textContent = content;
    msgEl.appendChild(div);
    msgEl.scrollTop = msgEl.scrollHeight;
  }

  async function askAssistant(text) {
    addMessage('user', text);
    input.value = '';
    const loading = document.createElement('div');
    loading.className = 'chat-msg assistant loading';
    loading.textContent = 'Thinking…';
    msgEl.appendChild(loading);
    msgEl.scrollTop = msgEl.scrollHeight;

    state.messages.push({ role: 'user', content: text });

    try {
      const res = await fetch('chatbot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ messages: state.messages, meta: {} })
      });
      const data = await res.json().catch(() => ({}));
      loading.remove();
      if (!res.ok || data.error) {
        const msg = data && data.error ? data.error : `Request failed (${res.status})`;
        addMessage('assistant', `Sorry, I can't reach the assistant right now. ${msg}`);
        return;
      }
      const reply = data.reply || '';
      state.messages.push({ role: 'assistant', content: reply });
      addMessage('assistant', reply);
    } catch (err) {
      loading.remove();
      addMessage('assistant', 'Network error. Please check your connection and try again.');
      console.error(err);
    }
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    askAssistant(text);
  });

  // Show greeting once per session
  const greeted = sessionStorage.getItem('chatbot_greeted');
  if (!greeted) {
    setTimeout(() => {
      addMessage('assistant', "Hi! I'm your canteen assistant. Ask me about today's menu, categories, or prices.");
      sessionStorage.setItem('chatbot_greeted', '1');
      setOpen(true);
    }, 700);
  }
})();
