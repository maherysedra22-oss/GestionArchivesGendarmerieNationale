<template>
  <div class="users-page">
    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->
    <header class="page-header">
      <div class="header-content">
        <div class="header-title-group">
          <div class="eyebrow">
            <ShieldCheck :size="15" />
            Administration
          </div>

          <h1>Gestion des utilisateurs</h1>

          <p>
            Gérez les comptes utilisateurs, les rôles, les grades et les
            autorisations d'accès au système.
          </p>
        </div>

        <button v-if="canCreate" class="btn btn-primary btn-new-user" @click="openCreateModal">
          <UserPlus :size="19" />
          <span>Nouvel utilisateur</span>
        </button>
      </div>
    </header>

    <!-- =========================================================
         STATS
    ========================================================== -->
    <section class="stats-grid">
      <article class="stat-card stat-total">
        <div class="stat-icon">
          <Users :size="22" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Total utilisateurs</span>
          <strong>{{ totalUsers }}</strong>
          <small>Comptes enregistrés</small>
        </div>
      </article>

      <article class="stat-card stat-active">
        <div class="stat-icon">
          <UserCheck :size="22" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Utilisateurs actifs</span>
          <strong>{{ activeUsers }}</strong>
          <small>Sur la page actuelle</small>
        </div>
      </article>

      <article class="stat-card stat-inactive">
        <div class="stat-icon">
          <UserX :size="22" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Utilisateurs inactifs</span>
          <strong>{{ inactiveUsers }}</strong>
          <small>Sur la page actuelle</small>
        </div>
      </article>

      <article class="stat-card stat-admin">
        <div class="stat-icon">
          <ShieldCheck :size="22" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Administrateurs</span>
          <strong>{{ adminUsers }}</strong>
          <small>Sur la page actuelle</small>
        </div>
      </article>
    </section>

    <!-- =========================================================
         FILTERS
    ========================================================== -->
    <section class="filters-card">
      <div class="filters-header">
        <div class="filters-title">
          <div class="filters-title-icon">
            <SlidersHorizontal :size="18" />
          </div>

          <div>
            <h2>Recherche et filtres</h2>
            <p>Filtrez rapidement les comptes utilisateurs.</p>
          </div>
        </div>

        <button
          class="btn btn-outline refresh-btn"
          :disabled="loading"
          @click="refreshUsers"
        >
          <RefreshCw :class="{ spinning: loading }" :size="17" />
          Actualiser
        </button>
      </div>

      <div class="filters-grid">
        <div class="field filter-search">
          <label for="search">
            <Search :size="14" />
            Rechercher
          </label>

          <div class="input-wrap">
            <Search :size="17" />
            <input
              id="search"
              v-model="search"
              type="text"
              placeholder="Nom, prénom, matricule, email..."
              @keyup.enter="applyFilters"
            />
            <button
              v-if="search"
              class="input-clear"
              type="button"
              title="Effacer"
              @click="search = ''"
            >
              <X :size="15" />
            </button>
          </div>
        </div>

        <div class="field">
          <label for="role-filter">
            <Shield :size="14" />
            Rôle
          </label>

          <select id="role-filter" v-model="roleFilter">
            <option value="">Tous les rôles</option>
            <option
              v-for="role in roles"
              :key="role.id_role"
              :value="String(role.id_role)"
            >
              {{ role.nom_role }}
            </option>
          </select>
        </div>

        <div class="field">
          <label for="grade-filter">
            <BadgeCheck :size="14" />
            Grade
          </label>

          <select id="grade-filter" v-model="gradeFilter">
            <option value="">Tous les grades</option>
            <option
              v-for="grade in grades"
              :key="grade.id_grade"
              :value="String(grade.id_grade)"
            >
              {{ grade.nom_grade }}
            </option>
          </select>
        </div>

        <div class="field">
          <label for="status-filter">
            <Power :size="14" />
            Statut
          </label>

          <select id="status-filter" v-model="statusFilter">
            <option value="">Tous les statuts</option>
            <option value="actif">Actifs</option>
            <option value="inactif">Inactifs</option>
          </select>
        </div>

        <div class="filter-actions">
          <button class="btn btn-filter" @click="applyFilters">
            <Search :size="17" />
            Rechercher
          </button>

          <button class="btn btn-reset" @click="resetFilters">
            <RotateCcw :size="16" />
            Réinitialiser
          </button>
        </div>
      </div>
    </section>

    <!-- =========================================================
         TABLE
    ========================================================== -->
    <section class="table-card">
      <div class="table-header">
        <div>
          <span class="section-eyebrow">Comptes</span>
          <h2>Liste des utilisateurs</h2>
        </div>

        <div class="result-count">
          <span>{{ total }}</span>
          utilisateur{{ total > 1 ? 's' : '' }}
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <div class="skeleton-table">
          <div
            v-for="n in 7"
            :key="n"
            class="skeleton-row"
          >
            <span class="skeleton skeleton-avatar"></span>
            <span class="skeleton skeleton-large"></span>
            <span class="skeleton skeleton-medium"></span>
            <span class="skeleton skeleton-medium"></span>
            <span class="skeleton skeleton-small"></span>
            <span class="skeleton skeleton-actions"></span>
          </div>
        </div>
      </div>

      <!-- Desktop -->
      <div v-else-if="users.length" class="table-responsive">
        <table class="users-table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Matricule</th>
              <th>Rôle</th>
              <th>Grade</th>
              <th>Statut</th>
              <th>Dernière connexion</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="user in users" :key="user.id_utilisateur">
              <td>
                <div class="user-cell">
                  <div class="avatar">
                    {{ getInitials(user) }}
                  </div>

                  <div class="user-identity">
                    <strong>{{ getFullName(user) }}</strong>
                    <span>{{ user.email || '—' }}</span>
                  </div>
                </div>
              </td>

              <td>
                <span class="matricule">
                  <Hash :size="14" />
                  {{ user.matricule || '—' }}
                </span>
              </td>

              <td>
                <span class="role-badge">
                  <Shield :size="13" />
                  {{ getRoleName(user) }}
                </span>
              </td>

              <td>
                <span class="grade-text">
                  <BadgeCheck :size="15" />
                  {{ getGradeName(user) }}
                </span>
              </td>

              <td>
                <span
                  class="status-badge"
                  :class="user.statut ? 'status-active' : 'status-inactive'"
                >
                  <span class="status-dot"></span>
                  {{ user.statut ? 'Actif' : 'Inactif' }}
                </span>
              </td>

              <td>
                <span class="date-cell">
                  <Clock3 :size="14" />
                  {{ formatDate(user.derniere_connexion) }}
                </span>
              </td>

              <td>
                <div class="action-buttons">
                  <button
                    v-if="canView"
                    class="icon-btn icon-view"
                    title="Voir"
                    aria-label="Voir"
                    @click="openDetailModal(user)"
                  >
                    <Eye :size="17" />
                  </button>

                  <button
                    v-if="canUpdate"
                    class="icon-btn icon-edit"
                    title="Modifier"
                    aria-label="Modifier"
                    @click="openEditModal(user)"
                  >
                    <Pencil :size="17" />
                  </button>

                  <button
                    v-if="canUpdate"
                    type="button"
                    class="icon-btn icon-password"
                    title="Réinitialiser le mot de passe"
                    aria-label="Réinitialiser le mot de passe"
                    @click="openResetPasswordModal(user)"
                  >
                    <LockKeyhole :size="16" />
                  </button>

                  <button
                    v-if="canUpdate && !isCurrentUser(user)"
                    class="icon-btn"
                    :class="user.statut ? 'icon-disable' : 'icon-enable'"
                    :title="user.statut ? 'Désactiver' : 'Activer'"
                    :aria-label="user.statut ? 'Désactiver' : 'Activer'"
                    @click="openStatusModal(user)"
                  >
                    <Power :size="17" />
                  </button>

                  <button
                    v-if="canDelete && !isCurrentUser(user)"
                    type="button"
                    class="icon-btn icon-delete"
                    title="Supprimer l'utilisateur"
                    aria-label="Supprimer l'utilisateur"
                    @click="openDeleteModal(user)"
                  >
                    <Trash2 :size="17" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile -->
      <div v-else-if="users.length" class="mobile-users">
        <article
          v-for="user in users"
          :key="user.id_utilisateur"
          class="mobile-user-card"
        >
          <div class="mobile-user-top">
            <div class="user-cell">
              <div class="avatar">
                {{ getInitials(user) }}
              </div>

              <div class="user-identity">
                <strong>{{ getFullName(user) }}</strong>
                <span>{{ user.email || '—' }}</span>
              </div>
            </div>

            <span
              class="status-badge"
              :class="user.statut ? 'status-active' : 'status-inactive'"
            >
              <span class="status-dot"></span>
              {{ user.statut ? 'Actif' : 'Inactif' }}
            </span>
          </div>

          <div class="mobile-info-grid">
            <div>
              <span>Matricule</span>
              <strong>{{ user.matricule || '—' }}</strong>
            </div>

            <div>
              <span>Rôle</span>
              <strong>{{ getRoleName(user) }}</strong>
            </div>

            <div>
              <span>Grade</span>
              <strong>{{ getGradeName(user) }}</strong>
            </div>

            <div>
              <span>Connexion</span>
              <strong>{{ formatDate(user.derniere_connexion) }}</strong>
            </div>
          </div>

          <div class="mobile-actions">
            <button v-if="canView" class="mobile-action view" @click="openDetailModal(user)">
              <Eye :size="16" />
              Voir
            </button>

            <button v-if="canUpdate" class="mobile-action edit" @click="openEditModal(user)">
              <Pencil :size="16" />
              Modifier
            </button>

            <button
              v-if="canUpdate"
              type="button"
              class="mobile-action password"
              @click="openResetPasswordModal(user)"
            >
              <KeyRound :size="16" />
              Mot de passe
            </button>
            
            <button
              v-if="canUpdate && !isCurrentUser(user)"
              class="mobile-action"
              :class="user.statut ? 'disable' : 'enable'"
              @click="openStatusModal(user)"
            >
              <Power :size="16" />
              {{ user.statut ? 'Désactiver' : 'Activer' }}
            </button>


            <button
              v-if="canDelete && !isCurrentUser(user)"
              type="button"
              class="mobile-action delete"
              @click="openDeleteModal(user)"
            >
              <Trash2 :size="16" />
              Supprimer
            </button>
          </div>
        </article>
      </div>

      <!-- Empty -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <Users :size="34" />
        </div>

        <h3>Aucun utilisateur trouvé</h3>

        <p>
          Aucun compte ne correspond aux critères de recherche actuels.
        </p>

        <button class="btn btn-reset" @click="resetFilters">
          <RotateCcw :size="16" />
          Réinitialiser les filtres
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="users.length && totalPages > 0" class="pagination">
        <div class="pagination-info">
          Affichage
          <strong>{{ pagination?.from || 0 }}</strong>
          à
          <strong>{{ pagination?.to || 0 }}</strong>
          sur
          <strong>{{ total }}</strong>
        </div>

        <div class="pagination-controls">
          <select v-model.number="perPage" @change="changePerPage">
            <option :value="10">10 / page</option>
            <option :value="15">15 / page</option>
            <option :value="25">25 / page</option>
            <option :value="50">50 / page</option>
          </select>

          <button
            class="page-btn"
            :disabled="currentPage <= 1"
            @click="goToPage(1)"
          >
            <ChevronsLeft :size="17" />
          </button>

          <button
            class="page-btn"
            :disabled="currentPage <= 1"
            @click="previousPage"
          >
            <ChevronLeft :size="17" />
          </button>

          <button
            v-for="page in visiblePages"
            :key="page"
            class="page-number"
            :class="{ active: page === currentPage }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>

          <button
            class="page-btn"
            :disabled="currentPage >= totalPages"
            @click="nextPage"
          >
            <ChevronRight :size="17" />
          </button>

          <button
            class="page-btn"
            :disabled="currentPage >= totalPages"
            @click="goToPage(totalPages)"
          >
            <ChevronsRight :size="17" />
          </button>
        </div>
      </div>
    </section>

    <!-- =========================================================
         1. CREATE / EDIT MODAL
    ========================================================== -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div
          v-if="showFormModal"
          class="modal-overlay base-modal-layer"
          @mousedown.self="closeFormModal"
        >
          <div
            class="modal form-modal"
            :class="isEditMode ? 'modal-edit' : 'modal-create'"
            role="dialog"
            aria-modal="true"
          >
            <div class="form-modal-header">
              <div class="form-header-left">
                <div class="form-header-icon">
                  <UserCog v-if="isEditMode" :size="25" />
                  <UserPlus v-else :size="25" />
                </div>

                <div>
                  <span class="modal-eyebrow">
                    Administration des comptes
                  </span>

                  <h2>
                    {{
                      isEditMode
                        ? 'Modifier l’utilisateur'
                        : 'Nouvel utilisateur'
                    }}
                  </h2>

                  <p>
                    {{
                      isEditMode
                        ? 'Mettez à jour les informations et les accès du compte.'
                        : 'Créez un nouveau compte utilisateur avec ses droits d’accès.'
                    }}
                  </p>
                </div>
              </div>

              <button
                class="modal-close"
                type="button"
                aria-label="Fermer"
                @click="closeFormModal"
              >
                <X :size="20" />
              </button>
            </div>

            <div class="form-modal-body">
              <!-- Personal -->
              <section class="form-section">
                <div class="form-section-title">
                  <div class="section-icon blue">
                    <CircleUserRound :size="18" />
                  </div>

                  <div>
                    <h3>Informations personnelles</h3>
                    <p>Identité et coordonnées professionnelles.</p>
                  </div>
                </div>

                <div class="form-grid">
                  <div class="field">
                    <label for="form-matricule">
                      Matricule <span>*</span>
                    </label>

                    <div
                      class="input-wrap"
                      :class="{ 'has-error': formErrors.matricule }"
                    >
                      <Hash :size="17" />
                      <input
                        id="form-matricule"
                        v-model="form.matricule"
                        type="text"
                        maxlength="30"
                        placeholder="Ex : ADM001"
                      />
                    </div>

                    <small v-if="formErrors.matricule" class="field-error">
                      {{ formErrors.matricule }}
                    </small>
                  </div>

                  <div class="field">
                    <label for="form-email">
                      Adresse e-mail <span>*</span>
                    </label>

                    <div
                      class="input-wrap"
                      :class="{ 'has-error': formErrors.email }"
                    >
                      <Mail :size="17" />
                      <input
                        id="form-email"
                        v-model="form.email"
                        type="email"
                        maxlength="150"
                        placeholder="exemple@gendarmerie.mg"
                      />
                    </div>

                    <small v-if="formErrors.email" class="field-error">
                      {{ formErrors.email }}
                    </small>
                  </div>

                  <div class="field">
                    <label for="form-nom">
                      Nom <span>*</span>
                    </label>

                    <div
                      class="input-wrap"
                      :class="{ 'has-error': formErrors.nom }"
                    >
                      <UserRound :size="17" />
                      <input
                        id="form-nom"
                        v-model="form.nom"
                        type="text"
                        maxlength="100"
                        placeholder="Nom de famille"
                      />
                    </div>

                    <small v-if="formErrors.nom" class="field-error">
                      {{ formErrors.nom }}
                    </small>
                  </div>

                  <div class="field">
                    <label for="form-prenom">
                      Prénom <span>*</span>
                    </label>

                    <div
                      class="input-wrap"
                      :class="{ 'has-error': formErrors.prenom }"
                    >
                      <UserRound :size="17" />
                      <input
                        id="form-prenom"
                        v-model="form.prenom"
                        type="text"
                        maxlength="100"
                        placeholder="Prénom"
                      />
                    </div>

                    <small v-if="formErrors.prenom" class="field-error">
                      {{ formErrors.prenom }}
                    </small>
                  </div>

                  <div class="field field-full">
                    <label for="form-poste">
                      Poste / Fonction <span>*</span>
                    </label>

                    <div
                      class="input-wrap"
                      :class="{ 'has-error': formErrors.poste_fonction }"
                    >
                      <BriefcaseBusiness :size="17" />
                      <input
                        id="form-poste"
                        v-model="form.poste_fonction"
                        type="text"
                        maxlength="150"
                        placeholder="Ex : Administrateur du système"
                      />
                    </div>

                    <small
                      v-if="formErrors.poste_fonction"
                      class="field-error"
                    >
                      {{ formErrors.poste_fonction }}
                    </small>
                  </div>
                </div>
              </section>

              <!-- Access -->
              <section class="form-section access-section">
                <div class="form-section-title">
                  <div class="section-icon purple">
                    <LockKeyhole :size="18" />
                  </div>

                  <div>
                    <h3>Accès et autorisations</h3>
                    <p>Définissez le grade, le rôle et l’état du compte.</p>
                  </div>
                </div>

                <div class="form-grid">
                  <div class="field">
                    <label for="form-grade">
                      Grade <span>*</span>
                    </label>

                    <div
                      class="select-wrap"
                      :class="{ 'has-error': formErrors.id_grade }"
                    >
                      <BadgeCheck :size="17" />

                      <select id="form-grade" v-model="form.id_grade">
                        <option value="">Sélectionner un grade</option>
                        <option
                          v-for="grade in grades"
                          :key="grade.id_grade"
                          :value="String(grade.id_grade)"
                        >
                          {{ grade.nom_grade }}
                        </option>
                      </select>
                    </div>

                    <small v-if="formErrors.id_grade" class="field-error">
                      {{ formErrors.id_grade }}
                    </small>
                  </div>

                  <div class="field">
                    <label for="form-role">
                      Rôle <span>*</span>
                    </label>

                    <div
                      class="select-wrap"
                      :class="{ 'has-error': formErrors.id_role }"
                    >
                      <Shield :size="17" />

                      <select id="form-role" v-model="form.id_role">
                        <option value="">Sélectionner un rôle</option>
                        <option
                          v-for="role in roles"
                          :key="role.id_role"
                          :value="String(role.id_role)"
                        >
                          {{ role.nom_role }}
                        </option>
                      </select>
                    </div>

                    <small v-if="formErrors.id_role" class="field-error">
                      {{ formErrors.id_role }}
                    </small>
                  </div>

                  <div class="field field-full">
                    <div class="switch-card">
                      <div class="switch-card-icon active-icon">
                        <Power :size="19" />
                      </div>

                      <div class="switch-card-content">
                        <strong>Compte actif</strong>
                        <span>
                          Autoriser l’utilisateur à accéder au système.
                        </span>
                      </div>

                      <label class="switch">
                        <input v-model="form.statut" type="checkbox" />
                        <span class="switch-slider"></span>
                      </label>
                    </div>
                  </div>

                  <div class="field field-full">
                    <div class="switch-card password-switch">
                      <div class="switch-card-icon password-icon">
                        <KeyRound :size="19" />
                      </div>

                      <div class="switch-card-content">
                        <strong>Changement de mot de passe requis</strong>
                        <span>
                          Demander à l’utilisateur de modifier son mot de
                          passe lors de sa prochaine connexion.
                        </span>
                      </div>

                      <label class="switch">
                        <input
                          v-model="form.doit_changer_mdp"
                          type="checkbox"
                        />
                        <span class="switch-slider"></span>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="security-note">
                  <div class="security-note-icon">
                    <Info :size="18" />
                  </div>

                  <div>
                    <strong>Gestion du mot de passe</strong>
                    <p>
                      Le mot de passe initial peut être généré par le système.
                      Il pourra ensuite être réinitialisé depuis la gestion
                      du compte.
                    </p>
                  </div>
                </div>
              </section>

              <div v-if="formGeneralError" class="form-general-error">
                <AlertTriangle :size="18" />
                <span>{{ formGeneralError }}</span>
              </div>
            </div>

            <div class="form-modal-footer">
              <button
                class="btn btn-cancel"
                type="button"
                :disabled="saving"
                @click="closeFormModal"
              >
                <X :size="17" />
                Annuler
              </button>

              <button
                class="btn form-submit-btn"
                :class="isEditMode ? 'btn-edit-submit' : 'btn-create-submit'"
                type="button"
                :disabled="saving || loadingReferences"
                @click="saveUser"
              >
                <LoaderCircle v-if="saving" class="spinning" :size="18" />
                <Save v-else-if="isEditMode" :size="18" />
                <UserPlus v-else :size="18" />

                {{
                  saving
                    ? 'Enregistrement...'
                    : isEditMode
                      ? 'Enregistrer les modifications'
                      : 'Créer l’utilisateur'
                }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- =========================================================
         2. DETAIL / VOIR
    ========================================================== -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div
          v-if="showDetailModal"
          class="modal-overlay base-modal-layer"
          @mousedown.self="closeDetailModal"
        >
          <div
            class="modal detail-modal"
            role="dialog"
            aria-modal="true"
          >
            <div class="detail-banner">
              <div class="detail-banner-pattern"></div>

              <button
                class="modal-close detail-close"
                type="button"
                @click="closeDetailModal"
              >
                <X :size="20" />
              </button>

              <div class="detail-profile">
                <div class="detail-avatar">
                  {{ getInitials(selectedUser) }}
                </div>

                <div class="detail-profile-info">
                  <span class="modal-eyebrow">
                    Profil utilisateur
                  </span>

                  <h2>{{ getFullName(selectedUser) }}</h2>

                  <div class="detail-meta">
                    <span>
                      <Hash :size="13" />
                      {{ selectedUser?.matricule || '—' }}
                    </span>

                    <span
                      class="detail-status"
                      :class="
                        selectedUser?.statut
                          ? 'detail-status-active'
                          : 'detail-status-inactive'
                      "
                    >
                      <span class="status-dot"></span>
                      {{
                        selectedUser?.statut
                          ? 'Compte actif'
                          : 'Compte inactif'
                      }}
                    </span>
                  </div>
                </div>

                <button
                  v-if="canUpdate"
                  class="detail-edit-button"
                  type="button"
                  @click="editFromDetail"
                >
                  <Pencil :size="16" />
                  Modifier
                </button>
              </div>
            </div>

            <div class="detail-body">
              <section class="detail-section">
                <div class="detail-section-heading">
                  <div class="detail-heading-icon blue">
                    <CircleUserRound :size="18" />
                  </div>

                  <div>
                    <h3>Informations personnelles</h3>
                    <p>Informations principales du compte.</p>
                  </div>
                </div>

                <div class="detail-grid">
                  <div class="detail-info-card">
                    <div class="detail-info-icon blue">
                      <UserRound :size="17" />
                    </div>

                    <div>
                      <span>Nom complet</span>
                      <strong>{{ getFullName(selectedUser) }}</strong>
                    </div>
                  </div>

                  <div class="detail-info-card">
                    <div class="detail-info-icon cyan">
                      <Hash :size="17" />
                    </div>

                    <div>
                      <span>Matricule</span>
                      <strong>{{ selectedUser?.matricule || '—' }}</strong>
                    </div>
                  </div>

                  <div class="detail-info-card">
                    <div class="detail-info-icon purple">
                      <Mail :size="17" />
                    </div>

                    <div>
                      <span>Adresse e-mail</span>
                      <strong class="break-text">
                        {{ selectedUser?.email || '—' }}
                      </strong>
                    </div>
                  </div>

                  <div class="detail-info-card">
                    <div class="detail-info-icon orange">
                      <BriefcaseBusiness :size="17" />
                    </div>

                    <div>
                      <span>Poste / Fonction</span>
                      <strong>
                        {{ selectedUser?.poste_fonction || '—' }}
                      </strong>
                    </div>
                  </div>
                </div>
              </section>

              <section class="detail-section">
                <div class="detail-section-heading">
                  <div class="detail-heading-icon purple">
                    <Shield :size="18" />
                  </div>

                  <div>
                    <h3>Accès et autorisations</h3>
                    <p>Rôle et grade associés à ce compte.</p>
                  </div>
                </div>

                <div class="access-detail-grid">
                  <div class="access-detail-card role-card">
                    <div class="access-card-icon">
                      <ShieldCheck :size="21" />
                    </div>

                    <div>
                      <span>Rôle</span>
                      <strong>{{ getRoleName(selectedUser) }}</strong>
                    </div>
                  </div>

                  <div class="access-detail-card grade-card">
                    <div class="access-card-icon">
                      <BadgeCheck :size="21" />
                    </div>

                    <div>
                      <span>Grade</span>
                      <strong>{{ getGradeName(selectedUser) }}</strong>
                    </div>
                  </div>

                  <div class="access-detail-card status-card">
                    <div class="access-card-icon">
                      <Power :size="21" />
                    </div>

                    <div>
                      <span>État du compte</span>
                      <strong>
                        {{
                          selectedUser?.statut
                            ? 'Actif'
                            : 'Inactif'
                        }}
                      </strong>
                    </div>
                  </div>

                  <div class="access-detail-card password-card">
                    <div class="access-card-icon">
                      <KeyRound :size="21" />
                    </div>

                    <div>
                      <span>Sécurité</span>
                      <strong>
                        {{
                          selectedUser?.doit_changer_mdp
                            ? 'Changement requis'
                            : 'Mot de passe confirmé'
                        }}
                      </strong>
                    </div>
                  </div>
                </div>
              </section>

              <section class="detail-section activity-section">
                <div class="detail-section-heading">
                  <div class="detail-heading-icon green">
                    <CalendarDays :size="18" />
                  </div>

                  <div>
                    <h3>Activité du compte</h3>
                    <p>Informations temporelles disponibles.</p>
                  </div>
                </div>

                <div class="activity-grid">
                  <div class="activity-item">
                    <Clock3 :size="17" />
                    <div>
                      <span>Dernière connexion</span>
                      <strong>
                        {{ formatDate(selectedUser?.derniere_connexion) }}
                      </strong>
                    </div>
                  </div>

                  <div class="activity-item">
                    <CalendarDays :size="17" />
                    <div>
                      <span>Compte créé le</span>
                      <strong>
                        {{ formatDate(selectedUser?.created_at) }}
                      </strong>
                    </div>
                  </div>
                </div>
              </section>
            </div>

            <div class="detail-footer">
              <button
                class="btn btn-cancel"
                type="button"
                @click="closeDetailModal"
              >
                Fermer
              </button>

              <button
                v-if="canUpdate"
                class="btn btn-detail-edit"
                type="button"
                @click="editFromDetail"
              >
                <Pencil :size="17" />
                Modifier cet utilisateur
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- =========================================================
         3. ACTIVER / DESACTIVER
    ========================================================== -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div
          v-if="showStatusModal"
          class="modal-overlay confirm-layer"
          @mousedown.self="closeStatusModal"
        >
          <div
            class="confirm-modal"
            :class="
              statusActionIsDisable
                ? 'confirm-disable'
                : 'confirm-enable'
            "
            role="dialog"
            aria-modal="true"
          >
            <div class="confirm-icon">
              <Power :size="29" />
            </div>

            <span class="confirm-label">
              Gestion du compte
            </span>

            <h2>{{ statusActionTitle }}</h2>

            <p class="confirm-description">
              Vous êtes sur le point de
              <strong>
                {{ statusActionIsDisable ? 'désactiver' : 'activer' }}
              </strong>
              le compte de :
            </p>

            <div class="target-user">
              <div class="target-avatar">
                {{ getInitials(statusTarget) }}
              </div>

              <div>
                <strong>{{ getFullName(statusTarget) }}</strong>
                <span>{{ statusTarget?.matricule || '—' }}</span>
              </div>
            </div>

            <div
              class="confirm-message"
              :class="
                statusActionIsDisable
                  ? 'warning-message'
                  : 'success-message'
              "
            >
              <AlertTriangle
                v-if="statusActionIsDisable"
                :size="18"
              />

              <CheckCircle2
                v-else
                :size="18"
              />

              <span>
                {{
                  statusActionIsDisable
                    ? 'Cet utilisateur ne pourra plus se connecter au système tant que son compte restera désactivé.'
                    : 'Cet utilisateur pourra de nouveau accéder au système avec ses identifiants.'
                }}
              </span>
            </div>

            <div class="confirm-actions">
              <button
                class="btn confirm-cancel"
                type="button"
                :disabled="changingStatus"
                @click="closeStatusModal"
              >
                Annuler
              </button>

              <button
                class="btn confirm-status-btn"
                :class="
                  statusActionIsDisable
                    ? 'confirm-orange'
                    : 'confirm-green'
                "
                type="button"
                :disabled="changingStatus"
                @click="confirmChangeStatus"
              >
                <LoaderCircle
                  v-if="changingStatus"
                  class="spinning"
                  :size="17"
                />

                <Power v-else :size="17" />

                {{
                  changingStatus
                    ? 'Traitement...'
                    : statusActionLabel
                }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- =========================================================
         4. SUPPRESSION
    ========================================================== -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div
          v-if="showDeleteModal"
          class="modal-overlay confirm-layer"
          @mousedown.self="closeDeleteModal"
        >
          <div
            class="confirm-modal confirm-delete"
            role="dialog"
            aria-modal="true"
          >
            <div class="confirm-icon delete-confirm-icon">
              <Trash2 :size="29" />
            </div>

            <span class="confirm-label delete-label">
              Suppression du compte
            </span>

            <h2>Supprimer l’utilisateur ?</h2>

            <p class="confirm-description">
              Vous êtes sur le point de supprimer le compte :
            </p>

            <div class="target-user delete-target">
              <div class="target-avatar delete-avatar">
                {{ getInitials(selectedUser) }}
              </div>

              <div>
                <strong>{{ getFullName(selectedUser) }}</strong>
                <span>{{ selectedUser?.matricule || '—' }}</span>
              </div>
            </div>

            <div class="confirm-message danger-message">
              <AlertTriangle :size="19" />

              <div>
                <strong>Attention</strong>
                <span>
                  Cette opération retirera cet utilisateur de
                  l'application. Vérifiez bien le compte avant de
                  confirmer.
                </span>
              </div>
            </div>

            <div class="confirm-actions">
              <button
                class="btn confirm-cancel"
                type="button"
                :disabled="deleting"
                @click="closeDeleteModal"
              >
                Annuler
              </button>

              <button
                class="btn confirm-delete-btn"
                type="button"
                :disabled="deleting"
                @click="confirmDeleteUser"
              >
                <LoaderCircle
                  v-if="deleting"
                  class="spinning"
                  :size="17"
                />

                <Trash2 v-else :size="17" />

                {{ deleting ? 'Suppression...' : 'Supprimer' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>


    <!-- =========================================================
        5. RESET PASSWORD
    ========================================================== -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div
          v-if="showResetPasswordModal"
          class="modal-overlay confirm-layer reset-password-layer"
          @mousedown.self="closeResetPasswordModal"
        >
          <div
            class="reset-password-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reset-password-title"
          >
            <!-- Header -->
            <div class="reset-password-header">
              <div class="reset-password-icon">
                <KeyRound :size="26" />
              </div>

              <button
                class="modal-close reset-close"
                type="button"
                aria-label="Fermer"
                :disabled="resettingPassword"
                @click="closeResetPasswordModal"
              >
                <X :size="20" />
              </button>

              <div>
                <span class="modal-eyebrow">
                  Sécurité du compte
                </span>

                <h2 id="reset-password-title">
                  Réinitialiser le mot de passe
                </h2>

                <p>
                  Définissez un nouveau mot de passe pour cet utilisateur.
                </p>
              </div>
            </div>

            <!-- Target user -->
            <div class="reset-target-user">
              <div class="reset-target-avatar">
                {{ getInitials(resetPasswordTarget) }}
              </div>

              <div class="reset-target-info">
                <strong>
                  {{ getFullName(resetPasswordTarget) }}
                </strong>

                <span>
                  {{ resetPasswordTarget?.matricule || '—' }}
                </span>

                <small>
                  {{ getRoleName(resetPasswordTarget) }}
                </small>
              </div>

              <div class="reset-target-badge">
                <ShieldCheck :size="15" />
                Compte utilisateur
              </div>
            </div>

            <!-- Security information -->
            <div class="reset-security-note">
              <div class="reset-security-note-icon">
                <ShieldAlert :size="19" />
              </div>

              <div>
                <strong>Important</strong>

                <p>
                  L'ancien mot de passe ne sera jamais affiché.
                  Le nouveau mot de passe sera enregistré de manière
                  sécurisée et l'utilisateur devra le modifier lors de
                  sa prochaine connexion.
                </p>
              </div>
            </div>

            <!-- Form -->
            <div class="reset-password-body">

              <!-- New password -->
              <div class="field">
                <label for="reset-password">
                  Nouveau mot de passe
                  <span>*</span>
                </label>

                <div
                  class="input-wrap password-input-wrap"
                  :class="{
                    'has-error':
                      resetPasswordErrors.mot_de_passe
                  }"
                >
                  <KeyRound :size="17" />

                  <input
                    id="reset-password"
                    v-model="resetPasswordForm.mot_de_passe"
                    :type="
                      showResetPassword
                        ? 'text'
                        : 'password'
                    "
                    autocomplete="new-password"
                    maxlength="255"
                    placeholder="Minimum 8 caractères"
                    @keyup.enter="confirmResetPassword"
                  />

                  <button
                    type="button"
                    class="password-toggle"
                    :aria-label="
                      showResetPassword
                        ? 'Masquer le mot de passe'
                        : 'Afficher le mot de passe'
                    "
                    @click="
                      showResetPassword =
                        !showResetPassword
                    "
                  >
                    <EyeOff
                      v-if="showResetPassword"
                      :size="17"
                    />

                    <Eye
                      v-else
                      :size="17"
                    />
                  </button>
                </div>

                <small
                  v-if="resetPasswordErrors.mot_de_passe"
                  class="field-error"
                >
                  {{ resetPasswordErrors.mot_de_passe }}
                </small>
              </div>

              <!-- Confirmation -->
              <div class="field">
                <label for="reset-password-confirmation">
                  Confirmer le nouveau mot de passe
                  <span>*</span>
                </label>

                <div
                  class="input-wrap password-input-wrap"
                  :class="{
                    'has-error':
                      resetPasswordErrors.mot_de_passe_confirmation
                  }"
                >
                  <CheckCircle2 :size="17" />

                  <input
                    id="reset-password-confirmation"
                    v-model="
                      resetPasswordForm.mot_de_passe_confirmation
                    "
                    :type="
                      showResetPasswordConfirmation
                        ? 'text'
                        : 'password'
                    "
                    autocomplete="new-password"
                    maxlength="255"
                    placeholder="Retapez le nouveau mot de passe"
                    @keyup.enter="confirmResetPassword"
                  />

                  <button
                    type="button"
                    class="password-toggle"
                    :aria-label="
                      showResetPasswordConfirmation
                        ? 'Masquer la confirmation'
                        : 'Afficher la confirmation'
                    "
                    @click="
                      showResetPasswordConfirmation =
                        !showResetPasswordConfirmation
                    "
                  >
                    <EyeOff
                      v-if="showResetPasswordConfirmation"
                      :size="17"
                    />

                    <Eye
                      v-else
                      :size="17"
                    />
                  </button>
                </div>

                <small
                  v-if="
                    resetPasswordErrors.mot_de_passe_confirmation
                  "
                  class="field-error"
                >
                  {{
                    resetPasswordErrors
                      .mot_de_passe_confirmation
                  }}
                </small>
              </div>

              <!-- General error -->
              <div
                v-if="resetPasswordGeneralError"
                class="form-general-error"
              >
                <AlertTriangle :size="18" />

                <span>
                  {{ resetPasswordGeneralError }}
                </span>
              </div>

              <!-- Password requirements -->
              <div class="password-requirements">
                <div class="requirements-title">
                  <ShieldCheck :size="16" />
                  Exigences du mot de passe
                </div>

                <div class="requirements-grid">
                  <div
                    :class="{
                      valid:
                        resetPasswordForm.mot_de_passe.length >= 8
                    }"
                  >
                    <CheckCircle2 :size="14" />
                    Au moins 8 caractères
                  </div>

                  <div
                    :class="{
                      valid:
                        resetPasswordForm.mot_de_passe &&
                        resetPasswordForm.mot_de_passe_confirmation &&
                        resetPasswordForm.mot_de_passe ===
                          resetPasswordForm.mot_de_passe_confirmation
                    }"
                  >
                    <CheckCircle2 :size="14" />
                    Les mots de passe correspondent
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="reset-password-footer">
              <button
                class="btn btn-cancel"
                type="button"
                :disabled="resettingPassword"
                @click="closeResetPasswordModal"
              >
                <X :size="17" />
                Annuler
              </button>

              <button
                class="btn reset-password-submit"
                type="button"
                :disabled="resettingPassword"
                @click="confirmResetPassword"
              >
                <LoaderCircle
                  v-if="resettingPassword"
                  class="spinning"
                  :size="18"
                />

                <KeyRound
                  v-else
                  :size="18"
                />

                <span v-if="resettingPassword">
                  Enregistrement...
                </span>

                <span v-else-if="resetPasswordClicked">
                  Enregistrer le nouveau mot de passe
                </span>

                <span v-else>
                  Réinitialiser le mot de passe
                </span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- =========================================================
         TOAST
    ========================================================== -->
    <Teleport to="body">
      <TransitionGroup
        name="toast"
        tag="div"
        class="toast-container"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="toast-item"
          :class="`toast-${toast.type}`"
        >
          <div class="toast-icon">
            <CheckCircle2
              v-if="toast.type === 'success'"
              :size="19"
            />

            <AlertTriangle
              v-else-if="toast.type === 'error'"
              :size="19"
            />

            <AlertTriangle
              v-else-if="toast.type === 'warning'"
              :size="19"
            />

            <Info v-else :size="19" />
          </div>

          <div class="toast-content">
            <strong>{{ toast.title }}</strong>
            <span>{{ toast.message }}</span>
          </div>

          <button
            class="toast-close"
            type="button"
            @click="removeToast(toast.id)"
          >
            <X :size="15" />
          </button>
        </div>
      </TransitionGroup>
    </Teleport>
  </div>
</template>

<script setup>
import {
  ref,
  computed,
  onMounted,
  onUnmounted,
  watch
} from 'vue'

import { useAuthStore } from '@/stores/auth'

import {
  Users,
  UserPlus,
  UserCheck,
  UserX,
  ShieldCheck,
  Search,
  SlidersHorizontal,
  RotateCcw,
  RefreshCw,
  Eye,
  Pencil,
  Power,
  Trash2,
  X,
  Save,
  LoaderCircle,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  Mail,
  BriefcaseBusiness,
  BadgeCheck,
  Shield,
  LockKeyhole,
  AlertTriangle,
  CheckCircle2,
  Info,
  CircleUserRound,
  CalendarDays,
  Clock3,
  KeyRound,
  Hash,
  UserRound,
  UserCog,
  EyeOff,
  ShieldAlert
} from 'lucide-vue-next'

const authStore = useAuthStore()

// ============================================================
// PERMISSIONS
// ============================================================

const canView = computed(() =>
  authStore.hasPermission('utilisateurs.view')
)

const canCreate = computed(() =>
  authStore.hasPermission('utilisateurs.create')
)

const canUpdate = computed(() =>
  authStore.hasPermission('utilisateurs.update')
)

const canDelete = computed(() =>
  authStore.hasPermission('utilisateurs.delete')
)

// ============================================================
// API
// ============================================================

const API_URL = 'http://127.0.0.1:8000/api'

function getToken() {
  return (
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token') ||
    ''
  )
}

async function apiFetch(endpoint, options = {}) {
  const token = getToken()

  const headers = {
    Accept: 'application/json',
    ...options.headers
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  const request = {
    ...options,
    headers
  }

  if (
    request.body &&
    typeof request.body !== 'string' &&
    !(request.body instanceof FormData)
  ) {
    headers['Content-Type'] = 'application/json'
    request.body = JSON.stringify(request.body)
  }

  const response = await fetch(
    `${API_URL}${endpoint}`,
    request
  )

  const text = await response.text()

  let data = {}

  try {
    data = text ? JSON.parse(text) : {}
  } catch {
    data = {
      message: text
    }
  }

  if (response.status === 401) {
    localStorage.removeItem('auth_token')
    sessionStorage.removeItem('auth_token')

    localStorage.removeItem('utilisateur')
    sessionStorage.removeItem('utilisateur')

    window.location.href = '/login'

    throw new Error('Session expirée.')
  }

  if (!response.ok) {
    const error = new Error(
      data.message ||
      `Erreur HTTP ${response.status}`
    )

    error.status = response.status
    error.data = data

    throw error
  }

  return data
}

// ============================================================
// STATE
// ============================================================

const users = ref([])
const roles = ref([])
const grades = ref([])

const loading = ref(false)
const loadingReferences = ref(false)

const saving = ref(false)
const changingStatus = ref(false)
const deleting = ref(false)

// ============================================================
// FILTERS
// ============================================================

const search = ref('')
const roleFilter = ref('')
const gradeFilter = ref('')
const statusFilter = ref('')

const currentPage = ref(1)
const perPage = ref(15)
const totalPages = ref(1)
const total = ref(0)

const pagination = ref(null)

// ============================================================
// MODALS
// ============================================================

const showFormModal = ref(false)
const showDetailModal = ref(false)
const showStatusModal = ref(false)
const showDeleteModal = ref(false)
const showResetPasswordModal = ref(false)

const resettingPassword = ref(false)
const resetPasswordClicked = ref(false)

const resetPasswordTarget = ref(null)

const resetPasswordForm = ref({
  mot_de_passe: '',
  mot_de_passe_confirmation: ''
})

const resetPasswordErrors = ref({})
const resetPasswordGeneralError = ref('')

const showResetPassword = ref(false)
const showResetPasswordConfirmation = ref(false)

const isEditMode = ref(false)

const selectedUser = ref(null)
const statusTarget = ref(null)

// ============================================================
// FORM
// ============================================================

const emptyForm = () => ({
  matricule: '',
  nom: '',
  prenom: '',
  poste_fonction: '',
  email: '',
  id_grade: '',
  id_role: '',
  statut: true,
  doit_changer_mdp: true
})

const form = ref(emptyForm())

const formErrors = ref({})
const formGeneralError = ref('')

// ============================================================
// TOASTS
// ============================================================

const toasts = ref([])

let toastCounter = 0

function showToast(
  message,
  type = 'success',
  title = ''
) {
  const id = ++toastCounter

  const defaultTitles = {
    success: 'Opération réussie',
    error: 'Une erreur est survenue',
    warning: 'Attention',
    info: 'Information'
  }

  toasts.value.push({
    id,
    message,
    type,
    title:
      title ||
      defaultTitles[type] ||
      'Information'
  })

  window.setTimeout(() => {
    removeToast(id)
  }, 4500)
}

function removeToast(id) {
  toasts.value = toasts.value.filter(
    toast => toast.id !== id
  )
}

// ============================================================
// STATS
// ============================================================

const totalUsers = computed(() =>
  total.value
)

const activeUsers = computed(() =>
  users.value.filter(
    user => !!user.statut
  ).length
)

const inactiveUsers = computed(() =>
  users.value.filter(
    user => !user.statut
  ).length
)

const adminUsers = computed(() =>
  users.value.filter(
    user => isAdmin(user)
  ).length
)

// ============================================================
// STATUS COMPUTED
// ============================================================

const statusActionLabel = computed(() =>
  statusTarget.value?.statut
    ? 'Désactiver'
    : 'Activer'
)

const statusActionTitle = computed(() =>
  statusTarget.value?.statut
    ? 'Désactiver le compte ?'
    : 'Activer le compte ?'
)

const statusActionIsDisable = computed(() =>
  !!statusTarget.value?.statut
)

// ============================================================
// MODAL STATE
// ============================================================

const anyModalOpen = computed(() =>
  showFormModal.value ||
  showDetailModal.value ||
  showStatusModal.value ||
  showDeleteModal.value ||
  showResetPasswordModal.value
)

watch(anyModalOpen, open => {
  document.body.style.overflow = open
    ? 'hidden'
    : ''
})

// ============================================================
// REFERENCES
// ============================================================

async function loadReferences() {
  loadingReferences.value = true

  try {
    const [
      roleResult,
      gradeResult
    ] = await Promise.all([
      apiFetch(
        '/utilisateurs-references/roles'
      ),
      apiFetch(
        '/utilisateurs-references/grades'
      )
    ])

    roles.value =
      Array.isArray(roleResult.data)
        ? roleResult.data
        : []

    grades.value =
      Array.isArray(gradeResult.data)
        ? gradeResult.data
        : []

  } catch (error) {
    showToast(
      error.message ||
      'Impossible de charger les références.',
      'error'
    )
  } finally {
    loadingReferences.value = false
  }
}

// ============================================================
// USERS
// ============================================================

async function loadUsers() {
  loading.value = true

  try {
    const params = new URLSearchParams()

    if (search.value.trim()) {
      params.set(
        'search',
        search.value.trim()
      )
    }

    if (roleFilter.value) {
      params.set(
        'id_role',
        roleFilter.value
      )
    }

    if (gradeFilter.value) {
      params.set(
        'id_grade',
        gradeFilter.value
      )
    }

    if (statusFilter.value !== '') {
      params.set(
        'statut',
        statusFilter.value === 'actif'
          ? '1'
          : '0'
      )
    }

    params.set(
      'page',
      String(currentPage.value)
    )

    params.set(
      'per_page',
      String(perPage.value)
    )

    params.set(
      'sort',
      'created_at'
    )

    params.set(
      'direction',
      'desc'
    )

    const result = await apiFetch(
      `/utilisateurs?${params.toString()}`
    )

    users.value =
      Array.isArray(result.data)
        ? result.data
        : []

    pagination.value =
      result.pagination || null

    total.value =
      result.pagination?.total ??
      users.value.length

    totalPages.value =
      result.pagination?.last_page ??
      1

    if (
      currentPage.value >
      totalPages.value
    ) {
      currentPage.value =
        totalPages.value || 1

      if (
        currentPage.value !== 1
      ) {
        await loadUsers()
      }
    }

  } catch (error) {
    showToast(
      error.message ||
      'Impossible de charger les utilisateurs.',
      'error'
    )
  } finally {
    loading.value = false
  }
}

// ============================================================
// FILTERS
// ============================================================

function applyFilters() {
  currentPage.value = 1
  loadUsers()
}

function resetFilters() {
  search.value = ''
  roleFilter.value = ''
  gradeFilter.value = ''
  statusFilter.value = ''
  currentPage.value = 1

  loadUsers()
}

async function refreshUsers() {
  await Promise.all([
    loadReferences(),
    loadUsers()
  ])

  showToast(
    'Les données ont été actualisées.',
    'success'
  )
}

// ============================================================
// PAGINATION
// ============================================================

const visiblePages = computed(() => {
  const totalPageCount =
    totalPages.value

  if (totalPageCount <= 1) {
    return [1]
  }

  const current =
    currentPage.value

  const pages = []

  let start = Math.max(
    1,
    current - 2
  )

  let end = Math.min(
    totalPageCount,
    current + 2
  )

  if (current <= 3) {
    start = 1
    end = Math.min(
      totalPageCount,
      5
    )
  }

  if (
    current >=
    totalPageCount - 2
  ) {
    start = Math.max(
      1,
      totalPageCount - 4
    )

    end = totalPageCount
  }

  for (
    let page = start;
    page <= end;
    page++
  ) {
    pages.push(page)
  }

  return pages
})

function goToPage(page) {
  const target = Number(page)

  if (
    target < 1 ||
    target > totalPages.value ||
    target === currentPage.value
  ) {
    return
  }

  currentPage.value = target

  loadUsers()
}

function previousPage() {
  if (currentPage.value > 1) {
    goToPage(
      currentPage.value - 1
    )
  }
}

function nextPage() {
  if (
    currentPage.value <
    totalPages.value
  ) {
    goToPage(
      currentPage.value + 1
    )
  }
}

function changePerPage() {
  currentPage.value = 1
  loadUsers()
}

// ============================================================
// HELPERS
// ============================================================

function getRoleName(user) {
  return (
    user?.role?.nom_role ||

    roles.value.find(
      role =>
        String(role.id_role) ===
        String(user?.id_role)
    )?.nom_role ||

    '—'
  )
}

function getGradeName(user) {
  return (
    user?.grade?.nom_grade ||

    grades.value.find(
      grade =>
        String(grade.id_grade) ===
        String(user?.id_grade)
    )?.nom_grade ||

    '—'
  )
}

function getFullName(user) {
  return (
    `${user?.prenom || ''} ${
      user?.nom || ''
    }`.trim() ||
    'Utilisateur'
  )
}

function getInitials(user) {
  const first =
    user?.prenom
      ?.trim()
      ?.charAt(0) || ''

  const last =
    user?.nom
      ?.trim()
      ?.charAt(0) || ''

  return (
    `${first}${last}`.toUpperCase() ||
    'U'
  )
}

function isAdmin(user) {
  return getRoleName(user)
    .toLowerCase()
    .includes('administrateur')
}

function isCurrentUser(user) {
  try {
    const storedUser =
      localStorage.getItem(
        'utilisateur'
      ) ||
      sessionStorage.getItem(
        'utilisateur'
      )

    if (!storedUser) {
      return false
    }

    const currentUser =
      JSON.parse(storedUser)

    return (
      String(
        currentUser?.id_utilisateur
      ) ===
      String(
        user?.id_utilisateur
      )
    )

  } catch (error) {
    console.error(
      'Impossible de récupérer l’utilisateur connecté:',
      error
    )

    return false
  }
}

function formatDate(value) {
  if (!value) {
    return 'Jamais'
  }

  const raw =
    String(value).trim()

  // Laravel envoie une date UTC avec "Z"
  const date = new Date(raw)

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
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    }
  ).format(date)
}

// ============================================================
// FORM VALIDATION
// ============================================================

function parseValidationErrors(error) {
  const errors =
    error?.data?.errors

  if (
    errors &&
    typeof errors === 'object'
  ) {
    const normalized = {}

    Object.keys(errors).forEach(
      key => {
        normalized[key] =
          Array.isArray(errors[key])
            ? errors[key][0]
            : String(errors[key])
      }
    )

    return normalized
  }

  return {}
}

function validateForm() {
  formErrors.value = {}
  formGeneralError.value = ''

  const errors = {}

  if (
    !form.value.matricule.trim()
  ) {
    errors.matricule =
      'Le matricule est obligatoire.'
  }

  if (!form.value.nom.trim()) {
    errors.nom =
      'Le nom est obligatoire.'
  }

  if (
    !form.value.prenom.trim()
  ) {
    errors.prenom =
      'Le prénom est obligatoire.'
  }

  if (
    !form.value.poste_fonction.trim()
  ) {
    errors.poste_fonction =
      'Le poste / fonction est obligatoire.'
  }

  if (!form.value.email.trim()) {
    errors.email =
      "L'adresse e-mail est obligatoire."
  } else if (
    !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
      form.value.email
    )
  ) {
    errors.email =
      "L'adresse e-mail n'est pas valide."
  }

  if (!form.value.id_grade) {
    errors.id_grade =
      'Veuillez sélectionner un grade.'
  }

  if (!form.value.id_role) {
    errors.id_role =
      'Veuillez sélectionner un rôle.'
  }

  formErrors.value = errors

  return (
    Object.keys(errors).length === 0
  )
}

// ============================================================
// CREATE / EDIT
// ============================================================

function openCreateModal() {
  if (!canCreate.value) {
    showToast(
      "Vous n'avez pas la permission de créer un utilisateur.",
      'error'
    )
    return
  }

  isEditMode.value = false
  selectedUser.value = null

  form.value = emptyForm()

  formErrors.value = {}
  formGeneralError.value = ''

  showFormModal.value = true
}

function openEditModal(user) {
  if (!canUpdate.value) {
    showToast(
      "Vous n'avez pas la permission de modifier un utilisateur.",
      'error'
    )
    return
  }

  if (!user) {
    return
  }

  isEditMode.value = true
  selectedUser.value = user

  form.value = {
    matricule:
      user?.matricule || '',

    nom:
      user?.nom || '',

    prenom:
      user?.prenom || '',

    poste_fonction:
      user?.poste_fonction || '',

    email:
      user?.email || '',

    id_grade:
      user?.id_grade ??
      user?.grade?.id_grade ??
      '',

    id_role:
      user?.id_role ??
      user?.role?.id_role ??
      '',

    statut:
      !!user?.statut,

    doit_changer_mdp:
      !!user?.doit_changer_mdp
  }

  formErrors.value = {}
  formGeneralError.value = ''

  showFormModal.value = true
}

// Fermeture forcée après opération réussie
function closeFormModal() {
  showFormModal.value = false

  formErrors.value = {}
  formGeneralError.value = ''

  selectedUser.value = null
  isEditMode.value = false
}

async function saveUser() {
  if (
    isEditMode.value &&
    !canUpdate.value
  ) {
    showToast(
      "Vous n'avez pas la permission de modifier un utilisateur.",
      'error'
    )
    return
  }

  if (
    !isEditMode.value &&
    !canCreate.value
  ) {
    showToast(
      "Vous n'avez pas la permission de créer un utilisateur.",
      'error'
    )
    return
  }

  if (!validateForm()) {
    showToast(
      'Veuillez corriger les champs indiqués.',
      'warning'
    )
    return
  }

  saving.value = true
  formGeneralError.value = ''

  const payload = {
    matricule:
      form.value.matricule.trim(),

    nom:
      form.value.nom.trim(),

    prenom:
      form.value.prenom.trim(),

    poste_fonction:
      form.value.poste_fonction.trim(),

    email:
      form.value.email.trim(),

    id_grade:
      Number(form.value.id_grade),

    id_role:
      Number(form.value.id_role),

    statut:
      !!form.value.statut,

    doit_changer_mdp:
      !!form.value.doit_changer_mdp
  }

  try {
    if (isEditMode.value) {
      if (
        !selectedUser.value?.id_utilisateur
      ) {
        throw new Error(
          "L'utilisateur à modifier est introuvable."
        )
      }

      await apiFetch(
        `/utilisateurs/${selectedUser.value.id_utilisateur}`,
        {
          method: 'PUT',
          body: payload
        }
      )

      // Fermer immédiatement après modification
      closeFormModal()

      showToast(
        'Les informations de l’utilisateur ont été mises à jour.',
        'success'
      )

    } else {
      await apiFetch(
        '/utilisateurs',
        {
          method: 'POST',
          body: payload
        }
      )

      // Fermer immédiatement après création
      closeFormModal()

      showToast(
        'Le nouvel utilisateur a été créé avec succès.',
        'success'
      )
    }

    // Actualiser la liste après fermeture
    await loadUsers()

  } catch (error) {
    formErrors.value =
      parseValidationErrors(error)

    formGeneralError.value =
      error.message ||
      'Impossible d’enregistrer les modifications.'

    showToast(
      error.message ||
      'Impossible d’enregistrer les modifications.',
      'error'
    )

  } finally {
    saving.value = false
  }
}

// ============================================================
// DETAIL
// ============================================================

async function openDetailModal(user) {
  if (!canView.value) {
    showToast(
      "Vous n'avez pas la permission de consulter les utilisateurs.",
      'error'
    )
    return
  }

  if (!user) {
    return
  }

  selectedUser.value = user
  showDetailModal.value = true

  try {
    const result = await apiFetch(
      `/utilisateurs/${user.id_utilisateur}`
    )

    if (result?.data) {
      selectedUser.value =
        result.data
    }

  } catch (error) {
    showToast(
      error.message ||
      'Impossible de récupérer les détails.',
      'error'
    )
  }
}

function closeDetailModal() {
  showDetailModal.value = false
}

function editFromDetail() {
  if (!canUpdate.value) {
    showToast(
      "Vous n'avez pas la permission de modifier un utilisateur.",
      'error'
    )
    return
  }

  const user =
    selectedUser.value

  closeDetailModal()

  if (user) {
    openEditModal(user)
  }
}

// ============================================================
// STATUS
// ============================================================

function openStatusModal(user) {
  if (!canUpdate.value) {
    showToast(
      "Vous n'avez pas la permission de modifier le statut d'un utilisateur.",
      'error'
    )
    return
  }

  if (!user) {
    return
  }

  if (isCurrentUser(user)) {
    showToast(
      'Vous ne pouvez pas modifier le statut de votre propre compte.',
      'warning'
    )
    return
  }

  statusTarget.value = user
  showStatusModal.value = true
}

function closeStatusModal() {
  showStatusModal.value = false
  statusTarget.value = null
}

async function confirmChangeStatus() {
  if (!canUpdate.value) {
    showToast(
      "Vous n'avez pas la permission de modifier le statut d'un utilisateur.",
      'error'
    )
    return
  }

  if (!statusTarget.value) {
    return
  }

  if (
    isCurrentUser(
      statusTarget.value
    )
  ) {
    showToast(
      'Vous ne pouvez pas modifier le statut de votre propre compte.',
      'warning'
    )
    return
  }

  const userId =
    statusTarget.value.id_utilisateur

  const newStatus =
    !statusTarget.value.statut

  changingStatus.value = true

  try {
    await apiFetch(
      `/utilisateurs/${userId}/statut`,
      {
        method: 'PATCH',
        body: {
          statut: newStatus
        }
      }
    )

    // Fermer immédiatement après changement
    closeStatusModal()

    showToast(
      newStatus
        ? 'Utilisateur activé avec succès.'
        : 'Utilisateur désactivé avec succès.',
      'success'
    )

    await loadUsers()

  } catch (error) {
    console.error(
      'Erreur changement statut:',
      error
    )

    showToast(
      error?.data?.message ||
      error.message ||
      'Erreur lors du changement de statut.',
      'error'
    )

  } finally {
    changingStatus.value = false
  }
}

// ============================================================
// RESET PASSWORD ADMIN
// ============================================================

function openResetPasswordModal(user) {
  if (!canUpdate.value) {
    showToast(
      "Vous n'avez pas la permission de réinitialiser un mot de passe.",
      'error'
    )
    return
  }

  if (!user) {
    return
  }

  resetPasswordTarget.value = user

  resetPasswordForm.value = {
    mot_de_passe: '',
    mot_de_passe_confirmation: ''
  }

  resetPasswordErrors.value = {}
  resetPasswordGeneralError.value = ''

  showResetPassword.value = false
  showResetPasswordConfirmation.value = false

  resettingPassword.value = false
  resetPasswordClicked.value = false

  showResetPasswordModal.value = true
}

function closeResetPasswordModal() {
  showResetPasswordModal.value = false

  resetPasswordTarget.value = null

  resetPasswordForm.value = {
    mot_de_passe: '',
    mot_de_passe_confirmation: ''
  }

  resetPasswordErrors.value = {}
  resetPasswordGeneralError.value = ''

  showResetPassword.value = false
  showResetPasswordConfirmation.value = false
  resetPasswordClicked.value = false
}

async function confirmResetPassword() {
  if (!canUpdate.value) {
    showToast(
      "Vous n'avez pas la permission de réinitialiser un mot de passe.",
      'error'
    )
    return
  }

  if (resettingPassword.value) {
    return
  }

  // Premier clic
  if (!resetPasswordClicked.value) {
    resetPasswordClicked.value = true
    return
  }

  const userId =
    resetPasswordTarget.value?.id_utilisateur

  if (!userId) {
    resetPasswordGeneralError.value =
      'Utilisateur introuvable.'
    return
  }

  if (!validateResetPassword()) {
    return
  }

  resettingPassword.value = true

  try {
    await apiFetch(
      `/utilisateurs/${userId}/reset-password`,
      {
        method: 'PATCH',
        body: {
          mot_de_passe:
            resetPasswordForm.value.mot_de_passe,

          mot_de_passe_confirmation:
            resetPasswordForm.value
              .mot_de_passe_confirmation
        }
      }
    )

    // Fermer immédiatement après succès
    closeResetPasswordModal()

    showToast(
      'Mot de passe réinitialisé avec succès.',
      'success'
    )

    await loadUsers()

  } catch (error) {
    console.error(
      'Erreur réinitialisation mot de passe:',
      error
    )

    console.error(
      'Réponse Laravel:',
      error?.data
    )

    resetPasswordGeneralError.value =
      error?.data?.message ||
      error?.message ||
      'Impossible de réinitialiser le mot de passe.'

    // Garder le formulaire ouvert en cas d'erreur
    resetPasswordClicked.value = true

  } finally {
    resettingPassword.value = false
  }
}

function validateResetPassword() {
  resetPasswordErrors.value = {}
  resetPasswordGeneralError.value = ''

  const errors = {}

  const password =
    resetPasswordForm.value.mot_de_passe

  const confirmation =
    resetPasswordForm.value
      .mot_de_passe_confirmation

  if (!password) {
    errors.mot_de_passe =
      'Le nouveau mot de passe est obligatoire.'

  } else if (
    password.length < 8
  ) {
    errors.mot_de_passe =
      'Le mot de passe doit contenir au moins 8 caractères.'

  } else if (
    password.length > 255
  ) {
    errors.mot_de_passe =
      'Le mot de passe ne doit pas dépasser 255 caractères.'
  }

  if (!confirmation) {
    errors.mot_de_passe_confirmation =
      'La confirmation du mot de passe est obligatoire.'

  } else if (
    password !== confirmation
  ) {
    errors.mot_de_passe_confirmation =
      'Les deux mots de passe ne correspondent pas.'
  }

  resetPasswordErrors.value =
    errors

  return (
    Object.keys(errors).length === 0
  )
}

// ============================================================
// DELETE
// ============================================================

function openDeleteModal(user) {
  if (!canDelete.value) {
    showToast(
      "Vous n'avez pas la permission de supprimer un utilisateur.",
      'error'
    )
    return
  }

  if (!user) {
    return
  }

  if (isCurrentUser(user)) {
    showToast(
      'Vous ne pouvez pas supprimer votre propre compte.',
      'warning'
    )
    return
  }

  selectedUser.value = user
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  selectedUser.value = null
}

async function confirmDeleteUser() {
  if (!canDelete.value) {
    showToast(
      "Vous n'avez pas la permission de supprimer un utilisateur.",
      'error'
    )
    return
  }

  if (!selectedUser.value) {
    return
  }

  if (
    isCurrentUser(
      selectedUser.value
    )
  ) {
    showToast(
      'Vous ne pouvez pas supprimer votre propre compte.',
      'warning'
    )
    return
  }

  deleting.value = true

  try {
    await apiFetch(
      `/utilisateurs/${selectedUser.value.id_utilisateur}`,
      {
        method: 'DELETE'
      }
    )

    // Fermer immédiatement après suppression
    closeDeleteModal()

    showToast(
      'L’utilisateur a été supprimé avec succès.',
      'success'
    )

    if (
      users.value.length === 1 &&
      currentPage.value > 1
    ) {
      currentPage.value--
    }

    await loadUsers()

  } catch (error) {
    showToast(
      error?.data?.message ||
      error.message ||
      'Impossible de supprimer cet utilisateur.',
      'error'
    )

  } finally {
    deleting.value = false
  }
}

// ============================================================
// KEYBOARD
// ============================================================

function handleEscape(event) {
  if (event.key !== 'Escape') {
    return
  }

  if (
    showResetPasswordModal.value
  ) {
    closeResetPasswordModal()
    return
  }

  if (showDeleteModal.value) {
    closeDeleteModal()
    return
  }

  if (showStatusModal.value) {
    closeStatusModal()
    return
  }

  if (showDetailModal.value) {
    closeDetailModal()
    return
  }

  if (showFormModal.value) {
    closeFormModal()
  }
}

// ============================================================
// WATCH FILTERS
// ============================================================

let searchTimer = null

watch(
  [
    roleFilter,
    gradeFilter,
    statusFilter
  ],
  () => {
    currentPage.value = 1
    loadUsers()
  }
)

watch(
  search,
  () => {
    window.clearTimeout(
      searchTimer
    )

    searchTimer = window.setTimeout(
      () => {
        currentPage.value = 1
        loadUsers()
      },
      450
    )
  }
)

// ============================================================
// LIFECYCLE
// ============================================================

onMounted(async () => {
  document.addEventListener(
    'keydown',
    handleEscape
  )

  await Promise.all([
    loadReferences(),
    loadUsers()
  ])
})

onUnmounted(() => {
  document.removeEventListener(
    'keydown',
    handleEscape
  )

  window.clearTimeout(
    searchTimer
  )

  document.body.style.overflow = ''
})
</script>

<style scoped>
/* ============================================================
   DESIGN SYSTEM
============================================================ */

:global(*) {
  box-sizing: border-box;
}

:global(body) {
  margin: 0;
}

.users-page {
  --primary: #0f2747;
  --primary-hover: #173d68;
  --background: #f5f7fa;
  --surface: #ffffff;
  --text: #172033;
  --secondary: #64748b;
  --muted: #94a3b8;
  --border: #e2e8f0;

  --success: #16a34a;
  --success-dark: #15803d;

  --warning: #f59e0b;
  --warning-dark: #d97706;

  --danger: #dc2626;
  --danger-dark: #b91c1c;

  --blue: #2563eb;
  --blue-dark: #1d4ed8;

  --purple: #7c3aed;
  --purple-dark: #6d28d9;

  --cyan: #0891b2;

  min-height: 100vh;
  padding: 30px;
  background:
    radial-gradient(
      circle at 90% 0%,
      rgba(37, 99, 235, 0.06),
      transparent 28%
    ),
    var(--background);
  color: var(--text);
}

/* ============================================================
   HEADER
============================================================ */

.page-header {
  margin-bottom: 26px;
}

.header-content {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
}

.header-title-group {
  max-width: 760px;
}

.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 9px;
  color: var(--blue);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.header-title-group h1 {
  margin: 0;
  color: var(--primary);
  font-size: clamp(27px, 3vw, 36px);
  line-height: 1.15;
  font-weight: 800;
  letter-spacing: -0.035em;
}

.header-title-group p {
  max-width: 700px;
  margin: 10px 0 0;
  color: var(--secondary);
  font-size: 14px;
  line-height: 1.7;
}

.btn-new-user {
  flex-shrink: 0;
  min-height: 48px;
}

/* ============================================================
   BUTTONS
============================================================ */

.btn {
  border: 0;
  border-radius: 11px;
  min-height: 42px;
  padding: 0 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-family: inherit;
  font-size: 13px;
  font-weight: 750;
  cursor: pointer;
  transition:
    transform 0.18s ease,
    box-shadow 0.18s ease,
    background 0.18s ease,
    border-color 0.18s ease,
    color 0.18s ease;
}

.btn:disabled {
  cursor: not-allowed;
  opacity: 0.58;
  transform: none !important;
}

.btn-primary {
  color: white;
  background: #2563EB;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
  height: 46px;
  font-size: 15px;
  font-weight: 600;
  border-radius: 12px;
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  background: #1D4ED8;
  box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
}

.btn-outline {
  color: var(--primary);
  background: white;
  border: 1px solid var(--border);
}

.btn-outline:hover:not(:disabled) {
  color: var(--blue);
  border-color: #bfdbfe;
  background: #f8fbff;
}

.btn-filter {
  color: white;
  background: #2563EB;
  box-shadow: 0 5px 14px rgba(15, 39, 71, 0.16);
}

.btn-filter:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.btn-reset,
.btn-cancel {
  color: #475569;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
}

.btn-reset:hover,
.btn-cancel:hover {
  color: var(--primary);
  background: #e8eef5;
  border-color: #cbd5e1;
}




/* ============================================================
   STATS
============================================================ */

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}

.stat-card {
  min-height: 126px;
  padding: 19px;
  display: flex;
  align-items: center;
  gap: 15px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow:
    0 4px 18px rgba(15, 23, 42, 0.045);
}

.stat-icon {
  width: 48px;
  height: 48px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 14px;
}

.stat-total .stat-icon {
  color: var(--blue);
  background: #eff6ff;
}

.stat-active .stat-icon {
  color: var(--success);
  background: #ecfdf5;
}

.stat-inactive .stat-icon {
  color: var(--warning-dark);
  background: #fff7ed;
}

.stat-admin .stat-icon {
  color: var(--purple);
  background: #f5f3ff;
}

.stat-content {
  min-width: 0;
}

.stat-label {
  display: block;
  margin-bottom: 3px;
  color: var(--secondary);
  font-size: 12px;
  font-weight: 650;
}

.stat-content strong {
  display: block;
  color: var(--text);
  font-size: 25px;
  line-height: 1.15;
}

.stat-content small {
  display: block;
  margin-top: 4px;
  color: var(--muted);
  font-size: 11px;
}

/* ============================================================
   FILTERS
============================================================ */

.filters-card {
  margin-bottom: 20px;
  padding: 20px;
  background: white;
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow:
    0 4px 18px rgba(15, 23, 42, 0.045);
}

.filters-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.filters-title {
  display: flex;
  align-items: center;
  gap: 11px;
}

.filters-title-icon {
  width: 39px;
  height: 39px;
  display: grid;
  place-items: center;
  border-radius: 11px;
  color: var(--primary);
  background: #eef4fb;
}

.filters-title h2 {
  margin: 0;
  font-size: 15px;
  font-weight: 800;
}

.filters-title p {
  margin: 3px 0 0;
  color: var(--secondary);
  font-size: 12px;
}

.filters-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
  align-items: end;
  gap: 12px;
}

.field {
  min-width: 0;
}

.field label {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 7px;
  color: #475569;
  font-size: 11px;
  font-weight: 750;
}

.field label span {
  color: var(--danger);
}

.input-wrap,
.select-wrap {
  position: relative;
}

.input-wrap > svg,
.select-wrap > svg {
  position: absolute;
  left: 13px;
  top: 50%;
  z-index: 1;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
}

.input-wrap input,
.select-wrap select,
.field > select {
  width: 100%;
  height: 43px;
  padding: 0 13px 0 39px;
  color: var(--text);
  background: white;
  border: 1px solid var(--border);
  border-radius: 10px;
  outline: none;
  font: inherit;
  font-size: 13px;
  transition:
    border-color 0.18s ease,
    box-shadow 0.18s ease,
    background 0.18s ease;
}

.field > select {
  padding-left: 13px;
}

.input-wrap input::placeholder {
  color: #a0aec0;
}

.input-wrap input:focus,
.select-wrap select:focus,
.field > select:focus {
  border-color: #60a5fa;
  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.10);
}

.input-clear {
  position: absolute;
  top: 50%;
  right: 8px;
  width: 28px;
  height: 28px;
  display: grid;
  place-items: center;
  transform: translateY(-50%);
  color: #64748b;
  background: #f1f5f9;
  border: 0;
  border-radius: 7px;
  cursor: pointer;
}

.input-clear:hover {
  color: var(--danger);
  background: #fee2e2;
}

.filter-actions {
  display: flex;
  gap: 8px;
}

/* ============================================================
   TABLE
============================================================ */

.table-card {
  overflow: hidden;
  background: white;
  border: 1px solid var(--border);
  border-radius: 17px;
  box-shadow:
    0 5px 22px rgba(15, 23, 42, 0.05);
}

.table-header {
  min-height: 75px;
  padding: 17px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
}

.section-eyebrow {
  display: block;
  margin-bottom: 3px;
  color: var(--blue);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.table-header h2 {
  margin: 0;
  font-size: 17px;
  font-weight: 800;
}

.result-count {
  padding: 7px 11px;
  color: var(--secondary);
  background: #f8fafc;
  border: 1px solid var(--border);
  border-radius: 9px;
  font-size: 12px;
}

.result-count span {
  color: var(--primary);
  font-weight: 800;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.users-table {
  width: 100%;
  min-width: 980px;
  border-collapse: collapse;
}

.users-table th {
  height: 43px;
  padding: 0 16px;
  color: #64748b;
  background: #f8fafc;
  border-bottom: 1px solid var(--border);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-align: left;
  text-transform: uppercase;
}

.users-table td {
  padding: 13px 16px;
  border-bottom: 1px solid #eef2f7;
  vertical-align: middle;
}

.users-table tbody tr {
  transition: background 0.15s ease;
}

.users-table tbody tr:hover {
  background: #f8fbff;
}

.users-table tbody tr:last-child td {
  border-bottom: 0;
}

.actions-column {
  text-align: center !important;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 11px;
  min-width: 210px;
}

.avatar,
.target-avatar {
  width: 42px;
  height: 42px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  color: white;
  background: linear-gradient(
    135deg,
    #0f2747,
    #2563eb
  );
  border-radius: 12px;
  font-size: 12px;
  font-weight: 850;
  box-shadow:
    0 4px 12px rgba(37, 99, 235, 0.16);
}

.user-identity {
  min-width: 0;
}

.user-identity strong {
  display: block;
  overflow: hidden;
  color: var(--text);
  font-size: 13px;
  font-weight: 750;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-identity span {
  display: block;
  max-width: 190px;
  margin-top: 3px;
  overflow: hidden;
  color: #94a3b8;
  font-size: 11px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.matricule,
.grade-text,
.date-cell {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #475569;
  font-size: 12px;
}

.matricule {
  padding: 6px 8px;
  color: var(--primary);
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 7px;
  font-weight: 700;
}

.role-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 9px;
  color: #5b21b6;
  background: #f5f3ff;
  border: 1px solid #ddd6fe;
  border-radius: 7px;
  font-size: 11px;
  font-weight: 750;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 9px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 750;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
}

.status-active {
  color: #166534;
  background: #ecfdf5;
  border: 1px solid #bbf7d0;
}

.status-active .status-dot {
  background: var(--success);
}

.status-inactive {
  color: #991b1b;
  background: #fef2f2;
  border: 1px solid #fecaca;
}

.status-inactive .status-dot {
  background: var(--danger);
}

/* ============================================================
   ACTION BUTTONS
============================================================ */

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 5px;
}

.icon-btn {
  position: relative;
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  border: 1px solid transparent;
  border-radius: 8px;
  cursor: pointer;
  transition:
    transform 0.16s ease,
    background 0.16s ease,
    color 0.16s ease,
    border-color 0.16s ease;
}

.icon-btn:hover {
  transform: translateY(-1px);
}

.icon-view {
  color: var(--blue);
  background: #eff6ff;
  border-color: #dbeafe;
}

.icon-view:hover {
  color: white;
  background: var(--blue);
  border-color: var(--blue);
}

.icon-edit {
  color: var(--purple);
  background: #f5f3ff;
  border-color: #ede9fe;
}

.icon-edit:hover {
  color: white;
  background: var(--purple);
  border-color: var(--purple);
}

.icon-disable {
  color: var(--warning-dark);
  background: #fff7ed;
  border-color: #fed7aa;
}

.icon-disable:hover {
  color: white;
  background: var(--warning);
  border-color: var(--warning);
}

.icon-enable {
  color: var(--success-dark);
  background: #ecfdf5;
  border-color: #bbf7d0;
}

.icon-enable:hover {
  color: white;
  background: var(--success);
  border-color: var(--success);
}

.icon-delete {
  color: var(--danger);
  background: #fef2f2;
  border-color: #fecaca;
}

.icon-delete:hover {
  color: white;
  background: var(--danger);
  border-color: var(--danger);
}

/* ============================================================
   MOBILE USERS
============================================================ */

.mobile-users {
  display: none;
}

.mobile-user-card {
  margin: 12px;
  padding: 15px;
  border: 1px solid var(--border);
  border-radius: 14px;
}

.mobile-user-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.mobile-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 9px;
  margin-top: 15px;
}

.mobile-info-grid > div {
  padding: 10px;
  background: #f8fafc;
  border-radius: 9px;
}

.mobile-info-grid span {
  display: block;
  margin-bottom: 3px;
  color: #94a3b8;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

.mobile-info-grid strong {
  display: block;
  color: var(--text);
  font-size: 12px;
}

.mobile-actions {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 7px;
  margin-top: 13px;
}

.mobile-action {
  min-height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border: 1px solid;
  border-radius: 8px;
  font: inherit;
  font-size: 11px;
  font-weight: 750;
  cursor: pointer;
}

.mobile-action.view {
  color: var(--blue);
  background: #eff6ff;
  border-color: #dbeafe;
}

.mobile-action.edit {
  color: var(--purple);
  background: #f5f3ff;
  border-color: #ddd6fe;
}

.mobile-action.disable {
  color: var(--warning-dark);
  background: #fff7ed;
  border-color: #fed7aa;
}

.mobile-action.enable {
  color: var(--success-dark);
  background: #ecfdf5;
  border-color: #bbf7d0;
}

.mobile-action.delete {
  color: var(--danger);
  background: #fef2f2;
  border-color: #fecaca;
}

/* ============================================================
   EMPTY / LOADING
============================================================ */

.empty-state {
  min-height: 300px;
  padding: 50px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.empty-icon {
  width: 68px;
  height: 68px;
  margin-bottom: 14px;
  display: grid;
  place-items: center;
  color: var(--blue);
  background: #eff6ff;
  border-radius: 18px;
}

.empty-state h3 {
  margin: 0;
  font-size: 16px;
}

.empty-state p {
  max-width: 420px;
  margin: 7px 0 18px;
  color: var(--secondary);
  font-size: 13px;
}

.loading-state {
  padding: 15px 18px;
}

.skeleton-row {
  min-height: 65px;
  display: grid;
  grid-template-columns: 220px 140px 150px 150px 100px 150px;
  align-items: center;
  gap: 16px;
  border-bottom: 1px solid #eef2f7;
}

.skeleton {
  height: 13px;
  display: block;
  background: linear-gradient(
    90deg,
    #f1f5f9,
    #e2e8f0,
    #f1f5f9
  );
  background-size: 200% 100%;
  border-radius: 6px;
  animation: skeleton-loading 1.4s infinite;
}

.skeleton-avatar {
  width: 42px;
  height: 42px;
  border-radius: 11px;
}

.skeleton-large {
  width: 170px;
}

.skeleton-medium {
  width: 110px;
}

.skeleton-small {
  width: 70px;
}

.skeleton-actions {
  width: 120px;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

/* ============================================================
   PAGINATION
============================================================ */

.pagination {
  min-height: 64px;
  padding: 10px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  border-top: 1px solid var(--border);
}

.pagination-info {
  color: #64748b;
  font-size: 11px;
}

.pagination-info strong {
  color: var(--text);
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}

.pagination-controls select {
  height: 34px;
  margin-right: 5px;
  padding: 0 8px;
  color: #475569;
  background: white;
  border: 1px solid var(--border);
  border-radius: 8px;
  outline: none;
  font: inherit;
  font-size: 11px;
}

.page-btn,
.page-number {
  min-width: 33px;
  height: 33px;
  display: grid;
  place-items: center;
  border: 1px solid transparent;
  border-radius: 8px;
  font: inherit;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.page-btn {
  color: #475569;
  background: white;
  border-color: var(--border);
}

.page-btn:hover:not(:disabled) {
  color: var(--primary);
  background: #f8fafc;
  border-color: #cbd5e1;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-number {
  color: #64748b;
  background: transparent;
}

.page-number:hover {
  color: var(--blue);
  background: #eff6ff;
}

.page-number.active {
  color: white;
  background: var(--primary);
  box-shadow:
    0 3px 9px rgba(15, 39, 71, 0.18);
}

/* ============================================================
   MODAL COMMON
============================================================ */

.modal-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(15, 23, 42, 0.68);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

.base-modal-layer {
  z-index: 3000;
}

.confirm-layer {
  z-index: 5000;
}

.modal {
  width: min(100%, 1100px);
  max-height: 92vh;
  overflow: hidden;
  background: white;
  border: 1px solid rgba(255,255,255,0.8);
  border-radius: 22px;
  box-shadow:
    0 30px 80px rgba(15, 23, 42, 0.30);
}

.modal-close {
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  color: rgba(255,255,255,0.8);
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 10px;
  cursor: pointer;
  transition: 0.18s ease;
}

.modal-close:hover {
  color: white;
  background: rgba(255,255,255,0.22);
}

/* ============================================================
   FORM MODAL
============================================================ */

.form-modal {
  display: flex;
  flex-direction: column;
}

.form-modal-header {
  position: relative;
  min-height: 132px;
  padding: 24px 27px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  color: white;
  overflow: hidden;
}

.modal-create .form-modal-header {
  background:
    radial-gradient(
      circle at 90% 0%,
      rgba(34, 211, 238, 0.32),
      transparent 35%
    ),
    linear-gradient(
      135deg,
      #0f2747,
      #2563eb 72%,
      #0891b2
    );
}

.modal-edit .form-modal-header {
  background:
    radial-gradient(
      circle at 90% 0%,
      rgba(167, 139, 250, 0.32),
      transparent 35%
    ),
    linear-gradient(
      135deg,
      #0f2747,
      #6d28d9 70%,
      #4f46e5
    );
}

.form-header-left {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
}

.form-header-icon {
  width: 56px;
  height: 56px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  color: white;
  background: rgba(255,255,255,0.13);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 16px;
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,0.16);
}

.modal-eyebrow {
  display: block;
  margin-bottom: 5px;
  color: rgba(255,255,255,0.68);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.form-modal-header h2 {
  margin: 0;
  font-size: 22px;
  line-height: 1.2;
  font-weight: 800;
  letter-spacing: -0.02em;
}

.form-modal-header p {
  margin: 6px 0 0;
  color: rgba(255,255,255,0.72);
  font-size: 12px;
}

.form-modal-body {
  padding: 25px 27px;
  overflow-y: auto;
}

.form-section + .form-section {
  margin-top: 27px;
  padding-top: 26px;
  border-top: 1px solid #e8edf3;
}

.form-section-title {
  display: flex;
  align-items: center;
  gap: 11px;
  margin-bottom: 18px;
}

.section-icon {
  width: 39px;
  height: 39px;
  display: grid;
  place-items: center;
  border-radius: 11px;
}

.section-icon.blue {
  color: var(--blue);
  background: #eff6ff;
}

.section-icon.purple {
  color: var(--purple);
  background: #f5f3ff;
}

.form-section-title h3 {
  margin: 0;
  font-size: 14px;
  font-weight: 800;
}

.form-section-title p {
  margin: 3px 0 0;
  color: var(--secondary);
  font-size: 11px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 17px 18px;
}

.field-full {
  grid-column: 1 / -1;
}

.form-grid .field label {
  margin-bottom: 7px;
  color: darkblue;
  font-size: 11px;
}

.form-grid .input-wrap input,
.form-grid .select-wrap select {
  height: 46px;
  border-radius: 11px;
  background: #e6e8ec;
}

.form-grid .input-wrap input:hover,
.form-grid .select-wrap select:hover {
  border-color: #cbd5e1;
}

.form-grid .input-wrap input:focus,
.form-grid .select-wrap select:focus {
  background: white;
  border: 1px solid darkblue;
}



.has-error input,
.has-error select {
  border-color: #fca5a5 !important;
  background: #fff7f7 !important;
  box-shadow:
    0 0 0 3px rgba(220,38,38,0.07) !important;
}

.field-error {
  display: block;
  margin-top: 5px;
  color: var(--danger);
  font-size: 10px;
  font-weight: 650;
}

/* ============================================================
   SWITCH CARDS
============================================================ */

.switch-card {
  min-height: 76px;
  padding: 13px 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 13px;
  transition:
    border-color 0.18s ease,
    background 0.18s ease;
}

.switch-card:hover {
  background: #fbfdff;
  border-color: #cbd5e1;
}

.switch-card-icon {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 11px;
}

.active-icon {
  color: var(--success);
  background: #dcfce7;
}

.password-icon {
  color: var(--purple);
  background: #ede9fe;
}

.switch-card-content {
  flex: 1;
  min-width: 0;
}

.switch-card-content strong {
  display: block;
  color: var(--text);
  font-size: 12px;
  font-weight: 800;
}

.switch-card-content span {
  display: block;
  margin-top: 3px;
  color: var(--secondary);
  font-size: 10px;
  line-height: 1.45;
}

.switch {
  position: relative;
  width: 46px;
  height: 25px;
  flex-shrink: 0;
}

.switch input {
  width: 0;
  height: 0;
  opacity: 0;
}

.switch-slider {
  position: absolute;
  inset: 0;
  background: #cbd5e1;
  border-radius: 999px;
  cursor: pointer;
  transition: 0.2s ease;
}

.switch-slider::before {
  content: '';
  position: absolute;
  width: 19px;
  height: 19px;
  left: 3px;
  top: 3px;
  background: white;
  border-radius: 50%;
  box-shadow:
    0 2px 5px rgba(15,23,42,0.2);
  transition: 0.2s ease;
}

.switch input:checked + .switch-slider {
  background: var(--success);
}

.switch input:checked + .switch-slider::before {
  transform: translateX(21px);
}

/* ============================================================
   SECURITY NOTE
============================================================ */

.security-note {
  margin-top: 15px;
  padding: 12px 14px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  color: #475569;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 11px;
}

.security-note-icon {
  width: 31px;
  height: 31px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  color: var(--blue);
  background: #eff6ff;
  border-radius: 8px;
}

.security-note strong {
  display: block;
  color: var(--text);
  font-size: 11px;
}

.security-note p {
  margin: 3px 0 0;
  font-size: 10px;
  line-height: 1.55;
}

.form-general-error {
  margin-top: 18px;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  gap: 9px;
  color: #991b1b;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 650;
}

.form-modal-footer {
  min-height: 73px;
  padding: 14px 27px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 9px;
  background: #f8fafc;
  border-top: 1px solid var(--border);
}

.form-submit-btn {
  height: 46px;
  padding: 0 19px;
  color: white;
  font-size: 15px;
  font-weight: 600;
  border-radius: 12px;
  border: none;
}

.btn-create-submit,
.btn-edit-submit {
  background: #2563EB;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-create-submit:hover:not(:disabled),
.btn-edit-submit:hover:not(:disabled) {
  background: #1D4ED8;
  box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
  transform: translateY(-1px);
}

.btn-create-submit:active:not(:disabled),
.btn-edit-submit:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
}

.btn-create-submit:disabled,
.btn-edit-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* ============================================================
   DETAIL MODAL
============================================================ */

.detail-modal {
  width: min(960px, 100%);
}

.detail-banner {
  position: relative;
  min-height: 188px;
  padding: 25px 28px;
  display: flex;
  align-items: flex-end;
  overflow: hidden;
  color: white;
  background:
    radial-gradient(
      circle at 90% 10%,
      rgba(34,211,238,0.3),
      transparent 30%
    ),
    linear-gradient(
      135deg,
      #0f2747,
      #1d4ed8 75%,
      #0891b2
    );
}

.detail-banner-pattern {
  position: absolute;
  inset: 0;
  opacity: 0.1;
  background-image:
    linear-gradient(
      135deg,
      transparent 25%,
      rgba(255,255,255,0.15) 25%,
      rgba(255,255,255,0.15) 26%,
      transparent 26%
    );
  background-size: 36px 36px;
}

.detail-close {
  position: absolute;
  top: 18px;
  right: 18px;
}

.detail-profile {
  position: relative;
  width: 100%;
  display: flex;
  align-items: center;
  gap: 17px;
}

.detail-avatar {
  width: 74px;
  height: 74px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  color: #0f2747;
  background: rgb(221, 213, 213);
  border: 4px solid rgba(255,255,255,0.3);
  border-radius: 20px;
  font-size: 20px;
  font-weight: 900;
  box-shadow:
    0 10px 25px rgba(15,23,42,0.2);
}

.detail-profile-info {
  min-width: 0;
}

.detail-profile-info h2 {
  margin: 0;
  font-size: 25px;
  font-weight: 850;
  letter-spacing: -0.025em;
}

.detail-meta {
  margin-top: 7px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.detail-meta > span {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: rgba(255,255,255,0.76);
  font-size: 11px;
}

.detail-status {
  padding: 5px 8px;
  border-radius: 999px;
}

.detail-status-active {
  color: #dcfce7 !important;
  background: rgba(22,163,74,0.2);
}

.detail-status-inactive {
  color: #fee2e2 !important;
  background: rgba(220,38,38,0.2);
}

.detail-edit-button {
  margin-left: auto;
  min-height: 38px;
  padding: 0 13px;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  color: white;
  background: rgba(255,255,255,0.13);
  border: 1px solid rgba(255,255,255,0.22);
  border-radius: 9px;
  font: inherit;
  font-size: 11px;
  font-weight: 750;
  cursor: pointer;
}

.detail-edit-button:hover {
  background: rgba(255,255,255,0.22);
}

.detail-body {
  max-height: calc(92vh - 260px);
  overflow-y: auto;
  padding: 25px 28px;
}

.detail-section + .detail-section {
  margin-top: 25px;
  padding-top: 24px;
  border-top: 1px solid var(--border);
}

.detail-section-heading {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.detail-heading-icon {
  width: 37px;
  height: 37px;
  display: grid;
  place-items: center;
  border-radius: 10px;
}

.detail-heading-icon.blue {
  color: var(--blue);
  background: #eff6ff;
}

.detail-heading-icon.purple {
  color: var(--purple);
  background: #f5f3ff;
}

.detail-heading-icon.green {
  color: var(--success);
  background: #ecfdf5;
}

.detail-section-heading h3 {
  margin: 0;
  font-size: 14px;
  font-weight: 800;
}

.detail-section-heading p {
  margin: 3px 0 0;
  color: var(--secondary);
  font-size: 10px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.detail-info-card,
.access-detail-card {
  min-height: 72px;
  padding: 12px;
  display: flex;
  align-items: center;
  gap: 11px;
  background: #f8fafc;
  border: 1px solid #e5eaf0;
  border-radius: 12px;
}

.detail-info-icon {
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 10px;
}

.detail-info-icon.blue {
  color: var(--blue);
  background: #dbeafe;
}

.detail-info-icon.cyan {
  color: var(--cyan);
  background: #cffafe;
}

.detail-info-icon.purple {
  color: var(--purple);
  background: #ede9fe;
}

.detail-info-icon.orange {
  color: var(--warning-dark);
  background: #ffedd5;
}

.detail-info-card span,
.access-detail-card span,
.activity-item span {
  display: block;
  margin-bottom: 3px;
  color: #94a3b8;
  font-size: 9px;
  font-weight: 750;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.detail-info-card strong,
.access-detail-card strong,
.activity-item strong {
  display: block;
  color: var(--text);
  font-size: 12px;
  font-weight: 750;
}

.break-text {
  word-break: break-word;
}

.access-detail-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.access-detail-card {
  align-items: flex-start;
}

.access-card-icon {
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 9px;
}

.role-card .access-card-icon {
  color: var(--purple);
  background: #ede9fe;
}

.grade-card .access-card-icon {
  color: var(--blue);
  background: #dbeafe;
}

.status-card .access-card-icon {
  color: var(--success);
  background: #dcfce7;
}

.password-card .access-card-icon {
  color: var(--warning-dark);
  background: #ffedd5;
}

.activity-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.activity-item {
  padding: 13px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--blue);
  background: #f8fafc;
  border: 1px solid #e5eaf0;
  border-radius: 11px;
}

.activity-item > div {
  min-width: 0;
}

.activity-item strong {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.detail-footer {
  min-height: 70px;
  padding: 13px 28px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 8px;
  background: #f8fafc;
  border-top: 1px solid var(--border);
}

.btn-detail-edit {
  color: white;
  background: #2563EB;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-detail-edit:hover:not(:disabled) {
  background: #1D4ED8;
  transform: translateY(-1px);
  box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
}

.btn-detail-edit:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
}

/* ============================================================
   CONFIRM MODALS
============================================================ */

.confirm-modal {
  width: min(100%, 450px);
  padding: 30px;
  text-align: center;
  background: white;
  border: 1px solid rgba(255,255,255,0.8);
  border-radius: 20px;
  box-shadow:
    0 30px 80px rgba(15,23,42,0.35);
}

.confirm-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 15px;
  display: grid;
  place-items: center;
  border-radius: 18px;
}

.confirm-disable .confirm-icon {
  color: var(--warning-dark);
  background: #ffedd5;
  border: 1px solid #fed7aa;
}

.confirm-enable .confirm-icon {
  color: var(--success-dark);
  background: #dcfce7;
  border: 1px solid #bbf7d0;
}

.confirm-label {
  display: block;
  margin-bottom: 6px;
  color: #94a3b8;
  font-size: 9px;
  font-weight: 850;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.confirm-modal h2 {
  margin: 0;
  color: var(--text);
  font-size: 21px;
  font-weight: 850;
}

.confirm-description {
  margin: 9px 0 14px;
  color: var(--secondary);
  font-size: 12px;
  line-height: 1.55;
}

.confirm-description strong {
  color: var(--text);
}

.target-user {
  margin-bottom: 14px;
  padding: 11px;
  display: flex;
  align-items: center;
  gap: 10px;
  text-align: left;
  background: #f8fafc;
  border: 1px solid var(--border);
  border-radius: 12px;
}

.target-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
}

.target-user > div:last-child {
  min-width: 0;
}

.target-user strong {
  display: block;
  overflow: hidden;
  color: var(--text);
  font-size: 12px;
  font-weight: 800;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.target-user span {
  display: block;
  margin-top: 2px;
  color: var(--secondary);
  font-size: 10px;
}

.confirm-message {
  margin-bottom: 17px;
  padding: 11px 12px;
  display: flex;
  align-items: flex-start;
  gap: 9px;
  text-align: left;
  border-radius: 10px;
  font-size: 10px;
  line-height: 1.55;
}

.warning-message {
  color: #92400e;
  background: #fffbeb;
  border: 1px solid #fde68a;
}

.success-message {
  color: #166534;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
}

.confirm-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 9px;
}

.confirm-actions .btn {
  min-height: 44px;
}

.confirm-cancel {
  color: #010002;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
}

.confirm-cancel:hover:not(:disabled) {
  color: var(--primary);
  background: #e2e8f0;
}

.confirm-status-btn {
  color: white;
}

.confirm-orange {
  background: var(--warning);
  box-shadow:
    0 6px 15px rgba(245,158,11,0.2);
}

.confirm-orange:hover:not(:disabled) {
  background: var(--warning-dark);
}

.confirm-green {
  background: var(--success);
  box-shadow:
    0 6px 15px rgba(22,163,74,0.2);
}

.confirm-green:hover:not(:disabled) {
  background: var(--success-dark);
}

/* DELETE */

.delete-confirm-icon {
  color: var(--danger);
  background: #fee2e2;
  border: 1px solid #fecaca;
}

.delete-label {
  color: var(--danger);
}

.delete-target {
  background: #fffafa;
  border-color: #fee2e2;
}

.delete-avatar {
  background: linear-gradient(
    135deg,
    #b91c1c,
    #ef4444
  );
}

.danger-message {
  color: #991b1b;
  background: #fef2f2;
  border: 1px solid #fecaca;
}

.danger-message strong {
  display: block;
  margin-bottom: 2px;
  font-size: 11px;
}

.danger-message span {
  display: block;
}

.confirm-delete-btn {
  color: white;
  background: #dc2626;
  box-shadow:
    0 6px 15px rgba(220,38,38,0.2);
}

.confirm-delete-btn:hover:not(:disabled) {
  background: #b91c1c;
}

/* ============================================================
   TOAST
============================================================ */

.toast-container {
  position: fixed;
  z-index: 7000;
  top: 22px;
  right: 22px;
  width: min(390px, calc(100vw - 30px));
  display: flex;
  flex-direction: column;
  gap: 9px;
}

.toast-item {
  min-height: 65px;
  padding: 11px 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  background: white;
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow:
    0 15px 35px rgba(15,23,42,0.15);
}

.toast-icon {
  width: 35px;
  height: 35px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 9px;
}

.toast-success .toast-icon {
  color: var(--success);
  background: #dcfce7;
}

.toast-error .toast-icon {
  color: var(--danger);
  background: #fee2e2;
}

.toast-warning .toast-icon {
  color: var(--warning-dark);
  background: #ffedd5;
}

.toast-info .toast-icon {
  color: var(--blue);
  background: #dbeafe;
}

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-content strong {
  display: block;
  color: var(--text);
  font-size: 11px;
  font-weight: 800;
}

.toast-content span {
  display: block;
  margin-top: 2px;
  color: var(--secondary);
  font-size: 10px;
  line-height: 1.4;
}

.toast-close {
  width: 27px;
  height: 27px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  color: #94a3b8;
  background: transparent;
  border: 0;
  border-radius: 7px;
  cursor: pointer;
}

.toast-close:hover {
  color: var(--danger);
  background: #fef2f2;
}

/* ============================================================
   TRANSITIONS
============================================================ */

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition:
    opacity 0.2s ease;
}

.modal-fade-enter-active .modal,
.modal-fade-enter-active .confirm-modal,
.modal-fade-leave-active .modal,
.modal-fade-leave-active .confirm-modal {
  transition:
    transform 0.22s ease,
    opacity 0.22s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .modal,
.modal-fade-enter-from .confirm-modal {
  opacity: 0;
  transform: translateY(10px) scale(0.985);
}

.modal-fade-leave-to .modal,
.modal-fade-leave-to .confirm-modal {
  opacity: 0;
  transform: translateY(7px) scale(0.985);
}

.toast-enter-active,
.toast-leave-active {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(25px);
}

.toast-move {
  transition: transform 0.25s ease;
}

/* ============================================================
   UTILITY
============================================================ */

.spinning {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 1180px) {
  .users-page {
    padding: 24px;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .filters-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .filter-search {
    grid-column: 1 / -1;
  }

  .filter-actions {
    grid-column: 1 / -1;
    justify-content: flex-end;
  }

  .access-detail-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 820px) {
  .users-page {
    padding: 18px;
  }

  .header-content {
    align-items: stretch;
    flex-direction: column;
  }

  .btn-new-user {
    align-self: flex-start;
  }

  .table-responsive {
    display: none;
  }

  .mobile-users {
    display: block;
  }

  .pagination {
    align-items: flex-start;
    flex-direction: column;
  }

  .pagination-controls {
    width: 100%;
    justify-content: flex-end;
  }

  .form-modal-header {
    min-height: 115px;
    padding: 20px;
  }

  .form-modal-body {
    padding: 20px;
  }

  .form-modal-footer {
    padding: 12px 20px;
  }

  .detail-banner {
    min-height: 205px;
    padding: 20px;
  }

  .detail-body {
    padding: 20px;
  }

  .detail-footer {
    padding: 12px 20px;
  }
}

@media (max-width: 620px) {
  .users-page {
    padding: 13px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .filter-search,
  .filter-actions {
    grid-column: auto;
  }

  .filter-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }

  .filters-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .refresh-btn {
    width: 100%;
  }

  .table-header {
    padding: 15px;
  }

  .form-grid,
  .detail-grid,
  .activity-grid,
  .access-detail-grid {
    grid-template-columns: 1fr;
  }

  .field-full {
    grid-column: auto;
  }

  .form-modal {
    max-height: 96vh;
    border-radius: 17px;
  }

  .form-modal-header {
    padding: 18px;
  }

  .form-header-left {
    align-items: flex-start;
  }

  .form-header-icon {
    width: 46px;
    height: 46px;
  }

  .form-modal-header h2 {
    font-size: 18px;
  }

  .form-modal-header p {
    line-height: 1.4;
  }

  .form-modal-footer {
    flex-direction: column-reverse;
  }

  .form-modal-footer .btn {
    width: 100%;
  }

  .detail-modal {
    max-height: 96vh;
    border-radius: 17px;
  }

  .detail-profile {
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .detail-avatar {
    width: 62px;
    height: 62px;
  }

  .detail-profile-info h2 {
    font-size: 19px;
  }

  .detail-edit-button {
    width: 100%;
    margin-left: 0;
    justify-content: center;
  }

  .detail-footer {
    flex-direction: column-reverse;
  }

  .detail-footer .btn {
    width: 100%;
  }

  .confirm-modal {
    padding: 24px 19px;
    border-radius: 17px;
  }

  .confirm-actions {
    grid-template-columns: 1fr;
  }

  .toast-container {
    top: 12px;
    right: 12px;
    left: 12px;
    width: auto;
  }
}

@media (max-width: 430px) {
  .header-title-group h1 {
    font-size: 25px;
  }

  .mobile-user-top {
    align-items: flex-start;
    flex-direction: column;
  }

  .mobile-actions {
    grid-template-columns: 1fr;
  }

  .pagination-controls {
    flex-wrap: wrap;
    justify-content: flex-start;
  }

  .pagination-info {
    line-height: 1.5;
  }
}

/* ============================================================
   SCROLLBAR
============================================================ */

.form-modal-body::-webkit-scrollbar,
.detail-body::-webkit-scrollbar,
.table-responsive::-webkit-scrollbar {
  width: 7px;
  height: 7px;
}

.form-modal-body::-webkit-scrollbar-thumb,
.detail-body::-webkit-scrollbar-thumb,
.table-responsive::-webkit-scrollbar-thumb {
  background: #99a9bc;
  border-radius: 99px;
}

.form-modal-body::-webkit-scrollbar-track,
.detail-body::-webkit-scrollbar-track,
.table-responsive::-webkit-scrollbar-track {
  background: #f8fafc;
}

/* =========================================================
   BOUTON CONFIRMATION STATUT
   ========================================================= */

.confirm-status-btn {
  min-width: 145px;
  height: 44px;
  padding: 0 18px;

  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;

  border-radius: 10px;
  border: 1.5px solid;
  background: #ffffff;

  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.1px;

  cursor: pointer;
  transition:
    background-color 0.2s ease,
    color 0.2s ease,
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    transform 0.15s ease;
}

/* Désactiver : blanc + orange */
.confirm-orange {
  color: #d97706;
  border-color: #f59e0b;
  background: #ffffff;
}

.confirm-orange:hover:not(:disabled) {
  color: #ffffff;
  background: #f59e0b;
  border-color: #f59e0b;
  box-shadow: 0 6px 16px rgba(245, 158, 11, 0.22);
  transform: translateY(-1px);
}

/* Activer : blanc + vert */
.confirm-green {
  color: #15803d;
  border-color: #22c55e;
  background: #ffffff;
}

.confirm-green:hover:not(:disabled) {
  color: #ffffff;
  background: #16a34a;
  border-color: #16a34a;
  box-shadow: 0 6px 16px rgba(22, 163, 74, 0.22);
  transform: translateY(-1px);
}

/* Clic */
.confirm-status-btn:active:not(:disabled) {
  transform: translateY(0);
}

/* Disabled */
.confirm-status-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Loading */
.confirm-status-btn .spinning {
  animation: spin 0.8s linear infinite;
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
   RESET PASSWORD MODAL
============================================================ */

.reset-password-layer {
  z-index: 1300;
}

.reset-password-modal {
  width: min(620px, calc(100vw - 28px));
  max-height: min(88vh, 760px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 22px;
  box-shadow:
    0 30px 80px rgba(15, 23, 42, 0.20),
    0 8px 30px rgba(15, 23, 42, 0.08);
  animation: reset-modal-in 0.22s ease-out;
}

@keyframes reset-modal-in {
  from {
    opacity: 0;
    transform: translateY(14px) scale(0.985);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.reset-password-header {
  position: relative;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 15px;
  padding: 25px 25px 20px;
  border-bottom: 1px solid #eef2f7;
  background:
    linear-gradient(
      135deg,
      #f8fafc 0%,
      #ffffff 65%
    );
}

.reset-password-icon {
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  color: #4f46e5;
  background: #eef2ff;
  border: 1px solid #e0e7ff;
  border-radius: 15px;
}

.reset-close {
  position: absolute;
  top: 17px;
  right: 18px;
}

.reset-password-header h2 {
  margin: 3px 40px 5px 0;
  color: #0f172a;
  font-size: 21px;
  line-height: 1.25;
  font-weight: 750;
}

.reset-password-header p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  line-height: 1.55;
}

.reset-target-user {
  margin: 20px 24px 0;
  padding: 14px;
  display: flex;
  align-items: center;
  gap: 13px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 15px;
}

.reset-target-avatar {
  width: 46px;
  height: 46px;
  flex: 0 0 46px;
  display: grid;
  place-items: center;
  color: #3730a3;
  background: #e0e7ff;
  border: 1px solid #c7d2fe;
  border-radius: 13px;
  font-weight: 800;
  font-size: 15px;
}

.reset-target-info {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.reset-target-info strong {
  color: #0f172a;
  font-size: 14px;
}

.reset-target-info span {
  color: #475569;
  font-size: 12px;
  font-weight: 600;
}

.reset-target-info small {
  color: #64748b;
  font-size: 11px;
}

.reset-target-badge {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 9px;
  color: #475569;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 9px;
  font-size: 11px;
  font-weight: 650;
  white-space: nowrap;
}

.reset-security-note {
  margin: 15px 24px 0;
  display: flex;
  gap: 11px;
  padding: 13px;
  color: #475569;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 13px;
}

.reset-security-note-icon {
  width: 34px;
  height: 34px;
  flex: 0 0 34px;
  display: grid;
  place-items: center;
  color: #4f46e5;
  background: #eef2ff;
  border-radius: 9px;
}

.reset-security-note strong {
  display: block;
  margin-bottom: 3px;
  color: #1e293b;
  font-size: 12px;
}

.reset-security-note p {
  margin: 0;
  color: #64748b;
  font-size: 11px;
  line-height: 1.55;
}

.reset-password-body {
  overflow-y: auto;
  padding: 22px 24px;
}

.reset-password-body .field {
  margin-bottom: 17px;
}

.reset-password-body label {
  display: block;
  margin-bottom: 7px;
  color: #334155;
  font-size: 12px;
  font-weight: 700;
}

.reset-password-body label span {
  color: #ef4444;
}

.password-input-wrap {
  position: relative;
}

.password-input-wrap input {
  padding-right: 45px;
}

.password-toggle {
  position: absolute;
  top: 50%;
  right: 9px;
  width: 32px;
  height: 32px;
  transform: translateY(-50%);
  display: grid;
  place-items: center;
  color: #64748b;
  background: transparent;
  border: 0;
  border-radius: 8px;
  cursor: pointer;
}

.password-toggle:hover {
  color: #334155;
  background: #f1f5f9;
}

.password-requirements {
  margin-top: 3px;
  padding: 13px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 13px;
}

.requirements-title {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 9px;
  color: #334155;
  font-size: 12px;
  font-weight: 700;
}

.requirements-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.requirements-grid > div {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #94a3b8;
  font-size: 11px;
}

.requirements-grid > div.valid {
  color: #16a34a;
}

.reset-password-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 17px 24px;
  border-top: 1px solid #eef2f7;
  background: #ffffff;
}

.reset-password-submit {
  height: 46px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 17px;
  color: #ffffff;
  background: #2563EB;
  border: 0;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
  transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.reset-password-submit:hover:not(:disabled) {
  background: #1D4ED8;
  box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
  transform: translateY(-1px);
}

.reset-password-submit:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
}

.reset-password-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Password action button */

.icon-password {
  color: #7c3aed;
  background: #f5f3ff;
  border-color: #ddd6fe;
}

.icon-password:hover {
  color: #fcfbfd;
  background: #03b218;
  border-color: #ffffff;
}

.mobile-action.password {
  color: #6d28d9;
  background: #f5f3ff;
  border-color: #ddd6fe;
}

/* ============================================================
   RESPONSIVE RESET PASSWORD
============================================================ */

@media (max-width: 640px) {
  .reset-password-modal {
    width: calc(100vw - 20px);
    max-height: 92vh;
    border-radius: 18px;
  }

  .reset-password-header {
    padding: 20px 18px 17px;
    gap: 11px;
  }

  .reset-password-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
  }

  .reset-password-header h2 {
    font-size: 18px;
  }

  .reset-target-user {
    margin-left: 18px;
    margin-right: 18px;
  }

  .reset-target-badge {
    display: none;
  }

  .reset-security-note {
    margin-left: 18px;
    margin-right: 18px;
  }

  .reset-password-body {
    padding: 18px;
  }

  .requirements-grid {
    grid-template-columns: 1fr;
  }

  .reset-password-footer {
    padding: 14px 18px;
    flex-direction: column-reverse;
  }

  .reset-password-footer .btn {
    width: 100%;
  }
}

/* Esorina ilay bouton œil automatique an'ny navigateur */
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
  display: none;
}
</style>