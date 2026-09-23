<template>
  <div class="journal-page">

    <!-- =========================================================
         HEADER
    ========================================================== -->
    <section class="page-header">
      <div class="header-left">
        <div class="header-icon">
          <History :size="26" />
        </div>

        <div>
          <h1>Journal des activités</h1>
          <p>
            Historique des actions effectuées dans l'application
          </p>
        </div>
      </div>

      <button
        class="refresh-btn"
        type="button"
        :disabled="loading"
        @click="loadJournal"
      >
        <RefreshCw
          :size="17"
          :class="{ spinning: loading }"
        />
        Actualiser
      </button>
    </section>

    <!-- =========================================================
         STATISTIQUES
    ========================================================== -->
    <section class="stats-grid">

      <!-- Total activités -->
      <div class="stat-card">
        <div class="stat-icon blue">
          <Activity :size="21" />
        </div>

        <div class="stat-content">
          <span>Total activités</span>
          <strong>{{ statistics.total_activites }}</strong>
        </div>
      </div>

      <!-- Utilisateurs actifs -->
      <div class="stat-card">
        <div class="stat-icon green">
          <User :size="21" />
        </div>

        <div class="stat-content">
          <span>Utilisateurs actifs</span>
          <strong>{{ statistics.utilisateurs_actifs }}</strong>
        </div>
      </div>

      <!-- Tables concernées -->
      <div class="stat-card">
        <div class="stat-icon orange">
          <Database :size="21" />
        </div>

        <div class="stat-content">
          <span>Tables concernées</span>
          <strong>{{ statistics.tables_concernees }}</strong>
        </div>
      </div>

      <!-- Activités affichées -->
      <div class="stat-card">
        <div class="stat-icon purple">
          <CalendarDays :size="21" />
        </div>

        <div class="stat-content">
          <span>Activités affichées</span>
          <strong>{{ journal.length }}</strong>
        </div>
      </div>

    </section>

    <!-- =========================================================
         FILTRES
    ========================================================== -->
    <section class="filters-card">

      <div class="filters-header">
        <div>
          <h2>
            <Filter :size="18" />
            Filtres
          </h2>

          <span>
            Rechercher et filtrer les activités
          </span>
        </div>

        <button
          v-if="hasActiveFilters"
          class="reset-btn"
          type="button"
          @click="resetFilters"
        >
          <RotateCcw :size="15" />
          Réinitialiser
        </button>
      </div>

      <div class="filters-grid">

        <!-- Recherche -->
        <div class="filter-group search-group">
          <label>Recherche</label>

          <div class="input-wrapper">
            <Search :size="17" />

            <input
              v-model="filters.search"
              type="text"
              placeholder="Utilisateur, action, référence, IP..."
              @keyup.enter="applyFilters"
            />

            <button
              v-if="filters.search"
              class="clear-input"
              type="button"
              title="Effacer"
              @click="filters.search = ''"
            >
              <X :size="15" />
            </button>
          </div>
        </div>

        <!-- Action -->
        <div class="filter-group">
          <label>Action</label>

          <select v-model="filters.action">
            <option value="">
              Toutes les actions
            </option>

            <option
              v-for="action in actionOptions"
              :key="action"
              :value="action"
            >
              {{ formatAction(action) }}
            </option>
          </select>
        </div>

        <!-- Table -->
        <div class="filter-group">
          <label>Table concernée</label>

          <select v-model="filters.table_concernee">
            <option value="">
              Toutes les tables
            </option>

            <option
              v-for="table in tableOptions"
              :key="table"
              :value="table"
            >
              {{ formatTable(table) }}
            </option>
          </select>
        </div>

        <!-- Utilisateur -->
        <div class="filter-group">
          <label>Utilisateur</label>

          <select v-model="filters.id_utilisateur">
            <option value="">
              Tous les utilisateurs
            </option>

            <option
              v-for="user in userOptions"
              :key="user.id"
              :value="user.id"
            >
              {{ user.name }}
            </option>
          </select>
        </div>

        <!-- Date début -->
        <div class="filter-group">
          <label>Date début</label>

          <div class="input-wrapper">
            <CalendarDays :size="16" />

            <input
              v-model="filters.date_debut"
              type="date"
            />
          </div>
        </div>

        <!-- Date fin -->
        <div class="filter-group">
          <label>Date fin</label>

          <div class="input-wrapper">
            <CalendarDays :size="16" />

            <input
              v-model="filters.date_fin"
              type="date"
            />
          </div>
        </div>

      </div>

      <div class="filters-actions">
        <button
          class="apply-btn"
          type="button"
          :disabled="loading"
          @click="applyFilters"
        >
          <Search :size="16" />
          Rechercher
        </button>
      </div>

    </section>

    <!-- =========================================================
         TABLEAU
    ========================================================== -->
    <section class="table-card">

      <div class="table-header">
        <div>
          <h2>
            Historique des activités
          </h2>

          <span>
            {{ pagination.total }}
            activité{{ pagination.total > 1 ? 's' : '' }}
          </span>
        </div>

        <div class="per-page">
          <span>Afficher</span>

          <select
            v-model.number="pagination.per_page"
            @change="changePerPage"
          >
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>
      </div>

      <!-- Loading -->
      <div
        v-if="loading"
        class="loading-state"
      >
        <LoaderCircle
          class="spinning"
          :size="34"
        />

        <strong>
          Chargement du journal...
        </strong>

        <span>
          Récupération des activités en cours
        </span>
      </div>

      <!-- Empty -->
      <div
        v-else-if="journal.length === 0"
        class="empty-state"
      >
        <div class="empty-icon">
          <History :size="30" />
        </div>

        <h3>
          Aucune activité trouvée
        </h3>

        <p>
          Aucune activité ne correspond aux critères sélectionnés.
        </p>

        <button
          v-if="hasActiveFilters"
          class="reset-btn"
          type="button"
          @click="resetFilters"
        >
          <RotateCcw :size="15" />
          Réinitialiser les filtres
        </button>
      </div>

      <!-- Desktop -->
      <div
        v-else
        class="table-wrapper"
      >
        <table class="journal-table">

          <thead>
            <tr>
              <th>Date / Heure</th>
              <th>Utilisateur</th>
              <th>Action</th>
              <th>Table concernée</th>
              <th>Référence</th>
              <th>Adresse IP</th>
              <th class="action-column">
                Détail
              </th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="item in journal"
              :key="item.id_journal"
            >

              <!-- Date -->
              <td>
                <div class="date-cell">
                  <strong>
                    {{ formatDate(item.created_at, false) }}
                  </strong>

                  <span>
                    {{ formatTime(item.created_at) }}
                  </span>
                </div>
              </td>

              <!-- Utilisateur -->
              <td>
                <div class="user-cell">

                  <div class="user-avatar">
                    {{ getInitials(item.nom_utilisateur) }}
                  </div>

                  <div>
                    <strong>
                      {{ item.nom_utilisateur || 'Système' }}
                    </strong>

                    <span v-if="item.id_utilisateur">
                      ID utilisateur :
                      {{ item.id_utilisateur }}
                    </span>
                  </div>

                </div>
              </td>

              <!-- Action -->
              <td>
                <span
                  class="action-badge"
                  :class="getActionClass(item.action)"
                >
                  <component
                    :is="getActionIcon(item.action)"
                    :size="14"
                  />

                  {{ formatAction(item.action) }}
                </span>
              </td>

              <!-- Table -->
              <td>
                <span class="table-badge">
                  <Database :size="14" />
                  {{ formatTable(item.table_concernee) }}
                </span>
              </td>

              <!-- Référence -->
              
              <td>
                <span
                  v-if="getDisplayReference(item) !== '—'"
                  class="reference-value"
                  :class="getReferenceClass(getDisplayReference(item))"
                >
                  {{ getDisplayReference(item) }}
                </span>

                <span v-else class="muted">
                  —
                </span>
              </td>

              <!-- IP -->
              <td>
                <span class="ip-value">
                  {{ item.adresse_ip || '—' }}
                </span>
              </td>

              <!-- Détail -->
              <td class="action-column">

                <button
                  class="detail-btn"
                  type="button"
                  title="Voir le détail"
                  @click="openDetail(item)"
                >
                  <Eye :size="17" />
                </button>

              </td>

            </tr>

          </tbody>

        </table>
      </div>

      <!-- =======================================================
           MOBILE
      ======================================================== -->
      <div
        v-if="!loading && journal.length"
        class="mobile-list"
      >

        <article
          v-for="item in journal"
          :key="`mobile-${item.id_journal}`"
          class="activity-card"
        >

          <div class="activity-card-header">

            <div class="user-cell">

              <div class="user-avatar">
                {{ getInitials(item.nom_utilisateur) }}
              </div>

              <div>
                <strong>
                  {{ item.nom_utilisateur || 'Système' }}
                </strong>

                <span>
                  {{ formatDate(item.created_at) }}
                </span>
              </div>

            </div>

            <span
              class="action-badge"
              :class="getActionClass(item.action)"
            >
              {{ formatAction(item.action) }}
            </span>

          </div>

          <div class="activity-card-body">

            <div class="mobile-info">
              <span>Table</span>

              <strong>
                {{ formatTable(item.table_concernee) }}
              </strong>
            </div>

            <div class="mobile-info">
              <span>Référence</span>

              <strong
                v-if="item.reference_objet"
                class="reference-value"
                :class="getReferenceClass(item.reference_objet)"
              >
                {{ item.reference_objet }}
              </strong>

              <strong
                v-else
              >
                {{
                  item.id_enregistrement !== null &&
                  item.id_enregistrement !== undefined
                    ? `#${item.id_enregistrement}`
                    : '—'
                }}
              </strong>
            </div>

            <div class="mobile-info">
              <span>Adresse IP</span>

              <strong>
                {{ item.adresse_ip || '—' }}
              </strong>
            </div>

          </div>

          <div class="activity-card-footer">

            <span>
              ID journal :
              #{{ item.id_journal }}
            </span>

            <button
              class="detail-btn"
              type="button"
              @click="openDetail(item)"
            >
              <Eye :size="16" />
              Détail
            </button>

          </div>

        </article>

      </div>

      <!-- =======================================================
           PAGINATION
      ======================================================== -->
      <div
        v-if="!loading && journal.length"
        class="pagination"
      >

        <div class="pagination-info">
          Affichage de

          <strong>
            {{ pagination.from || 0 }}
          </strong>

          à

          <strong>
            {{ pagination.to || 0 }}
          </strong>

          sur

          <strong>
            {{ pagination.total }}
          </strong>
        </div>

        <div class="pagination-controls">

          <button
            type="button"
            class="pagination-btn"
            :disabled="pagination.current_page <= 1"
            title="Page précédente"
            @click="
              goToPage(
                pagination.current_page - 1
              )
            "
          >
            <ChevronLeft :size="17" />
          </button>

          <button
            v-for="page in visiblePages"
            :key="page"
            type="button"
            class="pagination-page"
            :class="{
              active:
                page === pagination.current_page
            }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>

          <button
            type="button"
            class="pagination-btn"
            :disabled="
              pagination.current_page >=
              pagination.last_page
            "
            title="Page suivante"
            @click="
              goToPage(
                pagination.current_page + 1
              )
            "
          >
            <ChevronRight :size="17" />
          </button>

        </div>

      </div>

    </section>

    <!-- =========================================================
         MODAL DETAIL
    ========================================================== -->
    <Teleport to="body">

      <div
        v-if="showDetailModal"
        class="modal-overlay"
        @click.self="closeDetail"
      >

        <div class="detail-modal">

          <header class="modal-header">

            <div class="modal-title">

              <div class="modal-icon">
                <History :size="21" />
              </div>

              <div>
                <h3>
                  Détail de l'activité
                </h3>

                <span>
                  Journal #
                  {{ selectedActivity?.id_journal }}
                </span>
              </div>

            </div>

            <button
              class="modal-close"
              type="button"
              title="Fermer"
              @click="closeDetail"
            >
              <X :size="19" />
            </button>

          </header>

          <!-- Loading détail -->
          <div
            v-if="loadingDetail"
            class="modal-loading"
          >

            <LoaderCircle
              class="spinning"
              :size="30"
            />

            <span>
              Chargement du détail...
            </span>

          </div>

          <!-- Détail -->
          <div
            v-else-if="selectedActivity"
            class="modal-body"
          >

            <!-- Informations générales -->
            <section class="detail-section">

              <div class="section-title">
                <Info :size="17" />
                Informations générales
              </div>

              <div class="detail-grid">

                <div class="detail-item">
                  <span>Utilisateur</span>

                  <strong>
                    {{
                      selectedActivity.nom_utilisateur ||
                      'Système'
                    }}
                  </strong>
                </div>

                <div class="detail-item">
                  <span>ID utilisateur</span>

                  <strong>
                    {{
                      selectedActivity.id_utilisateur ||
                      '—'
                    }}
                  </strong>
                </div>

                <div class="detail-item">
                  <span>Action</span>

                  <strong>
                    <span
                      class="action-badge"
                      :class="
                        getActionClass(
                          selectedActivity.action
                        )
                      "
                    >
                      {{
                        formatAction(
                          selectedActivity.action
                        )
                      }}
                    </span>
                  </strong>
                </div>

                <div class="detail-item">
                  <span>Date et heure</span>

                  <strong>
                    {{
                      formatDate(
                        selectedActivity.created_at
                      )
                    }}
                  </strong>
                </div>

                <div class="detail-item">
                  <span>Table concernée</span>

                  <strong>
                    {{
                      formatTable(
                        selectedActivity.table_concernee
                      )
                    }}
                  </strong>
                </div>

                <div class="detail-item">
                  <span>ID enregistrement</span>

                  <strong>
                    {{
                      selectedActivity.id_enregistrement !==
                      null &&
                      selectedActivity.id_enregistrement !==
                      undefined
                        ? selectedActivity.id_enregistrement
                        : '—'
                    }}
                  </strong>
                </div>

                <div class="detail-item">
                  <span>Référence objet</span>

                  <strong>
                    {{
                      selectedActivity.reference_objet ||
                      '—'
                    }}
                  </strong>
                </div>

                <div class="detail-item">
                  <span>Adresse IP</span>

                  <strong>
                    {{
                      selectedActivity.adresse_ip ||
                      '—'
                    }}
                  </strong>
                </div>

              </div>

            </section>

            <!-- Données avant -->
            <section
              v-if="hasJsonData(selectedActivity.donnees_avant)"
              class="detail-section"
            >

              <div class="section-title">
                <ArrowLeft :size="17" />
                Données avant
              </div>

              <pre class="json-viewer">{{
                formatJson(
                  selectedActivity.donnees_avant
                )
              }}</pre>

            </section>

            <!-- Données après -->
            <section
              v-if="hasJsonData(selectedActivity.donnees_apres)"
              class="detail-section"
            >

              <div class="section-title">
                <ArrowRight :size="17" />
                Données après
              </div>

              <pre class="json-viewer">{{
                formatJson(
                  selectedActivity.donnees_apres
                )
              }}</pre>

            </section>

            <!-- Informations techniques -->
            <section
              v-if="selectedActivity.user_agent"
              class="detail-section"
            >

              <div class="section-title">
                <Monitor :size="17" />
                Informations techniques
              </div>

              <div class="technical-info">
                {{ selectedActivity.user_agent }}
              </div>

            </section>

          </div>

          <footer class="modal-footer">

            <button
              class="close-btn"
              type="button"
              @click="closeDetail"
            >
              Fermer
            </button>

          </footer>

        </div>

      </div>

    </Teleport>

    <!-- =========================================================
         TOAST
    ========================================================== -->
    <Teleport to="body">

      <Transition name="toast">

        <div
          v-if="toast.show"
          class="toast"
          :class="`toast-${toast.type}`"
        >

          <div class="toast-icon">

            <CheckCircle
              v-if="toast.type === 'success'"
              :size="19"
            />

            <AlertCircle
              v-else
              :size="19"
            />

          </div>

          <span>
            {{ toast.message }}
          </span>

          <button
            type="button"
            title="Fermer"
            @click="toast.show = false"
          >
            <X :size="16" />
          </button>

        </div>

      </Transition>

    </Teleport>

  </div>
</template>

<script setup>
import {
  ref,
  reactive,
  computed,
  onMounted,
  onBeforeUnmount
} from 'vue'

import {
  History,
  Activity,
  User,
  Database,
  CalendarDays,
  Filter,
  RotateCcw,
  Search,
  X,
  RefreshCw,
  LoaderCircle,
  Eye,
  ChevronLeft,
  ChevronRight,
  Info,
  ArrowLeft,
  ArrowRight,
  Monitor,
  CheckCircle,
  AlertCircle,
  Plus,
  Pencil,
  Trash2,
  LogIn,
  LogOut,
  FileText,
  ShieldCheck
} from 'lucide-vue-next'

import api from '@/api/api'

/* ============================================================
   DATA
============================================================ */

const journal = ref([])

const loading = ref(false)

const loadingDetail = ref(false)

const showDetailModal = ref(false)

const selectedActivity = ref(null)

/* ============================================================
   FILTRES
============================================================ */

const filters = reactive({
  search: '',
  action: '',
  table_concernee: '',
  id_utilisateur: '',
  date_debut: '',
  date_fin: ''
})

/* ============================================================
   PAGINATION
============================================================ */

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

/* ============================================================
   STATISTIQUES GLOBALES
   Ces valeurs viennent directement du backend.
============================================================ */

const statistics = reactive({
  total_activites: 0,
  utilisateurs_actifs: 0,
  tables_concernees: 0
})

/* ============================================================
   OPTIONS
============================================================ */

const actionOptions = ref([])

const tableOptions = ref([])

const userOptions = ref([])

/* ============================================================
   TOAST
============================================================ */

const toast = reactive({
  show: false,
  type: 'success',
  message: ''
})

/* ============================================================
   COMPUTED
============================================================ */

const hasActiveFilters = computed(() => {
  return Object.values(filters).some(
    value => String(value).trim() !== ''
  )
})

const uniqueUsers = computed(() => {
  return Number(
    statistics.utilisateurs_actifs || 0
  )
})

const uniqueTables = computed(() => {
  return Number(
    statistics.tables_concernees || 0
  )
})

const visiblePages = computed(() => {
  const total = Number(
    pagination.last_page || 1
  )

  const current = Number(
    pagination.current_page || 1
  )

  if (total <= 7) {
    return Array.from(
      { length: total },
      (_, index) => index + 1
    )
  }

  let start = Math.max(
    1,
    current - 2
  )

  let end = Math.min(
    total,
    current + 2
  )

  if (current <= 3) {
    start = 1
    end = 5
  }

  if (current >= total - 2) {
    start = total - 4
    end = total
  }

  return Array.from(
    { length: end - start + 1 },
    (_, index) => start + index
  )
})

/* ============================================================
   API
============================================================ */

async function loadJournal() {
  loading.value = true

  try {
    const params = {
      page: pagination.current_page,
      per_page: pagination.per_page
    }

    if (filters.search.trim()) {
      params.search =
        filters.search.trim()
    }

    if (filters.action) {
      params.action =
        filters.action
    }

    if (filters.table_concernee) {
      params.table_concernee =
        filters.table_concernee
    }

    if (filters.id_utilisateur) {
      params.id_utilisateur =
        filters.id_utilisateur
    }

    if (filters.date_debut) {
      params.date_debut =
        filters.date_debut
    }

    if (filters.date_fin) {
      params.date_fin =
        filters.date_fin
    }

    /*
     * IMPORTANT :
     * api.js du projet utilise fetch().
     * Il faut donc ajouter les query params à l'URL.
     */
    const queryString =
      new URLSearchParams(params).toString()

    const response = await api.get(
      `/journal-activites?${queryString}`
    )

    /*
     * La réponse de api.js est directement
     * le JSON Laravel.
     */
    const result = response || {}

    journal.value = Array.isArray(result.data)
      ? result.data
      : []

    /* Pagination globale */
    if (result.pagination) {
      Object.assign(
        pagination,
        {
          current_page:
            Number(
              result.pagination.current_page ||
              1
            ),

          last_page:
            Number(
              result.pagination.last_page ||
              1
            ),

          per_page:
            Number(
              result.pagination.per_page ||
              pagination.per_page
            ),

          total:
            Number(
              result.pagination.total ||
              0
            ),

          from:
            Number(
              result.pagination.from ||
              0
            ),

          to:
            Number(
              result.pagination.to ||
              0
            )
        }
      )
    }

    /*
     * STATISTIQUES GLOBALES
     *
     * Exemple backend :
     *
     * statistics:
     * {
     *   total_activites: 22,
     *   utilisateurs_actifs: 1,
     *   tables_concernees: 5
     * }
     */
    if (result.statistics) {
      statistics.total_activites =
        Number(
          result.statistics.total_activites ||
          0
        )

      statistics.utilisateurs_actifs =
        Number(
          result.statistics.utilisateurs_actifs ||
          0
        )

      statistics.tables_concernees =
        Number(
          result.statistics.tables_concernees ||
          0
        )
    } else {
      /*
       * Fallback si statistics n'existe pas.
       * On utilise au minimum le total pagination.
       */
      statistics.total_activites =
        Number(
          result.pagination?.total || 0
        )

      statistics.utilisateurs_actifs =
        countUniqueUsers(
          journal.value
        )

      statistics.tables_concernees =
        countUniqueTables(
          journal.value
        )
    }

    buildOptions()

  } catch (error) {
    console.error(
      'Erreur chargement journal :',
      error
    )

    showToast(
      'error',
      error?.message ||
      'Impossible de charger le journal des activités.'
    )

  } finally {
    loading.value = false
  }
}

/* ============================================================
   DETAIL API
============================================================ */

async function loadDetail(id) {
  if (!id) {
    return
  }

  loadingDetail.value = true

  try {
    const response =
      await api.get(
        `/journal-activites/${id}`
      )

    selectedActivity.value =
      response?.data || null

  } catch (error) {
    console.error(
      'Erreur détail journal :',
      error
    )

    showToast(
      'error',
      error?.message ||
      'Impossible de charger le détail.'
    )

  } finally {
    loadingDetail.value = false
  }
}

/* ============================================================
   FILTERS
============================================================ */

function applyFilters() {
  pagination.current_page = 1

  loadJournal()
}

function resetFilters() {
  filters.search = ''
  filters.action = ''
  filters.table_concernee = ''
  filters.id_utilisateur = ''
  filters.date_debut = ''
  filters.date_fin = ''

  pagination.current_page = 1

  loadJournal()
}

function changePerPage() {
  pagination.current_page = 1

  loadJournal()
}

function goToPage(page) {
  const targetPage =
    Number(page)

  if (
    targetPage < 1 ||
    targetPage >
      pagination.last_page ||
    targetPage ===
      pagination.current_page
  ) {
    return
  }

  pagination.current_page =
    targetPage

  loadJournal()
}

/* ============================================================
   OPTIONS
============================================================ */

function buildOptions() {
  const actions =
    new Set()

  const tables =
    new Set()

  const users =
    new Map()

  journal.value.forEach(item => {

    if (item.action) {
      actions.add(
        String(item.action)
      )
    }

    if (item.table_concernee) {
      tables.add(
        String(item.table_concernee)
      )
    }

    if (
      item.id_utilisateur !==
        null &&
      item.id_utilisateur !==
        undefined &&
      item.nom_utilisateur
    ) {
      users.set(
        String(item.id_utilisateur),
        {
          id:
            item.id_utilisateur,

          name:
            item.nom_utilisateur
        }
      )
    }
  })

  actionOptions.value =
    Array.from(actions)
      .sort(
        (a, b) =>
          formatAction(a)
            .localeCompare(
              formatAction(b),
              'fr'
            )
      )

  tableOptions.value =
    Array.from(tables)
      .sort(
        (a, b) =>
          formatTable(a)
            .localeCompare(
              formatTable(b),
              'fr'
            )
      )

  userOptions.value =
    Array.from(users.values())
      .sort(
        (a, b) =>
          a.name.localeCompare(
            b.name,
            'fr'
          )
      )
}

/* ============================================================
   DETAIL
============================================================ */

async function openDetail(item) {
  if (!item) {
    return
  }

  showDetailModal.value = true

  /*
   * Affichage immédiat de la ligne sélectionnée.
   */
  selectedActivity.value = {
    ...item
  }

  document.body.classList.add(
    'modal-open'
  )

  /*
   * Puis récupération des données
   * complètes depuis l'API.
   */
  await loadDetail(
    item.id_journal
  )
}

function closeDetail() {
  showDetailModal.value = false

  selectedActivity.value = null

  loadingDetail.value = false

  document.body.classList.remove(
    'modal-open'
  )
}

/* ============================================================
   FORMATTERS
============================================================ */

function formatDate(
  value,
  onlyDate = true
) {
  if (!value) {
    return '—'
  }

  const date =
    new Date(value)

  if (
    Number.isNaN(
      date.getTime()
    )
  ) {
    return '—'
  }

  const options = {
    timeZone:
      'Indian/Antananarivo',

    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }

  if (!onlyDate) {
    options.hour = '2-digit'
    options.minute = '2-digit'
    options.second = '2-digit'
    options.hour12 = false
  }

  return new Intl.DateTimeFormat(
    'fr-FR',
    options
  ).format(date)
}

function formatTime(value) {
  if (!value) {
    return '—'
  }

  const date =
    new Date(value)

  if (
    Number.isNaN(
      date.getTime()
    )
  ) {
    return '—'
  }

  return new Intl.DateTimeFormat(
    'fr-FR',
    {
      timeZone:
        'Indian/Antananarivo',

      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',

      hour12: false
    }
  ).format(date)
}

function formatAction(action) {
  if (!action) {
    return '—'
  }

  const normalized =
    String(action)
      .toUpperCase()
      .trim()

  const labels = {
    CREATE: 'Création',
    CREATED: 'Création',
    INSERT: 'Création',

    UPDATE: 'Modification',
    UPDATED: 'Modification',
    UPDATE_STATUT: 'Modification du statut',

    DELETE: 'Suppression',
    DELETED: 'Suppression',

    LOGIN: 'Connexion',
    LOGOUT: 'Déconnexion',

    ATTACH: 'Association',
    DETACH: 'Dissociation',
    ADD_DOCUMENT: 'Ajout de document',


    DOWNLOAD: 'Téléchargement',

    VIEW: 'Consultation',
    READ: 'Consultation',

    ACTIVATE: 'Activation',
    DEACTIVATE: 'Désactivation',

    RESET_PASSWORD:
      'Réinitialisation MDP'
  }

  return (
    labels[normalized] ||
    action
  )
}
function formatTable(table) {
  if (!table) {
    return '—'
  }

  const labels = {

    utilisateurs:
      'Utilisateurs',

    roles:
      'Rôles',

    permissions:
      'Permissions',

    courriers_arrives:
      'Courriers arrivés',

    courriers_depart:
      'Courriers départ',

    documents_numeriques:
      'Documents numériques',

    documents_courriers_arrives:
      'Documents courriers arrivés',

    documents_courriers_depart:
      'Documents courriers départ',

    pieces_suite:
      'Pièces de suite',

    destinations:
      'Destinations',

    classements:
      'Classements',

    natures_courriers_depart:
      'Natures courrier départ',

    journal_activites:
      'Journal des activités'
  }

  return (
    labels[table] ||
    table
  )
}

function getReferenceClass(reference) {
  const value = String(reference || '').toUpperCase()

  if (value.startsWith('COR_ARR')) {
    return 'reference-arrive'
  }

  if (value.startsWith('COR_DEP')) {
    return 'reference-depart'
  }

  return ''
}

/* ============================================================
   ACTION STYLE
============================================================ */

function getActionClass(action) {
  const normalized =
    String(action || '')
      .toUpperCase()

  if (
    [
      'CREATE',
      'CREATED',
      'INSERT',
      'ACTIVATE'
    ].includes(normalized)
  ) {
    return 'action-success'
  }

  if (
    [
      'UPDATE',
      'UPDATED',
      'UPDATE_STATUT'
    ].includes(normalized)
  ) {
    return 'action-warning'
  }

  if (
    [
      'DELETE',
      'DELETED'
    ].includes(normalized)
  ) {
    return 'action-danger'
  }

  if (
    [
      'LOGIN',
      'VIEW',
      'READ',
      'DOWNLOAD',
      'ADD_DOCUMENT'
    ].includes(normalized)
  ) {
    return 'action-info'
  }

  if (
    [
      'LOGOUT',
      'DEACTIVATE',
      'DETACH'
    ].includes(normalized)
  ) {
    return 'action-neutral'
  }

  if (
    [
      'ATTACH'
    ].includes(normalized)
  ) {
    return 'action-success'
  }

  return 'action-neutral'
}
/* ============================================================
   ACTION ICON
============================================================ */

function getActionIcon(action) {
  const normalized =
    String(action || '')
      .toUpperCase()

  if (
    [
      'CREATE',
      'CREATED',
      'INSERT'
    ].includes(normalized)
  ) {
    return Plus
  }

  if (
    [
      'UPDATE',
      'UPDATED',
      'UPDATE_STATUT'
    ].includes(normalized)
  ) {
    return Pencil
  }

  if (
    [
      'DELETE',
      'DELETED'
    ].includes(normalized)
  ) {
    return Trash2
  }

  if (
    normalized === 'LOGIN'
  ) {
    return LogIn
  }

  if (
    normalized === 'LOGOUT'
  ) {
    return LogOut
  }

  if (
    [
      'DOWNLOAD',
      'ATTACH',
      'DETACH',
      'ADD_DOCUMENT'
    ].includes(normalized)
  ) {
    return FileText
  }

  if (
    [
      'ACTIVATE',
      'DEACTIVATE'
    ].includes(normalized)
  ) {
    return ShieldCheck
  }

  return Activity
}
/* ============================================================
   INITIALS
============================================================ */

function getInitials(name) {
  if (!name) {
    return 'S'
  }

  const words =
    String(name)
      .trim()
      .split(/\s+/)
      .filter(Boolean)

  if (!words.length) {
    return 'S'
  }

  return words
    .slice(0, 2)
    .map(
      word =>
        word
          .charAt(0)
          .toUpperCase()
    )
    .join('')
}

/* ============================================================
   JSON
============================================================ */

function hasJsonData(value) {
  if (
    value === null ||
    value === undefined
  ) {
    return false
  }

  if (
    typeof value === 'string' &&
    value.trim() === ''
  ) {
    return false
  }

  if (
    typeof value === 'object' &&
    !Array.isArray(value) &&
    Object.keys(value).length === 0
  ) {
    return false
  }

  if (
    Array.isArray(value) &&
    value.length === 0
  ) {
    return false
  }

  return true
}

function formatJson(value) {
  if (!hasJsonData(value)) {
    return ''
  }

  try {
    if (
      typeof value === 'string'
    ) {
      try {
        return JSON.stringify(
          JSON.parse(value),
          null,
          2
        )
      } catch {
        return value
      }
    }

    return JSON.stringify(
      value,
      null,
      2
    )
  } catch {
    return String(value)
  }
}

/* ============================================================
   FALLBACK STATISTICS
============================================================ */

function countUniqueUsers(items) {
  return new Set(
    items
      .map(
        item =>
          item.id_utilisateur
      )
      .filter(
        id =>
          id !== null &&
          id !== undefined
      )
  ).size
}

function countUniqueTables(items) {
  return new Set(
    items
      .map(
        item =>
          item.table_concernee
      )
      .filter(Boolean)
  ).size
}

/* ============================================================
   TOAST
============================================================ */

function showToast(
  type,
  message
) {
  toast.type = type

  toast.message =
    message

  toast.show = true

  window.clearTimeout(
    showToast.timeout
  )

  showToast.timeout =
    window.setTimeout(() => {
      toast.show = false
    }, 3500)
}

/* ============================================================
   ESC
============================================================ */

function handleEscape(event) {
  if (
    event.key === 'Escape' &&
    showDetailModal.value
  ) {
    closeDetail()
  }
}



function getDisplayReference(item) {
  if (item.reference_objet) {
    return item.reference_objet
  }

  const id = item.id_enregistrement

  if (id === null || id === undefined) {
    return '—'
  }

  const table = String(
    item.table_concernee || ''
  ).toLowerCase()

  const numero = String(id).padStart(2, '0')

  if (
    table === 'courriers_arrives' ||
    table === 'documents_courriers_arrives'
  ) {
    return `COR_ARR${numero}`
  }

  if (
    table === 'courriers_depart' ||
    table === 'documents_courriers_depart'
  ) {
    return `COR_DEP${numero}`
  }

  return `#${id}`
}

/* ============================================================
   LIFECYCLE
============================================================ */

onMounted(() => {
  document.addEventListener(
    'keydown',
    handleEscape
  )

  loadJournal()
})

onBeforeUnmount(() => {
  document.removeEventListener(
    'keydown',
    handleEscape
  )

  document.body.classList.remove(
    'modal-open'
  )

  window.clearTimeout(
    showToast.timeout
  )
})
</script>


<style scoped>

/* ============================================================
   PAGE
============================================================ */

.journal-page {
  width: 100%;
  min-height: 100%;
  padding: 24px;
  background: #f7f9fc;
  box-sizing: border-box;
}

/* ============================================================
   HEADER
============================================================ */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 22px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.header-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
  background: #eff6ff;
  border: 1px solid #dbeafe;
}

.page-header h1 {
  margin: 0;
  color: #172033;
  font-size: 25px;
  line-height: 1.2;
  font-weight: 800;
}

.page-header p {
  margin: 5px 0 0;
  color: #6b7280;
  font-size: 13px;
}

.refresh-btn {
  height: 42px;
  padding: 0 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 1px solid #d9dee8;
  border-radius: 10px;
  background: #fff;
  color: #374151;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s ease;
}

.refresh-btn:hover:not(:disabled) {
  border-color: #bfdbfe;
  color: #2563eb;
  background: #f8fbff;
}

.refresh-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* ============================================================
   STATS
============================================================ */

.stats-grid {
  display: grid;
  grid-template-columns:
    repeat(4, minmax(0, 1fr));
  gap: 14px;
  margin-bottom: 18px;
}

.stat-card {
  min-height: 88px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 13px;
  background: #fff;
  border: 1px solid #e7ebf1;
  border-radius: 14px;
  box-shadow:
    0 3px 12px
    rgba(15, 23, 42, 0.035);
}

.stat-icon {
  width: 43px;
  height: 43px;
  flex: 0 0 43px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 11px;
}

.stat-icon.blue {
  color: #2563eb;
  background: #eff6ff;
}

.stat-icon.green {
  color: #059669;
  background: #ecfdf5;
}

.stat-icon.orange {
  color: #d97706;
  background: #fffbeb;
}

.stat-icon.purple {
  color: #7c3aed;
  background: #f5f3ff;
}

.stat-content {
  min-width: 0;
}

.stat-content span {
  display: block;
  margin-bottom: 3px;
  color: #7b8494;
  font-size: 12px;
}

.stat-content strong {
  color: #172033;
  font-size: 22px;
  font-weight: 800;
}

/* ============================================================
   FILTERS
============================================================ */

.filters-card,
.table-card {
  background: #fff;
  border: 1px solid #e7ebf1;
  border-radius: 15px;
  box-shadow:
    0 3px 12px
    rgba(15, 23, 42, 0.035);
}

.filters-card {
  margin-bottom: 18px;
  padding: 18px;
}

.filters-header,
.table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
}

.filters-header {
  margin-bottom: 16px;
}

.filters-header h2,
.table-header h2 {
  margin: 0;
  display: flex;
  align-items: center;
  gap: 7px;
  color: #172033;
  font-size: 15px;
  font-weight: 800;
}

.filters-header span,
.table-header span {
  display: block;
  margin-top: 4px;
  color: #87909e;
  font-size: 12px;
}

.reset-btn {
  height: 36px;
  padding: 0 12px;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  border: 1px solid #dfe4eb;
  border-radius: 9px;
  background: #fff;
  color: #4b5563;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.reset-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.filters-grid {
  display: grid;
  grid-template-columns:
    minmax(240px, 2fr)
    repeat(3, minmax(150px, 1fr))
    repeat(2, minmax(150px, 1fr));
  gap: 13px;
}

.filter-group {
  min-width: 0;
}

.filter-group label {
  display: block;
  margin-bottom: 6px;
  color: #4b5563;
  font-size: 12px;
  font-weight: 700;
}

.filter-group select,
.filter-group input {
  width: 100%;
  height: 40px;
  box-sizing: border-box;
  border: 1px solid #dce2ea;
  border-radius: 9px;
  outline: none;
  background: #fff;
  color: #273244;
  font-family: inherit;
  font-size: 12px;
  transition: 0.2s ease;
}

.filter-group select {
  padding: 0 11px;
}

.filter-group input {
  padding: 0 11px;
}

.filter-group select:focus,
.filter-group input:focus {
  border-color: #93c5fd;
  box-shadow:
    0 0 0 3px #eff6ff;
}

.input-wrapper {
  height: 40px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0 10px;
  box-sizing: border-box;
  border: 1px solid #dce2ea;
  border-radius: 9px;
  background: #fff;
  color: #8b95a5;
}

.input-wrapper:focus-within {
  border-color: #93c5fd;
  box-shadow:
    0 0 0 3px #eff6ff;
}

.input-wrapper input {
  height: 100%;
  padding: 0;
  border: 0;
  box-shadow: none !important;
}

.clear-input {
  width: 25px;
  height: 25px;
  flex: 0 0 25px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
}

.clear-input:hover {
  background: #f1f5f9;
  color: #374151;
}

.filters-actions {
  margin-top: 14px;
  display: flex;
  justify-content: flex-end;
}

.apply-btn {
  height: 40px;
  padding: 0 17px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid #2563eb;
  border-radius: 9px;
  background: #2563eb;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.apply-btn:hover:not(:disabled) {
  background: #1d4ed8;
  border-color: #1d4ed8;
}

.apply-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ============================================================
   TABLE
============================================================ */

.table-card {
  overflow: hidden;
}

.table-header {
  padding: 17px 18px;
  border-bottom: 1px solid #edf0f4;
}

.per-page {
  display: flex;
  align-items: center;
  gap: 7px;
  color: #7b8494;
  font-size: 12px;
}

.per-page select {
  height: 34px;
  padding: 0 8px;
  border: 1px solid #dce2ea;
  border-radius: 8px;
  background: #fff;
  color: #374151;
  outline: none;
}

.table-wrapper {
  width: 100%;
  overflow-x: auto;
}

.journal-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1080px;
}

.journal-table th {
  padding: 12px 14px;
  text-align: left;
  white-space: nowrap;
  color: #667085;
  background: #f8fafc;
  border-bottom: 1px solid #e7ebf1;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.journal-table td {
  padding: 12px 14px;
  vertical-align: middle;
  border-bottom: 1px solid #eef1f5;
}

.journal-table tbody tr:hover {
  background: #fafcff;
}

.date-cell strong,
.date-cell span {
  display: block;
}

.date-cell strong {
  color: #374151;
  font-size: 12px;
  font-weight: 700;
}

.date-cell span {
  margin-top: 3px;
  color: #9ca3af;
  font-size: 11px;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 9px;
  min-width: 170px;
}

.user-avatar {
  width: 34px;
  height: 34px;
  flex: 0 0 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 11px;
  font-weight: 800;
}

.user-cell strong,
.user-cell span {
  display: block;
}

.user-cell strong {
  max-width: 160px;
  overflow: hidden;
  color: #273244;
  font-size: 12px;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-cell span {
  margin-top: 2px;
  color: #9ca3af;
  font-size: 10px;
}

.action-badge,
.table-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  min-height: 28px;
  padding: 0 9px;
  box-sizing: border-box;
  border-radius: 7px;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
}

.action-success {
  color: #047857;
  background: #ecfdf5;
  border: 1px solid #d1fae5;
}

.action-warning {
  color: #b45309;
  background: #fffbeb;
  border: 1px solid #fef3c7;
}

.action-danger {
  color: #b91c1c;
  background: #fef2f2;
  border: 1px solid #fee2e2;
}

.action-info {
  color: #1d4ed8;
  background: #eff6ff;
  border: 1px solid #dbeafe;
}

.action-neutral {
  color: #475569;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
}

.table-badge {
  color: #475569;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.reference-value {
  display: inline-flex;
  align-items: center;
  min-height: 26px;
  padding: 0 8px;
  border-radius: 7px;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.01em;
}

.reference-arrive {
  color: #1d4ed8;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
}

.reference-depart {
  color: #78043e;
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
}

.reference-id,
.ip-value {
  color: #64748b;
  font-size: 11px;
  font-family: monospace;
}

.muted {
  color: #a0a8b5;
}

.action-column {
  width: 70px;
  text-align: center !important;
}

.detail-btn {
  min-width: 34px;
  height: 34px;
  padding: 0 9px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s ease;
}

.detail-btn:hover {
  color: #2563eb;
  background: #eff6ff;
  border-color: #bfdbfe;
}

/* ============================================================
   STATES
============================================================ */

.loading-state,
.empty-state {
  min-height: 330px;
  padding: 40px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.loading-state {
  color: #64748b;
}

.loading-state strong {
  margin-top: 12px;
  color: #374151;
  font-size: 14px;
}

.loading-state span {
  margin-top: 4px;
  color: #9ca3af;
  font-size: 12px;
}

.empty-icon {
  width: 62px;
  height: 62px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  color: #64748b;
  background: #f1f5f9;
}

.empty-state h3 {
  margin: 15px 0 5px;
  color: #374151;
  font-size: 15px;
}

.empty-state p {
  margin: 0 0 15px;
  color: #9ca3af;
  font-size: 12px;
}

/* ============================================================
   PAGINATION
============================================================ */

.pagination {
  min-height: 64px;
  padding: 10px 18px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  border-top: 1px solid #edf0f4;
}

.pagination-info {
  color: #7b8494;
  font-size: 11px;
}

.pagination-info strong {
  color: #374151;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 5px;
}

.pagination-btn,
.pagination-page {
  min-width: 32px;
  height: 32px;
  padding: 0 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dce2ea;
  border-radius: 7px;
  background: #fff;
  color: #475569;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.pagination-page.active {
  color: #fff;
  background: #2563eb;
  border-color: #2563eb;
}

.pagination-btn:hover:not(:disabled),
.pagination-page:hover:not(.active) {
  border-color: #bfdbfe;
  color: #2563eb;
  background: #eff6ff;
}

.pagination-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

/* ============================================================
   MOBILE LIST
============================================================ */

.mobile-list {
  display: none;
}

/* ============================================================
   MODAL
============================================================ */

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.52);
  backdrop-filter: blur(3px);
}

.detail-modal {
  width: min(820px, 100%);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 16px;
  background: #fff;
  box-shadow:
    0 25px 70px
    rgba(15, 23, 42, 0.25);
}

.modal-header {
  min-height: 70px;
  padding: 13px 18px;
  box-sizing: border-box;
  background: #08264d;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  border-bottom: 1px solid #edf0f4;
}

.modal-title {
  display: flex;
  align-items: center;
  gap: 11px;
}

.modal-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  color: #2004f8;
  background: #eff6ff;
}

.modal-title h3 {
  margin: 0;
  color: #eaecf0;
  font-size: 15px;
}

.modal-title span {
  display: block;
  margin-top: 3px;
  color: #dee2e9;
  font-size: 11px;
}

.modal-close {
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 8px;
  background: white;
  color: #110aea;
  cursor: pointer;
}

.modal-close:hover {
  background: #f1f5f9;
  color: #334155;
}

.modal-body {
  overflow-y: auto;
  padding: 20px;
}

.modal-loading {
  min-height: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: #64748b;
  font-size: 12px;
}

.detail-section {
  margin-bottom: 20px;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.section-title {
  margin-bottom: 11px;
  display: flex;
  align-items: center;
  gap: 7px;
  color: #334155;
  font-size: 13px;
  font-weight: 800;
}

.detail-grid {
  display: grid;
  grid-template-columns:
    repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.detail-item {
  padding: 12px;
  border: 1px solid #e7ebf1;
  border-radius: 9px;
  background: #fafbfc;
}

.detail-item span:first-child {
  display: block;
  margin-bottom: 5px;
  color: #8b95a5;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

.detail-item strong {
  color: #374151;
  font-size: 12px;
  word-break: break-word;
}

.json-viewer {
  max-height: 260px;
  margin: 0;
  padding: 14px;
  overflow: auto;
  border: 1px solid #e2e8f0;
  border-radius: 9px;
  background: #0f172a;
  color: #e2e8f0;
  font-size: 11px;
  line-height: 1.55;
  font-family:
    Consolas,
    Monaco,
    monospace;
}

.technical-info {
  padding: 12px;
  border: 1px solid #e7ebf1;
  border-radius: 9px;
  background: #f8fafc;
  color: #64748b;
  font-size: 11px;
  line-height: 1.55;
  word-break: break-word;
}

.modal-footer {
  padding: 10px 18px;
  display: flex;
  justify-content: flex-end;
  border-top: 1px solid #edf0f4;
}

.close-btn {
  height: 38px;
  padding: 0 16px;
  border: 1px solid #dce2ea;
  border-radius: 9px;
  background: #f7f7f7;
  color: #3504f8;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.close-btn:hover {
  background: #f8fafc;
}

/* ============================================================
   TOAST
============================================================ */

.toast {
  position: fixed;
  right: 22px;
  bottom: 22px;
  z-index: 2000;
  min-width: 300px;
  max-width: 420px;
  padding: 12px 13px;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 11px;
  background: #fff;
  box-shadow:
    0 12px 35px
    rgba(15, 23, 42, 0.14);
  color: #374151;
  font-size: 12px;
  font-weight: 600;
}

.toast-icon {
  display: flex;
  color: #2563eb;
}

.toast-error .toast-icon {
  color: #dc2626;
}

.toast button {
  margin-left: auto;
  display: flex;
  border: 0;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
}

.toast-enter-active,
.toast-leave-active {
  transition: 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* ============================================================
   ANIMATION
============================================================ */

.spinning {
  animation:
    spin 0.8s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 1200px) {

  .stats-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .filters-grid {
    grid-template-columns:
      repeat(3, minmax(0, 1fr));
  }

  .search-group {
    grid-column: span 2;
  }
}

@media (max-width: 850px) {

  .journal-page {
    padding: 16px;
  }

  .page-header {
    align-items: flex-start;
  }

  .filters-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .search-group {
    grid-column: span 2;
  }

  .table-wrapper {
    display: none;
  }

  .mobile-list {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .activity-card {
    border: 1px solid #e7ebf1;
    border-radius: 12px;
    background: #fff;
  }

  .activity-card-header {
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-bottom: 1px solid #edf0f4;
  }

  .activity-card-body {
    padding: 12px;
    display: grid;
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .mobile-info span,
  .mobile-info strong {
    display: block;
  }

  .mobile-info span {
    margin-bottom: 3px;
    color: #9ca3af;
    font-size: 10px;
  }

  .mobile-info strong {
    color: #475569;
    font-size: 11px;
    word-break: break-word;
  }

  .activity-card-footer {
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-top: 1px solid #edf0f4;
    color: #9ca3af;
    font-size: 10px;
  }

  .pagination {
    flex-direction: column;
    align-items: stretch;
  }

  .pagination-info {
    text-align: center;
  }

  .pagination-controls {
    justify-content: center;
  }
}

@media (max-width: 600px) {

  .journal-page {
    padding: 12px;
  }

  .page-header {
    flex-direction: column;
  }

  .refresh-btn {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .search-group {
    grid-column: auto;
  }

  .filters-actions .apply-btn {
    width: 100%;
    justify-content: center;
  }

  .table-header {
    align-items: flex-start;
  }

  .per-page {
    display: none;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .modal-overlay {
    padding: 10px;
    align-items: flex-end;
  }

  .detail-modal {
    max-height: 94vh;
    border-radius:
      16px 16px 0 0;
  }

  .modal-body {
    padding: 15px;
  }

  .toast {
    left: 12px;
    right: 12px;
    bottom: 12px;
    min-width: 0;
  }
}

</style>