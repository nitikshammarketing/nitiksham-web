#!/usr/bin/env python3
from pathlib import Path
from html.parser import HTMLParser
from subprocess import run
import sys
root=Path(__file__).resolve().parent.parent
public=root/'public_html'
routes=['/','/home/','/about-us/','/contact-us/','/copyright/','/digital-marketing/','/expertise/',
'/expertise-seo/','/expertise-web-development/','/gallery/','/login/','/print/','/privacy/',
'/services/','/social-media-marketing/','/software/','/studio/','/team/']
class Assets(HTMLParser):
 def __init__(self):super().__init__();self.links=[]
 def handle_starttag(self,tag,attrs):
  a=dict(attrs)
  if tag in ('img','script') and a.get('src'):self.links.append(a['src'])
  if tag=='link' and a.get('href') and a.get('rel') in (None,'stylesheet','icon'):self.links.append(a['href'])
count=0
for route in routes:
 file=public/route.lstrip('/')/('index.php' if route in ('/studio/','/login/') else 'index.html')
 assert file.exists(),f'Missing page {file}'
 s=file.read_text()
 assert 'nitiksham-brand.png' in s,route
 assert 'WordPress' not in s,route
 if file.suffix=='.html':
  assert '<title>' in s,route
  p=Assets();p.feed(s)
  for path in p.links:
   if path.startswith('/') and not path.startswith('/api/'):
    target=public/path.split('?')[0].lstrip('/')
    assert target.is_file(),f'Missing asset {route}: {path}'
 count+=1
for f in root.rglob('*.php'):
 r=run(['php','-l',str(f)],capture_output=True,text=True)
 assert r.returncode==0,r.stderr
 count+=1
for f in public.glob('*.js'):
 r=run(['node','--check',str(f)],capture_output=True,text=True)
 assert r.returncode==0,r.stderr
 count+=1
assert not (root/'private/config.php').exists(),'Do not commit production credentials'
assert (root/'private/schema.sql').is_file()
print(f'PASS: {count} source checks; no deployment performed')