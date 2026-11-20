import React, { useCallback, useEffect, useRef, useState } from 'react';
import { api } from './api.js';
import GalleryTab from './GalleryTab.jsx';
import SoftwareTab from './SoftwareTab.jsx';
import EnquiriesTab from './EnquiriesTab.jsx';

const TABS = [
  { id: 'uploads', label: 'Gallery & newsletters' },
  { id: 'tools', label: 'Software & demos' },
  { id: 'enquiries', label: 'Enquiries' },
];

// The list endpoints return either a bare array or an object wrapping one
// ({items: [...]} / {enquiries: [...]}). Accept both shapes.
function asArray(data, key) {
  if (Array.isArray(data)) return data;
  if (data && Array.isArray(data[key])) return data[key];
  return [];
}

export default function App() {
  const [ready, setReady] = useState(false);
  const [email, setEmail] = useState('');
  const [csrf, setCsrf] = useState('');
  const [items, setItems] = useState([]);
  const [activeTab, setActiveTab] = useState('uploads');
  const [message, setMessage] = useState({ text: '', error: false });
  const [undo, setUndo] = useState(null); // {id, title} of the last archived item
  const [presentation, setPresentation] = useState(false);
  const [enquiries, setEnquiries] = useState(null); // null = not loaded yet
  const [enquiriesLoading, setEnquiriesLoading] = useState(false);
  const undoTimer = useRef(null);
  const rootRef = useRef(null);

  const showMessage = useCallback((text, error = false) => {
    setMessage({ text: text || '', error: Boolean(error) });
  }, []);

  const refreshItems = useCallback(async () => {
    const data = await api.items();
    setItems(asArray(data, 'items'));
  }, []);

  const clearUndo = useCallback(() => {
    if (undoTimer.current) {
      clearTimeout(undoTimer.current);
      undoTimer.current = null;
    }
    setUndo(null);
  }, []);

  // Session gate: only the signed-in owner may use the studio.
  useEffect(() => {
    let cancelled = false;
    api
      .session()
      .then(async (session) => {
        if (cancelled) return;
        if (!session || !session.authenticated || !session.owner) {
          window.location.href = '/login/';
          return;
        }
        setEmail(session.email || '');
        setCsrf(session.csrf || '');
        setReady(true);
        try {
          await refreshItems();
        } catch (e) {
          if (!cancelled) showMessage(e.message, true);
        }
      })
      .catch((e) => {
        if (!cancelled) showMessage(e.message, true);
      });
    return () => {
      cancelled = true;
    };
  }, [refreshItems, showMessage]);

  useEffect(
    () => () => {
      if (undoTimer.current) clearTimeout(undoTimer.current);
    },
    [],
  );

  const loadEnquiries = useCallback(async () => {
    setEnquiriesLoading(true);
    try {
      const data = await api.enquiries();
      const list = asArray(data, 'enquiries');
      list.sort((a, b) => new Date(b.createdAt || 0) - new Date(a.createdAt || 0));
      setEnquiries(list);
    } catch (e) {
      showMessage(e.message, true);
    } finally {
      setEnquiriesLoading(false);
    }
  }, [showMessage]);

  // Load enquiries lazily the first time the tab opens.
  useEffect(() => {
    if (ready && activeTab === 'enquiries' && enquiries === null && !enquiriesLoading) {
      loadEnquiries();
    }
  }, [ready, activeTab, enquiries, enquiriesLoading, loadEnquiries]);

  async function archiveItem(item) {
    clearUndo();
    await api.patchItem(csrf, item.id, { archived: true });
    await refreshItems();
    showMessage(`“${item.title || 'Item'}” archived.`, false);
    setUndo({ id: item.id, title: item.title || 'Item' });
    undoTimer.current = setTimeout(() => setUndo(null), 5000);
  }

  async function undoArchive() {
    if (!undo) return;
    const id = undo.id;
    clearUndo();
    try {
      await api.patchItem(csrf, id, { archived: false });
      await refreshItems();
      showMessage('Restored.', false);
    } catch (e) {
      showMessage(e.message, true);
    }
  }

  function togglePresentation() {
    const next = !presentation;
    setPresentation(next);
    // studio.js toggled .presentation on the .dashboard section; fall back to
    // the studio root if the shell renders no .dashboard ancestor.
    const scope = document.querySelector('.dashboard') || rootRef.current;
    if (scope) scope.classList.toggle('presentation', next);
  }

  async function signOut(event) {
    event.preventDefault();
    try {
      await api.logout(csrf);
    } catch {
      // Still leave the studio even if the logout call fails.
    }
    window.location.href = '/login/';
  }

  function handleEnquiryDeleted(id) {
    setEnquiries((prev) => (prev || []).filter((e) => String(e.id) !== String(id)));
  }

  return (
    <div className="studio-app" ref={rootRef}>
      <div className="dashboard-heading">
        <div>
          <span className="eyebrow">YOUR PRIVATE WORKSPACE</span>
          <h1>
            Studio desk<span>.</span>
          </h1>
          <p id="account-status">
            {ready
              ? email
                ? `Signed in as ${email}`
                : 'Signed in'
              : 'Verifying access…'}
          </p>
        </div>
        <a href="/login/" onClick={signOut}>
          Sign out ↗
        </a>
      </div>
      <nav className="studio-tabs" aria-label="Studio sections">
        {TABS.map((tab) => (
          <button
            key={tab.id}
            type="button"
            data-panel={tab.id}
            className={activeTab === tab.id ? 'active' : ''}
            aria-selected={activeTab === tab.id}
            onClick={() => setActiveTab(tab.id)}
          >
            {tab.label}
          </button>
        ))}
      </nav>
      <p
        id="studio-message"
        role="status"
        aria-live="polite"
        className={message.error ? 'error' : ''}
      >
        {message.text}
        {undo && (
          <>
            {' '}
            <button type="button" onClick={undoArchive}>
              Undo
            </button>
          </>
        )}
      </p>
      {ready && (
        <div id="studio-content">
          {activeTab === 'uploads' && (
            <GalleryTab
              items={items}
              csrf={csrf}
              onRefresh={refreshItems}
              onMessage={showMessage}
              onArchive={archiveItem}
            />
          )}
          {activeTab === 'tools' && (
            <SoftwareTab
              items={items}
              csrf={csrf}
              onRefresh={refreshItems}
              onMessage={showMessage}
              onArchive={archiveItem}
              presentation={presentation}
              onTogglePresentation={togglePresentation}
            />
          )}
          {activeTab === 'enquiries' && (
            <EnquiriesTab
              enquiries={enquiries}
              loading={enquiriesLoading}
              csrf={csrf}
              onMessage={showMessage}
              onDeleted={handleEnquiryDeleted}
            />
          )}
        </div>
      )}
    </div>
  );
}
