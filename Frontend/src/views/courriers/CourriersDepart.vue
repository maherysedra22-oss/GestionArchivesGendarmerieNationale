```vue
<template>
  <div class="courriers-page">

    <!-- =========================================================
         HEADER
    ========================================================== -->
    <header class="page-header">
      <div class="header-left">
        <div class="header-icon">
          <Send :size="26" />
        </div>

        <div>
          <h1>Courriers départ</h1>
          <p>Gestion des courriers administratifs sortants</p>
        </div>
      </div>

      <button
        v-if="canCreateCourriersDepart"
        class="btn btn-primary"
        @click="ouvrirFormulaireCreation"
      >
        <Plus :size="18" />
        <span>Nouveau courrier</span>
      </button>
    </header>


    <!-- =========================================================
         ERROR GLOBAL
    ========================================================== -->
    <div v-if="errorMessage" class="alert alert-error">
      <AlertCircle :size="20" />
      <div>
        <strong>Erreur</strong>
        <p>{{ errorMessage }}</p>
      </div>

      <button class="alert-close" @click="errorMessage = ''">
        <X :size="18" />
      </button>
    </div>


    <!-- =========================================================
         STATISTICS
    ========================================================== -->
    <section class="stats-grid">

      <div class="stat-card">
        <div class="stat-icon stat-blue">
          <Send :size="22" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Total courriers</span>
          <strong>{{ statistiques.total }}</strong>
        </div>
      </div>


      <div class="stat-card">
        <div class="stat-icon stat-purple">
          <MapPin :size="22" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Destinations</span>
          <strong>{{ statistiques.destinations }}</strong>
        </div>
      </div>


      <div class="stat-card">
        <div class="stat-icon stat-green">
          <FileText :size="22" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Documents</span>
          <strong>{{ statistiques.documents }}</strong>
        </div>
      </div>


      <div class="stat-card">
        <div class="stat-icon stat-red">
          <AlertTriangle :size="22" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Très urgents</span>
          <strong>{{ statistiques.tresUrgents }}</strong>
        </div>
      </div>

    </section>


    <!-- =========================================================
         SEARCH + FILTERS
    ========================================================== -->
    <section class="filter-panel">

      <div class="search-row">

        <div class="search-box">
          <Search :size="19" />

          <input
            v-model="filtres.search"
            type="text"
            placeholder="Rechercher un courrier..."
            @keyup.enter="appliquerFiltres"
          />

          <button
            v-if="filtres.search"
            class="search-clear"
            type="button"
            @click="filtres.search = ''"
          >
            <X :size="16" />
          </button>
        </div>

        <button
          class="btn btn-primary filter-submit"
          type="button"
          @click="appliquerFiltres"
        >
          <Search :size="17" />
          Rechercher
        </button>

      </div>


      <div class="filters-grid">

        <!-- NATURE -->
        <div class="field">
          <label>Nature</label>

          <div class="select-with-action">
            <select v-model="filtres.num_nat">
              <option value="">Toutes les natures</option>

              <option
                v-for="nature in natures"
                :key="getNatureId(nature)"
                :value="getNatureId(nature)"
              >
                {{ getNatureLabel(nature) }}
              </option>
            </select>

            <button
              type="button"
              class="mini-action"
              title="Gérer les natures"
              @click="ouvrirGestionReference('nature')"
            >
              <Settings2 :size="16" />
            </button>
          </div>
        </div>


        <!-- CLASSEMENT -->
        <div class="field">
          <label>Classement</label>

          <div class="select-with-action">
            <select v-model="filtres.id_class">
              <option value="">Tous les classements</option>

              <option
                v-for="classement in classementsDepart"
                :key="getClassementId(classement)"
                :value="getClassementId(classement)"
              >
                {{ getClassementLabel(classement) }}
              </option>
            </select>

            <button
              type="button"
              class="mini-action"
              title="Gérer les classements"
              @click="ouvrirGestionReference('classement')"
            >
              <Settings2 :size="16" />
            </button>
          </div>
        </div>


        <!-- PRIORITE -->
        <div class="field">
          <label>Priorité</label>

          <select v-model="filtres.priorite">
            <option value="">Toutes les priorités</option>
            <option value="NORMAL">Normal</option>
            <option value="URGENT">Urgent</option>
            <option value="TRES_URGENT">Très urgent</option>
          </select>
        </div>


        <!-- DATE DEBUT -->
        <div class="field">
          <label>Date début</label>

          <input
            v-model="filtres.date_debut"
            type="date"
          />
        </div>


        <!-- DATE FIN -->
        <div class="field">
          <label>Date fin</label>

          <input
            v-model="filtres.date_fin"
            type="date"
          />
        </div>


        <!-- PAGE SIZE -->
        <div class="field">
          <label>Afficher</label>

          <select
            v-model.number="pagination.perPage"
            @change="changerNombreParPage"
          >
            <option :value="15">15 / page</option>
            <option :value="25">25 / page</option>
            <option :value="50">50 / page</option>
          </select>
        </div>

      </div>


      <div class="filter-actions">

        <button
          type="button"
          class="btn btn-light"
          @click="reinitialiserFiltres"
        >
          <RotateCcw :size="16" />
          Réinitialiser
        </button>

        <span class="result-count">
          {{ pagination.total }} courrier{{ pagination.total > 1 ? 's' : '' }}
        </span>

      </div>

    </section>


    <!-- =========================================================
         LOADING
    ========================================================== -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Chargement des courriers...</p>
    </div>


    <!-- =========================================================
         EMPTY
    ========================================================== -->
    <div
      v-else-if="!courriers.length"
      class="empty-state"
    >
      <div class="empty-icon">
        <Send :size="34" />
      </div>

      <h3>Aucun courrier départ trouvé</h3>
      <p>
        Aucun courrier ne correspond aux critères de recherche.
      </p>

      <button
        v-if="canCreateCourriersDepart"
        class="btn btn-primary"
        @click="ouvrirFormulaireCreation"
      >
        <Plus :size="17" />
        Nouveau courrier
      </button>
    </div>


    <!-- =========================================================
         DESKTOP TABLE
    ========================================================== -->
    <section
      v-else
      class="table-card desktop-table"
    >

      <div class="table-header">
        <div>
          <h2>Liste des courriers départ</h2>
          <span>
            {{ pagination.total }} résultat{{ pagination.total > 1 ? 's' : '' }}
          </span>
        </div>
      </div>


      <div class="table-wrapper">

        <table>
          <thead>
            <tr>
              <th>N°</th>
              <th>Date</th>
              <th>Nature</th>
              <th>Objet</th>
              <th>Classement</th>
              <th>Destinations</th>
              <th>Priorité</th>
              <th>Documents</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="courrier in courriers"
              :key="courrier.num_ordre_dep"
            >

              <td>
                <span class="number-badge">
                  {{ formatNumeroCourrier(courrier.num_ordre_dep) }}
                </span>
              </td>


              <td>
                <span class="date-value">
                  {{ formatDate(courrier.date_dep) }}
                </span>
              </td>


              <td>
                <span class="nature-value">
                  {{ getNatureFromCourrier(courrier) }}
                </span>
              </td>


              <td>
                <div class="objet-cell">
                  <strong>{{ courrier.objet_courr_dep || 'Sans objet' }}</strong>
                </div>
              </td>


              <td>
                <span class="classification-badge">
                  {{ getClassementFromCourrier(courrier) }}
                </span>
              </td>


              <td>
                <div class="destination-list">

                  <span
                    v-for="destination in getDestinations(courrier).slice(0, 2)"
                    :key="getDestinationId(destination)"
                    class="destination-badge"
                  >
                    <MapPin :size="12" />
                    {{ getDestinationLabel(destination) }}
                  </span>

                  <span
                    v-if="getDestinations(courrier).length > 2"
                    class="more-badge"
                  >
                    +{{ getDestinations(courrier).length - 2 }}
                  </span>

                  <span
                    v-if="!getDestinations(courrier).length"
                    class="muted"
                  >
                    —
                  </span>

                </div>
              </td>


              <td>
                <span
                  class="priority-badge"
                  :class="priorityClass(courrier.priorite)"
                >
                  <span class="priority-dot"></span>
                  {{ formatPriority(courrier.priorite) }}
                </span>
              </td>


              <td>
                <button
                  v-if="canViewCourriersDepart"
                  class="document-count"
                  type="button"
                  title="Voir les documents"
                  @click="voirCourrier(courrier)"
                >
                  <Paperclip :size="15" />
                  {{ getDocumentCount(courrier) }}
                </button>
              </td>


              <td>
                <div class="row-actions">

                  <button
                    v-if="canViewCourriersDepart"
                    class="icon-btn view"
                    title="Voir"
                    @click="voirCourrier(courrier)"
                  >
                    <Eye :size="17" />
                  </button>

                  <button
                    v-if="canUpdateCourriersDepart"
                    class="icon-btn edit"
                    title="Modifier"
                    @click="ouvrirFormulaireModification(courrier)"
                  >
                    <Pencil :size="17" />
                  </button>

                  <button
                    v-if="canDeleteCourriersDepart"
                    class="icon-btn delete"
                    title="Supprimer"
                    @click="confirmerSuppression(courrier)"
                  >
                    <Trash2 :size="17" />
                  </button>

                </div>
              </td>

            </tr>

          </tbody>
        </table>

      </div>
    </section>


    <!-- =========================================================
         MOBILE CARDS
    ========================================================== -->
    <section
      v-if="courriers.length"
      class="mobile-cards"
    >

      <article
        v-for="courrier in courriers"
        :key="courrier.num_ordre_dep"
        class="courrier-card"
      >

        <div class="card-top">

          <div>
            <span class="card-number">
              {{ formatNumeroCourrier(courrier.num_ordre_dep) }}
            </span>

            <span class="card-date">
              {{ formatDate(courrier.date_dep) }}
            </span>
          </div>

          <span
            class="priority-badge"
            :class="priorityClass(courrier.priorite)"
          >
            <span class="priority-dot"></span>
            {{ formatPriority(courrier.priorite) }}
          </span>

        </div>


        <div class="card-body">

          <div class="card-info">
            <span class="info-label">Nature</span>
            <strong>{{ getNatureFromCourrier(courrier) }}</strong>
          </div>

          <div class="card-info">
            <span class="info-label">Objet</span>
            <strong>{{ courrier.objet_courr_dep || 'Sans objet' }}</strong>
          </div>

          <div class="card-info">
            <span class="info-label">Classement</span>
            <strong>{{ getClassementFromCourrier(courrier) }}</strong>
          </div>

          <div class="card-info">
            <span class="info-label">Destinations</span>

            <div class="destination-list">
              <span
                v-for="destination in getDestinations(courrier).slice(0, 3)"
                :key="getDestinationId(destination)"
                class="destination-badge"
              >
                {{ getDestinationLabel(destination) }}
              </span>
            </div>
          </div>

          <div class="card-info">
            <span class="info-label">Documents</span>

            <span class="document-count">
              <Paperclip :size="15" />
              {{ getDocumentCount(courrier) }}
            </span>
          </div>

        </div>


        <div class="card-actions">
          <button
            v-if="canViewCourriersDepart"
            class="btn-card view"
            @click="voirCourrier(courrier)"
          >
            <Eye :size="16" />
            Voir
          </button>

          <button
            v-if="canUpdateCourriersDepart"
            class="btn-card edit"
            @click="ouvrirFormulaireModification(courrier)"
          >
            <Pencil :size="16" />
            Modifier
          </button>

          <button
            v-if="canDeleteCourriersDepart"
            class="btn-card delete"
            @click="confirmerSuppression(courrier)"
          >
            <Trash2 :size="16" />
            Supprimer
          </button>
        </div>

      </article>

    </section>


    <!-- =========================================================
         PAGINATION
    ========================================================== -->
    <section
      v-if="pagination.lastPage > 1"
      class="pagination-container"
    >

      <div class="pagination-info">
        Affichage de
        <strong>{{ paginationStart }}</strong>
        à
        <strong>{{ paginationEnd }}</strong>
        sur
        <strong>{{ pagination.total }}</strong>
        courriers
      </div>


      <div class="pagination">

        <button
          class="page-btn"
          :disabled="pagination.currentPage <= 1"
          @click="changerPage(pagination.currentPage - 1)"
        >
          <ChevronLeft :size="17" />
          Précédent
        </button>


        <button
          v-for="page in pages"
          :key="page"
          class="page-number"
          :class="{ active: page === pagination.currentPage }"
          @click="changerPage(page)"
        >
          {{ page }}
        </button>


        <button
          class="page-btn"
          :disabled="pagination.currentPage >= pagination.lastPage"
          @click="changerPage(pagination.currentPage + 1)"
        >
          Suivant
          <ChevronRight :size="17" />
        </button>

      </div>

    </section>


    <!-- =========================================================
         MODAL CREATE / EDIT
    ========================================================== -->
    <div
      v-if="showFormModal"
      class="modal-overlay"
      @click.self="fermerFormulaire"
    >

      <div class="modal modal-large">

        <div class="modal-header">

          <div>
            <h2>
              {{ modeFormulaire === 'creation'
                ? 'Nouveau courrier départ'
                : 'Modifier le courrier départ' }}
            </h2>

            <p>
              {{ modeFormulaire === 'creation'
                ? 'Enregistrer un nouveau courrier administratif sortant'
                : 'Modifier les informations du courrier' }}
            </p>
          </div>

          <button
            class="modal-close"
            type="button"
            @click="fermerFormulaire"
          >
            <X :size="20" />
          </button>

        </div>


        <form
          class="modal-body"
          @submit.prevent="enregistrerCourrier"
        >

          <!-- INFORMATIONS -->
          <div class="form-section">

            <div class="section-title">
              <div class="section-icon">
                <FileText :size="18" />
              </div>

              <div>
                <h3>Informations</h3>
                <p>Informations principales du courrier</p>
              </div>
            </div>


            <div class="form-grid">

              <!-- NUMERO D'ORDRE MANUEL -->
              <div class="field">
                <label for="num_ordre_dep">
                  Numéro d'ordre <span class="required">*</span>
                </label>

                <div class="input-with-icon">

                  <input
                    id="num_ordre_dep"
                    v-model="formulaire.num_ordre_dep"
                    type="number"
                    min="1"
                    step="1"
                    placeholder="Ex. 117"
                    required
                    :disabled="modeFormulaire === 'modification'"
                  />
                </div>

                <small v-if="modeFormulaire === 'creation'">
                  Saisissez uniquement le numéro, par exemple : 117
                </small>

                <small v-else>
                  Le numéro d'ordre ne peut pas être modifié.
                </small>
              </div>


              <!-- DATE -->
              <div class="field">
                <label for="date_dep">
                  Date <span class="required">*</span>
                </label>

                <input
                  id="date_dep"
                  v-model="formulaire.date_dep"
                  type="date"
                  required
                />
              </div>


              <!-- NATURE -->
              <div class="field">
                <label for="num_nat">
                  Nature <span class="required">*</span>
                </label>

                <div class="select-with-action">
                  <select
                    id="num_nat"
                    v-model="formulaire.num_nat"
                    required
                  >
                    <option value="">Sélectionner une nature</option>

                    <option
                      v-for="nature in natures"
                      :key="getNatureId(nature)"
                      :value="getNatureId(nature)"
                    >
                      {{ getNatureLabel(nature) }}
                    </option>
                  </select>

                  <button
                    v-if="canCreateCourriersDepart"
                    type="button"
                    class="mini-action"
                    title="Gérer les natures"
                    @click="ouvrirGestionReference('nature')"
                  >
                    <Plus :size="16" />
                  </button>
                </div>
              </div>


              <!-- CLASSEMENT -->
              <div class="field">
                <label for="id_class">
                  Classement <span class="required">*</span>
                </label>

                <div class="select-with-action">
                  <select
                    id="id_class"
                    v-model="formulaire.id_class"
                    required
                  >
                    <option value="">
                      Sélectionner un classement
                    </option>

                    <option
                      v-for="classement in classementsDepart"
                      :key="getClassementId(classement)"
                      :value="getClassementId(classement)"
                    >
                      {{ getClassementLabel(classement) }}
                    </option>
                  </select>

                  <button
                    v-if="canCreateCourriersDepart"
                    type="button"
                    class="mini-action"
                    title="Gérer les classements"
                    @click="ouvrirGestionReference('classement')"
                  >
                    <Plus :size="16" />
                  </button>
                </div>
              </div>


              <!-- PRIORITE -->
              <div class="field">
                <label for="priorite">
                  Priorité <span class="required">*</span>
                </label>

                <select
                  id="priorite"
                  v-model="formulaire.priorite"
                  required
                >
                  <option value="NORMAL">Normal</option>
                  <option value="URGENT">Urgent</option>
                  <option value="TRES_URGENT">Très urgent</option>
                </select>
              </div>


              <!-- OBJET -->
              <div class="field full-width">
                <label for="objet_courr_dep">
                  Objet <span class="required">*</span>
                </label>

                <textarea
                  id="objet_courr_dep"
                  v-model="formulaire.objet_courr_dep"
                  rows="4"
                  maxlength="1000"
                  placeholder="Saisissez l'objet du courrier..."
                  required
                ></textarea>

                <small>
                  {{ formulaire.objet_courr_dep.length }}/1000
                </small>
              </div>

            </div>

          </div>


          <!-- DESTINATIONS -->
          <div class="form-section">

            <div class="section-title">

              <div class="section-icon">
                <MapPin :size="18" />
              </div>

              <div>
                <h3>Destinations</h3>
                <p>
                  Sélectionnez une ou plusieurs destinations
                </p>
              </div>

            </div>


            <div class="destination-select-row">

              <select
                v-model="destinationSelection"
              >
                <option value="">
                  Ajouter une destination...
                </option>

                <option
                  v-for="destination in destinations"
                  :key="getDestinationId(destination)"
                  :value="getDestinationId(destination)"
                  :disabled="destinationDejaSelectionnee(destination)"
                >
                  {{ getDestinationLabel(destination) }}
                </option>
              </select>

              <button
                v-if="canCreateCourriersDepart"
                type="button"
                class="btn btn-secondary"
                @click="ajouterDestinationSelectionnee"
              >
                <Plus :size="17" />
                Ajouter
              </button>

              <button
                v-if="canCreateCourriersDepart"
                type="button"
                class="btn btn-light"
                title="Gérer les destinations"
                @click="ouvrirGestionReference('destination')"
              >
                <Settings2 :size="17" />
              </button>

            </div>


            <div
              v-if="formulaire.destinations.length"
              class="selected-destinations"
            >

              <div
                v-for="destinationId in formulaire.destinations"
                :key="destinationId"
                class="selected-destination"
              >
                <MapPin :size="14" />

                <span>
                  {{ getDestinationLabelById(destinationId) }}
                </span>

                <button
                  v-if="canCreateCourriersDepart"
                  type="button"
                  @click="retirerDestination(destinationId)"
                >
                  <X :size="14" />
                </button>
              </div>

            </div>


            <div
              v-else
              class="no-selection"
            >
              <MapPin :size="18" />
              <span>Aucune destination sélectionnée</span>
            </div>

          </div>


          <!-- DOCUMENTS -->
          <div class="form-section">

            <div class="section-title">

              <div class="section-icon">
                <Paperclip :size="18" />
              </div>

              <div>
                <h3>Documents joints</h3>
                <p>
                  PDF, JPG, JPEG, PNG, DOC, DOCX — maximum 10 Mo par fichier
                </p>
              </div>

            </div>


            <div
              v-if="canCreateCourriersDepart"
              class="dropzone"
              :class="{ dragging: isDragging }"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              @click="ouvrirInputFichier"
            >

              <input
                ref="fileInput"
                type="file"
                multiple
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                hidden
                @change="handleFileChange"
              />

              <div class="dropzone-icon">
                <UploadCloud :size="30" />
              </div>

              <strong>
                Glissez-déposez vos fichiers ici
              </strong>

              <span>
                ou cliquez pour sélectionner des fichiers
              </span>

              <small>
                Formats acceptés : PDF, JPG, JPEG, PNG, DOC, DOCX
              </small>

            </div>


            <!-- FICHIERS NOUVEAUX -->
            <div
              v-if="fichiersSelectionnes.length"
              class="file-list"
            >

              <div
                v-for="(file, index) in fichiersSelectionnes"
                :key="`${file.name}-${index}`"
                class="file-item"
              >

                <div class="file-icon">
                  <FileText :size="19" />
                </div>

                <div class="file-info">
                  <strong>{{ file.name }}</strong>
                  <span>{{ formatFileSize(file.size) }}</span>
                </div>

                <button
                  type="button"
                  class="file-remove"
                  title="Retirer"
                  @click.stop="retirerFichier(index)"
                >
                  <X :size="17" />
                </button>

              </div>

            </div>


            <!-- DOCUMENTS EXISTANTS EN MODIFICATION -->
            <div
              v-if="modeFormulaire === 'modification' && documentsCourant.length"
              class="existing-documents"
            >

              <h4>Documents existants</h4>

              <div
                v-for="document in documentsCourant"
                :key="document.num_doc"
                class="file-item"
              >

                <div class="file-icon">
                  <FileText :size="19" />
                </div>

                <div class="file-info">
                  <strong>
                    {{ document.nom_original || 'Document sans nom' }}
                  </strong>

                  <span>
                    {{ document.extension?.toUpperCase() || 'FICHIER' }}
                    <template v-if="document.taille">
                      · {{ formatFileSize(document.taille) }}
                    </template>
                  </span>
                </div>

                <div class="file-actions">

                    <button
                      v-if="canViewDocuments"
                      type="button"
                      class="file-preview"
                      title="Afficher"
                      :disabled="!document?.num_doc"
                      @click.stop="afficherDocument(document)"
                    >
                      <Eye :size="16" />
                    </button>

                    <button
                      v-if="canDownloadDocuments"
                      type="button"
                      class="file-download"
                      title="Télécharger"
                      :disabled="!document?.num_doc"
                      @click.stop="telechargerDocument(document)"
                    >
                      <Download :size="16" />
                    </button>

                    <button
                      v-if="canDeleteCourriersDepart"
                      type="button"
                      class="file-remove"
                      title="Supprimer"
                      :disabled="!document?.num_doc"
                      @click.stop="supprimerDocument(document)"
                    >
                      <Trash2 :size="16" />
                    </button>

                </div>

              </div>

            </div>


            <div
              v-if="erreursFormulaire.documents"
              class="field-error"
            >
              {{ erreursFormulaire.documents }}
            </div>

          </div>


          <!-- ERRORS VALIDATION -->
          <div
            v-if="Object.keys(erreursFormulaire).length"
            class="validation-summary"
          >
            <AlertCircle :size="19" />

            <div>
              <strong>Veuillez vérifier les informations</strong>

              <ul>
                <li
                  v-for="(messages, champ) in erreursFormulaire"
                  :key="champ"
                >
                  {{ formatErreurChamp(champ, messages) }}
                </li>
              </ul>
            </div>
          </div>


          <!-- FOOTER -->
          <div class="modal-footer">

            <button
              type="button"
              class="btn btn-light"
              :disabled="saving"
              @click="fermerFormulaire"
            >
              Annuler
            </button>

            <button
              type="submit"
              class="btn btn-primary"
              :disabled="saving"
            >

              <span
                v-if="saving"
                class="button-spinner"
              ></span>

              <Save
                v-else
                :size="17"
              />

              {{ saving
                ? 'Enregistrement...'
                : modeFormulaire === 'creation'
                  ? 'Enregistrer'
                  : 'Enregistrer les modifications' }}

            </button>

          </div>

        </form>

      </div>

    </div>


    <!-- =========================================================
         MODAL DETAILS
    ========================================================== -->
    <div
      v-if="showDetailsModal"
      class="modal-overlay"
      @click.self="fermerDetails"
    >

      <div class="modal modal-details">

        <div class="modal-header">

          <div>
            <div class="details-number">
              {{ formatNumeroCourrier(courrierSelectionne?.num_ordre_dep) }}
            </div>

            <h2>Détails du courrier</h2>

            <p>
              Informations complètes du courrier départ
            </p>
          </div>

          <button
            class="modal-close"
            @click="fermerDetails"
          >
            <X :size="20" />
          </button>

        </div>


        <div class="modal-body">

          <div
            v-if="loadingDetails"
            class="loading-container small"
          >
            <div class="spinner"></div>
            <p>Chargement des détails...</p>
          </div>


          <template v-else>

            <div class="details-grid">

              <div class="detail-box">
                <span>Numéro d'ordre</span>
                <strong>
                  {{ formatNumeroCourrier(courrierSelectionne?.num_ordre_dep) }}
                </strong>
              </div>

              <div class="detail-box">
                <span>Date</span>
                <strong>
                  {{ formatDate(courrierSelectionne?.date_dep) }}
                </strong>
              </div>

              <div class="detail-box">
                <span>Nature</span>
                <strong>
                  {{ getNatureFromCourrier(courrierSelectionne) }}
                </strong>
              </div>

              <div class="detail-box">
                <span>Classement</span>
                <strong>
                  {{ getClassementFromCourrier(courrierSelectionne) }}
                </strong>
              </div>

              <div class="detail-box">
                <span>Priorité</span>

                <strong>
                  <span
                    class="priority-badge"
                    :class="priorityClass(courrierSelectionne?.priorite)"
                  >
                    <span class="priority-dot"></span>
                    {{ formatPriority(courrierSelectionne?.priorite) }}
                  </span>
                </strong>
              </div>

              <div class="detail-box full">
                <span>Objet</span>
                <strong>
                  {{ courrierSelectionne?.objet_courr_dep || '—' }}
                </strong>
              </div>

            </div>


            <!-- DESTINATIONS DETAILS -->
            <div class="details-section">

              <div class="details-section-title">
                <MapPin :size="18" />
                <h3>Destinations</h3>
              </div>

              <div
                v-if="getDestinations(courrierSelectionne).length"
                class="detail-destinations"
              >
                <span
                  v-for="destination in getDestinations(courrierSelectionne)"
                  :key="getDestinationId(destination)"
                  class="selected-destination"
                >
                  <MapPin :size="14" />
                  {{ getDestinationLabel(destination) }}
                </span>
              </div>

              <div
                v-else
                class="no-selection"
              >
                Aucune destination
              </div>

            </div>


            <!-- DOCUMENTS DETAILS -->
            <div class="details-section">

              <div class="details-section-title">
                <Paperclip :size="18" />
                <h3>
                  Documents
                  <span class="count-pill">
                    {{ documentsCourant.length }}
                  </span>
                </h3>
              </div>


              <div
                v-if="loadingDocuments"
                class="document-loading"
              >
                <div class="spinner small-spinner"></div>
                Chargement des documents...
              </div>


              <div
                v-else-if="!documentsCourant.length"
                class="no-documents"
              >
                <FileText :size="28" />
                <span>Aucun document joint</span>
              </div>


              <div
                v-else
                class="file-list"
              >

                <div
                  v-for="document in documentsCourant"
                  :key="document.num_doc"
                  class="file-item"
                >

                  <div class="file-icon">
                    <FileText :size="19" />
                  </div>

                  <div class="file-info">

                    <strong>
                      {{ document.nom_original || 'Document' }}
                    </strong>

                    <span>
                      {{ document.extension?.toUpperCase() || 'FICHIER' }}

                      <template v-if="document.taille">
                        · {{ formatFileSize(document.taille) }}
                      </template>
                    </span>

                  </div>

                  <div class="file-actions">

                      <button
                        v-if="canViewDocuments"
                        type="button"
                        class="file-preview"
                        title="Afficher"
                        :disabled="!document?.num_doc"
                        @click.stop="afficherDocument(document)"
                      >
                        <Eye :size="16" />
                      </button>

                      <button
                        v-if="canDownloadDocuments"
                        type="button"
                        class="file-download"
                        title="Télécharger"
                        :disabled="!document?.num_doc"
                        @click.stop="telechargerDocument(document)"
                      >
                        <Download :size="16" />
                      </button>

                      <button
                        v-if="canDeleteCourriersDepart"
                        type="button"
                        class="file-remove"
                        title="Supprimer"
                        :disabled="!document?.num_doc"
                        @click.stop="supprimerDocument(document)"
                      >
                        <Trash2 :size="16" />
                      </button>

                  </div>

                </div>

              </div>

            </div>

          </template>

        </div>


        <div class="modal-footer">

          <button
            class="btn btn-light"
            @click="fermerDetails"
          >
            Fermer
          </button>

          <button
            v-if="canUpdateCourriersDepart"
            class="btn btn-primary"
            @click="modifierDepuisDetails"
          >
            <Pencil :size="17" />
            Modifier
          </button>

        </div>

      </div>

    </div>


    <!-- =========================================================
         MODAL SUPPRESSION
    ========================================================== -->
    <div
      v-if="showDeleteModal"
      class="modal-overlay"
      @click.self="fermerSuppression"
    >

      <div class="modal modal-confirm">

        <div class="confirm-icon">
          <Trash2 :size="27" />
        </div>

        <h2>Supprimer le courrier ?</h2>

        <p>
          Vous êtes sur le point de supprimer définitivement le courrier
          <strong>
            {{ formatNumeroCourrier(courrierASupprimer?.num_ordre_dep) }}
          </strong>.
        </p>

        <div class="confirm-object">
          {{ courrierASupprimer?.objet_courr_dep || 'Sans objet' }}
        </div>

        <div class="warning-box">
          <AlertTriangle :size="18" />

          <span>
            Cette action est irréversible. Les documents associés
            pourront également être concernés par cette suppression.
          </span>
        </div>


        <div class="confirm-actions">

          <button
            class="btn btn-light"
            :disabled="deleting"
            @click="fermerSuppression"
          >
            Annuler
          </button>

          <button
            class="btn btn-danger"
            :disabled="deleting"
            @click="supprimerCourrier"
          >

            <span
              v-if="deleting"
              class="button-spinner"
            ></span>

            <Trash2
              v-else
              :size="17"
            />

            {{ deleting ? 'Suppression...' : 'Supprimer' }}

          </button>

        </div>

      </div>

    </div>

    <!-- =========================================================
     MODAL CONFIRMATION RÉFÉRENCE
     ========================================================== -->

    <Teleport to="body">
      <div
        v-if="showReferenceDeleteModal"
        class="modal-overlay confirmation-overlay"
        @click.self="fermerSuppressionReference"
      >
        <div class="modal modal-confirm confirmation-modal">

          <div class="confirm-icon">
            <Trash2 :size="27" />
          </div>

          <h2>Supprimer cet élément ?</h2>

          <p>
            Voulez-vous vraiment supprimer
            <strong>
              {{ getReferenceLabel(referenceASupprimer) }}
            </strong>
            ?
          </p>

          <div class="warning-box">
            <AlertTriangle :size="18" />
            <span>
              Cette action est irréversible.
            </span>
          </div>

          <div class="confirm-actions">

            <button
              type="button"
              class="btn btn-light"
              :disabled="referenceDeleting"
              @click="fermerSuppressionReference"
            >
              Annuler
            </button>

            <button
              type="button"
              class="btn btn-danger"
              :disabled="referenceDeleting"
              @click="supprimerReference"
            >
              <span
                v-if="referenceDeleting"
                class="button-spinner"
              ></span>

              <Trash2
                v-else
                :size="17"
              />

              {{ referenceDeleting ? 'Suppression...' : 'Supprimer' }}
            </button>

          </div>

        </div>
      </div>
    </Teleport>
    <!-- MODAL CONFIRMATION DOCUMENT -->
    <div v-if="showDocumentDeleteModal" class="modal-overlay" @click.self="fermerSuppressionDocument">
      <div class="modal modal-confirm"><div class="confirm-icon"><Trash2 :size="27" /></div><h2>Supprimer le document ?</h2><p>Voulez-vous supprimer <strong>{{ documentASupprimer?.nom_original || 'ce document' }}</strong> ?</p><div class="warning-box"><AlertTriangle :size="18" /><span>Cette action est irréversible.</span></div><div class="confirm-actions"><button class="btn btn-light" @click="fermerSuppressionDocument">Annuler</button><button class="btn btn-danger" @click="confirmerSuppressionDocument"><Trash2 :size="17" /> Supprimer</button></div></div>
    </div>

    <!-- MODAL APERÇU DOCUMENT -->
    <div v-if="showDocumentPreviewModal" class="modal-overlay" @click.self="fermerApercuDocument">
      <div class="modal modal-preview-document">
        <div class="modal-header"><div><h2>{{ documentPreview?.nom_original || 'Aperçu du document' }}</h2><p>{{ documentPreview?.extension?.toUpperCase() || 'DOCUMENT' }}</p></div><button class="modal-close" @click="fermerApercuDocument"><X :size="20" /></button></div>
        <div class="document-preview-body">
          <div v-if="previewLoading" class="preview-loading"><div class="spinner"></div><span>Chargement de l’aperçu...</span></div>
          <template v-else-if="previewUrl">
            <iframe v-if="isPreviewPdf" :src="previewUrl" class="document-frame"></iframe>
            <img v-else-if="isPreviewImage" :src="previewUrl" class="document-image-preview" />
            <div v-else class="preview-unavailable"><FileText :size="52" /><h3>Aperçu non disponible</h3><p>Ce type de fichier ne peut pas être affiché directement.</p></div>
          </template>
          <div v-else class="preview-unavailable"><FileText :size="52" /><h3>Aperçu non disponible</h3><p>Utilisez le bouton Télécharger pour ouvrir ce document.</p></div>
        </div>
        <div class="modal-footer preview-footer"><button class="btn btn-light" @click="fermerApercuDocument">Fermer</button><button v-if="canDownloadDocuments" class="btn btn-primary" @click="telechargerDocument(documentPreview)"><Download :size="17" /> Télécharger</button><button v-if="canDeleteCourriersDepart" class="btn btn-danger" @click="supprimerDocumentDepuisApercu"><Trash2 :size="17" /> Supprimer</button></div>
      </div>
    </div>

    <!-- =========================================================
         MODAL GESTION REFERENCES
    ========================================================== -->
    <div
      v-if="showReferenceModal"
      class="modal-overlay reference-overlay"
      @click.self="fermerGestionReference"
    >

      <div class="modal modal-reference">

        <div class="modal-header">

          <div>
            <h2>{{ referenceTitle }}</h2>
            <p>Gérer les éléments disponibles</p>
          </div>

          <button
            class="modal-close"
            @click="fermerGestionReference"
          >
            <X :size="20" />
          </button>

        </div>


        <div class="reference-body">

          <!-- AJOUT -->
          <form
            class="reference-add"
            @submit.prevent="ajouterReference"
          >

            <input
              v-model="nouvelleReference"
              type="text"
              :placeholder="referencePlaceholder"
              maxlength="255"
              required
            />

            <button
              v-if="canCreateCourriersDepart"
              type="submit"
              class="btn btn-primary"
              :disabled="referenceSaving"
            >
              <Plus :size="17" />
              Ajouter
            </button>

          </form>


          <!-- LISTE -->
          <div
            v-if="referenceLoading"
            class="reference-loading"
          >
            <div class="spinner"></div>
            <span>Chargement...</span>
          </div>


          <div
            v-else-if="referenceItems.length === 0"
            class="reference-empty"
          >
            Aucun élément disponible.
          </div>


          <div
            v-else
            class="reference-list"
          >

            <div
              v-for="item in referenceItems"
              :key="getReferenceId(item)"
              class="reference-item"
            >

              <div class="reference-item-info">

                <div class="reference-item-icon">
                  <component
                    :is="
                      referenceType === 'nature'
                        ? FileText
                        : referenceType === 'classement'
                          ? Folder
                          : MapPin
                    "
                    :size="17"
                  />
                </div>

                <span>
                  {{ getReferenceLabel(item) }}
                </span>

              </div>


              <button
                v-if="canDeleteCourriersDepart"
                type="button"
                class="reference-delete"
                title="Supprimer"
                @click="confirmerSuppressionReference(item)"
              >
                <Trash2 :size="16" />
              </button>

            </div>

          </div>

        </div>


        <div class="modal-footer">

          <button
            class="btn btn-light"
            @click="fermerGestionReference"
          >
            Fermer
          </button>

        </div>

      </div>

    </div>


    <!-- =========================================================
         TOAST
    ========================================================== -->
    <Transition name="toast">

      <div
        v-if="toast.visible"
        class="toast"
        :class="`toast-${toast.type}`"
      >

        <CheckCircle
          v-if="toast.type === 'success'"
          :size="19"
        />

        <AlertCircle
          v-else
          :size="19"
        />

        <span>{{ toast.message }}</span>

        <button @click="toast.visible = false">
          <X :size="16" />
        </button>

      </div>

    </Transition>

  </div>
</template>


<script setup>
import {
  ref,
  reactive,
  computed,
  onMounted
} from 'vue'

import { useAuthStore } from '@/stores/auth'

import {
  Send,
  Plus,
  Search,
  X,
  AlertCircle,
  MapPin,
  FileText,
  AlertTriangle,
  Settings2,
  RotateCcw,
  Eye,
  Pencil,
  Trash2,
  Paperclip,
  ChevronLeft,
  ChevronRight,
  Hash,
  UploadCloud,
  Download,
  Save,
  CheckCircle,
  Folder
} from 'lucide-vue-next'


/* ============================================================
   CONFIGURATION
============================================================ */

const API_BASE = 'http://127.0.0.1:8000/api'


/*
 * Toutes les URLs sont regroupées ici.
 * Si une route Laravel est différente,
 * il suffit de modifier cette partie.
 */
const ENDPOINTS = {

  courriers: `${API_BASE}/courriers-depart`,

  natures: `${API_BASE}/natures-courriers-depart`,

  classements: `${API_BASE}/classements`,

  destinations: `${API_BASE}/destinations`,

  documents: (numOrdreDep) =>
    `${API_BASE}/courriers-depart/${encodeURIComponent(numOrdreDep)}/documents`,

  documentDownload: (numOrdreDep, numDoc) =>
    `${API_BASE}/courriers-depart/${encodeURIComponent(numOrdreDep)}/documents/${encodeURIComponent(numDoc)}/download`,

  documentDelete: (numOrdreDep, numDoc) =>
    `${API_BASE}/courriers-depart/${encodeURIComponent(numOrdreDep)}/documents/${encodeURIComponent(numDoc)}`
}


/*
 * Limites upload
 */
const MAX_FILE_SIZE = 10 * 1024 * 1024

const ALLOWED_EXTENSIONS = [
  'pdf',
  'jpg',
  'jpeg',
  'png',
  'doc',
  'docx'
]


/* ============================================================
   AUTH
============================================================ */

function getToken() {
  return (
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token')
  )
}


function headersJSON() {

  const token = getToken()

  return {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    ...(token
      ? {
          Authorization: `Bearer ${token}`
        }
      : {})
  }
}


function headersFormData() {

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


function gererErreurAuth(response) {

  if (response.status === 401) {

    localStorage.removeItem('auth_token')
    sessionStorage.removeItem('auth_token')

    localStorage.removeItem('utilisateur')
    sessionStorage.removeItem('utilisateur')

    window.location.href = '/login'

    return true
  }

  return false
}

/* ============================================================
   AUTH STORE / PERMISSIONS
============================================================ */

const authStore = useAuthStore()

const canViewCourriersDepart = computed(() =>
  authStore.hasPermission('courriers_depart.view')
)

const canCreateCourriersDepart = computed(() =>
  authStore.hasPermission('courriers_depart.create')
)

const canUpdateCourriersDepart = computed(() =>
  authStore.hasPermission('courriers_depart.update')
)

const canDeleteCourriersDepart = computed(() =>
  authStore.hasPermission('courriers_depart.delete')
)

const canViewDocuments = computed(() =>
  authStore.hasPermission('documents.view')
)

const canDownloadDocuments = computed(() =>
  authStore.hasPermission('documents.download')
)
/* ============================================================
   STATE
============================================================ */

const courriers = ref([])

const natures = ref([])

const classements = ref([])

const destinations = ref([])

const loading = ref(false)

const loadingDetails = ref(false)

const loadingDocuments = ref(false)

const saving = ref(false)

const deleting = ref(false)

const errorMessage = ref('')

const showFormModal = ref(false)

const showDetailsModal = ref(false)

const showDeleteModal = ref(false)

const showReferenceModal = ref(false)
const showReferenceDeleteModal = ref(false)
const showDocumentPreviewModal = ref(false)
const showDocumentDeleteModal = ref(false)
const documentASupprimer = ref(null)
const referenceASupprimer = ref(null)
const referenceDeleting = ref(false)
const documentPreview = ref(null)
const previewUrl = ref('')
const previewLoading = ref(false)

const modeFormulaire = ref('creation')

const courrierSelectionne = ref(null)

const courrierASupprimer = ref(null)

const documentsCourant = ref([])

const fichiersSelectionnes = ref([])

const destinationSelection = ref('')

const isDragging = ref(false)

const fileInput = ref(null)


/* ============================================================
   FILTERS
============================================================ */

const filtres = reactive({

  search: '',

  num_nat: '',

  id_class: '',

  date_debut: '',

  date_fin: '',

  priorite: '',

  sort: 'num_ordre_dep',

  direction: 'desc'

})


/* ============================================================
   PAGINATION
============================================================ */

const pagination = reactive({

  currentPage: 1,

  lastPage: 1,

  total: 0,

  perPage: 15

})


/* ============================================================
   FORMULAIRE
============================================================ */

const formulaire = reactive({

  num_ordre_dep: '',

  date_dep: getTodayDate(),

  num_nat: '',

  objet_courr_dep: '',

  id_class: '',

  destinations: [],

  priorite: 'NORMAL'

})


const erreursFormulaire = reactive({})


/* ============================================================
   REFERENCE MANAGER
============================================================ */

const referenceType = ref('nature')

const nouvelleReference = ref('')

const referenceItems = ref([])

const referenceLoading = ref(false)

const referenceSaving = ref(false)


const referenceTitle = computed(() => {

  if (referenceType.value === 'nature') {
    return 'Gestion des natures'
  }

  if (referenceType.value === 'classement') {
    return 'Gestion des classements'
  }

  return 'Gestion des destinations'
})


const isPreviewPdf = computed(() => String(documentPreview.value?.extension || '').toLowerCase() === 'pdf')
const isPreviewImage = computed(() => ['jpg','jpeg','png'].includes(String(documentPreview.value?.extension || '').toLowerCase()))

const referencePlaceholder = computed(() => {

  if (referenceType.value === 'nature') {
    return 'Nom de la nature...'
  }

  if (referenceType.value === 'classement') {
    return 'Nom du classement...'
  }

  return 'Nom de la destination...'
})


/* ============================================================
   TOAST
============================================================ */

const toast = reactive({

  visible: false,

  type: 'success',

  message: ''

})


let toastTimer = null


function showToast(message, type = 'success') {

  toast.message = message

  toast.type = type

  toast.visible = true

  clearTimeout(toastTimer)

  toastTimer = setTimeout(() => {
    toast.visible = false
  }, 4000)
}


/* ============================================================
   STATISTICS
============================================================ */

const statistiques = computed(() => {

  const total = pagination.total

  let totalDestinations = 0

  let totalDocuments = 0

  let tresUrgents = 0

  for (const courrier of courriers.value) {

    totalDestinations += getDestinations(courrier).length

    totalDocuments += getDocumentCount(courrier)

    if (courrier.priorite === 'TRES_URGENT') {
      tresUrgents++
    }
  }

  return {

    total,

    destinations: totalDestinations,

    documents: totalDocuments,

    tresUrgents

  }
})


/* ============================================================
   PAGINATION COMPUTED
============================================================ */

const paginationStart = computed(() => {

  if (!pagination.total) {
    return 0
  }

  return (
    (pagination.currentPage - 1) *
      pagination.perPage +
    1
  )
})


const paginationEnd = computed(() => {

  return Math.min(
    pagination.currentPage * pagination.perPage,
    pagination.total
  )
})


const pages = computed(() => {

  const total = pagination.lastPage

  const current = pagination.currentPage

  const result = []

  if (total <= 7) {

    for (let i = 1; i <= total; i++) {
      result.push(i)
    }

    return result
  }

  result.push(1)

  if (current > 4) {
    result.push('...')
  }

  const start = Math.max(2, current - 1)

  const end = Math.min(total - 1, current + 1)

  for (let i = start; i <= end; i++) {

    if (!result.includes(i)) {
      result.push(i)
    }
  }

  if (current < total - 3) {
    result.push('...')
  }

  result.push(total)

  return result.filter(page => page !== '...')
})


/* ============================================================
   CLASSEMENTS DEPART
============================================================ */

const classementsDepart = computed(() => {

  return classements.value.filter(item => {

    const type = String(
      item.type_courrier ??
      item.type ??
      ''
    ).toUpperCase()

    const actif = item.actif

    return (
      type === 'DEPART' &&
      actif !== false
    )
  })
})


/* ============================================================
   HELPERS
============================================================ */

function formatNumeroCourrier(numero) {
  if (
    numero === null ||
    numero === undefined ||
    numero === ''
  ) {
    return '—'
  }

  return `NR ${numero} -COM/2-DSIT/SEMF`
}

function getTodayDate() {

  const date = new Date()

  const year = date.getFullYear()

  const month = String(
    date.getMonth() + 1
  ).padStart(2, '0')

  const day = String(
    date.getDate()
  ).padStart(2, '0')

  return `${year}-${month}-${day}`
}


function formatDate(value) {

  if (!value) {
    return '—'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return value
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


function formatPriority(value) {

  if (value === 'TRES_URGENT') {
    return 'Très urgent'
  }

  if (value === 'URGENT') {
    return 'Urgent'
  }

  return 'Normal'
}


function priorityClass(value) {

  if (value === 'TRES_URGENT') {
    return 'priority-critical'
  }

  if (value === 'URGENT') {
    return 'priority-urgent'
  }

  return 'priority-normal'
}


function formatFileSize(bytes) {

  if (!bytes) {
    return '0 Ko'
  }

  if (bytes < 1024) {
    return `${bytes} octets`
  }

  if (bytes < 1024 * 1024) {
    return `${(bytes / 1024).toFixed(1)} Ko`
  }

  return `${(bytes / (1024 * 1024)).toFixed(2)} Mo`
}


/* ============================================================
   NATURE HELPERS
============================================================ */

function getNatureId(nature) {

  return (
    nature?.num_nat ??
    nature?.id ??
    nature?.id_nature ??
    nature?.id_nat
  )
}


function getNatureLabel(nature) {

  return (
    nature?.lib_nat ??
    nature?.nom_nature ??
    nature?.nom ??
    nature?.libelle ??
    nature?.name ??
    'Nature sans nom'
  )
}


function getNatureFromCourrier(courrier) {

  if (!courrier) {
    return '—'
  }

  if (courrier.nature) {
    return getNatureLabel(courrier.nature)
  }

  if (courrier.num_nat !== undefined) {

    const nature = natures.value.find(
      item =>
        String(getNatureId(item)) ===
        String(courrier.num_nat)
    )

    if (nature) {
      return getNatureLabel(nature)
    }
  }

  return '—'
}


/* ============================================================
   CLASSEMENT HELPERS
============================================================ */

function getClassementId(item) {

  return (
    item?.id_class ??
    item?.id ??
    item?.num_class
  )
}


function getClassementLabel(item) {

  return (
    item?.lib_class ??
    item?.nom_class ??
    item?.libelle ??
    item?.nom ??
    item?.name ??
    'Classement sans nom'
  )
}


function getClassementFromCourrier(courrier) {

  if (!courrier) {
    return '—'
  }

  if (courrier.classement) {
    return getClassementLabel(courrier.classement)
  }

  if (courrier.classification) {
    return getClassementLabel(courrier.classification)
  }

  if (courrier.id_class !== undefined) {

    const classement = classements.value.find(
      item =>
        String(getClassementId(item)) ===
        String(courrier.id_class)
    )

    if (classement) {
      return getClassementLabel(classement)
    }
  }

  return '—'
}


/* ============================================================
   DESTINATION HELPERS
============================================================ */

function getDestinationId(item) {
  return (
    item?.id_desti ??
    item?.id_dest ??
    item?.id_destination ??
    item?.id ??
    item?.num_dest
  )
}


function getDestinationLabel(item) {
  return (
    item?.lib_officiel_desti ??
    item?.lib_dest ??
    item?.nom_dest ??
    item?.libelle ??
    item?.nom ??
    item?.name ??
    'Destination sans nom'
  )
}


function getDestinations(courrier) {

  if (!courrier) {
    return []
  }

  if (Array.isArray(courrier.destinations)) {
    return courrier.destinations
  }

  if (Array.isArray(courrier.destination)) {
    return courrier.destination
  }

  if (courrier.destinations?.data) {
    return courrier.destinations.data
  }

  return []
}


function getDestinationLabelById(id) {

  const destination = destinations.value.find(
    item =>
      String(getDestinationId(item)) ===
      String(id)
  )

  return destination
    ? getDestinationLabel(destination)
    : `Destination #${id}`
}


function destinationDejaSelectionnee(destination) {

  const id = getDestinationId(destination)

  return formulaire.destinations.some(
    selectedId =>
      String(selectedId) === String(id)
  )
}


/* ============================================================
   DOCUMENT HELPERS
============================================================ */

function getDocumentsFromCourrier(courrier) {

  if (!courrier) {
    return []
  }

  if (Array.isArray(courrier.documents)) {
    return courrier.documents
  }

  if (courrier.documents?.data) {
    return courrier.documents.data
  }

  return []
}


function getDocumentCount(courrier) {

  return getDocumentsFromCourrier(courrier).length
}


/* ============================================================
   API FETCH HELPER
============================================================ */

async function parseResponse(response) {

  if (gererErreurAuth(response)) {
    throw new Error('SESSION_EXPIRED')
  }

  const contentType =
    response.headers.get('content-type') || ''

  if (
    contentType.includes('application/json')
  ) {
    return await response.json()
  }

  const text = await response.text()

  return {
    message: text
  }
}


async function apiRequest(
  url,
  options = {}
) {

  try {

    const response = await fetch(
      url,
      options
    )

    const data = await parseResponse(response)

    if (!response.ok) {

      const error = new Error(
        data?.message ||
        `Erreur HTTP ${response.status}`
      )

      error.status = response.status

      error.data = data

      throw error
    }

    return data

  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      throw error
    }

    throw error
  }
}


/* ============================================================
   NORMALISATION PAGINATION
============================================================ */

function normalizeListResponse(response) {

  if (Array.isArray(response)) {

    return {

      data: response,

      currentPage: 1,

      lastPage: 1,

      total: response.length,

      perPage: pagination.perPage

    }
  }


  const data = Array.isArray(response?.data)
    ? response.data
    : Array.isArray(response?.data?.data)
      ? response.data.data
      : []


  const meta =
    response?.meta ??
    response?.data?.meta ??
    {}


  return {

    data,

    currentPage:
      Number(
        meta.current_page ??
        response?.current_page ??
        1
      ),

    lastPage:
      Number(
        meta.last_page ??
        response?.last_page ??
        1
      ),

    total:
      Number(
        meta.total ??
        response?.total ??
        data.length
      ),

    perPage:
      Number(
        meta.per_page ??
        pagination.perPage
      )

  }
}


/* ============================================================
   CHARGER COURRIERS
============================================================ */

async function chargerCourriers() {

  loading.value = true

  errorMessage.value = ''

  try {

    const params = new URLSearchParams()

    params.set(
      'page',
      String(pagination.currentPage)
    )

    params.set(
      'per_page',
      String(pagination.perPage)
    )

    if (filtres.search.trim()) {
      params.set(
        'search',
        filtres.search.trim()
      )
    }

    if (filtres.num_nat) {
      params.set(
        'num_nat',
        String(filtres.num_nat)
      )
    }

    if (filtres.id_class) {
      params.set(
        'id_class',
        String(filtres.id_class)
      )
    }

    if (filtres.date_debut) {
      params.set(
        'date_debut',
        filtres.date_debut
      )
    }

    if (filtres.date_fin) {
      params.set(
        'date_fin',
        filtres.date_fin
      )
    }

    if (filtres.priorite) {
      params.set(
        'priorite',
        filtres.priorite
      )
    }

    params.set(
      'sort',
      filtres.sort
    )

    params.set(
      'direction',
      filtres.direction
    )


    const response = await apiRequest(
      `${ENDPOINTS.courriers}?${params.toString()}`,
      {
        method: 'GET',
        headers: headersJSON()
      }
    )


    const normalized =
      normalizeListResponse(response)


    courriers.value =
      normalized.data


    pagination.currentPage =
      normalized.currentPage

    pagination.lastPage =
      normalized.lastPage

    pagination.total =
      normalized.total

  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error(
      'Erreur chargement courriers:',
      error
    )

    errorMessage.value =
      error?.data?.message ||
      error.message ||
      'Impossible de charger les courriers départ.'

  } finally {

    loading.value = false
  }
}


/* ============================================================
   CHARGER NATURES
============================================================ */

async function chargerNatures() {

  try {

    const response =
      await apiRequest(
        ENDPOINTS.natures,
        {
          method: 'GET',
          headers: headersJSON()
        }
      )


    const normalized =
      normalizeListResponse(response)


    natures.value =
      normalized.data

  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error(
      'Erreur chargement natures:',
      error
    )

  }
}


/* ============================================================
   CHARGER CLASSEMENTS
============================================================ */

async function chargerClassements() {

  try {

    const response =
      await apiRequest(
        ENDPOINTS.classements,
        {
          method: 'GET',
          headers: headersJSON()
        }
      )


    const normalized =
      normalizeListResponse(response)


    classements.value =
      normalized.data

  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error(
      'Erreur chargement classements:',
      error
    )

  }
}


/* ============================================================
   CHARGER DESTINATIONS
============================================================ */

async function chargerDestinations() {

  try {

    const response =
      await apiRequest(
        ENDPOINTS.destinations,
        {
          method: 'GET',
          headers: headersJSON()
        }
      )


    const normalized =
      normalizeListResponse(response)


    destinations.value =
      normalized.data

  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error(
      'Erreur chargement destinations:',
      error
    )

  }
}


/* ============================================================
   RECHERCHE / FILTRES
============================================================ */

function appliquerFiltres() {

  pagination.currentPage = 1

  chargerCourriers()
}


function reinitialiserFiltres() {

  filtres.search = ''

  filtres.num_nat = ''

  filtres.id_class = ''

  filtres.date_debut = ''

  filtres.date_fin = ''

  filtres.priorite = ''

  pagination.currentPage = 1

  chargerCourriers()
}


function changerPage(page) {

  if (
    page < 1 ||
    page > pagination.lastPage
  ) {
    return
  }

  pagination.currentPage = page

  chargerCourriers()
}


function changerNombreParPage() {

  pagination.currentPage = 1

  chargerCourriers()
}


/* ============================================================
   FORM RESET
============================================================ */

function resetFormulaire() {

  formulaire.num_ordre_dep = ''

  formulaire.date_dep =
    getTodayDate()

  formulaire.num_nat = ''

  formulaire.objet_courr_dep = ''

  formulaire.id_class = ''

  formulaire.destinations = []

  formulaire.priorite = 'NORMAL'

  fichiersSelectionnes.value = []

  destinationSelection.value = ''

  documentsCourant.value = []

  clearFormErrors()
}


function clearFormErrors() {

  Object.keys(erreursFormulaire)
    .forEach(key => {
      delete erreursFormulaire[key]
    })
}


/* ============================================================
   CREATION
============================================================ */

function ouvrirFormulaireCreation() {

  resetFormulaire()

  modeFormulaire.value = 'creation'

  showDetailsModal.value = false

  showFormModal.value = true
}


/* ============================================================
   MODIFICATION
============================================================ */

async function ouvrirFormulaireModification(courrier) {

  resetFormulaire()

  modeFormulaire.value = 'modification'

  formulaire.num_ordre_dep =
    courrier.num_ordre_dep ?? null

  formulaire.date_dep =
    normalizeDateForInput(
      courrier.date_dep
    )

  formulaire.num_nat =
    courrier.num_nat ??
    courrier.nature?.num_nat ??
    getNatureId(courrier.nature) ??
    ''

  formulaire.objet_courr_dep =
    courrier.objet_courr_dep || ''

  formulaire.id_class =
    courrier.id_class ??
    getClassementId(courrier.classement) ??
    ''

  formulaire.priorite =
    courrier.priorite || 'NORMAL'


  formulaire.destinations =
    getDestinations(courrier)
      .map(destination =>
        getDestinationId(destination)
      )
      .filter(Boolean)


  showDetailsModal.value = false

  showFormModal.value = true


  if (formulaire.num_ordre_dep) {

    await chargerDocuments(
      formulaire.num_ordre_dep
    )
  }
}


function normalizeDateForInput(value) {

  if (!value) {
    return getTodayDate()
  }

  const stringValue =
    String(value)

  if (
    /^\d{4}-\d{2}-\d{2}$/
      .test(stringValue)
  ) {
    return stringValue
  }

  const date =
    new Date(stringValue)

  if (Number.isNaN(date.getTime())) {
    return getTodayDate()
  }

  return [
    date.getFullYear(),
    String(date.getMonth() + 1)
      .padStart(2, '0'),
    String(date.getDate())
      .padStart(2, '0')
  ].join('-')
}


/* ============================================================
   ENREGISTREMENT COURRIER
============================================================ */

async function enregistrerCourrier() {

  clearFormErrors()

  if (!validerFormulaire()) {
    return
  }

  saving.value = true

  try {

    const payload = {
      num_ordre_dep: Number(formulaire.num_ordre_dep),
      date_dep: formulaire.date_dep,
      num_nat: formulaire.num_nat,
      objet_courr_dep:
        formulaire.objet_courr_dep.trim(),
      id_class: formulaire.id_class,
      destinations: formulaire.destinations,
      priorite: formulaire.priorite
    }


    let response


    if (
      modeFormulaire.value === 'creation'
    ) {

      response =
        await apiRequest(
          ENDPOINTS.courriers,
          {
            method: 'POST',
            headers: headersJSON(),
            body: JSON.stringify(payload)
          }
        )

    } else {

      response =
        await apiRequest(
          `${ENDPOINTS.courriers}/${encodeURIComponent(
            formulaire.num_ordre_dep
          )}`,
          {
            method: 'PUT',
            headers: headersJSON(),
            body: JSON.stringify(payload)
          }
        )
    }


    const courrier =
      response?.data ??
      response?.courrier ??
      response


    const numOrdreDep =
      courrier?.num_ordre_dep ??
      formulaire.num_ordre_dep


    /*
     * Upload des nouveaux documents
     */
    if (
      numOrdreDep &&
      fichiersSelectionnes.value.length
    ) {

      await uploaderDocuments(
        numOrdreDep
      )
    }


    showToast(
      modeFormulaire.value === 'creation'
        ? 'Courrier départ créé avec succès.'
        : 'Courrier départ modifié avec succès.'
    )


    showFormModal.value = false

    await chargerCourriers()

  } catch (error) {

    if (
      error.message === 'SESSION_EXPIRED'
    ) {
      return
    }


    if (error.status === 422) {

      traiterErreursValidation(
        error.data
      )

      return
    }


    console.error(
      'Erreur enregistrement:',
      error
    )

    showToast(
      error?.data?.message ||
      error.message ||
      'Erreur lors de l’enregistrement.',
      'error'
    )

  } finally {

    saving.value = false
  }
}


/* ============================================================
   VALIDATION FORM
============================================================ */

function validerFormulaire() {

  let valid = true

  if (
    formulaire.num_ordre_dep === '' ||
    formulaire.num_ordre_dep === null ||
    formulaire.num_ordre_dep === undefined
  ) {
    erreursFormulaire.num_ordre_dep =
      'Le numéro d’ordre est obligatoire.'
    valid = false
  } else if (
    !Number.isInteger(Number(formulaire.num_ordre_dep)) ||
    Number(formulaire.num_ordre_dep) < 1
  ) {
    erreursFormulaire.num_ordre_dep =
      'Le numéro d’ordre doit être un nombre entier supérieur à 0.'
    valid = false
  }


  if (!formulaire.date_dep) {

    erreursFormulaire.date_dep =
      'La date est obligatoire.'

    valid = false
  }


  if (!formulaire.num_nat) {

    erreursFormulaire.num_nat =
      'La nature est obligatoire.'

    valid = false
  }


  if (!formulaire.objet_courr_dep.trim()) {

    erreursFormulaire.objet_courr_dep =
      'L’objet est obligatoire.'

    valid = false
  }


  if (!formulaire.id_class) {

    erreursFormulaire.id_class =
      'Le classement est obligatoire.'

    valid = false
  }


  if (!formulaire.destinations.length) {

    erreursFormulaire.destinations =
      'Sélectionnez au moins une destination.'

    valid = false
  }


  return valid
}


/* ============================================================
   VALIDATION API 422
============================================================ */

function traiterErreursValidation(data) {

  clearFormErrors()

  if (data?.errors) {

    Object.entries(data.errors)
      .forEach(
        ([champ, messages]) => {

          erreursFormulaire[champ] =
            Array.isArray(messages)
              ? messages.join(' ')
              : String(messages)
        }
      )
  }


  showToast(
    data?.message ||
    'Veuillez corriger les erreurs du formulaire.',
    'error'
  )
}


function formatErreurChamp(
  champ,
  messages
) {

  const labels = {

    date_dep: 'Date',

    num_nat: 'Nature',

    objet_courr_dep: 'Objet',

    id_class: 'Classement',

    destinations: 'Destinations',

    priorite: 'Priorité',

    documents: 'Documents'

  }


  return `${labels[champ] || champ} : ${
    Array.isArray(messages)
      ? messages.join(' ')
      : messages
  }`
}


/* ============================================================
   FERMER FORM
============================================================ */

function fermerFormulaire() {

  if (saving.value) {
    return
  }

  showFormModal.value = false
}


/* ============================================================
   DESTINATIONS FORM
============================================================ */

function ajouterDestinationSelectionnee() {
  if (!destinationSelection.value) {
    return
  }

  const id = destinationSelection.value

  const existe = formulaire.destinations.some(
    selectedId =>
      String(selectedId) === String(id)
  )

  if (!existe) {
    formulaire.destinations.push(id)
    delete erreursFormulaire.destinations
  }

  destinationSelection.value = ''
}


function retirerDestination(id) {

  formulaire.destinations =
    formulaire.destinations.filter(
      selectedId =>
        String(selectedId) !==
        String(id)
    )
}


/* ============================================================
   DOCUMENT FILE PICKER
============================================================ */

function ouvrirInputFichier() {

  fileInput.value?.click()
}


function handleFileChange(event) {

  const files =
    Array.from(
      event.target.files || []
    )

  ajouterFichiers(files)

  event.target.value = ''
}


function handleDrop(event) {

  isDragging.value = false

  const files =
    Array.from(
      event.dataTransfer?.files || []
    )

  ajouterFichiers(files)
}


function ajouterFichiers(files) {

  const invalidFiles = []

  for (const file of files) {

    const extension =
      file.name
        .split('.')
        .pop()
        ?.toLowerCase() || ''


    if (
      !ALLOWED_EXTENSIONS
        .includes(extension)
    ) {

      invalidFiles.push(
        `${file.name} : format non autorisé`
      )

      continue
    }


    if (
      file.size > MAX_FILE_SIZE
    ) {

      invalidFiles.push(
        `${file.name} : taille maximale 10 Mo`
      )

      continue
    }


    const duplicate =
      fichiersSelectionnes.value.some(
        existing =>
          existing.name === file.name &&
          existing.size === file.size
      )


    if (!duplicate) {

      fichiersSelectionnes.value.push(
        file
      )
    }
  }


  if (invalidFiles.length) {

    erreursFormulaire.documents =
      invalidFiles.join(' ; ')

  } else {

    delete erreursFormulaire.documents
  }
}


function retirerFichier(index) {

  fichiersSelectionnes.value
    .splice(index, 1)
}


/* ============================================================
   UPLOAD DOCUMENTS
============================================================ */
async function uploaderDocuments(numOrdreDep) {
  if (
    !numOrdreDep ||
    !fichiersSelectionnes.value.length
  ) {
    return
  }

  for (const file of fichiersSelectionnes.value) {
    const formData = new FormData()

    formData.append('document', file)

    // Debug : vérifier exactement ce qui est envoyé
    console.log('UPLOAD DOCUMENT')
    console.log('numOrdreDep:', numOrdreDep)
    console.log('nom fichier:', file.name)
    console.log('type:', file.type)
    console.log('taille:', file.size)

    try {
      await apiRequest(
        ENDPOINTS.documents(numOrdreDep),
        {
          method: 'POST',
          headers: headersFormData(),
          body: formData
        }
      )

      console.log(
        `Document ${file.name} envoyé avec succès.`
      )

    } catch (error) {
      console.error(
        'Erreur upload document:',
        error
      )

      console.error(
        'Status HTTP:',
        error?.status
      )

      console.error(
        'Données retournées par Laravel:',
        error?.data
      )

      console.error(
        'Erreurs validation:',
        error?.data?.errors
      )

      const erreurs =
        error?.data?.errors
          ? JSON.stringify(error.data.errors)
          : error?.data?.message ||
            error?.message ||
            'Erreur inconnue'

      throw new Error(
        `Impossible d'envoyer le document ${file.name}. ${erreurs}`
      )
    }
  }
}


/* ============================================================
   CHARGER DOCUMENTS
============================================================ */

async function chargerDocuments(
  numOrdreDep
) {

  if (!numOrdreDep) {
    documentsCourant.value = []
    return
  }


  loadingDocuments.value = true


  try {

    const response =
      await apiRequest(
        ENDPOINTS.documents(
          numOrdreDep
        ),
        {
          method: 'GET',
          headers: headersJSON()
        }
      )


    const normalized =
      normalizeListResponse(response)


    documentsCourant.value =
      normalized.data

  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error(
      'Erreur chargement documents:',
      error
    )

    documentsCourant.value = []

  } finally {

    loadingDocuments.value = false
  }
}


/* ============================================================
   TELECHARGER DOCUMENT
============================================================ */

async function telechargerDocument(
  doc
) {

  /*
   * REGLE CRITIQUE :
   * jamais document.id
   * toujours doc.num_doc
   */

  if (!doc?.num_doc) {

    showToast(
      'Identifiant du document introuvable.',
      'error'
    )

    return
  }


  const numOrdreDep =
    courrierSelectionne.value
      ?.num_ordre_dep ??
    formulaire.num_ordre_dep


  if (!numOrdreDep) {

    showToast(
      'Numéro du courrier introuvable.',
      'error'
    )

    return
  }


  try {

    const response =
      await fetch(
        ENDPOINTS.documentDownload(
          numOrdreDep,
          doc.num_doc
        ),
        {
          method: 'GET',
          headers: {
            Accept:
              'application/octet-stream, application/pdf, application/json',
            Authorization:
              `Bearer ${getToken()}`
          }
        }
      )


    if (gererErreurAuth(response)) {
      return
    }


    if (!response.ok) {

      const data =
        await parseResponse(response)

      throw new Error(
        data?.message ||
        'Téléchargement impossible.'
      )
    }


    const blob =
      await response.blob()


    const url =
      window.URL.createObjectURL(
        blob
      )


    const link =
      window.document.createElement('a')

    link.href = url

    link.download =
      doc.nom_original ||
      `document-${doc.num_doc}`


    window.document.body.appendChild(link)

    link.click()

    link.remove()

    window.URL.revokeObjectURL(url)

  } catch (error) {

    console.error(
      'Erreur téléchargement:',
      error
    )

    showToast(
      error.message ||
      'Impossible de télécharger le document.',
      'error'
    )
  }
}


/* ============================================================
   APERÇU DOCUMENT
============================================================ */
async function afficherDocument(doc) {
  if (!doc?.num_doc) return showToast('Identifiant du document introuvable.', 'error')
  const numOrdreDep = courrierSelectionne.value?.num_ordre_dep ?? formulaire.num_ordre_dep
  if (!numOrdreDep) return showToast('Numéro du courrier introuvable.', 'error')
  fermerApercuDocument()
  documentPreview.value = doc
  showDocumentPreviewModal.value = true
  previewLoading.value = true
  try {
    const response = await fetch(ENDPOINTS.documentDownload(numOrdreDep, doc.num_doc), { headers: { Accept: 'application/octet-stream, application/pdf, image/*', ...(getToken() ? { Authorization: `Bearer ${getToken()}` } : {}) } })
    if (!response.ok) throw new Error('Impossible de charger l’aperçu.')
    const blob = await response.blob()
    previewUrl.value = window.URL.createObjectURL(blob)
  } catch (error) {
    console.error('Erreur aperçu:', error)
    showToast(error.message || 'Impossible d’afficher le document.', 'error')
  } finally { previewLoading.value = false }
}
function fermerApercuDocument() {
  if (previewUrl.value) window.URL.revokeObjectURL(previewUrl.value)
  previewUrl.value = ''
  documentPreview.value = null
  previewLoading.value = false
  showDocumentPreviewModal.value = false
}
async function supprimerDocumentDepuisApercu() {
  const doc = documentPreview.value
  if (!doc?.num_doc) return
  const numOrdreDep = courrierSelectionne.value?.num_ordre_dep ?? formulaire.num_ordre_dep
  try {
    await apiRequest(ENDPOINTS.documentDelete(numOrdreDep, doc.num_doc), { method: 'DELETE', headers: headersJSON() })
    documentsCourant.value = documentsCourant.value.filter(item => String(item.num_doc) !== String(doc.num_doc))
    showToast('Document supprimé avec succès.')
    fermerApercuDocument()
    await chargerCourriers()
  } catch (error) { showToast(error?.data?.message || error.message || 'Impossible de supprimer le document.', 'error') }
}

/* ============================================================
   SUPPRIMER DOCUMENT
============================================================ */

function supprimerDocument(document) {
  if (!document?.num_doc) return showToast('Identifiant du document introuvable.', 'error')
  documentASupprimer.value = document
  showDocumentDeleteModal.value = true
}
function fermerSuppressionDocument() { showDocumentDeleteModal.value = false; documentASupprimer.value = null }
async function confirmerSuppressionDocument() {
  const doc = documentASupprimer.value
  if (!doc) return
  showDocumentDeleteModal.value = false
  documentASupprimer.value = null
  await supprimerDocumentDirect(doc)
}

async function supprimerDocumentDirect(
  document
) {

  /*
   * REGLE CRITIQUE :
   * toujours document.num_doc
   */

  if (!document?.num_doc) {

    showToast(
      'Identifiant du document introuvable.',
      'error'
    )

    return
  }


  const numOrdreDep =
    courrierSelectionne.value
      ?.num_ordre_dep ??
    formulaire.num_ordre_dep


  if (!numOrdreDep) {

    showToast(
      'Numéro du courrier introuvable.',
      'error'
    )

    return
  }

  try {

    await apiRequest(
      ENDPOINTS.documentDelete(
        numOrdreDep,
        document.num_doc
      ),
      {
        method: 'DELETE',
        headers: headersJSON()
      }
    )


    documentsCourant.value =
      documentsCourant.value.filter(
        item =>
          String(item.num_doc) !==
          String(document.num_doc)
      )


    showToast(
      'Document supprimé avec succès.'
    )


    await chargerCourriers()

  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error(
      'Erreur suppression document:',
      error
    )

    showToast(
      error?.data?.message ||
      error.message ||
      'Impossible de supprimer le document.',
      'error'
    )
  }
}


/* ============================================================
   DETAILS
============================================================ */

async function voirCourrier(courrier) {

  courrierSelectionne.value =
    courrier

  showDetailsModal.value = true

  loadingDetails.value = true

  documentsCourant.value = []


  try {

    const response =
      await apiRequest(
        `${ENDPOINTS.courriers}/${encodeURIComponent(
          courrier.num_ordre_dep
        )}`,
        {
          method: 'GET',
          headers: headersJSON()
        }
      )


    const data =
      response?.data ??
      response


    courrierSelectionne.value =
      data


  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error(
      'Erreur détails:',
      error
    )

  } finally {

    loadingDetails.value = false
  }


  await chargerDocuments(
    courrier.num_ordre_dep
  )
}


function fermerDetails() {

  showDetailsModal.value = false

  courrierSelectionne.value = null

  documentsCourant.value = []
}


function modifierDepuisDetails() {

  if (!courrierSelectionne.value) {
    return
  }

  ouvrirFormulaireModification(
    courrierSelectionne.value
  )
}


/* ============================================================
   SUPPRESSION COURRIER
============================================================ */

function confirmerSuppression(
  courrier
) {

  courrierASupprimer.value =
    courrier

  showDeleteModal.value = true
}


function fermerSuppression() {

  if (deleting.value) {
    return
  }

  showDeleteModal.value = false

  courrierASupprimer.value = null
}


async function supprimerCourrier() {

  if (
    !courrierASupprimer.value
      ?.num_ordre_dep
  ) {

    showToast(
      'Numéro du courrier introuvable.',
      'error'
    )

    return
  }


  deleting.value = true


  try {

    await apiRequest(
      `${ENDPOINTS.courriers}/${encodeURIComponent(
        courrierASupprimer.value.num_ordre_dep
      )}`,
      {
        method: 'DELETE',
        headers: headersJSON()
      }
    )


    showToast(
      'Courrier supprimé avec succès.'
    )


    showDeleteModal.value = false

    courrierASupprimer.value = null


    /*
     * Si dernière ligne de la page :
     * revenir à la page précédente.
     */
    if (
      courriers.value.length === 1 &&
      pagination.currentPage > 1
    ) {

      pagination.currentPage--
    }


    await chargerCourriers()

  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }


    console.error(
      'Erreur suppression courrier:',
      error
    )


    showToast(
      error?.data?.message ||
      error.message ||
      'Impossible de supprimer le courrier.',
      'error'
    )

  } finally {

    deleting.value = false
  }
}


/* ============================================================
   REFERENCE MANAGER
============================================================ */

function ouvrirGestionReference(
  type
) {

  referenceType.value = type

  nouvelleReference.value = ''

  showReferenceModal.value = true

  chargerReferenceItems()
}


function fermerGestionReference() {

  if (referenceSaving.value) {
    return
  }

  showReferenceModal.value = false

  nouvelleReference.value = ''

  referenceItems.value = []
}


async function chargerReferenceItems() {

  referenceLoading.value = true


  try {

    let endpoint


    if (referenceType.value === 'nature') {
      endpoint = ENDPOINTS.natures
    }

    else if (
      referenceType.value === 'classement'
    ) {
      endpoint = ENDPOINTS.classements
    }

    else {
      endpoint = ENDPOINTS.destinations
    }


    const response =
      await apiRequest(
        endpoint,
        {
          method: 'GET',
          headers: headersJSON()
        }
      )


    const normalized =
      normalizeListResponse(response)


    if (
      referenceType.value ===
      'classement'
    ) {

      referenceItems.value =
        normalized.data.filter(
          item => {

            const type =
              String(
                item.type_courrier ??
                item.type ??
                ''
              ).toUpperCase()

            return (
              type === 'DEPART' &&
              item.actif !== false
            )
          }
        )

    } else {

      referenceItems.value =
        normalized.data
    }


  } catch (error) {

    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error(
      'Erreur référence:',
      error
    )

    showToast(
      'Impossible de charger la liste.',
      'error'
    )

  } finally {

    referenceLoading.value = false
  }
}


function getReferenceId(item) {

  if (
    referenceType.value ===
    'nature'
  ) {
    return getNatureId(item)
  }

  if (
    referenceType.value ===
    'classement'
  ) {
    return getClassementId(item)
  }

  return getDestinationId(item)
}


function getReferenceLabel(item) {

  if (
    referenceType.value ===
    'nature'
  ) {
    return getNatureLabel(item)
  }

  if (
    referenceType.value ===
    'classement'
  ) {
    return getClassementLabel(item)
  }

  return getDestinationLabel(item)
}


/* ============================================================
   AJOUT REFERENCE
============================================================ */
async function ajouterReference() {
  const valeur = nouvelleReference.value.trim()

  if (!valeur) {
    showToast('Veuillez saisir une valeur.', 'error')
    return
  }

  referenceSaving.value = true

  try {
    let endpoint
    let payload

    // Nature
    if (referenceType.value === 'nature') {
      endpoint = ENDPOINTS.natures

      payload = {
        nom_nature: valeur
      }
    }

    // Classement
    else if (referenceType.value === 'classement') {
      endpoint = ENDPOINTS.classements

      payload = {
        nom_class: valeur,
        type_courrier: 'DEPART',
        actif: true
      }
    }

    // Destination
    else {
      endpoint = ENDPOINTS.destinations

      payload = {
        lib_officiel_desti: valeur
      }
    }

    console.log('AJOUT RÉFÉRENCE:', {
      endpoint,
      payload
    })

    await apiRequest(endpoint, {
      method: 'POST',
      headers: headersJSON(),
      body: JSON.stringify(payload)
    })

    nouvelleReference.value = ''

    await chargerReferenceItems()

    await Promise.all([
      chargerNatures(),
      chargerClassements(),
      chargerDestinations()
    ])

    showToast('Élément ajouté avec succès.')
  } catch (error) {
    if (error.message === 'SESSION_EXPIRED') {
      return
    }

    console.error('Erreur ajout référence:', error)

    if (error.status === 422) {
      showToast(
        error?.data?.message ||
        'Données invalides.',
        'error'
      )
    } else {
      showToast(
        error?.data?.message ||
        error.message ||
        'Impossible d’ajouter cet élément.',
        'error'
      )
    }
  } finally {
    referenceSaving.value = false
  }
}

/* ============================================================
   SUPPRESSION REFERENCE
============================================================ */

function confirmerSuppressionReference(item) {
  if (!item) {
    showToast(
      'Élément à supprimer introuvable.',
      'error'
    )
    return
  }

  referenceASupprimer.value = item
  showReferenceDeleteModal.value = true

  console.log('REFERENCE À SUPPRIMER :', {
    type: referenceType.value,
    item,
    id: getReferenceId(item),
    label: getReferenceLabel(item)
  })
}

function fermerSuppressionReference() {
  if (referenceDeleting.value) {
    return
  }

  showReferenceDeleteModal.value = false
  referenceASupprimer.value = null
}

async function supprimerReference() {
  const item = referenceASupprimer.value

  if (!item) {
    showToast(
      'Aucun élément sélectionné.',
      'error'
    )
    return
  }

  const id = getReferenceId(item)

  if (
    id === undefined ||
    id === null ||
    id === ''
  ) {
    console.error(
      'ID référence introuvable :',
      item
    )

    showToast(
      'Identifiant de l’élément introuvable.',
      'error'
    )

    return
  }

  let endpoint = ''

  if (referenceType.value === 'nature') {
    endpoint =
      `${ENDPOINTS.natures}/${encodeURIComponent(id)}`
  }

  else if (referenceType.value === 'classement') {
    endpoint =
      `${ENDPOINTS.classements}/${encodeURIComponent(id)}`
  }

  else if (referenceType.value === 'destination') {
    endpoint =
      `${ENDPOINTS.destinations}/${encodeURIComponent(id)}`
  }

  else {
    showToast(
      'Type de référence inconnu.',
      'error'
    )
    return
  }

  console.log('=================================')
  console.log('SUPPRESSION RÉFÉRENCE')
  console.log('Type :', referenceType.value)
  console.log('ID :', id)
  console.log('Endpoint :', endpoint)
  console.log('=================================')

  referenceDeleting.value = true

  try {
    const response = await apiRequest(
      endpoint,
      {
        method: 'DELETE',
        headers: headersJSON()
      }
    )

    console.log(
      'Réponse DELETE :',
      response
    )

    /*
     * Supprimer immédiatement de la liste locale
     * pour que l'interface soit mise à jour.
     */
    referenceItems.value =
      referenceItems.value.filter(
        currentItem =>
          String(getReferenceId(currentItem)) !==
          String(id)
      )

    /*
     * Recharger les listes globales.
     */
    await Promise.all([
      chargerNatures(),
      chargerClassements(),
      chargerDestinations()
    ])

    /*
     * Fermer la confirmation.
     */
    showReferenceDeleteModal.value = false
    referenceASupprimer.value = null

    showToast(
      `${referenceType.value === 'nature'
        ? 'Nature'
        : referenceType.value === 'classement'
          ? 'Classement'
          : 'Destination'
      } supprimé avec succès.`
    )

  } catch (error) {

    if (
      error.message === 'SESSION_EXPIRED'
    ) {
      return
    }

    console.error(
      'Erreur suppression référence :',
      error
    )

    console.error(
      'Status HTTP :',
      error?.status
    )

    console.error(
      'Réponse Laravel :',
      error?.data
    )

    showToast(
      error?.data?.message ||
      error.message ||
      'Impossible de supprimer cet élément.',
      'error'
    )

  } finally {

    referenceDeleting.value = false
  }
}
/* ============================================================
   MOUNTED
============================================================ */

onMounted(async () => {

  await Promise.all([
    chargerNatures(),
    chargerClassements(),
    chargerDestinations()
  ])

  await chargerCourriers()

})
</script>


<style scoped>

/* ============================================================
   GLOBAL
============================================================ */

* {
  box-sizing: border-box;
}


.courriers-page {
  width: 100%;
  min-height: 100%;
  padding: 28px;
  background: #f6f8fb;
  color: #172033;
}


/* ============================================================
   HEADER
============================================================ */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 26px;
}


.header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}


.header-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #eef4ff;
  color: #2563eb;
}


.page-header h1 {
  margin: 0 0 4px;
  font-size: 25px;
  font-weight: 750;
  letter-spacing: -0.02em;
}


.page-header p {
  margin: 0;
  color: #687386;
  font-size: 14px;
}


/* ============================================================
   BUTTONS
============================================================ */

.btn {
  min-height: 42px;
  padding: 0 16px;
  border: 1px solid transparent;
  border-radius: 9px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 650;
  cursor: pointer;
  transition:
    background .2s ease,
    border-color .2s ease,
    transform .2s ease,
    box-shadow .2s ease;
}


.btn:disabled {
  opacity: .55;
  cursor: not-allowed;
}


.btn-primary {
  color: white;
  background: #2563eb;
  border-color: #2563eb;
}


.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
  border-color: #1d4ed8;
  box-shadow: 0 5px 15px rgba(37, 99, 235, .18);
}


.btn-secondary {
  color: #344054;
  background: #eef2f7;
  border-color: #dce2ea;
}


.btn-secondary:hover:not(:disabled) {
  background: #e5eaf1;
}


.btn-light {
  color: #344054;
  background: white;
  border-color: #d9dee7;
}


.btn-light:hover:not(:disabled) {
  background: #f7f8fa;
}


.btn-danger {
  color: white;
  background: #dc2626;
  border-color: #dc2626;
}


.btn-danger:hover:not(:disabled) {
  background: #b91c1c;
}


/* ============================================================
   ALERT
============================================================ */

.alert {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 11px;
  margin-bottom: 22px;
}


.alert-error {
  background: #fff1f2;
  border: 1px solid #fecdd3;
  color: #9f1239;
}


.alert p {
  margin: 4px 0 0;
  font-size: 13px;
}


.alert-close {
  margin-left: auto;
  border: 0;
  background: transparent;
  cursor: pointer;
  color: inherit;
}


/* ============================================================
   STATISTICS
============================================================ */

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 22px;
}


.stat-card {
  min-height: 108px;
  padding: 20px;
  border: 1px solid #e5e9f0;
  border-radius: 13px;
  background: white;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
}


.stat-icon {
  width: 46px;
  height: 46px;
  border-radius: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
}


.stat-blue {
  background: #eff6ff;
  color: #2563eb;
}


.stat-purple {
  background: #f5f3ff;
  color: #7c3aed;
}


.stat-green {
  background: #ecfdf5;
  color: #059669;
}


.stat-red {
  background: #fff1f2;
  color: #e11d48;
}


.stat-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}


.stat-label {
  color: #7a8495;
  font-size: 12px;
  font-weight: 600;
}


.stat-content strong {
  font-size: 24px;
  line-height: 1;
}


/* ============================================================
   FILTER PANEL
============================================================ */

.filter-panel {
  padding: 18px;
  margin-bottom: 22px;
  border: 1px solid #e5e9f0;
  border-radius: 13px;
  background: white;
  box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
}


.search-row {
  display: flex;
  gap: 10px;
  margin-bottom: 17px;
}


.search-box {
  flex: 1;
  height: 44px;
  border: 1px solid #d9dee7;
  border-radius: 9px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 13px;
  color: #8a94a6;
  transition: border-color .2s, box-shadow .2s;
}


.search-box:focus-within {
  border-color: #7da6f8;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
}


.search-box input {
  width: 100%;
  height: 100%;
  border: 0;
  outline: 0;
  background: transparent;
  color: #172033;
  font-size: 13px;
}


.search-box input::placeholder {
  color: #a0a8b6;
}


.search-clear {
  width: 25px;
  height: 25px;
  border: 0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #eef1f5;
  color: #6b7280;
  cursor: pointer;
}


.filters-grid {
  display: grid;
  grid-template-columns:
    repeat(6, minmax(0, 1fr));
  gap: 14px;
}


.field {
  min-width: 0;
}


.field.full-width {
  grid-column: 1 / -1;
}


.field label {
  display: block;
  margin-bottom: 7px;
  color: #475467;
  font-size: 12px;
  font-weight: 650;
}


.required {
  color: #dc2626;
}


.field input,
.field select,
.field textarea,
.reference-add input {
  width: 100%;
  border: 1px solid #d9dee7;
  border-radius: 8px;
  outline: none;
  background: white;
  color: #1f2937;
  font-family: inherit;
  font-size: 13px;
  transition: border-color .2s, box-shadow .2s;
}


.field input,
.field select {
  height: 42px;
  padding: 0 11px;
}


.field textarea {
  padding: 11px;
  resize: vertical;
  min-height: 100px;
}


.field input:focus,
.field select:focus,
.field textarea:focus {
  border-color: #7da6f8;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .07);
}


.field small {
  display: block;
  margin-top: 5px;
  text-align: right;
  color: #98a2b3;
  font-size: 11px;
}


.select-with-action {
  display: flex;
  gap: 5px;
}


.select-with-action select {
  flex: 1;
}


.mini-action {
  width: 40px;
  min-width: 40px;
  border: 1px solid #d9dee7;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  color: #475467;
  cursor: pointer;
}


.mini-action:hover {
  background: #f5f7fa;
  color: #2563eb;
}


.filter-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 17px;
  padding-top: 15px;
  border-top: 1px solid #edf0f4;
}


.result-count {
  color: #667085;
  font-size: 12px;
}


/* ============================================================
   TABLE
============================================================ */

.table-card {
  border: 1px solid #e5e9f0;
  border-radius: 13px;
  overflow: hidden;
  background: white;
  box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
}


.table-header {
  padding: 17px 20px;
  border-bottom: 1px solid #edf0f4;
}


.table-header h2 {
  margin: 0 0 4px;
  font-size: 15px;
}


.table-header span {
  color: #8791a2;
  font-size: 12px;
}


.table-wrapper {
  width: 100%;
  overflow-x: auto;
}


table {
  width: 100%;
  min-width: 1120px;
  border-collapse: collapse;
}


thead {
  background: #f8fafc;
}


th {
  padding: 12px 14px;
  text-align: left;
  white-space: nowrap;
  color: #667085;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .025em;
}


td {
  padding: 14px;
  border-top: 1px solid #edf0f4;
  vertical-align: middle;
  font-size: 12px;
}


tbody tr {
  transition: background .15s;
}


tbody tr:hover {
  background: #fafcff;
}


.actions-column {
  text-align: center;
}


.number-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 48px;
  padding: 5px 8px;
  border-radius: 7px;
  background: #eef4ff;
  color: #1d4ed8;
  font-weight: 750;
}


.date-value {
  color: #475467;
  white-space: nowrap;
}


.nature-value {
  font-weight: 650;
  color: #344054;
}


.objet-cell {
  max-width: 260px;
}


.objet-cell strong {
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  color: #1f2937;
  font-weight: 600;
  line-height: 1.45;
}


.classification-badge {
  display: inline-flex;
  padding: 5px 8px;
  border-radius: 6px;
  background: #f3f4f6;
  color: #4b5563;
  font-size: 11px;
  font-weight: 650;
}


.destination-list {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 5px;
}


.destination-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 7px;
  border-radius: 6px;
  background: #f0fdf4;
  color: #15803d;
  font-size: 10px;
  font-weight: 650;
}


.more-badge {
  padding: 5px 7px;
  border-radius: 6px;
  background: #f1f5f9;
  color: #64748b;
  font-size: 10px;
  font-weight: 700;
}


.muted {
  color: #98a2b3;
}


.priority-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 8px;
  border-radius: 20px;
  white-space: nowrap;
  font-size: 10px;
  font-weight: 700;
}


.priority-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}


.priority-normal {
  background: #f1f5f9;
  color: #475569;
}


.priority-normal .priority-dot {
  background: #64748b;
}


.priority-urgent {
  background: #fff7ed;
  color: #c2410c;
}


.priority-urgent .priority-dot {
  background: #f97316;
}


.priority-critical {
  background: #fff1f2;
  color: #be123c;
}


.priority-critical .priority-dot {
  background: #e11d48;
}


.document-count {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  border: 0;
  background: transparent;
  color: #475467;
  font-weight: 650;
  cursor: pointer;
}


.document-count:hover {
  color: #2563eb;
}


.row-actions {
  display: flex;
  justify-content: center;
  gap: 5px;
}


.icon-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #e4e7ec;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  cursor: pointer;
  transition: all .15s;
}


.icon-btn.view {
  color: #2563eb;
}


.icon-btn.view:hover {
  background: #eff6ff;
}


.icon-btn.edit {
  color: #d97706;
}


.icon-btn.edit:hover {
  background: #fffbeb;
}


.icon-btn.delete {
  color: #dc2626;
}


.icon-btn.delete:hover {
  background: #fff1f2;
}


/* ============================================================
   MOBILE CARDS
============================================================ */

.mobile-cards {
  display: none;
}


.courrier-card {
  margin-bottom: 13px;
  border: 1px solid #e5e9f0;
  border-radius: 13px;
  background: white;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
}


.card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  padding: 15px;
  border-bottom: 1px solid #edf0f4;
}


.card-number {
  display: block;
  margin-bottom: 4px;
  color: #1d4ed8;
  font-weight: 750;
  font-size: 13px;
}


.card-date {
  color: #8791a2;
  font-size: 11px;
}


.card-body {
  padding: 15px;
}


.card-info {
  margin-bottom: 13px;
}


.card-info:last-child {
  margin-bottom: 0;
}


.info-label {
  display: block;
  margin-bottom: 4px;
  color: #98a2b3;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}


.card-info strong {
  color: #344054;
  font-size: 12px;
  line-height: 1.45;
}


.card-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  border-top: 1px solid #edf0f4;
}


.btn-card {
  min-height: 43px;
  border: 0;
  border-right: 1px solid #edf0f4;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 650;
  cursor: pointer;
}


.btn-card:last-child {
  border-right: 0;
}


.btn-card.view {
  color: #2563eb;
}


.btn-card.edit {
  color: #d97706;
}


.btn-card.delete {
  color: #dc2626;
}


/* ============================================================
   PAGINATION
============================================================ */

.pagination-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  padding: 17px 2px;
}


.pagination-info {
  color: #667085;
  font-size: 12px;
}


.pagination {
  display: flex;
  align-items: center;
  gap: 4px;
}


.page-btn,
.page-number {
  min-width: 34px;
  height: 34px;
  border: 1px solid #dfe4eb;
  border-radius: 7px;
  background: white;
  color: #475467;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
}


.page-btn {
  padding: 0 10px;
}


.page-btn:hover:not(:disabled),
.page-number:hover:not(.active) {
  background: #f8fafc;
}


.page-btn:disabled {
  opacity: .45;
  cursor: not-allowed;
}


.page-number.active {
  background: #2563eb;
  border-color: #2563eb;
  color: white;
}


/* ============================================================
   LOADING
============================================================ */

.loading-container {
  min-height: 280px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #667085;
}


.loading-container.small {
  min-height: 160px;
}


.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #dbe5f5;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin .8s linear infinite;
}


.small-spinner {
  width: 22px;
  height: 22px;
}


.button-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin .7s linear infinite;
}


@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}


/* ============================================================
   EMPTY
============================================================ */

.empty-state {
  min-height: 320px;
  padding: 40px 20px;
  border: 1px solid #e5e9f0;
  border-radius: 13px;
  background: white;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}


.empty-icon {
  width: 70px;
  height: 70px;
  margin-bottom: 15px;
  border-radius: 50%;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
}


.empty-state h3 {
  margin: 0 0 7px;
  font-size: 16px;
}


.empty-state p {
  margin: 0 0 18px;
  color: #8791a2;
  font-size: 13px;
}


/* ============================================================
   MODALS
============================================================ */

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  padding: 25px;
  background: rgba(15, 23, 42, .55);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow-y: auto;
  backdrop-filter: blur(3px);
}


.modal {
  width: min(95vw, 620px);
  max-height: 90vh;
  border-radius: 15px;
  background: white;
  overflow: hidden;
  box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
}


.modal-large {
  width: min(95vw, 850px);
}


.modal-details {
  width: min(95vw, 760px);
}


.modal-confirm {
  width: min(95vw, 450px);
  padding: 30px;
  text-align: center;
}


.modal-reference {
  width: min(95vw, 560px);
}


.modal-header {
  padding: 20px 22px;
  border-bottom: 1px solid #edf0f4;
  display: flex;
  justify-content: space-between;
  gap: 15px;
}


.modal-header h2 {
  margin: 0 0 4px;
  color: #172033;
  font-size: 17px;
}


.modal-header p {
  margin: 0;
  color: #8791a2;
  font-size: 12px;
}


.modal-close {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  border: 0;
  border-radius: 8px;
  background: #f4f6f8;
  color: #667085;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}


.modal-close:hover {
  background: #e9edf2;
}


.modal-body {
  max-height: calc(90vh - 145px);
  overflow-y: auto;
  padding: 22px;
}


.form-section {
  margin-bottom: 25px;
}


.form-section:last-child {
  margin-bottom: 0;
}


.section-title {
  display: flex;
  align-items: center;
  gap: 11px;
  margin-bottom: 17px;
}


.section-icon {
  width: 35px;
  height: 35px;
  border-radius: 9px;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
}


.section-title h3 {
  margin: 0 0 3px;
  font-size: 13px;
}


.section-title p {
  margin: 0;
  color: #98a2b3;
  font-size: 11px;
}


.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
}


.readonly-field {
  height: 42px;
  padding: 0 12px;
  border: 1px solid #e1e5eb;
  border-radius: 8px;
  background: #f8fafc;
  color: #667085;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}


.validation-summary {
  margin-top: 18px;
  padding: 13px;
  border: 1px solid #fecaca;
  border-radius: 9px;
  background: #fef2f2;
  color: #991b1b;
  display: flex;
  align-items: flex-start;
  gap: 9px;
  font-size: 12px;
}


.validation-summary ul {
  margin: 6px 0 0;
  padding-left: 18px;
}


.validation-summary li {
  margin-bottom: 3px;
}


.field-error {
  margin-top: 8px;
  color: #dc2626;
  font-size: 11px;
}


/* ============================================================
   DESTINATIONS FORM
============================================================ */

.destination-select-row {
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: 7px;
}


.destination-select-row select {
  height: 42px;
  padding: 0 11px;
  border: 1px solid #d9dee7;
  border-radius: 8px;
  outline: none;
  background: white;
  font-size: 13px;
}


.selected-destinations {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
  margin-top: 13px;
}


.selected-destination {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 9px;
  border-radius: 8px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 11px;
  font-weight: 650;
}


.selected-destination button {
  width: 18px;
  height: 18px;
  border: 0;
  border-radius: 50%;
  background: rgba(37,99,235,.1);
  color: #1d4ed8;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}


.no-selection {
  margin-top: 12px;
  padding: 13px;
  border: 1px dashed #d8dee8;
  border-radius: 9px;
  color: #98a2b3;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}


/* ============================================================
   DROPZONE
============================================================ */

.dropzone {
  min-height: 175px;
  padding: 25px;
  border: 1.5px dashed #b8c4d5;
  border-radius: 11px;
  background: #fafcff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  cursor: pointer;
  transition: all .2s;
}


.dropzone:hover,
.dropzone.dragging {
  border-color: #2563eb;
  background: #f5f9ff;
}


.dropzone-icon {
  width: 50px;
  height: 50px;
  margin-bottom: 10px;
  border-radius: 12px;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
}


.dropzone strong {
  margin-bottom: 4px;
  font-size: 13px;
}


.dropzone span {
  color: #667085;
  font-size: 12px;
}


.dropzone small {
  margin-top: 8px;
  color: #98a2b3;
  font-size: 10px;
}


/* ============================================================
   FILES
============================================================ */

.file-list {
  display: flex;
  flex-direction: column;
  gap: 7px;
  margin-top: 13px;
}


.file-item {
  min-height: 58px;
  padding: 9px 11px;
  border: 1px solid #e4e7ec;
  border-radius: 9px;
  display: flex;
  align-items: center;
  gap: 10px;
}


.file-icon {
  width: 35px;
  height: 35px;
  flex-shrink: 0;
  border-radius: 8px;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
}


.file-info {
  min-width: 0;
  flex: 1;
}


.file-info strong {
  display: block;
  overflow: hidden;
  color: #344054;
  font-size: 11px;
  text-overflow: ellipsis;
  white-space: nowrap;
}


.file-info span {
  display: block;
  margin-top: 3px;
  color: #98a2b3;
  font-size: 10px;
}


.file-remove,
.file-download {
  width: 31px;
  height: 31px;
  flex-shrink: 0;
  border: 0;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}


.file-remove {
  color: #dc2626;
  background: #fff1f2;
}


.file-download {
  color: #2563eb;
  background: #eff6ff;
}


.file-remove:disabled,
.file-download:disabled {
  opacity: .4;
  cursor: not-allowed;
}


.file-actions {
  display: flex;
  gap: 5px;
}


.existing-documents {
  margin-top: 18px;
}


.existing-documents h4 {
  margin: 0 0 8px;
  color: #475467;
  font-size: 12px;
}


/* ============================================================
   MODAL FOOTER
============================================================ */

.modal-footer {
  padding: 0 22px 8px;
  border-top: 1px solid #edf0f4;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 8px;
  min-height: 40px;
  box-sizing: border-box;
  position: relative;
  top: -15px;
}


/* ============================================================
   DETAILS
============================================================ */

.details-number {
  display: inline-flex;
  margin-bottom: 6px;
  padding: 4px 8px;
  border-radius: 6px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 10px;
  font-weight: 750;
}


.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}


.detail-box {
  padding: 13px;
  border: 1px solid #e8ebf0;
  border-radius: 9px;
  background: #fafbfc;
}


.detail-box.full {
  grid-column: 1 / -1;
}


.detail-box span:first-child {
  display: block;
  margin-bottom: 5px;
  color: #98a2b3;
  font-size: 10px;
  font-weight: 650;
  text-transform: uppercase;
}


.detail-box strong {
  color: #344054;
  font-size: 12px;
  line-height: 1.5;
}


.details-section {
  margin-top: 22px;
}


.details-section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 11px;
  color: #344054;
}


.details-section-title h3 {
  margin: 0;
  font-size: 13px;
}


.count-pill {
  display: inline-flex;
  min-width: 20px;
  height: 20px;
  margin-left: 4px;
  align-items: center;
  justify-content: center;
  border-radius: 20px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 10px;
}


.detail-destinations {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
}


.no-documents {
  min-height: 120px;
  border: 1px dashed #d8dee8;
  border-radius: 9px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 7px;
  color: #98a2b3;
  font-size: 11px;
}


.document-loading {
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  color: #667085;
  font-size: 12px;
}


/* ============================================================
   DELETE MODAL
============================================================ */

.confirm-icon {
  width: 58px;
  height: 58px;
  margin: 0 auto 15px;
  border-radius: 50%;
  background: #fff1f2;
  color: #dc2626;
  display: flex;
  align-items: center;
  justify-content: center;
}


.modal-confirm h2 {
  margin: 0 0 8px;
  font-size: 18px;
}


.modal-confirm > p {
  margin: 0;
  color: #667085;
  font-size: 12px;
  line-height: 1.6;
}


.confirm-object {
  margin: 15px 0;
  padding: 12px;
  border-radius: 8px;
  background: #f8fafc;
  color: #344054;
  font-size: 12px;
  font-weight: 650;
}


.warning-box {
  padding: 11px;
  border: 1px solid #fed7aa;
  border-radius: 8px;
  background: #fff7ed;
  color: #9a3412;
  display: flex;
  align-items: flex-start;
  gap: 8px;
  text-align: left;
  font-size: 11px;
  line-height: 1.5;
}


.confirm-actions {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 20px;
}


/* ============================================================
   REFERENCE MODAL
============================================================ */

.reference-body {
  max-height: calc(90vh - 145px);
  overflow-y: auto;
  padding: 20px;
}


.reference-add {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
  margin-bottom: 18px;
}


.reference-add input {
  height: 42px;
  padding: 0 11px;
}


.reference-add input:focus {
  border-color: #7da6f8;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .07);
}


.reference-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}


.reference-item {
  min-height: 49px;
  padding: 7px 9px;
  border: 1px solid #e7eaf0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}


.reference-item-info {
  display: flex;
  align-items: center;
  gap: 9px;
  color: #344054;
  font-size: 12px;
  font-weight: 600;
}


.reference-item-icon {
  width: 31px;
  height: 31px;
  border-radius: 7px;
  background: #f1f5f9;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
}


.reference-delete {
  width: 31px;
  height: 31px;
  border: 0;
  border-radius: 7px;
  background: #fff1f2;
  color: #dc2626;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}


.reference-loading,
.reference-empty {
  min-height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #98a2b3;
  font-size: 12px;
}


.reference-loading {
  flex-direction: column;
  gap: 10px;
}


/* ============================================================
   TOAST
============================================================ */

.toast {
  position: fixed;
  right: 25px;
  bottom: 25px;
  z-index: 2000;
  min-width: 280px;
  max-width: 420px;
  padding: 13px 15px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 9px;
  box-shadow: 0 12px 35px rgba(15, 23, 42, .16);
  font-size: 12px;
  font-weight: 600;
}


.toast-success {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #047857;
}


.toast-error {
  background: #fff1f2;
  border: 1px solid #fecdd3;
  color: #be123c;
}


.toast button {
  margin-left: auto;
  border: 0;
  background: transparent;
  color: inherit;
  cursor: pointer;
}


.toast-enter-active,
.toast-leave-active {
  transition: all .25s ease;
}


.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(10px);
}


/* ============================================================
   RESPONSIVE TABLET
============================================================ */

@media (max-width: 1200px) {

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }


  .filters-grid {
    grid-template-columns:
      repeat(3, minmax(0, 1fr));
  }

}


/* ============================================================
   RESPONSIVE MOBILE
============================================================ */

@media (max-width: 800px) {

  .courriers-page {
    padding: 17px;
  }


  .page-header {
    align-items: flex-start;
  }


  .page-header h1 {
    font-size: 21px;
  }


  .header-icon {
    width: 45px;
    height: 45px;
  }


  .page-header .btn span {
    display: none;
  }


  .page-header .btn {
    width: 43px;
    padding: 0;
  }


  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }


  .stat-card {
    min-height: 92px;
    padding: 13px;
  }


  .stat-icon {
    width: 39px;
    height: 39px;
  }


  .stat-content strong {
    font-size: 20px;
  }


  .filter-panel {
    padding: 13px;
  }


  .search-row {
    flex-direction: column;
  }


  .filter-submit {
    width: 100%;
  }


  .filters-grid {
    grid-template-columns: 1fr;
  }


  .filter-actions {
    align-items: stretch;
    flex-direction: column;
  }


  .filter-actions .btn {
    width: 100%;
  }


  .result-count {
    text-align: center;
  }


  .desktop-table {
    display: none;
  }


  .mobile-cards {
    display: block;
  }


  .pagination-container {
    align-items: stretch;
    flex-direction: column;
  }


  .pagination-info {
    text-align: center;
  }


  .pagination {
    justify-content: center;
    flex-wrap: wrap;
  }


  .page-btn {
    font-size: 10px;
  }


  .modal-overlay {
    padding: 10px;
  }


  .modal {
    width: 100%;
    max-height: 94vh;
    border-radius: 12px;
  }


  .modal-body {
    padding: 17px;
    max-height: calc(94vh - 135px);
  }


  .form-grid {
    grid-template-columns: 1fr;
  }


  .field.full-width {
    grid-column: auto;
  }


  .destination-select-row {
    grid-template-columns: 1fr;
  }


  .destination-select-row .btn {
    width: 100%;
  }


  .details-grid {
    grid-template-columns: 1fr;
  }


  .detail-box.full {
    grid-column: auto;
  }


  .modal-footer {
    padding: 12px 17px;
  }


  .modal-footer .btn {
    flex: 1;
  }


  .reference-add {
    grid-template-columns: 1fr;
  }


  .toast {
    right: 12px;
    bottom: 12px;
    left: 12px;
    min-width: 0;
  }

}


/* ============================================================
   AMÉLIORATIONS UI - LISTES ET APERÇU
============================================================ */
.reference-body { display:flex; flex-direction:column; padding:20px 22px; overflow:hidden; }
.reference-list { max-height:340px; overflow-y:auto; padding-right:5px; scrollbar-width:thin; scrollbar-color:#94a3b8 transparent; }
.reference-list::-webkit-scrollbar { width:7px; }
.reference-list::-webkit-scrollbar-thumb { background:#94a3b8; border-radius:10px; }
.reference-item { flex:0 0 auto; transition:.2s ease; }
.reference-item:hover { transform:translateX(2px); border-color:#cbd5e1; box-shadow:0 5px 14px rgba(15,23,42,.06); }
.file-preview { color:#7c3aed; background:#f5f3ff; }
.file-preview:hover { background:#ede9fe; transform:translateY(-1px); }
.file-preview,.file-download,.file-remove { width:34px; height:34px; border:0; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:.2s ease; }
.modal-preview-document { width:min(96vw,980px); height:min(90vh,760px); display:flex; flex-direction:column; }
.document-preview-body { flex:1; min-height:0; padding:16px; background:#f8fafc; overflow:hidden; display:flex; align-items:center; justify-content:center; }
.document-frame { width:100%; height:100%; min-height:500px; border:1px solid #e2e8f0; border-radius:10px; background:#fff; }
.document-image-preview { max-width:100%; max-height:100%; object-fit:contain; border-radius:10px; box-shadow:0 8px 30px rgba(15,23,42,.12); }
.preview-loading,.preview-unavailable { text-align:center; color:#64748b; display:flex; flex-direction:column; align-items:center; gap:12px; }
.preview-unavailable h3 { margin:0; color:#334155; }
.preview-unavailable p { margin:0; font-size:13px; }
.preview-footer { flex-wrap:wrap; }
@media (max-width:700px) { .modal-preview-document { height:92vh; } .document-frame { min-height:420px; } .preview-footer .btn { flex:1; justify-content:center; } .reference-list { max-height:280px; } }

/* ============================================================
   SMALL MOBILE
============================================================ */

@media (max-width: 480px) {

  .courriers-page {
    padding: 12px;
  }


  .header-left {
    gap: 9px;
  }


  .page-header p {
    font-size: 11px;
  }


  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }


  .stat-card {
    gap: 8px;
    padding: 10px;
  }


  .stat-icon {
    width: 34px;
    height: 34px;
  }


  .stat-label {
    font-size: 10px;
  }


  .stat-content strong {
    font-size: 18px;
  }


  .card-actions {
    grid-template-columns: 1fr;
  }


  .btn-card {
    border-right: 0;
    border-bottom: 1px solid #edf0f4;
  }


  .btn-card:last-child {
    border-bottom: 0;
  }


  .modal-header {
    padding: 15px;
  }


  .modal-header h2 {
    font-size: 15px;
  }


  .modal-confirm {
    padding: 22px 16px;
  }

}

/* ============================================================
   MODAL CONFIRMATION RÉFÉRENCE
   ============================================================ */

.confirmation-overlay {
  position: fixed !important;
  inset: 0 !important;

  z-index: 99999 !important;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 25px;

  background: rgba(15, 23, 42, 0.60);

  backdrop-filter: blur(4px);
}

.confirmation-modal {
  position: relative !important;

  z-index: 100000 !important;

  width: min(95vw, 450px);
  max-height: 90vh;

  padding: 30px;

  border-radius: 15px;

  background: #ffffff;

  overflow: hidden;

  box-shadow:
    0 24px 70px rgba(15, 23, 42, 0.30);
}
</style>
```
