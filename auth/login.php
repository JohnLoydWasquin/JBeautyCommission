<?php
// auth/login.php
session_start();
$error = $_SESSION['error_msg'] ?? '';
$success = $_SESSION['success_msg'] ?? '';
unset($_SESSION['error_msg'], $_SESSION['success_msg']); // Clear messages after showing them
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>JBeauty - Sign In</title>
<link rel="stylesheet" href="../assets/css/login.css">
<link rel="icon" type="image/png" href="assets/img/jbeautylogo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>

<div class="auth-shell">

  <div class="visual-panel" aria-hidden="true">
    <div class="visual-panel__placeholder">

      <svg width="100%" height="100%" viewBox="0 0 600 800" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" style="position:absolute;inset:0;">
        <defs>
          <radialGradient id="bg" cx="40%" cy="35%" r="70%">
            <stop offset="0%"   stop-color="#F0DDD7"/>
            <stop offset="100%" stop-color="#9E7E6F"/>
          </radialGradient>
          <radialGradient id="glow1" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.35"/>
            <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0"/>
          </radialGradient>
          <filter id="blur8">
            <feGaussianBlur stdDeviation="8"/>
          </filter>
          <filter id="blur20">
            <feGaussianBlur stdDeviation="20"/>
          </filter>
        </defs>

        <rect width="600" height="800" fill="url(#bg)"/>

        <ellipse cx="480" cy="120" rx="220" ry="200" fill="#E8C4B8" opacity="0.45" filter="url(#blur20)"/>
        <ellipse cx="80"  cy="650" rx="180" ry="160" fill="#C4917D" opacity="0.4"  filter="url(#blur20)"/>
        <ellipse cx="300" cy="400" rx="300" ry="260" fill="url(#glow1)" filter="url(#blur8)"/>

        <g transform="translate(180,140)">
          <rect x="60" y="110" width="120" height="180" rx="12" fill="rgba(255,255,255,0.22)" stroke="rgba(255,255,255,0.5)" stroke-width="1.5"/>
          <rect x="90" y="72" width="60"  height="42"  rx="6"  fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.45)" stroke-width="1.5"/>
          <rect x="82" y="48" width="76"  height="28"  rx="5"  fill="rgba(255,255,255,0.35)" stroke="rgba(255,255,255,0.6)" stroke-width="1.5"/>
          <rect x="75" y="145" width="90" height="100" rx="4"  fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
          <rect x="87" y="162" width="66" height="4" rx="2" fill="rgba(255,255,255,0.5)"/>
          <rect x="95" y="173" width="50" height="3" rx="2" fill="rgba(255,255,255,0.35)"/>
          <rect x="91" y="225" width="58" height="3" rx="2" fill="rgba(255,255,255,0.3)"/>
          <line x1="80" y1="120" x2="80" y2="280" stroke="rgba(255,255,255,0.35)" stroke-width="5" stroke-linecap="round"/>
        </g>

        <g transform="translate(330,210)">
          <rect x="22" y="60" width="36" height="100" rx="4" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.4)" stroke-width="1.2"/>
          <path d="M22,60 Q22,28 40,22 Q58,28 58,60 Z" fill="rgba(201,137,122,0.6)" stroke="rgba(255,255,255,0.5)" stroke-width="1.2"/>
          <line x1="34" y1="58" x2="38" y2="36" stroke="rgba(255,255,255,0.6)" stroke-width="2.5" stroke-linecap="round"/>
        </g>

        <g transform="translate(90,310)">
          <circle cx="50" cy="50" r="48" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.4)" stroke-width="1.2"/>
          <circle cx="50" cy="50" r="36" fill="rgba(201,137,122,0.3)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
          <circle cx="50" cy="50" r="22" fill="rgba(255,255,255,0.18)"/>
          <ellipse cx="38" cy="36" rx="10" ry="6" fill="rgba(255,255,255,0.35)" transform="rotate(-20,38,36)"/>
        </g>

        <circle cx="430" cy="550" r="120" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
        <circle cx="430" cy="550" r="90"  fill="none" stroke="rgba(255,255,255,0.1)"  stroke-width="1"/>

        <circle cx="460" cy="350" r="3" fill="rgba(255,255,255,0.4)"/>
        <circle cx="150" cy="500" r="2" fill="rgba(255,255,255,0.35)"/>
        <circle cx="370" cy="680" r="4" fill="rgba(255,255,255,0.25)"/>
        <circle cx="520" cy="220" r="2.5" fill="rgba(255,255,255,0.4)"/>
        <circle cx="60"  cy="180" r="3" fill="rgba(255,255,255,0.3)"/>

        <ellipse cx="390" cy="460" rx="14" ry="40" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.3)" stroke-width="1" transform="rotate(-30,390,460)"/>
        <ellipse cx="415" cy="445" rx="10" ry="30" fill="rgba(255,255,255,0.1)"  stroke="rgba(255,255,255,0.25)" stroke-width="1" transform="rotate(15,415,445)"/>
      </svg>

      <div class="visual-panel__overlay"></div>
    </div>

    <div class="visual-panel__brand-stamp">
      <p class="brand-stamp__eyebrow">JBeauty Collection</p>
      <p class="brand-stamp__quote">Beauty is the art of being<br>effortlessly yourself.</p>
    </div>
  </div>

  <div class="form-panel">

    <a href="../index.php" class="store-link">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M9 2L4 7L9 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Continue Shopping
    </a>

    <div class="form-panel__inner">

      <h1 class="form-heading">Welcome<br><em>back.</em></h1>
      <p class="form-subheading">Sign in to access your wishlist, order history, and exclusive member offers.</p>

      <?php if(!empty($success)): ?>
        <div style="background: #E8F5E9; color: #2E7D32; padding: 12px; border-radius: 4px; font-size: 13px; margin-bottom: 20px; border-left: 4px solid #2E7D32;">
            <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if(!empty($error)): ?>
        <div style="background: #FDECEA; color: #E53935; padding: 12px; border-radius: 4px; font-size: 13px; margin-bottom: 20px; border-left: 4px solid #E53935;">
            <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="social-row">
        <a href="#" class="social-btn">
          <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
            <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
            <path d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z" fill="#FBBC05"/>
            <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 6.293C4.672 4.166 6.656 3.58 9 3.58z" fill="#EA4335"/>
          </svg>
          Google
        </a>
        <a href="#" class="social-btn">
          <svg width="14" height="16" viewBox="0 0 814 1000" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
            <path d="M788.1 340.9c-5.8 4.5-108.2 62.2-108.2 190.5 0 148.4 130.3 200.9 134.2 202.2-.6 3.2-20.7 71.9-68.7 141.9-42.8 61.6-87.5 123.1-155.5 123.1s-85.5-39.5-164-39.5c-76 0-103.7 40.8-165.9 40.8s-105.3-57.8-155.5-127.4C46 376.7 0 255.3 0 139.9c0-92.3 33.6-177.4 84.7-235.8 50.4-58.4 121.5-93.8 185.5-93.8 58.4 0 112.1 39.5 150.4 39.5 36.6 0 98.9-42.5 167.6-42.5 30.4 0 108.2 5.2 166.3 72.9zm-56.6-164.5c-27.2 30.1-54.6 54.6-99.3 54.6-4.8 0-9.7-.6-14.6-.8 2.9-42.8 18.4-79.3 42.5-108.6 27-31.4 68.1-55.4 102.3-59.4.6 3.5.7 7 .7 10.5 0 41.6-14.4 82.9-31.6 103.7z"/>
          </svg>
          Apple
        </a>
      </div>

      <div class="divider">
        <div class="divider__line"></div>
        <span class="divider__text">or</span>
        <div class="divider__line"></div>
      </div>

      <form action="process_login.php" method="POST">
          <div class="field">
            <label class="field__label" for="login-email">Email Address</label>
            <input class="field__input" id="login-email" name="email" type="email" placeholder="hello@yourname.com" autocomplete="email" required/>
          </div>

          <div class="field">
            <label class="field__label" for="login-pass">Password</label>
            <div class="field__input-wrap">
              <input class="field__input" id="login-pass" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required/>
              <button class="field__toggle" type="button" aria-label="Show password" onclick="togglePwd(this,'login-pass')">
                <svg id="eye-login" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="field-row">
            <label class="checkbox-label">
              <input type="checkbox" name="remember"> Remember me
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button class="btn-primary" type="submit">Sign In</button>
      </form>

      <p class="switch-link">New to JBeauty? <a href="register.php">Create an account</a></p>

    </div>

  </div>
</div>

<script>
  function togglePwd(btn, id) {
    const input = document.getElementById(id);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    // swap icon
    btn.querySelector('svg').innerHTML = isHidden
      ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`
      : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  }
</script>

</body>
</html>