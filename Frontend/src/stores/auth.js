import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {

  // ==============================
  // ÉTAT D'AUTHENTIFICATION
  // ==============================

  const token = ref(
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token') ||
    null
  )

  const utilisateur = ref(
    (() => {
      try {
        const raw =
          localStorage.getItem('utilisateur') ||
          sessionStorage.getItem('utilisateur')

        return raw ? JSON.parse(raw) : null
      } catch {
        return null
      }
    })()
  )

  // ==============================
  // ÉTAT CALCULÉ
  // ==============================

  const isAuthenticated = computed(() => {
    return !!token.value
  })

  const role = computed(() => {
    return (
      utilisateur.value?.role?.nom_role ||
      utilisateur.value?.role?.name ||
      utilisateur.value?.role ||
      null
    )
  })

  const grade = computed(() => {
    return (
      utilisateur.value?.grade?.nom_grade ||
      utilisateur.value?.grade?.libelle ||
      utilisateur.value?.grade ||
      null
    )
  })

  // ==============================
  // PERMISSIONS
  // ==============================

  const permissions = computed(() => {
    const user = utilisateur.value

    if (!user) {
      return []
    }

    const permissionsUtilisateur =
      user?.role?.permissions ||
      user?.permissions ||
      []

    return permissionsUtilisateur
    .map(permission => {
      if (typeof permission === 'string') {
        return permission
      }

      return (
        permission?.code_permission ||
        permission?.code ||
        permission?.nom_permission ||
        null
      )
    })
    .filter(Boolean)
  })

  // ==============================
  // VÉRIFIER UNE PERMISSION
  // ==============================

  const hasPermission = (codePermission) => {
    if (!codePermission) {
      return false
    }

    return permissions.value.includes(codePermission)
  }

  // ==============================
  // VÉRIFIER PLUSIEURS PERMISSIONS
  // ==============================

  const hasAnyPermission = (codesPermissions = []) => {
    return codesPermissions.some(permission =>
      hasPermission(permission)
    )
  }

  const hasAllPermissions = (codesPermissions = []) => {
    return codesPermissions.every(permission =>
      hasPermission(permission)
    )
  }

  // ==============================
  // LOGIN
  // ==============================

  const login = async (
    email,
    mot_de_passe,
    remember = false
  ) => {

    const response = await fetch(
      'http://127.0.0.1:8000/api/login',
      {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          email,
          mot_de_passe
        })
      }
    )

    const data = await response.json()

    if (!response.ok) {
      throw new Error(
        data.message || 'Identifiants incorrects.'
      )
    }

    // Récupération du token
    token.value = data.token

    // Récupération de l'utilisateur
    utilisateur.value = data.utilisateur

    // Choix du type de stockage
    const storage = remember
      ? localStorage
      : sessionStorage

    // Nettoyer les anciennes données
    localStorage.removeItem('auth_token')
    localStorage.removeItem('utilisateur')

    sessionStorage.removeItem('auth_token')
    sessionStorage.removeItem('utilisateur')

    // Sauvegarder les nouvelles données
    storage.setItem(
      'auth_token',
      data.token
    )

    storage.setItem(
      'utilisateur',
      JSON.stringify(data.utilisateur)
    )

    return data
  }

  // ==============================
  // RAFRAÎCHIR L'UTILISATEUR
  // ==============================

  const loadCurrentUser = async () => {
    if (!token.value) {
      return null
    }

    try {
      const response = await fetch(
        'http://127.0.0.1:8000/api/me',
        {
          method: 'GET',
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token.value}`
          }
        }
      )

      const data = await response.json()

      if (!response.ok) {
        throw new Error(
          data.message || 'Impossible de récupérer l’utilisateur.'
        )
      }

      const user = data.data || data.utilisateur || data

      utilisateur.value = user

      const storage = localStorage.getItem('auth_token')
        ? localStorage
        : sessionStorage

      storage.setItem(
        'utilisateur',
        JSON.stringify(user)
      )

      return user

    } catch (error) {
      console.error(
        'Erreur chargement utilisateur connecté :',
        error
      )

      return null
    }
  }


  // ==============================
  // METTRE À JOUR L'UTILISATEUR
  // ==============================
  const updateUtilisateur = (updates = {}) => {
    if (!utilisateur.value) {
      return
    }

    utilisateur.value = {
      ...utilisateur.value,
      ...updates
    }

    const storage = localStorage.getItem('auth_token')
      ? localStorage
      : sessionStorage

    storage.setItem(
      'utilisateur',
      JSON.stringify(utilisateur.value)
    )
  }

  // ==============================
  // LOGOUT
  // ==============================

  const logout = () => {

    token.value = null
    utilisateur.value = null

    localStorage.removeItem('auth_token')
    localStorage.removeItem('utilisateur')

    sessionStorage.removeItem('auth_token')
    sessionStorage.removeItem('utilisateur')
  }

  // ==============================
  // RETURN
  // ==============================

  return {
    token,
    utilisateur,

    isAuthenticated,

    role,
    grade,

    permissions,

    hasPermission,
    hasAnyPermission,
    hasAllPermissions,

    login,
    loadCurrentUser,
    updateUtilisateur,
    logout
  }
})