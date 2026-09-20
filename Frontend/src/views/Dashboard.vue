<script>
export default {
  name: 'AppDashboard'
}
</script>
<script setup>
import {
  ref,
  computed,
  onMounted,
  onBeforeUnmount,
  nextTick,
} from 'vue'

import { useRouter } from 'vue-router'
import api from '@/api/api'

import {
  Inbox,
  Send,
  FileText,
  Users,
  Activity,
  Plus,
  RefreshCw,
  AlertTriangle,
  Archive,
  Clock3,
  ShieldCheck,
  Database,
  Eye,
  Pencil,
  Trash2,
  LogIn,
  XCircle,
  LoaderCircle,
  UserPlus,
  FolderArchive,
  CircleDot,
  TrendingUp,
  PieChart,
  LayoutDashboard,
  ChevronRight,
} from 'lucide-vue-next'

import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  DoughnutController,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  DoughnutController,
  Tooltip,
  Legend,
  Filler,
)

const router = useRouter()


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
      error,
    )

    utilisateur.value = null
  }
}


const prenom = computed(() => {
  return utilisateur.value?.prenom || 'Utilisateur'
})

const nom = computed(() => {
  return utilisateur.value?.nom || ''
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
  const first =
    prenom.value?.charAt(0) || ''

  const last =
    nom.value?.charAt(0) || ''

  const value =
    `${first}${last}`.toUpperCase()

  return value || 'U'
})


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

  /*
   * IMPORTANT :
   * archives =
   * arrivés avec statut ARCHIVÉ
   * +
   * courriers départ
   */
  archives: 0,

  /*
   * Nombre des arrivés réellement archivés.
   * Sert au graphique "État des courriers".
   */
  arrivesArchives: 0,

  enCours: 0,
  lecture: 0,
})


const evolution = ref({
  labels: [],
  arrives: [],
  depart: [],
})


const statuts = ref({
  enCours: 0,
  lecture: 0,
  archives: 0,
})


const priorites = ref({
  normal: 0,
  urgent: 0,
  tresUrgent: 0,
})


const activites = ref([])


const evolutionChartCanvas = ref(null)
const statutChartCanvas = ref(null)
const prioriteChartCanvas = ref(null)


let evolutionChart = null
let statutChart = null
let prioriteChart = null


async function chargerDashboard(options = {}) {
  const isRefresh =
    options.refresh === true

  if (isRefresh) {
    refreshing.value = true
  } else {
    loading.value = true
  }

  errorMessage.value = ''

  try {
    const result =
      await api.get('/dashboard')

    const data =
      result?.data || {}

    const statistiques =
      data?.statistiques || {}

    const courriersArrives =
      Number(
        statistiques.courriers_arrives,
      ) || 0


    const courriersDepart =
      Number(
        statistiques.courriers_depart,
      ) || 0


    const documents =
      Number(
        statistiques.documents,
      ) || 0


    const utilisateurs =
      Number(
        statistiques.utilisateurs,
      ) || 0


    const urgents =
      Number(
        statistiques.urgents,
      ) || 0


    const tresUrgents =
      Number(
        statistiques.tres_urgents,
      ) || 0


    const enCours =
      Number(
        data?.statuts?.en_cours ??
        statistiques.en_cours,
      ) || 0


    const lecture =
      Number(
        data?.statuts?.lecture ??
        statistiques.lecture,
      ) || 0


    /*
     * IMPORTANT :
     *
     * Ce nombre représente uniquement les
     * courriers ARRIVÉS avec statut ARCHIVÉ.
     *
     * On essaie d'abord data.statuts.archives,
     * puis statistiques.archives.
     */

    const arrivesArchives =
      Number(
        data?.statuts?.archives ??
        statistiques.archives,
      ) || 0


    /*
     * RÈGLE MÉTIER DU DASHBOARD :
     *
     * Courriers archivés =
     *
     * arrivés ARCHIVÉS
     * +
     * courriers DÉPART
     *
     * Exemple :
     * 2 arrivés archivés + 3 départ = 5
     */

    const totalArchives =
      arrivesArchives +
      courriersDepart


    stats.value = {
      courriersArrives,

      courriersDepart,

      documents,

      utilisateurs,

      urgents,

      tresUrgents,

      archives: totalArchives,

      arrivesArchives,

      enCours,

      lecture,
    }


    evolution.value = {
      labels:
        Array.isArray(
          data?.evolution?.labels,
        )
          ? data.evolution.labels
          : [],

      arrives:
        Array.isArray(
          data?.evolution?.arrives,
        )
          ? data.evolution.arrives.map(
              (value) =>
                Number(value) || 0,
            )
          : [],

      depart:
        Array.isArray(
          data?.evolution?.depart,
        )
          ? data.evolution.depart.map(
              (value) =>
                Number(value) || 0,
            )
          : [],
    }


    statuts.value = {
      enCours,

      lecture,

      /*
       * Ici on affiche uniquement les arrivés
       * ayant le statut ARCHIVÉ.
       *
       * Les courriers départ ne possèdent pas
       * ce statut dans cette logique métier.
       */

      archives: arrivesArchives,
    }


    priorites.value = {
      normal:
        Number(
          data?.priorites?.normal,
        ) || 0,

      urgent:
        Number(
          data?.priorites?.urgent ??
          statistiques.urgents,
        ) || 0,

      tresUrgent:
        Number(
          data?.priorites?.tres_urgent ??
          statistiques.tres_urgents,
        ) || 0,
    }



    activites.value =
      Array.isArray(data?.activites)
        ? data.activites
        : []


    /*
     * Attendre que Vue ait réellement rendu
     * les canvas avant Chart.js.
     */

    await nextTick()

    creerOuActualiserCharts()

  } catch (error) {
    console.error(
      'Erreur chargement dashboard :',
      error,
    )

    errorMessage.value =
      error?.response?.data?.message ||
      error?.message ||
      'Impossible de charger les données du tableau de bord.'

  } finally {
    loading.value = false
    refreshing.value = false
  }
}


async function actualiserDashboard() {
  await chargerDashboard({
    refresh: true,
  })
}


function allerVers(route) {
  router.push(route)
}


const dateAujourdHui = computed(() => {
  return new Intl.DateTimeFormat(
    'fr-FR',
    {
      weekday: 'long',
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    },
  ).format(new Date())
})


const totalUrgents = computed(() => {
  return (
    Number(stats.value.urgents || 0) +
    Number(stats.value.tresUrgents || 0)
  )
})


const totalCourriers = computed(() => {
  return (
    Number(stats.value.courriersArrives || 0) +
    Number(stats.value.courriersDepart || 0)
  )
})


/*
 * Taux d'archivage global :
 *
 * archives / total des courriers
 *
 * Exemple :
 * arrivés = 2
 * départ = 3
 * archives = 2 + 3 = 5
 * total = 2 + 3 = 5
 *
 * taux = 100%
 */

const tauxArchivage = computed(() => {
  const total =
    totalCourriers.value

  if (!total) {
    return 0
  }

  return Math.min(
    100,
    Math.max(
      0,
      Math.round(
        (Number(stats.value.archives || 0) /
          total) *
          100,
      ),
    ),
  )
})


/* ======================================================
   FORMAT DATE
====================================================== */

function formatDate(date) {
  if (!date) {
    return 'Date inconnue'
  }

  const parsedDate =
    new Date(date)

  if (
    Number.isNaN(
      parsedDate.getTime(),
    )
  ) {
    return date
  }

  return new Intl.DateTimeFormat(
    'fr-FR',
    {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    },
  ).format(parsedDate)
}


/* ======================================================
   NORMALISER ACTION
====================================================== */

function normaliserAction(action) {
  if (
    action === null ||
    action === undefined
  ) {
    return ''
  }

  if (typeof action === 'string') {
    try {
      const parsed =
        JSON.parse(action)

      if (
        typeof parsed === 'string'
      ) {
        return parsed
      }

      if (
        parsed &&
        typeof parsed === 'object'
      ) {
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

  if (
    typeof action === 'object'
  ) {
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
  const value =
    normaliserAction(action)

  return (
    value ||
    'Opération effectuée'
  )
}


/* ======================================================
   ICÔNE ACTIVITÉ
====================================================== */

function activityIcon(action) {
  const value =
    normaliserAction(action)
      .toLowerCase()

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


/* ======================================================
   CLASSE ACTIVITÉ
====================================================== */

function activityClass(action) {
  const value =
    normaliserAction(action)
      .toLowerCase()

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


/* ======================================================
   DESTROY CHARTS
====================================================== */

function detruireCharts() {
  if (evolutionChart) {
    evolutionChart.destroy()
    evolutionChart = null
  }

  if (statutChart) {
    statutChart.destroy()
    statutChart = null
  }

  if (prioriteChart) {
    prioriteChart.destroy()
    prioriteChart = null
  }
}


/* ======================================================
   CHART - EVOLUTION
====================================================== */

function creerEvolutionChart() {
  if (!evolutionChartCanvas.value) {
    return
  }

  if (evolutionChart) {
    evolutionChart.destroy()
    evolutionChart = null
  }

  const labels =
    evolution.value.labels.length
      ? evolution.value.labels
      : ['Aucun']

  const arrives =
    evolution.value.arrives.length
      ? evolution.value.arrives
      : [0]

  const depart =
    evolution.value.depart.length
      ? evolution.value.depart
      : [0]

  evolutionChart =
    new Chart(
      evolutionChartCanvas.value,
      {
        type: 'line',

        data: {
          labels,

          datasets: [
            {
              label:
                'Courriers arrivés',

              data: arrives,

              borderColor:
                '#174d7d',

              backgroundColor:
                'rgba(23, 77, 125, 0.10)',

              borderWidth: 2.5,

              tension: 0.4,

              fill: true,

              pointRadius: 3,

              pointHoverRadius: 5,
            },

            {
              label:
                'Courriers départ',

              data: depart,

              borderColor:
                '#237a57',

              backgroundColor:
                'rgba(35, 122, 87, 0.07)',

              borderWidth: 2.5,

              tension: 0.4,

              fill: true,

              pointRadius: 3,

              pointHoverRadius: 5,
            },
          ],
        },

        options: {
          responsive: true,

          maintainAspectRatio:
            false,

          interaction: {
            mode: 'index',
            intersect: false,
          },

          plugins: {
            legend: {
              position: 'top',

              align: 'end',

              labels: {
                usePointStyle: true,

                boxWidth: 8,

                font: {
                  size: 10,
                },
              },
            },

            tooltip: {
              backgroundColor:
                '#102a43',

              padding: 10,

              titleFont: {
                size: 11,
              },

              bodyFont: {
                size: 10,
              },
            },
          },

          scales: {
            x: {
              grid: {
                display: false,
              },

              ticks: {
                color: '#829ab1',

                font: {
                  size: 9,
                },
              },
            },

            y: {
              beginAtZero: true,

              grid: {
                color: '#edf1f5',
              },

              ticks: {
                color: '#829ab1',

                font: {
                  size: 9,
                },

                precision: 0,
              },
            },
          },
        },
      },
    )
}


/* ======================================================
   CHART - STATUTS
====================================================== */

function creerStatutChart() {
  if (!statutChartCanvas.value) {
    return
  }

  if (statutChart) {
    statutChart.destroy()
    statutChart = null
  }

  statutChart =
    new Chart(
      statutChartCanvas.value,
      {
        type: 'doughnut',

        data: {
          labels: [
            'En cours',
            'Lecture',
            'Archivé',
          ],

          datasets: [
            {
              data: [
                Number(
                  statuts.value.enCours,
                ) || 0,

                Number(
                  statuts.value.lecture,
                ) || 0,

                Number(
                  statuts.value.archives,
                ) || 0,
              ],

              backgroundColor: [
                '#4f7da8',
                '#c59b3c',
                '#3b946d',
              ],

              borderColor:
                '#ffffff',

              borderWidth: 4,

              hoverOffset: 7,
            },
          ],
        },

        options: {
          responsive: true,

          maintainAspectRatio:
            false,

          cutout: '68%',

          plugins: {
            legend: {
              position: 'bottom',

              labels: {
                usePointStyle: true,

                boxWidth: 8,

                padding: 15,

                font: {
                  size: 9,
                },
              },
            },

            tooltip: {
              backgroundColor:
                '#102a43',

              padding: 10,
            },
          },
        },
      },
    )
}


/* ======================================================
   CHART - PRIORITÉS
====================================================== */

function creerPrioriteChart() {
  if (!prioriteChartCanvas.value) {
    return
  }

  if (prioriteChart) {
    prioriteChart.destroy()
    prioriteChart = null
  }

  prioriteChart =
    new Chart(
      prioriteChartCanvas.value,
      {
        type: 'bar',

        data: {
          labels: [
            'Normal',
            'Urgent',
            'Très urgent',
          ],

          datasets: [
            {
              label:
                'Nombre de courriers',

              data: [
                Number(
                  priorites.value.normal,
                ) || 0,

                Number(
                  priorites.value.urgent,
                ) || 0,

                Number(
                  priorites.value.tresUrgent,
                ) || 0,
              ],

              backgroundColor: [
                '#4f7da8',
                '#c59b3c',
                '#bd3e3e',
              ],

              borderRadius: 6,

              borderSkipped: false,
            },
          ],
        },

        options: {
          responsive: true,

          maintainAspectRatio:
            false,

          plugins: {
            legend: {
              display: false,
            },

            tooltip: {
              backgroundColor:
                '#102a43',

              padding: 10,
            },
          },

          scales: {
            x: {
              grid: {
                display: false,
              },

              ticks: {
                color: '#829ab1',

                font: {
                  size: 9,
                },
              },
            },

            y: {
              beginAtZero: true,

              grid: {
                color: '#edf1f5',
              },

              ticks: {
                color: '#829ab1',

                precision: 0,

                font: {
                  size: 9,
                },
              },
            },
          },
        },
      },
    )
}


/* ======================================================
   CRÉER / ACTUALISER CHARTS
====================================================== */

function creerOuActualiserCharts() {
  /*
   * Vérification :
   * si aucun canvas n'est disponible,
   * on ne fait rien.
   */

  if (
    !evolutionChartCanvas.value &&
    !statutChartCanvas.value &&
    !prioriteChartCanvas.value
  ) {
    return
  }

  creerEvolutionChart()
  creerStatutChart()
  creerPrioriteChart()
}


/* ======================================================
   RESIZE
====================================================== */

function handleResize() {
  if (evolutionChart) {
    evolutionChart.resize()
  }

  if (statutChart) {
    statutChart.resize()
  }

  if (prioriteChart) {
    prioriteChart.resize()
  }
}


/* ======================================================
   MOUNT
====================================================== */

onMounted(async () => {
  chargerUtilisateurLocal()

  await chargerDashboard()

  window.addEventListener(
    'resize',
    handleResize,
  )
})


/* ======================================================
   UNMOUNT
====================================================== */

onBeforeUnmount(() => {
  detruireCharts()

  window.removeEventListener(
    'resize',
    handleResize,
  )
})
</script>


<template>
  <div class="dashboard-content">

    <!-- ==================================================
         HEADER
    =================================================== -->

    <section class="dashboard-header">

      <div class="dashboard-title">

        <div class="dashboard-title-icon">
          <LayoutDashboard :size="20" />
        </div>

        <div>
          <h1>
            Tableau de bord
          </h1>

          <p>
            Vue générale du système de gestion documentaire
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
          <span>
            {{ prenom }}
          </span>
        </h2>

        <p>
          Pilotez et suivez les courriers,
          documents et opérations administratives
          depuis votre espace de gestion.
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
         ERROR
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
         KPI
    =================================================== -->

    <section class="section">

      <div class="section-heading">

        <div>

          <h2>
            Vue générale
          </h2>

          <p>
            Indicateurs principaux du système
          </p>

        </div>

        <div class="section-total">

          <CircleDot :size="13" />

          {{ totalCourriers }}

          courriers au total

        </div>

      </div>


      <div class="statistics">

        <!-- ARRIVÉS -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon stat-icon-arrive">
              <Inbox :size="21" />
            </div>

            <span class="stat-badge">
              ARRIVÉ
            </span>

          </div>

          <div
            v-if="loading"
            class="stat-loading"
          >
            <LoaderCircle
              :size="22"
              class="spinning"
            />
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
            Courriers reçus et enregistrés
          </p>

        </article>


        <!-- DÉPART -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon stat-icon-depart">
              <Send :size="21" />
            </div>

            <span class="stat-badge">
              DÉPART
            </span>

          </div>

          <div
            v-if="loading"
            class="stat-loading"
          >
            <LoaderCircle
              :size="22"
              class="spinning"
            />
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
              NUMÉRIQUE
            </span>

          </div>

          <div
            v-if="loading"
            class="stat-loading"
          >
            <LoaderCircle
              :size="22"
              class="spinning"
            />
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
            Documents associés aux courriers
          </p>

        </article>


        <!-- UTILISATEURS -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon stat-icon-users">
              <Users :size="21" />
            </div>

            <span class="stat-badge">
              COMPTES
            </span>

          </div>

          <div
            v-if="loading"
            class="stat-loading"
          >
            <LoaderCircle
              :size="22"
              class="spinning"
            />
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


      <!-- MINI STATS -->

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
         GRAPHIQUES
    =================================================== -->

    <section class="charts-grid">

      <!-- ÉVOLUTION -->

      <div class="chart-card chart-large">

        <div class="chart-header">

          <div>

            <div class="chart-title">
              <TrendingUp :size="17" />

              Évolution des courriers
            </div>

            <p>
              Comparaison des courriers arrivés et départ
            </p>

          </div>

          <span class="chart-period">
            Activité
          </span>

        </div>

        <div class="chart-container evolution-container">

          <canvas
            ref="evolutionChartCanvas"
          ></canvas>

        </div>

      </div>


      <!-- STATUTS -->

      <div class="chart-card">

        <div class="chart-header">

          <div>

            <div class="chart-title">
              <PieChart :size="17" />

              État des courriers
            </div>

            <p>
              Répartition par statut
            </p>

          </div>

          <span class="chart-period">
            Arrivés
          </span>

        </div>

        <div class="chart-container status-container">

          <canvas
            ref="statutChartCanvas"
          ></canvas>

        </div>

      </div>


      <!-- PRIORITÉS -->

      <div class="chart-card">

        <div class="chart-header">

          <div>

            <div class="chart-title">
              <AlertTriangle :size="17" />

              Priorités
            </div>

            <p>
              Niveau de priorité des courriers
            </p>

          </div>

          <span class="chart-period">
            Global
          </span>

        </div>

        <div class="chart-container priority-container">

          <canvas
            ref="prioriteChartCanvas"
          ></canvas>

        </div>

      </div>


      <!-- ARCHIVAGE -->

      <div class="chart-card archive-summary">

        <div class="chart-header">

          <div>

            <div class="chart-title">
              <FolderArchive :size="17" />

              Archivage
            </div>

            <p>
              Suivi de l'état documentaire
            </p>

          </div>

        </div>


        <div class="archive-progress-area">

          <div
            class="archive-circle"
            :style="{
              '--archive-rate': `${tauxArchivage}%`
            }"
          >

            <strong>
              {{ tauxArchivage }}%
            </strong>

            <span>
              archivé
            </span>

          </div>


          <div class="archive-details">

            <div class="archive-line">

              <div>
                <span class="dot dot-green"></span>
                Archivé
              </div>

              <strong>
                {{ stats.archives }}
              </strong>

            </div>


            <div class="archive-line">

              <div>
                <span class="dot dot-blue"></span>
                En cours
              </div>

              <strong>
                {{ stats.enCours }}
              </strong>

            </div>


            <div class="archive-line">

              <div>
                <span class="dot dot-gold"></span>
                Lecture
              </div>

              <strong>
                {{ stats.lecture }}
              </strong>

            </div>

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


        <div
          v-else-if="activites.length"
          class="activities-list"
        >

          <div
            v-for="(activite, index) in activites"
            :key="
              activite.id ||
              activite.id_activite ||
              index
            "
            class="activity-item"
          >

            <div
              class="activity-icon"
              :class="
                activityClass(
                  activite.action,
                )
              "
            >

              <component
                :is="
                  activityIcon(
                    activite.action,
                  )
                "
                :size="16"
              />

            </div>


            <div class="activity-content">

              <strong>
                {{ getActionText(activite.action) }}
              </strong>

              <span>
                {{
                  activite.utilisateur ||
                  activite.nom_utilisateur ||
                  'Utilisateur système'
                }}
              </span>

              <small>
                {{
                  formatDate(
                    activite.date ||
                    activite.created_at ||
                    activite.createdAt,
                  )
                }}
              </small>

            </div>

          </div>

        </div>


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
            Les opérations effectuées dans
            le système apparaîtront ici.
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

          <button
            class="quick-action"
            type="button"
            @click="
              allerVers('/courriers-arrives')
            "
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
              <ChevronRight :size="16" />
            </span>

          </button>


          <button
            class="quick-action"
            type="button"
            @click="
              allerVers('/courriers-depart')
            "
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
              <ChevronRight :size="16" />
            </span>

          </button>


          <button
            class="quick-action"
            type="button"
            @click="
              allerVers('/documents')
            "
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
              <ChevronRight :size="16" />
            </span>

          </button>


          <button
            class="quick-action"
            type="button"
            @click="
              allerVers('/utilisateurs')
            "
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
              <ChevronRight :size="16" />
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
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

/* ======================================================
   BASE & TYPOGRAPHY
====================================================== */
.dashboard-content {
  padding: 30px 40px;
  background: #f4f7fa;
  font-family: 'Outfit', sans-serif;
  color: #1e293b;
  min-height: 100vh;
}

h1, h2, h3, h4, h5, h6 {
  font-family: 'Outfit', sans-serif;
}

/* ======================================================
   HEADER
====================================================== */
.dashboard-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 30px;
  animation: fadeInUp 0.5s ease-out;
}

.dashboard-title {
  display: flex;
  align-items: center;
  gap: 16px;
}

.dashboard-title-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: #ffffff;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.dashboard-title h1 {
  margin: 0;
  font-size: 26px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.5px;
}

.dashboard-title p {
  margin: 4px 0 0;
  font-size: 13px;
  color: #64748b;
  font-weight: 400;
}

.dashboard-header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.current-date {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #475569;
  font-weight: 500;
  background: #ffffff;
  padding: 10px 18px;
  border-radius: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.refresh-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: none;
  border-radius: 30px;
  background: #ffffff;
  color: #3b82f6;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  transition: all 0.3s ease;
}

.refresh-button:hover:not(:disabled) {
  background: #f0f9ff;
  color: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.refresh-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ======================================================
   WELCOME CARD (GLASSMORPHISM)
====================================================== */
.welcome-card {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 40px;
  margin-bottom: 35px;
  border-radius: 24px;
  background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
  color: #ffffff;
  overflow: hidden;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.2);
  animation: fadeInUp 0.6s ease-out;
}

.welcome-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(59,130,246,0.3) 0%, rgba(0,0,0,0) 70%);
  border-radius: 50%;
  pointer-events: none;
}

.welcome-content {
  position: relative;
  z-index: 2;
  max-width: 600px;
}

.welcome-label {
  display: inline-block;
  padding: 6px 14px;
  margin-bottom: 16px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  color: #e2e8f0;
  backdrop-filter: blur(10px);
}

.welcome-card h2 {
  margin: 0 0 12px;
  font-size: 32px;
  font-weight: 300;
}

.welcome-card h2 span {
  font-weight: 800;
  color: #fbbf24;
}

.welcome-card p {
  margin: 0 0 24px;
  font-size: 15px;
  line-height: 1.6;
  color: #94a3b8;
}

.welcome-meta {
  display: flex;
  align-items: center;
  gap: 16px;
}

.account-status,
.grade-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.account-status {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #34d399;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
}

.grade-badge {
  background: rgba(251, 191, 36, 0.15);
  border: 1px solid rgba(251, 191, 36, 0.3);
  color: #fbbf24;
}

.welcome-emblem {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  margin-right: 20px;
}

.emblem-circle {
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: linear-gradient(135deg, #f59e0b, #fbbf24);
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
  border: 6px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
  text-shadow: 0 2px 4px rgba(255,255,255,0.3);
}

.welcome-emblem span {
  color: #fbbf24;
  font-size: 10px;
  font-weight: 800;
  text-align: center;
  letter-spacing: 2px;
  line-height: 1.4;
}

/* ======================================================
   SECTION & KPI
====================================================== */
.section {
  margin-top: 35px;
}

.section-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 24px;
}

.section-heading h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
  color: #1e293b;
}

.section-heading p {
  margin: 4px 0 0;
  font-size: 13px;
  color: #64748b;
}

.section-total {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  background: #ffffff;
  color: #475569;
  font-size: 12px;
  font-weight: 700;
  box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

/* ======================================================
   STATISTICS GRID
====================================================== */
.statistics {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 24px;
}

.stat-card {
  position: relative;
  padding: 24px;
  background: #ffffff;
  border-radius: 20px;
  border: 1px solid rgba(226, 232, 240, 0.8);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  animation: fadeInUp 0.7s ease-out backwards;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
  border-color: transparent;
}

.stat-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stat-icon {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.stat-icon-arrive { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }
.stat-icon-depart { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #059669; }
.stat-icon-document { background: linear-gradient(135deg, #f3e8ff, #e9d5ff); color: #7c3aed; }
.stat-icon-users { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }

.stat-badge {
  padding: 6px 10px;
  border-radius: 12px;
  background: #f1f5f9;
  color: #64748b;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.5px;
}

.stat-number {
  margin-top: 24px;
  color: #0f172a;
  font-size: 36px;
  font-weight: 800;
  line-height: 1;
}

.stat-card h3 {
  margin: 12px 0 4px;
  color: #334155;
  font-size: 14px;
  font-weight: 700;
}

.stat-card p {
  margin: 0;
  color: #94a3b8;
  font-size: 12px;
}

/* ======================================================
   MINI STATISTICS
====================================================== */
.mini-statistics {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
  margin-top: 20px;
}

.mini-stat {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  background: #ffffff;
  border: 1px solid rgba(226, 232, 240, 0.8);
  border-radius: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  animation: fadeInUp 0.8s ease-out backwards;
}

.mini-stat:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
}

.mini-stat-icon {
  width: 44px;
  height: 44px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.mini-danger { background: #fee2e2; color: #dc2626; }
.mini-warning { background: #fef3c7; color: #d97706; }
.mini-info { background: #dbeafe; color: #2563eb; }
.mini-success { background: #d1fae5; color: #059669; }

.mini-stat > div:last-child {
  display: flex;
  flex-direction: column;
}

.mini-stat strong {
  color: #0f172a;
  font-size: 18px;
  font-weight: 800;
  line-height: 1;
  margin-bottom: 4px;
}

.mini-stat span {
  color: #64748b;
  font-size: 11px;
  font-weight: 500;
}

/* ======================================================
   DASHBOARD GRID & CHARTS
====================================================== */
.dashboard-grid, .charts-grid {
  display: grid;
  grid-template-columns: 1.55fr 1fr;
  gap: 24px;
  margin-top: 24px;
}

.panel, .chart-card {
  padding: 24px;
  background: #ffffff;
  border: 1px solid rgba(226, 232, 240, 0.8);
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
  transition: box-shadow 0.3s ease;
}

.panel:hover, .chart-card:hover {
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
}

.chart-large {
  grid-row: span 2;
}

.panel-header, .chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
}

.panel-header h2, .chart-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  color: #0f172a;
  font-size: 16px;
  font-weight: 800;
}

.chart-title svg, .panel-header svg {
  color: #3b82f6;
}

.panel-header p, .chart-header p {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 12px;
}

.chart-period {
  padding: 6px 12px;
  border-radius: 14px;
  background: #f1f5f9;
  color: #475569;
  font-size: 11px;
  font-weight: 700;
}

.chart-container, .evolution-container, .status-container, .priority-container {
  position: relative;
  width: 100%;
}

.evolution-container { height: 350px; }
.status-container, .priority-container { height: 260px; }

/* ======================================================
   ARCHIVE SUMMARY
====================================================== */
.archive-progress-area {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 40px;
  min-height: 240px;
}

.archive-circle {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  background: conic-gradient(#10b981 var(--archive-rate, 0%), #f1f5f9 0);
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 0 0 12px #ffffff, 0 4px 15px rgba(0,0,0,0.05);
}

.archive-circle::before {
  content: "";
  position: absolute;
  inset: 12px;
  border-radius: 50%;
  background: #ffffff;
  z-index: 1;
}

.archive-circle strong,
.archive-circle span {
  position: relative;
  z-index: 2;
}

.archive-circle strong {
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
  line-height: 1;
}

.archive-circle span {
  margin-top: 6px;
  color: #64748b;
  font-size: 11px;
  font-weight: 600;
}

.archive-details {
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-width: 160px;
}

.archive-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
  color: #475569;
}

.archive-line > div {
  display: flex;
  align-items: center;
  gap: 8px;
}

.archive-line strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.dot-green { background: #10b981; }
.dot-blue { background: #3b82f6; }
.dot-gold { background: #f59e0b; }

/* ======================================================
   ACTIVITÉS
====================================================== */
.activities-list {
  max-height: 320px;
  overflow-y: auto;
  padding-right: 10px;
}

.activities-list::-webkit-scrollbar {
  width: 6px;
}
.activities-list::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px 12px;
  border-bottom: 1px solid #f1f5f9;
  border-radius: 12px;
  transition: background 0.2s ease;
}

.activity-item:hover {
  background: #f8fafc;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.activity-default { background: #f1f5f9; color: #475569; }
.activity-success { background: #d1fae5; color: #059669; }
.activity-warning { background: #fef3c7; color: #d97706; }
.activity-danger { background: #fee2e2; color: #dc2626; }
.activity-info { background: #dbeafe; color: #2563eb; }

.activity-content {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.activity-content strong {
  color: #1e293b;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 4px;
}

.activity-content span, .activity-content small {
  color: #64748b;
  font-size: 11px;
}

.activity-content small {
  color: #94a3b8;
  margin-top: 4px;
}

/* ======================================================
   QUICK ACTIONS
====================================================== */
.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 20px;
}

.quick-action {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #ffffff;
  text-align: left;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.quick-action:hover {
  background: #f8fafc;
  border-color: #3b82f6;
  transform: translateX(6px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);
}

.quick-icon {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: #eff6ff;
  color: #2563eb;
  transition: all 0.3s ease;
}

.quick-action:hover .quick-icon {
  background: #2563eb;
  color: #ffffff;
}

.quick-text {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.quick-text strong {
  color: #1e293b;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 2px;
}

.quick-text small {
  color: #64748b;
  font-size: 11px;
}

.quick-arrow {
  color: #94a3b8;
  transition: transform 0.3s ease;
}

.quick-action:hover .quick-arrow {
  transform: translateX(4px);
  color: #3b82f6;
}

/* ======================================================
   ANIMATIONS & RESPONSIVE
====================================================== */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.spinning {
  animation: dashboard-spin 1s linear infinite;
}

@keyframes dashboard-spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@media (max-width: 1200px) {
  .statistics, .mini-statistics { grid-template-columns: repeat(2, 1fr); }
  .dashboard-grid, .charts-grid { grid-template-columns: 1fr; }
  .chart-large { grid-row: auto; }
}

@media (max-width: 768px) {
  .dashboard-content { padding: 20px; }
  .welcome-card { flex-direction: column; padding: 30px; text-align: center; }
  .welcome-emblem { margin: 20px 0 0; }
  .welcome-meta { justify-content: center; }
  .archive-progress-area { flex-direction: column; }
}

@media (max-width: 480px) {
  .statistics, .mini-statistics { grid-template-columns: 1fr; }
  .dashboard-header-right { flex-direction: column; align-items: flex-start; }
}
</style>

