import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  // ==============================
  // ÉTAT D'AUTHENTIFICATION
  // ==============================

  const token = ref(null)
  const utilisateur = ref(null)

  // ==============================
  // ÉTAT CALCULÉ
  // ==============================

  const isAuthenticated = computed(() => {
    return !!token.value
  })

  const role = computed(() => {
    return utilisateur.value?.role?.nom_role ||
           utilisateur.value?.role?.name ||
           utilisateur.value?.role ||
           null
  })

  const grade = computed(() => {
    return utilisateur.value?.grade?.nom_grade ||
           utilisateur.value?.grade?.libelle ||
           utilisateur.value?.grade ||
           null
  })

  // ==============================
  // LOGIN
  // ==============================

  const login = async (email, mot_de_passe, remember = false) => {
    const response = await fetch(
      'http://127.0.0.1:8000/api/login',
      {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
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
    storage.setItem('auth_token', data.token)

    storage.setItem(
      'utilisateur',
      JSON.stringify(data.utilisateur)
    )

    return data
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
    login
  }
})