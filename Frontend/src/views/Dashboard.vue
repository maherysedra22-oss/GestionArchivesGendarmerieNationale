<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  Inbox,
  Send,
  FileText,
  Users,
  Activity,
  Plus,
  ArrowRight,
  RefreshCw,
  AlertTriangle,
  Archive,
  Clock3,
  BarChart3,
  CheckCircle2,
  ShieldCheck,
  Database,
  Eye,
  Pencil,
  Trash2,
  LogIn,
  XCircle,
  LoaderCircle,
  UserPlus
} from 'lucide-vue-next'

const router = useRouter()

// ======================================================
// CONFIGURATION API
// ======================================================

const API_BASE = 'http://127.0.0.1:8000/api'

// ======================================================
// UTILISATEUR CONNECTÉ
// ======================================================

const utilisateur = ref(null)

function chargerUtilisateurLocal() {
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

const initials = computed(() => {
  const first = prenom.value?.charAt(0) || ''
  const last = nom.value?.charAt(0) || ''

  return `${first}${last}`.toUpperCase() || 'U'
})

// ======================================================
// ÉTAT DASHBOARD
// ======================================================

const loading = ref(false)
const refreshing = ref(false)
const errorMessage = ref('')

const stats = ref({
  courriersArrives: 0,
  courriersDepart: 0,
  documents: 0,
  utilisateurs: 0,
  urgents: 0,
  tresUrgents: 0,
  archives: 0
})

const activites = ref([])

// ======================================================
// TOKEN
// ======================================================

function getToken() {
  return (
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token')
  )
}

// ======================================================
// DÉCONNEXION AUTOMATIQUE SI 401
// ======================================================

function gererNonAuthentifie() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('utilisateur')

  sessionStorage.removeItem('auth_token')
  sessionStorage.removeItem('utilisateur')

  utilisateur.value = null

  router.push('/login')
}

// ======================================================
// CHARGER LE DASHBOARD
// ======================================================

async function chargerDashboard(options = {}) {
  const isRefresh = options.refresh === true

  if (isRefresh) {
    refreshing.value = true
  } else {
    loading.value = true
  }

  errorMessage.value = ''

  try {
    const token = getToken()

    if (!token) {
      gererNonAuthentifie()
      return
    }

    const response = await fetch(`${API_BASE}/dashboard`, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    })

    if (response.status === 401) {
      gererNonAuthentifie()
      return
    }

    const result = await response.json().catch(() => null)

    if (!response.ok) {
      throw new Error(
        result?.message ||
        `Erreur serveur (${response.status})`
      )
    }

    const data = result?.data || {}

    const statistiques = data?.statistiques || {}

    stats.value = {
      courriersArrives:
        Number(statistiques.courriers_arrives) || 0,

      courriersDepart:
        Number(statistiques.courriers_depart) || 0,

      documents:
        Number(statistiques.documents) || 0,

      utilisateurs:
        Number(statistiques.utilisateurs) || 0,

      urgents:
        Number(statistiques.urgents) || 0,

      tresUrgents:
        Number(statistiques.tres_urgents) || 0,

      archives:
        Number(statistiques.archives) || 0
    }

    activites.value = Array.isArray(data?.activites)
      ? data.activites
      : []

  } catch (error) {
    console.error(
      'Erreur chargement dashboard :',
      error
    )

    errorMessage.value =
      error?.message ||
      'Impossible de charger les données du tableau de bord.'
  } finally {
    loading.value = false
    refreshing.value = false
  }
}

// ======================================================
// ACTUALISER
// ======================================================

async function actualiserDashboard() {
  await chargerDashboard({
    refresh: true
  })
}

// ======================================================
// ACCÈS RAPIDES
// ======================================================

function allerVers(route) {
  router.push(route)
}

// ======================================================
// DATE ACTUELLE
// ======================================================

const dateAujourdHui = computed(() => {
  return new Intl.DateTimeFormat('fr-FR', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  }).format(new Date())
})

// ======================================================
// STATISTIQUES COMPLÉMENTAIRES
// ======================================================

const totalUrgents = computed(() => {
  return (
    stats.value.urgents +
    stats.value.tresUrgents
  )
})

// ======================================================
// FORMAT DATE ACTIVITÉ
// ======================================================

function formatDate(date) {
  if (!date) {
    return 'Date inconnue'
  }

  const parsedDate = new Date(date)

  if (Number.isNaN(parsedDate.getTime())) {
    return date
  }

  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(parsedDate)
}

// ======================================================
// ACTION ACTIVITÉ
// ======================================================

function normaliserAction(action) {
  if (action === null || action === undefined) {
    return ''
  }

  if (typeof action === 'string') {
    try {
      const parsed = JSON.parse(action)

      if (typeof parsed === 'string') {
        return parsed
      }

      if (parsed && typeof parsed === 'object') {
        return (
          parsed.action ||
          parsed.libelle ||
          parsed.description ||
          JSON.stringify(parsed)
        )
      }
    } catch {
      return action
    }

    return action
  }

  if (typeof action === 'object') {
    return (
      action.action ||
      action.libelle ||
      action.description ||
      JSON.stringify(action)
    )
  }

  return String(action)
}

function getActionText(action) {
  const value = normaliserAction(action)

  return value || 'Opération effectuée'
}

// ======================================================
// ICÔNE ACTIVITÉ
// ======================================================

function activityIcon(action) {
  const value = normaliserAction(action).toLowerCase()

  if (
    value.includes('connexion') ||
    value.includes('login') ||
    value.includes('connect')
  ) {
    return LogIn
  }

  if (
    value.includes('suppression') ||
    value.includes('supprim') ||
    value.includes('delete')
  ) {
    return Trash2
  }

  if (
    value.includes('modification') ||
    value.includes('modifi') ||
    value.includes('update')
  ) {
    return Pencil
  }

  if (
    value.includes('création') ||
    value.includes('creation') ||
    value.includes('ajout') ||
    value.includes('create')
  ) {
    return Plus
  }

  if (
    value.includes('lecture') ||
    value.includes('consult') ||
    value.includes('visual')
  ) {
    return Eye
  }

  if (
    value.includes('archive') ||
    value.includes('archivé') ||
    value.includes('archiv')
  ) {
    return Archive
  }

  return Activity
}

// ======================================================
// CLASSE ACTIVITÉ
// ======================================================

function activityClass(action) {
  const value = normaliserAction(action).toLowerCase()

  if (
    value.includes('suppression') ||
    value.includes('supprim') ||
    value.includes('delete')
  ) {
    return 'activity-danger'
  }

  if (
    value.includes('modification') ||
    value.includes('modifi') ||
    value.includes('update')
  ) {
    return 'activity-warning'
  }

  if (
    value.includes('création') ||
    value.includes('creation') ||
    value.includes('ajout') ||
    value.includes('create')
  ) {
    return 'activity-success'
  }

  if (
    value.includes('connexion') ||
    value.includes('login') ||
    value.includes('connect')
  ) {
    return 'activity-info'
  }

  return 'activity-default'
}

// ======================================================
// MOUNT
// ======================================================

onMounted(async () => {
  chargerUtilisateurLocal()
  await chargerDashboard()
})
</script>

<template>
  <div class="dashboard-content">

    <!-- ==================================================
         HEADER DASHBOARD
    =================================================== -->

    <section class="dashboard-header">

      <div class="dashboard-title">

        <div class="dashboard-title-icon">
          <BarChart3 :size="20" />
        </div>

        <div>
          <h1>
            Tableau de bord
          </h1>

          <p>
            Vue générale du système de gestion
          </p>
        </div>

      </div>

      <div class="dashboard-header-right">

        <div class="current-date">
          <Clock3 :size="14" />
          <span>
            {{ dateAujourdHui }}
          </span>
        </div>

        <button
          class="refresh-button"
          type="button"
          :disabled="refreshing || loading"
          @click="actualiserDashboard"
        >
          <RefreshCw
            :size="15"
            :class="{ spinning: refreshing }"
          />

          <span>
            Actualiser
          </span>
        </button>

      </div>

    </section>


    <!-- ==================================================
         WELCOME
    =================================================== -->

    <section class="welcome-card">

      <div class="welcome-content">

        <div class="welcome-label">
          ESPACE {{ role.toUpperCase() }}
        </div>

        <h2>
          Bienvenue,
          <span>{{ prenom }}</span>
        </h2>

        <p>
          Vous êtes connecté au système de gestion
          des archives administratives de la
          Gendarmerie Nationale.
        </p>

        <div class="welcome-meta">

          <div class="account-status">
            <span class="status-dot"></span>
            Compte actif
          </div>

          <div class="grade-badge">
            {{ grade }}
          </div>

        </div>

      </div>


      <div class="welcome-emblem">

        <div class="emblem-circle">
          {{ initials }}
        </div>

        <span>
          GENDARMERIE<br />
          NATIONALE
        </span>

      </div>

    </section>


    <!-- ==================================================
         ERREUR
    =================================================== -->

    <div
      v-if="errorMessage"
      class="dashboard-error"
    >

      <div class="dashboard-error-icon">
        <XCircle :size="19" />
      </div>

      <div class="dashboard-error-content">

        <strong>
          Erreur de chargement
        </strong>

        <span>
          {{ errorMessage }}
        </span>

      </div>

      <button
        type="button"
        @click="chargerDashboard()"
      >
        Réessayer
      </button>

    </div>


    <!-- ==================================================
         STATISTIQUES
    =================================================== -->

    <section class="section">

      <div class="section-heading">

        <div>
          <h2>
            Vue générale
          </h2>

          <p>
            Aperçu de l'activité du système
          </p>
        </div>

      </div>


      <div class="statistics">

        <!-- COURRIERS ARRIVÉS -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon stat-icon-arrive">
              <Inbox :size="21" />
            </div>

            <span class="stat-badge">
              Arrivée
            </span>

          </div>

          <div
            v-if="loading"
            class="stat-loading"
          >
            <LoaderCircle :size="22" />
          </div>

          <div
            v-else
            class="stat-number"
          >
            {{ stats.courriersArrives }}
          </div>

          <h3>
            Courriers arrivés
          </h3>

          <p>
            Courriers reçus
          </p>

        </article>


        <!-- COURRIERS DÉPART -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon stat-icon-depart">
              <Send :size="21" />
            </div>

            <span class="stat-badge">
              Départ
            </span>

          </div>

          <div
            v-if="loading"
            class="stat-loading"
          >
            <LoaderCircle :size="22" />
          </div>

          <div
            v-else
            class="stat-number"
          >
            {{ stats.courriersDepart }}
          </div>

          <h3>
            Courriers départ
          </h3>

          <p>
            Courriers envoyés
          </p>

        </article>


        <!-- DOCUMENTS -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon stat-icon-document">
              <FileText :size="21" />
            </div>

            <span class="stat-badge">
              Archives
            </span>

          </div>

          <div
            v-if="loading"
            class="stat-loading"
          >
            <LoaderCircle :size="22" />
          </div>

          <div
            v-else
            class="stat-number"
          >
            {{ stats.documents }}
          </div>

          <h3>
            Documents numériques
          </h3>

          <p>
            Documents archivés
          </p>

        </article>


        <!-- UTILISATEURS -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon stat-icon-users">
              <Users :size="21" />
            </div>

            <span class="stat-badge">
              Comptes
            </span>

          </div>

          <div
            v-if="loading"
            class="stat-loading"
          >
            <LoaderCircle :size="22" />
          </div>

          <div
            v-else
            class="stat-number"
          >
            {{ stats.utilisateurs }}
          </div>

          <h3>
            Utilisateurs
          </h3>

          <p>
            Comptes enregistrés
          </p>

        </article>

      </div>


      <!-- MINI STATISTIQUES -->

      <div class="mini-statistics">

        <div class="mini-stat">

          <div class="mini-stat-icon mini-danger">
            <AlertTriangle :size="17" />
          </div>

          <div>
            <strong>
              {{ stats.tresUrgents }}
            </strong>

            <span>
              Très urgents
            </span>
          </div>

        </div>


        <div class="mini-stat">

          <div class="mini-stat-icon mini-warning">
            <AlertTriangle :size="17" />
          </div>

          <div>
            <strong>
              {{ stats.urgents }}
            </strong>

            <span>
              Urgents
            </span>
          </div>

        </div>


        <div class="mini-stat">

          <div class="mini-stat-icon mini-info">
            <Activity :size="17" />
          </div>

          <div>
            <strong>
              {{ totalUrgents }}
            </strong>

            <span>
              Total prioritaires
            </span>
          </div>

        </div>


        <div class="mini-stat">

          <div class="mini-stat-icon mini-success">
            <Archive :size="17" />
          </div>

          <div>
            <strong>
              {{ stats.archives }}
            </strong>

            <span>
              Courriers archivés
            </span>
          </div>

        </div>

      </div>

    </section>


    <!-- ==================================================
         ACTIVITÉS + ACCÈS RAPIDES
    =================================================== -->

    <section class="dashboard-grid">


      <!-- ACTIVITÉS -->

      <div class="panel">

        <div class="panel-header">

          <div>
            <h2>
              Activités récentes
            </h2>

            <p>
              Dernières opérations effectuées
            </p>
          </div>

          <span class="panel-icon">
            <Activity :size="16" />
          </span>

        </div>


        <!-- LOADING -->

        <div
          v-if="loading"
          class="activity-loading"
        >
          <LoaderCircle
            :size="28"
            class="spinning"
          />

          <span>
            Chargement des activités...
          </span>
        </div>


        <!-- ACTIVITÉS -->

        <div
          v-else-if="activites.length"
          class="activities-list"
        >

          <div
            v-for="activite in activites"
            :key="activite.id"
            class="activity-item"
          >

            <div
              class="activity-icon"
              :class="activityClass(activite.action)"
            >

              <component
                :is="activityIcon(activite.action)"
                :size="16"
              />

            </div>


            <div class="activity-content">

              <strong>
                {{ getActionText(activite.action) }}
              </strong>

              <span>
                {{ activite.utilisateur || 'Utilisateur système' }}
              </span>

              <small>
                {{ formatDate(activite.date || activite.created_at) }}
              </small>

            </div>

          </div>

        </div>


        <!-- EMPTY -->

        <div
          v-else
          class="empty-state"
        >

          <div class="empty-state-icon">
            <Activity :size="20" />
          </div>

          <h3>
            Aucune activité récente
          </h3>

          <p>
            Les opérations effectuées dans le système
            apparaîtront ici.
          </p>

        </div>

      </div>


      <!-- ACCÈS RAPIDES -->

      <div class="panel">

        <div class="panel-header">

          <div>
            <h2>
              Accès rapides
            </h2>

            <p>
              Actions fréquentes
            </p>
          </div>

          <span class="panel-icon">
            <Plus :size="16" />
          </span>

        </div>


        <div class="quick-actions">


          <!-- COURRIER ARRIVÉ -->

          <button
            class="quick-action"
            type="button"
            @click="allerVers('/courriers-arrives')"
          >

            <span class="quick-icon">
              <Inbox :size="17" />
            </span>

            <span class="quick-text">

              <strong>
                Courrier arrivé
              </strong>

              <small>
                Enregistrer un courrier reçu
              </small>

            </span>

            <span class="quick-arrow">
              <ArrowRight :size="16" />
            </span>

          </button>


          <!-- COURRIER DÉPART -->

          <button
            class="quick-action"
            type="button"
            @click="allerVers('/courriers-depart')"
          >

            <span class="quick-icon">
              <Send :size="17" />
            </span>

            <span class="quick-text">

              <strong>
                Courrier départ
              </strong>

              <small>
                Enregistrer un courrier envoyé
              </small>

            </span>

            <span class="quick-arrow">
              <ArrowRight :size="16" />
            </span>

          </button>


          <!-- DOCUMENTS -->

          <button
            class="quick-action"
            type="button"
            @click="allerVers('/documents')"
          >

            <span class="quick-icon">
              <FileText :size="17" />
            </span>

            <span class="quick-text">

              <strong>
                Documents numériques
              </strong>

              <small>
                Consulter les documents archivés
              </small>

            </span>

            <span class="quick-arrow">
              <ArrowRight :size="16" />
            </span>

          </button>


          <!-- UTILISATEURS -->

          <button
            class="quick-action"
            type="button"
            @click="allerVers('/utilisateurs')"
          >

            <span class="quick-icon">
              <UserPlus :size="17" />
            </span>

            <span class="quick-text">

              <strong>
                Utilisateurs
              </strong>

              <small>
                Gérer les comptes utilisateurs
              </small>

            </span>

            <span class="quick-arrow">
              <ArrowRight :size="16" />
            </span>

          </button>

        </div>

      </div>

    </section>


    <!-- ==================================================
         INFORMATION
    =================================================== -->

    <section class="information">

      <div class="information-icon">
        <ShieldCheck :size="22" />
      </div>

      <div class="information-content">

        <h2>
          Système de gestion des archives
        </h2>

        <p>
          Cette plateforme permet de centraliser,
          organiser et consulter les courriers et
          documents administratifs de la Gendarmerie
          Nationale dans un environnement sécurisé.
        </p>

      </div>

      <div class="information-security">
        <Database :size="18" />
        <span>
          Système sécurisé
        </span>
      </div>

    </section>

  </div>
</template>


<style scoped>

/* ======================================================
   BASE
====================================================== */

.dashboard-content {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 30px;
}


/* ======================================================
   DASHBOARD HEADER
====================================================== */

.dashboard-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 22px;
}

.dashboard-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.dashboard-title-icon {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9px;
  background: #eaf1f8;
  color: #174d7d;
}

.dashboard-title h1 {
  margin: 0;
  color: #102a43;
  font-size: 20px;
  font-weight: 800;
}

.dashboard-title p {
  margin: 3px 0 0;
  color: #829ab1;
  font-size: 10px;
}

.dashboard-header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.current-date {
  display: flex;
  align-items: center;
  gap: 7px;
  color: #829ab1;
  font-size: 10px;
}

.refresh-button {
  height: 34px;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 0 12px;
  border: 1px solid #dce5ec;
  border-radius: 7px;
  background: white;
  color: #174d7d;
  font-size: 10px;
  font-weight: 700;
  cursor: pointer;
  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.refresh-button:hover:not(:disabled) {
  background: #f6f9fb;
  border-color: #c9d8e5;
  transform: translateY(-1px);
}

.refresh-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}


/* ======================================================
   WELCOME
====================================================== */

.welcome-card {
  min-height: 185px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 30px 34px;
  background:
    linear-gradient(
      120deg,
      #08264d 0%,
      #103c68 100%
    );
  border-radius: 13px;
  overflow: hidden;
  position: relative;
  box-shadow:
    0 10px 30px rgba(8, 38, 77, 0.13);
}

.welcome-card::after {
  content: "";
  position: absolute;
  width: 260px;
  height: 260px;
  right: -100px;
  top: -100px;
  border-radius: 50%;
  border: 1px solid rgba(213, 180, 92, 0.18);
}

.welcome-card::before {
  content: "";
  position: absolute;
  width: 170px;
  height: 170px;
  right: 90px;
  bottom: -130px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.07);
}

.welcome-content {
  position: relative;
  z-index: 2;
}

.welcome-label {
  color: #d5b45c;
  font-size: 9px;
  font-weight: 900;
  letter-spacing: 2px;
}

.welcome-card h2 {
  margin: 9px 0 7px;
  color: white;
  font-size: 28px;
  font-weight: 800;
}

.welcome-card h2 span {
  color: #d5b45c;
}

.welcome-card p {
  max-width: 650px;
  margin: 0;
  color: #b9c9da;
  font-size: 12px;
  line-height: 1.7;
}


/* ======================================================
   WELCOME META
====================================================== */

.welcome-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 18px;
}

.account-status,
.grade-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 11px;
  border-radius: 20px;
  font-size: 9px;
  font-weight: 700;
}

.account-status {
  background: rgba(255, 255, 255, 0.09);
  color: #dbe7f2;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #42c875;
  box-shadow:
    0 0 0 4px rgba(66, 200, 117, 0.12);
}

.grade-badge {
  background: rgba(213, 180, 92, 0.13);
  color: #e6ca7c;
}


/* ======================================================
   EMBLÈME
====================================================== */

.welcome-emblem {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  margin-right: 25px;
}

.emblem-circle {
  width: 90px;
  height: 90px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #d5b45c;
  color: #08264d;
  border: 5px solid rgba(255, 255, 255, 0.85);
  font-size: 23px;
  font-weight: 900;
  box-shadow:
    0 8px 25px rgba(0, 0, 0, 0.2);
}

.welcome-emblem > span {
  color: #d5b45c;
  font-size: 7px;
  font-weight: 800;
  text-align: center;
  letter-spacing: 1px;
  line-height: 1.5;
}


/* ======================================================
   ERROR
====================================================== */

.dashboard-error {
  display: flex;
  align-items: center;
  gap: 11px;
  margin-top: 18px;
  padding: 12px 14px;
  border: 1px solid #f2d4d4;
  border-radius: 8px;
  background: #fff7f7;
}

.dashboard-error-icon {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 7px;
  background: #fde8e8;
  color: #c0392b;
}

.dashboard-error-content {
  min-width: 0;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.dashboard-error-content strong {
  color: #842029;
  font-size: 10px;
}

.dashboard-error-content span {
  margin-top: 2px;
  color: #9b5555;
  font-size: 9px;
}

.dashboard-error button {
  border: none;
  background: transparent;
  color: #a33131;
  font-size: 9px;
  font-weight: 800;
  cursor: pointer;
}


/* ======================================================
   SECTION
====================================================== */

.section {
  margin-top: 28px;
}

.section-heading {
  margin-bottom: 14px;
}

.section-heading h2 {
  margin: 0;
  color: #102a43;
  font-size: 17px;
}

.section-heading p {
  margin: 4px 0 0;
  color: #829ab1;
  font-size: 10px;
}


/* ======================================================
   STATISTICS
====================================================== */

.statistics {
  display: grid;
  grid-template-columns:
    repeat(4, minmax(0, 1fr));
  gap: 17px;
}

.stat-card {
  padding: 19px;
  background: white;
  border: 1px solid #e4eaf0;
  border-radius: 10px;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow:
    0 10px 25px rgba(8, 38, 77, 0.08);
}

.stat-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stat-icon {
  width: 43px;
  height: 43px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.stat-icon-arrive {
  background: #eaf1f8;
  color: #174d7d;
}

.stat-icon-depart {
  background: #edf7f3;
  color: #237a57;
}

.stat-icon-document {
  background: #f2effa;
  color: #654e9e;
}

.stat-icon-users {
  background: #fff5e6;
  color: #a36a17;
}

.stat-badge {
  padding: 5px 8px;
  border-radius: 12px;
  background: #f4f7fa;
  color: #829ab1;
  font-size: 8px;
  font-weight: 700;
}

.stat-number {
  margin-top: 18px;
  color: #08264d;
  font-size: 28px;
  font-weight: 900;
}

.stat-loading {
  height: 34px;
  display: flex;
  align-items: center;
  margin-top: 18px;
  color: #829ab1;
}

.stat-card h3 {
  margin: 3px 0 0;
  color: #243b53;
  font-size: 11px;
}

.stat-card p {
  margin: 5px 0 0;
  color: #9aa9b8;
  font-size: 9px;
}


/* ======================================================
   MINI STATISTICS
====================================================== */

.mini-statistics {
  display: grid;
  grid-template-columns:
    repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-top: 13px;
}

.mini-stat {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 13px;
  background: white;
  border: 1px solid #e4eaf0;
  border-radius: 9px;
}

.mini-stat-icon {
  width: 35px;
  height: 35px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 7px;
}

.mini-danger {
  background: #fdecec;
  color: #bd3e3e;
}

.mini-warning {
  background: #fff5df;
  color: #a66a12;
}

.mini-info {
  background: #eaf1f8;
  color: #174d7d;
}

.mini-success {
  background: #eaf7f0;
  color: #268158;
}

.mini-stat > div:last-child {
  display: flex;
  flex-direction: column;
}

.mini-stat strong {
  color: #102a43;
  font-size: 15px;
  font-weight: 900;
}

.mini-stat span {
  margin-top: 2px;
  color: #829ab1;
  font-size: 8px;
}


/* ======================================================
   DASHBOARD GRID
====================================================== */

.dashboard-grid {
  display: grid;
  grid-template-columns:
    1.35fr 1fr;
  gap: 18px;
  margin-top: 20px;
}

.panel {
  min-height: 270px;
  padding: 22px;
  background: white;
  border: 1px solid #e4eaf0;
  border-radius: 10px;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  padding-bottom: 15px;
  border-bottom: 1px solid #edf1f5;
}

.panel-header h2 {
  margin: 0;
  color: #102a43;
  font-size: 14px;
}

.panel-header p {
  margin: 4px 0 0;
  color: #829ab1;
  font-size: 9px;
}

.panel-icon {
  width: 31px;
  height: 31px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 7px;
  background: #eaf1f8;
  color: #174d7d;
}


/* ======================================================
   ACTIVITÉS
====================================================== */

.activities-list {
  max-height: 250px;
  overflow-y: auto;
  padding-top: 6px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 11px 3px;
  border-bottom: 1px solid #f0f3f6;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 33px;
  height: 33px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.activity-default {
  background: #eaf1f8;
  color: #174d7d;
}

.activity-success {
  background: #eaf7f0;
  color: #268158;
}

.activity-warning {
  background: #fff5df;
  color: #a66a12;
}

.activity-danger {
  background: #fdecec;
  color: #bd3e3e;
}

.activity-info {
  background: #eef3fb;
  color: #426ca8;
}

.activity-content {
  min-width: 0;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.activity-content strong {
  overflow: hidden;
  color: #243b53;
  font-size: 10px;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.activity-content span {
  margin-top: 3px;
  color: #829ab1;
  font-size: 8px;
}

.activity-content small {
  margin-top: 3px;
  color: #a7b3bf;
  font-size: 8px;
}

.activity-loading {
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 9px;
  color: #829ab1;
  font-size: 9px;
}


/* ======================================================
   EMPTY STATE
====================================================== */

.empty-state {
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.empty-state-icon {
  width: 43px;
  height: 43px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #f1f5f8;
  color: #829ab1;
}

.empty-state h3 {
  margin: 10px 0 4px;
  color: #486581;
  font-size: 12px;
}

.empty-state p {
  max-width: 270px;
  margin: 0;
  color: #9aa9b8;
  font-size: 9px;
  line-height: 1.6;
}


/* ======================================================
   QUICK ACTIONS
====================================================== */

.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 9px;
  margin-top: 15px;
}

.quick-action {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border: 1px solid #e7edf2;
  border-radius: 7px;
  background: white;
  color: #243b53;
  text-align: left;
  cursor: pointer;
  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.quick-action:hover {
  background: #f8fafc;
  border-color: #d5b45c;
  transform: translateX(2px);
}

.quick-icon {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  background: #eaf1f8;
  color: #174d7d;
}

.quick-text {
  min-width: 0;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.quick-text strong {
  font-size: 10px;
}

.quick-text small {
  margin-top: 3px;
  color: #829ab1;
  font-size: 8px;
}

.quick-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9aa9b8;
}


/* ======================================================
   INFORMATION
====================================================== */

.information {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-top: 20px;
  padding: 20px;
  border-radius: 10px;
  background: #08264d;
  color: white;
}

.information-icon {
  width: 45px;
  height: 45px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #d5b45c;
  color: #08264d;
}

.information-content {
  flex: 1;
}

.information-content h2 {
  margin: 0;
  font-size: 14px;
}

.information-content p {
  max-width: 800px;
  margin: 5px 0 0;
  color: #aebfd0;
  font-size: 9px;
  line-height: 1.7;
}

.information-security {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  color: #b9c9da;
  font-size: 8px;
  white-space: nowrap;
}


/* ======================================================
   ANIMATION
====================================================== */

.spinning {
  animation: dashboard-spin 0.9s linear infinite;
}

@keyframes dashboard-spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}


/* ======================================================
   TABLET
====================================================== */

@media (max-width: 1100px) {

  .dashboard-content {
    padding: 25px;
  }

  .statistics {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .mini-statistics {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }

}


/* ======================================================
   MOBILE
====================================================== */

@media (max-width: 800px) {

  .dashboard-content {
    padding: 20px 16px;
  }

  .dashboard-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .dashboard-header-right {
    width: 100%;
    justify-content: space-between;
  }

  .welcome-card {
    padding: 25px;
    min-height: auto;
  }

  .welcome-emblem {
    display: none;
  }

  .information-security {
    display: none;
  }

}


/* ======================================================
   SMALL MOBILE
====================================================== */

@media (max-width: 600px) {

  .dashboard-content {
    padding: 16px 13px;
  }

  .dashboard-title h1 {
    font-size: 18px;
  }

  .current-date {
    max-width: 200px;
  }

  .current-date span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .welcome-card {
    padding: 22px 20px;
    border-radius: 10px;
  }

  .welcome-label {
    font-size: 8px;
  }

  .welcome-card h2 {
    font-size: 23px;
  }

  .welcome-card p {
    font-size: 10px;
  }

  .welcome-meta {
    align-items: flex-start;
    flex-direction: column;
  }

  .statistics {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .mini-statistics {
    grid-template-columns: 1fr;
    gap: 9px;
  }

  .stat-card {
    padding: 16px;
  }

  .stat-number {
    font-size: 25px;
  }

  .dashboard-grid {
    gap: 13px;
  }

  .panel {
    padding: 17px;
    min-height: auto;
  }

  .information {
    align-items: flex-start;
    padding: 17px;
  }

  .information-icon {
    width: 38px;
    height: 38px;
  }

  .dashboard-error {
    align-items: flex-start;
  }

}


/* ======================================================
   VERY SMALL MOBILE
====================================================== */

@media (max-width: 380px) {

  .welcome-card h2 {
    font-size: 20px;
  }

  .quick-text small {
    display: none;
  }

  .refresh-button span {
    display: none;
  }

}
</style>