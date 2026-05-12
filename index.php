<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) {
    $r = $_SESSION['user_role'];
    if ($r === 'administrateur')  header('Location: /dashboard/admin.php');
    elseif ($r === 'bibliothecaire') header('Location: /dashboard/bibliothecaire.php');
    else header('Location: /dashboard/adherent.php');
    exit;
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bibliothèque Universitaire — Connexion</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; }
  .playfair { font-family: 'Playfair Display', serif; }

  .bg-pattern {
    background-color: #0f172a;
    background-image:
      radial-gradient(circle at 20% 50%, rgba(245,158,11,0.10) 0%, transparent 50%),
      radial-gradient(circle at 80% 20%, rgba(59,130,246,0.07) 0%, transparent 40%),
      radial-gradient(circle at 60% 85%, rgba(245,158,11,0.06) 0%, transparent 40%);
  }

  .card-glass {
    background: rgba(30,41,59,0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.08);
  }

  .input-field {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
    transition: all 0.2s;
    width: 100%;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
  }
  .input-field:focus {
    outline: none;
    border-color: #f59e0b;
    background: rgba(245,158,11,0.06);
  }
  .input-field::placeholder { color: rgba(255,255,255,0.25); }

  .btn-primary {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    transition: all 0.2s;
    width: 100%;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
  }
  .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(245,158,11,0.35);
  }
  .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

  .tab-active   { color: white; border-bottom: 2px solid #f59e0b; }
  .tab-inactive { color: rgba(255,255,255,0.35); border-bottom: 2px solid transparent; }

  .label {
    display: block;
    color: rgba(203,213,225,1);
    font-size: 0.7rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.4rem;
  }
</style>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center p-4">

<!-- Decoration top-left -->
<div class="fixed top-0 left-0 w-96 h-96 opacity-20 pointer-events-none">
  <svg viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="0" cy="0" r="300" fill="url(#grad1)"/>
    <defs>
      <radialGradient id="grad1" cx="0%" cy="0%" r="100%">
        <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:0.3"/>
        <stop offset="100%" style="stop-color:#f59e0b;stop-opacity:0"/>
      </radialGradient>
    </defs>
  </svg>
</div>

<div class="w-full max-w-md relative z-10">

  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-5 shadow-lg"
         style="background: linear-gradient(135deg,#f59e0b,#d97706)">
      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
             C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
             C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
             C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
      </svg>
    </div>
    <h1 class="playfair text-4xl text-white font-bold tracking-tight">Bibliothèque</h1>
    <p class="text-slate-400 mt-2 text-sm tracking-widest uppercase">Faculté des Sciences</p>
  </div>

  <!-- Card -->
  <div class="card-glass rounded-3xl p-8 shadow-2xl">

    <!-- Tabs -->
    <div class="flex border-b border-slate-700/60 mb-7 gap-6">
      <button id="tab-login"
        onclick="switchTab('login')"
        class="pb-3 text-sm font-medium transition-all tab-active">
        Connexion
      </button>
      <button id="tab-register"
        onclick="switchTab('register')"
        class="pb-3 text-sm font-medium transition-all tab-inactive">
        Inscription
      </button>
    </div>

    <!-- Alertes -->
    <?php if ($error === 'access_denied'): ?>
    <div class="bg-red-500/10 border border-red-500/25 text-red-300 px-4 py-3 rounded-xl mb-5 text-sm flex items-center gap-2">
      <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      Accès non autorisé. Veuillez vous connecter.
    </div>
    <?php endif; ?>

    <div id="error-msg" class="hidden bg-red-500/10 border border-red-500/25 text-red-300 px-4 py-3 rounded-xl mb-5 text-sm flex items-center gap-2">
      <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <span id="error-text"></span>
    </div>

    <div id="success-msg" class="hidden bg-green-500/10 border border-green-500/25 text-green-300 px-4 py-3 rounded-xl mb-5 text-sm flex items-center gap-2">
      <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
      </svg>
      <span id="success-text"></span>
    </div>

    <!-- ── FORMULAIRE CONNEXION ── -->
    <div id="form-login">
      <div class="space-y-4">

        <div>
          <label class="label">Email</label>
          <input type="email" id="login-email"
            placeholder="votre@email.com"
            class="input-field">
        </div>

        <div>
          <label class="label">Mot de passe</label>
          <div class="relative">
            <input type="password" id="login-password"
              placeholder="••••••••"
              style="padding-right:2.75rem"
              class="input-field">
            <button type="button" onclick="togglePwd('login-password')"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                     -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <button onclick="login()" id="btn-login" class="btn-primary mt-2">
          Se connecter
        </button>
      </div>

      <!-- Comptes démo -->
      <div class="mt-6 p-4 rounded-2xl" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07)">
        <p class="text-xs text-slate-400 font-semibold mb-3 uppercase tracking-wider">
          Comptes de démonstration
        </p>
        <div class="space-y-2">
          <button onclick="fillLogin('admin@biblio.com')"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-white/5 transition text-left">
            <span class="w-7 h-7 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center text-xs">👑</span>
            <div>
              <p class="text-xs font-medium text-slate-200">Administrateur</p>
              <p class="text-xs text-slate-500">admin@biblio.com</p>
            </div>
          </button>
          <button onclick="fillLogin('biblio@biblio.com')"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-white/5 transition text-left">
            <span class="w-7 h-7 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs">📚</span>
            <div>
              <p class="text-xs font-medium text-slate-200">Bibliothécaire</p>
              <p class="text-xs text-slate-500">biblio@biblio.com</p>
            </div>
          </button>
          <button onclick="fillLogin('ahmed@etudiant.com')"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-white/5 transition text-left">
            <span class="w-7 h-7 rounded-lg bg-green-500/20 text-green-400 flex items-center justify-center text-xs">🎓</span>
            <div>
              <p class="text-xs font-medium text-slate-200">Adhérent</p>
              <p class="text-xs text-slate-500">ahmed@etudiant.com</p>
            </div>
          </button>
        </div>
        <p class="text-xs text-slate-600 mt-3 text-center">
          Mot de passe : <span class="text-slate-400 font-mono">password</span>
        </p>
      </div>
    </div>

    <!-- ── FORMULAIRE INSCRIPTION ── -->
    <div id="form-register" class="hidden">
      <div class="space-y-3">

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Prénom</label>
            <input type="text" id="reg-prenom" placeholder="Prénom" class="input-field">
          </div>
          <div>
            <label class="label">Nom</label>
            <input type="text" id="reg-nom" placeholder="Nom" class="input-field">
          </div>
        </div>

        <div>
          <label class="label">Email</label>
          <input type="email" id="reg-email" placeholder="votre@email.com" class="input-field">
        </div>

        <div>
          <label class="label">Téléphone</label>
          <input type="tel" id="reg-tel" placeholder="55 000 000" class="input-field">
        </div>

        <div>
          <label class="label">Mot de passe</label>
          <div class="relative">
            <input type="password" id="reg-password"
              placeholder="Min. 6 caractères"
              style="padding-right:2.75rem"
              class="input-field">
            <button type="button" onclick="togglePwd('reg-password')"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                     -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <button onclick="register()" id="btn-register" class="btn-primary">
          Créer mon compte
        </button>

      </div>
    </div>

  </div>

  <p class="text-center text-slate-700 text-xs mt-6">
    © <?= date('Y') ?> Bibliothèque Universitaire. Tous droits réservés.
  </p>
</div>

<script>
// ── TAB SWITCH ─────────────────────────────────────────────
function switchTab(tab) {
  const isLogin = tab === 'login';
  document.getElementById('form-login').classList.toggle('hidden', !isLogin);
  document.getElementById('form-register').classList.toggle('hidden', isLogin);
  document.getElementById('tab-login').className    = 'pb-3 text-sm font-medium transition-all ' + (isLogin  ? 'tab-active' : 'tab-inactive');
  document.getElementById('tab-register').className = 'pb-3 text-sm font-medium transition-all ' + (!isLogin ? 'tab-active' : 'tab-inactive');
  hideAlerts();
}

// ── ALERTS ─────────────────────────────────────────────────
function showError(msg) {
  document.getElementById('error-text').textContent = msg;
  document.getElementById('error-msg').classList.remove('hidden');
  document.getElementById('success-msg').classList.add('hidden');
}
function showSuccess(msg) {
  document.getElementById('success-text').textContent = msg;
  document.getElementById('success-msg').classList.remove('hidden');
  document.getElementById('error-msg').classList.add('hidden');
}
function hideAlerts() {
  document.getElementById('error-msg').classList.add('hidden');
  document.getElementById('success-msg').classList.add('hidden');
}

// ── TOGGLE PASSWORD ────────────────────────────────────────
function togglePwd(id) {
  const inp = document.getElementById(id);
  inp.type  = inp.type === 'password' ? 'text' : 'password';
}

// ── FILL DEMO LOGIN ────────────────────────────────────────
function fillLogin(email) {
  document.getElementById('login-email').value    = email;
  document.getElementById('login-password').value = 'password';
  hideAlerts();
}

// ── LOGIN ──────────────────────────────────────────────────
async function login() {
  const email    = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value;

  if (!email || !password) {
    showError('Veuillez remplir tous les champs');
    return;
  }

  const btn = document.getElementById('btn-login');
  btn.textContent = 'Connexion en cours…';
  btn.disabled    = true;

  try {
    const res  = await fetch('/api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    });
    const data = await res.json();

    if (data.success) {
      const routes = {
        administrateur:  '/dashboard/admin.php',
        bibliothecaire:  '/dashboard/bibliothecaire.php'
      };
      window.location.href = routes[data.role] || '/dashboard/adherent.php';
    } else {
      showError(data.message || 'Identifiants incorrects');
      btn.textContent = 'Se connecter';
      btn.disabled    = false;
    }
  } catch (e) {
    showError('Erreur réseau, veuillez réessayer');
    btn.textContent = 'Se connecter';
    btn.disabled    = false;
  }
}

// ── REGISTER ───────────────────────────────────────────────
async function register() {
  const prenom = document.getElementById('reg-prenom').value.trim();
  const nom    = document.getElementById('reg-nom').value.trim();
  const email  = document.getElementById('reg-email').value.trim();
  const tel    = document.getElementById('reg-tel').value.trim();
  const pass   = document.getElementById('reg-password').value;

  if (!prenom || !nom || !email || !pass) {
    showError('Veuillez remplir tous les champs obligatoires');
    return;
  }
  if (pass.length < 6) {
    showError('Le mot de passe doit contenir au moins 6 caractères');
    return;
  }

  const btn = document.getElementById('btn-register');
  btn.textContent = 'Création en cours…';
  btn.disabled    = true;

  try {
    const res  = await fetch('/api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=register`
           + `&nom=${encodeURIComponent(nom)}`
           + `&prenom=${encodeURIComponent(prenom)}`
           + `&email=${encodeURIComponent(email)}`
           + `&password=${encodeURIComponent(pass)}`
           + `&telephone=${encodeURIComponent(tel)}`
    });
    const data = await res.json();

    if (data.success) {
      showSuccess('Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
      switchTab('login');
      document.getElementById('login-email').value = email;
    } else {
      showError(data.message || 'Erreur lors de la création du compte');
    }
  } catch (e) {
    showError('Erreur réseau, veuillez réessayer');
  }

  btn.textContent = 'Créer mon compte';
  btn.disabled    = false;
}

// ── ENTER KEY ──────────────────────────────────────────────
document.addEventListener('keypress', e => {
  if (e.key === 'Enter') {
    const registerHidden = document.getElementById('form-register').classList.contains('hidden');
    registerHidden ? login() : register();
  }
});
</script>
</body>
</html>