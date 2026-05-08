<?php
// auth/register.php
session_start();
$error = $_SESSION['error_msg'] ?? '';
unset($_SESSION['error_msg']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>JBeauty - Create Account</title>
<link rel="stylesheet" href="../assets/css/register.css">
<link rel="icon" type="image/png" href="assets/img/jbeautylogo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

</head>
<body>

<div class="auth-shell">

  <!-- ① Form Panel (left) -->
  <div class="form-panel">

    <a href="../index.php" class="store-link">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M9 2L4 7L9 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Return to Store
    </a>

    <div class="form-panel__inner">

      <h1 class="form-heading">Join the<br><em>JBeauty family.</em></h1>
      <p class="form-subheading">Create your free account and discover beauty that speaks to you — plus exclusive member perks from day one.</p>

      <!-- PHP Error Display -->
      <?php if(!empty($error)): ?>
        <div style="background: #FDECEA; color: #E53935; padding: 12px; border-radius: 4px; font-size: 13px; margin-bottom: 20px; border-left: 4px solid #E53935;">
            <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Form Wrapper Added Here -->
      <form action="process_register.php" method="POST" enctype="multipart/form-data">
          <!-- Name row -->
          <div class="field-cols">
            <div class="field">
              <label class="field__label" for="r-fname">First Name</label>
              <!-- Added name="first_name" and required -->
              <input class="field__input" id="r-fname" name="first_name" type="text" placeholder="e.g. Jamie" autocomplete="given-name" required/>
            </div>
            <div class="field">
              <label class="field__label" for="r-lname">Last Name</label>
              <!-- Added name="last_name" and required -->
              <input class="field__input" id="r-lname" name="last_name" type="text" placeholder="e.g. Santos" autocomplete="family-name" required/>
            </div>

            <!-- Email -->
            <div class="field field--full" style="margin-bottom:0">
              <label class="field__label" for="r-email">Email Address</label>
              <!-- Added name="email" and required -->
              <input class="field__input" id="r-email" name="email" type="email" placeholder="hello@yourname.com" autocomplete="email" required/>
            </div>

            <!-- Phone -->
            <div class="field field--full" style="margin-bottom:0">
              <label class="field__label" for="r-phone">Mobile Number <span style="font-size:10px;opacity:.6;text-transform:none;letter-spacing:0">(for order updates)</span></label>
              <!-- Added name="phone" -->
              <input class="field__input" id="r-phone" name="phone" type="tel" placeholder="+63 917 000 0000" autocomplete="tel"/>
            </div>

            <!-- Password -->
            <div class="field field--full" style="margin-bottom:4px">
              <label class="field__label" for="r-pass">Password</label>
              <div class="field__input-wrap">
                <!-- Added name="password" and required -->
                <input class="field__input" id="r-pass" name="password" type="password" placeholder="Create a secure password" autocomplete="new-password" required oninput="updateStrength(this.value)"/>
                <button class="field__toggle" type="button" aria-label="Show password" onclick="togglePwd(this,'r-pass')">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
              </div>
              <!-- strength bars -->
              <div class="pwd-strength" id="strength-bars">
                <div class="pwd-bar" id="bar1"></div>
                <div class="pwd-bar" id="bar2"></div>
                <div class="pwd-bar" id="bar3"></div>
                <div class="pwd-bar" id="bar4"></div>
              </div>
            </div>
          </div>

          <!-- Identity Verification (KYC — softened) -->
          <div class="kyc-section" style="margin-top:8px">
            <div class="kyc-section__header">
              <div class="kyc-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C9897A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="5" width="20" height="14" rx="2"/>
                  <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
              </div>
              <div>
                <div class="kyc-section__title">Secure Shopper Verification</div>
                <div class="kyc-section__desc">Upload a valid government-issued ID to confirm your identity. This keeps your account and purchases safe. <em>Optional — you can complete this later in your account settings.</em></div>
              </div>
            </div>
            <div class="kyc-upload" id="kyc-drop">
              <!-- Added name="kyc_doc" -->
              <input type="file" name="kyc_doc" accept="image/*,.pdf" aria-label="Upload ID document" onchange="handleFileSelect(this)"/>
              <div class="kyc-upload__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="16 16 12 12 8 16"/>
                  <line x1="12" y1="12" x2="12" y2="21"/>
                  <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                </svg>
              </div>
              <div class="kyc-upload__text" id="kyc-text">Upload ID for Secure Shopping</div>
              <div class="kyc-upload__hint">Driver's licence, passport, or national ID · JPG, PNG or PDF</div>
            </div>
          </div>

          <!-- Terms -->
          <div class="terms-row">
            <!-- Added name and required -->
            <input type="checkbox" id="terms-chk" name="terms" required/>
            <label class="terms-text" for="terms-chk">
              I agree to JBeauty's <a href="#">Terms of Service</a>, <a href="#">Privacy Policy</a>, and consent to receiving personalised beauty recommendations and order updates by email.
            </label>
          </div>

          <!-- Changed type from "button" to "submit" -->
          <button class="btn-primary" type="submit">Create My Account</button>

      </form>

      <p class="switch-link">Already have an account? <a href="../auth/login.php">Sign in</a></p>

    </div>

    <p class="form-footer">
      Your data is encrypted and never sold. <a href="#">Learn how we protect you →</a>
    </p>

  </div>

  <!-- ② Visual Panel (right) -->
  <div class="visual-panel" aria-hidden="true">
    <div class="visual-panel__placeholder">

      <svg width="100%" height="100%" viewBox="0 0 600 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" style="position:absolute;inset:0">
        <defs>
          <radialGradient id="bg2" cx="60%" cy="30%" r="80%">
            <stop offset="0%" stop-color="#C8AFA4"/>
            <stop offset="100%" stop-color="#5E3E32"/>
          </radialGradient>
          <radialGradient id="glow2" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.28"/>
            <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0"/>
          </radialGradient>
          <filter id="blur12"><feGaussianBlur stdDeviation="12"/></filter>
          <filter id="blur22"><feGaussianBlur stdDeviation="22"/></filter>
        </defs>

        <rect width="600" height="900" fill="url(#bg2)"/>
        <ellipse cx="130" cy="200" rx="200" ry="180" fill="#D4B0A0" opacity="0.5" filter="url(#blur22)"/>
        <ellipse cx="500" cy="700" rx="220" ry="200" fill="#7A4E3C" opacity="0.5" filter="url(#blur22)"/>
        <ellipse cx="300" cy="450" rx="280" ry="240" fill="url(#glow2)" filter="url(#blur12)"/>

        <!-- Mascara tube -->
        <g transform="translate(260,80)">
          <rect x="30" y="0" width="30" height="160" rx="8" fill="rgba(28,28,30,0.7)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
          <rect x="30" y="155" width="30" height="50" rx="4" fill="rgba(201,137,122,0.7)" stroke="rgba(255,255,255,0.35)" stroke-width="1"/>
          <!-- wand -->
          <rect x="43" y="-40" width="4" height="50" rx="2" fill="rgba(255,255,255,0.6)"/>
          <ellipse cx="45" cy="-44" rx="10" ry="5" fill="rgba(28,28,30,0.6)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
          <!-- shine -->
          <rect x="35" y="10" width="5" height="130" rx="3" fill="rgba(255,255,255,0.2)"/>
        </g>

        <!-- Serum dropper bottle -->
        <g transform="translate(100,180)">
          <!-- bottle -->
          <rect x="30" y="80" width="70" height="150" rx="16" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.45)" stroke-width="1.2"/>
          <!-- neck -->
          <rect x="50" y="42" width="30" height="42" rx="5" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.4)" stroke-width="1"/>
          <!-- dropper top -->
          <ellipse cx="65" cy="38" rx="22" ry="12" fill="rgba(255,255,255,0.3)" stroke="rgba(255,255,255,0.5)" stroke-width="1"/>
          <rect x="62" y="8" width="6" height="34" rx="3" fill="rgba(255,255,255,0.55)"/>
          <!-- label -->
          <rect x="36" y="110" width="58" height="90" rx="4" fill="rgba(255,255,255,0.1)" stroke="rgba(255,255,255,0.25)" stroke-width="1"/>
          <rect x="44" y="125" width="42" height="3.5" rx="2" fill="rgba(255,255,255,0.45)"/>
          <rect x="50" y="135" width="30" height="2.5" rx="2" fill="rgba(255,255,255,0.3)"/>
          <rect x="44" y="175" width="42" height="2.5" rx="2" fill="rgba(255,255,255,0.25)"/>
          <!-- shine -->
          <line x1="42" y1="90" x2="42" y2="225" stroke="rgba(255,255,255,0.3)" stroke-width="4" stroke-linecap="round"/>
        </g>

        <!-- Face cream jar -->
        <g transform="translate(330,320)">
          <ellipse cx="70" cy="90" rx="70" ry="24" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.35)" stroke-width="1"/>
          <rect x="0" y="65" width="140" height="60" rx="4" fill="rgba(255,255,255,0.16)" stroke="rgba(255,255,255,0.35)" stroke-width="1"/>
          <!-- lid -->
          <ellipse cx="70" cy="65" rx="70" ry="22" fill="rgba(255,255,255,0.3)" stroke="rgba(255,255,255,0.5)" stroke-width="1.2"/>
          <ellipse cx="70" cy="64" rx="56" ry="16" fill="rgba(255,255,255,0.15)"/>
          <!-- label on body -->
          <rect x="20" y="70" width="100" height="38" rx="3" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>
          <rect x="30" y="79" width="80" height="3" rx="2" fill="rgba(255,255,255,0.4)"/>
          <rect x="38" y="87" width="64" height="2" rx="2" fill="rgba(255,255,255,0.3)"/>
          <!-- shine on lid -->
          <ellipse cx="48" cy="58" rx="18" ry="7" fill="rgba(255,255,255,0.3)" transform="rotate(-12,48,58)"/>
        </g>

        <!-- Skincare bottle -->
        <g transform="translate(140,520)">
          <rect x="20" y="40" width="80" height="200" rx="20" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.35)" stroke-width="1.2"/>
          <!-- pump head -->
          <rect x="46" y="8" width="28" height="36" rx="6" fill="rgba(255,255,255,0.22)" stroke="rgba(255,255,255,0.4)" stroke-width="1"/>
          <rect x="66" y="0" width="14" height="20" rx="4" fill="rgba(255,255,255,0.3)" stroke="rgba(255,255,255,0.45)" stroke-width="1"/>
          <!-- label -->
          <rect x="28" y="80" width="64" height="120" rx="4" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.22)" stroke-width="1"/>
          <rect x="36" y="98" width="48" height="3" rx="2" fill="rgba(255,255,255,0.4)"/>
          <rect x="42" y="108" width="36" height="2" rx="2" fill="rgba(255,255,255,0.3)"/>
          <rect x="36" y="162" width="48" height="2" rx="2" fill="rgba(255,255,255,0.22)"/>
          <!-- shine -->
          <line x1="30" y1="52" x2="30" y2="235" stroke="rgba(255,255,255,0.28)" stroke-width="5" stroke-linecap="round"/>
        </g>

        <!-- Decorative lines -->
        <circle cx="480" cy="280" r="100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
        <circle cx="480" cy="280" r="70"  fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
        <circle cx="80"  cy="760" r="80"  fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>

        <!-- Scattered dots -->
        <circle cx="400" cy="150" r="3"   fill="rgba(255,255,255,0.4)"/>
        <circle cx="220" cy="440" r="2.5" fill="rgba(255,255,255,0.35)"/>
        <circle cx="490" cy="520" r="3.5" fill="rgba(255,255,255,0.3)"/>
        <circle cx="55"  cy="460" r="2"   fill="rgba(255,255,255,0.4)"/>
        <circle cx="330" cy="760" r="3"   fill="rgba(255,255,255,0.25)"/>
        <circle cx="520" cy="380" r="2"   fill="rgba(255,255,255,0.35)"/>
      </svg>

      <div class="visual-panel__overlay"></div>
    </div>

    <div class="visual-panel__brand-stamp">
      <p class="brand-stamp__eyebrow">Your beauty journey</p>
      <p class="brand-stamp__quote">Where every skin story begins.</p>
    </div>
  </div>

</div>

<script>
  function togglePwd(btn, id) {
    const input = document.getElementById(id);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    btn.querySelector('svg').innerHTML = isHidden
      ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`
      : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  }

  function updateStrength(val) {
    let score = 0;
    if (val.length >= 8)            score++;
    if (/[A-Z]/.test(val))          score++;
    if (/[0-9]/.test(val))          score++;
    if (/[^A-Za-z0-9]/.test(val))   score++;
    const classes = ['active-1','active-2','active-3','active-4'];
    for (let i = 1; i <= 4; i++) {
      const bar = document.getElementById('bar' + i);
      bar.className = 'pwd-bar';
      if (i <= score) bar.classList.add(classes[score - 1]);
    }
  }

  function handleFileSelect(input) {
    if (input.files && input.files[0]) {
      document.getElementById('kyc-text').textContent = '✓ ' + input.files[0].name;
    }
  }
</script>

</body>
</html>