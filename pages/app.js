/* ===== PRO TOURNAMENT ARENA — CORE JS ===== */

// ===== LOCAL STORAGE DB =====
const DB = {
  get: (k) => JSON.parse(localStorage.getItem('pta_' + k) || 'null'),
  set: (k, v) => localStorage.setItem('pta_' + k, JSON.stringify(v)),
  del: (k) => localStorage.removeItem('pta_' + k),
  init() {
    if (!this.get('users')) {
      this.set('users', [
        { id: 1, name: 'Muhammad Anas', email: 'anas@pta.com', password: '123456', role: 'admin', coins: 5000, avatar: '🎮', kills: 342, wins: 47, matches: 120, joined: '2024-01-15', referral: 'ANAS2024' },
        { id: 2, name: 'ProSniper_FF', email: 'sniper@pta.com', password: '123456', role: 'user', coins: 2340, avatar: '🎯', kills: 891, wins: 132, matches: 340, joined: '2024-02-20', referral: 'SNIP2024' },
        { id: 3, name: 'CS_King', email: 'csking@pta.com', password: '123456', role: 'user', coins: 1800, avatar: '👑', kills: 654, wins: 98, matches: 270, joined: '2024-03-10', referral: 'KING2024' },
        { id: 4, name: 'FireStorm', email: 'fire@pta.com', password: '123456', role: 'user', coins: 3200, avatar: '🔥', kills: 1203, wins: 178, matches: 410, joined: '2024-01-28', referral: 'FIRE2024' },
        { id: 5, name: 'GhostBlade', email: 'ghost@pta.com', password: '123456', role: 'user', coins: 980, avatar: '👻', kills: 445, wins: 61, matches: 190, joined: '2024-04-05', referral: 'GOST2024' },
      ]);
    }
    if (!this.get('tournaments')) {
      this.set('tournaments', [
        { id: 1, name: 'Free Fire Grand Championship', game: 'Free Fire', mode: 'Battle Royale Squad', map: 'Bermuda', entry: 50, entryType: 'coin', prize: 5000, prizeType: 'coin', date: '2025-07-15', time: '20:00', slots: 12, filled: 8, status: 'upcoming', rules: 'No hacking. Squad of 4.', desc: 'Monthly flagship tournament.', thumb: '🔥' },
        { id: 2, name: 'CS2 Pro League Season 3', game: 'Counter Strike 2', mode: '5v5', map: 'Dust2', entry: 100, entryType: 'coin', prize: 10000, prizeType: 'coin', date: '2025-07-10', time: '18:00', slots: 16, filled: 14, status: 'live', rules: 'Standard competitive rules.', desc: 'Premier CS2 league.', thumb: '🎯' },
        { id: 3, name: 'FF Solo Showdown', game: 'Free Fire', mode: 'Battle Royale Solo', map: 'Kalahari', entry: 0, entryType: 'free', prize: 1000, prizeType: 'coin', date: '2025-07-20', time: '19:00', slots: 48, filled: 22, status: 'upcoming', rules: 'Solo only. No teaming.', desc: 'Free weekly solo event.', thumb: '⚡' },
        { id: 4, name: 'Clash Squad Cup', game: 'Free Fire', mode: 'Clash Squad', map: 'Purgatory', entry: 30, entryType: 'coin', prize: 2000, prizeType: 'coin', date: '2025-07-08', time: '21:00', slots: 24, filled: 24, status: 'completed', rules: 'Team of 4. Best of 3.', desc: 'Intense CS action.', thumb: '💥' },
        { id: 5, name: 'CS2 Wingman Weekly', game: 'Counter Strike 2', mode: 'Wingman', map: 'Inferno', entry: 50, entryType: 'coin', prize: 3000, prizeType: 'coin', date: '2025-07-18', time: '17:00', slots: 16, filled: 6, status: 'upcoming', rules: '2v2 format. No smurfing.', desc: 'Weekly wingman event.', thumb: '🏆' },
        { id: 6, name: 'FF Duo Blitz', game: 'Free Fire', mode: 'Battle Royale Duo', map: 'Bermuda Remastered', entry: 20, entryType: 'coin', prize: 1500, prizeType: 'coin', date: '2025-07-25', time: '20:30', slots: 24, filled: 10, status: 'upcoming', rules: 'Duo only.', desc: 'Duo championship.', thumb: '🌟' },
      ]);
    }
    if (!this.get('transactions')) { this.set('transactions', []); }
    if (!this.get('notifications')) {
      this.set('notifications', [
        { id: 1, title: 'Welcome to Pro Tournament Arena!', msg: 'Your account has been created. Start competing!', time: '2 hours ago', read: false, type: 'info' },
        { id: 2, title: 'Tournament Starting Soon', msg: 'CS2 Pro League starts in 30 minutes. Get ready!', time: '30 minutes ago', read: false, type: 'warning' },
      ]);
    }
  }
};

// ===== AUTH =====
const Auth = {
  login(email, password) {
    const users = DB.get('users') || [];
    const user = users.find(u => u.email === email && u.password === password);
    if (user) { DB.set('currentUser', user); return { success: true, user }; }
    return { success: false, msg: 'Invalid email or password.' };
  },
  register(data) {
    const users = DB.get('users') || [];
    if (users.find(u => u.email === data.email)) return { success: false, msg: 'Email already registered.' };
    const newUser = {
      id: Date.now(), name: data.name, email: data.email, password: data.password,
      role: 'user', coins: 100, avatar: '🎮', kills: 0, wins: 0, matches: 0,
      joined: new Date().toISOString().split('T')[0], referral: data.name.toUpperCase().slice(0,4) + Date.now().toString().slice(-4)
    };
    users.push(newUser);
    DB.set('users', users);
    DB.set('currentUser', newUser);
    return { success: true, user: newUser };
  },
  logout() { DB.del('currentUser'); window.location.href = '../index.html'; },
  current() { return DB.get('currentUser'); },
  isLoggedIn() { return !!this.current(); },
  isAdmin() { const u = this.current(); return u && u.role === 'admin'; },
  require() { if (!this.isLoggedIn()) { window.location.href = 'login.html'; return false; } return true; },
  requireAdmin() { if (!this.isAdmin()) { window.location.href = '../pages/dashboard.html'; return false; } return true; }
};

// ===== TOAST =====
const Toast = {
  show(msg, type = 'info', duration = 3500) {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span class="toast-icon">${icons[type]||'ℹ️'}</span><span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(20px)'; setTimeout(() => toast.remove(), 400); }, duration);
  }
};

// ===== CURSOR =====
function initCursor() {
  const cur = document.getElementById('cursor');
  const ring = document.getElementById('cursor-ring');
  if (!cur || !ring) return;
  let mx=0,my=0,rx=0,ry=0;
  document.addEventListener('mousemove', e => { mx=e.clientX; my=e.clientY; });
  (function loop(){ cur.style.left=mx+'px'; cur.style.top=my+'px'; rx+=(mx-rx)*.1; ry+=(my-ry)*.1; ring.style.left=rx+'px'; ring.style.top=ry+'px'; requestAnimationFrame(loop); })();
}

// ===== PRELOADER =====
function initPreloader() {
  const pre = document.getElementById('preloader');
  if (!pre) return;
  window.addEventListener('load', () => {
    setTimeout(() => {
      pre.style.opacity = '0'; pre.style.transition = 'opacity .6s';
      setTimeout(() => pre.style.display = 'none', 600);
    }, 2200);
  });
}

// ===== SCROLL REVEAL =====
function initReveal() {
  const els = document.querySelectorAll('.reveal');
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
  }, { threshold: 0.12 });
  els.forEach(el => obs.observe(el));
}

// ===== COUNTER ANIMATION =====
function animateCounter(el, target, suffix = '') {
  const step = Math.ceil(target / 60);
  let count = 0;
  const iv = setInterval(() => {
    count = Math.min(count + step, target);
    el.textContent = count.toLocaleString() + suffix;
    if (count >= target) clearInterval(iv);
  }, 25);
}
function initCounters() {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const el = e.target;
        animateCounter(el, +el.dataset.count, el.dataset.suffix || '');
        obs.unobserve(el);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('[data-count]').forEach(el => obs.observe(el));
}

// ===== BACK TO TOP =====
function initBTT() {
  const btn = document.getElementById('btt');
  if (!btn) return;
  window.addEventListener('scroll', () => btn.classList.toggle('show', window.scrollY > 400));
  btn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ===== NAVBAR SCROLL =====
function initNavbar() {
  const nav = document.getElementById('navbar');
  if (!nav) return;
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) nav.style.background = 'rgba(11,15,25,0.98)';
    else nav.style.background = 'rgba(11,15,25,0.85)';
  });
  // Update nav based on auth
  const user = Auth.current();
  const navAuth = document.getElementById('nav-auth');
  if (navAuth && user) {
    navAuth.innerHTML = `<span style="color:var(--muted);font-size:13px">${user.avatar} ${user.name}</span><a href="pages/dashboard.html" class="btn btn-primary btn-sm">Dashboard</a><button onclick="Auth.logout()" class="btn btn-outline btn-sm">Logout</button>`;
  }
}

// ===== MOBILE MENU =====
function toggleMobile() {
  const m = document.getElementById('mobile-menu');
  if (m) m.classList.toggle('open');
}

// ===== FAQ =====
function initFAQ() {
  document.querySelectorAll('.faq-item').forEach(item => {
    item.querySelector('.faq-q')?.addEventListener('click', () => {
      const wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!wasOpen) item.classList.add('open');
    });
  });
}

// ===== PARTICLES =====
function initParticles(canvasId) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  canvas.width = canvas.offsetWidth;
  canvas.height = canvas.offsetHeight;
  const particles = Array.from({ length: 60 }, () => ({
    x: Math.random() * canvas.width, y: Math.random() * canvas.height,
    vx: (Math.random() - .5) * .4, vy: (Math.random() - .5) * .4,
    r: Math.random() * 2 + .5, alpha: Math.random() * .5 + .1,
    color: Math.random() > .5 ? '123,46,255' : '0,229,255'
  }));
  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
      if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
      ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${p.color},${p.alpha})`; ctx.fill();
    });
    particles.forEach((a, i) => {
      particles.slice(i + 1).forEach(b => {
        const d = Math.hypot(a.x - b.x, a.y - b.y);
        if (d < 100) { ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.strokeStyle = `rgba(123,46,255,${.08 * (1 - d/100)})`; ctx.stroke(); }
      });
    });
    requestAnimationFrame(draw);
  }
  draw();
  window.addEventListener('resize', () => { canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; });
}

// ===== COUNTDOWN =====
function startCountdown(targetDate, el) {
  if (!el) return;
  function update() {
    const diff = new Date(targetDate) - new Date();
    if (diff <= 0) { el.textContent = 'STARTED'; return; }
    const d = Math.floor(diff/86400000), h = Math.floor(diff%86400000/3600000), m = Math.floor(diff%3600000/60000), s = Math.floor(diff%60000/1000);
    el.innerHTML = `<span>${d}d</span> <span>${String(h).padStart(2,'0')}h</span> <span>${String(m).padStart(2,'0')}m</span> <span>${String(s).padStart(2,'0')}s</span>`;
  }
  update(); setInterval(update, 1000);
}

// ===== MODAL =====
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

// ===== INIT =====
document.addEventListener('DOMContentLoaded', () => {
  DB.init();
  initCursor();
  initPreloader();
  initReveal();
  initCounters();
  initBTT();
  initNavbar();
  initFAQ();
});
