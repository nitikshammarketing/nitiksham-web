import React, { useRef, useState } from 'react';
import { api, fileUrl } from './api.js';

const MAX_FILE_BYTES = 15 * 1024 * 1024; // 15 MB, matches the server limit.

function kindLabel(item) {
  if (!item || !item.kind) return 'ITEM';
  return String(item.kind).toUpperCase();
}

export default function GalleryTab({ items, csrf, onRefresh, onMessage, onArchive }) {
  const [uploading, setUploading] = useState(false);
  const [busyId, setBusyId] = useState(null);
  const [renamingId, setRenamingId] = useState(null);
  const [renameValue, setRenameValue] = useState('');
  const formRef = useRef(null);

  const visible = (items || []).filter((x) => x && x.kind !== 'software' && !x.archived);
  const archived = (items || []).filter((x) => x && x.kind !== 'software' && x.archived);

  async function handleUpload(event) {
    event.preventDefault();
    const form = formRef.current;
    if (!form) return;
    const file = form.elements.file && form.elements.file.files[0];
    if (file && file.size > MAX_FILE_BYTES) {
      onMessage('Maximum file size is 15 MB.', true);
      return;
    }
    setUploading(true);
    onMessage('Uploading your piece…', false);
    try {
      const data = new FormData(form);
      data.set('published', String(form.elements.published.checked));
      await api.upload(csrf, data);
      form.reset();
      await onRefresh();
      onMessage('Upload saved. You can publish or rename it from your collection.', false);
    } catch (e) {
      onMessage(e.message, true);
    } finally {
      setUploading(false);
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

  function startRename(item) {
    setRenamingId(item.id);
    setRenameValue(item.title || '');
  }

  async function saveRename(item) {
    const title = renameValue.trim();
    if (!title) {
      onMessage('Title cannot be empty.', true);
      return;
    }
    await runFor(item.id, async () => {
      await api.patchItem(csrf, item.id, { title: title.slice(0, 120) });
      setRenamingId(null);
      await onRefresh();
      onMessage('Title updated.', false);
    });
  }

  function renderCard(item) {
    const busy = busyId === item.id;
    const renaming = renamingId === item.id;
    const isImage = typeof item.mime === 'string' && item.mime.startsWith('image/');
    const isPdf = item.mime === 'application/pdf';
    return (
      <article className="managed-card" key={item.id}>
        {isImage && <img src={fileUrl(item.id)} alt={item.title || ''} loading="lazy" />}
        {isPdf && (
          <div className="pdf-cover" aria-hidden="true">
            <span>PDF</span>
          </div>
        )}
        <small>{kindLabel(item)}</small>
        {renaming ? (
          <span className="rename-row">
            <input
              type="text"
              value={renameValue}
              maxLength={120}
              autoFocus
              aria-label="New title"
              onChange={(e) => setRenameValue(e.target.value)}
              onKeyDown={(e) => {
                if (e.key === 'Enter') {
                  e.preventDefault();
                  saveRename(item);
                }
                if (e.key === 'Escape') setRenamingId(null);
              }}
            />
            <button type="button" disabled={busy} onClick={() => saveRename(item)}>
              Save
            </button>
            <button type="button" onClick={() => setRenamingId(null)}>
              Cancel
            </button>
          </span>
        ) : (
          <h3>{item.title || 'Untitled'}</h3>
        )}
        {item.description ? <p>{item.description}</p> : null}
        <span className="status-tag">{item.published ? 'Visible on website' : 'Private'}</span>
        <div className="card-actions">
          <a href={fileUrl(item.id)} target="_blank" rel="noopener noreferrer">
            {isPdf ? 'Download PDF ↗' : 'View image ↗'}
          </a>
          {!renaming && (
            <button type="button" disabled={busy} onClick={() => startRename(item)}>
              Rename
            </button>
          )}
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
        </div>
      </article>
    );
  }

  return (
    <section id="uploads" className="studio-panel">
      <div className="workspace-grid">
        <form id="upload-form" className="editor-card" ref={formRef} onSubmit={handleUpload}>
          <span className="eyebrow">ADD TO YOUR GALLERY</span>
          <h2>Share a new piece.</h2>
          <label>
            Title
            <input name="title" maxLength={120} required placeholder="e.g. November newsletter" />
          </label>
          <label>
            Type
            <select name="kind" defaultValue="design">
              <option value="design">Design</option>
              <option value="newsletter">Newsletter</option>
            </select>
          </label>
          <label>
            Short description
            <textarea name="description" maxLength={1000} rows={3} />
          </label>
          <label>
            Upload file
            <input
              name="file"
              type="file"
              accept="image/jpeg,image/png,image/webp,application/pdf"
              required
            />
          </label>
          <small>JPG, PNG, WebP or PDF · maximum 15 MB</small>
          <label className="check-label">
            <input type="checkbox" name="published" />
            Show in website gallery
          </label>
          <button className="button" type="submit" disabled={uploading}>
            {uploading ? 'Uploading…' : 'Upload piece ↑'}
          </button>
        </form>
        <div>
          <div className="section-label">
            <h2>Your collection</h2>
            <a href="/gallery/" target="_blank" rel="noopener noreferrer">
              View gallery ↗
            </a>
          </div>
          <div id="managed-gallery" className="managed-list">
            {visible.map(renderCard)}
            {visible.length === 0 && (
              <p className="empty-state">
                Your collection is empty. Upload your first design or newsletter to get started.
              </p>
            )}
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
