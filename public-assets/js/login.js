/* Studio login page: email + password form -> PHP session login. */
(function () {
  'use strict';
  var form = document.querySelector('#login-form');
  if (!form) return;
  var statusEl = document.querySelector('#login-status');
  var submitBtn = form.querySelector('button[type="submit"]');

  function setStatus(msg) { if (statusEl) statusEl.textContent = msg; }

  /* The login endpoint requires the CSRF token issued by /api/session.php. */
  var csrfToken = null;
  function fetchSession() {
    return fetch('/api/session.php', { credentials: 'include' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (s) { if (s && s.csrf) csrfToken = s.csrf; return s; });
  }

  /* Already signed in? Go straight to the studio. */
  fetchSession()
    .then(function (s) {
      if (s && s.authenticated && s.owner) window.location.replace('/studio/');
    })
    .catch(function () { /* stay on the login page */ });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!form.reportValidity()) return;
    if (csrfToken) { sendLogin(); return; }
    fetchSession().then(sendLogin).catch(sendLogin);
  });

  function sendLogin() {
    if (submitBtn) submitBtn.disabled = true;
    setStatus('Signing you in…');
    fetch('/api/login.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken || ''
      },
      credentials: 'include',
      body: JSON.stringify({
        email: form.elements.email.value,
        password: form.elements.password.value
      })
    })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
      .then(function (res) {
        if (res.ok) {
          window.location.replace('/studio/');
        } else {
          setStatus(res.body && res.body.error ? res.body.error : 'Sign-in failed. Please try again.');
          if (submitBtn) submitBtn.disabled = false;
        }
      })
      .catch(function () {
        setStatus('Sign-in is unavailable right now. Please try again in a moment.');
        if (submitBtn) submitBtn.disabled = false;
      });
  }
})();
