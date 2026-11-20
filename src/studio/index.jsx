import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.jsx';

const rootEl = document.getElementById('studio-root');
if (rootEl) {
  createRoot(rootEl).render(<App />);
}
