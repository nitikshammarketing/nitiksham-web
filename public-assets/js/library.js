/* Studio journal (gallery page): loads published designs & newsletters from the
   PHP API and renders them as cards. Adapted from the WordPress library.js:
   apiBase is now /api and files come from /api/file.php?id=. */
(function () {
  'use strict';
  var grid = document.querySelector('#gallery-grid');
  var status = document.querySelector('#gallery-status');
  if (!grid || !status) return;

  var fileUrl = function (id) { return '/api/file.php?id=' + encodeURIComponent(id); };

  var items = [];
  var kind = 'all';

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = text;
    return n;
  }

  function render() {
    grid.replaceChildren();
    var list = items.filter(function (x) { return kind === 'all' || x.kind === kind; });
    status.textContent = list.length
      ? ''
      : (items.length ? 'No pieces in this category yet.' : 'The journal is taking shape. New designs and newsletters will appear here when published.');
    status.className = list.length ? '' : 'empty-state';
    list.forEach(function (x) {
      var card = el('article', 'library-item');
      var link = el('a');
      link.href = fileUrl(x.id);
      link.target = '_blank';
      link.rel = 'noopener';
      if (x.mime && x.mime.indexOf('image/') === 0) {
        var img = el('img');
        img.src = link.href;
        img.alt = x.title;
        img.loading = 'lazy';
        link.append(img);
      } else {
        link.append(el('div', 'pdf-cover', 'PDF / Journal'));
      }
      var copy = el('div', 'item-copy');
      copy.append(
        el('small', null, x.kind === 'newsletter' ? 'NEWSLETTER' : 'DESIGN'),
        el('h3', null, x.title),
        el('p', null, x.description || ''),
        el('span', null, x.mime === 'application/pdf' ? 'Download PDF ↗' : 'View design ↗')
      );
      link.append(copy);
      card.append(link);
      grid.append(card);
    });
  }

  document.querySelectorAll('[data-kind]').forEach(function (b) {
    b.addEventListener('click', function () {
      kind = b.dataset.kind;
      document.querySelectorAll('[data-kind]').forEach(function (n) { n.classList.toggle('active', n === b); });
      render();
    });
  });

  fetch('/api/gallery.php')
    .then(function (r) { if (!r.ok) throw new Error('gallery failed'); return r.json(); })
    .then(function (data) { items = data; render(); })
    .catch(function () { status.textContent = 'The gallery could not load. Please refresh to try again.'; });
})();
