// Fetch helpers for the Studio PHP API (contract in PLAN.md).
// Every request sends cookies (the PHP session) and every mutation sends the
// CSRF token issued by GET /api/session.php as the X-CSRF-Token header.

export async function request(url, options = {}) {
  const { csrf, ...fetchOptions } = options;
  const headers = new Headers(fetchOptions.headers || {});
  if (csrf) headers.set('X-CSRF-Token', csrf);

  const response = await fetch(url, {
    ...fetchOptions,
    headers,
    credentials: 'include',
  });

  let data = null;
  const text = await response.text();
  if (text) {
    try {
      data = JSON.parse(text);
    } catch {
      // Non-JSON body (e.g. a plain-text server error page) — handled below.
    }
  }

  if (!response.ok) {
    const serverMessage = data && (data.error || data.message);
    throw new Error(serverMessage || `Request failed (${response.status}).`);
  }
  return data;
}

export const api = {
  session: () => request('/api/session.php'),

  logout: (csrf) => request('/api/logout.php', { method: 'POST', csrf }),

  items: () => request('/api/manage/items.php'),

  // FormData — Content-Type must NOT be set manually (the browser adds the
  // multipart boundary).
  upload: (csrf, formData) =>
    request('/api/manage/upload.php', { method: 'POST', csrf, body: formData }),

  saveSoftware: (csrf, payload) =>
    request('/api/manage/software.php', {
      method: 'POST',
      csrf,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    }),

  patchItem: (csrf, id, patch) =>
    request(`/api/manage/items.php?id=${encodeURIComponent(id)}`, {
      method: 'PATCH',
      csrf,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(patch),
    }),

  enquiries: () => request('/api/manage/enquiries.php'),

  deleteEnquiry: (csrf, id) =>
    request(`/api/manage/enquiries.php?id=${encodeURIComponent(id)}`, {
      method: 'DELETE',
      csrf,
    }),
};

// File endpoint: serves the stored file for a record id (inline for images,
// attachment for PDFs). Access rules are enforced server-side.
export const fileUrl = (id) => `/api/file.php?id=${encodeURIComponent(id)}`;
