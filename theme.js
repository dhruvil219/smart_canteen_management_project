// theme.js - Dark mode and accent color customization
(function(){
  const STORAGE_KEYS = { theme: 'vc_theme', accent: 'vc_accent' };

  // Elements (created per-page if present)
  function $(sel){ return document.querySelector(sel); }

  function applyTheme(theme){
    const isDark = theme === 'dark';
    document.body.classList.toggle('theme-dark', isDark);
    localStorage.setItem(STORAGE_KEYS.theme, isDark ? 'dark' : 'light');
    const toggle = $('#themeToggle');
    if (toggle) toggle.textContent = isDark ? '🌙' : '🌞';
  }

  function applyAccent(accent){
    if (accent) {
      document.documentElement.style.setProperty('--accent', accent);
      localStorage.setItem(STORAGE_KEYS.accent, accent);
    }
  }

  function initFromStorage(){
    const savedTheme = localStorage.getItem(STORAGE_KEYS.theme) || 'light';
    applyTheme(savedTheme);
    const savedAccent = localStorage.getItem(STORAGE_KEYS.accent);
    if (savedAccent) applyAccent(savedAccent);
  }

  function bindUI(){
    const toggle = $('#themeToggle');
    if (toggle){
      toggle.addEventListener('click', ()=>{
        const isDark = document.body.classList.contains('theme-dark');
        applyTheme(isDark ? 'light' : 'dark');
      });
    }

    const openPanelBtn = $('#themeCustomize');
    const panel = $('#themePanel');
    if (openPanelBtn && panel){
      openPanelBtn.addEventListener('click', ()=>{
        panel.classList.toggle('open');
      });
      // Close when clicking outside
      document.addEventListener('click', (e)=>{
        if (!panel.contains(e.target) && e.target !== openPanelBtn) {
          panel.classList.remove('open');
        }
      });
    }

    // Accent color buttons
    document.querySelectorAll('[data-accent]').forEach(btn => {
      btn.addEventListener('click', ()=>{
        applyAccent(btn.getAttribute('data-accent'));
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    initFromStorage();
    bindUI();
  });
})();
