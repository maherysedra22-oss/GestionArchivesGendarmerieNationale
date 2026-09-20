```vue
<script setup>
import { ref, computed, onMounted } from 'vue'
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
  Shield
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

const mobileMenuOpen = ref(false)
const isLoggingOut = ref(false)

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
    Roles: 'Rôles & permissions',
    Journal: 'Journal des activités'
  }

  return titres[route.name] || 'Gestion des archives'
})

const sousTitrePage = computed(() => {
  const sousTitres = {
    Dashboard: 'Gestion des archives administratives',
    ChangePassword: 'Sécurité et protection de votre compte',
    CourriersArrives: 'Gestion des courriers reçus',
    CourriersDepart: 'Gestion des courriers envoyés',
    Documents: 'Gestion des documents numériques',
    Destinations: 'Gestion des destinations des courriers',
    Classements: 'Gestion des classements administratifs',
    Utilisateurs: 'Gestion des comptes utilisateurs',
    Roles: 'Gestion des rôles et des permissions',
    Journal: 'Suivi des activités du système'
  }

  return (
    sousTitres[route.name] ||
    'Système de gestion des archives'
  )
})

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

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

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
    <div
      v-if="mobileMenuOpen"
      class="mobile-overlay"
      @click="closeMobileMenu"
    ></div>

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

        <div class="nav-section-title administration-title">
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

    <main class="main-content">
      <header class="topbar">
        <button
          type="button"
          class="menu-toggle"
          aria-label="Ouvrir le menu"
          @click="mobileMenuOpen = true"
        >
          <Menu :size="20" />
        </button>

        <div class="topbar-title">
          <div class="title-with-icon">
            <div class="page-title-icon">
              <component
                :is="currentPageIcon"
                :size="19"
                :stroke-width="2"
              />
            </div>

            <div>
              <h1>
                {{ titrePage }}
              </h1>

              <p>
                {{ sousTitrePage }}
              </p>
            </div>
          </div>
        </div>

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
  transform:
    translateX(2px);
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

.sidebar-logout {
  width: 100%;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border:
    1px solid
    rgba(255, 255, 255, 0.14);
  border-radius: 8px;
  background:
    rgba(255, 255, 255, 0.06);
  color: white;
  cursor: pointer;
  font-size: 11px;
  font-weight: 700;
  transition:
    background 0.2s ease,
    transform 0.2s ease;
}

.sidebar-logout:hover {
  background:
    rgba(255, 255, 255, 0.12);
  transform:
    translateY(-1px);
}

.sidebar-logout:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.main-content {
  min-height: 100vh;
  margin-left: 270px;
}

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
  backdrop-filter:
    blur(10px);
  border-bottom:
    1px solid #e5eaf0;
}

.topbar-title {
  min-width: 0;
}

.title-with-icon {
  display: flex;
  align-items: center;
  gap: 11px;
}

.page-title-icon {
  width: 37px;
  height: 37px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border-radius: 9px;
  background: #eef3f8;
  color: #08264d;
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
  border:
    2px solid #d5b45c;
}

.menu-toggle {
  display: none;
  width: 38px;
  height: 38px;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border:
    1px solid #e0e6ec;
  border-radius: 8px;
  background: white;
  color: #08264d;
  cursor: pointer;
}

.page-content {
  width: 100%;
  min-height:
    calc(100vh - 82px);
}

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

@media (max-width: 800px) {
  .sidebar {
    width: 270px;
    transform:
      translateX(-100%);
    box-shadow:
      10px 0 35px
      rgba(0, 0, 0, 0.18);
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
    display: flex;
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
    display: flex;
  }

  .mobile-overlay {
    position: fixed;
    inset: 0;
    display: block;
    background:
      rgba(0, 0, 0, 0.45);
    z-index: 999;
    backdrop-filter:
      blur(2px);
  }

  .page-content {
    min-height:
      calc(100vh - 70px);
  }
}

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

  .page-title-icon {
    width: 33px;
    height: 33px;
  }
}

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

button:focus-visible,
a:focus-visible {
  outline:
    3px solid
    rgba(213, 180, 92, 0.5);
  outline-offset: 2px;
}
</style>
```
