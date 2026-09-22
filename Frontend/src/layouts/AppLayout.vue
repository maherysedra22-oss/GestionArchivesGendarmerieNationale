
<script setup>
import {
  ref,
  computed,
  onMounted,
  onBeforeUnmount
} from 'vue'

import { useRouter, useRoute } from 'vue-router'

import {
  LayoutDashboard,
  Inbox,
  Send,
  FileText,
  Users,
  ShieldCheck,
  Activity,
  LogOut,
  Menu,
  X,
  ChevronRight,
  Shield,
  Clock
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

const mobileMenuOpen = ref(false)
const isLoggingOut = ref(false)
const utilisateur = ref(null)

const heureActuelle = ref('')
let horloge = null

// =========================================================
// HEURE ACTUELLE
// =========================================================
const actualiserHeure = () => {
  heureActuelle.value = new Date().toLocaleTimeString(
    'fr-FR',
    {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    }
  )
}

// =========================================================
// CHARGEMENT UTILISATEUR
// =========================================================
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

// Un seul onMounted
onMounted(() => {
  chargerUtilisateur()
  actualiserHeure()

  horloge = setInterval(actualiserHeure, 1000)
})

onBeforeUnmount(() => {
  if (horloge) {
    clearInterval(horloge)
    horloge = null
  }
})

// =========================================================
// INFORMATIONS UTILISATEUR
// =========================================================
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

// =========================================================
// MENUS
// =========================================================
const menuItems = [
  {
    label: 'Tableau de bord',
    icon: LayoutDashboard,
    route: '/dashboard'
  },
  {
    label: 'Courriers arrivés',
    icon: Inbox,
    route: '/courriers-arrives'
  },
  {
    label: 'Courriers départ',
    icon: Send,
    route: '/courriers-depart'
  },
  {
    label: 'Documents numériques',
    icon: FileText,
    route: '/documents'
  }
]

const adminItems = [
  {
    label: 'Utilisateurs',
    icon: Users,
    route: '/utilisateurs'
  },
  {
    label: 'Rôles & permissions',
    icon: ShieldCheck,
    route: '/roles'
  },
  {
    label: 'Journal des activités',
    icon: Activity,
    route: '/journal'
  }
]

// =========================================================
// ROUTES ACTIVES
// =========================================================
const isActiveRoute = (itemRoute) => {
  return (
    route.path === itemRoute ||
    route.path.startsWith(`${itemRoute}/`)
  )
}

const currentPageIcon = computed(() => {
  const allItems = [
    ...menuItems,
    ...adminItems
  ]

  const currentItem = allItems.find((item) =>
    isActiveRoute(item.route)
  )

  return currentItem?.icon || LayoutDashboard
})

// =========================================================
// MENU MOBILE
// =========================================================
const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

// =========================================================
// DÉCONNEXION
// =========================================================
const logout = async () => {
  if (isLoggingOut.value) {
    return
  }

  isLoggingOut.value = true

  const token =
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token')

  try {
    if (token) {
      await fetch(
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

    <!-- Overlay mobile -->
    <div
      v-if="mobileMenuOpen"
      class="mobile-overlay"
      @click="closeMobileMenu"
    ></div>

    <!-- ===================================================
         SIDEBAR
    ==================================================== -->
    <aside
      class="sidebar"
      :class="{
        'sidebar-open': mobileMenuOpen
      }"
    >
      <div class="sidebar-header">

        <div class="brand">
          <div class="logo">
            <Shield
              :size="22"
              :stroke-width="2.4"
            />
          </div>

          <div class="brand-text">
            <strong>GENDARMERIE</strong>
            <span>NATIONALE</span>
          </div>
        </div>

        <button
          type="button"
          class="mobile-close"
          aria-label="Fermer le menu"
          @click="closeMobileMenu"
        >
          <X :size="20" />
        </button>
      </div>

      <nav class="navigation">

        <div class="nav-section-title">
          PRINCIPAL
        </div>

        <router-link
          v-for="item in menuItems"
          :key="item.route"
          :to="item.route"
          class="nav-item"
          :class="{
            active: isActiveRoute(item.route)
          }"
          @click="closeMobileMenu"
        >
          <span class="nav-icon">
            <component
              :is="item.icon"
              :size="18"
              :stroke-width="2"
            />
          </span>

          <span class="nav-label">
            {{ item.label }}
          </span>

          <ChevronRight
            v-if="isActiveRoute(item.route)"
            class="nav-arrow"
            :size="15"
          />
        </router-link>

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
          :class="{
            active: isActiveRoute(item.route)
          }"
          @click="closeMobileMenu"
        >
          <span class="nav-icon">
            <component
              :is="item.icon"
              :size="18"
              :stroke-width="2"
            />
          </span>

          <span class="nav-label">
            {{ item.label }}
          </span>

          <ChevronRight
            v-if="isActiveRoute(item.route)"
            class="nav-arrow"
            :size="15"
          />
        </router-link>
      </nav>

      <!-- Utilisateur et déconnexion -->
      <div class="sidebar-bottom">

        <div class="sidebar-user">
          <div class="avatar">
            {{ initiales }}
          </div>

          <div class="sidebar-user-info">
            <strong>
              {{ nomComplet }}
            </strong>

            <span>
              <ShieldCheck
                :size="11"
                :stroke-width="2.3"
              />
              {{ role }}
            </span>
          </div>
        </div>

        <button
          type="button"
          class="sidebar-logout"
          :disabled="isLoggingOut"
          @click="logout"
        >
          <LogOut
            :size="16"
            :stroke-width="2"
          />

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

    <!-- ===================================================
         CONTENU PRINCIPAL
    ==================================================== -->
    <main class="main-content">

      <!-- Barre supérieure -->
      <header class="topbar">

        <!-- Bouton menu mobile -->
        <button
          type="button"
          class="menu-toggle"
          aria-label="Ouvrir le menu"
          @click="mobileMenuOpen = true"
        >
          <Menu :size="20" />
        </button>

        <!-- Heure + DIST/SEMF -->
        <div class="topbar-title">
          <div class="topbar-system-info">

            <div class="system-clock">
              <span class="clock-icon">
                <Clock :size="18" />
              </span>

              <span class="clock-time">
                {{ heureActuelle }}
              </span>
            </div>

            <div class="system-divider"></div>

            <div class="system-name">
              <span class="system-label">
                DIST / SEMF
              </span>

              <span class="system-description">
                Gestion des courriers et documents administratifs
              </span>
            </div>

          </div>
        </div>

        <!-- Profil utilisateur -->
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

      <!-- Page affichée -->
      <div class="page-content">
        <router-view />
      </div>

    </main>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

/* =========================================================
   LAYOUT GLOBAL
========================================================= */
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

/* =========================================================
   SIDEBAR
========================================================= */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 270px;

  display: flex;
  flex-direction: column;

  background:
    linear-gradient(
      180deg,
      #061c36 0%,
      #08264d 100%
    );

  color: white;
  z-index: 1000;

  box-shadow:
    5px 0 25px
    rgba(8, 38, 77, 0.14);

  transition:
    transform 0.25s ease;
}

.sidebar-header {
  height: 82px;
  display: flex;
  align-items: center;
  padding: 0 20px;

  border-bottom:
    1px solid
    rgba(255, 255, 255, 0.08);
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo {
  width: 43px;
  height: 43px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;
  border-radius: 12px;

  background: #d5b45c;
  color: #08264d;

  box-shadow:
    0 5px 15px
    rgba(0, 0, 0, 0.15);
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-text strong {
  color: white;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.8px;
}

.brand-text span {
  margin-top: 3px;
  color: #d5b45c;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 2px;
}

/* =========================================================
   NAVIGATION
========================================================= */
.navigation {
  flex: 1;
  padding: 20px 13px;
  overflow-y: auto;
}

.navigation::-webkit-scrollbar {
  width: 4px;
}

.navigation::-webkit-scrollbar-thumb {
  background:
    rgba(255, 255, 255, 0.12);
  border-radius: 20px;
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

.nav-item {
  position: relative;
  min-height: 45px;

  display: flex;
  align-items: center;
  gap: 12px;

  margin: 3px 0;
  padding: 10px 12px;

  border-radius: 9px;
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
    rgba(255, 255, 255, 0.075);

  color: white;
  transform: translateX(2px);
}

.nav-item.active {
  background:
    linear-gradient(
      90deg,
      #d5b45c,
      #e2c77b
    );

  color: #08264d;
  font-weight: 800;

  box-shadow:
    0 6px 16px
    rgba(0, 0, 0, 0.13);
}

.nav-icon {
  width: 22px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;
}

.nav-label {
  flex: 1;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.nav-arrow {
  flex-shrink: 0;
}

/* =========================================================
   SIDEBAR USER
========================================================= */
.sidebar-bottom {
  padding: 15px;

  border-top:
    1px solid
    rgba(255, 255, 255, 0.08);
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;

  margin-bottom: 12px;
  padding: 8px;

  border-radius: 10px;

  background:
    rgba(255, 255, 255, 0.045);
}

.avatar {
  width: 39px;
  height: 39px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;
  border-radius: 50%;

  background: #d5b45c;
  color: #08264d;

  font-size: 11px;
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
  display: flex;
  align-items: center;
  gap: 4px;

  margin-top: 3px;
  color: #9fb3c8;
  font-size: 9px;
}

/* =========================================================
   BOUTON DÉCONNEXION
========================================================= */
.sidebar-logout {
  width: 100%;
  min-height: 40px;

  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;

  border: 1px solid #ffffff;
  border-radius: 8px;

  background: rgb(217, 13, 13);
  color: white;

  cursor: pointer;
  font-size: 11px;
  font-weight: 700;

  transition:
    background 0.2s ease,
    transform 0.2s ease;
}

.sidebar-logout:hover {
  background: #b91c1c;
  transform: translateY(-1px);
}

.sidebar-logout:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* =========================================================
   CONTENU PRINCIPAL
========================================================= */
.main-content {
  min-height: 100vh;
  margin-left: 270px;
}

/* =========================================================
   TOPBAR
========================================================= */
.topbar {
  position: sticky;
  top: 0;
  z-index: 900;

  min-height: 82px;

  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 0 32px;

  background:
    rgba(255, 255, 255, 0.96);

  backdrop-filter: blur(10px);

  border-bottom:
    1px solid #e5eaf0;
}

.topbar-title {
  min-width: 0;
}

/* =========================================================
   HEURE + DIST / SEMF
   CSS placé hors des media queries
========================================================= */
.topbar-system-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.system-clock {
  display: flex;
  align-items: center;
  gap: 9px;
  color: #08264d;
}

.clock-icon {
  width: 36px;
  height: 36px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;
  border-radius: 10px;

  background: #eef3f8;
  color: #08264d;
}

.clock-time {
  color: #08264d;
  font-size: 17px;
  font-weight: 900;
  font-variant-numeric: tabular-nums;
  letter-spacing: 0.5px;
}

.system-divider {
  width: 1px;
  height: 35px;
  flex-shrink: 0;
  background: #dce4ed;
}

.system-name {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.system-label {
  color: #08264d;
  font-size: 16px;
  font-weight: 900;
  letter-spacing: 1.2px;
}

.system-description {
  color: #829ab1;
  font-size: 10px;
}

/* =========================================================
   UTILISATEUR TOPBAR
========================================================= */
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

  flex-shrink: 0;
  border-radius: 50%;

  background:
    linear-gradient(
      135deg,
      #eef3f8,
      #dfe8f1
    );

  color: #08264d;
  font-size: 12px;
  font-weight: 900;

  border: 2px solid #d5b45c;
}

/* =========================================================
   BOUTON MENU MOBILE
========================================================= */
.menu-toggle {
  display: none;

  width: 38px;
  height: 38px;

  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border: 1px solid #e0e6ec;
  border-radius: 8px;

  background: white;
  color: #08264d;

  cursor: pointer;
}

/* =========================================================
   PAGE CONTENT
========================================================= */
.page-content {
  width: 100%;
  min-height: calc(100vh - 82px);
}

/* =========================================================
   BOUTON FERMER MENU MOBILE
========================================================= */
.mobile-close {
  display: none;

  margin-left: auto;

  width: 31px;
  height: 31px;

  align-items: center;
  justify-content: center;

  border: 0;
  border-radius: 7px;

  background:
    rgba(255, 255, 255, 0.06);

  color: white;
  cursor: pointer;
}

.mobile-overlay {
  display: none;
}

/* =========================================================
   RESPONSIVE : TABLETTE
========================================================= */
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

/* =========================================================
   RESPONSIVE : MOBILE
========================================================= */
@media (max-width: 800px) {
  .sidebar {
    width: 270px;

    transform: translateX(-100%);

    box-shadow:
      10px 0 35px
      rgba(0, 0, 0, 0.18);
  }

  .sidebar.sidebar-open {
    transform: translateX(0);
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
    display: flex;
  }

  .topbar-title {
    flex: 1;
    min-width: 0;
  }

  .topbar-system-info {
    gap: 10px;
  }

  .clock-time {
    font-size: 14px;
  }

  .system-label {
    font-size: 13px;
  }

  .system-description {
    font-size: 9px;
  }

  .topbar-user-info {
    display: none;
  }

  .topbar-avatar {
    width: 38px;
    height: 38px;
  }

  .mobile-close {
    display: flex;
  }

  .mobile-overlay {
    position: fixed;
    inset: 0;

    display: block;

    background:
      rgba(0, 0, 0, 0.45);

    z-index: 999;

    backdrop-filter: blur(2px);
  }

  .page-content {
    min-height: calc(100vh - 70px);
  }
}

/* =========================================================
   RESPONSIVE : PETIT MOBILE
========================================================= */
@media (max-width: 600px) {
  .topbar {
    padding: 0 14px;
  }

  .clock-icon {
    width: 33px;
    height: 33px;
  }

  .clock-time {
    font-size: 13px;
  }

  .system-label {
    font-size: 12px;
  }

  .system-description {
    font-size: 8px;
  }
}

/* =========================================================
   RESPONSIVE : TRÈS PETIT ÉCRAN
========================================================= */
@media (max-width: 380px) {
  .brand-text strong {
    font-size: 11px;
  }

  .brand-text span {
    font-size: 8px;
  }

  .sidebar {
    width: 255px;
  }

  .system-description {
    display: none;
  }

  .topbar-system-info {
    gap: 8px;
  }

  .system-divider {
    height: 28px;
  }

  .clock-time {
    font-size: 12px;
  }

  .system-label {
    font-size: 11px;
    letter-spacing: 0.7px;
  }
}

/* =========================================================
   ACCESSIBILITÉ
========================================================= */
button:focus-visible,
a:focus-visible {
  outline:
    3px solid
    rgba(213, 180, 92, 0.5);

  outline-offset: 2px;
}
</style>