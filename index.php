<?php
// Start session to access the cart
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate the total number of items in the cart
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_column($cart, 'qty'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="JBeauty — Premium, inclusive cosmetics crafted for every skin tone. Discover our curated collection of luxurious skincare and makeup." />
  <title>JBeauty - Premium Cosmetics</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="assets/css/home.css" />
  <link rel="icon" type="image/png" href="assets/img/jbeautylogo.jpg">
</head>

<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <header id="header" role="banner" aria-label="Main navigation">
    <div class="container">
      <nav class="nav-inner" aria-label="Primary">

        <a href="#home" class="nav-logo" aria-label="JBeauty — go to homepage">
          <div class="nav-logo-mark" aria-hidden="true">J</div>
          <span class="nav-logo-text">JBeauty</span>
        </a>

        <ul class="nav-links" role="list">
          <li><a href="#home">Home</a></li>
          <li><a href="shop.php">Shop</a></li>
          <li><a href="#about">About Us</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>

        <div class="nav-actions">
          <?php $profileRoute = isset($_SESSION['user_id']) ? 'profile.php' : 'auth/login.php'; ?>
          <a href="<?= $profileRoute ?>" class="nav-icon-btn" aria-label="View your profile">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
          </a>
          <a href="shop.php" class="nav-icon-btn" aria-label="Shopping cart">
            <?php if ($cartCount > 0): ?>
                <span class="cart-badge" aria-hidden="true"><?= $cartCount ?></span>
            <?php endif; ?>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
            </svg>
          </a>

          <button class="nav-hamburger" id="hamburgerBtn" aria-label="Open navigation menu" aria-expanded="false" aria-controls="mobileMenu">
            <span></span><span></span><span></span>
          </button>
        </div>

      </nav>
    </div>

    <nav id="mobileMenu" class="mobile-menu" aria-label="Mobile navigation" aria-hidden="true">
    <a href="#home">Home</a>
    <a href="shop.php">Shop</a>
    <a href="#about">About Us</a>
    <a href="#contact">Contact</a>

    <!-- Divider -->
    <hr style="border:none; border-top:1px solid rgba(44,27,14,.12); margin:.5rem 1.2rem;">

    <!-- Dynamic account links -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php">My Profile</a>
        <a href="auth/logout.php" style="color:#C0392B;">Log Out</a>
    <?php else: ?>
        <a href="auth/login.php">Log In / Register</a>
    <?php endif; ?>
    </nav>
  </header>

  <main id="main-content">

    <section id="home" aria-labelledby="hero-headline">

      <div class="hero-bg" aria-hidden="true">
        <div class="hero-bg-art"></div>
        <div class="hero-bg-img"></div>
      </div>

      <div class="hero-image-placeholder" aria-hidden="true">
        <span class="hero-image-label">Campaign 2026</span>
      </div>

      <div class="hero-circle" aria-hidden="true"></div>

      <div class="container">
        <div class="hero-content">
          <p class="hero-eyebrow">Premium Cosmetics</p>
          <h1 class="hero-headline" id="hero-headline">
            Beauty is the art of<br />being <em>effortlessly</em><br />yourself.
          </h1>
          <p class="hero-subtext">
            Discover a curated collection of clean, luxurious cosmetics crafted for every skin tone, every story, every you.
          </p>
          <div class="hero-cta-group">
            <a href="shop.php" class="btn btn-primary">
              Shop the Collection
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3"/>
              </svg>
            </a>
            <a href="#about" class="btn btn-outline">Our Story</a>
          </div>
        </div>
      </div>

      <div class="hero-scroll-hint" aria-hidden="true">
        <span>Scroll</span>
        <div class="scroll-line"></div>
      </div>

    </section>

    <div class="marquee-section" aria-hidden="true">
      <div class="marquee-track">
        <span class="marquee-item">Premium Cosmetics <span class="marquee-dot"></span></span>
        <span class="marquee-item">Clean Beauty <span class="marquee-dot"></span></span>
        <span class="marquee-item">Inclusive Shades <span class="marquee-dot"></span></span>
        <span class="marquee-item">Cruelty-Free <span class="marquee-dot"></span></span>
        <span class="marquee-item">Dermatologist Tested <span class="marquee-dot"></span></span>
        <span class="marquee-item">Vegan Formulas <span class="marquee-dot"></span></span>
        <span class="marquee-item">Sustainably Sourced <span class="marquee-dot"></span></span>
        <span class="marquee-item">JBeauty 2026 <span class="marquee-dot"></span></span>
        <span class="marquee-item">Premium Cosmetics <span class="marquee-dot"></span></span>
        <span class="marquee-item">Clean Beauty <span class="marquee-dot"></span></span>
        <span class="marquee-item">Inclusive Shades <span class="marquee-dot"></span></span>
        <span class="marquee-item">Cruelty-Free <span class="marquee-dot"></span></span>
        <span class="marquee-item">Dermatologist Tested <span class="marquee-dot"></span></span>
        <span class="marquee-item">Vegan Formulas <span class="marquee-dot"></span></span>
        <span class="marquee-item">Sustainably Sourced <span class="marquee-dot"></span></span>
        <span class="marquee-item">JBeauty 2026 <span class="marquee-dot"></span></span>
      </div>
    </div>

    <section id="about" aria-labelledby="about-title">
      <div class="container">
        <div class="about-layout">

          <div class="about-visual reveal">
            <div class="about-main-img">
              <img src="assets/img/campaign_photo.jpg" alt="JBeauty inclusive campaign models">
            </div>

            <div class="about-stat-card card-b" aria-hidden="true">
              <p class="stat-number">40+</p>
              <p class="stat-label">Skin-tone shades</p>
            </div>
            <div class="about-stat-card card-a" aria-hidden="true">
              <p class="stat-number">98%</p>
              <p class="stat-label">Customer satisfaction</p>
            </div>
          </div>

          <div class="about-text">
            <span class="section-label reveal">Our Story</span>
            <h2 class="section-title reveal reveal-delay-1" id="about-title">
              More than makeup —<br />a movement.
            </h2>

            <div class="about-body">
              <p class="lead reveal reveal-delay-2">
                "Every shade of humanity deserves a shade of its own."
              </p>
              <p class="reveal reveal-delay-2">
                JBeauty was born from a simple but powerful belief: that beauty products should celebrate the full spectrum of human skin, not conform to a narrow standard. Founded by a team of cosmetic chemists and makeup artists with decades of combined experience, JBeauty set out to reimagine what a <strong>premium cosmetics brand</strong> could look like — one where inclusivity isn't a marketing afterthought, but the foundation everything is built upon.
              </p>
              <p class="reveal reveal-delay-3">
                Every product in our collection is the result of relentless research and testing across <strong>diverse skin tones, types, and undertones</strong>. We work alongside board-certified dermatologists to formulate cosmetics that don't just perform beautifully — they actively care for your skin. Our ingredient philosophy is unapologetically transparent: no parabens, no synthetic dyes, no compromises. We source nourishing botanicals like rosehip oil, shea butter, and hyaluronic acid from ethical, sustainably managed suppliers across four continents.
              </p>
              <p class="reveal reveal-delay-3">
                At JBeauty, luxury is redefined. It isn't about exclusivity — it's about craftsmanship, integrity, and the confidence that comes from wearing something <strong>made with genuine care</strong>. Whether you're reaching for a barely-there tinted moisturizer or a bold, statement lip, our collection is designed to move with your life: long-wearing, comfortable, and effortlessly, beautifully you.
              </p>
            </div>

            <div class="about-cta reveal reveal-delay-4">
              <a href="#shop" class="btn btn-primary">Explore Products</a>
              <div class="about-signature" aria-hidden="true">
                Joana B.<br />
                <span style="font-size:0.9rem; color: var(--clr-text-light);">Founder &amp; Creative Director</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <section id="shop" aria-labelledby="shop-title">
      <div class="container">

        <div class="shop-header">
          <div class="shop-header-text reveal">
            <span class="section-label">Shop by Category</span>
            <h2 class="section-title" id="shop-title">
              Find your<br />perfect match.
            </h2>
          </div>
          <a href="shop.php" class="btn btn-ghost reveal reveal-delay-1">View All Products</a>
        </div>

        <div class="categories-grid" role="list">

          <a href="shop.php" class="category-card reveal reveal-delay-1" role="listitem" aria-label="Browse Face products">
            <div class="cat-img-wrap">
              <div class="cat-img-inner cat-face">
                <img src="assets/img/face.jpg" alt="Woman applying smooth foundation to her face">
              </div>
              <div class="cat-overlay" aria-hidden="true"></div>
              <span class="cat-tag" aria-hidden="true">Bestsellers</span>
              <span class="cat-cta" aria-hidden="true">Shop Face →</span>
            </div>
            <div class="cat-body">
              <p class="cat-name">Face</p>
              <p class="cat-desc">Foundations, concealers &amp; setting powders for a flawless, natural-looking finish.</p>
              <span class="cat-count">48 Products</span>
            </div>
          </a>

          <a href="shop.php" class="category-card reveal reveal-delay-2" role="listitem" aria-label="Browse Eyes products">
            <div class="cat-img-wrap">
              <div class="cat-img-inner cat-eyes">
                <img src="assets/img/eyes.jpg" alt="Close up of eye makeup and mascara wands">
              </div>
              <div class="cat-overlay" aria-hidden="true"></div>
              <span class="cat-tag" aria-hidden="true">New Arrivals</span>
              <span class="cat-cta" aria-hidden="true">Shop Eyes →</span>
            </div>
            <div class="cat-body">
              <p class="cat-name">Eyes</p>
              <p class="cat-desc">Eyeshadow palettes, mascaras &amp; liners that define and mesmerize.</p>
              <span class="cat-count">36 Products</span>
            </div>
          </a>

          <a href="shop.php" class="category-card reveal reveal-delay-3" role="listitem" aria-label="Browse Lips products">
            <div class="cat-img-wrap">
              <div class="cat-img-inner cat-lips">
                <img src="assets/img/lips.jpg" alt="Various shades of nude and bold lipsticks">
              </div>
              <div class="cat-overlay" aria-hidden="true"></div>
              <span class="cat-tag" aria-hidden="true">Fan Favorites</span>
              <span class="cat-cta" aria-hidden="true">Shop Lips →</span>
            </div>
            <div class="cat-body">
              <p class="cat-name">Lips</p>
              <p class="cat-desc">Hydrating lipsticks, glosses &amp; liners in a spectrum of bold and nude tones.</p>
              <span class="cat-count">52 Products</span>
            </div>
          </a>

          <a href="shop.php" class="category-card reveal reveal-delay-4" role="listitem" aria-label="Browse Skincare products">
            <div class="cat-img-wrap">
              <div class="cat-img-inner cat-skin">
                <img src="assets/img/skincare.jpg" alt="Glass dropper bottles of skincare serums">
              </div>
              <div class="cat-overlay" aria-hidden="true"></div>
              <span class="cat-tag" aria-hidden="true">Dermatologist Pick</span>
              <span class="cat-cta" aria-hidden="true">Shop Skincare →</span>
            </div>
            <div class="cat-body">
              <p class="cat-name">Skincare</p>
              <p class="cat-desc">Serums, moisturizers &amp; SPF essentials formulated to nourish every skin type.</p>
              <span class="cat-count">29 Products</span>
            </div>
          </a>

        </div>
      </div>
    </section>

    <section class="quote-section" aria-label="Customer testimonials">
      <div class="container">
        <div class="quote-mark" aria-hidden="true">"</div>

        <div id="quoteContent">
          <blockquote class="quote-text" id="quoteText">
            The JBeauty foundation is the first one I've ever tried that actually matches my skin tone. I've been searching for years. This brand truly sees every woman.
          </blockquote>
          <div class="quote-stars" aria-label="5 stars out of 5">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          </div>
          <p class="quote-author" id="quoteAuthor">— Maria C., Verified Buyer</p>
        </div>

        <nav class="quote-nav" aria-label="Testimonial navigation">
          <button class="quote-dot active" aria-label="Testimonial 1" data-index="0"></button>
          <button class="quote-dot" aria-label="Testimonial 2" data-index="1"></button>
          <button class="quote-dot" aria-label="Testimonial 3" data-index="2"></button>
        </nav>
      </div>
    </section>

  </main>

  <footer id="contact" role="contentinfo" aria-label="Site footer">
    <div class="container">

      <div class="footer-top">

        <div class="footer-brand">
          <div class="nav-logo-mark" aria-hidden="true">J</div>
          <p class="nav-logo-text">JBeauty</p>
          <p class="footer-brand-desc">
            Premium, inclusive cosmetics crafted for every skin tone. Beauty without compromise, luxury without exclusion.
          </p>
          <nav aria-label="Social media links">
            <div class="footer-social">
              <a href="#" class="social-btn" aria-label="Follow JBeauty on Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
              </a>
              <a href="#" class="social-btn" aria-label="Follow JBeauty on Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
              </a>
              <a href="#" class="social-btn" aria-label="Follow JBeauty on TikTok">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
              </a>
              <a href="#" class="social-btn" aria-label="Follow JBeauty on Pinterest">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.65 7.86 6.39 9.29-.09-.78-.17-1.98.03-2.83.19-.77 1.27-5.38 1.27-5.38s-.32-.65-.32-1.61c0-1.51.88-2.64 1.97-2.64.93 0 1.38.7 1.38 1.53 0 .93-.6 2.34-.9 3.63-.26 1.09.53 1.97 1.6 1.97 1.91 0 3.19-2.46 3.19-5.37 0-2.21-1.48-3.87-4.16-3.87-3.03 0-4.92 2.26-4.92 4.78 0 .87.26 1.47.66 1.94.18.22.21.3.14.55-.05.18-.16.61-.2.78-.06.25-.26.34-.47.25-1.32-.54-1.94-2-1.94-3.63 0-2.69 2.27-5.92 6.77-5.92 3.62 0 5.99 2.63 5.99 5.46 0 3.74-2.07 6.54-5.12 6.54-1.02 0-1.98-.55-2.31-1.18l-.64 2.49c-.23.88-.85 1.97-1.27 2.64.96.29 1.97.45 3.01.45 5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
              </a>
            </div>
          </nav>
        </div>

        <div class="footer-col">
          <h4>Navigate</h4>
          <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="#">Blog</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Help</h4>
          <ul>
            <li><a href="#">Shade Finder</a></li>
            <li><a href="#">Track My Order</a></li>
            <li><a href="#">Returns &amp; Exchanges</a></li>
            <li><a href="#">Shipping Info</a></li>
            <li><a href="#">FAQ</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <div class="footer-newsletter">
            <h4>Stay in the loop</h4>
            <p>Be the first to hear about new launches, exclusive offers &amp; beauty tips.</p>
            <form class="newsletter-form" onsubmit="handleNewsletter(event)" aria-label="Email newsletter signup">
              <label for="newsletterEmail" class="sr-only">Email address</label>
              <input
                type="email"
                id="newsletterEmail"
                class="newsletter-input"
                placeholder="your@email.com"
                required
                aria-required="true"
                autocomplete="email"
              />
              <button type="submit" class="newsletter-btn" aria-label="Subscribe to newsletter">
                Subscribe
              </button>
            </form>
          </div>
        </div>

      </div>

      <div class="footer-bottom">
        <p class="footer-copy">
          &copy; <span id="year"></span> JBeauty. All rights reserved. Made with love &amp; purpose.
        </p>
        <nav class="footer-legal" aria-label="Legal links">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Cookie Policy</a>
        </nav>
      </div>

    </div>
  </footer>

  <script src="assets/js/home.js"></script>
</body>
</html>