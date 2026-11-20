/* Software studio (software page): loads published software from the PHP API.
   Adapted from the WordPress software-public.js: apiBase is now /api. */
(function () {
  'use strict';
  fetch('/api/software.php')
    .then(function (r) { return r.ok ? r.json() : []; })
    .then(function (items) {
      var grid = document.querySelector('#software-public');
      var feature = document.querySelector('.software-feature');
      var featured = items.find(function (x) { return x.id === 'stockdesk'; });
      if (feature) {
        feature.hidden = !featured;
        if (featured) {
          var title = feature.querySelector('div:last-child h2');
          var desc = feature.querySelector('div:last-child p');
          if (title) title.textContent = featured.title;
          if (desc) desc.textContent = featured.description;
        }
      }
      if (!grid) return;
      items.filter(function (x) { return x.id !== 'stockdesk'; }).forEach(function (x) {
        var card = document.createElement('article');
        card.className = 'managed-card';
        var h = document.createElement('h3');
        h.textContent = x.title;
        var p = document.createElement('p');
        p.textContent = x.description;
        var a = document.createElement('a');
        a.href = '/contact-us/?interest=' + encodeURIComponent(x.title + ' demo');
        a.textContent = 'Request a walkthrough ↗';
        card.append(h, p, a);
        grid.append(card);
      });
    })
    .catch(function () { /* keep the static page content if the API is unreachable */ });
})();
