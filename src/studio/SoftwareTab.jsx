import React, { useState } from 'react';
import { api } from './api.js';

const EMPTY_FORM = { id: '', title: '', description: '', liveUrl: '', demoUrl: '', published: false };

export default function SoftwareTab({
  items,
  csrf,
  onRefresh,
  onMessage,
  onArchive,
  presentation,
  onTogglePresentation,
}) {
  const [form, setForm] = useState(EMPTY_FORM);
  const [saving, setSaving] = useState(false);
  const [busyId, setBusyId] = useState(null);

  const visible = (items || []).filter((x) => x && x.kind === 'software' && !x.archived);
  const archived = (items || []).filter((x) => x && x.kind === 'software' && x.archived);
  const editing = Boolean(form.id);

  function setField(name, value) {
    setForm((prev) => ({ ...prev, [name]: value }));
  }

  function startEdit(item) {
    setForm({
      id: item.id || '',
      title: item.title || '',
      description: item.description || '',
      liveUrl: item.liveUrl || '',
      demoUrl: item.demoUrl || '',
      published: Boolean(item.published),
    });
    const formEl = document.getElementById('software-form');
    if (formEl) {
      formEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      const titleInput = formEl.elements.title;
      if (titleInput) titleInput.focus();
    }
  }

  function cancelEdit() {
    setForm(EMPTY_FORM);
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setSaving(true);
    try {
      const payload = {
        title: form.title.trim(),
        description: form.description.trim(),
        liveUrl: form.liveUrl.trim(),
        demoUrl: form.demoUrl.trim(),
        published: form.published,
      };
      // id present only when editing an existing entry.
      if (form.id) payload.id = form.id;
      await api.saveSoftware(csrf, payload);
      setForm(EMPTY_FORM);
      await onRefresh();
      onMessage('Software saved.', false);
    } catch (e) {
      onMessage(e.message, true);
    } finally {
      setSaving(false);
    }
  }

  // Runs a per-card action with a busy state and error feedback.
  async function runFor(id, fn) {
    setBusyId(id);
    try {
      await fn();
    } catch (e) {
      onMessage(e.message, true);
    } finally {
      setBusyId(null);
    }
  }

  async function togglePublished(item) {
    await runFor(item.id, async () => {
      await api.patchItem(csrf, item.id, { published: !item.published });
      await onRefresh();
      onMessage(
        item.published ? 'Removed from website display.' : 'Published to the website.',
        false,
      );
    });
  }

  async function restore(item) {
    await runFor(item.id, async () => {
      await api.patchItem(csrf, item.id, { archived: false });
      await onRefresh();
      onMessage('Item restored.', false);
    });
  }

  function renderCard(item) {
    const busy = busyId === item.id;
    return (
      <article className="managed-card" key={item.id}>
        <small>SOFTWARE</small>
        <h3>{item.title || 'Untitled'}</h3>
        {item.description ? <p>{item.description}</p> : null}
        <span className="status-tag">{item.published ? 'Visible on website' : 'Private'}</span>
        <div className="card-actions">
          {item.liveUrl && (
            <a href={item.liveUrl} target="_blank" rel="noopener noreferrer">
              Open live software ↗
            </a>
          )}
          {item.demoUrl && (
            <a href={item.demoUrl} target="_blank" rel="noopener noreferrer">
              Launch demo ↗
            </a>
          )}
          {!presentation && (
            <>
              {!item.demoUrl && <p>Separate demo link not configured.</p>}
              <button type="button" disabled={busy} onClick={() => startEdit(item)}>
                Edit links
              </button>
              <button type="button" disabled={busy} onClick={() => togglePublished(item)}>
                {item.published ? 'Unpublish' : 'Publish'}
              </button>
              <button
                type="button"
                disabled={busy}
                onClick={() => runFor(item.id, () => onArchive(item))}
              >
                Archive
              </button>
            </>
          )}
        </div>
      </article>
    );
  }

  return (
    <section id="tools" className="studio-panel">
      <div className="workspace-grid">
        <form id="software-form" className="editor-card" onSubmit={handleSubmit}>
          <span className="eyebrow">YOUR SOFTWARE SHELF</span>
          <h2>{editing ? 'Edit this tool.' : 'Add a tool.'}</h2>
          <input type="hidden" name="id" value={form.id} readOnly />
          <label>
            Software name
            <input
              name="title"
              maxLength={120}
              required
              value={form.title}
              onChange={(e) => setField('title', e.target.value)}
            />
          </label>
          <label>
            Description
            <textarea
              name="description"
              maxLength={1000}
              rows={3}
              value={form.description}
              onChange={(e) => setField('description', e.target.value)}
            />
          </label>
          <label>
            Live software URL
            <input
              name="liveUrl"
              type="url"
              placeholder="https://…"
              value={form.liveUrl}
              onChange={(e) => setField('liveUrl', e.target.value)}
            />
          </label>
          <label>
            Separate demo URL (optional)
            <input
              name="demoUrl"
              type="url"
              placeholder="https://…"
              value={form.demoUrl}
              onChange={(e) => setField('demoUrl', e.target.value)}
            />
          </label>
          <small>Use a separate demo environment with sample data for client walkthroughs.</small>
          <label className="check-label">
            <input
              type="checkbox"
              name="published"
              checked={form.published}
              onChange={(e) => setField('published', e.target.checked)}
            />
            Show description in Software studio
          </label>
          <button className="button" type="submit" disabled={saving}>
            {saving ? 'Saving…' : 'Save software ↗'}
          </button>
          {editing && (
            <button type="button" id="cancel-edit" onClick={cancelEdit}>
              Cancel edit
            </button>
          )}
        </form>
        <div>
          <div className="section-label">
            <h2>Your software</h2>
            <button type="button" id="presentation-toggle" onClick={onTogglePresentation}>
              {presentation ? 'Exit presentation view' : 'Presentation view ↗'}
            </button>
          </div>
          <div id="managed-software" className="managed-list">
            {visible.map(renderCard)}
            {visible.length === 0 && <p>Add your first software using the form.</p>}
          </div>
          {archived.length > 0 && (
            <details className="archived-list">
              <summary>Archived items ({archived.length})</summary>
              {archived.map((item) => (
                <p key={item.id}>
                  {item.title || 'Untitled'}{' '}
                  <button type="button" disabled={busyId === item.id} onClick={() => restore(item)}>
                    Restore
                  </button>
                </p>
              ))}
            </details>
          )}
        </div>
      </div>
    </section>
  );
}
