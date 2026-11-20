// Build: pre-render all pages with ReactDOMServer, bundle the Studio SPA,
// and assemble dist/ — the exact content of public_html on the cPanel server.
//
//   dist/
//     index.html, <slug>/index.html   (17 public pages + login + studio)
//     404.html, sitemap.xml, robots.txt, llms.txt, .htaccess
//     assets/                         (css, js, img, brand files)
//     api/                            (PHP endpoints + schema.sql)
//     studio/app.js                   (Studio SPA bundle)
import { build as esbuild } from 'esbuild';
import { renderToStaticMarkup } from 'react-dom/server';
import React from 'react';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { SITE_URL, SITEMAP_PATHS, LLMS_TXT } from './site.config.js';

const root = path.dirname(fileURLToPath(import.meta.url)) + '/..';
const dist = path.join(root, 'dist');
const tmpDir = path.join(root, '.build');

function outPath(pagePath) {
  if (pagePath === '/') return path.join(dist, 'index.html');
  if (pagePath.endsWith('.html')) return path.join(dist, pagePath.slice(1));
  return path.join(dist, pagePath.slice(1), 'index.html');
}

function copyDir(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
    const s = path.join(src, entry.name);
    const d = path.join(dest, entry.name);
    if (entry.isDirectory()) copyDir(s, d);
    else fs.copyFileSync(s, d);
  }
}

function countFiles(dir) {
  let n = 0;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    n += entry.isDirectory() ? countFiles(path.join(dir, entry.name)) : 1;
  }
  return n;
}

async function main() {
  fs.rmSync(dist, { recursive: true, force: true });
  fs.rmSync(tmpDir, { recursive: true, force: true });
  fs.mkdirSync(dist, { recursive: true });
  fs.mkdirSync(tmpDir, { recursive: true });

  // 1. Bundle the page components for server-side rendering.
  const renderBundle = path.join(tmpDir, 'render.cjs');
  await esbuild({
    entryPoints: [path.join(root, 'src/render-entry.jsx')],
    bundle: true,
    platform: 'node',
    format: 'cjs',
    outfile: renderBundle,
    jsx: 'automatic',
    logLevel: 'warning',
  });
  const { pages } = await import(pathToFileURL(renderBundle).href);

  // 2. Pre-render every page.
  for (const { meta, Component } of pages) {
    const html = '<!doctype html>' + renderToStaticMarkup(React.createElement(Component));
    const dest = outPath(meta.path);
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.writeFileSync(dest, html + '\n');
    console.log(`rendered ${meta.path} -> ${path.relative(root, dest)} (${html.length} bytes)`);
  }

  // 3. Bundle the Studio SPA.
  await esbuild({
    entryPoints: [path.join(root, 'src/studio/index.jsx')],
    bundle: true,
    minify: true,
    outfile: path.join(dist, 'studio/app.js'),
    jsx: 'automatic',
    logLevel: 'warning',
  });
  console.log('bundled studio/app.js');

  // 4. Static assets, PHP API, schema, .htaccess.
  copyDir(path.join(root, 'public-assets'), path.join(dist, 'assets'));
  copyDir(path.join(root, 'api'), path.join(dist, 'api'));
  fs.copyFileSync(path.join(root, 'db/schema.sql'), path.join(dist, 'api/schema.sql'));
  fs.copyFileSync(path.join(root, '.htaccess'), path.join(dist, '.htaccess'));
  console.log(`copied assets (${countFiles(path.join(dist, 'assets'))} files), api (${countFiles(path.join(dist, 'api'))} files)`);

  // 5. sitemap.xml, robots.txt, llms.txt.
  const lastmod = new Date().toISOString().slice(0, 10);
  const sitemap = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
    ...SITEMAP_PATHS.map(
      (p) => `  <url><loc>${SITE_URL}${p}</loc><lastmod>${lastmod}</lastmod></url>`,
    ),
    '</urlset>',
    '',
  ].join('\n');
  fs.writeFileSync(path.join(dist, 'sitemap.xml'), sitemap);
  fs.writeFileSync(
    path.join(dist, 'robots.txt'),
    'User-agent: *\nDisallow: /studio/\nDisallow: /login/\nDisallow: /api/\n\nSitemap: https://nitiksham.com/sitemap.xml\n',
  );
  fs.writeFileSync(path.join(dist, 'llms.txt'), LLMS_TXT);
  console.log(`wrote sitemap.xml (${SITEMAP_PATHS.length} urls), robots.txt, llms.txt`);

  fs.rmSync(tmpDir, { recursive: true, force: true });
  console.log(`\nBuild complete: ${countFiles(dist)} files in dist/`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
