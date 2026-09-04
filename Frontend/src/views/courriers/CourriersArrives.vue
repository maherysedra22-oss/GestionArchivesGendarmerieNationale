<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../api/api'

/* =========================================================
   ÉTAT PRINCIPAL
========================================================= */

const courriers = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

const search = ref('')
const filtrePriorite = ref('')
const dateDebut = ref('')
const dateFin = ref('')

/* =========================================================
   MODALS
========================================================= */

const showDetailModal = ref(false)
const showFormModal = ref(false)
const showDeleteModal = ref(false)

const modeFormulaire = ref('create')

const courrierSelectionne = ref(null)

/* =========================================================
   FORMULAIRE
========================================================= */

const formulaire = ref({
  id: null,
  reference: '',
  date_enreg: '',
  num_ordre_orig: '',
  lib_orig: '',
  objet_courr_arri: '',
  priorite: 'NORMAL',
  classement_id: '',
  observations: ''
})

/* =========================================================
   PAGINATION
========================================================= */

const currentPage = ref(1)
const lastPage = ref(1)
const totalCourriers = ref(0)

const perPage = ref(10)

/* =========================================================
   STATISTIQUES
========================================================= */

const total = computed(() => courriers.value.length)

const urgents = computed(() => {
  return courriers.value.filter(
    courrier =>
      courrier.priorite === 'URGENT' ||
      courrier.priorite === 'TRES_URGENT' ||
      courrier.priorite === 'TRÈS URGENT'
  ).length
})

const normaux = computed(() => {
  return courriers.value.filter(
    courrier => courrier.priorite === 'NORMAL'
  ).length
})

/* =========================================================
   CHARGEMENT DES COURRIERS
========================================================= */

async function chargerCourriers(page = 1) {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get(
      `/courriers-arrives?page=${page}&per_page=${perPage.value}`
    )

    const pagination = response.data

    courriers.value = pagination?.data || []

    currentPage.value = pagination?.current_page || 1
    lastPage.value = pagination?.last_page || 1
    totalCourriers.value = pagination?.total || courriers.value.length
  } catch (error) {
    console.error(error)

    errorMessage.value =
      error.message || 'Impossible de récupérer les courriers arrivés.'
  } finally {
    loading.value = false
  }
}

/* =========================================================
   RECHERCHE + FILTRES
========================================================= */

const courriersFiltres = computed(() => {
  const terme = search.value.toLowerCase().trim()

  return courriers.value.filter(courrier => {
    const correspondRecherche =
      !terme ||
      String(courrier.num_enreg_courr_arr || '')
        .toLowerCase()
        .includes(terme) ||
      String(courrier.reference || '')
        .toLowerCase()
        .includes(terme) ||
      String(courrier.lib_orig || '')
        .toLowerCase()
        .includes(terme) ||
      String(courrier.objet_courr_arri || '')
        .toLowerCase()
        .includes(terme)

    const correspondPriorite =
      !filtrePriorite.value ||
      courrier.priorite === filtrePriorite.value

    const dateCourrier = courrier.date_enreg
      ? courrier.date_enreg.substring(0, 10)
      : ''

    const correspondDateDebut =
      !dateDebut.value ||
      (dateCourrier && dateCourrier >= dateDebut.value)

    const correspondDateFin =
      !dateFin.value ||
      (dateCourrier && dateCourrier <= dateFin.value)

    return (
      correspondRecherche &&
      correspondPriorite &&
      correspondDateDebut &&
      correspondDateFin
    )
  })
})

/* =========================================================
   FORMATAGE
========================================================= */

function formaterDate(date) {
  if (!date) return '—'

  const d = new Date(date)

  if (Number.isNaN(d.getTime())) return '—'

  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(d)
}

/* =========================================================
   PRIORITÉ
========================================================= */

function getPrioriteClass(priorite) {
  switch (priorite) {
    case 'URGENT':
      return 'priority-urgent'

    case 'TRES_URGENT':
    case 'TRÈS URGENT':
      return 'priority-tres-urgent'

    case 'NORMAL':
    default:
      return 'priority-normal'
  }
}

/* =========================================================
   OUVRIR DÉTAIL
========================================================= */

function ouvrirDetails(courrier) {
  courrierSelectionne.value = courrier
  showDetailModal.value = true
}

function fermerDetails() {
  showDetailModal.value = false
  courrierSelectionne.value = null
}

/* =========================================================
   FORMULAIRE : NOUVEAU
========================================================= */

function nouveauCourrier() {
  modeFormulaire.value = 'create'

  formulaire.value = {
    id: null,
    reference: '',
    date_enreg: '',
    num_ordre_orig: '',
    lib_orig: '',
    objet_courr_arri: '',
    priorite: 'NORMAL',
    classement_id: '',
    observations: ''
  }

  errorMessage.value = ''
  successMessage.value = ''

  showFormModal.value = true
}

/* =========================================================
   FORMULAIRE : MODIFIER
========================================================= */

function modifierCourrier(courrier) {
  modeFormulaire.value = 'edit'

  formulaire.value = {
    id: courrier.id,
    reference: courrier.reference || '',
    date_enreg: courrier.date_enreg
      ? courrier.date_enreg.substring(0, 10)
      : '',
    num_ordre_orig: courrier.num_ordre_orig || '',
    lib_orig: courrier.lib_orig || '',
    objet_courr_arri: courrier.objet_courr_arri || '',
    priorite: courrier.priorite || 'NORMAL',
    classement_id: courrier.classement_id || '',
    observations: courrier.observations || ''
  }

  errorMessage.value = ''
  successMessage.value = ''

  showFormModal.value = true
}

function fermerFormulaire() {
  if (saving.value) return

  showFormModal.value = false
}

/* =========================================================
   VALIDATION
========================================================= */

function validerFormulaire() {
  if (!formulaire.value.reference.trim()) {
    errorMessage.value = 'La référence est obligatoire.'
    return false
  }

  if (!formulaire.value.date_enreg) {
    errorMessage.value = 'La date d’enregistrement est obligatoire.'
    return false
  }

  if (!formulaire.value.lib_orig.trim()) {
    errorMessage.value = "L'origine du courrier est obligatoire."
    return false
  }

  if (!formulaire.value.objet_courr_arri.trim()) {
    errorMessage.value = "L'objet du courrier est obligatoire."
    return false
  }

  if (!formulaire.value.priorite) {
    errorMessage.value = 'La priorité est obligatoire.'
    return false
  }

  return true
}

/* =========================================================
   CRÉER / MODIFIER
========================================================= */

async function enregistrerCourrier() {
  errorMessage.value = ''
  successMessage.value = ''

  if (!validerFormulaire()) return

  saving.value = true

  try {
    const payload = {
      reference: formulaire.value.reference,
      date_enreg: formulaire.value.date_enreg,
      num_ordre_orig: formulaire.value.num_ordre_orig || null,
      lib_orig: formulaire.value.lib_orig,
      objet_courr_arri: formulaire.value.objet_courr_arri,
      priorite: formulaire.value.priorite,
      classement_id: formulaire.value.classement_id || null,
      observations: formulaire.value.observations || null
    }

    if (modeFormulaire.value === 'create') {
      await api.post('/courriers-arrives', payload)

      successMessage.value =
        'Courrier arrivé créé avec succès.'
    } else {
      await api.put(
        `/courriers-arrives/${formulaire.value.id}`,
        payload
      )

      successMessage.value =
        'Courrier arrivé modifié avec succès.'
    }

    showFormModal.value = false

    await chargerCourriers(currentPage.value)

    afficherSucces(successMessage.value)
  } catch (error) {
    console.error(error)

    errorMessage.value =
      error.message || 'Une erreur est survenue lors de l’enregistrement.'
  } finally {
    saving.value = false
  }
}

/* =========================================================
   SUPPRESSION
========================================================= */

function demanderSuppression(courrier) {
  courrierSelectionne.value = courrier
  showDeleteModal.value = true
}

function fermerSuppression() {
  if (deleting.value) return

  showDeleteModal.value = false
  courrierSelectionne.value = null
}

async function supprimerCourrier() {
  if (!courrierSelectionne.value?.id) return

  deleting.value = true
  errorMessage.value = ''

  try {
    await api.delete(
      `/courriers-arrives/${courrierSelectionne.value.id}`
    )

    showDeleteModal.value = false

    courrierSelectionne.value = null

    await chargerCourriers(currentPage.value)

    afficherSucces('Courrier arrivé supprimé avec succès.')
  } catch (error) {
    console.error(error)

    errorMessage.value =
      error.message || 'Impossible de supprimer ce courrier.'
  } finally {
    deleting.value = false
  }
}

/* =========================================================
   MESSAGE SUCCÈS
========================================================= */

function afficherSucces(message) {
  successMessage.value = message

  setTimeout(() => {
    successMessage.value = ''
  }, 4000)
}

/* =========================================================
   RESET FILTRES
========================================================= */

function reinitialiserFiltres() {
  search.value = ''
  filtrePriorite.value = ''
  dateDebut.value = ''
  dateFin.value = ''
}

/* =========================================================
   PAGINATION
========================================================= */

function allerPage(page) {
  if (page < 1 || page > lastPage.value) return

  chargerCourriers(page)
}

/* =========================================================
   INITIALISATION
========================================================= */

onMounted(() => {
  chargerCourriers()
})
</script>

<template>
  <div class="courriers-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <section class="page-header">

      <div class="header-content">

        <div class="header-icon">
          📥
        </div>

        <div>
          <span class="page-kicker">
            GESTION DU COURRIER
          </span>

          <h1>
            Courriers arrivés
          </h1>

          <p>
            Consultez, recherchez et gérez les courriers reçus.
          </p>
        </div>

      </div>

      <button
        type="button"
        class="btn-primary"
        @click="nouveauCourrier"
      >
        <span>＋</span>
        Nouveau courrier
      </button>

    </section>


    <!-- =====================================================
         MESSAGE SUCCÈS
    ====================================================== -->

    <Transition name="alert">

      <div
        v-if="successMessage"
        class="alert success-alert"
      >
        <span class="alert-icon">✓</span>

        <span>
          {{ successMessage }}
        </span>

        <button
          type="button"
          @click="successMessage = ''"
        >
          ✕
        </button>
      </div>

    </Transition>


    <!-- =====================================================
         MESSAGE ERREUR
    ====================================================== -->

    <Transition name="alert">

      <div
        v-if="errorMessage"
        class="alert error-alert"
      >
        <span class="alert-icon">!</span>

        <span>
          {{ errorMessage }}
        </span>

        <button
          type="button"
          @click="errorMessage = ''"
        >
          ✕
        </button>
      </div>

    </Transition>


    <!-- =====================================================
         STATISTIQUES
    ====================================================== -->

    <section class="stats-grid">

      <div class="stat-card">

        <div class="stat-icon blue">
          📥
        </div>

        <div class="stat-info">
          <span>Total courriers</span>

          <strong>
            {{ totalCourriers || total }}
          </strong>
        </div>

      </div>


      <div class="stat-card">

        <div class="stat-icon green">
          ✓
        </div>

        <div class="stat-info">
          <span>Priorité normale</span>

          <strong>
            {{ normaux }}
          </strong>
        </div>

      </div>


      <div class="stat-card">

        <div class="stat-icon orange">
          !
        </div>

        <div class="stat-info">
          <span>Courriers urgents</span>

          <strong>
            {{ urgents }}
          </strong>
        </div>

      </div>


      <div class="stat-card">

        <div class="stat-icon purple">
          ◷
        </div>

        <div class="stat-info">
          <span>Page actuelle</span>

          <strong>
            {{ currentPage }}
          </strong>
        </div>

      </div>

    </section>


    <!-- =====================================================
         FILTRES
    ====================================================== -->

    <section class="filter-card">

      <div class="filter-top">

        <div class="search-box">

          <span class="search-icon">
            🔎
          </span>

          <input
            v-model="search"
            type="text"
            placeholder="Rechercher par référence, origine, objet..."
          />

          <button
            v-if="search"
            type="button"
            class="clear-search"
            @click="search = ''"
          >
            ✕
          </button>

        </div>


        <button
          type="button"
          class="refresh-btn"
          :disabled="loading"
          @click="chargerCourriers(currentPage)"
        >
          <span :class="{ spinning: loading }">
            ↻
          </span>

          Actualiser
        </button>

      </div>


      <div class="filter-bottom">

        <div class="filter-field">

          <label>
            Priorité
          </label>

          <select v-model="filtrePriorite">

            <option value="">
              Toutes les priorités
            </option>

            <option value="NORMAL">
              Normal
            </option>

            <option value="URGENT">
              Urgent
            </option>

            <option value="TRES_URGENT">
              Très urgent
            </option>

          </select>

        </div>


        <div class="filter-field">

          <label>
            Date début
          </label>

          <input
            v-model="dateDebut"
            type="date"
          />

        </div>


        <div class="filter-field">

          <label>
            Date fin
          </label>

          <input
            v-model="dateFin"
            type="date"
          />

        </div>


        <button
          type="button"
          class="reset-btn"
          @click="reinitialiserFiltres"
        >
          Réinitialiser
        </button>

      </div>

    </section>


    <!-- =====================================================
         TABLEAU
    ====================================================== -->

    <section class="table-card">

      <div class="table-header">

        <div>

          <h2>
            Liste des courriers
          </h2>

          <p>
            {{ courriersFiltres.length }} résultat(s) affiché(s)
          </p>

        </div>

        <span class="result-count">
          {{ totalCourriers }} courrier(s)
        </span>

      </div>


      <!-- LOADING -->

      <div
        v-if="loading"
        class="loading-state"
      >

        <div class="loader"></div>

        <p>
          Chargement des courriers...
        </p>

      </div>


      <!-- EMPTY -->

      <div
        v-else-if="courriersFiltres.length === 0"
        class="empty-state"
      >

        <div class="empty-icon">
          📭
        </div>

        <h3>
          Aucun courrier trouvé
        </h3>

        <p>
          Aucun courrier ne correspond aux critères de recherche.
        </p>

        <button
          type="button"
          class="btn-secondary"
          @click="reinitialiserFiltres"
        >
          Réinitialiser les filtres
        </button>

      </div>


      <!-- TABLE -->

      <div
        v-else
        class="table-wrapper"
      >

        <table>

          <thead>

            <tr>

              <th>
                N°
              </th>

              <th>
                Référence
              </th>

              <th>
                Date
              </th>

              <th>
                Origine
              </th>

              <th>
                Objet
              </th>

              <th>
                Priorité
              </th>

              <th class="actions-column">
                Actions
              </th>

            </tr>

          </thead>


          <tbody>

            <tr
              v-for="courrier in courriersFiltres"
              :key="courrier.id"
            >

              <td>

                <span class="number-cell">
                  {{ courrier.num_enreg_courr_arr || '—' }}
                </span>

              </td>


              <td>

                <div class="reference-cell">

                  <strong>
                    {{ courrier.reference || '—' }}
                  </strong>

                </div>

              </td>


              <td>

                <span class="date-cell">
                  {{ formaterDate(courrier.date_enreg) }}
                </span>

              </td>


              <td>

                <div class="origin-cell">
                  {{ courrier.lib_orig || '—' }}
                </div>

              </td>


              <td>

                <div class="object-cell">
                  {{ courrier.objet_courr_arri || '—' }}
                </div>

              </td>


              <td>

                <span
                  class="priority-badge"
                  :class="getPrioriteClass(courrier.priorite)"
                >
                  <span class="priority-dot"></span>

                  {{ courrier.priorite || 'NORMAL' }}
                </span>

              </td>


              <td>

                <div class="actions">

                  <button
                    type="button"
                    class="action-btn view"
                    title="Voir le détail"
                    @click="ouvrirDetails(courrier)"
                  >
                    👁️
                  </button>


                  <button
                    type="button"
                    class="action-btn edit"
                    title="Modifier"
                    @click="modifierCourrier(courrier)"
                  >
                    ✏️
                  </button>


                  <button
                    type="button"
                    class="action-btn delete"
                    title="Supprimer"
                    @click="demanderSuppression(courrier)"
                  >
                    🗑️
                  </button>

                </div>

              </td>

            </tr>

          </tbody>

        </table>

      </div>


      <!-- PAGINATION -->

      <div
        v-if="!loading && courriersFiltres.length > 0"
        class="pagination"
      >

        <button
          type="button"
          :disabled="currentPage <= 1"
          @click="allerPage(currentPage - 1)"
        >
          ← Précédent
        </button>


        <div class="page-numbers">

          <button
            v-for="page in lastPage"
            :key="page"
            type="button"
            :class="{ active: page === currentPage }"
            @click="allerPage(page)"
          >
            {{ page }}
          </button>

        </div>


        <button
          type="button"
          :disabled="currentPage >= lastPage"
          @click="allerPage(currentPage + 1)"
        >
          Suivant →
        </button>

      </div>

    </section>


    <!-- =====================================================
         MODAL DÉTAIL
    ====================================================== -->

    <Transition name="modal">

      <div
        v-if="showDetailModal && courrierSelectionne"
        class="modal-overlay"
        @click.self="fermerDetails"
      >

        <div class="modal">

          <div class="modal-header">

            <div>

              <span class="modal-kicker">
                COURRIER ARRIVÉ
              </span>

              <h2>
                Détail du courrier
              </h2>

            </div>

            <button
              type="button"
              class="modal-close"
              @click="fermerDetails"
            >
              ✕
            </button>

          </div>


          <div class="modal-body">

            <div class="detail-reference">

              <div class="detail-reference-icon">
                📩
              </div>

              <div>

                <span>
                  Référence
                </span>

                <strong>
                  {{ courrierSelectionne.reference || '—' }}
                </strong>

              </div>

            </div>


            <div class="detail-grid">

              <div class="detail-item">

                <span>
                  Numéro d'enregistrement
                </span>

                <strong>
                  {{ courrierSelectionne.num_enreg_courr_arr || '—' }}
                </strong>

              </div>


              <div class="detail-item">

                <span>
                  Date d'enregistrement
                </span>

                <strong>
                  {{ formaterDate(courrierSelectionne.date_enreg) }}
                </strong>

              </div>


              <div class="detail-item">

                <span>
                  Numéro d'ordre original
                </span>

                <strong>
                  {{ courrierSelectionne.num_ordre_orig || '—' }}
                </strong>

              </div>


              <div class="detail-item">

                <span>
                  Priorité
                </span>

                <span
                  class="priority-badge"
                  :class="getPrioriteClass(courrierSelectionne.priorite)"
                >
                  <span class="priority-dot"></span>

                  {{ courrierSelectionne.priorite || 'NORMAL' }}
                </span>

              </div>


              <div class="detail-item full">

                <span>
                  Origine
                </span>

                <strong>
                  {{ courrierSelectionne.lib_orig || '—' }}
                </strong>

              </div>


              <div class="detail-item full">

                <span>
                  Objet
                </span>

                <p>
                  {{ courrierSelectionne.objet_courr_arri || '—' }}
                </p>

              </div>


              <div class="detail-item full">

                <span>
                  Observations
                </span>

                <p>
                  {{ courrierSelectionne.observations || 'Aucune observation.' }}
                </p>

              </div>

            </div>

          </div>


          <div class="modal-footer">

            <button
              type="button"
              class="btn-secondary"
              @click="fermerDetails"
            >
              Fermer
            </button>

            <button
              type="button"
              class="btn-primary"
              @click="fermerDetails(); modifierCourrier(courrierSelectionne)"
            >
              ✏️ Modifier
            </button>

          </div>

        </div>

      </div>

    </Transition>


    <!-- =====================================================
         MODAL CRÉATION / MODIFICATION
    ====================================================== -->

    <Transition name="modal">

      <div
        v-if="showFormModal"
        class="modal-overlay"
        @click.self="fermerFormulaire"
      >

        <div class="modal form-modal">

          <div class="modal-header">

            <div>

              <span class="modal-kicker">
                {{ modeFormulaire === 'create' ? 'NOUVEAU COURRIER' : 'MODIFICATION' }}
              </span>

              <h2>
                {{
                  modeFormulaire === 'create'
                    ? 'Créer un courrier arrivé'
                    : 'Modifier le courrier'
                }}
              </h2>

            </div>

            <button
              type="button"
              class="modal-close"
              :disabled="saving"
              @click="fermerFormulaire"
            >
              ✕
            </button>

          </div>


          <form
            class="form-body"
            @submit.prevent="enregistrerCourrier"
          >

            <div
              v-if="errorMessage"
              class="form-error"
            >
              ⚠️ {{ errorMessage }}
            </div>


            <div class="form-grid">

              <!-- Référence -->

              <div class="form-group">

                <label>
                  Référence
                  <span>*</span>
                </label>

                <input
                  v-model="formulaire.reference"
                  type="text"
                  placeholder="Ex : CA-2026-0001"
                  :disabled="saving"
                />

              </div>


              <!-- Date -->

              <div class="form-group">

                <label>
                  Date d'enregistrement
                  <span>*</span>
                </label>

                <input
                  v-model="formulaire.date_enreg"
                  type="date"
                  :disabled="saving"
                />

              </div>


              <!-- Numéro ordre -->

              <div class="form-group">

                <label>
                  Numéro d'ordre original
                </label>

                <input
                  v-model="formulaire.num_ordre_orig"
                  type="text"
                  placeholder="Ex : ORD-2026-001"
                  :disabled="saving"
                />

              </div>


              <!-- Priorité -->

              <div class="form-group">

                <label>
                  Priorité
                  <span>*</span>
                </label>

                <select
                  v-model="formulaire.priorite"
                  :disabled="saving"
                >

                  <option value="NORMAL">
                    Normal
                  </option>

                  <option value="URGENT">
                    Urgent
                  </option>

                  <option value="TRES_URGENT">
                    Très urgent
                  </option>

                </select>

              </div>


              <!-- Origine -->

              <div class="form-group full">

                <label>
                  Origine
                  <span>*</span>
                </label>

                <input
                  v-model="formulaire.lib_orig"
                  type="text"
                  placeholder="Ex : Ministère de la Défense"
                  :disabled="saving"
                />

              </div>


              <!-- Objet -->

              <div class="form-group full">

                <label>
                  Objet du courrier
                  <span>*</span>
                </label>

                <textarea
                  v-model="formulaire.objet_courr_arri"
                  rows="4"
                  placeholder="Saisissez l'objet du courrier..."
                  :disabled="saving"
                ></textarea>

              </div>


              <!-- Observations -->

              <div class="form-group full">

                <label>
                  Observations
                </label>

                <textarea
                  v-model="formulaire.observations"
                  rows="3"
                  placeholder="Informations complémentaires..."
                  :disabled="saving"
                ></textarea>

              </div>

            </div>


            <div class="form-footer">

              <button
                type="button"
                class="btn-secondary"
                :disabled="saving"
                @click="fermerFormulaire"
              >
                Annuler
              </button>

              <button
                type="submit"
                class="btn-primary"
                :disabled="saving"
              >

                <span
                  v-if="saving"
                  class="small-loader"
                ></span>

                <span v-else>
                  ✓
                </span>

                {{
                  saving
                    ? 'Enregistrement...'
                    : modeFormulaire === 'create'
                      ? 'Créer le courrier'
                      : 'Enregistrer les modifications'
                }}

              </button>

            </div>

          </form>

        </div>

      </div>

    </Transition>


    <!-- =====================================================
         MODAL SUPPRESSION
    ====================================================== -->

    <Transition name="modal">

      <div
        v-if="showDeleteModal && courrierSelectionne"
        class="modal-overlay"
        @click.self="fermerSuppression"
      >

        <div class="delete-modal">

          <div class="delete-icon">
            🗑️
          </div>

          <h2>
            Supprimer le courrier ?
          </h2>

          <p>
            Voulez-vous vraiment supprimer le courrier
            <strong>
              {{ courrierSelectionne.reference }}
            </strong>
            ?
          </p>

          <span class="warning-text">
            Cette action peut être irréversible.
          </span>


          <div class="delete-actions">

            <button
              type="button"
              class="btn-secondary"
              :disabled="deleting"
              @click="fermerSuppression"
            >
              Annuler
            </button>

            <button
              type="button"
              class="btn-danger"
              :disabled="deleting"
              @click="supprimerCourrier"
            >

              <span
                v-if="deleting"
                class="small-loader"
              ></span>

              <span v-else>
                🗑️
              </span>

              {{ deleting ? 'Suppression...' : 'Supprimer' }}

            </button>

          </div>

        </div>

      </div>

    </Transition>

  </div>
</template>


<style scoped>

/* =========================================================
   PAGE
========================================================= */

.courriers-page {
  width: 100%;
  min-height: 100%;
  padding: 28px;
  background: #f8fafc;
}


/* =========================================================
   HEADER
========================================================= */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 26px;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  width: 56px;
  height: 56px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 16px;

  background: linear-gradient(
    135deg,
    #1d4ed8,
    #2563eb
  );

  color: white;
  font-size: 25px;

  box-shadow:
    0 10px 25px rgba(37, 99, 235, 0.25);
}

.page-kicker {
  display: block;
  margin-bottom: 3px;

  font-size: 11px;
  font-weight: 800;

  letter-spacing: 0.08em;

  color: #64748b;
}

.page-header h1 {
  margin: 0;

  color: #0f172a;

  font-size: 28px;
  font-weight: 800;
}

.page-header p {
  margin: 5px 0 0;

  color: #64748b;

  font-size: 14px;
}


/* =========================================================
   BUTTONS
========================================================= */

.btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;

  min-height: 42px;

  padding: 0 17px;

  border: none;
  border-radius: 10px;

  background: #1d4ed8;

  color: white;

  font-size: 13px;
  font-weight: 700;

  cursor: pointer;

  box-shadow:
    0 5px 15px rgba(29, 78, 216, 0.2);

  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #1e40af;

  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;

  min-height: 42px;

  padding: 0 16px;

  border: 1px solid #cbd5e1;
  border-radius: 10px;

  background: white;

  color: #334155;

  font-size: 13px;
  font-weight: 700;

  cursor: pointer;
}

.btn-secondary:hover:not(:disabled) {
  background: #f8fafc;
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}


/* =========================================================
   ALERTS
========================================================= */

.alert {
  display: flex;
  align-items: center;
  gap: 11px;

  margin-bottom: 20px;
  padding: 13px 15px;

  border-radius: 11px;

  font-size: 13px;
  font-weight: 600;
}

.alert button {
  margin-left: auto;

  border: none;
  background: transparent;

  cursor: pointer;

  color: inherit;
}

.success-alert {
  border: 1px solid #bbf7d0;
  background: #f0fdf4;
  color: #166534;
}

.error-alert {
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
}

.alert-icon {
  width: 24px;
  height: 24px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: currentColor;

  color: white;
}


/* =========================================================
   STATISTIQUES
========================================================= */

.stats-grid {
  display: grid;

  grid-template-columns:
    repeat(4, minmax(0, 1fr));

  gap: 16px;

  margin-bottom: 20px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 14px;

  padding: 19px;

  border: 1px solid #e2e8f0;
  border-radius: 14px;

  background: white;

  box-shadow:
    0 3px 12px rgba(15, 23, 42, 0.04);
}

.stat-icon {
  width: 45px;
  height: 45px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 12px;

  font-size: 19px;
  font-weight: 800;
}

.stat-icon.blue {
  background: #eff6ff;
  color: #2563eb;
}

.stat-icon.green {
  background: #f0fdf4;
  color: #16a34a;
}

.stat-icon.orange {
  background: #fff7ed;
  color: #ea580c;
}

.stat-icon.purple {
  background: #faf5ff;
  color: #9333ea;
}

.stat-info span {
  display: block;

  margin-bottom: 3px;

  color: #64748b;

  font-size: 12px;
}

.stat-info strong {
  color: #0f172a;

  font-size: 22px;
}


/* =========================================================
   FILTER
========================================================= */

.filter-card {
  margin-bottom: 20px;

  padding: 18px;

  border: 1px solid #e2e8f0;
  border-radius: 14px;

  background: white;

  box-shadow:
    0 3px 12px rgba(15, 23, 42, 0.04);
}

.filter-top {
  display: flex;
  gap: 12px;
}

.search-box {
  position: relative;

  flex: 1;
}

.search-box input {
  width: 100%;
  height: 42px;

  padding:
    0 42px;

  border: 1px solid #cbd5e1;
  border-radius: 10px;

  outline: none;

  font-size: 13px;

  box-sizing: border-box;
}

.search-box input:focus {
  border-color: #2563eb;

  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-icon {
  position: absolute;

  left: 14px;
  top: 50%;

  transform: translateY(-50%);

  font-size: 15px;
}

.clear-search {
  position: absolute;

  right: 11px;
  top: 50%;

  transform: translateY(-50%);

  border: none;

  background: transparent;

  color: #64748b;

  cursor: pointer;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 7px;

  height: 42px;

  padding: 0 15px;

  border: 1px solid #cbd5e1;
  border-radius: 10px;

  background: white;

  color: #334155;

  font-weight: 600;

  cursor: pointer;
}

.refresh-btn:hover {
  background: #f8fafc;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinning {
  display: inline-block;

  animation: spin 0.8s linear infinite;
}

.filter-bottom {
  display: grid;

  grid-template-columns:
    1fr 1fr 1fr auto;

  gap: 12px;

  margin-top: 14px;
}

.filter-field label {
  display: block;

  margin-bottom: 6px;

  color: #475569;

  font-size: 12px;
  font-weight: 600;
}

.filter-field select,
.filter-field input {
  width: 100%;
  height: 40px;

  padding: 0 11px;

  border: 1px solid #cbd5e1;
  border-radius: 9px;

  background: white;

  outline: none;

  font-size: 13px;

  box-sizing: border-box;
}

.filter-field select:focus,
.filter-field input:focus {
  border-color: #2563eb;
}

.reset-btn {
  align-self: end;

  height: 40px;

  padding: 0 15px;

  border: none;
  border-radius: 9px;

  background: #f1f5f9;

  color: #475569;

  font-weight: 600;

  cursor: pointer;
}

.reset-btn:hover {
  background: #e2e8f0;
}


/* =========================================================
   TABLE
========================================================= */

.table-card {
  overflow: hidden;

  border: 1px solid #e2e8f0;
  border-radius: 14px;

  background: white;

  box-shadow:
    0 3px 12px rgba(15, 23, 42, 0.04);
}

.table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 20px;

  border-bottom: 1px solid #e2e8f0;
}

.table-header h2 {
  margin: 0 0 4px;

  color: #0f172a;

  font-size: 16px;
}

.table-header p {
  margin: 0;

  color: #64748b;

  font-size: 12px;
}

.result-count {
  padding: 6px 10px;

  border-radius: 20px;

  background: #eff6ff;

  color: #1d4ed8;

  font-size: 12px;
  font-weight: 700;
}

.table-wrapper {
  width: 100%;

  overflow-x: auto;
}

table {
  width: 100%;

  border-collapse: collapse;

  min-width: 950px;
}

thead {
  background: #f8fafc;
}

th {
  padding: 13px 16px;

  border-bottom: 1px solid #e2e8f0;

  color: #64748b;

  font-size: 11px;
  font-weight: 800;

  text-align: left;

  text-transform: uppercase;
  letter-spacing: 0.04em;
}

td {
  padding: 15px 16px;

  border-bottom: 1px solid #f1f5f9;

  color: #334155;

  font-size: 13px;

  vertical-align: middle;
}

tbody tr {
  transition: background 0.15s ease;
}

tbody tr:hover {
  background: #f8fafc;
}

.number-cell {
  display: inline-flex;
  align-items: center;
  justify-content: center;

  min-width: 30px;
  height: 30px;

  border-radius: 8px;

  background: #f1f5f9;

  color: #475569;

  font-weight: 700;
}

.reference-cell strong {
  color: #1d4ed8;

  font-size: 13px;
}

.date-cell {
  white-space: nowrap;

  color: #475569;
}

.origin-cell {
  max-width: 170px;

  overflow: hidden;

  text-overflow: ellipsis;

  white-space: nowrap;
}

.object-cell {
  max-width: 260px;

  overflow: hidden;

  text-overflow: ellipsis;

  white-space: nowrap;
}


/* =========================================================
   PRIORITÉ
========================================================= */

.priority-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;

  padding: 6px 9px;

  border-radius: 20px;

  font-size: 11px;
  font-weight: 800;

  white-space: nowrap;
}

.priority-dot {
  width: 6px;
  height: 6px;

  border-radius: 50%;

  background: currentColor;
}

.priority-normal {
  background: #f0fdf4;
  color: #15803d;
}

.priority-urgent {
  background: #fff7ed;
  color: #c2410c;
}

.priority-tres-urgent {
  background: #fef2f2;
  color: #b91c1c;
}


/* =========================================================
   ACTIONS
========================================================= */

.actions-column {
  text-align: center;
}

.actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.action-btn {
  width: 33px;
  height: 33px;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 1px solid #e2e8f0;
  border-radius: 8px;

  background: white;

  cursor: pointer;

  transition: all 0.15s ease;
}

.action-btn:hover {
  transform: translateY(-1px);
}

.action-btn.view:hover {
  background: #eff6ff;
  border-color: #bfdbfe;
}

.action-btn.edit:hover {
  background: #fefce8;
  border-color: #fde68a;
}

.action-btn.delete:hover {
  background: #fef2f2;
  border-color: #fecaca;
}


/* =========================================================
   LOADING
========================================================= */

.loading-state {
  min-height: 300px;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  color: #64748b;
}

.loader {
  width: 36px;
  height: 36px;

  margin-bottom: 12px;

  border: 3px solid #dbeafe;
  border-top-color: #2563eb;

  border-radius: 50%;

  animation: spin 0.8s linear infinite;
}

.loading-state p {
  margin: 0;

  font-size: 13px;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-state {
  min-height: 300px;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  padding: 30px;

  text-align: center;
}

.empty-icon {
  width: 65px;
  height: 65px;

  display: flex;
  align-items: center;
  justify-content: center;

  margin-bottom: 12px;

  border-radius: 18px;

  background: #f1f5f9;

  font-size: 28px;
}

.empty-state h3 {
  margin: 0 0 6px;

  color: #0f172a;

  font-size: 16px;
}

.empty-state p {
  margin: 0 0 17px;

  color: #64748b;

  font-size: 13px;
}


/* =========================================================
   PAGINATION
========================================================= */

.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 15px 20px;

  border-top: 1px solid #e2e8f0;
}

.pagination > button {
  min-height: 36px;

  padding: 0 12px;

  border: 1px solid #cbd5e1;
  border-radius: 8px;

  background: white;

  color: #475569;

  font-size: 12px;
  font-weight: 600;

  cursor: pointer;
}

.pagination > button:hover:not(:disabled) {
  background: #f8fafc;
}

.pagination button:disabled {
  opacity: 0.45;

  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 5px;
}

.page-numbers button {
  width: 34px;
  height: 34px;

  border: 1px solid #e2e8f0;
  border-radius: 8px;

  background: white;

  color: #475569;

  cursor: pointer;
}

.page-numbers button.active {
  border-color: #2563eb;

  background: #2563eb;

  color: white;
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

  background: rgba(15, 23, 42, 0.58);

  backdrop-filter: blur(5px);
}

.modal {
  width: min(760px, 100%);
  max-height: 90vh;

  display: flex;
  flex-direction: column;

  overflow: hidden;

  border-radius: 18px;

  background: white;

  box-shadow:
    0 25px 70px rgba(15, 23, 42, 0.25);
}

.form-modal {
  width: min(820px, 100%);
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;

  padding: 21px 24px;

  border-bottom: 1px solid #e2e8f0;
}

.modal-kicker {
  display: block;

  margin-bottom: 4px;

  color: #64748b;

  font-size: 10px;
  font-weight: 800;

  letter-spacing: 0.08em;
}

.modal-header h2 {
  margin: 0;

  color: #0f172a;

  font-size: 19px;
}

.modal-close {
  width: 35px;
  height: 35px;

  border: none;
  border-radius: 9px;

  background: #f1f5f9;

  color: #475569;

  cursor: pointer;
}

.modal-close:hover {
  background: #e2e8f0;
}

.modal-body {
  overflow-y: auto;

  padding: 24px;
}

.detail-reference {
  display: flex;
  align-items: center;
  gap: 13px;

  margin-bottom: 20px;
  padding: 15px;

  border: 1px solid #dbeafe;
  border-radius: 12px;

  background: #eff6ff;
}

.detail-reference-icon {
  width: 44px;
  height: 44px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 11px;

  background: white;

  font-size: 20px;
}

.detail-reference span {
  display: block;

  margin-bottom: 3px;

  color: #64748b;

  font-size: 11px;
}

.detail-reference strong {
  color: #1e40af;

  font-size: 16px;
}

.detail-grid {
  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 12px;
}

.detail-item {
  padding: 14px;

  border: 1px solid #e2e8f0;
  border-radius: 11px;

  background: #f8fafc;
}

.detail-item.full {
  grid-column: 1 / -1;
}

.detail-item > span:first-child {
  display: block;

  margin-bottom: 7px;

  color: #64748b;

  font-size: 11px;
  font-weight: 600;
}

.detail-item strong {
  color: #0f172a;

  font-size: 13px;
}

.detail-item p {
  margin: 0;

  color: #334155;

  font-size: 13px;

  line-height: 1.6;
}

.modal-footer,
.form-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 9px;

  padding: 16px 24px;

  border-top: 1px solid #e2e8f0;

  background: #f8fafc;
}


/* =========================================================
   FORMULAIRE
========================================================= */

.form-body {
  overflow-y: auto;

  padding: 24px;
}

.form-error {
  margin-bottom: 16px;
  padding: 11px 13px;

  border: 1px solid #fecaca;
  border-radius: 9px;

  background: #fef2f2;

  color: #b91c1c;

  font-size: 12px;
  font-weight: 600;
}

.form-grid {
  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 16px;
}

.form-group.full {
  grid-column: 1 / -1;
}

.form-group label {
  display: block;

  margin-bottom: 6px;

  color: #334155;

  font-size: 12px;
  font-weight: 700;
}

.form-group label span {
  color: #dc2626;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;

  box-sizing: border-box;

  border: 1px solid #cbd5e1;
  border-radius: 9px;

  background: white;

  color: #0f172a;

  outline: none;

  font-family: inherit;

  font-size: 13px;
}

.form-group input,
.form-group select {
  height: 42px;

  padding: 0 11px;
}

.form-group textarea {
  padding: 11px;

  resize: vertical;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #2563eb;

  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-group input:disabled,
.form-group select:disabled,
.form-group textarea:disabled {
  background: #f1f5f9;

  cursor: not-allowed;
}


/* =========================================================
   DELETE MODAL
========================================================= */

.delete-modal {
  width: min(430px, 100%);

  padding: 28px;

  border-radius: 18px;

  background: white;

  text-align: center;

  box-shadow:
    0 25px 70px rgba(15, 23, 42, 0.25);
}

.delete-icon {
  width: 58px;
  height: 58px;

  display: flex;
  align-items: center;
  justify-content: center;

  margin: 0 auto 15px;

  border-radius: 16px;

  background: #fef2f2;

  font-size: 25px;
}

.delete-modal h2 {
  margin: 0 0 8px;

  color: #0f172a;

  font-size: 19px;
}

.delete-modal p {
  margin: 0 auto 8px;

  max-width: 330px;

  color: #64748b;

  font-size: 13px;

  line-height: 1.6;
}

.delete-modal p strong {
  color: #0f172a;
}

.warning-text {
  display: block;

  margin-bottom: 22px;

  color: #dc2626;

  font-size: 11px;
  font-weight: 600;
}

.delete-actions {
  display: flex;
  justify-content: center;
  gap: 9px;
}

.btn-danger {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;

  min-height: 42px;

  padding: 0 16px;

  border: none;
  border-radius: 10px;

  background: #dc2626;

  color: white;

  font-size: 13px;
  font-weight: 700;

  cursor: pointer;
}

.btn-danger:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}


/* =========================================================
   LOADERS
========================================================= */

.small-loader {
  width: 14px;
  height: 14px;

  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: white;

  border-radius: 50%;

  animation: spin 0.7s linear infinite;
}


/* =========================================================
   TRANSITIONS
========================================================= */

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.alert-enter-active,
.alert-leave-active {
  transition: all 0.2s ease;
}

.alert-enter-from,
.alert-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}


/* =========================================================
   ANIMATION
========================================================= */

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

  .stats-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .filter-bottom {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

}


@media (max-width: 768px) {

  .courriers-page {
    padding: 18px;
  }

  .page-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .page-header .btn-primary {
    width: 100%;
  }

  .filter-top {
    flex-direction: column;
  }

  .refresh-btn {
    justify-content: center;
  }

  .filter-bottom {
    grid-template-columns: 1fr;
  }

  .reset-btn {
    width: 100%;
  }

  .detail-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .detail-item.full,
  .form-group.full {
    grid-column: auto;
  }

  .pagination {
    gap: 10px;
    flex-wrap: wrap;
  }

}


@media (max-width: 520px) {

  .courriers-page {
    padding: 12px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .page-header h1 {
    font-size: 23px;
  }

  .header-icon {
    width: 48px;
    height: 48px;
  }

  .table-header {
    align-items: flex-start;
    flex-direction: column;
    gap: 10px;
  }

  .page-numbers {
    display: none;
  }

  .modal-overlay {
    padding: 10px;
  }

  .modal-header,
  .modal-body,
  .form-body {
    padding: 18px;
  }

  .modal-footer,
  .form-footer {
    padding: 14px 18px;
  }

}
</style>