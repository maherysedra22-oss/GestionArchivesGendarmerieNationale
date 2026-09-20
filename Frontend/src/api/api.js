
const API_URL = 'http://127.0.0.1:8000/api'

// ======================================================
// Récupérer le token
// ======================================================

const getToken = () => {
  return (
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token')
  )
}

// ======================================================
// Requête API centralisée
// ======================================================

const request = async (endpoint, options = {}) => {
  const token = getToken()

  const headers = {
    Accept: 'application/json',
    ...options.headers,
  }

  // Ajouter Content-Type uniquement si nécessaire
  if (!(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
  }

  // Ajouter le token Sanctum
  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  const response = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers,
  })

  // ====================================================
  // Token invalide
  // ====================================================

  if (response.status === 401) {
    localStorage.removeItem('auth_token')
    sessionStorage.removeItem('auth_token')

    localStorage.removeItem('utilisateur')
    sessionStorage.removeItem('utilisateur')

    window.location.href = '/login'

    throw new Error('Session expirée ou token invalide.')
  }

  // ====================================================
  // Lire la réponse JSON
  // ====================================================

  let data = null

  try {
    data = await response.json()
  } catch {
    data = null
  }

  // ====================================================
  // Erreur HTTP
  // ====================================================

  if (!response.ok) {
    const error = new Error(
      data?.message || 'Une erreur est survenue.'
    )

    error.status = response.status
    error.data = data

    throw error
  }

  return data
}

// ======================================================
// GET
// ======================================================

const get = (endpoint) => {
  return request(endpoint, {
    method: 'GET',
  })
}

// ======================================================
// POST
// ======================================================

const post = (endpoint, body = {}) => {
  return request(endpoint, {
    method: 'POST',
    body: JSON.stringify(body),
  })
}

// ======================================================
// PUT
// ======================================================

const put = (endpoint, body = {}) => {
  return request(endpoint, {
    method: 'PUT',
    body: JSON.stringify(body),
  })
}

// ======================================================
// PATCH
// ======================================================

const patch = (endpoint, body = {}) => {
  return request(endpoint, {
    method: 'PATCH',
    body: JSON.stringify(body),
  })
}

// ======================================================
// DELETE
// ======================================================

const remove = (endpoint) => {
  return request(endpoint, {
    method: 'DELETE',
  })
}

// ======================================================
// Upload fichier
// ======================================================

const upload = (endpoint, formData) => {
  return request(endpoint, {
    method: 'POST',
    body: formData,
  })
}

// ======================================================
// Export
// ======================================================

export default {
  get,
  post,
  put,
  patch,
  delete: remove,
  upload,
}

