<template>
  <div class="documents-page">

    <!-- =========================================================
         HEADER
    ========================================================== -->
    <header class="page-header">
      <div class="header-left">
        <div class="header-icon">
          <FolderOpen :size="24" />
        </div>

        <div>
          <h1>Documents numériques</h1>
          <p>
            Centralisation des documents des courriers arrivés et départ
          </p>
        </div>
      </div>

      <button
        class="refresh-button"
        type="button"
        :disabled="loading"
        @click="loadAllDocuments"
      >
        <RefreshCw
          :size="17"
          :class="{ spinning: loading }"
        />
        Actualiser
      </button>
    </header>

    <!-- =========================================================
         STATISTIQUES
    ========================================================== -->
    <section class="stats-grid">

      <div class="stat-card">
        <div class="stat-icon stat-icon-total">
          <FileText :size="20" />
        </div>

        <div>
          <span>Total documents</span>
          <strong>{{ documents.length }}</strong>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-arrive">
          <Inbox :size="20" />
        </div>

        <div>
          <span>Documents arrivés</span>
          <strong>{{ totalArrives }}</strong>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-depart">
          <Send :size="20" />
        </div>

        <div>
          <span>Documents départ</span>
          <strong>{{ totalDeparts }}</strong>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-urgent">
          <AlertTriangle :size="20" />
        </div>

        <div>
          <span>Documents urgents</span>
          <strong>{{ totalUrgents }}</strong>
        </div>
      </div>

    </section>

    <!-- =========================================================
         FILTRES
    ========================================================== -->
    <section class="filters-card">

      <div class="search-wrapper">
        <Search :size="18" />

        <input
          v-model="filters.search"
          type="search"
          placeholder="Rechercher un document par nom, courrier, objet..."
        />

        <button
          v-if="filters.search"
          type="button"
          class="clear-search"
          @click="filters.search = ''"
        >
          <X :size="15" />
        </button>
      </div>

      <select v-model="filters.type">
        <option value="">Tous les types</option>
        <option value="arrive">Courriers arrivés</option>
        <option value="depart">Courriers départ</option>
      </select>

      <select v-model="filters.extension">
        <option value="">Tous les formats</option>
        <option value="pdf">PDF</option>
        <option value="doc">DOC</option>
        <option value="docx">DOCX</option>
        <option value="xls">XLS</option>
        <option value="xlsx">XLSX</option>
        <option value="jpg">JPG</option>
        <option value="jpeg">JPEG</option>
        <option value="png">PNG</option>
      </select>

      <select v-model="filters.priorite">
        <option value="">Toutes les priorités</option>
        <option value="NORMAL">Normal</option>
        <option value="URGENT">Urgent</option>
        <option value="TRES_URGENT">Très urgent</option>
      </select>

      <button
        type="button"
        class="reset-button"
        @click="resetFilters"
      >
        <RotateCcw :size="16" />
        Réinitialiser
      </button>

    </section>

    <!-- =========================================================
         RESULTAT
    ========================================================== -->
    <section class="documents-section">

      <div class="section-header">
        <div>
          <h2>Documents</h2>

          <p v-if="filteredDocuments.length">
            {{ filteredDocuments.length }}
            document(s) affiché(s)
          </p>

          <p v-else>
            Aucun document trouvé
          </p>
        </div>
      </div>

      <!-- LOADING -->
      <div
        v-if="loading"
        class="state-box"
      >
        <RefreshCw
          :size="24"
          class="spinning"
        />

        <span>
          Chargement des documents...
        </span>
      </div>

      <!-- EMPTY -->
      <div
        v-else-if="!filteredDocuments.length"
        class="state-box empty-state"
      >
        <div class="empty-icon">
          <FileText :size="30" />
        </div>

        <h3>Aucun document</h3>

        <p>
          Aucun document numérique ne correspond aux critères sélectionnés.
        </p>
      </div>

      <!-- DOCUMENT GRID -->
      <div
        v-else
        class="documents-grid"
      >

        <article
          v-for="item in filteredDocuments"
          :key="item.key"
          class="document-card"
          :class="documentTypeClass(item)"
        >

          <!-- TYPE -->
          <div class="card-top">

            <span
              class="document-origin"
              :class="item.type === 'arrive'
                ? 'origin-arrive'
                : 'origin-depart'"
            >
              <Inbox
                v-if="item.type === 'arrive'"
                :size="13"
              />

              <Send
                v-else
                :size="13"
              />

              {{ item.type === 'arrive' ? 'ARRIVÉE' : 'DÉPART' }}
            </span>

            <span
              class="priority-badge"
              :class="priorityClass(item.priorite)"
            >
              {{ priorityLabel(item.priorite) }}
            </span>

          </div>

          <!-- FILE -->
          <div class="file-area">

            <div
              class="file-icon"
              :class="fileIconClass(item.nom)"
            >
              <FileText :size="27" />

              <span>
                {{ fileExtension(item.nom) }}
              </span>
            </div>

            <div class="file-information">

              <h3 :title="item.nom">
                {{ item.nom }}
              </h3>

              <span>
                {{ formatFileSize(item.taille) }}
              </span>

            </div>

          </div>

          <!-- COURRIER -->
          <div class="courrier-information">

            <div class="information-line">
              <span>Courrier</span>

              <strong>
                {{ item.numeroCourrier }}
              </strong>
            </div>

            <div class="information-line">
              <span>Date</span>

              <strong>
                {{ formatDate(item.date) }}
              </strong>
            </div>

            <div class="object-line">
              {{ item.objet || 'Objet non renseigné' }}
            </div>

          </div>

          <!-- ACTIONS -->
          <div class="card-actions">

            <button
              v-if="canViewDocuments && canPreview(item)"
              type="button"
              class="action-button action-view"
              @click="previewDocument(item)"
            >
              <Eye :size="15" />
              Afficher
            </button>

            <button
              v-if="canDownloadDocuments"
              type="button"
              class="action-button action-download"
              @click="downloadDocument(item)"
            >
              <Download :size="15" />
              Télécharger
            </button>

            <button
                v-if="
                  (item.type === 'arrive' && canDeleteArrives) ||
                  (item.type === 'depart' && canDeleteDeparts)
                "
              type="button"
              class="icon-action delete-action"
              title="Supprimer"
              @click="askDeleteDocument(item)"
            >
              <Trash2 :size="16" />
            </button>

          </div>

        </article>

      </div>

    </section>

    <!-- =========================================================
         PREVIEW
    ========================================================== -->
    <div
      v-if="showPreview"
      class="modal-overlay"
      @click.self="closePreview"
    >
      <div class="preview-modal">

        <header class="preview-header">

          <div>
            <span>Prévisualisation</span>
            <h3>
              {{ previewDocument?.nom }}
            </h3>
          </div>

          <button
            type="button"
            class="close-button"
            @click="closePreview"
          >
            <X :size="20" />
          </button>

        </header>

        <div class="preview-content">

          <div
            v-if="previewLoading"
            class="preview-loading"
          >
            <RefreshCw
              :size="25"
              class="spinning"
            />

            Chargement...
          </div>

          <iframe
            v-else-if="previewType === 'pdf'"
            :src="previewUrl"
            class="preview-frame"
          />

          <img
            v-else-if="previewType === 'image'"
            :src="previewUrl"
            class="preview-image"
            alt="Prévisualisation"
          />

          <div
            v-else
            class="unsupported-preview"
          >
            <FileText :size="40" />

            <p>
              Ce format ne peut pas être prévisualisé directement.
            </p>

            <button
              v-if="canDownloadDocuments"
              type="button"
              class="action-button action-download"
              @click="downloadDocument(previewDocument)"
            >
              <Download :size="16" />
              Télécharger
            </button>
          </div>

        </div>

      </div>
    </div>

    <!-- =========================================================
         CONFIRMATION SUPPRESSION
    ========================================================== -->
    <div
      v-if="showDeleteModal"
      class="modal-overlay"
      @click.self="closeDeleteModal"
    >
      <div class="confirm-modal">

        <div class="confirm-icon">
          <Trash2 :size="24" />
        </div>

        <h3>Supprimer le document ?</h3>

        <p>
          Le document
          <strong>
            « {{ documentToDelete?.nom }} »
          </strong>
          sera définitivement supprimé.
        </p>

        <div class="confirm-actions">

          <button
            type="button"
            class="cancel-button"
            :disabled="deleting"
            @click="closeDeleteModal"
          >
            Annuler
          </button>

          <button
            type="button"
            class="danger-button"
            :disabled="deleting"
            @click="confirmDelete"
          >
            <Trash2 :size="16" />

            {{
              deleting
                ? 'Suppression...'
                : 'Supprimer'
            }}
          </button>

        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import {
  computed,
  onBeforeUnmount,
  onMounted,
  reactive,
  ref
} from 'vue'

import {
  AlertTriangle,
  Download,
  Eye,
  FileText,
  FolderOpen,
  Inbox,
  RefreshCw,
  RotateCcw,
  Search,
  Send,
  Trash2,
  X
} from 'lucide-vue-next'

import { useAuthStore } from '@/stores/auth'

/* =========================================================
   API
========================================================= */

const API_URL = 'http://127.0.0.1:8000/api'

const ARRIVES_ENDPOINT = '/courriers-arrives'
const DEPARTS_ENDPOINT = '/courriers-depart'

/* =========================================================
   AUTH
========================================================= */

const authStore = useAuthStore()

const canViewDocuments = computed(() =>
  authStore.hasPermission('documents.view')
)
const canDownloadDocuments = computed(() =>
  authStore.hasPermission('documents.download')
)

const canDeleteArrives = computed(() =>
  authStore.hasPermission('courriers_arrives.delete')
)

const canDeleteDeparts = computed(() =>
  authStore.hasPermission('courriers_depart.delete')
)

/*
 * Si ton système possède une permission spécifique
 * documents.delete, elle sera utilisée.
 */
const canDeleteDocuments = computed(() =>
  authStore.hasPermission('documents.delete')
)

/* =========================================================
   STATE
========================================================= */

const documents = ref([])

const loading = ref(false)

const showPreview = ref(false)
const previewLoading = ref(false)
const previewUrl = ref('')
const previewType = ref('')
const previewDocumentItem = ref(null)

const showDeleteModal = ref(false)
const documentToDelete = ref(null)
const deleting = ref(false)

/* =========================================================
   FILTERS
========================================================= */

const filters = reactive({
  search: '',
  type: '',
  extension: '',
  priorite: ''
})

/* =========================================================
   TOKEN
========================================================= */

function getToken() {
  return (
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token') ||
    ''
  )
}

function headers() {
  const token = getToken()

  return {
    Accept: 'application/json',
    ...(token
      ? {
          Authorization: `Bearer ${token}`
        }
      : {})
  }
}

/* =========================================================
   API REQUEST
========================================================= */

async function apiRequest(url, options = {}) {
  const response = await fetch(url, {
    ...options,
    headers: {
      ...headers(),
      ...options.headers
    }
  })

  if (response.status === 401) {
    localStorage.removeItem('auth_token')
    sessionStorage.removeItem('auth_token')
    localStorage.removeItem('utilisateur')
    sessionStorage.removeItem('utilisateur')

    window.location.href = '/login'

    throw new Error('Session expirée.')
  }

  const contentType =
    response.headers.get('content-type') || ''

  let data = null

  if (contentType.includes('application/json')) {
    data = await response.json()
  }

  if (!response.ok) {
    throw new Error(
      data?.message ||
      `Erreur HTTP ${response.status}`
    )
  }

  return {
    response,
    data
  }
}

/* =========================================================
   EXTRACTION
========================================================= */

function extractList(payload) {
  if (Array.isArray(payload)) {
    return payload
  }

  if (Array.isArray(payload?.data)) {
    return payload.data
  }

  if (Array.isArray(payload?.data?.data)) {
    return payload.data.data
  }

  return []
}

/* =========================================================
   DOCUMENT HELPERS
========================================================= */

function documentName(doc) {
  return (
    doc?.nom_original ??
    doc?.nom_document ??
    doc?.nom_fichier ??
    doc?.filename ??
    doc?.file_name ??
    doc?.name ??
    'Document'
  )
}

function documentSize(doc) {
  return Number(
    doc?.taille ??
    doc?.size ??
    doc?.taille_fichier ??
    0
  )
}

function documentId(doc) {
  return doc?.num_doc ?? null
}

function getExtension(filename) {
  if (!filename) {
    return ''
  }

  const parts = String(filename).split('.')

  if (parts.length < 2) {
    return ''
  }

  return parts.pop().toLowerCase()
}

function fileExtension(filename) {
  return (
    getExtension(filename).toUpperCase() ||
    'FILE'
  )
}

/* =========================================================
   DATE
========================================================= */

function formatDate(value) {
  if (!value) {
    return '—'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return '—'
  }

  return new Intl.DateTimeFormat(
    'fr-FR',
    {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    }
  ).format(date)
}

/* =========================================================
   FILE SIZE
========================================================= */

function formatFileSize(size) {
  const value = Number(size) || 0

  if (value < 1024) {
    return `${value} o`
  }

  if (value < 1024 * 1024) {
    return `${(value / 1024).toFixed(1)} Ko`
  }

  return `${(
    value /
    (1024 * 1024)
  ).toFixed(1)} Mo`
}

/* =========================================================
   PRIORITY
========================================================= */

function priorityLabel(priority) {
  switch (priority) {
    case 'URGENT':
      return 'Urgent'

    case 'TRES_URGENT':
      return 'Très urgent'

    case 'NORMAL':
    default:
      return 'Normal'
  }
}

function priorityClass(priority) {
  switch (priority) {
    case 'URGENT':
      return 'priority-urgent'

    case 'TRES_URGENT':
      return 'priority-very-urgent'

    default:
      return 'priority-normal'
  }
}

/* =========================================================
   FILE CLASS
========================================================= */

function fileIconClass(filename) {
  const extension = getExtension(filename)

  switch (extension) {
    case 'pdf':
      return 'file-pdf'

    case 'doc':
    case 'docx':
      return 'file-word'

    case 'xls':
    case 'xlsx':
      return 'file-excel'

    case 'jpg':
    case 'jpeg':
    case 'png':
      return 'file-image'

    default:
      return 'file-default'
  }
}

/* =========================================================
   NORMALISATION DOCUMENT ARRIVÉ
========================================================= */

function normalizeArriveDocument(
  document,
  courrier
) {
  const numDoc = documentId(document)

  return {
    key: `arrive-${courrier?.num_enreg_courr_arr}-${numDoc}`,

    type: 'arrive',

    documentId: numDoc,

    document,

    nom: documentName(document),

    taille: documentSize(document),

    courrierId:
      courrier?.num_enreg_courr_arr,

    numeroCourrier:
      courrier?.num_enreg_courr_arr ??
      '—',

    date:
      courrier?.date_enreg ??
      document?.created_at,

    objet:
      courrier?.objet_courr_arri ??
      '',

    priorite:
      courrier?.priorite ??
      'NORMAL'
  }
}

/* =========================================================
   NORMALISATION DOCUMENT DÉPART
========================================================= */

function normalizeDepartDocument(
  document,
  courrier
) {
  const numDoc = documentId(document)

  return {
    key: `depart-${courrier?.num_ordre_dep}-${numDoc}`,

    type: 'depart',

    documentId: numDoc,

    document,

    nom: documentName(document),

    taille: documentSize(document),

    courrierId:
      courrier?.num_ordre_dep,

    numeroCourrier:
      courrier?.num_ordre_dep ??
      '—',

    date:
      courrier?.date_dep ??
      document?.created_at,

    objet:
      courrier?.objet_courr_dep ??
      '',

    priorite:
      courrier?.priorite ??
      'NORMAL'
  }
}

/* =========================================================
   CHARGEMENT ARRIVÉS
========================================================= */

async function loadArriveDocuments() {
  const documentsArrives = []

  let page = 1
  let lastPage = 1

  do {
    const url =
      `${API_URL}${ARRIVES_ENDPOINT}` +
      `?page=${page}&per_page=100`

    const { data } =
      await apiRequest(url)

    const payload =
      data?.data ?? data

    const courriers =
      extractList(payload)

    for (const courrier of courriers) {

      let documentsCourrier =
        Array.isArray(courrier?.documents)
          ? courrier.documents
          : null

      if (!documentsCourrier) {
        try {
          const result =
            await apiRequest(
              `${API_URL}${ARRIVES_ENDPOINT}/${encodeURIComponent(
                courrier.num_enreg_courr_arr
              )}/documents`
            )

          documentsCourrier =
            extractList(result.data)
        } catch (error) {
          console.warn(
            'Impossible de charger les documents du courrier arrivé:',
            courrier?.num_enreg_courr_arr,
            error
          )

          documentsCourrier = []
        }
      }

      for (const document of documentsCourrier) {
        documentsArrives.push(
          normalizeArriveDocument(
            document,
            courrier
          )
        )
      }
    }

    lastPage =
      Number(
        payload?.last_page ??
        data?.last_page ??
        1
      )

    page += 1

  } while (page <= lastPage)

  return documentsArrives
}

/* =========================================================
   CHARGEMENT DÉPARTS
========================================================= */

async function loadDepartDocuments() {
  const documentsDeparts = []

  let page = 1
  let lastPage = 1

  do {
    const url =
      `${API_URL}${DEPARTS_ENDPOINT}` +
      `?page=${page}&per_page=100`

    const { data } =
      await apiRequest(url)

    const payload =
      data?.data ?? data

    const courriers =
      extractList(payload)

    for (const courrier of courriers) {

      let documentsCourrier =
        Array.isArray(courrier?.documents)
          ? courrier.documents
          : null

      if (!documentsCourrier) {
        try {
          const result =
            await apiRequest(
              `${API_URL}${DEPARTS_ENDPOINT}/${encodeURIComponent(
                courrier.num_ordre_dep
              )}/documents`
            )

          documentsCourrier =
            extractList(result.data)
        } catch (error) {
          console.warn(
            'Impossible de charger les documents du courrier départ:',
            courrier?.num_ordre_dep,
            error
          )

          documentsCourrier = []
        }
      }

      for (const document of documentsCourrier) {
        documentsDeparts.push(
          normalizeDepartDocument(
            document,
            courrier
          )
        )
      }
    }

    lastPage =
      Number(
        payload?.last_page ??
        data?.last_page ??
        1
      )

    page += 1

  } while (page <= lastPage)

  return documentsDeparts
}

/* =========================================================
   CHARGER TOUS LES DOCUMENTS
========================================================= */

async function loadAllDocuments() {
  loading.value = true

  try {
    const [
      arrives,
      departs
    ] = await Promise.all([
      loadArriveDocuments(),
      loadDepartDocuments()
    ])

    documents.value = [
      ...arrives,
      ...departs
    ].filter(
      item => item.documentId
    )

  } catch (error) {
    console.error(
      'Erreur chargement documents numériques:',
      error
    )

    documents.value = []

  } finally {
    loading.value = false
  }
}

/* =========================================================
   FILTERED DOCUMENTS
========================================================= */

const filteredDocuments = computed(() => {
  const search =
    filters.search
      .trim()
      .toLowerCase()

  return documents.value.filter(
    item => {

      if (
        filters.type &&
        item.type !== filters.type
      ) {
        return false
      }

      if (
        filters.extension &&
        getExtension(item.nom) !==
          filters.extension
      ) {
        return false
      }

      if (
        filters.priorite &&
        item.priorite !==
          filters.priorite
      ) {
        return false
      }

      if (search) {
        const content = [
          item.nom,
          item.numeroCourrier,
          item.objet,
          item.type,
          item.priorite
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase()

        if (!content.includes(search)) {
          return false
        }
      }

      return true
    }
  )
})

/* =========================================================
   STATS
========================================================= */

const totalArrives = computed(() =>
  documents.value.filter(
    item => item.type === 'arrive'
  ).length
)

const totalDeparts = computed(() =>
  documents.value.filter(
    item => item.type === 'depart'
  ).length
)

const totalUrgents = computed(() =>
  documents.value.filter(
    item =>
      item.priorite === 'URGENT' ||
      item.priorite === 'TRES_URGENT'
  ).length
)

/* =========================================================
   TYPE CARD
========================================================= */

function documentTypeClass(item) {
  return item.type === 'arrive'
    ? 'card-arrive'
    : 'card-depart'
}

/* =========================================================
   PREVIEW
========================================================= */

function canPreview(item) {
  return [
    'pdf',
    'jpg',
    'jpeg',
    'png'
  ].includes(
    getExtension(item.nom)
  )
}

async function previewDocument(item) {
  if (!canViewDocuments.value) {
    return
  }

  if (!item.documentId) {
    return
  }

  previewDocumentItem.value = item
  previewLoading.value = true
  showPreview.value = true
  previewUrl.value = ''

  const extension =
    getExtension(item.nom)

  try {
    const endpoint =
      item.type === 'arrive'
        ? `${ARRIVES_ENDPOINT}/${encodeURIComponent(
            item.courrierId
          )}/documents/${encodeURIComponent(
            item.documentId
          )}/view`
        : `${DEPARTS_ENDPOINT}/${encodeURIComponent(
            item.courrierId
          )}/documents/${encodeURIComponent(
            item.documentId
          )}/download`

    const { response } =
      await apiRequest(
        `${API_URL}${endpoint}`
      )

    const blob =
      await response.blob()

    let mimeType =
      response.headers.get(
        'content-type'
      ) ||
      blob.type

    if (
      !mimeType ||
      mimeType ===
        'application/octet-stream'
    ) {
      if (extension === 'pdf') {
        mimeType =
          'application/pdf'
      } else if (
        ['jpg', 'jpeg'].includes(extension)
      ) {
        mimeType =
          'image/jpeg'
      } else if (
        extension === 'png'
      ) {
        mimeType =
          'image/png'
      }
    }

    const previewBlob =
      new Blob(
        [blob],
        {
          type: mimeType
        }
      )

    previewUrl.value =
      URL.createObjectURL(
        previewBlob
      )

    previewType.value =
      extension === 'pdf'
        ? 'pdf'
        : [
            'jpg',
            'jpeg',
            'png'
          ].includes(extension)
          ? 'image'
          : 'other'

  } catch (error) {
    console.error(
      'Erreur preview:',
      error
    )

    closePreview()

  } finally {
    previewLoading.value = false
  }
}

/* =========================================================
   DOWNLOAD
========================================================= */

async function downloadDocument(item) {
  if (!canDownloadDocuments.value) {
    return
  }

  if (!item.documentId) {
    return
  }

  try {
    const endpoint =
      item.type === 'arrive'
        ? `${ARRIVES_ENDPOINT}/${encodeURIComponent(
            item.courrierId
          )}/documents/${encodeURIComponent(
            item.documentId
          )}/download?_t=${Date.now()}`
        : `${DEPARTS_ENDPOINT}/${encodeURIComponent(
            item.courrierId
          )}/documents/${encodeURIComponent(
            item.documentId
          )}/download?_t=${Date.now()}`

    const { response } =
      await apiRequest(
        `${API_URL}${endpoint}`
      )

    const blob =
      await response.blob()

    const url =
      URL.createObjectURL(blob)

    const link =
      document.createElement('a')

    link.href = url
    link.download = item.nom
    link.style.display = 'none'

    document.body.appendChild(link)
    link.click()
    link.remove()

    setTimeout(() => {
      URL.revokeObjectURL(url)
    }, 1000)

  } catch (error) {
    console.error(
      'Erreur téléchargement:',
      error
    )
  }
}

/* =========================================================
   DELETE
========================================================= */

function askDeleteDocument(item) {
  documentToDelete.value = item
  showDeleteModal.value = true
}

function closeDeleteModal() {
  if (deleting.value) {
    return
  }

  showDeleteModal.value = false
  documentToDelete.value = null
}
async function confirmDelete() {
  const item = documentToDelete.value

  if (!item || !item.documentId) {
    return
  }

  deleting.value = true

  try {
    const endpoint =
      item.type === 'arrive'
        ? `${ARRIVES_ENDPOINT}/${encodeURIComponent(
            item.courrierId
          )}/documents/${encodeURIComponent(
            item.documentId
          )}`
        : `${DEPARTS_ENDPOINT}/${encodeURIComponent(
            item.courrierId
          )}/documents/${encodeURIComponent(
            item.documentId
          )}`

    await apiRequest(
      `${API_URL}${endpoint}`,
      {
        method: 'DELETE'
      }
    )

    // Esorina avy hatrany ao amin'ny liste
    documents.value = documents.value.filter(
      document =>
        document.key !== item.key
    )

    // Raha preview-n'ilay document no misokatra
    if (
      previewDocumentItem.value?.key === item.key
    ) {
      closePreview()
    }

    // Akatona ilay modal de confirmation
    showDeleteModal.value = false

    // Diovina ny document sélectionné
    documentToDelete.value = null

  } catch (error) {
    console.error(
      'Erreur suppression document:',
      error
    )

  } finally {
    deleting.value = false
  }
}
/* =========================================================
   PREVIEW CLOSE
========================================================= */

function closePreview() {
  showPreview.value = false

  if (previewUrl.value) {
    URL.revokeObjectURL(
      previewUrl.value
    )
  }

  previewUrl.value = ''
  previewType.value = ''
  previewDocumentItem.value = null
  previewLoading.value = false
}

/* =========================================================
   RESET FILTERS
========================================================= */

function resetFilters() {
  filters.search = ''
  filters.type = ''
  filters.extension = ''
  filters.priorite = ''
}

/* =========================================================
   ESCAPE
========================================================= */

function handleEscape(event) {
  if (event.key !== 'Escape') {
    return
  }

  if (showDeleteModal.value) {
    closeDeleteModal()
    return
  }

  if (showPreview.value) {
    closePreview()
  }
}

/* =========================================================
   MOUNT
========================================================= */

onMounted(async () => {
  await authStore.loadCurrentUser()

  document.addEventListener(
    'keydown',
    handleEscape
  )

  await loadAllDocuments()
})

/* =========================================================
   UNMOUNT
========================================================= */

onBeforeUnmount(() => {
  document.removeEventListener(
    'keydown',
    handleEscape
  )

  if (previewUrl.value) {
    URL.revokeObjectURL(
      previewUrl.value
    )
  }
})
</script>

<style scoped>
.documents-page {
  min-height: 100%;
  padding: 24px;
  background: #f5f7fb;
}

/* =========================================================
   HEADER
========================================================= */

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
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 13px;
  color: #334155;
  background: #e8edf5;
}

.page-header h1 {
  margin: 0;
  color: #172033;
  font-size: 24px;
  font-weight: 750;
}

.page-header p {
  margin: 5px 0 0;
  color: #64748b;
  font-size: 13px;
}

.refresh-button,
.reset-button {
  min-height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 1px solid #a7bad7;
  border-radius: 9px;
  padding: 0 14px;
  color: #0b03f2;
  background: #f6f3f3;
  cursor: pointer;
}

.refresh-button:hover,
.reset-button:hover {
  background: #f8fafc;
}

.refresh-button:disabled {
  opacity: .6;
  cursor: not-allowed;
}

/* =========================================================
   STATS
========================================================= */

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}

.stat-card {
  min-height: 82px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px;
  border: 1px solid #e3e8ef;
  border-radius: 12px;
  background: #ffffff;
}

.stat-icon {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
}

.stat-icon-total {
  color: #475569;
  background: #eef2f7;
}

.stat-icon-arrive {
  color: #2563eb;
  background: #eff6ff;
}

.stat-icon-depart {
  color: #6366f1;
  background: #eef2ff;
}

.stat-icon-urgent {
  color: #ea580c;
  background: #fff7ed;
}

.stat-card span {
  display: block;
  color: #64748b;
  font-size: 12px;
}

.stat-card strong {
  display: block;
  margin-top: 3px;
  color: #172033;
  font-size: 21px;
}

/* =========================================================
   FILTERS
========================================================= */

.filters-card {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  padding: 13px;
  margin-bottom: 20px;
  border: 1px solid #e3e8ef;
  border-radius: 12px;
  background: #ffffff;
}

.search-wrapper {
  position: relative;
  flex: 1 1 280px;
  min-width: 240px;
}

.search-wrapper > svg {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-wrapper input,
.filters-card select {
  height: 40px;
  border: 1px solid #dbe1ea;
  border-radius: 8px;
  outline: none;
  background: #ffffff;
  color: #334155;
  font-size: 13px;
}

.search-wrapper input {
  width: 100%;
  padding: 0 38px;
}

.filters-card select {
  min-width: 145px;
  padding: 0 10px;
}

.search-wrapper input:focus,
.filters-card select:focus {
  border-color: #94a3b8;
}

.clear-search {
  position: absolute;
  right: 8px;
  top: 50%;
  width: 26px;
  height: 26px;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 6px;
  color: #64748b;
  background: transparent;
  cursor: pointer;
}

/* =========================================================
   SECTION
========================================================= */

.documents-section {
  min-width: 0;
}

.section-header {
  margin-bottom: 13px;
}

.section-header h2 {
  margin: 0;
  color: #1e293b;
  font-size: 17px;
}

.section-header p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 12px;
}

/* =========================================================
   GRID
========================================================= */

.documents-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

/* =========================================================
   CARD
========================================================= */

.document-card {
  position: relative;
  min-width: 0;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: #ffffff;
  transition:
    transform .18s ease,
    box-shadow .18s ease,
    border-color .18s ease;
}

.document-card::before {
  content: '';
  position: absolute;
  inset: 0 auto 0 0;
  width: 3px;
}

.card-arrive::before {
  background: darkblue;
}

.card-depart::before {
  background: darkgreen;
}

.document-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
}

.card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 7px;
  padding: 12px 12px 8px 15px;
}

.document-origin,
.priority-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  min-height: 23px;
  padding: 0 7px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
}

.origin-arrive {
  color: darkblue;
  background: #eff6ff;
}

.origin-depart {
  color: darkgreen;
  background: #eef2ff;
}

.priority-normal {
  color: #64748b;
  background: #f1f5f9;
}

.priority-urgent {
  color: #c2410c;
  background: #fff7ed;
}

.priority-very-urgent {
  color: #b91c1c;
  background: #fef2f2;
}

/* =========================================================
   FILE
========================================================= */

.file-area {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 7px 15px 12px;
}

.file-icon {
  position: relative;
  flex: 0 0 48px;
  width: 48px;
  height: 55px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.file-icon span {
  position: absolute;
  bottom: 3px;
  font-size: 8px;
  font-weight: 800;
}

.file-pdf {
  color: #dc2626;
  background: #fef2f2;
}

.file-word {
  color: #2563eb;
  background: #eff6ff;
}

.file-excel {
  color: #15803d;
  background: #f0fdf4;
}

.file-image {
  color: #7c3aed;
  background: #f5f3ff;
}

.file-default {
  color: #64748b;
  background: #f1f5f9;
}

.file-information {
  min-width: 0;
}

.file-information h3 {
  overflow: hidden;
  margin: 0 0 4px;
  color: #1e293b;
  font-size: 13px;
  font-weight: 650;
  line-height: 1.35;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-information span {
  color: #94a3b8;
  font-size: 11px;
}

/* =========================================================
   COURRIER INFO
========================================================= */

.courrier-information {
  padding: 0 15px 11px;
}

.information-line {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  padding: 3px 0;
}

.information-line span {
  color: #94a3b8;
  font-size: 11px;
}

.information-line strong {
  overflow: hidden;
  color: #475569;
  font-size: 11px;
  font-weight: 650;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.object-line {
  overflow: hidden;
  margin-top: 7px;
  color: #64748b;
  font-size: 11px;
  line-height: 1.45;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* =========================================================
   ACTIONS
========================================================= */

.card-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 11px 11px 15px;
  border-top: 1px solid #edf0f4;
}

.action-button,
.icon-action {
  min-height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  border-radius: 7px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
}

.action-button {
  flex: 1;
  padding: 0 8px;
  border: 1px solid #dbe1ea;
  background: #ffffff;
  color: #475569;
}

.action-view:hover {
  color: #1d4ed8;
  border-color: #bfdbfe;
  background: #eff6ff;
}

.action-download:hover {
  color: #4f46e5;
  border-color: #c7d2fe;
  background: #eef2ff;
}

.icon-action {
  width: 32px;
  flex: 0 0 32px;
  border: 1px solid #fecaca;
  color: #dc2626;
  background: #fff;
}

.icon-action:hover {
  color: #b91c1c;
  background: #fef2f2;
}

/* =========================================================
   STATES
========================================================= */

.state-box {
  min-height: 250px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 1px dashed #dbe1ea;
  border-radius: 12px;
  background: #ffffff;
  color: #64748b;
}

.empty-icon {
  width: 58px;
  height: 58px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: #94a3b8;
  background: #f1f5f9;
}

.state-box h3 {
  margin: 0;
  color: #334155;
  font-size: 15px;
}

.state-box p {
  max-width: 400px;
  margin: 0;
  text-align: center;
  color: #94a3b8;
  font-size: 12px;
}

/* =========================================================
   MODAL
========================================================= */

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(15, 23, 42, .58);
  backdrop-filter: blur(3px);
}

.preview-modal {
  width: min(1000px, 95vw);
  height: min(750px, 90vh);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  border-radius: 13px;
  background: #08264d;
  box-shadow: 0 25px 70px rgba(15, 23, 42, .25);
}

.preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
}

.preview-header span {
  color: #f9f9f9;
  font-size: 12px;
}

.preview-header h3 {
  overflow: hidden;
  margin: 3px 0 0;
  color: #1e293b;
  font-size: 14px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.close-button {
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 8px;
  color: #64748b;
  background: #f1f5f9;
  cursor: pointer;
}

.preview-content {
  flex: 1;
  min-height: 0;
  background: #f1f5f9;
}

.preview-frame {
  width: 100%;
  height: 100%;
  border: 0;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.preview-loading,
.unsupported-preview {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #64748b;
  font-size: 13px;
}

.unsupported-preview p {
  margin: 0;
}

/* =========================================================
   CONFIRMATION
========================================================= */

.confirm-modal {
  width: min(430px, 94vw);
  padding: 24px;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 25px 70px rgba(15, 23, 42, .25);
}

.confirm-icon {
  width: 46px;
  height: 46px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 13px;
  border-radius: 11px;
  color: #dc2626;
  background: #fef2f2;
}

.confirm-modal h3 {
  margin: 0;
  color: #1e293b;
  font-size: 17px;
}

.confirm-modal p {
  margin: 9px 0 20px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.55;
}

.confirm-modal strong {
  color: #334155;
}

.confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.cancel-button,
.danger-button {
  min-height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 0 14px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 650;
}

.cancel-button {
  border: 1px solid #dbe1ea;
  color: #475569;
  background: #ffffff;
}

.danger-button {
  border: 1px solid #dc2626;
  color: #ffffff;
  background: #dc2626;
}

.cancel-button:disabled,
.danger-button:disabled {
  opacity: .6;
  cursor: not-allowed;
}

/* =========================================================
   ANIMATION
========================================================= */

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1250px) {
  .documents-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 850px) {
  .documents-page {
    padding: 16px;
  }

  .documents-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .page-header {
    align-items: flex-start;
  }

  .filters-card {
    align-items: stretch;
  }

  .filters-card select,
  .reset-button {
    flex: 1;
  }
}

@media (max-width: 600px) {
  .page-header {
    flex-direction: column;
  }

  .refresh-button {
    width: 100%;
  }

  .stats-grid,
  .documents-grid {
    grid-template-columns: 1fr;
  }

  .search-wrapper {
    min-width: 100%;
  }

  .filters-card select,
  .reset-button {
    width: 100%;
    flex: auto;
  }

  .card-actions {
    flex-wrap: wrap;
  }

  .action-button {
    min-width: 0;
  }
}
</style>