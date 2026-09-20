
import { createRouter, createWebHistory } from 'vue-router'

import Login from '../views/Login.vue'
import Roles from '../views/Roles.vue'
import ChangePassword from '../views/ChangePassword.vue'
import Dashboard from '../views/Dashboard.vue'
import AppLayout from '../layouts/AppLayout.vue'
import CourriersArrives from '../views/courriers/CourriersArrives.vue'
import CourriersDepart from '../views/courriers/CourriersDepart.vue'
import Utilisateurs from '../views/utilisateurs/Utilisateurs.vue'
import JournalActivites from '../views/journal/JournalActivites.vue'
import DocumentsNumeriques from '../views/DocumentsNumeriques.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    // =====================================================
    // ROOT
    // =====================================================
    {
      path: '/',
      redirect: '/login',
    },

    // =====================================================
    // PUBLIC
    // =====================================================
    {
      path: '/login',
      name: 'Login',
      component: Login,
      meta: {
        guest: true,
      },
    },

    // =====================================================
    // PROTECTED ROUTES
    // =====================================================
    {
      path: '/',
      component: AppLayout,
      meta: {
        requiresAuth: true,
      },

      children: [
        // =================================================
        // DASHBOARD
        // =================================================
        {
          path: 'dashboard',
          name: 'Dashboard',
          component: Dashboard,
        },

        // =================================================
        // CHANGE PASSWORD
        // =================================================
        {
          path: 'change-password',
          name: 'ChangePassword',
          component: ChangePassword,
        },

        // =================================================
        // COURRIERS ARRIVÉS
        // =================================================
        {
          path: 'courriers-arrives',
          name: 'CourriersArrives',
          component: CourriersArrives,
        },

        // =================================================
        // COURRIERS DÉPART
        // =================================================
        {
          path: 'courriers-depart',
          name: 'CourriersDepart',
          component: CourriersDepart,
        },

        // =================================================
        // DOCUMENTS NUMÉRIQUES
        // =================================================
        {
          path: 'documents',
          name: 'Documents',
          component: DocumentsNumeriques,
        },

        /* =================================================
        // DESTINATIONS
        // =================================================
        {
          path: 'destinations',
          name: 'Destinations',
          component: ComingSoon,
        },

        // =================================================
        // CLASSEMENTS
        // =================================================
        {
          path: 'classements',
          name: 'Classements',
          component: ComingSoon,
        },*/

        // =================================================
        // UTILISATEURS
        // =================================================
        {
          path: 'utilisateurs',
          name: 'Utilisateurs',
          component: Utilisateurs,

          meta: {
            roles: ['Administrateur'],
          },
        },

        {
          path: 'roles',
          name: 'Roles',
          component: Roles,
          meta: {
            roles: ['Administrateur'],
          },
        },

        // =================================================
        // JOURNAL DES ACTIVITÉS
        // =================================================
        {
          path: 'journal',
          name: 'Journal',
          component: JournalActivites,

          meta: {
            roles: ['Administrateur'],
          },
        },
      ],
    },
  ],
})

// =========================================================
// AUTH GUARD
// =========================================================

router.beforeEach(async (to) => {
  const token =
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token')

  // -------------------------------------------------------
  // Route protégée sans token
  // -------------------------------------------------------
  if (to.meta.requiresAuth && !token) {
    return {
      name: 'Login',
    }
  }

  // -------------------------------------------------------
  // Route Login avec token déjà présent
  // -------------------------------------------------------
  if (to.meta.guest && token) {
    return {
      name: 'Dashboard',
    }
  }

  // -------------------------------------------------------
  // Vérification du token auprès de Laravel
  // -------------------------------------------------------
  if (to.meta.requiresAuth && token) {
    try {
      const response = await fetch(
        'http://127.0.0.1:8000/api/me',
        {
          method: 'GET',

          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        }
      )

      // ---------------------------------------------------
      // Token invalide / expiré / supprimé
      // ---------------------------------------------------
      if (response.status === 401) {
        localStorage.removeItem('auth_token')
        sessionStorage.removeItem('auth_token')

        localStorage.removeItem('utilisateur')
        sessionStorage.removeItem('utilisateur')

        return {
          name: 'Login',
        }
      }

      // ---------------------------------------------------
      // Erreur serveur
      // ---------------------------------------------------
      if (!response.ok) {
        console.error(
          'Erreur lors de la vérification du token :',
          response.status
        )

        return true
      }

      return true
    } catch (error) {
      console.error(
        'Erreur de connexion avec Laravel :',
        error
      )

      return true
    }
  }

  return true
})

export default router