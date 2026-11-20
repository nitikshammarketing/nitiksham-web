import React, { useState } from 'react';
import { api } from './api.js';

function formatDate(value) {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

export default function EnquiriesTab({ enquiries, loading, csrf, onMessage, onDeleted }) {
  const [busyId, setBusyId] = useState(null);

  async function handleDelete(enquiry) {
    const label = enquiry.name || enquiry.email || 'this enquiry';
    if (!window.confirm(`Delete the enquiry from ${label}? This cannot be undone.`)) return;
    setBusyId(enquiry.id);
    try {
      await api.deleteEnquiry(csrf, enquiry.id);
      onDeleted(enquiry.id);
      onMessage('Enquiry deleted.', false);
    } catch (e) {
      onMessage(e.message, true);
    } finally {
      setBusyId(null);
    }
  }

  return (
    <section id="enquiries" className="studio-panel">
      <div className="workspace-grid">
        <div className="editor-card">
          <span className="eyebrow">CONTACT FORM</span>
          <h2>Enquiries.</h2>
          <p>Messages sent through the contact form on the website, newest first.</p>
        </div>
        <div>
          <div className="section-label">
            <h2>Your enquiries</h2>
          </div>
          {loading && <p className="empty-state">Loading enquiries…</p>}
          {!loading && (!enquiries || enquiries.length === 0) && (
            <p className="empty-state">
              No enquiries yet. New contact-form messages will appear here.
            </p>
          )}
          {!loading && enquiries && enquiries.length > 0 && (
            <div className="managed-list">
              {enquiries.map((enquiry) => (
                <article className="managed-card" key={enquiry.id}>
                  <small>{formatDate(enquiry.createdAt)}</small>
                  <h3>{enquiry.name || 'Unnamed'}</h3>
                  <p>
                    {enquiry.email ? (
                      <a href={`mailto:${enquiry.email}`}>{enquiry.email}</a>
                    ) : null}
                    {enquiry.company ? (
                      <>
                        <br />
                        {enquiry.company}
                      </>
                    ) : null}
                    {enquiry.service ? (
                      <>
                        <br />
                        Service: {enquiry.service}
                      </>
                    ) : null}
                    {enquiry.phone ? (
                      <>
                        <br />
                        {enquiry.phone}
                      </>
                    ) : null}
                  </p>
                  {enquiry.message ? <p>{enquiry.message}</p> : null}
                  <div className="card-actions">
                    <button
                      type="button"
                      disabled={busyId === enquiry.id}
                      onClick={() => handleDelete(enquiry)}
                    >
                      Delete
                    </button>
                  </div>
                </article>
              ))}
            </div>
          )}
        </div>
      </div>
    </section>
  );
}
