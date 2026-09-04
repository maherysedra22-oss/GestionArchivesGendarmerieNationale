```vue
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// ======================================================
// SIDEBAR MOBILE
// ======================================================

const mobileMenuOpen = ref(false)
const isLoggingOut = ref(false)

// ======================================================
// UTILISATEUR CONNECTÉ
// ======================================================

const utilisateur = ref(null)

const chargerUtilisateur = () => {
  const data =
    localStorage.getItem('utilisateur') ||
    sessionStorage.getItem('utilisateur')

  if (!data) {
    utilisateur.value = null
    return
  }

  try {
    utilisateur.value = JSON.parse(data)
  } catch (error) {
    console.error(
      'Erreur lors de la lecture de l’utilisateur :',
      error
    )

    utilisateur.value = null
  }
}

onMounted(() => {
  chargerUtilisateur()
})

// ======================================================
// INFORMATIONS UTILISATEUR
// ======================================================

const prenom = computed(() => {
  return utilisateur.value?.prenom || 'Utilisateur'
})

const nom = computed(() => {
  return utilisateur.value?.nom || ''
})

const nomComplet = computed(() => {
  const value = `${prenom.value} ${nom.value}`.trim()

  return value || 'Utilisateur'
})

const initiales = computed(() => {
  const first = prenom.value?.charAt(0) || ''
  const last = nom.value?.charAt(0) || ''

  const initials = `${first}${last}`.toUpperCase()

  return initials || 'U'
})

const role = computed(() => {
  return (
    utilisateur.value?.role?.nom_role ||
    utilisateur.value?.role?.name ||
    utilisateur.value?.role ||
    'Administrateur'
  )
})

const grade = computed(() => {
  return (
    utilisateur.value?.grade?.nom_grade ||
    utilisateur.value?.grade?.libelle ||
    utilisateur.value?.grade ||
    'Grade non défini'
  )
})

// ======================================================
// MENU PRINCIPAL
// ======================================================

const menuItems = [
  {
    label: 'Tableau de bord',
    icon: '⌂',
    route: '/dashboard'
  },
  {
    label: 'Courriers arrivés',
    icon: '↓',
    route: '/courriers-arrives'
  },
  {
    label: 'Courriers départ',
    icon: '↑',
    route: '/courriers-depart'
  },
  {
    label: 'Documents numériques',
    icon: '▤',
    route: '/documents'
  },
  {
    label: 'Destinations',
    icon: '⌖',
    route: '/destinations'
  },
  {
    label: 'Classements',
    icon: '▦',
    route: '/classements'
  }
]

// ======================================================
// ADMINISTRATION
// ======================================================

const adminItems = [
  {
    label: 'Utilisateurs',
    icon: '♙',
    route: '/utilisateurs'
  },
  {
    label: 'Journal des activités',
    icon: '◷',
    route: '/journal'
  }
]

// ======================================================
// TITRE DYNAMIQUE
// ======================================================

const titrePage = computed(() => {
  const titres = {
    Dashboard: 'Tableau de bord',
    ChangePassword: 'Changer le mot de passe',
    CourriersArrives: 'Courriers arrivés',
    CourriersDepart: 'Courriers départ',
    Documents: 'Documents numériques',
    Destinations: 'Destinations',
    Classements: 'Classements',
    Utilisateurs: 'Utilisateurs',
    Journal: 'Journal des activités'
  }

  return titres[route.name] || 'Gestion des archives'
})

// ======================================================
// SOUS-TITRE DYNAMIQUE
// ======================================================

const sousTitrePage = computed(() => {
  const sousTitres = {
    Dashboard: 'Gestion des archives administratives',
    ChangePassword: 'Sécurité du compte',
    CourriersArrives: 'Gestion des courriers reçus',
    CourriersDepart: 'Gestion des courriers envoyés',
    Documents: 'Gestion des documents numériques',
    Destinations: 'Gestion des destinations',
    Classements: 'Gestion des classements',
    Utilisateurs: 'Gestion des comptes utilisateurs',
    Journal: 'Suivi des activités du système'
  }

  return (
    sousTitres[route.name] ||
    'Système de gestion des archives'
  )
})

// ======================================================
// FERMER MENU MOBILE
// ======================================================

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

// ======================================================
// LOGOUT
// ======================================================

const logout = async () => {
  if (isLoggingOut.value) return

  isLoggingOut.value = true

  const token =
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token')

  console.log('=== LOGOUT DEBUG ===')
  console.log('Token présent :', !!token)

  try {
    if (token) {
      const response = await fetch(
        'http://127.0.0.1:8000/api/logout',
        {
          method: 'POST',

          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`
          }
        }
      )

      console.log(
        'Logout HTTP status :',
        response.status
      )

      const data = await response
        .json()
        .catch(() => null)

      console.log('Logout response :', data)

      if (response.ok) {
        console.log(
          'Déconnexion Laravel réussie'
        )
      } else if (response.status === 401) {
        console.warn(
          'Token refusé par Sanctum'
        )
      } else {
        console.warn(
          'Erreur logout :',
          response.status
        )
      }
    }
  } catch (error) {
    console.error(
      'Erreur réseau logout :',
      error
    )
  } finally {
    localStorage.removeItem('auth_token')
    sessionStorage.removeItem('auth_token')

    localStorage.removeItem('utilisateur')
    sessionStorage.removeItem('utilisateur')

    utilisateur.value = null

    mobileMenuOpen.value = false

    router.push('/login')
  }
}
</script>


<template>
  <div class="app-layout">

    <!-- ==================================================
         MOBILE OVERLAY
    ================================================== -->

    <div
      v-if="mobileMenuOpen"
      class="mobile-overlay"
      @click="closeMobileMenu"
    ></div>


    <!-- ==================================================
         SIDEBAR
    ================================================== -->

    <aside
      class="sidebar"
      :class="{
        'sidebar-open': mobileMenuOpen
      }"
    >

      <!-- ==================================================
           SIDEBAR HEADER
      ================================================== -->

      <div class="sidebar-header">

        <div class="logo">
          GN
        </div>

        <div class="brand-text">
          <strong>GENDARMERIE</strong>

          <span>
            NATIONALE
          </span>
        </div>

        <!-- Bouton fermeture mobile -->

        <button
          class="mobile-close"
          @click="closeMobileMenu"
          aria-label="Fermer le menu"
        >
          ×
        </button>

      </div>


      <!-- ==================================================
           NAVIGATION
      ================================================== -->

      <nav class="navigation">

        <!-- PRINCIPAL -->

        <div class="nav-section-title">
          PRINCIPAL
        </div>

        <router-link
          v-for="item in menuItems"
          :key="item.route"
          :to="item.route"
          class="nav-item"
          active-class="active"
          @click="closeMobileMenu"
        >

          <span class="nav-icon">
            {{ item.icon }}
          </span>

          <span class="nav-label">
            {{ item.label }}
          </span>

        </router-link>


        <!-- ADMINISTRATION -->

        <div
          class="nav-section-title administration-title"
        >
          ADMINISTRATION
        </div>

        <router-link
          v-for="item in adminItems"
          :key="item.route"
          :to="item.route"
          class="nav-item"
          active-class="active"
          @click="closeMobileMenu"
        >

          <span class="nav-icon">
            {{ item.icon }}
          </span>

          <span class="nav-label">
            {{ item.label }}
          </span>

        </router-link>

      </nav>


      <!-- ==================================================
           SIDEBAR FOOTER
      ================================================== -->

      <div class="sidebar-bottom">

        <!-- USER -->

        <div class="sidebar-user">

          <div class="avatar">
            {{ initiales }}
          </div>

          <div class="sidebar-user-info">

            <strong>
              {{ nomComplet }}
            </strong>

            <span>
              {{ role }}
            </span>

          </div>

        </div>


        <!-- CHANGE PASSWORD -->

        <router-link
          to="/change-password"
          class="change-password"
          @click="closeMobileMenu"
        >

          <span class="change-password-icon">
            🔒
          </span>

          <span>
            Changer le mot de passe
          </span>

        </router-link>


        <!-- LOGOUT -->

        <button
          class="sidebar-logout"
          @click="logout"
          :disabled="isLoggingOut"
        >

          <span>
            ↪
          </span>

          <span>
            {{
              isLoggingOut
                ? 'Déconnexion...'
                : 'Se déconnecter'
            }}
          </span>

        </button>

      </div>

    </aside>


    <!-- ==================================================
         MAIN
    ================================================== -->

    <main class="main-content">

      <!-- ==================================================
           TOPBAR
      ================================================== -->

      <header class="topbar">

        <!-- MOBILE MENU -->

        <button
          class="menu-toggle"
          @click="mobileMenuOpen = true"
          aria-label="Ouvrir le menu"
        >
          ☰
        </button>


        <!-- PAGE TITLE -->

        <div class="topbar-title">

          <h1>
            {{ titrePage }}
          </h1>

          <p>
            {{ sousTitrePage }}
          </p>

        </div>


        <!-- USER -->

        <div class="topbar-user">

          <div class="topbar-user-info">

            <strong>
              {{ nomComplet }}
            </strong>

            <span>
              {{ grade }} · {{ role }}
            </span>

          </div>

          <div class="topbar-avatar">
            {{ initiales }}
          </div>

        </div>

      </header>


      <!-- ==================================================
           PAGE CONTENT
      ================================================== -->

      <div class="page-content">

        <router-view />

      </div>

    </main>

  </div>
</template>


<style scoped>

/* ======================================================
   RESET
====================================================== */

* {
  box-sizing: border-box;
}

.app-layout {
  min-height: 100vh;

  background: #f5f7fa;

  color: #243b53;

  font-family:
    Inter,
    "Segoe UI",
    Arial,
    sans-serif;
}


/* ======================================================
   SIDEBAR
====================================================== */

.sidebar {
  position: fixed;

  top: 0;
  left: 0;
  bottom: 0;

  width: 270px;

  display: flex;
  flex-direction: column;

  background: #08264d;

  color: white;

  z-index: 1000;

  box-shadow:
    5px 0 25px rgba(8, 38, 77, 0.12);

  transition:
    transform 0.25s ease;
}


/* ======================================================
   SIDEBAR HEADER
====================================================== */

.sidebar-header {
  height: 82px;

  display: flex;
  align-items: center;

  gap: 12px;

  padding: 0 22px;

  border-bottom:
    1px solid rgba(255, 255, 255, 0.08);
}

.logo {
  width: 44px;
  height: 44px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: #d5b45c;

  color: #08264d;

  font-size: 13px;
  font-weight: 900;

  border:
    2px solid rgba(255, 255, 255, 0.9);
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-text strong {
  color: white;

  font-size: 13px;

  letter-spacing: 0.8px;
}

.brand-text span {
  margin-top: 3px;

  color: #d5b45c;

  font-size: 9px;

  letter-spacing: 2px;

  font-weight: 700;
}


/* ======================================================
   NAVIGATION
====================================================== */

.navigation {
  flex: 1;

  padding: 20px 13px;

  overflow-y: auto;
}

.nav-section-title {
  padding: 0 11px 9px;

  color: #8fa8c2;

  font-size: 9px;

  font-weight: 800;

  letter-spacing: 1.5px;
}

.administration-title {
  margin-top: 22px;
}


/* ======================================================
   NAV ITEM
====================================================== */

.nav-item {
  min-height: 44px;

  display: flex;
  align-items: center;

  gap: 12px;

  margin: 3px 0;

  padding: 10px 12px;

  border-radius: 7px;

  color: #dbe7f2;

  text-decoration: none;

  font-size: 12px;

  font-weight: 500;

  transition:
    background 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease;
}

.nav-item:hover {
  background:
    rgba(255, 255, 255, 0.08);

  color: white;

  transform:
    translateX(2px);
}

.nav-item.active {
  background: #d5b45c;

  color: #08264d;

  font-weight: 800;

  box-shadow:
    0 5px 15px rgba(0, 0, 0, 0.12);
}

.nav-icon {
  width: 22px;

  display: flex;

  align-items: center;
  justify-content: center;

  font-size: 16px;

  font-weight: 800;
}

.nav-label {
  white-space: nowrap;
}


/* ======================================================
   SIDEBAR BOTTOM
====================================================== */

.sidebar-bottom {
  padding: 15px;

  border-top:
    1px solid rgba(255, 255, 255, 0.08);
}


/* ======================================================
   SIDEBAR USER
====================================================== */

.sidebar-user {
  display: flex;
  align-items: center;

  gap: 10px;

  margin-bottom: 12px;
}

.avatar {
  width: 38px;
  height: 38px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: #d5b45c;

  color: #08264d;

  font-size: 12px;

  font-weight: 900;
}

.sidebar-user-info {
  min-width: 0;

  display: flex;
  flex-direction: column;
}

.sidebar-user-info strong {
  overflow: hidden;

  color: white;

  font-size: 11px;

  white-space: nowrap;

  text-overflow: ellipsis;
}

.sidebar-user-info span {
  margin-top: 3px;

  color: #9fb3c8;

  font-size: 9px;
}


/* ======================================================
   CHANGE PASSWORD
====================================================== */

.change-password {
  min-height: 38px;

  display: flex;
  align-items: center;

  gap: 8px;

  margin-bottom: 8px;

  padding: 8px 10px;

  border-radius: 7px;

  color: #dbe7f2;

  text-decoration: none;

  font-size: 10px;

  font-weight: 600;

  transition: 0.2s ease;
}

.change-password:hover {
  background:
    rgba(255, 255, 255, 0.08);

  color: white;
}

.change-password-icon {
  font-size: 13px;
}


/* ======================================================
   LOGOUT
====================================================== */

.sidebar-logout {
  width: 100%;

  min-height: 40px;

  display: flex;
  align-items: center;
  justify-content: center;

  gap: 8px;

  border:
    1px solid rgba(255, 255, 255, 0.14);

  border-radius: 7px;

  background:
    rgba(255, 255, 255, 0.06);

  color: white;

  cursor: pointer;

  font-size: 11px;

  font-weight: 700;

  transition: 0.2s ease;
}

.sidebar-logout:hover {
  background:
    rgba(255, 255, 255, 0.12);
}

.sidebar-logout:disabled {
  opacity: 0.6;

  cursor: not-allowed;
}


/* ======================================================
   MAIN
====================================================== */

.main-content {
  min-height: 100vh;

  margin-left: 270px;
}


/* ======================================================
   TOPBAR
====================================================== */

.topbar {
  min-height: 82px;

  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 0 32px;

  background: white;

  border-bottom:
    1px solid #e5eaf0;
}


/* ======================================================
   TOPBAR TITLE
====================================================== */

.topbar-title {
  min-width: 0;
}

.topbar-title h1 {
  margin: 0;

  color: #08264d;

  font-size: 21px;

  font-weight: 800;
}

.topbar-title p {
  margin: 4px 0 0;

  color: #829ab1;

  font-size: 11px;
}


/* ======================================================
   TOPBAR USER
====================================================== */

.topbar-user {
  display: flex;
  align-items: center;

  gap: 11px;
}

.topbar-user-info {
  display: flex;
  flex-direction: column;

  text-align: right;
}

.topbar-user-info strong {
  color: #243b53;

  font-size: 12px;
}

.topbar-user-info span {
  margin-top: 3px;

  color: #829ab1;

  font-size: 10px;
}

.topbar-avatar {
  width: 43px;
  height: 43px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: #e8eef5;

  color: #08264d;

  font-size: 12px;

  font-weight: 900;

  border:
    2px solid #d5b45c;
}


/* ======================================================
   MOBILE MENU BUTTON
====================================================== */

.menu-toggle {
  display: none;

  border: 1px solid #e0e6ec;

  border-radius: 7px;

  background: white;

  color: #08264d;

  cursor: pointer;
}


/* ======================================================
   PAGE CONTENT
====================================================== */

.page-content {
  width: 100%;

  min-height: calc(100vh - 82px);
}


/* ======================================================
   MOBILE CLOSE
====================================================== */

.mobile-close {
  display: none;
}


/* ======================================================
   OVERLAY
====================================================== */

.mobile-overlay {
  display: none;
}


/* ======================================================
   TABLET
====================================================== */

@media (max-width: 1100px) {

  .sidebar {
    width: 235px;
  }

  .main-content {
    margin-left: 235px;
  }

  .topbar {
    padding: 0 25px;
  }

}


/* ======================================================
   TABLET / MOBILE
====================================================== */

@media (max-width: 800px) {

  .sidebar {
    width: 270px;

    transform:
      translateX(-100%);

    box-shadow:
      10px 0 35px rgba(0, 0, 0, 0.18);
  }

  .sidebar.sidebar-open {
    transform:
      translateX(0);
  }

  .main-content {
    margin-left: 0;
  }

  .topbar {
    min-height: 70px;

    justify-content: flex-start;

    padding: 0 18px;

    gap: 12px;
  }

  .menu-toggle {
    width: 38px;
    height: 38px;

    display: flex;

    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    font-size: 18px;
  }

  .topbar-title {
    flex: 1;

    min-width: 0;
  }

  .topbar-title h1 {
    font-size: 17px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
  }

  .topbar-title p {
    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
  }

  .topbar-user-info {
    display: none;
  }

  .topbar-avatar {
    width: 38px;
    height: 38px;
  }

  .mobile-close {
    margin-left: auto;

    display: flex;

    align-items: center;
    justify-content: center;

    width: 30px;
    height: 30px;

    border: 0;

    background: transparent;

    color: white;

    cursor: pointer;

    font-size: 23px;
  }

  .mobile-overlay {
    position: fixed;

    inset: 0;

    display: block;

    background:
      rgba(0, 0, 0, 0.45);

    z-index: 999;
  }

  .page-content {
    min-height:
      calc(100vh - 70px);
  }

}


/* ======================================================
   MOBILE
====================================================== */

@media (max-width: 600px) {

  .topbar {
    padding: 0 14px;
  }

  .topbar-title h1 {
    font-size: 15px;
  }

  .topbar-title p {
    font-size: 9px;
  }

}


/* ======================================================
   SMALL MOBILE
====================================================== */

@media (max-width: 380px) {

  .brand-text strong {
    font-size: 11px;
  }

  .brand-text span {
    font-size: 8px;
  }

  .topbar-title p {
    display: none;
  }

  .sidebar {
    width: 255px;
  }

}


/* ======================================================
   ACCESSIBILITY
====================================================== */

button:focus-visible,
a:focus-visible {
  outline:
    3px solid rgba(213, 180, 92, 0.5);

  outline-offset: 2px;
}

</style>
```
