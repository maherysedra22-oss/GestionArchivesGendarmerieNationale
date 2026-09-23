<template>
  <div class="courriers-page">
    <!-- ============================================================
         HEADER
    ============================================================ -->
    <header class="page-header">
      <div class="header-title">
        <div class="title-icon">
          <span>✉</span>
        </div>

        <div>
          <h1>Courriers arrivés</h1>
          <p>Gestion et suivi des courriers administratifs reçus</p>
        </div>
      </div>

      <button
        v-if="canCreateCourriers"
        type="button"
        class="btn btn-primary btn-new"
        @click="openCreateModal"
        :disabled="loading"
      >
        <span class="btn-icon">＋</span>
        <span>Nouveau courrier</span>
      </button>
    </header>

    <!-- ============================================================
         STATISTIQUES
    ============================================================ -->
    <section class="stats-grid">
      <article class="stat-card stat-total">
        <div class="stat-icon"> 🗁 </div>
        <div class="stat-content">
          <span class="stat-label">Total courriers</span>
          <strong>{{ pagination.total }}</strong>
        </div>
      </article>

      <article class="stat-card stat-danger">
        <div class="stat-icon">⚠︎</div>
        <div class="stat-content">
          <span class="stat-label">Très urgents</span>
          <strong>{{ stats.tresUrgent }}</strong>
        </div>
      </article>

      <article class="stat-card stat-warning">
        <div class="stat-icon"><AlertCircle :size="22" /></div>
        <div class="stat-content">
          <span class="stat-label">Urgents</span>
          <strong>{{ stats.urgent }}</strong>
        </div>
      </article>

      <article class="stat-card stat-success">
        <div class="stat-icon"> <Archive :size="22" /></div>
        <div class="stat-content">
          <span class="stat-label">Archivés</span>
          <strong>{{ stats.archive }}</strong>
        </div>
      </article>
    </section>

    <!-- ============================================================
         FILTRES
    ============================================================ -->
    <section class="filters-card">
      <div class="filters-header">
        <div>
          <h2>Recherche et filtres</h2>
          <p>Rechercher et filtrer les courriers arrivés</p>
        </div>

        <button
          type="button"
          class="btn btn-light"
          @click="resetFilters"
          :disabled="loading"
        >
          <span class="btn-icon">↻</span>
          <span>Réinitialiser</span>
        </button>
      </div>

      <form class="filters-grid" @submit.prevent="searchCourriers">
        <!-- Recherche -->
        <div class="form-group filter-search">
          <label for="search">Recherche</label>

          <div class="input-with-icon">
            <span class="input-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
                >
                  <circle cx="11" cy="11" r="7"></circle>
                  <path d="m20 20-4-4"></path>
                </svg>
            </span>

            <input
              id="search"
              v-model="filters.search"
              type="text"
              placeholder="Numéro, origine, objet..."
              autocomplete="off"
              @keyup.enter="searchCourriers"
            />
          </div>
        </div>

        <!-- Priorité -->
        <div class="form-group">
          <label for="filter-priorite">Priorité</label>

          <select
            id="filter-priorite"
            v-model="filters.priorite"
            class="form-control"
            @change="searchCourriers"
          >
            <option value="">Toutes les priorités</option>
            <option value="NORMAL">Normal</option>
            <option value="URGENT">Urgent</option>
            <option value="TRES_URGENT">Très urgent</option>
          </select>
        </div>

        <!-- Statut -->
        <div class="form-group">
          <label for="filter-statut">Statut</label>

          <select
            id="filter-statut"
            v-model="filters.statut_dossier"
            class="form-control"
            @change="searchCourriers"
          >
            <option value="">Tous les statuts</option>
            <option value="En cours">En cours</option>
            <option value="Lecture">Lecture</option>
            <option value="Archivé">Archivé</option>
          </select>
        </div>

        <!-- Date début -->
        <div class="form-group">
          <label for="date-debut">Date début</label>

          <input
            id="date-debut"
            v-model="filters.date_debut"
            type="date"
            class="form-control"
          />
        </div>

        <!-- Date fin -->
        <div class="form-group">
          <label for="date-fin">Date fin</label>

          <input
            id="date-fin"
            v-model="filters.date_fin"
            type="date"
            class="form-control"
          />
        </div>

        <div class="filter-action">
          <button
            type="submit"
            class="btn btn-primary btn-search"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner spinner-small"></span>
            <span v-else class="btn-icon">⌕</span>
            <span>{{ loading ? 'Recherche...' : 'Rechercher' }}</span>
          </button>
        </div>
      </form>
    </section>

    <!-- ============================================================
         TABLEAU
    ============================================================ -->
    <section class="table-card">
      <div class="table-header">
        <div>
          <h2>Liste des courriers</h2>
          <p>
            {{ pagination.total }}
            courrier{{ pagination.total > 1 ? 's' : '' }}
          </p>
        </div>

        <div class="per-page">
          <label for="per-page">Afficher</label>

          <select
            id="per-page"
            v-model.number="pagination.perPage"
            @change="changePerPage"
            :disabled="loading"
          >
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>
      </div>

      <!-- Loading desktop -->
      <div v-if="loading" class="table-loading">
        <div
          v-for="n in 6"
          :key="n"
          class="skeleton-row"
        >
          <span></span>
          <span></span>
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>

      <!-- Desktop/tablette -->
      <div
        v-else-if="courriers.length > 0"
        class="table-wrapper desktop-table"
      >
        <table>
          <thead>
            <tr>
              <th>N°</th>
              <th>Date</th>
              <th>N° origine</th>
              <th>Origine</th>
              <th>Objet</th>
              <th>Pièce de suite</th>
              <th>Priorité</th>
              <th>Statut</th>
              <th>Documents</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="courrier in courriers"
              :key="courrier.num_enreg_courr_arr"
            >
              <!-- N° -->
              <td>
                <strong class="number-cell">
                  {{ courrier.num_enreg_courr_arr }}
                </strong>
              </td>

              <!-- Date -->
              <td class="date-cell">
                {{ formatDate(courrier.date_enreg) }}
              </td>

              <!-- Numéro origine -->
              <td>
                <span class="origin-number">
                  {{ courrier.num_ordre_orig || '—' }}
                </span>
              </td>

              <!-- Origine -->
              <td>
                <span class="origin-name">
                  {{ courrier.lib_orig || '—' }}
                </span>
              </td>

              <!-- Objet -->
              <td class="object-cell">
                <span
                  :title="courrier.objet_courr_arri"
                >
                  {{ truncate(courrier.objet_courr_arri, 70) }}
                </span>
              </td>

              <!-- Pièce -->
              <td>
                <span class="piece-badge">
                  {{ getPieceName(courrier.id_piece_suit) }}
                </span>
              </td>

              <!-- Priorité -->
              <td>
                <span
                  class="badge priority-badge"
                  :class="priorityClass(courrier.priorite)"
                >
                  <span class="badge-dot"></span>
                  {{ priorityLabel(courrier.priorite) }}
                </span>
              </td>

              <!-- Statut -->
              <td>
                <span
                  class="badge status-badge"
                  :class="statusClass(courrier.statut_dossier)"
                >
                  <span class="status-icon">
                    {{ statusIcon(courrier.statut_dossier) }}
                  </span>
                  {{ courrier.statut_dossier || 'En cours' }}
                </span>
              </td>

              <!-- Documents -->
              <td>
                <button
                  v-if="canViewCourriers"
                  type="button"
                  class="documents-count"
                  @click="openDetailModal(courrier)"
                  :aria-label="`Voir les documents du courrier ${courrier.num_enreg_courr_arr}`"
                >
                  <Paperclip :size="15" />
                  {{ getDocumentCount(courrier) }}
                </button>
              </td>

              <!-- Actions -->
              <td>
                <div class="action-buttons">
                  <button
                    v-if="canViewCourriers"
                    type="button"
                    class="icon-button view"
                    title="Voir le courrier"
                    aria-label="Voir le courrier"
                    @click="openDetailModal(courrier)"
                  >
                    <Eye :size="17" />
                  </button>

                  <button
                    v-if="canUpdateCourriers"
                    type="button"
                    class="icon-button edit"
                    title="Modifier le courrier"
                    aria-label="Modifier le courrier"
                    @click="openEditModal(courrier)"
                  >
                    <Pencil :size="17" />
                  </button>

                  <button
                    v-if="canDeleteCourriers"
                    type="button"
                    class="icon-button delete"
                    title="Supprimer le courrier"
                    aria-label="Supprimer le courrier"
                    @click="askDeleteCourrier(courrier)"
                  >
                    <Trash2 :size="17" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ============================================================
           MOBILE : CARDS
      ============================================================ -->
      <div
        v-else-if="courriers.length > 0"
        class="mobile-cards"
      >
        <article
          v-for="courrier in courriers"
          :key="courrier.num_enreg_courr_arr"
          class="courrier-mobile-card"
        >
          <div class="mobile-card-header">
            <div>
              <span class="mobile-number">
                N° {{ courrier.num_enreg_courr_arr }}
              </span>

              <span class="mobile-date">
                {{ formatDate(courrier.date_enreg) }}
              </span>
            </div>

            <span
              class="badge priority-badge"
              :class="priorityClass(courrier.priorite)"
            >
              {{ priorityLabel(courrier.priorite) }}
            </span>
          </div>

          <div class="mobile-card-content">
            <div class="mobile-info">
              <span class="mobile-label">N° origine</span>
              <strong>{{ courrier.num_ordre_orig || '—' }}</strong>
            </div>

            <div class="mobile-info">
              <span class="mobile-label">Origine</span>
              <strong>{{ courrier.lib_orig || '—' }}</strong>
            </div>

            <div class="mobile-info mobile-object">
              <span class="mobile-label">Objet</span>
              <strong>{{ courrier.objet_courr_arri || '—' }}</strong>
            </div>

            <div class="mobile-badges">
              <span class="piece-badge">
                {{ getPieceName(courrier.id_piece_suit) }}
              </span>

              <span
                class="badge status-badge"
                :class="statusClass(courrier.statut_dossier)"
              >
                {{ statusIcon(courrier.statut_dossier) }}
                {{ courrier.statut_dossier }}
              </span>

              <button
                v-if="canViewCourriers"
                type="button"
                class="documents-count"
                @click="openDetailModal(courrier)"
              >
                ▣ {{ getDocumentCount(courrier) }}
              </button>
            </div>
          </div>

          <div class="mobile-card-actions">
            <button
              v-if="canViewCourriers"
              type="button"
              class="btn btn-outline btn-small"
              @click="openDetailModal(courrier)"
            >
              <span>◉</span>
              Voir
            </button>

            <button
              v-if="canUpdateCourriers"
              type="button"
              class="btn btn-outline btn-small"
              @click="openEditModal(courrier)"
            >
              <span>✎</span>
              Modifier
            </button>

            <button
              v-if="canDeleteCourriers"
              type="button"
              class="btn btn-danger-light btn-small"
              @click="askDeleteCourrier(courrier)"
            >
              <span>♲</span>
              Supprimer
            </button>
          </div>
        </article>
      </div>

      <!-- Empty -->
      <div
        v-if="!loading && courriers.length === 0"
        class="empty-state"
      >
        <div class="empty-icon">✉</div>

        <h3>Aucun courrier arrivé</h3>

        <p>
          Aucun courrier ne correspond aux critères sélectionnés.
        </p>

        <button
          v-if="canCreateCourriers"
          type="button"
          class="btn btn-primary"
          @click="openCreateModal"
        >
          <span class="btn-icon">＋</span>
          <span>Ajouter un courrier</span>
        </button>
      </div>

      <!-- ============================================================
           PAGINATION
      ============================================================ -->
      <div
        v-if="!loading && courriers.length > 0 && pagination.lastPage > 1"
        class="pagination"
      >
        <div class="pagination-info">
          Page {{ pagination.currentPage }}
          sur {{ pagination.lastPage }}
        </div>

        <div class="pagination-buttons">
          <button
            type="button"
            class="pagination-button"
            :disabled="pagination.currentPage <= 1 || loading"
            @click="goToPage(pagination.currentPage - 1)"
            aria-label="Page précédente"
          >
            ‹
          </button>

          <button
            v-for="page in visiblePages"
            :key="page"
            type="button"
            class="pagination-button"
            :class="{ active: page === pagination.currentPage }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>

          <button
            type="button"
            class="pagination-button"
            :disabled="
              pagination.currentPage >= pagination.lastPage || loading
            "
            @click="goToPage(pagination.currentPage + 1)"
            aria-label="Page suivante"
          >
            ›
          </button>
        </div>
      </div>
    </section>

    <!-- ============================================================
         MODAL : NOUVEAU / MODIFIER
    ============================================================ -->
    <div
      v-if="showFormModal"
      class="modal-overlay"
      @click.self="closeFormModal"
    >
      <div
        class="modal modal-large"
        role="dialog"
        aria-modal="true"
        aria-labelledby="courrier-form-title"
      >
        <!-- Modal header -->
        <div class="modal-header">
          <div class="modal-title-wrapper">
            <div class="modal-title-icon">
              <span>{{ isEditing ? '✎' : '＋' }}</span>
            </div>

            <div>
              <h2 id="courrier-form-title">
                {{ isEditing ? 'Modifier le courrier' : 'Nouveau courrier' }}
              </h2>

              <p>
                {{
                  isEditing
                    ? 'Modifier les informations du courrier arrivé'
                    : 'Enregistrer un nouveau courrier arrivé'
                }}
              </p>
            </div>
          </div>

          <button
            type="button"
            class="modal-close"
            @click="closeFormModal"
            :disabled="saving"
            aria-label="Fermer"
          >
            ×
          </button>
        </div>

        <!-- Modal body -->
        <form
          class="modal-body"
          @submit.prevent="saveCourrier"
        >
          <!-- Informations générées -->
          <div
            v-if="isEditing"
            class="system-info"
          >
            <div class="system-info-item">
              <span class="system-info-label">
                Numéro d'enregistrement
              </span>
              <strong>
                {{ form.num_enreg_courr_arr || '—' }}
              </strong>
            </div>

            <div class="system-info-item">
              <span class="system-info-label">
                Date d'enregistrement
              </span>
              <strong>
                {{ formatDate(form.date_enreg) }}
              </strong>
            </div>
          </div>

          <!-- Grille -->
          <div class="form-grid">
            <!-- Numéro origine -->
            <div class="form-group">
              <label for="num-ordre-orig">
                Numéro origine
                <span class="required">*</span>
              </label>

              <input
                id="num-ordre-orig"
                v-model="form.num_ordre_orig"
                type="text"
                class="form-control"
                :class="{
                  'input-error': errors.num_ordre_orig
                }"
                maxlength="100"
                placeholder="Ex. 125/SEMF/2026"
                autocomplete="off"
                required
              />

              <div
                v-if="errors.num_ordre_orig"
                class="field-error"
              >
                {{ errors.num_ordre_orig }}
              </div>
            </div>

            <!-- Origine -->
            <div class="form-group">
              <label for="lib-orig">
                Origine
                <span class="required">*</span>
              </label>

              <input
                id="lib-orig"
                v-model="form.lib_orig"
                type="text"
                class="form-control"
                :class="{
                  'input-error': errors.lib_orig
                }"
                maxlength="255"
                placeholder="Ex. Direction Générale"
                autocomplete="organization"
                required
              />

              <div
                v-if="errors.lib_orig"
                class="field-error"
              >
                {{ errors.lib_orig }}
              </div>
            </div>

            <!-- Pièce de suite -->
            <div class="form-group">
              <label for="piece-suite">
                Pièce de suite
                <span class="required">*</span>
              </label>

              <div class="piece-select-wrapper">
                <select
                  id="piece-suite"
                  v-model="form.id_piece_suit"
                  class="form-control"
                  :class="{
                    'input-error': errors.id_piece_suit
                  }"
                  required
                >
                  <option value="">
                    Sélectionner une pièce
                  </option>

                  <option
                    v-for="piece in piecesSuite"
                    :key="pieceId(piece)"
                    :value="pieceId(piece)"
                  >
                    {{ pieceLabel(piece) }}
                  </option>
                </select>

                <button
                  v-if="canCreateCourriers"
                  type="button"
                  class="btn btn-add-piece"
                  @click="openPieceModal"
                  :disabled="saving"
                  title="Ajouter une pièce de suite"
                >
                  <span>＋</span>
                  <span>Ajouter</span>
                </button>
              </div>

              <div
                v-if="errors.id_piece_suit"
                class="field-error"
              >
                {{ errors.id_piece_suit }}
              </div>
            </div>

            <!-- Utilisateur -->
            <div class="form-group">
              <label>
                Utilisateur créateur
              </label>

              <div class="user-display">
                <div class="user-avatar">
                  {{ currentUserInitials }}
                </div>

                <div>
                  <strong>{{ currentUserName }}</strong>
                  <span>
                    Création automatique
                  </span>
                </div>

                <span class="locked-icon">🔒︎</span>
              </div>

              <small class="field-help">
                Ce champ est renseigné automatiquement.
              </small>
            </div>
          </div>

          <!-- Objet -->
          <div class="form-group">
            <div class="label-row">
              <label for="objet-courrier">
                Objet du courrier
                <span class="required">*</span>
              </label>

              <span
                class="character-counter"
                :class="{
                  warning: form.objet_courr_arri.length > 900
                }"
              >
                {{ form.objet_courr_arri.length }}/1000
              </span>
            </div>

            <textarea
              id="objet-courrier"
              v-model="form.objet_courr_arri"
              class="form-control textarea"
              :class="{
                'input-error': errors.objet_courr_arri
              }"
              maxlength="1000"
              rows="4"
              placeholder="Saisir l'objet du courrier..."
              required
            ></textarea>

            <div
              v-if="errors.objet_courr_arri"
              class="field-error"
            >
              {{ errors.objet_courr_arri }}
            </div>
          </div>

          <!-- ========================================================
               PRIORITÉ
          ======================================================== -->
          <div class="form-section">
            <div class="section-heading">
              <div class="section-icon">!</div>

              <div>
                <h3>Priorité</h3>
                <p>Sélectionner le niveau de priorité du courrier</p>
              </div>
            </div>

            <div class="choice-grid priority-grid">
              <!-- NORMAL -->
              <button
                type="button"
                class="choice-card priority-normal"
                :class="{
                  selected: form.priorite === 'NORMAL'
                }"
                @click="form.priorite = 'NORMAL'"
                :aria-pressed="form.priorite === 'NORMAL'"
              >
                <span class="choice-icon">●</span>

                <span class="choice-content">
                  <strong>Normal</strong>
                  <small>Traitement standard</small>
                </span>

                <span
                  v-if="form.priorite === 'NORMAL'"
                  class="choice-check"
                >
                  ✓
                </span>
              </button>

              <!-- URGENT -->
              <button
                type="button"
                class="choice-card priority-urgent"
                :class="{
                  selected: form.priorite === 'URGENT'
                }"
                @click="form.priorite = 'URGENT'"
                :aria-pressed="form.priorite === 'URGENT'"
              >
                <span class="choice-icon">▲</span>

                <span class="choice-content">
                  <strong>Urgent</strong>
                  <small>Traitement prioritaire</small>
                </span>

                <span
                  v-if="form.priorite === 'URGENT'"
                  class="choice-check"
                >
                  ✓
                </span>
              </button>

              <!-- TRÈS URGENT -->
              <button
                type="button"
                class="choice-card priority-very-urgent"
                :class="{
                  selected: form.priorite === 'TRES_URGENT'
                }"
                @click="form.priorite = 'TRES_URGENT'"
                :aria-pressed="form.priorite === 'TRES_URGENT'"
              >
                <span class="choice-icon">‼</span>

                <span class="choice-content">
                  <strong>Très urgent</strong>
                  <small>Traitement immédiat</small>
                </span>

                <span
                  v-if="form.priorite === 'TRES_URGENT'"
                  class="choice-check"
                >
                  ✓
                </span>
              </button>
            </div>
          </div>

          <!-- ========================================================
               STATUT
          ======================================================== -->
          <div class="form-section">
            <div class="section-heading">
              <div class="section-icon">◉</div>

              <div>
                <h3>Statut du dossier</h3>
                <p>Définir l'état actuel du courrier</p>
              </div>
            </div>

            <div class="choice-grid status-grid">
              <!-- EN COURS -->
              <button
                type="button"
                class="choice-card status-progress"
                :class="{
                  selected: form.statut_dossier === 'En cours'
                }"
                @click="form.statut_dossier = 'En cours'"
                :aria-pressed="form.statut_dossier === 'En cours'"
              >
                <span class="choice-icon">◷</span>

                <span class="choice-content">
                  <strong>En cours</strong>
                  <small>Traitement en cours</small>
                </span>

                <span
                  v-if="form.statut_dossier === 'En cours'"
                  class="choice-check"
                >
                  ✓
                </span>
              </button>

              <!-- LECTURE -->
              <button
                type="button"
                class="choice-card status-reading"
                :class="{
                  selected: form.statut_dossier === 'Lecture'
                }"
                @click="form.statut_dossier = 'Lecture'"
                :aria-pressed="form.statut_dossier === 'Lecture'"
              >
                <span class="choice-icon">◉</span>

                <span class="choice-content">
                  <strong>Lecture</strong>
                  <small>En cours de consultation</small>
                </span>

                <span
                  v-if="form.statut_dossier === 'Lecture'"
                  class="choice-check"
                >
                  ✓
                </span>
              </button>

              <!-- ARCHIVÉ -->
              <button
                type="button"
                class="choice-card status-archived"
                :class="{
                  selected: form.statut_dossier === 'Archivé'
                }"
                @click="form.statut_dossier = 'Archivé'"
                :aria-pressed="form.statut_dossier === 'Archivé'"
              >
                <span class="choice-icon">⛁</span>

                <span class="choice-content">
                  <strong>Archivé</strong>
                  <small>Classé et archivé</small>
                </span>

                <span
                  v-if="form.statut_dossier === 'Archivé'"
                  class="choice-check"
                >
                  ✓
                </span>
              </button>
            </div>
          </div>

          <!-- ========================================================
               DOCUMENTS
          ======================================================== -->
          <div class="form-section">
            <div class="section-heading">
              <div class="section-icon">🗎</div>

              <div>
                <h3>Documents numériques</h3>
                <p>
                  Ajouter un ou plusieurs documents au courrier
                </p>
              </div>
            </div>

            <!-- Dropzone -->
            <div
              v-if="canCreateCourriers"
              class="dropzone"
              :class="{ dragging: isDragging }"
              @dragenter.prevent="isDragging = true"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              @click="openFilePicker"
            >
              <input
                ref="fileInput"
                type="file"
                class="hidden-file-input"
                multiple
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                @change="handleFileSelect"
              />

              <div class="dropzone-icon">⇧</div>

              <strong>
                Glisser-déposer vos fichiers ici
              </strong>

              <span>
                ou
              </span>

              <button
                type="button"
                class="dropzone-button"
                @click.stop="openFilePicker"
              >
                <span>＋</span>
                Sélectionner des fichiers
              </button>

              <small>
                PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX
              </small>
            </div>

            <!-- Erreur fichiers -->
            <div
              v-if="fileError"
              class="file-error"
            >
              <span>⚠</span>
              {{ fileError }}
            </div>

            <!-- Fichiers sélectionnés -->
            <div
              v-if="selectedFiles.length > 0"
              class="selected-files"
            >
              <div class="files-title">
                <strong>
                  Fichiers sélectionnés
                </strong>

                <span>
                  {{ selectedFiles.length }}
                </span>
              </div>

              <div class="files-list">
                <div
                  v-for="(item, index) in selectedFiles"
                  :key="item.key"
                  class="file-item"
                >
                  <div
                    class="file-icon"
                    :class="fileIconClass(item.file.name)"
                  >
                    {{ fileIcon(item.file.name) }}
                  </div>

                  <div class="file-info">
                    <strong :title="item.file.name">
                      {{ item.file.name }}
                    </strong>

                    <span>
                      {{ formatFileSize(item.file.size) }}
                      ·
                      {{ getExtension(item.file.name).toUpperCase() }}
                    </span>
                  </div>

                  <button
                    type="button"
                    class="remove-file"
                    @click="removeSelectedFile(index)"
                    :disabled="saving"
                    title="Retirer le fichier"
                    aria-label="Retirer le fichier"
                  >
                    ×
                  </button>
                </div>
              </div>
            </div>

            <div class="document-help">
              Taille maximale recommandée : 10 Mo par fichier.
            </div>
          </div>

          <!-- Erreur générale -->
          <div
            v-if="formError"
            class="form-error-box"
          >
            <span>⚠</span>
            <div>
              <strong>Impossible d'enregistrer</strong>
              <p>{{ formError }}</p>
            </div>
          </div>

          <!-- Modal footer -->
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-light"
              @click="closeFormModal"
              :disabled="saving"
            >
              <span>×</span>
              <span>Annuler</span>
            </button>

            <button
              type="submit"
              class="btn btn-primary btn-save"
              :disabled="saving"
            >
              <span
                v-if="saving"
                class="spinner spinner-small"
              ></span>

              <span v-else>✓</span>

              <span>
                {{
                  saving
                    ? 'Enregistrement...'
                    : isEditing
                      ? 'Enregistrer les modifications'
                      : 'Enregistrer le courrier'
                }}
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ============================================================
        MODAL : GESTION PIÈCES DE SUITE
    ============================================================ -->

    <div
      v-if="showPieceModal"
      class="modal-overlay"
      @click.self="closePieceModal"
    >
      <div
        class="modal modal-piece-manager"
        role="dialog"
        aria-modal="true"
        aria-labelledby="piece-modal-title"
      >

        <!-- ================= HEADER ================= -->

        <div class="modal-header">

          <div class="modal-title-wrapper">

            <div class="modal-title-icon">
              <span>📄</span>
            </div>

            <div>
              <h2 id="piece-modal-title">
                Gérer les pièces de suite
              </h2>

              <p>
                Ajouter ou supprimer une pièce de suite
              </p>
            </div>

          </div>

          <button
            type="button"
            class="modal-close"
            @click="closePieceModal"
            :disabled="savingPiece"
            aria-label="Fermer"
          >
            ×
          </button>

        </div>


        <!-- ================= BODY ================= -->

        <div class="modal-body piece-manager-body">


          <!-- ================= AJOUT ================= -->

          <section class="piece-add-section">

            <div class="piece-section-title">

              <div class="piece-section-icon">
                ＋
              </div>

              <div>
                <h3>
                  Nouvelle pièce
                </h3>

                <p>
                  Ajouter une nouvelle pièce de suite
                </p>
              </div>

            </div>


            <form
              @submit.prevent="createPieceSuite"
            >

              <div class="form-group">

                <label for="piece-name">

                  Nom de la pièce

                  <span class="required">
                    *
                  </span>

                </label>


                <div class="piece-input-row">

                  <input
                    id="piece-name"
                    v-model="newPieceName"
                    type="text"
                    class="form-control"
                    maxlength="255"
                    placeholder="Ex. Bordereau d'envoi"
                    :disabled="savingPiece"
                    required
                  />


                  <button
                    type="submit"
                    class="btn btn-primary btn-piece-add"
                    :disabled="savingPiece"
                  >

                    <span
                      v-if="savingPiece"
                      class="spinner spinner-small"
                    ></span>

                    <span v-else>
                      ＋
                    </span>

                    <span>
                      {{
                        savingPiece
                          ? 'Ajout...'
                          : 'Ajouter'
                      }}
                    </span>

                  </button>

                </div>


                <div
                  v-if="pieceError"
                  class="field-error"
                >
                  {{ pieceError }}
                </div>

              </div>

            </form>

          </section>


          <!-- ================= SEPARATEUR ================= -->

          <div class="piece-divider"></div>


          <!-- ================= LISTE ================= -->

          <section class="piece-list-section">


            <div class="piece-list-header">

              <div>

                <h3>
                  Pièces existantes
                </h3>

                <p>
                  {{ piecesSuite.length }}
                  pièce(s) disponible(s)
                </p>

              </div>


              <button
                type="button"
                class="btn-refresh-piece"
                @click="loadPiecesSuite"
                :disabled="savingPiece || deletingPieceId"
                title="Actualiser la liste"
              >
                ↻
              </button>

            </div>


            <!-- EMPTY -->

            <div
              v-if="!piecesSuite.length"
              class="piece-empty"
            >

              <div class="piece-empty-icon">
                📄
              </div>

              <p>
                Aucune pièce de suite disponible.
              </p>

            </div>


            <!-- LISTE -->

            <div
              v-else
              class="piece-list"
            >

              <div
                v-for="piece in piecesSuite"
                :key="pieceId(piece)"
                class="piece-list-item"
              >


                <!-- NOM -->

                <div class="piece-info">

                  <div class="piece-document-icon">
                    📄
                  </div>


                  <div class="piece-name-wrapper">

                    <span class="piece-name">
                      {{ pieceLabel(piece) }}
                    </span>

                    <span class="piece-id">
                      ID #{{ pieceId(piece) }}
                    </span>

                  </div>

                </div>


                <!-- ACTION -->

                <button
                  v-if="canDeleteCourriers"
                  type="button"
                  class="btn-delete-piece"
                  @click="openDeletePieceModal(piece)"
                  :disabled="deletingPieceId === pieceId(piece)"
                  title="Supprimer cette pièce"
                  aria-label="Supprimer"
                >

                  <span
                    v-if="
                      deletingPieceId ===
                      pieceId(piece)
                    "
                    class="spinner spinner-small"
                  ></span>


                  <span v-else>
                    🗑
                  </span>

                </button>


              </div>

            </div>


          </section>


        </div>


        <!-- ================= FOOTER ================= -->

        <div class="modal-footer">

          <button
            type="button"
            class="btn btn-light"
            @click="closePieceModal"
            :disabled="savingPiece || deletingPieceId"
          >

            <span>
              ×
            </span>

            Fermer

          </button>

        </div>


      </div>

    </div>


        <!-- ============================================================
        MODAL : CONFIRMATION SUPPRESSION PIÈCE
    ============================================================ -->

    <div
      v-if="showDeletePieceModal"
      class="modal-overlay modal-delete-overlay"
      @click.self="closeDeletePieceModal"
    >

      <div
        class="modal modal-delete-piece"
        role="dialog"
        aria-modal="true"
      >


        <!-- HEADER -->

        <div class="delete-piece-header">

          <div class="delete-warning-icon">
            ⚠
          </div>

          <h2>
            Supprimer cette pièce ?
          </h2>

          <p>
            Cette action est irréversible.
          </p>

        </div>


        <!-- BODY -->

        <div class="delete-piece-body">

          <p>
            Vous êtes sur le point de supprimer :
          </p>


          <div class="delete-piece-name">

            <span>
              📄
            </span>

            <strong>
              {{
                pieceToDelete
                  ? pieceLabel(pieceToDelete)
                  : ''
              }}
            </strong>

          </div>


          <div
            v-if="deletePieceError"
            class="delete-piece-error"
          >

            <span>
              ⚠
            </span>

            {{ deletePieceError }}

          </div>


          <p class="delete-piece-warning">

            Si cette pièce est déjà utilisée
            par un courrier arrivé, sa suppression
            sera refusée.

          </p>

        </div>


        <!-- FOOTER -->

        <div class="modal-footer">

          <button
            type="button"
            class="btn btn-light"
            @click="closeDeletePieceModal"
            :disabled="deletingPieceId"
          >

            Annuler

          </button>


          <button
            type="button"
            class="btn btn-danger"
            @click="deletePieceSuite"
            :disabled="deletingPieceId"
          >


            <span
              v-if="deletingPieceId"
              class="spinner spinner-small"
            ></span>


            <span v-else>
              🗑
            </span>


            <span>
              {{
                deletingPieceId
                  ? 'Suppression...'
                  : 'Supprimer'
              }}
            </span>


          </button>

        </div>


      </div>

    </div>

    <!-- ============================================================
         MODAL : DÉTAIL
    ============================================================ -->
    <div
      v-if="showDetailModal"
      class="modal-overlay"
      @click.self="closeDetailModal"
    >
      <div
        class="modal modal-large"
        role="dialog"
        aria-modal="true"
        aria-labelledby="detail-modal-title"
      >
        <div class="modal-header">
          <div class="modal-title-wrapper">
            <div class="modal-title-icon">
              <span>📃</span>
            </div>

            <div>
              <h2 id="detail-modal-title">
                Détails du courrier
              </h2>

              <p>
                Informations complètes du courrier arrivé
              </p>
            </div>
          </div>

          <button
            type="button"
            class="modal-close"
            @click="closeDetailModal"
            aria-label="Fermer"
          >
            ×
          </button>
        </div>

        <div
          v-if="selectedCourrier"
          class="modal-body detail-body"
        >
          <!-- Bandeau -->
          <div class="detail-banner">
            <div>
              <span class="detail-banner-label">
                Numéro d'enregistrement
              </span>

              <strong>
                {{ selectedCourrier.num_enreg_courr_arr }}
              </strong>
            </div>

            <div class="detail-banner-right">
              <span
                class="badge priority-badge"
                :class="
                  priorityClass(selectedCourrier.priorite)
                "
              >
                {{ priorityLabel(selectedCourrier.priorite) }}
              </span>

              <span
                class="badge status-badge"
                :class="
                  statusClass(selectedCourrier.statut_dossier)
                "
              >
                {{ statusIcon(selectedCourrier.statut_dossier) }}
                {{ selectedCourrier.statut_dossier }}
              </span>
            </div>
          </div>

          <!-- Informations -->
          <div class="detail-grid">
            <div class="detail-item">
              <span>N° origine</span>
              <strong>
                {{ selectedCourrier.num_ordre_orig || '—' }}
              </strong>
            </div>

            <div class="detail-item">
              <span>Date d'enregistrement</span>
              <strong>
                {{ formatDate(selectedCourrier.date_enreg) }}
              </strong>
            </div>

            <div class="detail-item">
              <span>Origine</span>
              <strong>
                {{ selectedCourrier.lib_orig || '—' }}
              </strong>
            </div>

            <div class="detail-item">
              <span>Pièce de suite</span>
              <strong>
                {{
                  getPieceName(selectedCourrier.id_piece_suit)
                }}
              </strong>
            </div>
          </div>

          <!-- Objet -->
          <div class="detail-object">
            <span>Objet du courrier</span>
            <p>
              {{ selectedCourrier.objet_courr_arri || '—' }}
            </p>
          </div>

          <!-- Documents -->
          <div class="documents-section">
            <div class="documents-section-header">
              <div>
                <h3>Documents numériques</h3>
                <p>
                  {{ detailDocuments.length }}
                  document{{
                    detailDocuments.length > 1 ? 's' : ''
                  }}
                </p>
              </div>

              <button
                v-if="canCreateCourriers"
                type="button"
                class="btn btn-primary btn-small"
                @click="addDocumentsToCurrent"
              >
                <span>＋</span>
                Ajouter
              </button>
            </div>

            <!-- Loading docs -->
            <div
              v-if="loadingDocuments"
              class="documents-loading"
            >
              <span class="spinner"></span>
              Chargement des documents...
            </div>

            <!-- Documents -->
            <div
              v-else-if="detailDocuments.length > 0"
              class="detail-documents-list"
            >
              <div
                v-for="document in detailDocuments"
                :key="documentId(document)"
                class="detail-document-item"
              >
                <div
                  class="file-icon"
                  :class="
                    fileIconClass(documentName(document))
                  "
                >
                  {{ fileIcon(documentName(document)) }}
                </div>

                <div class="file-info">
                  <strong
                    :title="documentName(document)"
                  >
                    {{ documentName(document) }}
                  </strong>

                  <span>
                    {{ formatDocumentType(document) }}
                    <template
                      v-if="documentSize(document)"
                    >
                      ·
                      {{ formatFileSize(documentSize(document)) }}
                    </template>
                  </span>
                </div>

                <div class="document-actions">
                  <button
                      v-if="canViewDocuments"
                      type="button"
                      class="icon-button view"
                      title="Afficher le document"
                      aria-label="Afficher le document"
                      @click="previewDocument(document)"
                  >
                    <Eye :size="17" />
                  </button>

                  <button
                    v-if="canDownloadDocuments"
                    type="button"
                    class="icon-button download"
                    title="Télécharger"
                    aria-label="Télécharger le document"
                    @click="downloadDocument(document)"
                  >
                    <Download :size="16" />
                  </button>

                  <button
                    v-if="canDeleteCourriers"
                    type="button"
                    class="icon-button delete"
                    title="Supprimer"
                    aria-label="Supprimer le document"
                    @click="askDeleteDocument(document)"
                  >
                    <Trash2 :size="17" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty documents -->
            <div
              v-else
              class="documents-empty"
            >
              <div>▣</div>
              <strong>Aucun document</strong>
              <p>
                Aucun document numérique n'est associé à ce courrier.
              </p>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-light"
              @click="closeDetailModal"
            >
              <span>×</span>
              Fermer
            </button>

            <button
              v-if="canUpdateCourriers"
              type="button"
              class="btn btn-primary"
              @click="editFromDetail"
            >
              <span>✎</span>
              Modifier
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================
     MODAL : PRÉVISUALISATION DOCUMENT
============================================================ -->
    <div
      v-if="showDocumentPreview"
      class="document-preview-overlay"
      @click.self="closeDocumentPreview"
    >
      <div
        class="document-preview-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="document-preview-title"
      >

        <!-- ========================================================
            HEADER
        ========================================================= -->
        <div class="document-preview-header">

          <div class="document-preview-title-wrapper">

            <div
              class="document-preview-title-icon"
              :class="
                previewDocumentData
                  ? fileIconClass(
                      documentName(
                        previewDocumentData
                      )
                    )
                  : ''
              "
            >
              {{
                previewDocumentData
                  ? fileIcon(
                      documentName(
                        previewDocumentData
                      )
                    )
                  : 'FILE'
              }}
            </div>

            <div class="document-preview-title-content">

              <h2 id="document-preview-title">
                Affichage du document
              </h2>

              <p
                :title="
                  previewDocumentData
                    ? documentName(
                        previewDocumentData
                      )
                    : ''
                "
              >
                {{
                  previewDocumentData
                    ? documentName(
                        previewDocumentData
                      )
                    : 'Document'
                }}
              </p>

            </div>

          </div>

          <!-- X -->
          <button
            type="button"
            class="document-preview-close"
            title="Fermer"
            aria-label="Fermer"
            @click="closeDocumentPreview"
          >
            ×
          </button>

        </div>

        <!-- ========================================================
            BODY
        ========================================================= -->
        <div class="document-preview-body">

          <!-- Chargement -->
          <div
            v-if="loadingPreview"
            class="document-preview-loading"
          >
            <span class="spinner"></span>

            <strong>
              Chargement du document...
            </strong>
          </div>

          <!-- PDF -->
          <iframe
            v-else-if="
              previewDocumentData &&
              (
                previewDocumentData.type_mime ===
                  'application/pdf' ||
                /\.pdf$/i.test(
                  documentName(
                    previewDocumentData
                  )
                )
              )
            "
            :src="previewDocumentUrl"
            class="document-preview-frame"
            title="Prévisualisation PDF"
          ></iframe>

          <!-- IMAGE -->
          <div
            v-else-if="
              previewDocumentData &&
              (
                previewDocumentData.type_mime?.startsWith(
                  'image/'
                ) ||
                /\.(jpg|jpeg|png)$/i.test(
                  documentName(
                    previewDocumentData
                  )
                )
              )
            "
            class="document-preview-image-container"
          >

            <img
              :src="previewDocumentUrl"
              :alt="
                documentName(
                  previewDocumentData
                )
              "
              class="document-preview-image"
            />

          </div>

          <!-- DOC / XLS / AUTRES -->
          <div
            v-else
            class="document-preview-unsupported"
          >

            <div
              class="unsupported-file-icon"
              :class="
                previewDocumentData
                  ? fileIconClass(
                      documentName(
                        previewDocumentData
                      )
                    )
                  : ''
              "
            >
              {{
                previewDocumentData
                  ? fileIcon(
                      documentName(
                        previewDocumentData
                      )
                    )
                  : 'FILE'
              }}
            </div>

            <h3>
              Prévisualisation non disponible
            </h3>

            <p>
              Ce format de document ne peut pas être
              affiché directement dans le navigateur.
            </p>

            <strong
              v-if="previewDocumentData"
              class="unsupported-file-name"
            >
              {{
                documentName(
                  previewDocumentData
                )
              }}
            </strong>

          </div>

        </div>

        <!-- ========================================================
            FOOTER
        ========================================================= -->
        <div class="document-preview-footer">

          <div class="document-preview-info">

            <span class="preview-info-label">
              Document
            </span>

            <strong
              :title="
                previewDocumentData
                  ? documentName(
                      previewDocumentData
                    )
                  : ''
              "
            >
              {{
                previewDocumentData
                  ? documentName(
                      previewDocumentData
                    )
                  : 'Document'
              }}
            </strong>

          </div>

          <div class="document-preview-actions">

            <!-- Télécharger -->
            <button
              v-if="canDownloadDocuments"
              type="button"
              class="btn btn-primary"
              :disabled="!previewDocumentData"
              @click="
                downloadDocument(
                  previewDocumentData
                )
              "
            >
              <span><Download :size="16" /></span>
              <span>Télécharger</span>
            </button>

            <!-- Supprimer -->
            <button
              v-if="canDeleteCourriers"
              type="button"
              class="btn btn-danger"
              :disabled="!previewDocumentData"
              @click="deletePreviewDocument(
                previewDocumentData
              )"
            >
              <span><Trash2 :size="17" /></span>
              <span>Supprimer</span>
            </button>

            <!-- Fermer -->
            <button
              type="button"
              class="btn btn-light"
              @click="closeDocumentPreview"
            >
              <span>×</span>
              <span>Fermer</span>
            </button>

          </div>

        </div>

      </div>
    </div>

    <!-- ============================================================
         MODAL : CONFIRMATION
    ============================================================ -->
    <div
      v-if="showConfirmModal"
      class="modal-overlay confirm-overlay"
      @click.self="closeConfirmModal"
    >
      <div
        class="modal modal-confirm"
        role="alertdialog"
        aria-modal="true"
      >
        <div class="confirm-icon">
          {{ confirmType === 'delete' ? '!' : '⚠' }}
        </div>

        <h2>{{ confirmTitle }}</h2>

        <p>
          {{ confirmMessage }}
        </p>

        <div class="confirm-actions">
          <button
            type="button"
            class="btn btn-light"
            @click="closeConfirmModal"
            :disabled="confirmLoading"
          >
            <span>×</span>
            Annuler
          </button>

          <button
            type="button"
            class="btn btn-danger"
            @click="executeConfirmation"
            :disabled="confirmLoading"
          >
            <span
              v-if="confirmLoading"
              class="spinner spinner-small"
            ></span>

            <span v-else>♲</span>

            <span>
              {{ confirmLoading ? 'Suppression...' : 'Supprimer' }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================================
         TOASTS
    ============================================================ -->
    <div
      class="toast-container"
      aria-live="polite"
      aria-atomic="true"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast"
        :class="`toast-${toast.type}`"
      >
        <div class="toast-icon">
          {{ toastIcon(toast.type) }}
        </div>

        <div class="toast-content">
          <strong>{{ toast.title }}</strong>
          <p>{{ toast.message }}</p>
        </div>

        <button
          type="button"
          class="toast-close"
          @click="removeToast(toast.id)"
          aria-label="Fermer la notification"
        >
          ×
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>

import {
  Inbox,
  Plus,
  Search,
  X,
  AlertCircle,
  AlertTriangle,
  FileText,
  Archive,
  Clock,
  Eye,
  Pencil,
  Trash2,
  Paperclip,
  Download,
  UploadCloud,
  Save,
  Check,
  CheckCircle,
  RotateCcw,
  ChevronLeft,
  ChevronRight,
  Lock,
  RefreshCw,
  FilePlus,
  FolderOpen
} from 'lucide-vue-next'
/**
 * ================================================================
 * COURRIERS ARRIVES
 * Vue 3 / Composition API / Script Setup
 * ================================================================
 */

import {
  computed,
  nextTick,
  onBeforeUnmount,
  onMounted,
  reactive,
  ref,
} from 'vue'
import { useAuthStore } from '@/stores/auth'

/* ================================================================
   CONFIGURATION API
================================================================ */

const API_URL = 'http://127.0.0.1:8000/api'

const COURRIERS_ENDPOINT = '/courriers-arrives'
const PIECES_ENDPOINT = '/pieces-suite'

const ALLOWED_EXTENSIONS = [
  'pdf',
  'jpg',
  'jpeg',
  'png',
  'doc',
  'docx',
  'xls',
  'xlsx',
]

const MAX_FILE_SIZE = 10 * 1024 * 1024

/* ================================================================
   AUTHENTIFICATION
================================================================ */

function getToken() {
  return (
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token') ||
    ''
  )
}

function authHeaders(extra = {}) {
  const token = getToken()

  return {
    Accept: 'application/json',
    ...(token
      ? {
          Authorization: `Bearer ${token}`,
        }
      : {}),
    ...extra,
  }
}

/**
 * Fonction API centrale.
 * isFormData permet de ne pas envoyer Content-Type JSON
 * lorsque le body est un FormData.
 */
async function apiFetch(path, options = {}) {
  const {
    isFormData = false,
    ...fetchOptions
  } = options

  const headers = {
    ...authHeaders(
      isFormData
        ? {}
        : {
            'Content-Type': 'application/json',
          },
    ),
    ...fetchOptions.headers,
  }

  const response = await fetch(
    `${API_URL}${path}`,
    {
      ...fetchOptions,
      headers,
    },
  )

  if (response.status === 401) {
    localStorage.removeItem('auth_token')
    sessionStorage.removeItem('auth_token')
    localStorage.removeItem('utilisateur')
    sessionStorage.removeItem('utilisateur')

    window.location.href = '/login'

    throw new Error(
      'Session expirée ou token invalide.'
    )
  }

  let payload = null

  try {
    payload = await response.json()
  } catch {
    payload = null
  }

  if (!response.ok) {
    const error = new Error(
      payload?.message ||
        'Une erreur est survenue lors de la communication avec le serveur.',
    )

    error.status = response.status
    error.payload = payload

    throw error
  }

  return payload
}

/* ================================================================
   ETATS PRINCIPAUX
================================================================ */

const courriers = ref([])
const piecesSuite = ref([])

const loading = ref(false)
const saving = ref(false)
const savingPiece = ref(false)
const loadingDocuments = ref(false)

const currentUser = ref(null)

const selectedCourrier = ref(null)
const detailDocuments = ref([])

/* ================================================================
   MODALES
================================================================ */

const showFormModal = ref(false)
const showPieceModal = ref(false)
const showDetailModal = ref(false)
const showConfirmModal = ref(false)

const showDocumentPreview = ref(false)

const previewDocumentUrl = ref('')
const previewDocumentData = ref(null)
const loadingPreview = ref(false)

const isEditing = ref(false)

/* ================================================================
   FORMULAIRE
================================================================ */

const createEmptyForm = () => ({
  num_enreg_courr_arr: '',
  date_enreg: '',
  num_ordre_orig: '',
  lib_orig: '',
  objet_courr_arri: '',
  id_piece_suit: '',
  priorite: 'NORMAL',
  statut_dossier: 'En cours',
})

const form = reactive(createEmptyForm())

const errors = reactive({
  num_ordre_orig: '',
  lib_orig: '',
  objet_courr_arri: '',
  id_piece_suit: '',
  priorite: '',
  statut_dossier: '',
})

const formError = ref('')

/* ================================================================
   FILTRES
================================================================ */

const filters = reactive({
  search: '',
  priorite: '',
  statut_dossier: '',
  date_debut: '',
  date_fin: '',
})

/* ================================================================
   PAGINATION
================================================================ */

const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  total: 0,
  perPage: 15,
})

/* ================================================================
   PIECE DE SUITE
================================================================ */

const newPieceName = ref('')
const pieceError = ref('')

const deletingPieceId = ref(null)
const showDeletePieceModal = ref(false)
const pieceToDelete = ref(null)
const deletePieceError = ref('')
/* ================================================================
   FICHIERS
================================================================ */

const fileInput = ref(null)
const selectedFiles = ref([])
const isDragging = ref(false)
const fileError = ref('')

/* ================================================================
   CONFIRMATION
================================================================ */

const confirmType = ref('')
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmLoading = ref(false)

let confirmationAction = null

/* ================================================================
   TOAST
================================================================ */

const toasts = ref([])
let toastCounter = 0

/* ================================================================
   STATISTIQUES
================================================================ */

const stats = computed(() => {
  return {
    tresUrgent: courriers.value.filter(
      (item) => item.priorite === 'TRES_URGENT',
    ).length,

    urgent: courriers.value.filter(
      (item) => item.priorite === 'URGENT',
    ).length,

    archive: courriers.value.filter(
      (item) => item.statut_dossier === 'Archivé',
    ).length,
  }
})

/* ================================================================
   UTILISATEUR
================================================================ */

const currentUserName = computed(() => {
  if (!currentUser.value) {
    return 'Utilisateur connecté'
  }

  const user = currentUser.value

  if (user.nom_complet) {
    return user.nom_complet
  }

  const fullName = [
    user.prenom,
    user.nom,
  ]
    .filter(Boolean)
    .join(' ')

  if (fullName) {
    return fullName
  }

  if (user.name) {
    return user.name
  }

  if (user.email) {
    return user.email
  }

  return 'Utilisateur connecté'
})

const authStore = useAuthStore()

const canViewCourriers = computed(() =>
  authStore.hasPermission('courriers_arrives.view')
)

const canCreateCourriers = computed(() =>
  authStore.hasPermission('courriers_arrives.create')
)

const canUpdateCourriers = computed(() =>
  authStore.hasPermission('courriers_arrives.update')
)

const canDeleteCourriers = computed(() =>
  authStore.hasPermission('courriers_arrives.delete')
)

const canViewDocuments = computed(() =>
  authStore.hasPermission('documents.view')
)

const canDownloadDocuments = computed(() =>
  authStore.hasPermission('documents.download')
)

const currentUserInitials = computed(() => {
  const name = currentUserName.value

  const parts = name
    .trim()
    .split(/\s+/)
    .filter(Boolean)

  if (parts.length >= 2) {
    return (
      parts[0][0] +
      parts[parts.length - 1][0]
    ).toUpperCase()
  }

  return name.substring(0, 2).toUpperCase()
})

/* ================================================================
   PAGES VISIBLES
================================================================ */

const visiblePages = computed(() => {
  const total = pagination.lastPage
  const current = pagination.currentPage

  if (total <= 7) {
    return Array.from(
      { length: total },
      (_, index) => index + 1,
    )
  }

  const pages = new Set([
    1,
    total,
    current,
    current - 1,
    current + 1,
    current - 2,
    current + 2,
  ])

  return Array.from(pages)
    .filter(
      (page) =>
        page >= 1 &&
        page <= total,
    )
    .sort((a, b) => a - b)
})

/* ================================================================
   EXTRACTION REPONSE API
================================================================ */

/**
 * Compatible avec :
 *
 * data: [...]
 *
 * ou :
 *
 * data: {
 *   data: [...]
 * }
 */
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

function extractObject(payload) {
  if (
    payload?.data &&
    typeof payload.data === 'object' &&
    !Array.isArray(payload.data)
  ) {
    return payload.data
  }

  return payload
}

/* ================================================================
   CHARGER UTILISATEUR
================================================================ */

async function loadCurrentUser() {
  try {
    const response = await apiFetch('/me')

    currentUser.value =
      response?.data?.utilisateur ||
      response?.data?.user ||
      response?.utilisateur ||
      response?.user ||
      response?.data ||
      null
  } catch (error) {
    console.warn(
      'Impossible de récupérer l’utilisateur connecté.',
      error,
    )
  }
}

/* ================================================================
   CHARGER PIECES DE SUITE
================================================================ */

async function loadPiecesSuite() {
  try {
    const response = await apiFetch(
      PIECES_ENDPOINT,
    )

    const list = extractList(response)

    piecesSuite.value = list.filter(
      (piece) => {
        if (
          Object.prototype.hasOwnProperty.call(
            piece,
            'actif',
          )
        ) {
          return (
            piece.actif === true ||
            piece.actif === 1 ||
            piece.actif === '1'
          )
        }

        return true
      },
    )
  } catch (error) {
    console.error(
      'Erreur chargement pièces de suite:',
      error,
    )

    showToast(
      'error',
      'Pièces de suite',
      'Impossible de charger les pièces de suite.',
    )
  }
}

/* ================================================================
   CHARGER COURRIERS
================================================================ */

async function loadCourriers(page = pagination.currentPage) {
  loading.value = true

  try {
    const params = new URLSearchParams()

    params.set(
      'page',
      String(page),
    )

    params.set(
      'per_page',
      String(pagination.perPage),
    )

    if (filters.search.trim()) {
      params.set(
        'search',
        filters.search.trim(),
      )
    }

    if (filters.priorite) {
      params.set(
        'priorite',
        filters.priorite,
      )
    }

    if (filters.statut_dossier) {
      params.set(
        'statut_dossier',
        filters.statut_dossier,
      )
    }

    if (filters.date_debut) {
      params.set(
        'date_debut',
        filters.date_debut,
      )
    }

    if (filters.date_fin) {
      params.set(
        'date_fin',
        filters.date_fin,
      )
    }

    const response = await apiFetch(
      `${COURRIERS_ENDPOINT}?${params.toString()}`,
    )

    /**
     * IMPORTANT :
     *
     * Laravel pagination retourne généralement :
     *
     * response.data.data
     *
     * et non directement response.data.
     */
    const payload =
      response?.data ?? response

    const rows = Array.isArray(payload)
      ? payload
      : Array.isArray(payload?.data)
        ? payload.data
        : []

    courriers.value = rows.filter(Boolean)

    pagination.currentPage =
      payload?.current_page ??
      response?.current_page ??
      page

    pagination.lastPage =
      payload?.last_page ??
      response?.last_page ??
      1

    pagination.total =
      payload?.total ??
      response?.total ??
      courriers.value.length
  } catch (error) {
    console.error(
      'Erreur chargement courriers:',
      error,
    )

    courriers.value = []

    showToast(
      'error',
      'Erreur',
      error.message ||
        'Impossible de charger les courriers.',
    )
  } finally {
    loading.value = false
  }
}

/* ================================================================
   RECHERCHE
================================================================ */

async function searchCourriers() {
  pagination.currentPage = 1

  await loadCourriers(1)
}

/* ================================================================
   RESET FILTRES
================================================================ */

async function resetFilters() {
  filters.search = ''
  filters.priorite = ''
  filters.statut_dossier = ''
  filters.date_debut = ''
  filters.date_fin = ''

  pagination.currentPage = 1

  await loadCourriers(1)
}

/* ================================================================
   PAGINATION
================================================================ */

async function goToPage(page) {
  if (
    page < 1 ||
    page > pagination.lastPage ||
    page === pagination.currentPage
  ) {
    return
  }

  await loadCourriers(page)
}

async function changePerPage() {
  pagination.currentPage = 1

  await loadCourriers(1)
}

/* ================================================================
   PIECES SUITE : HELPERS
================================================================ */

function pieceId(piece) {
  return (
    piece?.id_piece_suit ??
    piece?.id ??
    ''
  )
}

function pieceLabel(piece) {
  return (
    piece?.nom_piece_suit ??
    piece?.lib_piece_suit ??
    piece?.nom ??
    piece?.libelle ??
    `Pièce #${pieceId(piece)}`
  )
}

function getPieceName(id) {
  if (!id) {
    return '—'
  }

  const piece = piecesSuite.value.find(
    (item) =>
      String(pieceId(item)) ===
      String(id),
  )

  return piece
    ? pieceLabel(piece)
    : `Pièce #${id}`
}

/* ================================================================
   AJOUTER PIECE SUITE
================================================================ */

async function openPieceModal() {
  newPieceName.value = ''
  pieceError.value = ''

  await loadPiecesSuite()

  showPieceModal.value = true

  nextTick(() => {
    document
      .getElementById('piece-name')
      ?.focus()
  })
}

function closePieceModal() {
  if (savingPiece.value) {
    return
  }

  showPieceModal.value = false
  newPieceName.value = ''
  pieceError.value = ''
}

function openDeletePieceModal(piece) {
  if (!piece) {
    return
  }

  pieceToDelete.value = piece

  deletePieceError.value = ''

  showDeletePieceModal.value = true
}

function closeDeletePieceModal() {
  if (deletingPieceId.value) {
    return
  }

  showDeletePieceModal.value = false

  pieceToDelete.value = null

  deletePieceError.value = ''
}

async function deletePieceSuite() {
  const piece = pieceToDelete.value

  if (!piece) {
    return
  }

  const id = pieceId(piece)

  if (!id) {
    deletePieceError.value =
      'Identifiant de la pièce introuvable.'

    return
  }

  deletingPieceId.value = id

  deletePieceError.value = ''

  try {

    await apiFetch(
      `${PIECES_ENDPOINT}/${id}`,
      {
        method: 'DELETE',
      },
    )


    // Si la pièce supprimée était sélectionnée
    if (
      String(form.id_piece_suit) ===
      String(id)
    ) {
      form.id_piece_suit = ''
    }


    // Actualiser la liste
    await loadPiecesSuite()


    // Réinitialiser avant fermeture
    deletingPieceId.value = null

    closeDeletePieceModal()


    showToast(
      'success',
      'Pièce supprimée',
      'La pièce de suite a été supprimée avec succès.',
    )

  } catch (error) {

    console.error(
      'Erreur suppression pièce:',
      error,
    )

    deletePieceError.value =
      error?.payload?.message ||
      error.message ||
      'Impossible de supprimer cette pièce de suite.'

  } finally {

    deletingPieceId.value = null

  }
}

async function createPieceSuite() {
  pieceError.value = ''

  const name =
    newPieceName.value.trim()

  if (!name) {
    pieceError.value =
      'Le nom de la pièce est obligatoire.'

    return
  }

  savingPiece.value = true

  try {
    const response = await apiFetch(
      PIECES_ENDPOINT,
      {
        method: 'POST',
        body: JSON.stringify({
          nom_piece_suit: name,
        }),
      },
    )

    const created =
      extractObject(response)

    await loadPiecesSuite()

    const newId =
      pieceId(created)

    if (newId) {
      form.id_piece_suit = newId
    } else {
      const found =
        piecesSuite.value.find(
          (piece) =>
            pieceLabel(piece)
              .toLowerCase() ===
            name.toLowerCase(),
        )

      if (found) {
        form.id_piece_suit =
          pieceId(found)
      }
    }

    closePieceModal()

    showToast(
      'success',
      'Pièce ajoutée',
      'La pièce de suite a été ajoutée avec succès.',
    )
  } catch (error) {
    console.error(
      'Erreur création pièce:',
      error,
    )

    pieceError.value =
      error?.payload?.errors
        ?.nom_piece_suit?.[0] ||
      error.message ||
      'Impossible de créer la pièce de suite.'
  } finally {
    savingPiece.value = false
  }
}

/* ================================================================
   FORMULAIRE : RESET
================================================================ */

function resetForm() {
  Object.assign(
    form,
    createEmptyForm(),
  )

  Object.keys(errors).forEach(
    (key) => {
      errors[key] = ''
    },
  )

  formError.value = ''

  selectedFiles.value = []

  fileError.value = ''
  isDragging.value = false
}

/* ================================================================
   FORMULAIRE : CREATE
================================================================ */

function openCreateModal() {
  resetForm()

  isEditing.value = false

  showDetailModal.value = false
  showFormModal.value = true

  nextTick(() => {
    document
      .getElementById('num-ordre-orig')
      ?.focus()
  })
}

/* ================================================================
   FORMULAIRE : EDIT
================================================================ */

function openEditModal(courrier) {
  resetForm()

  isEditing.value = true

  Object.assign(
    form,
    {
      num_enreg_courr_arr:
        courrier.num_enreg_courr_arr ??
        '',
      date_enreg:
        courrier.date_enreg ??
        '',
      num_ordre_orig:
        courrier.num_ordre_orig ??
        '',
      lib_orig:
        courrier.lib_orig ??
        '',
      objet_courr_arri:
        courrier.objet_courr_arri ??
        '',
      id_piece_suit:
        courrier.id_piece_suit ??
        '',
      priorite:
        courrier.priorite ??
        'NORMAL',
      statut_dossier:
        courrier.statut_dossier ??
        'En cours',
    },
  )

  showDetailModal.value = false
  showFormModal.value = true

  nextTick(() => {
    document
      .getElementById('num-ordre-orig')
      ?.focus()
  })
}

function closeFormModal() {
  showFormModal.value = false
  resetForm()
  isEditing.value = false
}

/* ================================================================
   VALIDATION
================================================================ */

function validateForm() {
  Object.keys(errors).forEach(
    (key) => {
      errors[key] = ''
    },
  )

  formError.value = ''

  let valid = true

  if (
    !form.num_ordre_orig ||
    !form.num_ordre_orig.trim()
  ) {
    errors.num_ordre_orig =
      'Le numéro origine est obligatoire.'

    valid = false
  } else if (
    form.num_ordre_orig.length > 100
  ) {
    errors.num_ordre_orig =
      'Le numéro origine ne doit pas dépasser 100 caractères.'

    valid = false
  }

  if (
    !form.lib_orig ||
    !form.lib_orig.trim()
  ) {
    errors.lib_orig =
      "L'origine est obligatoire."

    valid = false
  } else if (
    form.lib_orig.length > 255
  ) {
    errors.lib_orig =
      "L'origine ne doit pas dépasser 255 caractères."

    valid = false
  }

  if (
    !form.objet_courr_arri ||
    !form.objet_courr_arri.trim()
  ) {
    errors.objet_courr_arri =
      "L'objet du courrier est obligatoire."

    valid = false
  }

  if (!form.id_piece_suit) {
    errors.id_piece_suit =
      'Veuillez sélectionner une pièce de suite.'

    valid = false
  }

  if (
    ![
      'NORMAL',
      'URGENT',
      'TRES_URGENT',
    ].includes(form.priorite)
  ) {
    errors.priorite =
      'La priorité sélectionnée est invalide.'

    valid = false
  }

  if (
    ![
      'En cours',
      'Lecture',
      'Archivé',
    ].includes(form.statut_dossier)
  ) {
    errors.statut_dossier =
      'Le statut sélectionné est invalide.'

    valid = false
  }

  if (!valid) {
    formError.value =
      'Veuillez corriger les champs obligatoires.'

    return false
  }

  return true
}

/* ================================================================
   SAVE COURRIER
================================================================ */


async function saveCourrier() {
  if (!validateForm()) {
    return
  }

  const wasEditing = isEditing.value

  saving.value = true
  formError.value = ''

  try {
    const body = {
      num_ordre_orig: form.num_ordre_orig.trim(),
      lib_orig: form.lib_orig.trim(),
      objet_courr_arri: form.objet_courr_arri.trim(),
      id_piece_suit: form.id_piece_suit,
      priorite: form.priorite,
      statut_dossier: form.statut_dossier,
    }

    let response

    if (wasEditing) {
      response = await apiFetch(
        `${COURRIERS_ENDPOINT}/${form.num_enreg_courr_arr}`,
        {
          method: 'PUT',
          body: JSON.stringify(body),
        },
      )
    } else {
      response = await apiFetch(
        COURRIERS_ENDPOINT,
        {
          method: 'POST',
          body: JSON.stringify(body),
        },
      )
    }

    const createdOrUpdated = extractObject(response)

    const courrierId =
      createdOrUpdated?.num_enreg_courr_arr ??
      form.num_enreg_courr_arr

    const newFiles = selectedFiles.value
      .filter((item) => item.file instanceof File)
      .map((item) => item.file)

    if (courrierId && newFiles.length > 0) {
      await uploadDocuments(
        courrierId,
        newFiles,
      )
    }

    // ✅ Fermer automatiquement après succès
    closeFormModal()

    // ✅ Actualiser le tableau
    await loadCourriers(
      wasEditing
        ? pagination.currentPage
        : 1,
    )

    showToast(
      'success',
      wasEditing
        ? 'Courrier modifié'
        : 'Courrier enregistré',
      wasEditing
        ? 'Les informations du courrier ont été modifiées avec succès.'
        : 'Le nouveau courrier a été enregistré avec succès.',
    )

  } catch (error) {
    console.error(
      'Erreur sauvegarde courrier:',
      error,
    )

    // ❌ Raha erreur dia mijanona misokatra ny formulaire
    formError.value =
      extractApiValidationMessage(error) ||
      error.message ||
      "Impossible d'enregistrer le courrier."

  } finally {
    saving.value = false
  }
}
/* ================================================================
   SUPPRESSION COURRIER
================================================================ */

function askDeleteCourrier(courrier) {

  console.log(
    '📌 Courrier sélectionné :',
    courrier
  )

  selectedCourrier.value = courrier

  confirmType.value = 'delete-courrier'

  confirmTitle.value =
    'Supprimer ce courrier ?'

  confirmMessage.value =
    `Voulez-vous vraiment supprimer le courrier n° ${courrier.num_enreg_courr_arr} ?`

  confirmationAction =
    deleteSelectedCourrier

  showConfirmModal.value = true
}


async function deleteSelectedCourrier() {

  if (!selectedCourrier.value) {

    return

  }

  confirmLoading.value = true

  try {

    const courrierId =
      selectedCourrier.value.num_enreg_courr_arr

    console.log(
      '🗑️ Suppression courrier ID :',
      courrierId
    )

    await apiFetch(
      `${COURRIERS_ENDPOINT}/${courrierId}`,
      {
        method: 'DELETE',
      }
    )

    await loadCourriers(
      pagination.currentPage
    )

    selectedCourrier.value = null

    closeConfirmModal()

    showToast(
      'success',
      'Courrier supprimé',
      'Le courrier a été supprimé avec succès.'
    )

  } catch (error) {

    console.error(
      'Erreur suppression courrier:',
      error
    )

    closeConfirmModal()

    showToast(
      'error',
      'Suppression impossible',
      error.message ||
        'Impossible de supprimer le courrier.'
    )

  } finally {

    confirmLoading.value = false

  }
}


/* ================================================================
   DÉTAIL COURRIER
================================================================ */

async function openDetailModal(courrier) {
  selectedCourrier.value = courrier

  showDetailModal.value = true

  await loadDocuments(
    courrier.num_enreg_courr_arr,
  )
}

function closeDetailModal() {
  showDetailModal.value = false
  selectedCourrier.value = null
  detailDocuments.value = []
}

function editFromDetail() {
  if (!selectedCourrier.value) {
    return
  }

  const courrier =
    selectedCourrier.value

  openEditModal(courrier)
}

/* ================================================================
   DOCUMENTS : CHARGEMENT
================================================================ */

async function loadDocuments(courrierId) {
  loadingDocuments.value = true

  try {
    const response = await apiFetch(
      `${COURRIERS_ENDPOINT}/${courrierId}/documents`,
    )

    detailDocuments.value =
      extractList(response)
  } catch (error) {
    console.error(
      'Erreur chargement documents:',
      error,
    )

    detailDocuments.value = []

    showToast(
      'error',
      'Documents',
      'Impossible de charger les documents du courrier.',
    )
  } finally {
    loadingDocuments.value = false
  }
}

/* ================================================================
   DOCUMENTS : SÉLECTION
================================================================ */

function openFilePicker() {
  fileInput.value?.click()
}

function handleFileSelect(event) {
  const files =
    Array.from(
      event.target.files || [],
    )

  addFiles(files)

  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function handleDrop(event) {
  isDragging.value = false

  const files =
    Array.from(
      event.dataTransfer?.files || [],
    )

  addFiles(files)
}

/* ================================================================
   DOCUMENTS : AJOUT
================================================================ */

function addFiles(files) {
  fileError.value = ''

  if (!files.length) {
    return
  }

  for (const file of files) {
    const extension =
      getExtension(file.name)

    if (
      !ALLOWED_EXTENSIONS.includes(
        extension,
      )
    ) {
      fileError.value =
        `Le fichier "${file.name}" possède un format non autorisé.`

      continue
    }

    if (file.size > MAX_FILE_SIZE) {
      fileError.value =
        `Le fichier "${file.name}" dépasse la taille maximale de 10 Mo.`

      continue
    }

    const alreadyExists =
      selectedFiles.value.some(
        (item) =>
          item.file.name ===
            file.name &&
          item.file.size ===
            file.size &&
          item.file.lastModified ===
            file.lastModified,
      )

    if (alreadyExists) {
      continue
    }

    selectedFiles.value.push({
      file,
      key: `${file.name}-${file.size}-${file.lastModified}-${Math.random()}`,
    })
  }
}

function removeSelectedFile(index) {
  selectedFiles.value.splice(
    index,
    1,
  )
}

/* ================================================================
   DOCUMENTS : UPLOAD
================================================================ */


async function uploadDocuments(courrierId, files) {
  if (!courrierId || !files?.length) {
    return
  }

  for (const file of files) {
    if (!(file instanceof File)) {
      continue
    }

    const formData = new FormData()

    // Champ unique : document
    formData.append('document', file)

    await apiFetch(
      `${COURRIERS_ENDPOINT}/${courrierId}/documents`,
      {
        method: 'POST',
        body: formData,
        isFormData: true,
      },
    )
  }
}
/* ================================================================
   AJOUTER DOCUMENTS DEPUIS DÉTAIL
================================================================ */

function addDocumentsToCurrent() {
  if (!selectedCourrier.value) {
    return
  }

  /**
   * On ouvre le formulaire d'édition
   * pour réutiliser la zone documents.
   */
  openEditModal(
    selectedCourrier.value,
  )
}

/* ================================================================
   DOCUMENT : ID / NOM / TAILLE
================================================================ */

function documentId(doc) {
  return doc?.num_doc ?? null
}

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
  return (
    doc?.taille ??
    doc?.size ??
    doc?.taille_fichier ??
    0
  )
}

/* ================================================================
   DOCUMENT : TYPE
================================================================ */

function formatDocumentType(document) {
  const name =
    documentName(document)

  const extension =
    getExtension(name)

  return extension
    ? extension.toUpperCase()
    : 'DOCUMENT'
}

/* ================================================================
   DOCUMENT : PREVIEW
================================================================ */

function canPreview(document) {
  const extension =
    getExtension(
      documentName(document),
    )

  return [
    'pdf',
    'jpg',
    'jpeg',
    'png',
  ].includes(extension)
}

async function previewDocument(doc) {
  try {
    if (!canViewDocuments.value) {
      throw new Error(
        "Vous n'avez pas l'autorisation d'afficher ce document."
      )
    }

    if (!selectedCourrier.value) {
      throw new Error(
        'Aucun courrier sélectionné.'
      )
    }

    const courrierId =
      selectedCourrier.value.num_enreg_courr_arr

    const numDoc = documentId(doc)

    if (!courrierId) {
      throw new Error(
        'Identifiant du courrier introuvable.'
      )
    }

    if (!numDoc) {
      console.error(
        'Document sans num_doc :',
        doc
      )

      throw new Error(
        'Identifiant du document introuvable.'
      )
    }

    loadingPreview.value = true

    const response = await fetch(
      `${API_URL}${COURRIERS_ENDPOINT}/${courrierId}/documents/${numDoc}/view`,
      {
        method: 'GET',
        headers: authHeaders(),
      }
    )

    // ------------------------------------------------------------
    // SESSION EXPIREE
    // ------------------------------------------------------------
    if (response.status === 401) {
      localStorage.removeItem('auth_token')
      sessionStorage.removeItem('auth_token')
      localStorage.removeItem('utilisateur')
      sessionStorage.removeItem('utilisateur')

      window.location.href = '/login'

      throw new Error(
        'Session expirée ou token invalide.'
      )
    }

    // ------------------------------------------------------------
    // ERREUR API
    // ------------------------------------------------------------
    if (!response.ok) {
      let message =
        "Impossible d'afficher le document."

      try {
        const payload =
          await response.json()

        message =
          payload?.message ||
          message
      } catch {
        // La réponse n'est pas du JSON
      }

      throw new Error(message)
    }

    // ------------------------------------------------------------
    // RECUPERATION DU FICHIER
    // ------------------------------------------------------------
    const blob = await response.blob()

    console.log('📄 Preview document')
    console.log('➡️ Content-Type API :', response.headers.get('content-type'))
    console.log('➡️ Blob type :', blob.type)
    console.log('➡️ Blob size :', blob.size)

    if (!blob.size) {
      throw new Error(
        'Le document reçu est vide.'
      )
    }

    // ------------------------------------------------------------
    // DETERMINER LE MIME TYPE
    // ------------------------------------------------------------
    let mimeType =
      response.headers.get('content-type') ||
      blob.type ||
      ''

    // Éviter les erreurs si Laravel retourne octet-stream
    if (
      !mimeType ||
      mimeType === 'application/octet-stream'
    ) {
      const extension =
        getExtension(documentName(doc))

      const mimeTypes = {
        pdf: 'application/pdf',
        jpg: 'image/jpeg',
        jpeg: 'image/jpeg',
        png: 'image/png',
      }

      mimeType =
        mimeTypes[extension] ||
        'application/octet-stream'
    }

    // ------------------------------------------------------------
    // RECREER LE BLOB AVEC LE BON MIME TYPE
    // ------------------------------------------------------------
    const previewBlob =
      new Blob(
        [blob],
        {
          type: mimeType,
        }
      )

    // Libérer l'ancienne URL
    if (previewDocumentUrl.value) {
      URL.revokeObjectURL(
        previewDocumentUrl.value
      )
    }

    // Créer nouvelle URL
    previewDocumentUrl.value =
      URL.createObjectURL(
        previewBlob
      )

    // Informations document
    previewDocumentData.value = {
      ...doc,
      num_doc: numDoc,
      nom_original:
        documentName(doc),
      type_mime: mimeType,
      taille:
        documentSize(doc),
    }

    console.log(
      '✅ URL preview :',
      previewDocumentUrl.value
    )

    console.log(
      '✅ MIME final :',
      mimeType
    )

    // Ouvrir modal
    showDocumentPreview.value = true

  } catch (error) {
    console.error(
      '❌ Erreur affichage document :',
      error
    )

    showToast(
      'error',
      'Affichage',
      error.message ||
        "Impossible d'afficher le document."
    )
  } finally {
    loadingPreview.value = false
  }
}
function closeDocumentPreview() {
  // Fermer la fenêtre de prévisualisation
  showDocumentPreview.value = false

  // Libérer l'URL temporaire du Blob
  if (previewDocumentUrl.value) {
    URL.revokeObjectURL(previewDocumentUrl.value)
  }

  // Réinitialiser les données
  previewDocumentUrl.value = ''
  previewDocumentData.value = null
  loadingPreview.value = false
}

function askDeletePreviewDocument() {
  if (!previewDocumentData.value) {
    return
  }

  const doc =
    previewDocumentData.value

  confirmType.value =
    'delete-document'

  confirmTitle.value =
    'Supprimer ce document ?'

  confirmMessage.value =
    `Le document "${documentName(doc)}" sera définitivement supprimé.`

  confirmationAction = async () => {
    await deletePreviewDocument(doc)
  }

  showConfirmModal.value = true
}

async function deletePreviewDocument(doc) {
  if (!selectedCourrier.value) {
    return
  }

  confirmLoading.value = true

  try {
    const courrierId =
      selectedCourrier.value.num_enreg_courr_arr

    const numDoc =
      documentId(doc)

    if (!courrierId) {
      throw new Error(
        'Identifiant du courrier introuvable.'
      )
    }

    if (!numDoc) {
      throw new Error(
        'Identifiant du document introuvable.'
      )
    }

    console.log(
      '🗑️ Suppression document :',
      `${COURRIERS_ENDPOINT}/${courrierId}/documents/${numDoc}`
    )

    await apiFetch(
      `${COURRIERS_ENDPOINT}/${courrierId}/documents/${numDoc}`,
      {
        method: 'DELETE',
      }
    )

    /*
     * Fermer la fenêtre de confirmation
     */
    closeConfirmModal()

    /*
     * Fermer la fenêtre de prévisualisation
     */
    closeDocumentPreview()

    /*
     * Recharger immédiatement les documents
     */
    await loadDocuments(courrierId)

    /*
     * Recharger le tableau pour mettre
     * à jour le compteur sans F5
     */
    await loadCourriers(
      pagination.currentPage
    )

    showToast(
      'success',
      'Document supprimé',
      'Le document a été supprimé avec succès.'
    )

  } catch (error) {
    console.error(
      'Erreur suppression document :',
      error
    )

    closeConfirmModal()

    showToast(
      'error',
      'Suppression impossible',
      error.message ||
        'Impossible de supprimer le document.'
    )

  } finally {
    confirmLoading.value = false
  }
}
/* ================================================================
   DOCUMENT : DOWNLOAD
================================================================ */

async function downloadDocument(doc) {
  try {
    if (!canDownloadDocuments.value) {
      throw new Error(
        "Vous n'avez pas l'autorisation de télécharger ce document."
      )
    }

    if (!selectedCourrier.value) {
      throw new Error(
        'Aucun courrier sélectionné.'
      )
    }

    const courrierId =
      selectedCourrier.value.num_enreg_courr_arr

    const numDoc =
      documentId(doc)

    if (!courrierId) {
      throw new Error(
        'Identifiant du courrier introuvable.'
      )
    }

    if (!numDoc) {
      console.error(
        'Document sans num_doc:',
        doc
      )

      throw new Error(
        'Identifiant du document introuvable.'
      )
    }

    const response = await fetch(
      `${API_URL}${COURRIERS_ENDPOINT}/${courrierId}/documents/${numDoc}/download?_t=${Date.now()}`,
      {
        method: 'GET',
        headers: authHeaders(),
        cache: 'no-store',
      }
    )

    if (!response.ok) {
      let message =
        'Impossible de télécharger le document.'

      try {
        const payload =
          await response.json()

        message =
          payload?.message ||
          message

      } catch {
        // La réponse peut être un fichier
      }

      throw new Error(message)
    }

    const blob =
      await response.blob()

    const url =
      URL.createObjectURL(blob)

    const link =
      window.document.createElement('a')

    link.href = url
    link.download =
      documentName(doc)

    link.style.display = 'none'

    window.document.body.appendChild(
      link
    )

    link.click()
    link.remove()

    setTimeout(() => {
      URL.revokeObjectURL(url)
    }, 1000)

    showToast(
      'success',
      'Téléchargement',
      'Le document a été téléchargé avec succès.'
    )

  } catch (error) {
    console.error(
      'Erreur téléchargement document:',
      error
    )

    showToast(
      'error',
      'Téléchargement',
      error.message ||
        'Impossible de télécharger le document.'
    )
  }
}



 /* ================================================================
    DOCUMENT : SUPPRESSION
 ================================================================= */

async function deleteDocument(doc) {
  confirmLoading.value = true

  try {
    // Vérifier le courrier sélectionné
    if (!selectedCourrier.value) {
      throw new Error(
        'Aucun courrier sélectionné.'
      )
    }

    // ID du courrier arrivé
    const courrierId =
      selectedCourrier.value.num_enreg_courr_arr

    // ID du document
    const numDoc = documentId(doc)

    console.log(
      '📌 Courrier ID :',
      courrierId
    )

    console.log(
      '📌 Document ID :',
      numDoc
    )

    // Vérifications
    if (!courrierId) {
      throw new Error(
        'Identifiant du courrier introuvable.'
      )
    }

    if (!numDoc) {
      console.error(
        'Document invalide :',
        doc
      )

      throw new Error(
        'Identifiant du document introuvable.'
      )
    }

    // URL DELETE
    const deleteUrl =
      `${COURRIERS_ENDPOINT}/${courrierId}/documents/${numDoc}`

    console.log(
      '🗑️ DELETE :',
      deleteUrl
    )

    // Suppression
    const response = await apiFetch(
      deleteUrl,
      {
        method: 'DELETE',
      }
    )

    console.log(
      '✅ Document supprimé :',
      response
    )

    // ============================================================
    // 🔄 ACTUALISER LES DOCUMENTS DU COURRIER
    // ============================================================

    await loadDocuments(courrierId)

    // ============================================================
    // 🔄 ACTUALISER LE TABLEAU DES COURRIERS
    // Permet de mettre à jour le nombre de documents
    // sans F5
    // ============================================================

    await loadCourriers(
      pagination.currentPage
    )

    // Fermer la confirmation
    closeConfirmModal()

    // Notification
    showToast(
      'success',
      'Document supprimé',
      'Le document a été supprimé avec succès.'
    )

  } catch (error) {
    console.error(
      '❌ Erreur suppression document :',
      error
    )

    closeConfirmModal()

    showToast(
      'error',
      'Suppression impossible',
      error.message ||
        'Impossible de supprimer le document.'
    )

  } finally {
    confirmLoading.value = false
  }
}


/* ================================================================
   DEMANDER CONFIRMATION SUPPRESSION DOCUMENT
================================================================ */

function askDeleteDocument(doc) {
  const numDoc = documentId(doc)

  if (!numDoc) {
    showToast(
      'error',
      'Document invalide',
      'Identifiant du document introuvable.'
    )

    return
  }

  console.log(
    '📄 Document à supprimer :',
    doc
  )

  console.log(
    '🔢 num_doc :',
    numDoc
  )

  confirmType.value =
    'delete-document'

  confirmTitle.value =
    'Supprimer ce document ?'

  confirmMessage.value =
    `Le document "${documentName(doc)}" sera supprimé.`

  confirmationAction = async () => {
    await deleteDocument(doc)
  }

  showConfirmModal.value = true
}


/* ================================================================
   FERMER MODAL CONFIRMATION
================================================================ */

function closeConfirmModal() {
  showConfirmModal.value = false

  confirmType.value = ''

  confirmTitle.value = ''

  confirmMessage.value = ''

  confirmationAction = null
}


/* ================================================================
   EXÉCUTER CONFIRMATION
================================================================ */

async function executeConfirmation() {
  if (confirmLoading.value) {
    return
  }

  if (
    typeof confirmationAction !==
    'function'
  ) {
    console.error(
      '❌ Aucune action de confirmation définie.'
    )

    showToast(
      'error',
      'Erreur',
      'Aucune action de suppression définie.'
    )

    return
  }

  try {
    await confirmationAction()

  } catch (error) {
    console.error(
      '❌ Erreur executeConfirmation :',
      error
    )
  }
}
/* ================================================================
   PRIORITÉ
================================================================ */

function priorityLabel(priority) {
  switch (priority) {
    case 'NORMAL':
      return 'Normal'

    case 'URGENT':
      return 'Urgent'

    case 'TRES_URGENT':
      return 'Très urgent'

    default:
      return priority || 'Normal'
  }
}

function priorityClass(priority) {
  switch (priority) {
    case 'URGENT':
      return 'priority-urgent-badge'

    case 'TRES_URGENT':
      return 'priority-very-urgent-badge'

    case 'NORMAL':
    default:
      return 'priority-normal-badge'
  }
}

/* ================================================================
   STATUT
================================================================ */

function statusClass(status) {
  switch (status) {
    case 'Lecture':
      return 'status-reading-badge'

    case 'Archivé':
      return 'status-archived-badge'

    case 'En cours':
    default:
      return 'status-progress-badge'
  }
}

function statusIcon(status) {
  switch (status) {
    case 'Lecture':
      return '◉'

    case 'Archivé':
      return '▣'

    case 'En cours':
    default:
      return '◷'
  }
}

/* ================================================================
   DOCUMENT COUNT
================================================================ */

function getDocumentCount(courrier) {
  if (
    Array.isArray(
      courrier?.documents,
    )
  ) {
    return courrier.documents.length
  }

  if (
    typeof courrier?.documents_count ===
    'number'
  ) {
    return courrier.documents_count
  }

  if (
    typeof courrier?.documents_count ===
    'string'
  ) {
    return Number(
      courrier.documents_count,
    )
  }

  return 0
}

/* ================================================================
   HELPERS
================================================================ */

function formatDate(value) {
  if (!value) {
    return '—'
  }

  const date =
    new Date(value)

  if (
    Number.isNaN(
      date.getTime(),
    )
  ) {
    return '—'
  }

  return new Intl.DateTimeFormat(
    'fr-FR',
    {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    },
  ).format(date)
}

function truncate(
  value,
  length = 60,
) {
  if (!value) {
    return '—'
  }

  if (value.length <= length) {
    return value
  }

  return `${value.substring(
    0,
    length,
  )}…`
}

function getExtension(filename) {
  if (!filename) {
    return ''
  }

  const parts =
    filename.split('.')

  if (parts.length < 2) {
    return ''
  }

  return parts
    .pop()
    .toLowerCase()
}

function formatFileSize(size) {
  if (!size) {
    return '0 Ko'
  }

  if (size < 1024) {
    return `${size} o`
  }

  if (size < 1024 * 1024) {
    return `${(
      size / 1024
    ).toFixed(1)} Ko`
  }

  return `${(
    size /
    (1024 * 1024)
  ).toFixed(1)} Mo`
}

function fileIcon(filename) {
  const extension =
    getExtension(filename)

  switch (extension) {
    case 'pdf':
      return 'PDF'

    case 'doc':
    case 'docx':
      return 'DOC'

    case 'xls':
    case 'xlsx':
      return 'XLS'

    case 'jpg':
    case 'jpeg':
    case 'png':
      return 'IMG'

    default:
      return 'FILE'
  }
}

function fileIconClass(filename) {
  const extension =
    getExtension(filename)

  switch (extension) {
    case 'pdf':
      return 'file-pdf'

    case 'doc':
    case 'docx':
      return 'file-doc'

    case 'xls':
    case 'xlsx':
      return 'file-xls'

    case 'jpg':
    case 'jpeg':
    case 'png':
      return 'file-image'

    default:
      return 'file-default'
  }
}

/* ================================================================
   TOAST
================================================================ */

function showToast(
  type,
  title,
  message,
  duration = 4500,
) {
  const id =
    ++toastCounter

  toasts.value.push({
    id,
    type,
    title,
    message,
  })

  window.setTimeout(
    () => {
      removeToast(id)
    },
    duration,
  )
}

function removeToast(id) {
  const index =
    toasts.value.findIndex(
      (toast) =>
        toast.id === id,
    )

  if (index !== -1) {
    toasts.value.splice(
      index,
      1,
    )
  }
}

function toastIcon(type) {
  switch (type) {
    case 'success':
      return '✓'

    case 'error':
      return '!'

    case 'warning':
      return '⚠'

    default:
      return 'i'
  }
}

/* ================================================================
   ESCAPE KEY
================================================================ */

function handleEscape(event) {
  if (event.key !== 'Escape') {
    return
  }

  if (showConfirmModal.value) {
    closeConfirmModal()
    return
  }

  if (showDocumentPreview.value) {
    closeDocumentPreview()
    return
  }

  if (showPieceModal.value) {
    closePieceModal()
    return
  }

  if (showFormModal.value) {
    closeFormModal()
    return
  }

  if (showDetailModal.value) {
    closeDetailModal()
  }
}

/* ================================================================
   MOUNT
================================================================ */

onMounted(async () => {
  await authStore.loadCurrentUser()

  currentUser.value = authStore.utilisateur

  await loadPiecesSuite()
  await loadCourriers()
})

/* ================================================================
   UNMOUNT
================================================================ */

onBeforeUnmount(() => {
  document.removeEventListener(
    'keydown',
    handleEscape,
  )
})
</script>




<style scoped>
/* ================================================================
   VARIABLES
================================================================ */

.courriers-page {
  --primary: #0f2747;
  --primary-light: #1e4e79;

  --background: #f5f7fa;
  --surface: #ffffff;

  --text: #172033;
  --text-secondary: #64748b;

  --border: #e2e8f0;

  --success: #16a34a;
  --warning: #f97316;
  --danger: #dc2626;

  --blue: #2563eb;
  --purple: #7c3aed;

  --radius: 14px;

  min-height: 100%;
  padding: 28px;

  background: var(--background);
  color: var(--text);
}

/* ================================================================
   RESET LOCAL
================================================================ */

.courriers-page *,
.courriers-page *::before,
.courriers-page *::after {
  box-sizing: border-box;
}

button,
input,
select,
textarea {
  font: inherit;
}

/* ================================================================
   HEADER
================================================================ */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 24px;
}

.header-title {
  display: flex;
  align-items: center;
  gap: 16px;
}

.title-icon {
  width: 56px;
  height: 56px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 16px;

  background: linear-gradient(
    135deg,
    var(--primary),
    var(--primary-light)
  );

  color: white;
  font-size: 27px;

  box-shadow:
    0 8px 20px
    rgba(15, 39, 71, 0.18);
}

.header-title h1 {
  margin: 0 0 5px;

  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.header-title p {
  margin: 0;

  color: var(--text-secondary);
  font-size: 14px;
}

/* ================================================================
   BUTTONS
================================================================ */

.btn {
  min-height: 44px;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  gap: 8px;

  padding: 10px 17px;

  border: 1px solid transparent;
  border-radius: 10px;

  cursor: pointer;

  font-size: 14px;
  font-weight: 700;

  transition:
    background 0.2s ease,
    color 0.2s ease,
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn:active:not(:disabled) {
  transform: translateY(0);
}

.btn:focus-visible,
.icon-button:focus-visible,
.choice-card:focus-visible,
.pagination-button:focus-visible,
.modal-close:focus-visible,
.remove-file:focus-visible,
.toast-close:focus-visible {
  outline: 3px solid
    rgba(37, 99, 235, 0.25);
  outline-offset: 2px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-primary {
  background: #2563EB;
  color: #ffffff;
  border: 0;
  border-radius: 12px;
  height: 46px;
  font-size: 15px;
  font-weight: 600;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #1D4ED8;
  box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
  transform: translateY(-1px);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
}

.btn-light {
  background: #ffffff;
  color: var(--text);
  border-color: var(--border);
}

.btn-light:hover:not(:disabled) {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.btn-outline {
  background: white;
  color: var(--primary);
  border-color: #cbd5e1;
}

.btn-outline:hover:not(:disabled) {
  background: #f1f5f9;
  border-color: var(--primary);
}

.btn-danger {
  background: var(--danger);
  color: white;
  border-color: var(--danger);
}

.btn-danger:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-danger-light {
  background: #fef2f2;
  color: #b91c1c;
  border-color: #fecaca;
}

.btn-danger-light:hover:not(:disabled) {
  background: #fee2e2;
}

.btn-icon {
  font-size: 19px;
  line-height: 1;
}

.btn-small {
  min-height: 38px;
  padding: 8px 12px;
  font-size: 13px;
}

/* ================================================================
   STATS
================================================================ */

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;

  margin-bottom: 24px;
}

.stat-card {
  min-height: 110px;

  display: flex;
  align-items: center;
  gap: 15px;

  padding: 20px;

  background: var(--surface);

  border: 1px solid var(--border);
  border-radius: var(--radius);

  box-shadow:
    0 4px 14px
    rgba(15, 23, 42, 0.04);
}

.stat-icon {
  width: 50px;
  height: 50px;

  flex: 0 0 50px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 13px;

  font-size: 22px;
  font-weight: 800;
}

.stat-total .stat-icon {
  background: #e8eef7;
  color: var(--primary);
}

.stat-danger .stat-icon {
  background: #fee2e2;
  color: var(--danger);
}

.stat-warning .stat-icon {
  background: #ffedd5;
  color: var(--warning);
}

.stat-success .stat-icon {
  background: #dcfce7;
  color: var(--success);
}

.stat-content {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.stat-label {
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 600;
}

.stat-content strong {
  color: var(--text);
  font-size: 25px;
  line-height: 1;
}

/* ================================================================
   FILTERS
================================================================ */

.filters-card,
.table-card {
  background: var(--surface);

  border: 1px solid var(--border);
  border-radius: var(--radius);

  box-shadow:
    0 4px 16px
    rgba(15, 23, 42, 0.04);
}

.filters-card {
  margin-bottom: 24px;
  padding: 22px;
}

.filters-header,
.table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 20px;

  margin-bottom: 20px;
}

.filters-header h2,
.table-header h2 {
  margin: 0 0 4px;

  font-size: 17px;
  font-weight: 800;
}

.filters-header p,
.table-header p {
  margin: 0;

  color: var(--text-secondary);
  font-size: 13px;
}

.filters-grid {
  display: grid;

  grid-template-columns:
    minmax(200px, 2fr)
    repeat(4, minmax(150px, 1fr))
    auto;

  gap: 14px;

  align-items: end;
}

.form-group {
  min-width: 0;
}

.form-group label {
  display: block;

  margin-bottom: 7px;

  color: #334155;

  font-size: 13px;
  font-weight: 700;
}

.required {
  color: var(--danger);
  margin-left: 2px;
}

.form-control {
  width: 100%;
  min-height: 44px;

  padding: 10px 12px;

  background: #ffffff;

  border: 1px solid #cbd5e1;
  border-radius: 9px;

  color: var(--text);

  font-size: 14px;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease;
}

.form-control:hover {
  border-color: #94a3b8;
}

.form-control:focus {
  outline: none;

  border-color: var(--primary-light);

  box-shadow:
    0 0 0 3px
    rgba(30, 78, 121, 0.12);
}

.form-control::placeholder {
  color: #94a3b8;
}


/* =========================
   CHAMP RECHERCHE MODERNE
   ========================= */

.filter-search {
  flex: 1;
  min-width: 280px;
}

.filter-search label {
  display: block;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #344054;
  letter-spacing: 0.1px;
}

.input-with-icon {
  position: relative;
  width: 100%;
}

.input-with-icon .input-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #667085;
  font-size: 20px;
  line-height: 1;
  pointer-events: none;
  z-index: 2;
}

.input-with-icon input {
  width: 100%;
  height: 44px;
  padding: 0 15px 0 45px;

  border: 1px solid #d0d5dd;
  border-radius: 10px;

  background: #ffffff;
  color: #101828;

  font-size: 14px;
  font-family: inherit;

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background-color 0.2s ease;
}

.input-with-icon input::placeholder {
  color: #98a2b3;
  font-size: 13px;
}

.input-with-icon input:hover {
  border-color: #98a2b3;
  background: #fcfcfd;
}

.input-with-icon input:focus {
  border-color: #2563eb;
  background: #ffffff;

  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.10),
    0 1px 2px rgba(16, 24, 40, 0.05);
}

.input-with-icon:focus-within .input-icon {
  color: #2563eb;
}
.input-with-icon .input-icon svg {
  width: 18px;
  height: 18px;
}
/*recherche*/
.input-with-icon {
  position: relative;
}

.input-with-icon input {
  padding-left: 39px;
}

.input-icon {
  position: absolute;

  left: 13px;
  top: 50%;

  transform: translateY(-50%);

  color: #64748b;

  font-size: 20px;

  pointer-events: none;
}

.filter-action {
  display: flex;
}

.btn-search {
  width: 100%;
}

/* ================================================================
   TABLE
================================================================ */

.table-card {
  overflow: hidden;
}

.table-header {
  padding: 20px 22px;
  margin: 0;
}

.per-page {
  display: flex;
  align-items: center;
  gap: 8px;

  color: var(--text-secondary);
  font-size: 13px;
}

.per-page select {
  min-height: 36px;

  padding: 5px 9px;

  border: 1px solid var(--border);
  border-radius: 8px;

  background: white;
  color: var(--text);

  font-weight: 600;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  min-width: 1200px;

  border-collapse: collapse;
}

thead {
  background: #f8fafc;
}

th {
  padding: 13px 12px;

  color: #475569;

  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);

  text-align: left;

  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.4px;

  white-space: nowrap;
}

td {
  padding: 14px 12px;

  border-bottom: 1px solid #edf2f7;

  vertical-align: middle;

  font-size: 13px;
}

tbody tr {
  transition: background 0.2s ease;
}

tbody tr:hover {
  background: #f8fafc;
}

tbody tr:last-child td {
  border-bottom: 0;
}

.actions-column {
  width: 120px;
  text-align: center;
}

.number-cell {
  color: var(--primary);
  font-weight: 800;
}

.date-cell {
  color: var(--text-secondary);
  white-space: nowrap;
}

.origin-number {
  display: inline-block;

  padding: 4px 8px;

  border-radius: 6px;

  background: #f1f5f9;

  color: #334155;

  font-size: 12px;
  font-weight: 700;
}

.origin-name {
  font-weight: 700;
}

.object-cell {
  max-width: 250px;

  color: #475569;
  line-height: 1.45;
}

.piece-badge {
  display: inline-flex;
  align-items: center;

  max-width: 150px;

  padding: 5px 9px;

  background: #eff6ff;

  border: 1px solid #dbeafe;
  border-radius: 7px;

  color: #1e40af;

  font-size: 11px;
  font-weight: 700;

  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* ================================================================
   BADGES
================================================================ */

.badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;

  min-height: 27px;

  padding: 5px 9px;

  border-radius: 999px;

  font-size: 11px;
  font-weight: 800;

  white-space: nowrap;
}

.badge-dot {
  width: 6px;
  height: 6px;

  border-radius: 50%;

  background: currentColor;
}

.priority-normal-badge {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.priority-urgent-badge {
  background: #ffedd5;
  color: #c2410c;
  border: 1px solid #fed7aa;
}

.priority-very-urgent-badge {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.status-progress-badge {
  background: #dbeafe;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}

.status-reading-badge {
  background: #ede9fe;
  color: #6d28d9;
  border: 1px solid #ddd6fe;
}

.status-archived-badge {
  background: #e2e8f0;
  color: #334155;
  border: 1px solid #cbd5e1;
}

/* ================================================================
   ACTIONS
================================================================ */

.action-buttons,
.document-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.icon-button {
  width: 34px;
  height: 34px;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  border-radius: 8px;

  border: 1px solid transparent;

  cursor: pointer;

  background: white;

  font-size: 15px;
  font-weight: 800;

  transition:
    background 0.2s ease,
    color 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.icon-button:hover {
  transform: translateY(-1px);
}

.icon-button.view {
  color: #2563eb;
  background: #eff6ff;
  border-color: #dbeafe;
}

.icon-button.view:hover {
  background: #dbeafe;
}

.icon-button.edit {
  color: #7c3aed;
  background: #f5f3ff;
  border-color: #ede9fe;
}

.icon-button.edit:hover {
  background: #ede9fe;
}

.icon-button.delete {
  color: #dc2626;
  background: #fef2f2;
  border-color: #fee2e2;
}

.icon-button.delete:hover {
  background: #fee2e2;
}

.icon-button.download {
  color: #15803d;
  background: #f0fdf4;
  border-color: #dcfce7;
}

.documents-count {
  display: inline-flex;
  align-items: center;
  gap: 5px;

  padding: 5px 8px;

  border: 1px solid #cbd5e1;
  border-radius: 7px;

  background: white;

  color: var(--primary);

  font-size: 11px;
  font-weight: 800;

  cursor: pointer;
}

.documents-count:hover {
  background: #f1f5f9;
  border-color: var(--primary-light);
}

/* ================================================================
   EMPTY STATE
================================================================ */

.empty-state {
  min-height: 340px;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  padding: 40px 20px;

  text-align: center;
}

.empty-icon {
  width: 72px;
  height: 72px;

  display: flex;
  align-items: center;
  justify-content: center;

  margin-bottom: 18px;

  border-radius: 20px;

  background: #eff6ff;

  color: var(--primary-light);

  font-size: 32px;
}

.empty-state h3 {
  margin: 0 0 7px;

  font-size: 18px;
}

.empty-state p {
  max-width: 420px;

  margin: 0 0 20px;

  color: var(--text-secondary);

  font-size: 14px;
}

/* ================================================================
   LOADING
================================================================ */

.table-loading {
  padding: 8px 0;
}

.skeleton-row {
  display: grid;
  grid-template-columns:
    70px
    100px
    130px
    150px
    1fr
    130px;

  gap: 14px;

  padding: 16px 20px;

  border-bottom: 1px solid #f1f5f9;
}

.skeleton-row span {
  height: 18px;

  border-radius: 5px;

  background: linear-gradient(
    90deg,
    #f1f5f9 25%,
    #e2e8f0 50%,
    #f1f5f9 75%
  );

  background-size: 200% 100%;

  animation: skeleton 1.4s infinite;
}

@keyframes skeleton {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.spinner {
  width: 20px;
  height: 20px;

  display: inline-block;

  border: 2px solid
    rgba(255, 255, 255, 0.35);

  border-top-color: white;

  border-radius: 50%;

  animation: spin 0.7s linear infinite;
}

.spinner-small {
  width: 15px;
  height: 15px;

  border-width: 2px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ================================================================
   PAGINATION
================================================================ */

.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 20px;

  padding: 17px 22px;

  border-top: 1px solid var(--border);
}

.pagination-info {
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 600;
}

.pagination-buttons {
  display: flex;
  align-items: center;
  gap: 5px;
}

.pagination-button {
  min-width: 35px;
  height: 35px;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  border: 1px solid var(--border);
  border-radius: 8px;

  background: white;

  color: var(--text);

  cursor: pointer;

  font-size: 13px;
  font-weight: 700;
}

.pagination-button:hover:not(:disabled) {
  border-color: var(--primary-light);
  background: #eff6ff;
}

.pagination-button.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.pagination-button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ================================================================
   MODAL
================================================================ */

.modal-overlay {
  position: fixed;
  inset: 0;

  z-index: 1000;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 20px;

  background:
    rgba(15, 23, 42, 0.58);

  backdrop-filter: blur(3px);

  overflow-y: auto;
}

.modal {
  width: 100%;
  max-height: calc(100vh - 40px);

  display: flex;
  flex-direction: column;

  background: white;

  border-radius: 18px;

  box-shadow:
    0 25px 70px
    rgba(15, 23, 42, 0.25);

  overflow: hidden;

  animation: modalIn 0.2s ease;
}

@keyframes modalIn {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.99);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-large {
  max-width: 1000px;
}

.modal-small {
  max-width: 520px;
}

.modal-confirm {
  max-width: 430px;

  padding: 32px;

  text-align: center;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;

  gap: 20px;

  padding: 20px 24px;

  border-bottom: 1px solid var(--border);

  background: #08264d;
}

.modal-title-wrapper {
  display: flex;
  align-items: center;
  gap: 13px;
}

.modal-title-icon {
  width: 45px;
  height: 45px;

  flex: 0 0 45px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 12px;

  background: #eaf1f8;

  color: var(--primary);

  font-size: 20px;
  font-weight: 800;
}

.modal-header h2 {
  margin: 0 0 4px;
  color: white;

  font-size: 19px;
  font-weight: 800;
}

.modal-header p {
  margin: 0;

  color: var(--text-secondary);

  font-size: 12px;
}

.modal-close {
  width: 36px;
  height: 36px;

  flex: 0 0 36px;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 0;
  border-radius: 9px;

  background: #f1f5f9;

  color: #475569;

  cursor: pointer;

  font-size: 24px;
  line-height: 1;
}

.modal-close:hover:not(:disabled) {
  background: #e2e8f0;
  color: var(--danger);
}

.modal-body {
  padding: 24px;

  overflow-y: auto;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;

  gap: 10px;

  margin-top: 24px;
  padding-top: 18px;

  border-top: 1px solid var(--border);
}

/* ================================================================
   SYSTEM INFO
================================================================ */

.system-info {
  display: grid;
  grid-template-columns: 1fr 1fr;

  gap: 12px;

  margin-bottom: 20px;

  padding: 14px;

  background: #f8fafc;

  border: 1px solid var(--border);
  border-radius: 11px;
}

.system-info-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.system-info-label {
  color: var(--text-secondary);

  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.system-info-item strong {
  color: var(--primary);

  font-size: 14px;
}

/* ================================================================
   FORM GRID
================================================================ */

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;

  gap: 18px;
}

.textarea {
  min-height: 110px;

  resize: vertical;

  line-height: 1.55;
}

.label-row {
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.character-counter {
  color: #64748b;
  font-size: 11px;
  font-weight: 700;
}

.character-counter.warning {
  color: var(--warning);
}

.input-error {
  border-color: var(--danger) !important;

  box-shadow:
    0 0 0 3px
    rgba(220, 38, 38, 0.08) !important;
}

.field-error {
  margin-top: 5px;

  color: var(--danger);

  font-size: 12px;
  font-weight: 600;
}

.field-help {
  display: block;

  margin-top: 6px;

  color: var(--text-secondary);

  font-size: 11px;
}

/* ================================================================
   PIECE SELECT
================================================================ */

.piece-select-wrapper {
  display: grid;
  grid-template-columns: 1fr auto;

  gap: 8px;
}

.btn-add-piece {
  min-height: 44px;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  gap: 5px;

  padding: 9px 12px;

  border: 1px solid #bfdbfe;
  border-radius: 9px;

  background: #eff6ff;
  color: #1d4ed8;

  cursor: pointer;

  font-size: 13px;
  font-weight: 800;
}

.btn-add-piece:hover:not(:disabled) {
  background: #dbeafe;
  border-color: #93c5fd;
}

.btn-add-piece:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ================================================================
   USER DISPLAY
================================================================ */

.user-display {
  min-height: 44px;

  display: flex;
  align-items: center;

  gap: 10px;

  padding: 7px 10px;

  background: #f8fafc;

  border: 1px solid var(--border);
  border-radius: 9px;
}

.user-avatar {
  width: 32px;
  height: 32px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 9px;

  background: var(--primary);
  color: white;

  font-size: 11px;
  font-weight: 800;
}

.user-display > div:nth-child(2) {
  min-width: 0;

  display: flex;
  flex-direction: column;
  gap: 1px;
}

.user-display strong {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;

  font-size: 12px;
}

.user-display span {
  color: var(--text-secondary);
  font-size: 10px;
}

.locked-icon {
  margin-left: auto;
}

/* ================================================================
   FORM SECTIONS
================================================================ */

.form-section {
  margin-top: 24px;

  padding: 18px;

  background: #fbfcfe;

  border: 1px solid var(--border);
  border-radius: 13px;
}

.section-heading {
  display: flex;
  align-items: center;

  gap: 10px;

  margin-bottom: 15px;
}

.section-icon {
  width: 36px;
  height: 36px;

  flex: 0 0 36px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 9px;

  background: #eaf1f8;
  color: var(--primary);

  font-weight: 900;
}

.section-heading h3 {
  margin: 0 0 3px;

  font-size: 15px;
  font-weight: 800;
}

.section-heading p {
  margin: 0;

  color: var(--text-secondary);

  font-size: 11px;
}

/* ================================================================
   CHOICE CARDS
================================================================ */

.choice-grid {
  display: grid;

  grid-template-columns: repeat(3, 1fr);

  gap: 12px;
}

.choice-card {
  position: relative;

  min-height: 78px;

  display: flex;
  align-items: center;

  gap: 11px;

  padding: 13px;

  border: 2px solid #e2e8f0;
  border-radius: 11px;

  background: white;

  color: var(--text);

  cursor: pointer;

  text-align: left;

  transition:
    border-color 0.2s ease,
    background 0.2s ease,
    color 0.2s ease,
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.choice-card:hover {
  transform: translateY(-1px);

  border-color: #94a3b8;

  box-shadow:
    0 5px 14px
    rgba(15, 23, 42, 0.06);
}

.choice-card.selected {
  color: white;

  box-shadow:
    0 7px 18px
    rgba(15, 23, 42, 0.14);

  transform: translateY(-1px);
}

.choice-icon {
  width: 34px;
  height: 34px;

  flex: 0 0 34px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 9px;

  background: #f1f5f9;

  font-size: 15px;
  font-weight: 900;
}

.choice-content {
  min-width: 0;

  display: flex;
  flex-direction: column;
  gap: 3px;
}

.choice-content strong {
  font-size: 13px;
  font-weight: 800;
}

.choice-content small {
  font-size: 10px;

  color: #64748b;
}

.choice-check {
  margin-left: auto;

  width: 23px;
  height: 23px;

  flex: 0 0 23px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: rgba(255, 255, 255, 0.2);

  color: white;

  font-size: 13px;
  font-weight: 900;
}

/* ================================================================
   PRIORITÉ - COULEURS SÉLECTIONNÉES
================================================================ */

.priority-normal:hover {
  border-color: #22c55e;
}

.priority-normal.selected {
  background: #15803d;
  border-color: #15803d;
}

.priority-normal.selected .choice-icon {
  background: rgba(255, 255, 255, 0.18);
  color: white;
}

.priority-normal.selected .choice-content small {
  color: #dcfce7;
}

.priority-urgent:hover {
  border-color: #f97316;
}

.priority-urgent.selected {
  background: #c2410c;
  border-color: #c2410c;
}

.priority-urgent.selected .choice-icon {
  background: rgba(255, 255, 255, 0.18);
  color: white;
}

.priority-urgent.selected .choice-content small {
  color: #ffedd5;
}

.priority-very-urgent:hover {
  border-color: #ef4444;
}

.priority-very-urgent.selected {
  background: #b91c1c;
  border-color: #b91c1c;
}

.priority-very-urgent.selected .choice-icon {
  background: rgba(255, 255, 255, 0.18);
  color: white;
}

.priority-very-urgent.selected .choice-content small {
  color: #fee2e2;
}

/* ================================================================
   STATUT - COULEURS SÉLECTIONNÉES
================================================================ */

.status-progress:hover {
  border-color: #3b82f6;
}

.status-progress.selected {
  background: #1d4ed8;
  border-color: #1d4ed8;
}

.status-progress.selected .choice-icon {
  background: rgba(255, 255, 255, 0.18);
}

.status-progress.selected .choice-content small {
  color: #dbeafe;
}

.status-reading:hover {
  border-color: #8b5cf6;
}

.status-reading.selected {
  background: #6d28d9;
  border-color: #6d28d9;
}

.status-reading.selected .choice-icon {
  background: rgba(255, 255, 255, 0.18);
}

.status-reading.selected .choice-content small {
  color: #ede9fe;
}

.status-archived:hover {
  border-color: #64748b;
}

.status-archived.selected {
  background: #334155;
  border-color: #334155;
}

.status-archived.selected .choice-icon {
  background: rgba(255, 255, 255, 0.18);
}

.status-archived.selected .choice-content small {
  color: #e2e8f0;
}

/* ================================================================
   DROPZONE
================================================================ */

.dropzone {
  min-height: 180px;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  gap: 7px;

  padding: 22px;

  border: 2px dashed #cbd5e1;
  border-radius: 13px;

  background: #ffffff;

  color: #475569;

  cursor: pointer;

  text-align: center;

  transition:
    border-color 0.2s ease,
    background 0.2s ease;
}

.dropzone:hover,
.dropzone.dragging {
  border-color: var(--primary-light);
  background: #f0f7ff;
}

.dropzone-icon {
  width: 48px;
  height: 48px;

  display: flex;
  align-items: center;
  justify-content: center;

  margin-bottom: 3px;

  border-radius: 13px;

  background: #eaf1f8;

  color: var(--primary);

  font-size: 22px;
  font-weight: 900;
}

.dropzone strong {
  color: var(--text);

  font-size: 13px;
}

.dropzone span {
  color: var(--text-secondary);
  font-size: 11px;
}

.dropzone-button {
  display: inline-flex;
  align-items: center;

  gap: 5px;

  padding: 7px 11px;

  border: 1px solid #bfdbfe;
  border-radius: 7px;

  background: #eff6ff;
  color: #1d4ed8;

  cursor: pointer;

  font-size: 12px;
  font-weight: 800;
}

.dropzone-button:hover {
  background: #dbeafe;
}

.dropzone small {
  color: #94a3b8;
  font-size: 10px;
}

.hidden-file-input {
  display: none;
}

/* ================================================================
   FILES
================================================================ */

.selected-files {
  margin-top: 15px;
}

.files-title {
  display: flex;
  align-items: center;
  gap: 7px;

  margin-bottom: 9px;

  font-size: 12px;
}

.files-title span {
  min-width: 21px;
  height: 21px;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: var(--primary);
  color: white;

  font-size: 10px;
}

.files-list {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.file-item,
.detail-document-item {
  display: flex;
  align-items: center;

  gap: 10px;

  padding: 10px;

  border: 1px solid var(--border);
  border-radius: 9px;

  background: white;
}

.file-icon {
  width: 38px;
  height: 42px;

  flex: 0 0 38px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 8px;

  font-size: 8px;
  font-weight: 900;
}

.file-pdf {
  background: #fee2e2;
  color: #b91c1c;
}

.file-doc {
  background: #dbeafe;
  color: #1d4ed8;
}

.file-xls {
  background: #dcfce7;
  color: #15803d;
}

.file-image {
  background: #fef3c7;
  color: #b45309;
}

.file-default {
  background: #f1f5f9;
  color: #475569;
}

.file-info {
  min-width: 0;

  flex: 1;

  display: flex;
  flex-direction: column;

  gap: 3px;
}

.file-info strong {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;

  font-size: 12px;
}

.file-info span {
  color: var(--text-secondary);
  font-size: 10px;
}

.remove-file {
  width: 30px;
  height: 30px;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 0;
  border-radius: 7px;

  background: #fef2f2;
  color: var(--danger);

  cursor: pointer;

  font-size: 19px;
}

.remove-file:hover:not(:disabled) {
  background: #fee2e2;
}

.file-error {
  display: flex;
  align-items: center;
  gap: 7px;

  margin-top: 10px;

  padding: 9px 11px;

  border: 1px solid #fecaca;
  border-radius: 8px;

  background: #fef2f2;

  color: #b91c1c;

  font-size: 12px;
  font-weight: 600;
}

.document-help {
  margin-top: 8px;

  color: #94a3b8;

  font-size: 10px;
}

.form-error-box {
  display: flex;
  align-items: flex-start;
  gap: 10px;

  margin-top: 20px;
  padding: 12px 14px;

  border: 1px solid #fecaca;
  border-radius: 10px;

  background: #fef2f2;

  color: #991b1b;
}

.form-error-box > span {
  font-size: 18px;
  font-weight: 900;
}

.form-error-box strong {
  font-size: 12px;
}

.form-error-box p {
  margin: 3px 0 0;

  font-size: 11px;
}

/* ================================================================
   DETAIL
================================================================ */

.detail-body {
  background: #fbfcfe;
}

.detail-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 15px;

  padding: 17px;

  background: linear-gradient(
    135deg,
    #0f2747,
    #1e4e79
  );

  border-radius: 12px;

  color: white;
}

.detail-banner-label {
  display: block;

  margin-bottom: 4px;

  color: #cbd5e1;

  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

.detail-banner strong {
  font-size: 22px;
}

.detail-banner-right {
  display: flex;
  align-items: center;
  gap: 7px;
  flex-wrap: wrap;
}

.detail-banner-right .badge {
  background: white;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);

  gap: 12px;

  margin-top: 16px;
}

.detail-item {
  padding: 13px;

  background: white;

  border: 1px solid var(--border);
  border-radius: 10px;
}

.detail-item span,
.detail-object > span {
  display: block;

  margin-bottom: 5px;

  color: var(--text-secondary);

  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

.detail-item strong {
  font-size: 13px;
}

.detail-object {
  margin-top: 12px;

  padding: 14px;

  background: white;

  border: 1px solid var(--border);
  border-radius: 10px;
}

.detail-object p {
  margin: 0;

  color: #334155;

  font-size: 13px;
  line-height: 1.6;

  white-space: pre-wrap;
}

.documents-section {
  margin-top: 20px;

  padding: 17px;

  background: white;

  border: 1px solid var(--border);
  border-radius: 12px;
}

.documents-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 15px;

  margin-bottom: 13px;
}

.documents-section-header h3 {
  margin: 0 0 3px;

  font-size: 15px;
}

.documents-section-header p {
  margin: 0;

  color: var(--text-secondary);

  font-size: 11px;
}

.detail-documents-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.document-actions {
  margin-left: auto;
}

.documents-empty {
  padding: 30px 15px;

  border: 1px dashed #cbd5e1;
  border-radius: 10px;

  text-align: center;
}

.documents-empty > div {
  margin-bottom: 8px;

  color: #94a3b8;

  font-size: 27px;
}

.documents-empty strong {
  display: block;

  margin-bottom: 4px;

  font-size: 13px;
}

.documents-empty p {
  margin: 0;

  color: var(--text-secondary);

  font-size: 11px;
}

.documents-loading {
  display: flex;
  align-items: center;
  justify-content: center;

  gap: 9px;

  min-height: 100px;

  color: var(--text-secondary);

  font-size: 12px;
}

.documents-loading .spinner {
  border-color: #cbd5e1;
  border-top-color: var(--primary);
}

/* ================================================================
   CONFIRM
================================================================ */

.confirm-icon {
  width: 58px;
  height: 58px;

  display: flex;
  align-items: center;
  justify-content: center;

  margin: 0 auto 17px;

  border-radius: 50%;

  background: #fee2e2;
  color: var(--danger);

  font-size: 25px;
  font-weight: 900;
}

.modal-confirm h2 {
  margin: 0 0 8px;

  font-size: 19px;
}

.modal-confirm > p {
  margin: 0;

  color: var(--text-secondary);

  font-size: 13px;
  line-height: 1.55;
}

.confirm-actions {
  display: flex;
  justify-content: center;

  gap: 9px;

  margin-top: 24px;
}

/* ================================================================
   TOAST
================================================================ */

.toast-container {
  position: fixed;

  top: 20px;
  right: 20px;

  z-index: 2000;

  width: min(
    380px,
    calc(100vw - 40px)
  );

  display: flex;
  flex-direction: column;

  gap: 9px;
}

.toast {
  display: flex;
  align-items: flex-start;

  gap: 10px;

  padding: 13px;

  background: white;

  border: 1px solid var(--border);
  border-radius: 11px;

  box-shadow:
    0 10px 30px
    rgba(15, 23, 42, 0.14);

  animation: toastIn 0.25s ease;
}

@keyframes toastIn {
  from {
    opacity: 0;
    transform: translateX(15px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.toast-icon {
  width: 30px;
  height: 30px;

  flex: 0 0 30px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  font-weight: 900;
}

.toast-success .toast-icon {
  background: #dcfce7;
  color: var(--success);
}

.toast-error .toast-icon {
  background: #fee2e2;
  color: var(--danger);
}

.toast-warning .toast-icon {
  background: #ffedd5;
  color: var(--warning);
}

.toast-info .toast-icon {
  background: #dbeafe;
  color: var(--blue);
}

.toast-content {
  min-width: 0;
  flex: 1;
}

.toast-content strong {
  display: block;

  margin-bottom: 2px;

  font-size: 12px;
}

.toast-content p {
  margin: 0;

  color: var(--text-secondary);

  font-size: 11px;
  line-height: 1.4;
}

.toast-close {
  width: 25px;
  height: 25px;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 0;
  border-radius: 6px;

  background: transparent;

  color: #64748b;

  cursor: pointer;

  font-size: 18px;
}

.toast-close:hover {
  background: #f1f5f9;
}

/* ================================================================
   MOBILE CARDS
================================================================ */

.mobile-cards {
  display: none;
}

.courrier-mobile-card {
  margin: 12px;

  padding: 15px;

  background: white;

  border: 1px solid var(--border);
  border-radius: 12px;

  box-shadow:
    0 3px 10px
    rgba(15, 23, 42, 0.04);
}

.mobile-card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;

  gap: 10px;

  padding-bottom: 12px;

  border-bottom: 1px solid #eef2f7;
}

.mobile-number {
  display: block;

  margin-bottom: 3px;

  color: var(--primary);

  font-size: 14px;
  font-weight: 800;
}

.mobile-date {
  color: var(--text-secondary);

  font-size: 11px;
}

.mobile-card-content {
  padding: 13px 0;
}

.mobile-info {
  display: flex;
  flex-direction: column;

  gap: 3px;

  margin-bottom: 10px;
}

.mobile-label {
  color: var(--text-secondary);

  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

.mobile-info strong {
  font-size: 12px;
}

.mobile-object strong {
  line-height: 1.5;
}

.mobile-badges {
  display: flex;
  flex-wrap: wrap;

  gap: 6px;
}

.mobile-card-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);

  gap: 7px;

  padding-top: 12px;

  border-top: 1px solid #eef2f7;
}

.mobile-card-actions .btn {
  width: 100%;
}

/* ================================================================
   RESPONSIVE : TABLET
================================================================ */

@media (max-width: 1200px) {
  .courriers-page {
    padding: 20px;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .filters-grid {
    grid-template-columns:
      repeat(2, 1fr);
  }

  .filter-search {
    grid-column: span 2;
  }

  .filter-action {
    grid-column: span 2;
  }

  .desktop-table table {
    min-width: 1100px;
  }
}

/* ================================================================
   RESPONSIVE : MOBILE
================================================================ */

@media (max-width: 767px) {
  .courriers-page {
    padding: 13px;
  }

  .page-header {
    align-items: flex-start;

    flex-direction: column;

    gap: 15px;
  }

  .header-title {
    align-items: flex-start;
  }

  .title-icon {
    width: 45px;
    height: 45px;

    flex: 0 0 45px;

    border-radius: 12px;

    font-size: 21px;
  }

  .header-title h1 {
    font-size: 22px;
  }

  .header-title p {
    font-size: 12px;
    line-height: 1.4;
  }

  .btn-new {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .stat-card {
    min-height: 88px;
    padding: 15px;
  }

  .filters-card {
    padding: 15px;
  }

  .filters-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .filters-header .btn {
    width: 100%;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .filter-search,
  .filter-action {
    grid-column: auto;
  }

  .table-header {
    align-items: flex-start;
    flex-direction: column;

    padding: 15px;
  }

  .per-page {
    width: 100%;
    justify-content: space-between;
  }

  .desktop-table {
    display: none;
  }

  .mobile-cards {
    display: block;
  }

  .pagination {
    align-items: flex-start;
    flex-direction: column;

    padding: 15px;
  }

  .pagination-buttons {
    width: 100%;

    overflow-x: auto;

    padding-bottom: 2px;
  }

  .pagination-button {
    flex: 0 0 35px;
  }

  .modal-overlay {
    align-items: flex-end;

    padding: 0;
  }

  .modal {
    max-height: 94vh;

    border-radius: 18px 18px 0 0;
  }

  .modal-header {
    padding: 16px;
  }

  .modal-body {
    padding: 16px;
  }

  .form-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .system-info {
    grid-template-columns: 1fr;
  }

  .form-section {
    padding: 13px;
  }

  .choice-grid {
    grid-template-columns: 1fr;
  }

  .choice-card {
    min-height: 68px;
  }

  .piece-select-wrapper {
    grid-template-columns: 1fr;
  }

  .btn-add-piece {
    width: 100%;
  }

  .modal-footer {
    flex-direction: column-reverse;
  }

  .modal-footer .btn {
    width: 100%;
  }

  .detail-banner {
    align-items: flex-start;
    flex-direction: column;
  }

  .detail-banner-right {
    width: 100%;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .documents-section-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .documents-section-header .btn {
    width: 100%;
  }

  .detail-document-item {
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .document-actions {
    width: 100%;
    justify-content: flex-end;

    margin-left: 0;
  }

  .modal-confirm {
    margin: 15px;

    max-height: none;

    padding: 25px;

    border-radius: 16px;
  }

  .confirm-actions {
    flex-direction: column;
  }

  .confirm-actions .btn {
    width: 100%;
  }

  .toast-container {
    top: 12px;
    right: 12px;
    left: 12px;

    width: auto;
  }
}

/* ================================================================
   RESPONSIVE : PETIT MOBILE
================================================================ */

@media (max-width: 420px) {
  .courriers-page {
    padding: 9px;
  }

  .header-title h1 {
    font-size: 20px;
  }

  .stat-content strong {
    font-size: 22px;
  }

  .mobile-card-actions {
    grid-template-columns: 1fr;
  }

  .choice-card {
    padding: 11px;
  }
}

/* ================================================================
   ACCESSIBILITÉ
================================================================ */

@media (prefers-reduced-motion: reduce) {
  .courriers-page *,
  .courriers-page *::before,
  .courriers-page *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}


/* ============================================================
   MODAL : PRÉVISUALISATION DOCUMENT
============================================================ */

.document-preview-overlay {
  position: fixed;
  inset: 0;
  z-index: 3000;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 24px;

  background: rgba(8, 18, 35, 0.78);

  backdrop-filter: blur(5px);
}

.document-preview-modal {
  width: min(1200px, 96vw);
  height: min(850px, 92vh);

  display: flex;
  flex-direction: column;

  overflow: hidden;

  background: #ffffff;

  border-radius: 18px;

  box-shadow:
    0 30px 80px rgba(0, 0, 0, 0.35);

  animation:
    documentPreviewIn
    0.2s ease-out;
}

/* ============================================================
   HEADER
============================================================ */

.document-preview-header {
  flex-shrink: 0;

  min-height: 76px;

  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 20px;

  padding: 14px 20px;

  background: #0b1f3a;

  color: #ffffff;

  border-bottom: 3px solid #c9a227;
}

.document-preview-title-wrapper {
  min-width: 0;

  display: flex;
  align-items: center;

  gap: 14px;
}

.document-preview-title-icon {
  width: 44px;
  height: 44px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 11px;

  background: rgba(255, 255, 255, 0.1);

  font-size: 12px;
  font-weight: 800;
}

.document-preview-title-content {
  min-width: 0;
}

.document-preview-title-content h2 {
  margin: 0;

  font-size: 18px;
  font-weight: 700;
}

.document-preview-title-content p {
  max-width: 650px;

  margin: 4px 0 0;

  overflow: hidden;

  white-space: nowrap;

  text-overflow: ellipsis;

  color: rgba(255, 255, 255, 0.7);

  font-size: 13px;
}

/* ============================================================
   CLOSE
============================================================ */

.document-preview-close {
  width: 42px;
  height: 42px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 0;
  border-radius: 10px;

  background: rgba(255, 255, 255, 0.08);

  color: #ffffff;

  font-size: 28px;
  line-height: 1;

  cursor: pointer;

  transition:
    background 0.2s ease,
    transform 0.2s ease;
}

.document-preview-close:hover {
  background: rgba(255, 255, 255, 0.16);

  transform: rotate(90deg);
}

/* ============================================================
   BODY
============================================================ */

.document-preview-body {
  flex: 1;

  min-height: 0;

  position: relative;

  display: flex;
  align-items: center;
  justify-content: center;

  overflow: hidden;

  background: #e9edf2;
}

/* PDF */

.document-preview-frame {
  width: 100%;
  height: 100%;

  border: 0;

  background: #ffffff;
}

/* IMAGE */

.document-preview-image-container {
  width: 100%;
  height: 100%;

  display: flex;
  align-items: center;
  justify-content: center;

  overflow: auto;

  padding: 25px;
}

.document-preview-image {
  max-width: 100%;
  max-height: 100%;

  object-fit: contain;

  border-radius: 8px;

  box-shadow:
    0 10px 30px rgba(0, 0, 0, 0.18);
}

/* ============================================================
   DOCUMENT NON SUPPORTÉ
============================================================ */

.document-preview-unsupported {
  max-width: 500px;

  padding: 40px;

  text-align: center;
}

.unsupported-file-icon {
  width: 80px;
  height: 80px;

  margin: 0 auto 20px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 18px;

  background: #f0f3f7;

  color: #0b1f3a;

  font-size: 18px;
  font-weight: 800;
}

.document-preview-unsupported h3 {
  margin: 0 0 10px;

  color: #182235;

  font-size: 20px;
}

.document-preview-unsupported p {
  margin: 0 0 16px;

  color: #697586;

  line-height: 1.6;
}

.unsupported-file-name {
  display: block;

  max-width: 100%;

  overflow: hidden;

  text-overflow: ellipsis;

  white-space: nowrap;

  color: #0b1f3a;
}

/* ============================================================
   LOADING
============================================================ */

.document-preview-loading {
  display: flex;
  flex-direction: column;

  align-items: center;
  justify-content: center;

  gap: 15px;

  color: #536071;
}

/* ============================================================
   FOOTER
============================================================ */

.document-preview-footer {
  flex-shrink: 0;

  min-height: 72px;

  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 20px;

  padding: 12px 20px;

  background: #ffffff;

  border-top: 1px solid #e2e7ed;
}

.document-preview-info {
  min-width: 0;

  display: flex;
  flex-direction: column;

  gap: 4px;
}

.preview-info-label {
  color: #8a94a3;

  font-size: 11px;
  font-weight: 600;

  text-transform: uppercase;

  letter-spacing: 0.05em;
}

.document-preview-info strong {
  max-width: 400px;

  overflow: hidden;

  white-space: nowrap;

  text-overflow: ellipsis;

  color: #263246;

  font-size: 13px;
}

.document-preview-actions {
  display: flex;
  align-items: center;

  gap: 8px;

  flex-shrink: 0;
}

/* ============================================================
   ANIMATION
============================================================ */

@keyframes documentPreviewIn {
  from {
    opacity: 0;

    transform:
      translateY(10px)
      scale(0.97);
  }

  to {
    opacity: 1;

    transform:
      translateY(0)
      scale(1);
  }
}

/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 768px) {

  .document-preview-overlay {
    padding: 10px;
  }

  .document-preview-modal {
    width: 100%;
    height: 95vh;

    border-radius: 14px;
  }

  .document-preview-header {
    min-height: 68px;

    padding: 10px 14px;
  }

  .document-preview-title-content p {
    max-width: 200px;
  }

  .document-preview-footer {
    flex-direction: column;
    align-items: stretch;

    padding: 12px 14px;
  }

  .document-preview-actions {
    width: 100%;
  }

  .document-preview-actions .btn {
    flex: 1;
  }

  .document-preview-info {
    display: none;
  }
}



/* ============================================================
   GESTION PIÈCES DE SUITE
============================================================ */

/* ============================================================
   MODAL PRINCIPAL
============================================================ */

.modal-piece-manager {
  width: min(680px, 95vw);
  max-height: 88vh;

  display: flex;
  flex-direction: column;

  overflow: hidden;
}


/* ============================================================
   BODY
   Tsy misy scroll eto
============================================================ */

.piece-manager-body {
  padding: 20px 22px;

  overflow: hidden;

  display: flex;
  flex-direction: column;

  min-height: 0;
}


/* ============================================================
   AJOUT SECTION
   Tsy misy scrollbar
============================================================ */

.piece-add-section {
  flex-shrink: 0;

  padding: 2px 0 18px;
}


/* TITLE AJOUT */

.piece-section-title {
  display: flex;
  align-items: center;

  gap: 12px;

  margin-bottom: 18px;
}


/* ICON AJOUT */

.piece-section-icon {
  width: 42px;
  height: 42px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 12px;

  font-size: 22px;
  font-weight: 700;

  background: rgba(184, 134, 11, 0.12);

  color: #b8860b;
}


/* TITLE */

.piece-section-title h3 {
  margin: 0;

  font-size: 16px;
  font-weight: 700;

  color: #1e293b;
}


/* DESCRIPTION */

.piece-section-title p {
  margin: 4px 0 0;

  font-size: 13px;

  color: #64748b;
}


/* ============================================================
   INPUT ROW
============================================================ */

.piece-input-row {
  display: flex;

  align-items: center;

  gap: 10px;
}


/* INPUT */

.piece-input-row .form-control {
  flex: 1;

  min-width: 0;
}


/* BOUTON AJOUTER */

.btn-piece-add {
  min-width: 125px;
  height: 42px;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  gap: 7px;

  flex-shrink: 0;
}


/* ============================================================
   DIVIDER
============================================================ */

.piece-divider {
  height: 1px;

  flex-shrink: 0;

  margin: 0 0 18px;

  background: #e2e8f0;
}


/* ============================================================
   LIST SECTION
   Ity no espace afaka mihena
============================================================ */

.piece-list-section {
  display: flex;
  flex-direction: column;

  flex: 1;

  min-height: 0;

  overflow: hidden;
}


/* ============================================================
   LIST HEADER
============================================================ */

.piece-list-header {
  display: flex;

  align-items: center;

  justify-content: space-between;

  flex-shrink: 0;

  margin-bottom: 12px;
}


/* TITLE */

.piece-list-header h3 {
  margin: 0;

  font-size: 16px;
  font-weight: 700;

  color: #1e293b;
}


/* COUNT */

.piece-list-header p {
  margin: 4px 0 0;

  font-size: 13px;

  color: #64748b;
}


/* ============================================================
   BOUTON REFRESH
============================================================ */

.btn-refresh-piece {
  width: 38px;
  height: 38px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 1px solid #e2e8f0;

  border-radius: 10px;

  background: #ffffff;

  color: #475569;

  font-size: 20px;

  cursor: pointer;

  transition:
    background 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease,
    border-color 0.2s ease;
}


.btn-refresh-piece:hover:not(:disabled) {
  background: #f8fafc;

  border-color: #cbd5e1;

  color: #1e293b;

  transform: rotate(90deg);
}


.btn-refresh-piece:disabled {
  opacity: 0.5;

  cursor: not-allowed;
}


/* ============================================================
   LIST
   Ity irery ihany no misy SCROLL
============================================================ */

.piece-list {
  display: flex;
  flex-direction: column;

  gap: 8px;

  flex: 1;

  min-height: 0;

  max-height: 300px;

  overflow-y: auto;
  overflow-x: hidden;

  padding-right: 5px;

  padding-bottom: 4px;
}


/* ============================================================
   SCROLLBAR PROFESSIONAL
============================================================ */

.piece-list::-webkit-scrollbar {
  width: 7px;
}


.piece-list::-webkit-scrollbar-track {
  background: #f1f5f9;

  border-radius: 10px;
}


.piece-list::-webkit-scrollbar-thumb {
  background: #cbd5e1;

  border-radius: 10px;
}


.piece-list::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}


/* ============================================================
   LIST ITEM
============================================================ */

.piece-list-item {
  display: flex;

  align-items: center;

  justify-content: space-between;

  gap: 15px;

  padding: 11px 12px;

  border: 1px solid #e2e8f0;

  border-radius: 11px;

  background: #ffffff;

  flex-shrink: 0;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    transform 0.2s ease,
    background 0.2s ease;
}


.piece-list-item:hover {
  border-color: #cbd5e1;

  background: #fafafa;

  box-shadow:
    0 4px 12px
    rgba(15, 23, 42, 0.06);

  transform: translateY(-1px);
}


/* ============================================================
   PIECE INFO
============================================================ */

.piece-info {
  display: flex;

  align-items: center;

  gap: 12px;

  min-width: 0;

  flex: 1;
}


/* DOCUMENT ICON */

.piece-document-icon {
  width: 38px;
  height: 38px;

  flex-shrink: 0;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 10px;

  background: #f1f5f9;

  font-size: 18px;
}


/* NAME WRAPPER */

.piece-name-wrapper {
  display: flex;

  flex-direction: column;

  min-width: 0;

  flex: 1;
}


/* NAME */

.piece-name {
  overflow: hidden;

  text-overflow: ellipsis;

  white-space: nowrap;

  font-size: 14px;

  font-weight: 600;

  color: #1e293b;
}


/* ID */

.piece-id {
  margin-top: 3px;

  font-size: 11px;

  color: #94a3b8;
}


/* ============================================================
   BOUTON SUPPRIMER LIST
============================================================ */

.btn-delete-piece {
  width: 40px;
  height: 40px;

  flex-shrink: 0;

  display: flex;

  align-items: center;
  justify-content: center;

  border: 1px solid #de0a0a;

  border-radius: 10px;

  background: white;

  color: #de0a0a;

  font-size: 18px;

  cursor: pointer;

  opacity: 1;

  transition:
    background 0.2s ease,
    color 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}


/* HOVER */

.btn-delete-piece:hover:not(:disabled) {
  background: #dc2626;

  border-color: #dc2626;

  color: #ffffff;

  transform: scale(1.05);

  box-shadow:
    0 5px 12px
    rgba(220, 38, 38, 0.25);
}


/* ACTIVE */

.btn-delete-piece:active:not(:disabled) {
  transform: scale(0.96);
}


/* DISABLED */

.btn-delete-piece:disabled {
  cursor: not-allowed;

  opacity: 0.55;

  background: #f8fafc;

  color: #94a3b8;

  border-color: #e2e8f0;
}


/* ============================================================
   EMPTY
============================================================ */

.piece-empty {
  padding: 30px 20px;

  text-align: center;

  border: 1px dashed #cbd5e1;

  border-radius: 12px;

  background: #f8fafc;
}


.piece-empty-icon {
  margin-bottom: 10px;

  font-size: 30px;
}


.piece-empty p {
  margin: 0;

  font-size: 14px;

  color: #64748b;
}


/* ============================================================
   FOOTER MODAL PRINCIPAL
============================================================ */

.modal-piece-manager .modal-footer {
  flex-shrink: 0;

  display: flex;

  justify-content: flex-end;

  align-items: center;

  gap: 10px;

  padding: 14px 22px;

  border-top: 1px solid #e2e8f0;

  background: #ffffff;
}


/* BOUTON FERMER */

.modal-piece-manager .modal-footer .btn-light {
  min-width: 105px;

  height: 40px;

  display: inline-flex;

  align-items: center;
  justify-content: center;

  gap: 7px;

  border: 1px solid #cbd5e1;

  border-radius: 9px;

  background: #ffffff;

  color: #475569;

  font-weight: 600;

  cursor: pointer;

  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}


.modal-piece-manager .modal-footer .btn-light:hover:not(:disabled) {
  background: #f8fafc;

  border-color: #94a3b8;

  transform: translateY(-1px);
}


/* ============================================================
   MODAL SUPPRESSION
============================================================ */

.modal-delete-piece {
  width: min(440px, 92vw);

  overflow: hidden;
}


/* ============================================================
   DELETE HEADER
============================================================ */

.delete-piece-header {
  padding: 24px 24px 16px;

  text-align: center;

  border-bottom: 1px solid #e2e8f0;
}


/* WARNING ICON */

.delete-warning-icon {
  width: 58px;
  height: 58px;

  margin: 0 auto 14px;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: #fff1f2;

  border: 1px solid #fecaca;

  color: #dc2626;

  font-size: 28px;

  font-weight: 700;
}


/* TITLE */

.delete-piece-header h2 {
  margin: 0;

  font-size: 20px;

  font-weight: 700;

  color: #1e293b;
}


/* DESCRIPTION */

.delete-piece-header p {
  margin: 7px 0 0;

  font-size: 13px;

  color: #64748b;
}


/* ============================================================
   DELETE BODY
============================================================ */

.delete-piece-body {
  padding: 20px 24px;
}


.delete-piece-body > p:first-child {
  margin: 0;

  font-size: 14px;

  color: #475569;
}


/* ============================================================
   PIECE NAME
============================================================ */

.delete-piece-name {
  display: flex;

  align-items: center;

  gap: 10px;

  padding: 13px;

  margin: 14px 0;

  border: 1px solid #e2e8f0;

  border-radius: 10px;

  background: #f8fafc;

  color: #1e293b;
}


.delete-piece-name span {
  font-size: 20px;
}


.delete-piece-name strong {
  overflow: hidden;

  text-overflow: ellipsis;

  white-space: nowrap;

  font-size: 14px;
}


/* ============================================================
   WARNING
============================================================ */

.delete-piece-warning {
  margin: 16px 0 0;

  padding: 11px 12px;

  border-left: 3px solid #f59e0b;

  border-radius: 6px;

  background: #fffbeb;

  font-size: 12px;

  line-height: 1.55;

  color: #92400e;
}


/* ============================================================
   ERROR
============================================================ */

.delete-piece-error {
  display: flex;

  gap: 8px;

  align-items: flex-start;

  margin-top: 12px;

  padding: 11px 12px;

  border: 1px solid #fecaca;

  border-radius: 8px;

  background: #fff1f2;

  color: #b91c1c;

  font-size: 13px;

  line-height: 1.5;
}


/* ============================================================
   DELETE FOOTER
   BOUTONS AKAiky kokoa
============================================================ */

.modal-delete-piece .modal-footer {
  display: flex;

  justify-content: flex-end;

  align-items: center;

  gap: 9px;

  padding: 13px 20px;

  border-top: 1px solid #e2e8f0;

  background: #ffffff;
}


/* ANNULER */

.modal-delete-piece .btn-light {
  min-width: 105px;

  height: 40px;

  display: inline-flex;

  align-items: center;
  justify-content: center;

  border: 1px solid #cbd5e1;

  border-radius: 9px;

  background: #ffffff;

  color: #475569;

  font-weight: 600;

  cursor: pointer;

  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}


.modal-delete-piece .btn-light:hover:not(:disabled) {
  background: #f8fafc;

  border-color: #94a3b8;

  transform: translateY(-1px);
}


/* ============================================================
   BOUTON SUPPRIMER FOOTER
============================================================ */

.modal-delete-piece .btn-danger {
  min-width: 130px;

  height: 40px;

  display: inline-flex;

  align-items: center;
  justify-content: center;

  gap: 8px;

  border: 1px solid #dc2626;

  border-radius: 9px;

  padding: 0 17px;

  background: #dc2626;

  color: #ffffff;

  font-size: 14px;

  font-weight: 600;

  cursor: pointer;

  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}


/* HOVER */

.modal-delete-piece .btn-danger:hover:not(:disabled) {
  background: #b91c1c;

  border-color: #b91c1c;

  transform: translateY(-1px);

  box-shadow:
    0 5px 14px
    rgba(220, 38, 38, 0.25);
}


/* ACTIVE */

.modal-delete-piece .btn-danger:active:not(:disabled) {
  transform: translateY(0);
}


/* DISABLED */

.modal-delete-piece .btn-danger:disabled {
  opacity: 0.6;

  cursor: not-allowed;

  box-shadow: none;
}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 600px) {

  .modal-piece-manager {
    width: 95vw;

    max-height: 90vh;
  }


  .piece-manager-body {
    padding: 18px;
  }


  /* AJOUT */

  .piece-input-row {
    flex-direction: column;

    align-items: stretch;
  }


  .btn-piece-add {
    width: 100%;
  }


  /* LIST */

  .piece-list {
    max-height: 250px;
  }


  /* FOOTER */

  .modal-piece-manager .modal-footer {
    padding: 13px 18px;
  }


  /* DELETE MODAL */

  .modal-delete-piece {
    width: 94vw;
  }


  .delete-piece-header {
    padding: 22px 18px 15px;
  }


  .delete-piece-body {
    padding: 18px;
  }


  .modal-delete-piece .modal-footer {
    padding: 12px 18px;

    gap: 8px;
  }


  .modal-delete-piece .btn-light,
  .modal-delete-piece .btn-danger {
    flex: 1;

    min-width: 0;
  }

}
</style>