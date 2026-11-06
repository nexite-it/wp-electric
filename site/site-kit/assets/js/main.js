/* Site Kit — frontend interactions (no framework, no build step)
   Sticky header, mobile navigation, live office status, scroll reveal,
   count-up, service sub-navigation, inquiry form helpers, click-to-load map,
   filter tabs, external links, back-to-top. */
(function () {
  'use strict';

  var d = document;
  var $ = function (sel, ctx) { return (ctx || d).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || d).querySelectorAll(sel)); };
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var lang = d.documentElement.lang || 'de-DE';

  /* ---------- Sticky header ----------
     The header carries the emergency number, so it stays visible. Add
     data-autohide to the <header> to get the hide-on-scroll behaviour back. */
  var header = $('.sk-header');
  var totop = $('.sk-totop');
  if (header) {
    var autohide = header.hasAttribute('data-autohide');
    var lastY = Math.max(0, window.scrollY);
    var travel = 0;
    var ticking = false;

    var update = function () {
      ticking = false;
      var y = Math.max(0, window.scrollY);
      var dy = y - lastY;
      lastY = y;

      header.classList.toggle('is-scrolled', y > 12);
      if (totop) totop.classList.toggle('is-visible', y > 640);

      if (!autohide) return;
      if (y <= 160 || $('.sk-mnav.is-open') || header.contains(d.activeElement)) {
        travel = 0;
        header.classList.remove('is-hidden');
        return;
      }
      travel = (dy > 0) === (travel > 0) ? travel + dy : dy;
      if (travel > 30) header.classList.add('is-hidden');
      else if (travel < -90) header.classList.remove('is-hidden');
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
    mnav.addEventListener('click', function (e) { if (e.target === mnav) closeNav(); });
    $$('a', mnav).forEach(function (a) { a.addEventListener('click', closeNav); });
    d.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeNav(); });
  }

  /* ---------- Live office status ("jetzt geöffnet") ----------
     Reads the opening hours printed by the header (#sk-hours) and evaluates
     them in the business's time zone, not the visitor's. */
  var hoursEl = $('#sk-hours');
  var statusEls = $$('[data-open-status]');
  if (hoursEl && statusEls.length && window.Intl && Intl.DateTimeFormat) {
    var hours = null;
    try { hours = JSON.parse(hoursEl.textContent); } catch (e) { hours = null; }

    var DAYS = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'];
    var LABEL = { mo: 'Mo', tu: 'Di', we: 'Mi', th: 'Do', fr: 'Fr', sa: 'Sa', su: 'So' };
    var toMin = function (hhmm) { var p = hhmm.split(':'); return (+p[0]) * 60 + (+p[1]); };

    var nowIn = function (tz) {
      var parts = {};
      new Intl.DateTimeFormat('en-US', {
        timeZone: tz, weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
      }).formatToParts(new Date()).forEach(function (p) { parts[p.type] = p.value; });
      var day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(parts.weekday);
      return { day: day, min: (+parts.hour % 24) * 60 + (+parts.minute) };
    };

    var describe = function () {
      var now;
      try { now = nowIn(hours.tz || 'Europe/Berlin'); } catch (e) { return null; }
      if (now.day < 0) return null;

      var today = hours.week[DAYS[now.day]];
      if (today && now.min >= toMin(today[0]) && now.min < toMin(today[1])) {
        return { open: true, text: 'Jetzt geöffnet · bis ' + today[1] + ' Uhr' };
      }
      // Next opening: later today, or the next day with hours.
      for (var i = 0; i < 8; i++) {
        var key = DAYS[(now.day + i) % 7];
        var slot = hours.week[key];
        if (!slot) continue;
        if (i === 0 && now.min >= toMin(slot[0])) continue;
        var when = i === 0 ? 'heute' : (i === 1 ? 'morgen' : LABEL[key]);
        return { open: false, text: 'Geschlossen · öffnet ' + when + ' ' + slot[0] + ' Uhr' };
      }
      return { open: false, text: 'Geschlossen' };
    };

    var paint = function () {
      var state = hours && hours.week ? describe() : null;
      if (!state) return;
      statusEls.forEach(function (el) {
        el.setAttribute('data-state', state.open ? 'open' : 'closed');
        var text = $('[data-open-text]', el);
        if (text) text.textContent = state.text;
      });
    };
    paint();
    setInterval(paint, 60000);
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

  /* ---------- Count-up numbers (German grouping: 1.800) ---------- */
  var format = function (n) {
    try { return n.toLocaleString(lang); } catch (e) { return String(n); }
  };
  var counters = reduceMotion ? [] : $$('[data-count]');
  var runCount = function (el) {
    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
    var dur = 1400, t0 = null;
    var step = function (t) {
      if (!t0) t0 = t;
      var p = Math.min((t - t0) / dur, 1);
      el.textContent = format(Math.round(target * (1 - Math.pow(1 - p, 3))));
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
  }

  /* ---------- Service sub-navigation: highlight the section in view ---------- */
  var subLinks = $$('.sk-subnav__list a[href^="#"]');
  if (subLinks.length && 'IntersectionObserver' in window) {
    var byId = {};
    subLinks.forEach(function (a) { byId[a.getAttribute('href').slice(1)] = a; });
    var so = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        subLinks.forEach(function (a) { a.classList.remove('is-active'); });
        var link = byId[en.target.id];
        if (link) {
          link.classList.add('is-active');
          // Keep the active chip visible on narrow screens without moving the page.
          var list = link.closest('.sk-subnav__list');
          if (list) list.scrollLeft = link.offsetLeft - list.clientWidth / 2 + link.offsetWidth / 2;
        }
      });
    }, { rootMargin: '-40% 0px -55% 0px' });
    Object.keys(byId).forEach(function (id) {
      var section = d.getElementById(id);
      if (section) so.observe(section);
    });
  }

  /* ---------- Inquiry form ---------- */
  $$('form.sk-form').forEach(function (form) {
    // The message placeholder follows the chosen topic, so visitors know
    // which details help us quote quickly.
    var message = $('textarea[name="sk_message"]', form);
    $$('input[name="sk_topic"]', form).forEach(function (radio) {
      radio.addEventListener('change', function () {
        if (message && radio.checked && radio.getAttribute('data-hint')) {
          message.setAttribute('placeholder', radio.getAttribute('data-hint'));
        }
      });
    });

    // A callback needs a phone number.
    var callback = $('input[name="sk_callback"]', form);
    var phone = $('input[name="sk_phone"]', form);
    if (callback && phone) {
      callback.addEventListener('change', function () {
        phone.required = callback.checked;
        if (callback.checked && !phone.value) phone.focus();
      });
    }

    // Prevent double submissions; the page reloads via redirect.
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.textContent = form.getAttribute('data-sending') || '…';
      }
    });

    var formT = form.querySelector('input[name="sk_t"]');
    if (formT && !formT.value) formT.value = Math.floor(Date.now() / 1000);
  });

  /* ---------- Map: load OpenStreetMap only after a click ---------- */
  $$('[data-map-src]').forEach(function (box) {
    var btn = $('[data-map-load]', box);
    if (!btn) return;
    btn.addEventListener('click', function () {
      var frame = d.createElement('iframe');
      frame.src = box.getAttribute('data-map-src');
      frame.title = ($('title', box) || {}).textContent || 'Karte';
      frame.loading = 'lazy';
      frame.referrerPolicy = 'no-referrer';
      box.insertBefore(frame, box.firstChild);
      box.classList.add('is-loaded');
    });
  });

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

  /* ---------- FAQ: only one answer open at a time ---------- */
  $$('.sk-faq__item').forEach(function (item) {
    item.addEventListener('toggle', function () {
      if (!item.open) return;
      $$('.sk-faq__item[open]').forEach(function (other) {
        if (other !== item) other.open = false;
      });
    });
  });

  /* ---------- External links in editor content ---------- */
  $$('.sk-prose a[href^="http"]').forEach(function (a) {
    if (a.hostname && a.hostname !== window.location.hostname) {
      a.setAttribute('target', '_blank');
      a.setAttribute('rel', 'noopener');
    }
  });

  /* ---------- Smooth scroll for in-page anchors ---------- */
  if (!reduceMotion) {
    $$('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var id = a.getAttribute('href');
        if (id.length < 2) return;
        var target = d.getElementById(id.slice(1));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          if (history.replaceState) history.replaceState(null, '', id);
          // Move keyboard focus along (skip link, sub-navigation).
          if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
          target.focus({ preventScroll: true });
        }
      });
    });
  }

  /* ---------- Back to top ---------- */
  if (totop) {
    totop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }
})();
