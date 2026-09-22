<template>
  <div class="dashboard">

    <!-- =========================================================
         HEADER
    ========================================================== -->
    <header class="page-header">

      <div class="heading">
        <div class="heading-icon">
          <LayoutDashboard :size="23" />
        </div>

        <div>
          <div class="breadcrumb">
            Accueil
            <span>›</span>
            Tableau de bord
          </div>

          <h1>Tableau de bord</h1>

          <p>
            Vue générale de la gestion administrative des courriers et documents.
          </p>
        </div>
      </div>

      <div class="header-tools">

        <div class="date-chip">
          <CalendarDays :size="17" />
          {{ todayLabel }}
        </div>

        <!-- Période -->
        <div class="period-control">
          <CalendarRange :size="17" />

          <select
            v-model.number="selectedMonth"
            aria-label="Mois"
          >
            <option
              v-for="m in months"
              :key="m.value"
              :value="m.value"
            >
              {{ m.label }}
            </option>
          </select>

          <select
            v-model.number="selectedYear"
            aria-label="Année"
          >
            <option
              v-for="y in years"
              :key="y"
              :value="y"
            >
              {{ y }}
            </option>
          </select>
        </div>

        <button
          class="icon-button"
          type="button"
          title="Actualiser"
          :disabled="loading"
          @click="refreshDashboard"
        >
          <RefreshCw
            :size="18"
            :class="{ spinning: loading }"
          />
        </button>

      </div>
    </header>


    <!-- =========================================================
         ERREUR
    ========================================================== -->
    <div
      v-if="errorMessage"
      class="error-banner"
    >
      <AlertTriangle :size="18" />

      <span>
        {{ errorMessage }}
      </span>

      <button
        type="button"
        @click="loadDashboard"
      >
        Réessayer
      </button>

      <button
        class="close-error"
        type="button"
        @click="errorMessage = ''"
      >
        <X :size="16" />
      </button>
    </div>


    <!-- =========================================================
         KPI CARDS
    ========================================================== -->
    <section class="kpi-grid">

      <!-- Courriers arrivés -->
      <article class="kpi-card kpi-blue">

        <div class="kpi-top">
          <span class="kpi-icon">
            <Inbox :size="23" />
          </span>

          <span class="kpi-tag">
            {{ selectedMonthLabel }}
          </span>
        </div>

        <div class="kpi-label">
          Courriers arrivés
        </div>

        <div class="kpi-number">
          {{ formatNumber(stats.courriers_arrives) }}
        </div>

        <div class="kpi-foot">
          <span class="kpi-mark">↗</span>
          Entrants · période sélectionnée
        </div>

        <Inbox
          class="kpi-watermark"
          :size="78"
        />
      </article>


      <!-- Courriers départ -->
      <article class="kpi-card kpi-teal">

        <div class="kpi-top">
          <span class="kpi-icon">
            <Send :size="23" />
          </span>

          <span class="kpi-tag">
            {{ selectedMonthLabel }}
          </span>
        </div>

        <div class="kpi-label">
          Courriers départ
        </div>

        <div class="kpi-number">
          {{ formatNumber(stats.courriers_depart) }}
        </div>

        <div class="kpi-foot">
          <span class="kpi-mark">↗</span>
          Sortants · période sélectionnée
        </div>

        <Send
          class="kpi-watermark"
          :size="78"
        />
      </article>


      <!-- Documents -->
      <article class="kpi-card kpi-purple">

        <div class="kpi-top">
          <span class="kpi-icon">
            <Files :size="23" />
          </span>

          <span class="kpi-tag">
            Système
          </span>
        </div>

        <div class="kpi-label">
          Documents numériques
        </div>

        <div class="kpi-number">
          {{ formatNumber(stats.documents) }}
        </div>

        <div class="kpi-foot">
          <span class="kpi-mark">▤</span>
          Documents archivés
        </div>

        <Files
          class="kpi-watermark"
          :size="78"
        />
      </article>


      <!-- En cours -->
      <article class="kpi-card kpi-orange">

        <div class="kpi-top">
          <span class="kpi-icon">
            <Clock3 :size="23" />
          </span>

          <span class="kpi-tag">
            Dossiers
          </span>
        </div>

        <div class="kpi-label">
          En cours de traitement
        </div>

        <div class="kpi-number">
          {{ formatNumber(status.en_cours) }}
        </div>

        <div class="kpi-foot">
          <span class="kpi-mark">•</span>
          Dossiers à traiter
        </div>

        <Clock3
          class="kpi-watermark"
          :size="78"
        />
      </article>

    </section>


    <!-- =========================================================
         GRAPHIQUES PRINCIPAUX
    ========================================================== -->
    <section class="main-grid">

      <!-- =======================================================
           ÉVOLUTION
      ======================================================== -->
      <article class="panel evolution-panel">

        <div class="panel-heading">

          <div class="panel-title">

            <span class="panel-icon blue-icon">
              <ChartNoAxesCombined :size="19" />
            </span>

            <div>
              <h2>
                Évolution des courriers
              </h2>

              <p>
                Comparaison quotidienne des courriers arrivés et départ
                pour {{ selectedMonthLabel.toLowerCase() }} {{ selectedYear }}.
              </p>
            </div>

          </div>

          <div class="legend">

            <span>
              <i class="dot blue-dot"></i>
              Arrivés
            </span>

            <span>
              <i class="dot teal-dot"></i>
              Départs
            </span>

          </div>

        </div>


        <!--
          IMPORTANT :
          Le canvas reste toujours dans le DOM.
          On ne fait plus v-if sur le canvas.
          Cela évite les problèmes de référence Vue + Chart.js.
        -->
        <div class="chart-wrap">

          <canvas
            ref="evolutionCanvas"
            class="evolution-canvas"
            :class="{
              'canvas-hidden': loading || !hasEvolutionData
            }"
          ></canvas>

          <!-- Loading -->
          <div
            v-if="loading"
            class="chart-overlay"
          >
            <span class="spinner"></span>
            <span>Chargement des données…</span>
          </div>

          <!-- Pas de données -->
          <div
            v-else-if="!hasEvolutionData"
            class="chart-overlay"
          >
            <ChartNoAxesCombined :size="28" />
            <span>
              Aucune donnée disponible pour cette période.
            </span>
          </div>

        </div>

      </article>


      <!-- =======================================================
           STATUTS
      ======================================================== -->
      <article class="panel status-panel">

        <div class="panel-heading">

          <div class="panel-title">

            <span class="panel-icon blue-icon">
              <ChartPie :size="19" />
            </span>

            <div>
              <h2>
                Répartition par statut des courriers arrivés
              </h2>

              <p>
                État des courriers arrivés pour la période.
              </p>
            </div>

          </div>

        </div>


        <div class="status-layout">

          <div class="donut-wrap">

            <canvas ref="statusCanvas"></canvas>

            <div class="donut-center">
              <strong>
                {{ formatNumber(statusTotal) }}
              </strong>

              <span>
                Total
              </span>
            </div>

          </div>


          <div class="status-legend">

            <div class="status-row">
              <i class="dot blue-dot"></i>

              <span>
                En cours
              </span>

              <b>
                {{ statusPercent(status.en_cours) }}%
              </b>

              <small>
                {{ formatNumber(status.en_cours) }}
              </small>
            </div>


            <div class="status-row">
              <i class="dot teal-dot"></i>

              <span>
                Lecture
              </span>

              <b>
                {{ statusPercent(status.lecture) }}%
              </b>

              <small>
                {{ formatNumber(status.lecture) }}
              </small>
            </div>


            <div class="status-row">
              <i class="dot purple-dot"></i>

              <span>
                Archivé
              </span>

              <b>
                {{ statusPercent(status.archives) }}%
              </b>

              <small>
                {{ formatNumber(status.archives) }}
              </small>
            </div>

          </div>

        </div>

      </article>

    </section>


    <!-- =========================================================
         PRIORITÉS
    ========================================================== -->
    <section class="lower-grid">

      <!-- =======================================================
           PRIORITÉS DÉPART
      ======================================================== -->
      <article class="panel priority-stats-panel">

        <div class="panel-heading">

          <div class="panel-title">

            <span class="panel-icon orange-icon">
              <Flag :size="19" />
            </span>

            <div>

              <h2>
                Priorités des courriers départ
              </h2>

              <p>
                Répartition des courriers sortants par niveau de priorité —
                {{ selectedMonthLabel }} {{ selectedYear }}.
              </p>

            </div>

          </div>

        </div>


        <div
          v-if="loading"
          class="chart-state"
          style="min-height:180px"
        >
          <span class="spinner"></span>
          Chargement…
        </div>


        <div
          v-else-if="priorityDepartTotal === 0"
          class="chart-state"
          style="min-height:180px"
        >
          Aucune donnée de priorité pour cette période.
        </div>


        <div
          v-else
          class="priority-circles"
        >

          <!-- Normal -->
          <div class="prio-circle-card">

            <div class="prio-ring">

              <svg
                viewBox="0 0 100 100"
                class="prio-svg"
              >

                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  class="ring-bg"
                />

                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  class="ring-fill ring-normal"
                  :stroke-dasharray="`${priorityDepartPercent(priorityDepart.normal) * 2.513} 251.3`"
                />

              </svg>

              <div class="prio-ring-inner">

                <strong>
                  {{ priorityDepartPercent(priorityDepart.normal) }}%
                </strong>

                <span>
                  {{ formatNumber(priorityDepart.normal) }}
                </span>

              </div>

            </div>

            <div class="prio-label">
              <i class="dot teal-dot"></i>
              <span>Normal</span>
            </div>

            <div class="prio-count">
              {{ formatNumber(priorityDepart.normal) }} courrier(s)
            </div>

          </div>


          <!-- Urgent -->
          <div class="prio-circle-card">

            <div class="prio-ring">

              <svg
                viewBox="0 0 100 100"
                class="prio-svg"
              >

                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  class="ring-bg"
                />

                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  class="ring-fill ring-urgent"
                  :stroke-dasharray="`${priorityDepartPercent(priorityDepart.urgent) * 2.513} 251.3`"
                />

              </svg>

              <div class="prio-ring-inner">

                <strong>
                  {{ priorityDepartPercent(priorityDepart.urgent) }}%
                </strong>

                <span>
                  {{ formatNumber(priorityDepart.urgent) }}
                </span>

              </div>

            </div>

            <div class="prio-label">
              <i class="dot orange-dot"></i>
              <span>Urgent</span>
            </div>

            <div class="prio-count">
              {{ formatNumber(priorityDepart.urgent) }} courrier(s)
            </div>

          </div>


          <!-- Très urgent -->
          <div class="prio-circle-card">

            <div class="prio-ring">

              <svg
                viewBox="0 0 100 100"
                class="prio-svg"
              >

                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  class="ring-bg"
                />

                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  class="ring-fill ring-tres-urgent"
                  :stroke-dasharray="`${priorityDepartPercent(priorityDepart.tres_urgent) * 2.513} 251.3`"
                />

              </svg>

              <div class="prio-ring-inner">

                <strong>
                  {{ priorityDepartPercent(priorityDepart.tres_urgent) }}%
                </strong>

                <span>
                  {{ formatNumber(priorityDepart.tres_urgent) }}
                </span>

              </div>

            </div>

            <div class="prio-label">
              <i class="dot red-dot"></i>
              <span>Très urgent</span>
            </div>

            <div class="prio-count">
              {{ formatNumber(priorityDepart.tres_urgent) }} courrier(s)
            </div>

          </div>


          <!-- Total -->
          <div class="prio-total-card">

            <div class="prio-total-icon">
              <Send :size="26" />
            </div>

            <div class="prio-total-info">

              <strong>
                {{ formatNumber(priorityDepartTotal) }}
              </strong>

              <span>
                Total départ
              </span>

            </div>

          </div>

        </div>


        <!-- Barre comparative -->
        <div
          v-if="!loading && priorityDepartTotal > 0"
          class="prio-bar-section"
        >

          <div class="prio-bar-label">

            <span>
              <i class="dot teal-dot"></i>
              Normal
            </span>

            <span>
              <i class="dot orange-dot"></i>
              Urgent
            </span>

            <span>
              <i class="dot red-dot"></i>
              Très urgent
            </span>

          </div>

          <div class="prio-bar-track">

            <div
              class="prio-bar-seg prio-seg-normal"
              :style="{
                width: priorityDepartPercent(priorityDepart.normal) + '%'
              }"
            ></div>

            <div
              class="prio-bar-seg prio-seg-urgent"
              :style="{
                width: priorityDepartPercent(priorityDepart.urgent) + '%'
              }"
            ></div>

            <div
              class="prio-bar-seg prio-seg-tres-urgent"
              :style="{
                width: priorityDepartPercent(priorityDepart.tres_urgent) + '%'
              }"
            ></div>

          </div>

        </div>

      </article>


      <!-- =======================================================
           PRIORITÉS ARRIVÉS
      ======================================================== -->
      <article class="panel priority-arrive-panel">

        <div class="panel-heading">

          <div class="panel-title">

            <span class="panel-icon blue-icon">
              <Flag :size="19" />
            </span>

            <div>

              <h2>
                Priorités des courriers arrivés
              </h2>

              <p>
                Nombre de courriers entrants par niveau de priorité —
                {{ selectedMonthLabel }} {{ selectedYear }}.
              </p>

            </div>

          </div>

        </div>


        <div class="priority-chart-wrap">

          <canvas
            ref="priorityCanvas"
            :class="{
              'canvas-hidden': loading || priorityTotal === 0
            }"
          ></canvas>

          <div
            v-if="loading"
            class="chart-overlay"
          >
            <span class="spinner"></span>
            Chargement des priorités…
          </div>

          <div
            v-else-if="priorityTotal === 0"
            class="chart-overlay"
          >
            Aucune priorité renseignée pour cette période.
          </div>

        </div>


        <div class="priority-summary">

          <span>
            <i class="dot teal-dot"></i>
            Normal
            <b>{{ formatNumber(priority.normal) }}</b>
          </span>

          <span>
            <i class="dot orange-dot"></i>
            Urgent
            <b>{{ formatNumber(priority.urgent) }}</b>
          </span>

          <span>
            <i class="dot red-dot"></i>
            Très urgent
            <b>{{ formatNumber(priority.tres_urgent) }}</b>
          </span>

        </div>

      </article>

    </section>


    <!-- =========================================================
         ACTIVITÉS + ACCÈS RAPIDE
    ========================================================== -->
    <section class="bottom-grid">

      <!-- Activités -->
      <article class="panel activity-panel">

        <div class="panel-heading">

          <div class="panel-title">

            <span class="panel-icon blue-icon">
              <Activity :size="19" />
            </span>

            <div>

              <h2>
                Activités récentes
              </h2>

              <p>
                Les 3 dernières opérations enregistrées dans le système.
              </p>

            </div>

          </div>

          <button
            class="text-button"
            type="button"
            @click="goToActivities"
          >
            Voir tout
            <ArrowRight :size="16" />
          </button>

        </div>


        <div
          v-if="recentActivities.length"
          class="activity-list"
        >

          <div
            v-for="(item, index) in recentActivities"
            :key="item.id ?? index"
            class="activity-row"
          >

            <span
              class="activity-symbol"
              :class="activityClass(item)"
            >
              <component
                :is="activityIcon(item)"
                :size="17"
              />
            </span>

            <div class="activity-copy">

              <strong>
                {{ item.action || 'Activité système' }}
              </strong>

              <span>
                {{ item.description || activityDescription(item) }}
              </span>

              <small
                v-if="item.utilisateur || item.nom_utilisateur"
              >
                <UserRound :size="12" />

                {{ item.utilisateur || item.nom_utilisateur }}

                <template
                  v-if="item.table || item.table_concernee"
                >
                  ·
                  {{ formatTable(item.table || item.table_concernee) }}
                </template>

              </small>

            </div>

            <time>
              {{ formatTime(item.date || item.created_at) }}
            </time>

          </div>

        </div>


        <div
          v-else
          class="empty-state"
        >
          <Activity :size="26" />

          <strong>
            Aucune activité récente
          </strong>

          <span>
            Les dernières opérations apparaîtront ici.
          </span>
        </div>

      </article>


      <!-- Accès rapide -->
      <article class="panel links-panel">

        <div class="panel-heading">

          <div class="panel-title">

            <span class="panel-icon blue-icon">
              <ArrowRight :size="19" />
            </span>

            <div>

              <h2>
                Accès rapide
              </h2>

              <p>
                Navigation directe vers les modules principaux.
              </p>

            </div>

          </div>

        </div>


        <div class="quick-links">

          <button
            type="button"
            @click="goTo('/courriers-arrives')"
          >

            <span class="quick-icon quick-blue">
              <Inbox :size="18" />
            </span>

            <span>
              <b>Courriers arrivés</b>
              <small>Consulter les courriers entrants</small>
            </span>

            <ArrowRight :size="16" />

          </button>


          <button
            type="button"
            @click="goTo('/courriers-depart')"
          >

            <span class="quick-icon quick-teal">
              <Send :size="18" />
            </span>

            <span>
              <b>Courriers départ</b>
              <small>Consulter les courriers sortants</small>
            </span>

            <ArrowRight :size="16" />

          </button>


          <button
            type="button"
            @click="goTo('/documents')"
          >

            <span class="quick-icon quick-purple">
              <FileText :size="18" />
            </span>

            <span>
              <b>Documents numériques</b>
              <small>Accéder aux pièces jointes</small>
            </span>

            <ArrowRight :size="16" />

          </button>


          <button
            type="button"
            @click="goTo('/journal')"
          >

            <span class="quick-icon quick-orange">
              <Activity :size="18" />
            </span>

            <span>
              <b>Journal des activités</b>
              <small>Historique des opérations</small>
            </span>

            <ArrowRight :size="16" />

          </button>

        </div>

      </article>

    </section>


    <!-- =========================================================
         FOOTER
    ========================================================== -->
    <footer class="dashboard-footer">

      <span>
        <ShieldCheck :size="15" />
        Gestion des archives · DIST / SEMF
      </span>

      <span>
        Mis à jour : {{ lastUpdated || '—' }}
      </span>

    </footer>

  </div>
</template>


<script setup>
import {
  ref,
  computed,
  nextTick,
  onMounted,
  onBeforeUnmount,
  watch
} from 'vue'

import { useRouter } from 'vue-router'
import api from '@/api/api'

import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  DoughnutController,
  ArcElement,
  BarController,
  BarElement,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

import {
  LayoutDashboard,
  CalendarDays,
  CalendarRange,
  RefreshCw,
  X,
  AlertTriangle,
  Inbox,
  Send,
  Files,
  Clock3,
  ChartNoAxesCombined,
  ChartPie,
  Activity,
  ArrowRight,
  UserRound,
  Flag,
  FileText,
  ShieldCheck,
  Database,
  Plus,
  Pencil,
  Trash2,
  LogIn,
  LogOut,
  UserPlus,
  Settings
} from 'lucide-vue-next'


/* ============================================================
   CHART.JS
============================================================ */

Chart.register(
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  DoughnutController,
  ArcElement,
  BarController,
  BarElement,
  Tooltip,
  Legend,
  Filler
)


/* ============================================================
   ROUTER
============================================================ */

const router = useRouter()


/* ============================================================
   ÉTAT GÉNÉRAL
============================================================ */

const loading = ref(false)
const errorMessage = ref('')


/* ============================================================
   CANVAS
============================================================ */

const evolutionCanvas = ref(null)
const statusCanvas = ref(null)
const priorityCanvas = ref(null)


/* ============================================================
   INSTANCES CHART.JS
============================================================ */

let evolutionChart = null
let statusChart = null
let priorityChart = null


/* ============================================================
   PÉRIODE
============================================================ */

const initialDate = new Date()

const selectedMonth = ref(initialDate.getMonth() + 1)
const selectedYear = ref(initialDate.getFullYear())


const months = [
  { value: 1, label: 'Janvier' },
  { value: 2, label: 'Février' },
  { value: 3, label: 'Mars' },
  { value: 4, label: 'Avril' },
  { value: 5, label: 'Mai' },
  { value: 6, label: 'Juin' },
  { value: 7, label: 'Juillet' },
  { value: 8, label: 'Août' },
  { value: 9, label: 'Septembre' },
  { value: 10, label: 'Octobre' },
  { value: 11, label: 'Novembre' },
  { value: 12, label: 'Décembre' }
]


const years = Array.from(
  { length: 7 },
  (_, i) => initialDate.getFullYear() - 5 + i
)


const selectedMonthLabel = computed(() => {
  return (
    months.find(
      month => month.value === selectedMonth.value
    )?.label || ''
  )
})


const todayLabel = new Intl.DateTimeFormat(
  'fr-FR',
  {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }
).format(initialDate)


/* ============================================================
   STATISTIQUES
============================================================ */

const stats = ref({
  courriers_arrives: 0,
  courriers_depart: 0,
  documents: 0,
  utilisateurs: 0
})


const status = ref({
  en_cours: 0,
  lecture: 0,
  archives: 0
})


const priority = ref({
  normal: 0,
  urgent: 0,
  tres_urgent: 0
})


const priorityDepart = ref({
  normal: 0,
  urgent: 0,
  tres_urgent: 0
})


/* ============================================================
   ACTIVITÉS
============================================================ */

const activities = ref([])


const recentActivities = computed(() => {
  return activities.value.slice(0, 3)
})


/* ============================================================
   ÉVOLUTION
============================================================ */

const monthlyLabels = ref([])
const monthlyArrives = ref([])
const monthlyDeparts = ref([])


const hasEvolutionData = computed(() => {
  return (
    monthlyArrives.value.some(
      value => Number(value) > 0
    ) ||
    monthlyDeparts.value.some(
      value => Number(value) > 0
    )
  )
})


/* ============================================================
   TOTAUX
============================================================ */

const statusTotal = computed(() => {
  return (
    Number(status.value.en_cours) +
    Number(status.value.lecture) +
    Number(status.value.archives)
  )
})


const priorityTotal = computed(() => {
  return (
    Number(priority.value.normal) +
    Number(priority.value.urgent) +
    Number(priority.value.tres_urgent)
  )
})


const priorityDepartTotal = computed(() => {
  return (
    Number(priorityDepart.value.normal) +
    Number(priorityDepart.value.urgent) +
    Number(priorityDepart.value.tres_urgent)
  )
})


/* ============================================================
   MISE À JOUR
============================================================ */

const lastUpdated = ref('')


/* ============================================================
   FORMATAGE
============================================================ */

function formatNumber(value) {
  return new Intl.NumberFormat('fr-FR').format(
    Number(value) || 0
  )
}


function statusPercent(value) {
  return statusTotal.value
    ? Math.round(
        (Number(value || 0) / statusTotal.value) * 100
      )
    : 0
}


function priorityDepartPercent(value) {
  return priorityDepartTotal.value
    ? Math.round(
        (Number(value || 0) /
          priorityDepartTotal.value) *
          100
      )
    : 0
}


function formatTime(value) {
  if (!value) return ''

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return ''
  }

  return new Intl.DateTimeFormat(
    'fr-FR',
    {
      hour: '2-digit',
      minute: '2-digit',
      day: '2-digit',
      month: '2-digit'
    }
  ).format(date)
}


function formatTable(value) {

  const names = {
    courriers_arrives: 'Courriers arrivés',
    courriers_depart: 'Courriers départ',
    utilisateurs: 'Utilisateurs',
    documents_numeriques: 'Documents',
    journal_activites: 'Journal des activités'
  }

  return (
    names[String(value || '').toLowerCase()] ||
    String(value || '').replaceAll('_', ' ')
  )
}


/* ============================================================
   ACTIVITÉS
============================================================ */

function activityDescription(item) {

  const table = formatTable(
    item.table_concernee || item.table
  )

  const reference =
    item.reference_objet ||
    item.id_enregistrement

  return (
    [
      table,
      reference
        ? `Référence : ${reference}`
        : ''
    ]
      .filter(Boolean)
      .join(' · ') ||
    'Opération enregistrée'
  )
}


function activityIcon(item) {

  const action = String(
    item.action || ''
  ).toLowerCase()

  if (
    action.includes('connexion') ||
    action.includes('login')
  ) {
    return LogIn
  }

  if (
    action.includes('déconnexion') ||
    action.includes('logout')
  ) {
    return LogOut
  }

  if (action.includes('supprim')) {
    return Trash2
  }

  if (
    action.includes('modifi') ||
    action.includes('mis à jour')
  ) {
    return Pencil
  }

  if (
    action.includes('cré') ||
    action.includes('ajout')
  ) {
    return Plus
  }

  if (action.includes('utilisateur')) {
    return UserPlus
  }

  if (action.includes('paramètre')) {
    return Settings
  }

  return Database
}


function activityClass(item) {

  const action = String(
    item.action || ''
  ).toLowerCase()

  if (action.includes('supprim')) {
    return 'symbol-red'
  }

  if (
    action.includes('cré') ||
    action.includes('ajout')
  ) {
    return 'symbol-teal'
  }

  if (action.includes('connexion')) {
    return 'symbol-purple'
  }

  return 'symbol-blue'
}


/* ============================================================
   NAVIGATION
============================================================ */

function goTo(path) {
  router.push(path)
}


function goToActivities() {
  router.push('/journal')
}


/* ============================================================
   NETTOYAGE DES CHARTS
============================================================ */

function destroyCharts() {

  if (evolutionChart) {
    evolutionChart.destroy()
    evolutionChart = null
  }

  if (statusChart) {
    statusChart.destroy()
    statusChart = null
  }

  if (priorityChart) {
    priorityChart.destroy()
    priorityChart = null
  }
}


/* ============================================================
   RENDU DES GRAPHIQUES
============================================================ */

function renderCharts() {

  console.log('======================================')
  console.log('🎨 RENDU DES GRAPHIQUES')
  console.log(
    'Période :',
    selectedMonth.value,
    selectedYear.value
  )
  console.log(
    'Labels :',
    [...monthlyLabels.value]
  )
  console.log(
    'Arrivés :',
    [...monthlyArrives.value]
  )
  console.log(
    'Départs :',
    [...monthlyDeparts.value]
  )
  console.log('======================================')


  /*
   * Toujours détruire les anciennes instances
   * AVANT d'en créer de nouvelles.
   */
  destroyCharts()


  /* ==========================================================
     ÉVOLUTION DES COURRIERS
  =========================================================== */

  if (
    evolutionCanvas.value &&
    hasEvolutionData.value
  ) {

    const labels = [
      ...monthlyLabels.value
    ]

    const arrives =
      monthlyArrives.value.map(
        value => Number(value) || 0
      )

    const departs =
      monthlyDeparts.value.map(
        value => Number(value) || 0
      )


    evolutionChart = new Chart(
      evolutionCanvas.value,
      {
        type: 'line',

        data: {
          labels,

          datasets: [

            {
              label: 'Courriers arrivés',

              data: arrives,

              borderColor: '#1769e8',

              backgroundColor:
                'rgba(23, 105, 232, 0.10)',

              fill: true,

              tension: 0.38,

              borderWidth: 3,

              pointRadius: 3,

              pointHoverRadius: 6,

              pointHitRadius: 12
            },


            {
              label: 'Courriers départ',

              data: departs,

              borderColor: '#10aa9b',

              backgroundColor:
                'rgba(16, 170, 155, 0.08)',

              fill: true,

              tension: 0.38,

              borderWidth: 3,

              pointRadius: 3,

              pointHoverRadius: 6,

              pointHitRadius: 12
            }

          ]
        },


        options: {

          responsive: true,

          maintainAspectRatio: false,

          animation: {
            duration: 450
          },

          interaction: {
            mode: 'index',
            intersect: false
          },

          plugins: {

            legend: {
              display: false
            },

            tooltip: {

              backgroundColor: '#102b58',

              padding: 12,

              displayColors: true

            }

          },


          scales: {

            x: {

              grid: {
                color:
                  'rgba(128, 153, 190, 0.13)'
              },

              ticks: {

                color: '#6c7f9f',

                maxRotation: 0,

                autoSkip: true,

                maxTicksLimit: 15

              }

            },


            y: {

              beginAtZero: true,

              grid: {
                color:
                  'rgba(128, 153, 190, 0.16)'
              },

              ticks: {

                precision: 0,

                color: '#6c7f9f'

              }

            }

          }

        }

      }
    )

    console.log(
      '✅ Graphique évolution créé'
    )
  }


  /* ==========================================================
     STATUTS
  =========================================================== */

  if (statusCanvas.value) {

    statusChart = new Chart(
      statusCanvas.value,
      {

        type: 'doughnut',

        data: {

          labels: [
            'En cours',
            'Lecture',
            'Archivé'
          ],

          datasets: [
            {

              data: [
                Number(status.value.en_cours) || 0,
                Number(status.value.lecture) || 0,
                Number(status.value.archives) || 0
              ],

              backgroundColor: [
                '#1769e8',
                '#10aa9b',
                '#7047d7'
              ],

              borderColor: '#ffffff',

              borderWidth: 3,

              hoverOffset: 5

            }
          ]

        },


        options: {

          responsive: true,

          maintainAspectRatio: false,

          cutout: '68%',

          plugins: {

            legend: {
              display: false
            },

            tooltip: {
              padding: 10
            }

          }

        }

      }
    )
  }


  /* ==========================================================
     PRIORITÉS ARRIVÉS
  =========================================================== */

  if (
    priorityCanvas.value &&
    priorityTotal.value > 0
  ) {

    priorityChart = new Chart(
      priorityCanvas.value,
      {

        type: 'bar',

        data: {

          labels: [
            'Normal',
            'Urgent',
            'Très urgent'
          ],

          datasets: [

            {

              label: 'Courriers arrivés',

              data: [
                Number(priority.value.normal) || 0,
                Number(priority.value.urgent) || 0,
                Number(priority.value.tres_urgent) || 0
              ],

              backgroundColor: [
                '#10aa9b',
                '#f59e0b',
                '#ef5350'
              ],

              borderRadius: 8,

              borderSkipped: false,

              maxBarThickness: 48

            }

          ]

        },


        options: {

          responsive: true,

          maintainAspectRatio: false,

          plugins: {

            legend: {
              display: false
            },

            tooltip: {

              backgroundColor: '#102b58',

              padding: 12

            }

          },


          scales: {

            x: {

              grid: {
                display: false
              },

              ticks: {
                color: '#6c7f9f'
              }

            },


            y: {

              beginAtZero: true,

              ticks: {

                precision: 0,

                color: '#6c7f9f'

              },

              grid: {

                color:
                  'rgba(128, 153, 190, 0.16)'

              }

            }

          }

        }

      }
    )
  }
}


/* ============================================================
   PAYLOAD API
============================================================ */

function readPayload(response) {

  return (
    response?.data?.data ??
    response?.data ??
    {}
  )
}


/* ============================================================
   CHARGEMENT DASHBOARD
============================================================ */

let requestId = 0


async function loadDashboard() {

  const currentRequest =
    ++requestId


  loading.value = true

  errorMessage.value = ''


  const mois =
    Number(selectedMonth.value)

  const annee =
    Number(selectedYear.value)


  console.log('======================================')
  console.log('📊 CHARGEMENT DASHBOARD')
  console.log('Mois sélectionné :', mois)
  console.log('Année sélectionnée :', annee)
  console.log('======================================')


  /*
   * On détruit les anciens charts immédiatement.
   * Les canvas restent cependant dans le DOM.
   */
  destroyCharts()


  try {

    /* ========================================================
       API
    ========================================================= */

    const response =
      await api.get(
        '/dashboard',
        {
          params: {
            mois,
            annee,
            // Cache-busting : évite qu'un cache (navigateur, proxy,
            // ou l'instance axios) ne renvoie la réponse d'une
            // période précédente pour la même URL.
            _: Date.now()
          },
          headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache'
          }
        }
      )


    /*
     * Si une requête plus récente existe,
     * on ignore celle-ci.
     */
    if (
      currentRequest !== requestId
    ) {
      return
    }


    const data =
      readPayload(response)


    /* ========================================================
       VÉRIFICATION DE COHÉRENCE MOIS / ANNÉE
       Le backend renvoie parfois (cache, valeur par défaut
       serveur, etc.) des données pour une autre période que
       celle demandée. Si c'est le cas, on ne doit PAS afficher
       cette courbe : elle ne correspondrait pas à la sélection
       de l'utilisateur.
    ========================================================= */

    const backendMois =
      data?.evolution?.mois !== undefined
        ? Number(data.evolution.mois)
        : null

    const backendAnnee =
      data?.evolution?.annee !== undefined
        ? Number(data.evolution.annee)
        : null

    if (
      (backendMois !== null && backendMois !== mois) ||
      (backendAnnee !== null && backendAnnee !== annee)
    ) {

      console.warn(
        '⚠️ Période renvoyée par le backend différente de la période demandée :',
        { demande: { mois, annee }, recue: { mois: backendMois, annee: backendAnnee } }
      )

      if (currentRequest === requestId) {
        errorMessage.value =
          "Les données reçues ne correspondent pas au mois/année sélectionné. Réessayez."
        destroyCharts()
        monthlyLabels.value = []
        monthlyArrives.value = []
        monthlyDeparts.value = []
        loading.value = false
      }

      return
    }


    /* ========================================================
       DEBUG BACKEND
    ========================================================= */

    console.log(
      '📥 Réponse dashboard :',
      data
    )

    console.log(
      '📅 Mois backend :',
      data?.evolution?.mois
    )

    console.log(
      '📅 Année backend :',
      data?.evolution?.annee
    )

    console.log(
      '🏷️ Labels backend :',
      data?.evolution?.labels
    )

    console.log(
      '📨 Arrivés backend :',
      data?.evolution?.arrives
    )

    console.log(
      '📤 Départs backend :',
      data?.evolution?.depart
    )


    /* ========================================================
       STATISTIQUES
    ========================================================= */

    const statistiques =
      data?.statistiques || {}


    stats.value = {

      courriers_arrives:
        Number(
          statistiques.courriers_arrives ?? 0
        ),

      courriers_depart:
        Number(
          statistiques.courriers_depart ?? 0
        ),

      documents:
        Number(
          statistiques.documents ?? 0
        ),

      utilisateurs:
        Number(
          statistiques.utilisateurs ?? 0
        )

    }


    /* ========================================================
       STATUTS
    ========================================================= */

    const statuts =
      data?.statuts || {}


    status.value = {

      en_cours:
        Number(
          statuts.en_cours ?? 0
        ),

      lecture:
        Number(
          statuts.lecture ?? 0
        ),

      archives:
        Number(
          statuts.archives ??
          statuts.archive ??
          0
        )

    }


    /* ========================================================
       PRIORITÉS ARRIVÉS
    ========================================================= */

    const priorites =
      data?.priorites || {}


    priority.value = {

      normal:
        Number(
          priorites.normal ?? 0
        ),

      urgent:
        Number(
          priorites.urgent ?? 0
        ),

      tres_urgent:
        Number(
          priorites.tres_urgent ?? 0
        )

    }


    /* ========================================================
       PRIORITÉS DÉPART
    ========================================================= */

    const prioritesDepart =
      data?.priorites_depart || {}


    priorityDepart.value = {

      normal:
        Number(
          prioritesDepart.normal ??
          0
        ),

      urgent:
        Number(
          prioritesDepart.urgent ??
          0
        ),

      tres_urgent:
        Number(
          prioritesDepart.tres_urgent ??
          0
        )

    }


    /* ========================================================
       ACTIVITÉS
    ========================================================= */

    activities.value =
      Array.isArray(data?.activites)
        ? data.activites
        : []


    /* ========================================================
       ÉVOLUTION
    ========================================================= */

    const evolution =
      data?.evolution || {}


    const labels =
      Array.isArray(evolution.labels)
        ? evolution.labels
        : []


    const arrives =
      Array.isArray(evolution.arrives)
        ? evolution.arrives
        : []


    const departs =
      Array.isArray(evolution.depart)
        ? evolution.depart
        : []


    /*
     * On reconstruit complètement les tableaux.
     * Cela garantit qu'un ancien mois ne reste pas.
     */
    monthlyLabels.value = [
      ...labels
    ]


    monthlyArrives.value =
      monthlyLabels.value.map(
        (_, index) =>
          Number(
            arrives[index] ?? 0
          )
      )


    monthlyDeparts.value =
      monthlyLabels.value.map(
        (_, index) =>
          Number(
            departs[index] ?? 0
          )
      )


    /* ========================================================
       DEBUG FRONTEND
    ========================================================= */

    console.log(
      '======================================'
    )

    console.log(
      '📊 FRONTEND APRÈS NORMALISATION'
    )

    console.log(
      'Période frontend :',
      selectedMonth.value,
      selectedYear.value
    )

    console.log(
      'Labels FRONT :',
      [...monthlyLabels.value]
    )

    console.log(
      'Arrivés FRONT :',
      [...monthlyArrives.value]
    )

    console.log(
      'Départs FRONT :',
      [...monthlyDeparts.value]
    )

    console.log(
      'Nombre de jours :',
      monthlyLabels.value.length
    )

    console.log(
      '======================================'
    )


    /* ========================================================
       DATE DE MISE À JOUR
    ========================================================= */

    lastUpdated.value =
      new Intl.DateTimeFormat(
        'fr-FR',
        {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        }
      ).format(new Date())


    /* ========================================================
       DOM
    ========================================================= */

    await nextTick()


    if (
      currentRequest !== requestId
    ) {
      return
    }


    /*
     * On arrête le loading AVANT le rendu.
     */
    loading.value = false


    await nextTick()


    if (
      currentRequest !== requestId
    ) {
      return
    }


    console.log(
      '🎨 Canvas évolution :',
      evolutionCanvas.value
    )


    /*
     * Le canvas existe toujours.
     */
    renderCharts()


    console.log(
      '✅ Dashboard chargé avec succès'
    )

  } catch (error) {

    if (
      currentRequest !== requestId
    ) {
      return
    }


    console.error(
      '❌ Erreur Dashboard :',
      error
    )


    errorMessage.value =
      error?.response?.data?.message ||
      error?.message ||
      'Une erreur est survenue lors du chargement du tableau de bord.'


    destroyCharts()

  } finally {

    if (
      currentRequest === requestId
    ) {
      loading.value = false
    }

  }
}

function refreshDashboard() {
  const currentDate = new Date()
  const currentMonth = currentDate.getMonth() + 1
  const currentYear = currentDate.getFullYear()

  const alreadyCurrent =
    selectedMonth.value === currentMonth &&
    selectedYear.value === currentYear

  selectedMonth.value = currentMonth
  selectedYear.value = currentYear

  // Raha tsy niova ny période, tsy hiasa ilay watch,
  // ka antsoina mivantana ny chargement.
  if (alreadyCurrent) {
    loadDashboard()
  }
}


/* ============================================================
   WATCH MOIS / ANNÉE
============================================================ */

/*
 * Dès que le mois OU l'année change,
 * on recharge automatiquement le dashboard.
 *
 * Il ne faut PAS remettre @change="loadDashboard"
 * sur les deux <select>.
 */

watch(
  [selectedMonth, selectedYear],
  async (
    [newMonth, newYear],
    [oldMonth, oldYear]
  ) => {

    if (
      newMonth === oldMonth &&
      newYear === oldYear
    ) {
      return
    }


    console.log('======================================')
    console.log('🔄 CHANGEMENT DE PÉRIODE')
    console.log('Ancienne période :', oldMonth, oldYear)
    console.log('Nouvelle période :', newMonth, newYear)
    console.log('======================================')


    await loadDashboard()
  }
)


/* ============================================================
   MONTAGE
============================================================ */

onMounted(() => {
  loadDashboard()
})


/* ============================================================
   DESTRUCTION
============================================================ */

onBeforeUnmount(() => {

  requestId++

  destroyCharts()

})

</script>


<style scoped>

:global(*) {
  box-sizing: border-box;
}


.dashboard {
  --navy: #102d61;
  --blue: #1769e8;
  --teal: #10aa9b;
  --purple: #7047d7;
  --orange: #f59e0b;
  --muted: #7183a3;

  display: flex;
  flex-direction: column;
  gap: 20px;

  min-width: 0;

  padding: clamp(16px, 2vw, 28px);

  color: #17315e;

  background: #f4f7fc;

  font-family:
    Inter,
    ui-sans-serif,
    system-ui,
    -apple-system,
    "Segoe UI",
    sans-serif;
}


/* ============================================================
   HEADER
============================================================ */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 22px;

  flex-wrap: wrap;
}


.heading {
  display: flex;
  align-items: center;

  gap: 15px;

  min-width: 260px;
}


.heading-icon {
  display: grid;
  place-items: center;

  width: 48px;
  height: 48px;

  border-radius: 14px;

  color: #fff;

  background:
    linear-gradient(
      135deg,
      #1c75f0,
      #174bb2
    );

  box-shadow:
    0 8px 20px
    rgba(23, 105, 232, .17);
}


.breadcrumb {
  display: flex;
  align-items: center;

  gap: 10px;

  margin-bottom: 5px;

  color: #8291ac;

  font-size: 12px;
}


.breadcrumb span {
  color: #aab7ca;
}


h1 {
  margin: 0;

  color: #142f60;

  font-size:
    clamp(25px, 2.3vw, 34px);

  font-weight: 800;

  letter-spacing: -.7px;
}


.heading p {
  margin: 6px 0 0;

  color: #7183a3;

  font-size: 13px;
}


.header-tools {
  display: flex;
  align-items: center;
  justify-content: flex-end;

  gap: 10px;

  flex-wrap: wrap;
}


.date-chip,
.period-control {
  display: flex;
  align-items: center;

  gap: 9px;

  min-height: 42px;

  padding: 0 13px;

  border: 1px solid #dce6f5;

  border-radius: 13px;

  background: #fff;

  color: #617797;

  font-size: 12px;
}


.period-control select {
  max-width: 115px;

  border: 0;
  outline: 0;

  background: transparent;

  color: #27436f;

  font: inherit;

  cursor: pointer;
}


.icon-button {
  display: grid;
  place-items: center;

  width: 42px;
  height: 42px;

  border: 1px solid #dce6f5;

  border-radius: 13px;

  background: #fff;

  color: #2459a5;

  cursor: pointer;

  transition:
    .2s ease;
}


.icon-button:hover {
  background: #eaf2ff;
  transform: translateY(-1px);
}


.icon-button:disabled {
  opacity: .55;
  cursor: wait;
}


/* ============================================================
   ERROR
============================================================ */

.error-banner {
  display: flex;
  align-items: center;

  gap: 10px;

  padding: 12px 14px;

  border: 1px solid #fecaca;

  border-radius: 10px;

  background: #fff1f2;

  color: #b42332;

  font-size: 12px;
}


.error-banner span {
  flex: 1;
}


.error-banner button {
  border: 0;

  background: transparent;

  color: #b42332;

  font-weight: 700;

  cursor: pointer;
}


.close-error {
  display: grid;
  place-items: center;
}


/* ============================================================
   KPI
============================================================ */

.kpi-grid {
  display: grid;

  grid-template-columns:
    repeat(4, minmax(0, 1fr));

  gap: 15px;
}


.kpi-card {
  position: relative;

  overflow: hidden;

  min-height: 155px;

  padding: 18px 20px;

  border: 1px solid var(--border);

  border-left:
    4px solid var(--accent);

  border-radius: 13px;

  background:
    linear-gradient(
      115deg,
      #fff 0%,
      var(--tint) 145%
    );

  box-shadow:
    0 5px 18px
    rgba(27, 60, 112, .06);

  transition:
    transform .2s ease,
    box-shadow .2s ease;
}


.kpi-card:hover {
  transform: translateY(-3px);

  box-shadow:
    0 12px 28px
    rgba(27, 60, 112, .10);
}


.kpi-blue {
  --accent: #1769e8;
  --border: #c5dcff;
  --tint: #edf5ff;
}


.kpi-teal {
  --accent: #10aa9b;
  --border: #bcece5;
  --tint: #edfcf9;
}


.kpi-purple {
  --accent: #7047d7;
  --border: #d9ccff;
  --tint: #f5f0ff;
}


.kpi-orange {
  --accent: #ed9700;
  --border: #ffe0a8;
  --tint: #fff8ec;
}


.kpi-top {
  position: relative;
  z-index: 1;

  display: flex;
  align-items: center;
  justify-content: space-between;
}


.kpi-icon {
  display: grid;
  place-items: center;

  width: 46px;
  height: 46px;

  border-radius: 50%;

  background: var(--accent);

  color: white;

  box-shadow:
    0 5px 14px
    rgba(23, 105, 232, .12);
}


.kpi-tag {
  padding: 5px 9px;

  border-radius: 20px;

  background: rgba(255,255,255,.72);

  color: #7183a3;

  font-size: 10px;

  font-weight: 700;
}


.kpi-label {
  position: relative;
  z-index: 1;

  margin-top: 13px;

  color: #4e6386;

  font-size: 12px;

  font-weight: 650;
}


.kpi-number {
  position: relative;
  z-index: 1;

  margin-top: 2px;

  color: #142f60;

  font-size: 29px;

  line-height: 1.25;

  font-weight: 800;

  letter-spacing: -.6px;
}


.kpi-foot {
  position: relative;
  z-index: 1;

  margin-top: 6px;

  color: #8190aa;

  font-size: 11px;
}


.kpi-mark {
  margin-right: 3px;

  color: var(--accent);

  font-weight: 900;
}


.kpi-watermark {
  position: absolute;

  right: 10px;
  bottom: 10px;

  color: var(--accent);

  opacity: .08;
}


/* ============================================================
   PANELS
============================================================ */

.main-grid,
.lower-grid,
.bottom-grid {
  display: grid;

  gap: 15px;
}


.main-grid {
  grid-template-columns:
    minmax(0, 1.8fr)
    minmax(320px, 1fr);
}


.lower-grid {
  grid-template-columns:
    minmax(0, 1.65fr)
    minmax(320px, 1fr);
}


.bottom-grid {
  grid-template-columns:
    minmax(0, 1.65fr)
    minmax(280px, 1fr);
}


.panel {
  min-width: 0;

  padding: 20px;

  border: 1px solid #e0e9f6;

  border-radius: 13px;

  background: #fff;

  box-shadow:
    0 5px 20px
    rgba(27, 60, 112, .04);
}


.panel-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;

  gap: 14px;

  margin-bottom: 17px;
}


.panel-title {
  display: flex;
  align-items: flex-start;

  gap: 12px;

  min-width: 0;
}


.panel-icon {
  display: grid;
  place-items: center;

  flex: 0 0 34px;

  width: 34px;
  height: 34px;

  border-radius: 10px;
}


.blue-icon {
  background: #eaf2ff;
  color: #1769e8;
}


.orange-icon {
  background: #fff3dc;
  color: #dc8a00;
}


.panel h2 {
  margin: 0;

  color: #193664;

  font-size: 17px;

  font-weight: 750;

  letter-spacing: -.2px;
}


.panel-heading p {
  margin: 5px 0 0;

  color: #8492aa;

  font-size: 11px;

  line-height: 1.5;
}


/* ============================================================
   LEGEND
============================================================ */

.legend {
  display: flex;
  align-items: center;

  gap: 15px;

  padding-top: 5px;

  color: #667b9c;

  font-size: 11px;

  white-space: nowrap;
}


.legend span {
  display: flex;
  align-items: center;

  gap: 6px;
}


/* ============================================================
   DOTS
============================================================ */

.dot {
  display: inline-block;

  width: 9px;
  height: 9px;

  flex: 0 0 9px;

  border-radius: 50%;
}


.blue-dot {
  background: #1769e8;
}


.teal-dot {
  background: #10aa9b;
}


.purple-dot {
  background: #7047d7;
}


.orange-dot {
  background: #f59e0b;
}


.red-dot {
  background: #ef5350;
}


/* ============================================================
   CHART WRAP
============================================================ */

.chart-wrap {
  position: relative;

  height: 260px;

  min-height: 220px;
}


.chart-wrap canvas {
  width: 100% !important;
  height: 100% !important;
}


.canvas-hidden {
  visibility: hidden;
}


.chart-overlay {
  position: absolute;

  inset: 0;

  display: flex;

  flex-direction: column;

  align-items: center;
  justify-content: center;

  gap: 10px;

  color: #8b9ab2;

  font-size: 12px;

  text-align: center;

  background: rgba(255,255,255,.96);
}


.chart-state {
  display: grid;

  place-items: center;

  align-content: center;

  gap: 10px;

  height: 100%;

  color: #8b9ab2;

  font-size: 12px;
}


/* ============================================================
   STATUS
============================================================ */

.status-layout {
  display: flex;

  align-items: center;

  gap: 16px;

  min-height: 245px;
}


.donut-wrap {
  position: relative;

  flex: 0 0 52%;

  height: 220px;

  min-width: 0;
}


.donut-wrap canvas {
  width: 100% !important;
  height: 100% !important;
}


.donut-center {
  position: absolute;

  inset: 0;

  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  pointer-events: none;
}


.donut-center strong {
  color: #193664;

  font-size: 25px;

  font-weight: 800;
}


.donut-center span {
  margin-top: 2px;

  color: #8795ad;

  font-size: 11px;
}


.status-legend {
  display: flex;

  flex: 1;

  flex-direction: column;

  gap: 22px;

  min-width: 0;
}


.status-row {
  display: grid;

  grid-template-columns:
    10px
    minmax(55px, 1fr)
    auto;

  align-items: center;

  gap: 8px;

  color: #425a80;

  font-size: 11px;
}


.status-row b {
  color: #193664;

  font-size: 12px;
}


.status-row small {
  grid-column: 2 / 4;

  color: #91a0b7;

  font-size: 10px;

  text-align: right;
}


/* ============================================================
   PRIORITÉS DÉPART
============================================================ */

.priority-circles {
  display: grid;

  grid-template-columns:
    repeat(3, 1fr)
    auto;

  gap: 12px;

  align-items: center;

  margin-bottom: 18px;
}


.prio-circle-card {
  display: flex;

  flex-direction: column;

  align-items: center;

  gap: 8px;
}


.prio-ring {
  position: relative;

  width: 110px;
  height: 110px;
}


.prio-svg {
  width: 100%;
  height: 100%;

  transform: rotate(-90deg);
}


.ring-bg {
  fill: none;

  stroke: #edf2f9;

  stroke-width: 10;
}


.ring-fill {
  fill: none;

  stroke-width: 10;

  stroke-linecap: round;

  transition:
    stroke-dasharray .6s
    cubic-bezier(.4,0,.2,1);
}


.ring-normal {
  stroke: #10aa9b;
}


.ring-urgent {
  stroke: #f59e0b;
}


.ring-tres-urgent {
  stroke: #ef5350;
}


.prio-ring-inner {
  position: absolute;

  inset: 0;

  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  gap: 1px;
}


.prio-ring-inner strong {
  color: #193664;

  font-size: 19px;

  font-weight: 800;

  line-height: 1;
}


.prio-ring-inner span {
  color: #8795ad;

  font-size: 10px;

  font-weight: 600;
}


.prio-label {
  display: flex;

  align-items: center;

  gap: 6px;

  color: #3d5580;

  font-size: 12px;

  font-weight: 700;
}


.prio-count {
  color: #8795ad;

  font-size: 10px;
}


.prio-total-card {
  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  gap: 10px;

  min-width: 90px;

  padding: 16px;

  border: 1.5px dashed #d0ddf3;

  border-radius: 14px;
}


.prio-total-icon {
  display: grid;

  place-items: center;

  width: 44px;
  height: 44px;

  border-radius: 50%;

  background:
    linear-gradient(
      135deg,
      #eaf2ff,
      #d0e5ff
    );

  color: #1769e8;
}


.prio-total-info {
  display: flex;

  flex-direction: column;

  align-items: center;

  gap: 2px;
}


.prio-total-info strong {
  color: #193664;

  font-size: 22px;

  font-weight: 800;
}


.prio-total-info span {
  color: #8795ad;

  font-size: 10px;

  font-weight: 600;

  text-align: center;
}


/* ============================================================
   BARRE PRIORITÉ
============================================================ */

.prio-bar-section {
  margin-top: 4px;
}


.prio-bar-label {
  display: flex;

  justify-content: space-between;

  margin-bottom: 6px;

  color: #7183a3;

  font-size: 10px;
}


.prio-bar-label span {
  display: flex;

  align-items: center;

  gap: 5px;
}


.prio-bar-track {
  display: flex;

  height: 8px;

  overflow: hidden;

  border-radius: 99px;

  background: #edf2f9;
}


.prio-bar-seg {
  height: 100%;

  transition:
    width .5s
    cubic-bezier(.4,0,.2,1);
}


.prio-seg-normal {
  background: #10aa9b;
}


.prio-seg-urgent {
  background: #f59e0b;
}


.prio-seg-tres-urgent {
  background: #ef5350;
}


/* ============================================================
   PRIORITÉS ARRIVÉS
============================================================ */

.priority-chart-wrap {
  position: relative;

  height: 190px;

  min-height: 170px;
}


.priority-chart-wrap canvas {
  width: 100% !important;
  height: 100% !important;
}


.priority-summary {
  display: flex;

  justify-content: space-between;

  gap: 8px;

  flex-wrap: wrap;

  padding: 10px 0 0;

  color: #627797;

  font-size: 10px;
}


.priority-summary span {
  display: flex;

  align-items: center;

  gap: 6px;
}


.priority-summary b {
  margin-left: 2px;

  color: #193664;
}


/* ============================================================
   ACTIVITÉS
============================================================ */

.text-button {
  display: flex;

  align-items: center;

  gap: 6px;

  border: 0;

  background: transparent;

  color: #1769e8;

  font-size: 11px;

  font-weight: 700;

  cursor: pointer;

  white-space: nowrap;
}


.activity-list {
  display: flex;

  flex-direction: column;
}


.activity-row {
  display: flex;

  align-items: center;

  gap: 12px;

  padding: 12px 0;

  border-bottom: 1px solid #edf1f8;
}


.activity-row:last-child {
  border-bottom: 0;
}


.activity-symbol {
  display: grid;

  place-items: center;

  flex: 0 0 34px;

  width: 34px;
  height: 34px;

  border-radius: 50%;

  color: #fff;
}


.symbol-blue {
  background: #1769e8;
}


.symbol-teal {
  background: #10aa9b;
}


.symbol-purple {
  background: #7047d7;
}


.symbol-red {
  background: #e85b62;
}


.activity-copy {
  display: flex;

  flex: 1;

  flex-direction: column;

  gap: 4px;

  min-width: 0;
}


.activity-copy strong {
  overflow: hidden;

  color: #29466f;

  font-size: 12px;

  text-overflow: ellipsis;

  white-space: nowrap;
}


.activity-copy > span {
  overflow: hidden;

  color: #8493ab;

  font-size: 11px;

  text-overflow: ellipsis;

  white-space: nowrap;
}


.activity-copy small {
  display: flex;

  align-items: center;

  gap: 4px;

  color: #98a5ba;

  font-size: 10px;
}


.activity-row time {
  align-self: flex-start;

  padding-top: 3px;

  color: #8a9ab3;

  font-size: 10px;

  white-space: nowrap;
}


/* ============================================================
   EMPTY
============================================================ */

.empty-state {
  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  gap: 9px;

  min-height: 190px;

  color: #8b9ab2;

  text-align: center;
}


.empty-state strong {
  color: #4a6286;

  font-size: 13px;
}


.empty-state span {
  font-size: 11px;
}


/* ============================================================
   QUICK LINKS
============================================================ */

.quick-links {
  display: flex;

  flex-direction: column;
}


.quick-links button {
  display: flex;

  align-items: center;

  gap: 10px;

  width: 100%;

  padding: 10px 0;

  border: 0;

  border-bottom: 1px solid #f0f3f8;

  background: transparent;

  color: #7f91ac;

  text-align: left;

  cursor: pointer;

  transition:
    background .15s ease,
    padding .15s ease;
}


.quick-links button:last-child {
  border-bottom: 0;
}


.quick-links button:hover {
  padding-left: 6px;

  border-radius: 8px;

  background: #f7faff;
}


.quick-links button > span:nth-child(2) {
  display: flex;

  flex: 1;

  flex-direction: column;

  gap: 3px;

  min-width: 0;
}


.quick-links b {
  color: #345078;

  font-size: 11px;
}


.quick-links small {
  color: #8a9ab2;

  font-size: 10px;
}


.quick-icon {
  display: grid;

  place-items: center;

  flex: 0 0 34px;

  width: 34px;
  height: 34px;

  border-radius: 10px;
}


.quick-blue {
  background: #eaf2ff;

  color: #1769e8;
}


.quick-teal {
  background: #e7faf6;

  color: #10aa9b;
}


.quick-purple {
  background: #f1ebff;

  color: #7047d7;
}


.quick-orange {
  background: #fff3dc;

  color: #dc8a00;
}


/* ============================================================
   FOOTER
============================================================ */

.dashboard-footer {
  display: flex;

  justify-content: space-between;

  gap: 12px;

  flex-wrap: wrap;

  padding: 4px 2px;

  color: #91a0b6;

  font-size: 10px;
}


.dashboard-footer span:first-child {
  display: flex;

  align-items: center;

  gap: 7px;
}


/* ============================================================
   ANIMATION
============================================================ */

.spinning {
  animation:
    spin 1s linear infinite;
}


.spinner {
  width: 20px;
  height: 20px;

  border: 2px solid #dbe6f5;

  border-top-color: #1769e8;

  border-radius: 50%;

  animation:
    spin .8s linear infinite;
}


@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 1100px) {

  .kpi-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }


  .main-grid,
  .lower-grid,
  .bottom-grid {
    grid-template-columns: 1fr;
  }


  .status-layout {
    justify-content: center;
  }


  .status-panel .status-layout {
    max-width: 560px;

    margin: auto;
  }


  .priority-circles {
    grid-template-columns:
      repeat(2, 1fr);

    justify-items: center;
  }


  .prio-total-card {
    grid-column: 1 / -1;

    width: 100%;

    flex-direction: row;

    gap: 14px;
  }

}


@media (max-width: 650px) {

  .dashboard {
    padding: 13px;

    gap: 14px;
  }


  .page-header {
    align-items: flex-start;
  }


  .heading {
    align-items: flex-start;
  }


  .heading-icon {
    width: 42px;
    height: 42px;

    flex-basis: 42px;
  }


  .header-tools {
    justify-content: flex-start;

    width: 100%;
  }


  .date-chip {
    display: none;
  }


  .kpi-grid {
    grid-template-columns:
      1fr 1fr;

    gap: 10px;
  }


  .kpi-card {
    min-height: 145px;

    padding: 14px;
  }


  .kpi-number {
    font-size: 25px;
  }


  .kpi-label {
    font-size: 11px;
  }


  .kpi-foot {
    font-size: 10px;
  }


  .panel {
    padding: 15px;
  }


  .panel-heading {
    gap: 8px;
  }


  .panel h2 {
    font-size: 15px;
  }


  .panel-heading p {
    font-size: 10px;
  }


  .legend {
    gap: 8px;

    font-size: 10px;
  }


  .chart-wrap {
    height: 230px;
  }


  .status-layout {
    flex-direction: column;
  }


  .donut-wrap {
    width: 100%;

    max-width: 250px;

    flex-basis: auto;
  }


  .status-legend {
    width: 100%;

    gap: 12px;
  }


  .status-row {
    grid-template-columns:
      10px
      1fr
      auto
      auto;
  }


  .status-row small {
    grid-column: auto;
  }


  .activity-row {
    gap: 9px;
  }


  .activity-row time {
    font-size: 9px;
  }


  .priority-circles {
    grid-template-columns:
      1fr 1fr;

    gap: 10px;
  }


  .prio-total-card {
    grid-column: 1 / -1;

    flex-direction: row;
  }


  .prio-ring {
    width: 90px;
    height: 90px;
  }


  .prio-ring-inner strong {
    font-size: 16px;
  }


  .priority-chart-wrap {
    height: 175px;
  }


  .bottom-grid {
    grid-template-columns: 1fr;
  }

}


@media (max-width: 390px) {

  .kpi-grid {
    grid-template-columns: 1fr;
  }


  .kpi-card {
    min-height: 130px;
  }


  .legend {
    flex-direction: column;

    align-items: flex-start;
  }


  .period-control {
    width: 100%;

    justify-content: space-between;
  }


  .period-control select {
    max-width: 45%;
  }


  .priority-circles {
    grid-template-columns: 1fr;
  }


  .prio-total-card {
    flex-direction: row;
  }

}


@media (prefers-reduced-motion: reduce) {

  *,
  *::before,
  *::after {

    animation-duration: .01ms !important;

    transition-duration: .01ms !important;

    scroll-behavior: auto !important;
  }

}

</style>