/* Site Kit — frontend interactions (no framework, no build step)
   Sticky header, mobile navigation, scroll reveal, count-up, filter tabs,
   external-link decoration, back-to-top. */
(function () {
  'use strict';

  var d = document;
  var $ = function (sel, ctx) { return (ctx || d).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || d).querySelectorAll(sel)); };
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Sticky header ---------- */
  var header = $('.sk-header');
  if (header) {
    // Hide only after a deliberate scroll distance in one direction; tiny
    // counter-movements from momentum scrolling would make it flicker.
    var HIDE_AFTER = 30;   // px scrolled down in one go
    var SHOW_AFTER = 90;   // px scrolled up in one go
    var TOP_ZONE = 160;    // always visible near the top
    var MIN_GAP = 400;     // ms between two state switches
    var lastY = Math.max(0, window.scrollY);
    var travel = 0;
    var lastSwitch = 0;
    var ticking = false;
    var totop = $('.sk-totop');

    var setHidden = function (hide) {
      if (header.classList.contains('is-hidden') === hide) return;
      var now = Date.now();
      if (now - lastSwitch < MIN_GAP) return;
      header.classList.toggle('is-hidden', hide);
      lastSwitch = now;
    };

    var update = function () {
      ticking = false;
      var maxY = Math.max(0, d.documentElement.scrollHeight - window.innerHeight);
      var y = Math.min(Math.max(0, window.scrollY), maxY);
      var dy = y - lastY;
      lastY = y;

      header.classList.toggle('is-scrolled', y > 12);
      if (totop) totop.classList.toggle('is-visible', y > 640);

      if (y <= TOP_ZONE || $('.sk-mnav.is-open') || header.contains(d.activeElement)) {
        travel = 0;
        lastSwitch = 0;
        header.classList.remove('is-hidden');
        return;
      }
      if (dy === 0) return;
      travel = (dy > 0) === (travel > 0) ? travel + dy : dy;
      if (travel > HIDE_AFTER) setHidden(true);
      else if (travel < -SHOW_AFTER) setHidden(false);
    };

    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
    }, { passive: true });
    update();
  }

  /* ---------- Mobile navigation ---------- */
  var burger = $('.sk-burger');
  var mnav = $('.sk-mnav');
  if (burger && mnav) {
    var panel = $('.sk-mnav__panel', mnav);
    var closeNav = function () {
      if (!mnav.classList.contains('is-open')) return;
      mnav.classList.remove('is-open');
      mnav.setAttribute('aria-hidden', 'true');
      burger.setAttribute('aria-expanded', 'false');
      d.body.style.overflow = '';
      burger.focus();
    };
    burger.addEventListener('click', function () {
      mnav.classList.add('is-open');
      mnav.setAttribute('aria-hidden', 'false');
      burger.setAttribute('aria-expanded', 'true');
      d.body.style.overflow = 'hidden';
      var first = $('.sk-mnav__close', mnav);
      if (first) setTimeout(function () { first.focus(); }, 60);
    });
    $('.sk-mnav__close', mnav).addEventListener('click', closeNav);
    if (panel) {
      // Close on backdrop click (the container itself, not the panel).
      mnav.addEventListener('click', function (e) { if (e.target === mnav) closeNav(); });
    }
    d.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeNav(); });
  }

  /* ---------- Scroll reveal ---------- */
  var revealEls = $$('.reveal');
  if ('IntersectionObserver' in window && revealEls.length && !reduceMotion) {
    var ro = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-in'); ro.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    revealEls.forEach(function (el) { ro.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-in'); });
  }

  /* ---------- Count-up numbers ---------- */
  var counters = $$('[data-count]');
  if (reduceMotion) counters = [];
  var runCount = function (el) {
    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
    var dur = 1600, t0 = null;
    var step = function (t) {
      if (!t0) t0 = t;
      var p = Math.min((t - t0) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased).toString();
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };
  if ('IntersectionObserver' in window && counters.length) {
    var co = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { runCount(en.target); co.unobserve(en.target); }
      });
    }, { threshold: 0.6 });
    counters.forEach(function (el) { co.observe(el); });
  } else {
    counters.forEach(runCount);
  }

  /* ---------- Filter tabs ---------- */
  $$('[data-filter]').forEach(function (bar) {
    var containerId = bar.getAttribute('data-filter-for');
    var items = containerId ? $$('[data-categories]', $('#' + containerId)) : [];
    if (!items.length) return;

    bar.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-filter-val]');
      if (!btn) return;
      $$('[data-filter-val]', bar).forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var filter = btn.getAttribute('data-filter-val');
      items.forEach(function (item) {
        var cats = (item.getAttribute('data-categories') || '').split(',').map(function (s) { return s.trim(); });
        item.hidden = !(filter === '*' || cats.indexOf(filter) !== -1);
      });
    });
  });

  /* ---------- Detail elements: only one open at a time (FAQ) ---------- */
  $$('.sk-faq__item').forEach(function (item) {
    item.addEventListener('toggle', function () {
      if (!item.open) return;
      $$('.sk-faq__item[open]').forEach(function (other) {
        if (other !== item) other.open = false;
      });
    });
  });

  /* ---------- Forms ---------- */
  $$('form.sk-form').forEach(function (form) {
    // Prevent accidental double submissions; the page reloads via redirect,
    // so a failed submit re-enables the button automatically.
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.dataset.orig = btn.dataset.orig || btn.textContent;
        btn.textContent = btn.dataset.sending || '……';
      }
    });

    // Autofill magazine-style date fields is noisy; help the user a little.
    var formT = form.querySelector('input[name="sk_t"]');
    if (formT && !formT.value) formT.value = Math.floor(Date.now() / 1000);
  });

  /* ---------- External links ---------- */
  $$('.sk-prose a[href^="http"]').forEach(function (a) {
    if (a.hostname && a.hostname !== window.location.hostname) {
      a.setAttribute('target', '_blank');
      a.setAttribute('rel', 'noopener');
    }
  });

  /* ---------- Smooth scroll for anchors ---------- */
  if (!reduceMotion) {
    $$('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var id = a.getAttribute('href');
        if (id.length < 2) return;
        var target = d.querySelector(id);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          target.setAttribute('tabindex', '-1');
          target.focus({ preventScroll: true });
        }
      });
    });
  }

  /* ---------- Back to top ---------- */
  var totopBtn = $('.sk-totop');
  if (totopBtn) {
    totopBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }
})();
