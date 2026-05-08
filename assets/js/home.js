/* ---------- Dynamic year ---------- */
document.getElementById('year').textContent = new Date().getFullYear();

/* ---------- Sticky header ---------- */
const header = document.getElementById('header');
let lastScroll = 0;
window.addEventListener('scroll', () => {
  const y = window.scrollY;
  header.classList.toggle('scrolled', y > 20);
  lastScroll = y;
}, { passive: true });

/* ---------- Mobile menu ---------- */
const hamburger = document.getElementById('hamburgerBtn');
const mobileMenu = document.getElementById('mobileMenu');

function toggleMenu(open) {
  hamburger.classList.toggle('open', open);
  mobileMenu.classList.toggle('open', open);
  hamburger.setAttribute('aria-expanded', String(open));
  mobileMenu.setAttribute('aria-hidden', String(!open));
}

hamburger.addEventListener('click', () => {
  const isOpen = mobileMenu.classList.contains('open');
  toggleMenu(!isOpen);
});

// Close on link click
mobileMenu.querySelectorAll('a').forEach(link => {
  link.addEventListener('click', () => toggleMenu(false));
});

// Close on outside click
document.addEventListener('click', (e) => {
  if (!header.contains(e.target)) toggleMenu(false);
});

// Close on Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') toggleMenu(false);
});

/* ---------- Scroll reveal ---------- */
const reveals = document.querySelectorAll('.reveal');
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

reveals.forEach(el => revealObserver.observe(el));

/* ---------- Testimonial rotator ---------- */
const testimonials = [
  {
    quote: "The JBeauty foundation is the first one I've ever tried that actually matches my skin tone. I've been searching for years. This brand truly sees every woman.",
    author: "— Maria C., Verified Buyer"
  },
  {
    quote: "I love that JBeauty's skincare line doesn't irritate my sensitive skin. The ingredients are thoughtful, the packaging is beautiful, and the results are real.",
    author: "— Priya S., Verified Buyer"
  },
  {
    quote: "Finally a luxury brand that doesn't make me feel like an outsider. The shade range is extraordinary — I found my perfect match on the very first try.",
    author: "— Angela T., Verified Buyer"
  }
];

let currentQuote = 0;
const quoteText    = document.getElementById('quoteText');
const quoteAuthor  = document.getElementById('quoteAuthor');
const quoteDots    = document.querySelectorAll('.quote-dot');

function showQuote(index) {
  quoteText.style.opacity   = '0';
  quoteAuthor.style.opacity = '0';
  quoteText.style.transform = 'translateY(12px)';

  setTimeout(() => {
    quoteText.textContent   = testimonials[index].quote;
    quoteAuthor.textContent = testimonials[index].author;
    quoteText.style.transition   = 'opacity 0.5s, transform 0.5s';
    quoteAuthor.style.transition = 'opacity 0.5s';
    quoteText.style.opacity   = '1';
    quoteAuthor.style.opacity = '1';
    quoteText.style.transform = 'translateY(0)';
  }, 280);

  quoteDots.forEach((dot, i) => {
    dot.classList.toggle('active', i === index);
  });

  currentQuote = index;
}

quoteDots.forEach(dot => {
  dot.addEventListener('click', () => showQuote(Number(dot.dataset.index)));
});

// Auto-rotate every 5 seconds
let quoteTimer = setInterval(() => {
  showQuote((currentQuote + 1) % testimonials.length);
}, 5000);

// Pause on interaction
document.querySelector('.quote-section').addEventListener('mouseenter', () => clearInterval(quoteTimer));
document.querySelector('.quote-section').addEventListener('mouseleave', () => {
  quoteTimer = setInterval(() => showQuote((currentQuote + 1) % testimonials.length), 5000);
});

/* ---------- Newsletter ---------- */
function handleNewsletter(e) {
  e.preventDefault();
  const input = document.getElementById('newsletterEmail');
  const btn = e.target.querySelector('button');
  const original = btn.textContent;
  btn.textContent = '✓ Subscribed!';
  btn.style.background = '#6A8F70';
  input.value = '';
  setTimeout(() => {
    btn.textContent = original;
    btn.style.background = '';
  }, 3000);
}