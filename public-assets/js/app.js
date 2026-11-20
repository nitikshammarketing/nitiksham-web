/* Nitiksham public site JS: gallery filter + lightbox, contact enquiry form.
   Rebuilt version: the enquiry form now submits to the PHP API (/api/contact.php)
   which stores the enquiry and emails the studio. The old WhatsApp submit was
   removed. The mailto "prepare an email" option is kept as a secondary channel. */
(function () {
  'use strict';

  /* --- Editorial gallery filter (gallery page, static grid) --- */
  document.querySelectorAll('[data-filter]').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('[data-filter]').forEach(function (x) { x.classList.remove('active'); });
      b.classList.add('active');
      document.querySelectorAll('.gallery-item').forEach(function (x) {
        x.hidden = b.dataset.filter !== 'all' && x.dataset.category !== b.dataset.filter;
      });
    });
  });

  /* --- Lightbox (gallery page, static grid) --- */
  var box = document.querySelector('#lightbox');
  if (box) {
    document.querySelectorAll('[data-image]').forEach(function (b) {
      b.addEventListener('click', function () {
        box.querySelector('img').src = b.dataset.image;
        box.querySelector('img').alt = b.dataset.title;
        box.querySelector('p').textContent = b.dataset.title;
        box.showModal();
      });
    });
    var closeBtn = box.querySelector('.close');
    if (closeBtn) closeBtn.addEventListener('click', function () { box.close(); });
    box.addEventListener('click', function (e) { if (e.target === box) box.close(); });
  }

  /* --- Enquiry form (contact page) --- */
  var form = document.querySelector('#enquiry');
  if (!form) return;

  var params = new URLSearchParams(location.search);
  var interest = params.get('interest');
  if (interest && form.elements.message) {
    form.elements.message.value = 'I would like to arrange a walkthrough of ' + interest + '.';
  }
  var service = params.get('service');
  if (service && form.elements.service) form.elements.service.value = service;

  var statusEl = document.querySelector('#form-status');
  function setStatus(msg) { if (statusEl) statusEl.textContent = msg; }

  /* The contact endpoint requires the CSRF token issued by /api/session.php
     (the session cookie must travel with both requests). */
  var csrfToken = null;
  var tokenPromise = fetch('/api/session.php', { credentials: 'include' })
    .then(function (r) { return r.ok ? r.json() : null; })
    .then(function (s) { if (s && s.csrf) csrfToken = s.csrf; })
    .catch(function () { /* token fetch retried at submit time */ });
  function withToken(fn) {
    if (csrfToken) { fn(); return; }
    tokenPromise.then(function () {
      if (csrfToken) { fn(); return; }
      fetch('/api/session.php', { credentials: 'include' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (s) { if (s && s.csrf) csrfToken = s.csrf; fn(); })
        .catch(fn);
    });
  }

  function prepareText() {
    var d = new FormData(form);
    var sel = form.elements.service;
    return 'Hello Nitiksham, I would like to discuss a project.\n\n'
      + 'Name: ' + d.get('name') + '\n'
      + 'Email: ' + d.get('email') + '\n'
      + 'Company: ' + (d.get('company') || '') + '\n'
      + 'Phone: ' + (d.get('phone') || '') + '\n'
      + 'Service: ' + (sel && sel.value ? sel.options[sel.selectedIndex].text : 'To discuss') + '\n\n'
      + (d.get('message') || '');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!form.reportValidity()) return;
    withToken(function () { sendEnquiry(); });
  });

  function sendEnquiry() {
    var d = new FormData(form);
    var payload = {
      name: d.get('name') || '',
      email: d.get('email') || '',
      company: d.get('company') || '',
      phone: d.get('phone') || '',
      service: d.get('service') || '',
      message: d.get('message') || '',
      website: d.get('website') || '' /* honeypot: must stay empty */
    };
    var submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;
    setStatus('Sending your enquiry…');
    fetch('/api/contact.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken || ''
      },
      credentials: 'include',
      body: JSON.stringify(payload)
    }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
      .then(function (res) {
        if (res.ok) {
          setStatus('Thank you — your enquiry has been sent. We will reply to your email address soon.');
          form.reset();
        } else {
          setStatus(res.body && res.body.error ? res.body.error : 'Something went wrong. Please try again, or use the email option below.');
        }
      })
      .catch(function () {
        setStatus('Your enquiry could not be sent. Please check your connection and try again, or use the email option below.');
      })
      .finally(function () { if (submitBtn) submitBtn.disabled = false; });
  }

  var emailBtn = form.querySelector('.email-send');
  if (emailBtn) {
    emailBtn.addEventListener('click', function () {
      if (!form.reportValidity()) return;
      location.href = 'mailto:contact@nitiksham.com?subject='
        + encodeURIComponent('New project enquiry — ' + form.elements.name.value)
        + '&body=' + encodeURIComponent(prepareText());
      setStatus('Your email application will open with a draft. Review it and send to complete your enquiry.');
    });
  }
})();
