<template>
  <div class="roles-page">

    <!-- =========================================================
         HEADER
    ========================================================== -->
    <section class="page-header">
      <div class="header-left">
        <div class="header-icon">
          <ShieldCheck :size="26" />
        </div>

        <div>
          <h1>Gestion des rôles</h1>
          <p>
            Gérez les rôles, leurs permissions et leur statut
            d'accès à l'application.
          </p>
        </div>
      </div>

      <button
        v-if="canCreate"
        class="btn btn-primary"
        type="button"
        @click="openCreateModal"
      >
        <Plus :size="18" />
        <span>Nouveau rôle</span>
      </button>
    </section>

    <!-- =========================================================
         ALERT
    ========================================================== -->
    <Transition name="alert">
      <div
        v-if="alert.visible"
        class="global-alert"
        :class="`alert-${alert.type}`"
      >
        <CheckCircle2
          v-if="alert.type === 'success'"
          :size="19"
        />

        <AlertCircle
          v-else
          :size="19"
        />

        <span>{{ alert.message }}</span>

        <button
          type="button"
          class="alert-close"
          @click="closeAlert"
        >
          <X :size="17" />
        </button>
      </div>
    </Transition>

    <!-- =========================================================
         STATISTICS
    ========================================================== -->
    <section class="stats-grid">

      <div class="stat-card">
        <div class="stat-icon stat-blue">
          <Shield :size="21" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Total rôles</span>
          <strong>{{ roles.length }}</strong>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-green">
          <ShieldCheck :size="21" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Rôles actifs</span>
          <strong>{{ activeRolesCount }}</strong>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-orange">
          <Users :size="21" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Utilisateurs affectés</span>
          <strong>{{ usersAssignedCount }}</strong>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-purple">
          <KeyRound :size="21" />
        </div>

        <div class="stat-content">
          <span class="stat-label">Permissions disponibles</span>
          <strong>{{ visiblePermissionsCount }}</strong>
        </div>
      </div>

    </section>

    <!-- =========================================================
         FILTERS
    ========================================================== -->
    <section class="filters-card">

      <div class="filters-top">

        <div class="search-wrapper">
          <Search :size="19" class="search-icon" />

          <input
            v-model="search"
            type="text"
            class="search-input"
            placeholder="Rechercher un rôle, une description..."
          />

          <button
            v-if="search"
            type="button"
            class="search-clear"
            @click="search = ''"
          >
            <X :size="16" />
          </button>
        </div>

        <div class="filter-actions">

          <div class="select-wrapper">
            <Filter :size="16" />

            <select v-model="statusFilter">
              <option value="all">Tous les statuts</option>
              <option value="active">Actifs</option>
              <option value="inactive">Inactifs</option>
            </select>
          </div>

          <div class="select-wrapper">
            <Shield :size="16" />

            <select v-model="typeFilter">
              <option value="all">Tous les types</option>
              <option value="system">Système</option>
              <option value="custom">Personnalisés</option>
            </select>
          </div>

          <button
            type="button"
            class="btn btn-secondary"
            @click="resetFilters"
          >
            <RotateCcw :size="17" />
            <span>Réinitialiser</span>
          </button>

        </div>

      </div>

      <div class="results-info">
        <span>
          <strong>{{ filteredRoles.length }}</strong>
          rôle(s) trouvé(s)
        </span>
      </div>

    </section>

    <!-- =========================================================
         TABLE
    ========================================================== -->
    <section class="table-card">

      <!-- Loading -->
      <div
        v-if="loading"
        class="table-loading"
      >
        <Loader2
          :size="30"
          class="spin"
        />

        <span>Chargement des rôles...</span>
      </div>

      <!-- Empty -->
      <div
        v-else-if="paginatedRoles.length === 0"
        class="empty-state"
      >
        <div class="empty-icon">
          <ShieldOff :size="32" />
        </div>

        <h3>Aucun rôle trouvé</h3>

        <p>
          Aucun rôle ne correspond aux critères de recherche.
        </p>

        <button
          type="button"
          class="btn btn-secondary"
          @click="resetFilters"
        >
          <RotateCcw :size="17" />
          Réinitialiser
        </button>
      </div>

      <!-- Desktop table -->
      <div
        v-else
        class="table-responsive"
      >
        <table class="roles-table">

          <thead>
            <tr>
              <th>Rôle</th>
              <th>Type</th>
              <th>Statut</th>
              <th>Utilisateurs</th>
              <th>Permissions</th>
              <th>Création</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="role in paginatedRoles"
              :key="role.id_role"
            >

              <!-- ROLE -->
              <td>
                <div class="role-cell">

                  <div
                    class="role-avatar"
                    :class="{
                      'role-system': role.systeme,
                      'role-custom': !role.systeme
                    }"
                  >
                    <ShieldCheck
                      v-if="role.systeme"
                      :size="19"
                    />

                    <Shield
                      v-else
                      :size="19"
                    />
                  </div>

                  <div class="role-info">

                    <div class="role-name">
                      {{ role.nom_role }}

                      <span
                        v-if="role.systeme"
                        class="system-badge"
                      >
                        <Lock :size="12" />
                        Système
                      </span>
                    </div>

                    <div class="role-description">
                      {{ role.description || 'Aucune description' }}
                    </div>

                  </div>

                </div>
              </td>

              <!-- TYPE -->
              <td>
                <span
                  class="type-badge"
                  :class="role.systeme ? 'type-system' : 'type-custom'"
                >
                  <Lock
                    v-if="role.systeme"
                    :size="13"
                  />

                  <Settings
                    v-else
                    :size="13"
                  />

                  {{ role.systeme ? 'Système' : 'Personnalisé' }}
                </span>
              </td>

              <!-- STATUS -->
              <td>
                <button
                  v-if="!role.systeme && canUpdate"
                  type="button"
                  class="status-button"
                  :class="role.actif ? 'status-active' : 'status-inactive'"
                  :disabled="statusUpdatingId === role.id_role"
                  @click="toggleRoleStatus(role)"
                >
                  <Loader2
                    v-if="statusUpdatingId === role.id_role"
                    :size="13"
                    class="spin"
                  />

                  <CheckCircle2
                    v-else-if="role.actif"
                    :size="13"
                  />

                  <ShieldOff
                    v-else
                    :size="13"
                  />

                  {{ role.actif ? 'Actif' : 'Inactif' }}
                </button>

                <span
                  v-else
                  class="status-button"
                  :class="role.actif ? 'status-active' : 'status-inactive'"
                >
                  <CheckCircle2
                    v-if="role.actif"
                    :size="13"
                  />

                  <ShieldOff
                    v-else
                    :size="13"
                  />

                  {{ role.actif ? 'Actif' : 'Inactif' }}
                </span>
              </td>

              <!-- USERS -->
              <td>
                <div class="count-cell">
                  <Users :size="16" />
                  <strong>{{ role.utilisateurs_count ?? 0 }}</strong>
                </div>
              </td>

              <!-- PERMISSIONS -->
              <td>
                <div class="count-cell permission-count">
                  <KeyRound :size="16" />
                  <strong>{{ role.permissions_count ?? 0 }}</strong>
                </div>
              </td>

              <!-- DATE -->
              <td>
                <div class="date-cell">
                  <CalendarDays :size="15" />
                  {{ formatDate(role.created_at) }}
                </div>
              </td>

              <!-- ACTIONS -->
              <td>
                <div class="actions">
                  <button
                    v-if="canView"
                    type="button"
                    class="icon-btn view"
                    title="Voir les détails"
                    @click="openDetailsModal(role)"
                  >
                    <Eye :size="17" />
                  </button>

                  <button
                    v-if="canUpdate && !role.systeme"
                    type="button"
                    class="icon-btn edit"
                    title="Modifier"
                    @click="openEditModal(role)"
                  >
                    <Pencil :size="17" />
                  </button>

                  <button
                    v-if="canUpdate && !role.systeme"
                    type="button"
                    class="icon-btn permissions"
                    title="Gérer les permissions"
                    @click="openPermissionModal(role)"
                  >
                    <KeyRound :size="17" />
                  </button>

                  <button
                    v-if="canDelete && !role.systeme"
                    type="button"
                    class="icon-btn delete"
                    title="Supprimer"
                    @click="openDeleteModal(role)"
                  >
                    <Trash2 :size="17" />
                  </button>

                  <span
                    v-if="role.systeme"
                    class="system-lock"
                    title="Rôle système protégé"
                  >
                    <LockKeyhole :size="17" />
                  </span>

                </div>
              </td>

            </tr>

          </tbody>

        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="!loading && filteredRoles.length > 0"
        class="pagination"
      >

        <div class="pagination-info">
          Affichage
          <strong>{{ paginationStart }}</strong>
          –
          <strong>{{ paginationEnd }}</strong>
          sur
          <strong>{{ filteredRoles.length }}</strong>
        </div>

        <div class="pagination-controls">

          <select
            v-model.number="perPage"
            class="per-page"
          >
            <option :value="5">5 / page</option>
            <option :value="10">10 / page</option>
            <option :value="20">20 / page</option>
            <option :value="50">50 / page</option>
          </select>

          <button
            type="button"
            class="page-btn"
            :disabled="currentPage <= 1"
            @click="currentPage--"
          >
            <ChevronLeft :size="17" />
          </button>

          <span class="page-number">
            {{ currentPage }} / {{ totalPages }}
          </span>

          <button
            type="button"
            class="page-btn"
            :disabled="currentPage >= totalPages"
            @click="currentPage++"
          >
            <ChevronRight :size="17" />
          </button>

        </div>
      </div>

    </section>

    <!-- =========================================================
         CREATE / EDIT ROLE MODAL
    ========================================================== -->
    <Transition name="modal">
      <div
        v-if="showRoleModal"
        class="modal-overlay"
        @click.self="closeRoleModal"
      >

        <div class="modal role-modal">

          <div class="modal-header">

            <div class="modal-title-wrapper">

              <div class="modal-icon">
                <Pencil
                  v-if="editingRole"
                  :size="21"
                />

                <Plus
                  v-else
                  :size="21"
                />
              </div>

              <div>
                <h2>
                  {{ editingRole ? 'Modifier le rôle' : 'Créer un rôle' }}
                </h2>

                <p>
                  {{
                    editingRole
                      ? 'Modifiez les informations du rôle sélectionné.'
                      : 'Créez un nouveau rôle et définissez ses informations.'
                  }}
                </p>
              </div>

            </div>

            <button
              type="button"
              class="modal-close"
              :disabled="saving"
              @click="closeRoleModal"
            >
              <X :size="20" />
            </button>

          </div>

          <form
            class="modal-body"
            @submit.prevent="saveRole"
          >

            <!-- Error -->
            <div
              v-if="formError"
              class="form-alert error"
            >
              <AlertCircle :size="18" />
              <span>{{ formError }}</span>
            </div>

            <!-- Nom -->
            <div class="form-group">

              <label>
                Nom du rôle
                <span>*</span>
              </label>

              <input
                v-model="roleForm.nom_role"
                type="text"
                class="form-input"
                :class="{ invalid: formErrors.nom_role }"
                placeholder="Ex : Archiviste"
                maxlength="100"
                :disabled="saving"
              />

              <small
                v-if="formErrors.nom_role"
                class="field-error"
              >
                {{ formErrors.nom_role }}
              </small>

            </div>

            <!-- Description -->
            <div class="form-group">

              <label>Description</label>

              <textarea
                v-model="roleForm.description"
                class="form-textarea"
                :class="{ invalid: formErrors.description }"
                rows="4"
                maxlength="500"
                placeholder="Description du rôle..."
                :disabled="saving"
              />

              <div class="textarea-footer">
                <small>
                  Description facultative
                </small>

                <small>
                  {{ roleForm.description.length }}/500
                </small>
              </div>

              <small
                v-if="formErrors.description"
                class="field-error"
              >
                {{ formErrors.description }}
              </small>

            </div>

            <!-- Status -->
            <div class="form-group">

              <label>Statut</label>

              <label class="switch-row">

                <input
                  v-model="roleForm.actif"
                  type="checkbox"
                  :disabled="saving"
                />

                <span class="switch">
                  <span></span>
                </span>

                <span class="switch-text">
                  <strong>
                    {{ roleForm.actif ? 'Rôle actif' : 'Rôle inactif' }}
                  </strong>

                  <small>
                    {{
                      roleForm.actif
                        ? 'Le rôle peut être utilisé.'
                        : 'Le rôle ne pourra pas être utilisé.'
                    }}
                  </small>
                </span>

              </label>

            </div>

            <!-- Permissions -->
            <div class="form-group">

              <div class="permission-header">

                <div>
                  <label>Permissions</label>

                  <small>
                    Sélectionnez les permissions attribuées à ce rôle.
                  </small>
                </div>

                <span class="permission-total">
                  {{ visiblePermissionsCount > 0
                    ? selectedPermissionIds.filter(id =>
                        visiblePermissions.some(
                          permission =>
                            Number(permission.id_permission) === Number(id)
                        )
                      ).length
                    : 0
                  }}
                  /
                  {{ visiblePermissionsCount }}
                  sélectionnée(s)
                  
                </span>

              </div>

              <!-- Loading permissions -->
              <div
                v-if="loadingRolePermissions"
                class="permissions-loading"
              >
                <Loader2
                  :size="21"
                  class="spin"
                />

                <span>
                  Chargement des permissions...
                </span>
              </div>

              <div
                v-else-if="permissionsByPage.length === 0"
                class="permissions-empty"
              >
                <KeyRound :size="25" />
                <span>Aucune permission disponible.</span>
              </div>

              <div
                v-else
                class="permissions-groups"
              >

                <div
                  v-for="group in permissionsByPage"
                  :key="group.page"
                  class="permission-group"
                >

                  <div class="permission-group-header">

                    <div class="permission-page">
                      <component
                        :is="getPermissionIcon(group.page)"
                        :size="17"
                      />

                      <strong>
                        {{ group.label }}
                      </strong>
                    </div>

                    <button
                      v-if="canUpdate || !editingRole"
                      type="button"
                      class="select-group-btn"
                      :disabled="saving"
                      @click="togglePermissionGroup(group)"
                    >
                      {{
                        isPermissionGroupFullySelected(group)
                          ? 'Désélectionner'
                          : 'Tout sélectionner'
                      }}
                    </button>

                  </div>

                  <div class="permission-list">

                    <label
                      v-for="permission in group.permissions"
                      :key="permission.id_permission"
                      class="permission-item"
                      :class="{
                        selected:
                          selectedPermissionIds.includes(
                            permission.id_permission
                          )
                      }"
                    >

                      <input
                        v-model="selectedPermissionIds"
                        type="checkbox"
                        :value="permission.id_permission"
                        :disabled="saving"
                      />

                      <span class="permission-check">
                        <Check :size="13" />
                      </span>

                      <span class="permission-info">

                        <strong>
                          {{ permission.code_permission }}
                        </strong>

                        <small>
                          {{ permission.libelle || permission.description || 'Permission système' }}
                        </small>

                      </span>

                    </label>

                  </div>

                </div>

              </div>

              <small
                v-if="formErrors.permissions"
                class="field-error"
              >
                {{ formErrors.permissions }}
              </small>

            </div>

            <!-- Footer -->
            <div class="modal-footer">

              <button
                type="button"
                class="btn btn-secondary"
                :disabled="saving"
                @click="closeRoleModal"
              >
                Annuler
              </button>

              <button
                type="submit"
                class="btn btn-primary"
                :disabled="saving || loadingRolePermissions"
              >

                <Loader2
                  v-if="saving"
                  :size="18"
                  class="spin"
                />

                <Save
                  v-else
                  :size="18"
                />

                {{
                  saving
                    ? 'Enregistrement...'
                    : editingRole
                      ? 'Enregistrer les modifications'
                      : 'Créer le rôle'
                }}

              </button>

            </div>

          </form>

        </div>

      </div>
    </Transition>

    <!-- =========================================================
         PERMISSIONS MODAL
    ========================================================== -->
    <Transition name="modal">
      <div
        v-if="showPermissionModal"
        class="modal-overlay"
        @click.self="closePermissionModal"
      >

        <div class="modal permission-modal">

          <div class="modal-header">

            <div class="modal-title-wrapper">

              <div class="modal-icon permission-icon">
                <KeyRound :size="21" />
              </div>

              <div>
                <h2>Gestion des permissions</h2>

                <p>
                  {{ selectedRole?.nom_role }}
                </p>
              </div>

            </div>

            <button
              type="button"
              class="modal-close"
              :disabled="savingPermissions"
              @click="closePermissionModal"
            >
              <X :size="20" />
            </button>

          </div>

          <div class="modal-body">

            <div
              v-if="permissionModalError"
              class="form-alert error"
            >
              <AlertCircle :size="18" />
              <span>{{ permissionModalError }}</span>
            </div>

            <div class="permission-summary">
              <div>
                <KeyRound :size="18" />
                <strong>
                  {{ permissionModalSelectedIds.length }}
                </strong>
                permission(s) sélectionnée(s)
              </div>
            </div>

            <div
              v-if="permissionsLoading"
              class="permissions-loading"
            >
              <Loader2
                :size="22"
                class="spin"
              />

              <span>Chargement des permissions...</span>
            </div>

            <div
              v-else
              class="permissions-groups"
            >

              <div
                v-for="group in permissionsByPage"
                :key="`modal-${group.page}`"
                class="permission-group"
              >

                <div class="permission-group-header">

                  <div class="permission-page">

                    <component
                      :is="getPermissionIcon(group.page)"
                      :size="17"
                    />

                    <strong>
                      {{ group.label }}
                    </strong>

                  </div>

                  <button
                    type="button"
                    class="select-group-btn"
                    :disabled="savingPermissions"
                    @click="togglePermissionGroupModal(group)"
                  >
                    {{
                      isPermissionGroupFullySelectedModal(group)
                        ? 'Désélectionner'
                        : 'Tout sélectionner'
                    }}
                  </button>

                </div>

                <div class="permission-list">

                  <label
                    v-for="permission in group.permissions"
                    :key="`modal-${permission.id_permission}`"
                    class="permission-item"
                    :class="{
                      selected:
                        permissionModalSelectedIds.includes(
                          permission.id_permission
                        )
                    }"
                  >

                    <input
                      v-model="permissionModalSelectedIds"
                      type="checkbox"
                      :value="permission.id_permission"
                      :disabled="savingPermissions"
                    />

                    <span class="permission-check">
                      <Check :size="13" />
                    </span>

                    <span class="permission-info">

                      <strong>
                        {{ permission.code_permission }}
                      </strong>

                      <small>
                        {{ permission.libelle || permission.description || 'Permission système' }}
                      </small>

                    </span>

                  </label>

                </div>

              </div>

            </div>

          </div>

          <div class="modal-footer">

            <button
              type="button"
              class="btn btn-secondary"
              :disabled="savingPermissions"
              @click="closePermissionModal"
            >
              Annuler
            </button>

            <button
              type="button"
              class="btn btn-primary"
              :disabled="savingPermissions || permissionsLoading"
              @click="savePermissions"
            >

              <Loader2
                v-if="savingPermissions"
                :size="18"
                class="spin"
              />

              <Save
                v-else
                :size="18"
              />

              {{
                savingPermissions
                  ? 'Enregistrement...'
                  : 'Enregistrer les permissions'
              }}

            </button>

          </div>

        </div>

      </div>
    </Transition>

    <!-- =========================================================
         DETAILS MODAL
    ========================================================== -->
    <Transition name="modal">
      <div
        v-if="showDetailsModal"
        class="modal-overlay"
        @click.self="closeDetailsModal"
      >

        <div class="modal details-modal">

          <div class="modal-header">

            <div class="modal-title-wrapper">

              <div class="modal-icon">
                <Eye :size="21" />
              </div>

              <div>
                <h2>Détails du rôle</h2>
                <p>
                  Informations et permissions associées.
                </p>
              </div>

            </div>

            <button
              type="button"
              class="modal-close"
              :disabled="detailsLoading"
              @click="closeDetailsModal"
            >
              <X :size="20" />
            </button>

          </div>

          <div class="modal-body">

            <div
              v-if="detailsLoading"
              class="details-loading"
            >
              <Loader2
                :size="30"
                class="spin"
              />

              <span>Chargement des détails...</span>
            </div>

            <template v-else-if="selectedRoleDetails">

              <div class="details-hero">

                <div class="details-avatar">
                  <ShieldCheck :size="28" />
                </div>

                <div>
                  <h3>
                    {{ selectedRoleDetails.nom_role }}
                  </h3>

                  <span
                    class="type-badge"
                    :class="
                      selectedRoleDetails.systeme
                        ? 'type-system'
                        : 'type-custom'
                    "
                  >
                    {{
                      selectedRoleDetails.systeme
                        ? 'Rôle système'
                        : 'Rôle personnalisé'
                    }}
                  </span>
                </div>

              </div>

              <div class="details-grid">

                <div class="detail-box">
                  <span>Statut</span>

                  <strong
                    :class="
                      selectedRoleDetails.actif
                        ? 'text-success'
                        : 'text-danger'
                    "
                  >
                    {{
                      selectedRoleDetails.actif
                        ? 'Actif'
                        : 'Inactif'
                    }}
                  </strong>
                </div>

                <div class="detail-box">
                  <span>Utilisateurs</span>

                  <strong>
                    {{
                      selectedRoleDetails.utilisateurs_count ?? 0
                    }}
                  </strong>
                </div>

                <div class="detail-box">
                  <span>Permissions</span>

                  <strong>
                    {{
                      selectedRoleDetails.permissions_count ??
                      selectedRoleDetails.permissions?.length ??
                      0
                    }}
                  </strong>
                </div>

                <div class="detail-box">
                  <span>Créé le</span>

                  <strong>
                    {{
                      formatDate(
                        selectedRoleDetails.created_at
                      )
                    }}
                  </strong>
                </div>

              </div>

              <div class="details-description">

                <div class="details-section-title">
                  <FileText :size="17" />
                  Description
                </div>

                <p>
                  {{
                    selectedRoleDetails.description ||
                    'Aucune description.'
                  }}
                </p>

              </div>

              <div class="details-permissions">

                <div class="details-section-title">
                  <KeyRound :size="17" />
                  Permissions
                </div>

                <div
                  v-if="
                    !selectedRoleDetails.permissions ||
                    selectedRoleDetails.permissions.length === 0
                  "
                  class="no-permissions"
                >
                  <KeyRound :size="22" />
                  <span>Aucune permission attribuée.</span>
                </div>

                <div
                  v-else
                  class="permission-tags"
                >

                  <span
                    v-for="permission in selectedRoleDetails.permissions"
                    :key="permission.id_permission"
                    class="permission-tag"
                  >
                    <Check :size="12" />
                    {{ permission.code_permission }}
                  </span>

                </div>

              </div>

            </template>

          </div>

          <div class="modal-footer">

            <button
              type="button"
              class="btn btn-secondary"
              @click="closeDetailsModal"
            >
              Fermer
            </button>

            <button
              v-if="
                selectedRoleDetails &&
                canUpdate &&
                !selectedRoleDetails.systeme
              "
              type="button"
              class="btn btn-primary"
              @click="editFromDetails"
            >
              <Pencil :size="17" />
              Modifier
            </button>

          </div>

        </div>

      </div>
    </Transition>

    <!-- =========================================================
         DELETE CONFIRMATION MODAL
    ========================================================== -->
    <Transition name="modal">
      <div
        v-if="showDeleteModal"
        class="modal-overlay danger-overlay"
        @click.self="closeDeleteModal"
      >

        <div class="modal delete-modal">

          <div class="delete-icon">
            <Trash2 :size="26" />
          </div>

          <div class="delete-content">

            <h2>Supprimer ce rôle ?</h2>

            <p>
              Vous êtes sur le point de supprimer le rôle
              <strong>
                {{ selectedRole?.nom_role }}
              </strong>.
            </p>

            <p class="delete-warning">
              Cette action est définitive.
            </p>

          </div>

          <div
            v-if="deleteError"
            class="form-alert error"
          >
            <AlertTriangle :size="18" />
            <span>{{ deleteError }}</span>
          </div>

          <div class="modal-footer delete-footer">

            <button
              type="button"
              class="btn btn-secondary"
              :disabled="deleting"
              @click="closeDeleteModal"
            >
              Annuler
            </button>

            <button
              type="button"
              class="btn btn-danger"
              :disabled="deleting"
              @click="deleteRole"
            >

              <Loader2
                v-if="deleting"
                :size="18"
                class="spin"
              />

              <Trash2
                v-else
                :size="18"
              />

              {{
                deleting
                  ? 'Suppression...'
                  : 'Supprimer définitivement'
              }}

            </button>

          </div>

        </div>

      </div>
    </Transition>

  </div>
</template>

<script setup>
import {
  ref,
  reactive,
  computed,
  onMounted,
  watch
} from 'vue'

import api from '@/api/api'
import { useAuthStore } from '@/stores/auth'

import {
  Shield,
  ShieldCheck,
  ShieldOff,
  Lock,
  LockKeyhole,
  Plus,
  Search,
  X,
  Filter,
  RotateCcw,
  Users,
  KeyRound,
  Pencil,
  Trash2,
  Eye,
  CalendarDays,
  ChevronLeft,
  ChevronRight,
  Check,
  CheckCircle2,
  AlertCircle,
  AlertTriangle,
  Loader2,
  Save,
  FileText,
  LayoutDashboard,
  Mail,
  Send,
  FileArchive,
  UserCog,
  Settings,
  Activity,
  GraduationCap,
  Database,
  Globe
} from 'lucide-vue-next'

/* ============================================================
   STATE
============================================================ */

const roles = ref([])
const allPermissions = ref([])

const loading = ref(false)
const permissionsLoading = ref(false)
const saving = ref(false)
const savingPermissions = ref(false)
const deleting = ref(false)
const detailsLoading = ref(false)

const loadingRolePermissions = ref(false)

const statusUpdatingId = ref(null)

const search = ref('')
const statusFilter = ref('all')
const typeFilter = ref('all')

const currentPage = ref(1)
const perPage = ref(10)

/* ============================================================
   ALERT
============================================================ */

const alert = reactive({
  visible: false,
  message: '',
  type: 'success'
})

let alertTimer = null

function showAlert(message, type = 'success') {
  alert.visible = true
  alert.message = message
  alert.type = type

  if (alertTimer) {
    clearTimeout(alertTimer)
  }

  alertTimer = setTimeout(() => {
    closeAlert()
  }, 4500)
}

function closeAlert() {
  alert.visible = false
}

/* ============================================================
   MODALS
============================================================ */

const showRoleModal = ref(false)
const showPermissionModal = ref(false)
const showDetailsModal = ref(false)
const showDeleteModal = ref(false)

const editingRole = ref(null)
const selectedRole = ref(null)
const selectedRoleDetails = ref(null)

const formError = ref('')
const permissionModalError = ref('')
const deleteError = ref('')

/* ============================================================
   FORMS
============================================================ */

const roleForm = reactive({
  nom_role: '',
  description: '',
  actif: true
})

const formErrors = reactive({
  nom_role: '',
  description: '',
  permissions: ''
})

const selectedPermissionIds = ref([])
const permissionModalSelectedIds = ref([])

/* ============================================================
   CURRENT USER / PERMISSIONS
============================================================ */

const authStore = useAuthStore()

const canView = computed(() => {
  return authStore.hasPermission('roles.view')
})

const canCreate = computed(() => {
  return authStore.hasPermission('roles.create')
})

const canUpdate = computed(() => {
  return authStore.hasPermission('roles.update')
})

const canDelete = computed(() => {
  return authStore.hasPermission('roles.delete')
})
/* ============================================================
   STATISTICS
============================================================ */

const activeRolesCount = computed(() => {
  return roles.value.filter(role => Boolean(role.actif)).length
})

const usersAssignedCount = computed(() => {
  return roles.value.reduce(
    (total, role) =>
      total + Number(role.utilisateurs_count || 0),
    0
  )
})

/* ============================================================
   FILTERS
============================================================ */

const filteredRoles = computed(() => {
  const term = search.value.trim().toLowerCase()

  return roles.value.filter(role => {

    const matchesSearch =
      !term ||
      String(role.nom_role || '')
        .toLowerCase()
        .includes(term) ||
      String(role.description || '')
        .toLowerCase()
        .includes(term)

    const matchesStatus =
      statusFilter.value === 'all' ||
      (
        statusFilter.value === 'active' &&
        Boolean(role.actif)
      ) ||
      (
        statusFilter.value === 'inactive' &&
        !role.actif
      )

    const matchesType =
      typeFilter.value === 'all' ||
      (
        typeFilter.value === 'system' &&
        Boolean(role.systeme)
      ) ||
      (
        typeFilter.value === 'custom' &&
        !role.systeme
      )

    return (
      matchesSearch &&
      matchesStatus &&
      matchesType
    )
  })
})

const totalPages = computed(() => {
  return Math.max(
    1,
    Math.ceil(
      filteredRoles.value.length / perPage.value
    )
  )
})

const paginatedRoles = computed(() => {
  const start =
    (currentPage.value - 1) * perPage.value

  return filteredRoles.value.slice(
    start,
    start + perPage.value
  )
})

const paginationStart = computed(() => {
  if (!filteredRoles.value.length) {
    return 0
  }

  return (
    (currentPage.value - 1) *
      perPage.value +
    1
  )
})

const paginationEnd = computed(() => {
  return Math.min(
    currentPage.value * perPage.value,
    filteredRoles.value.length
  )
})

function resetFilters() {
  search.value = ''
  statusFilter.value = 'all'
  typeFilter.value = 'all'
  currentPage.value = 1
}

/* ============================================================
   PERMISSIONS GROUPING
============================================================ */

const permissionPageLabels = {
  dashboard: 'Tableau de bord',
  courriers_arrives: 'Courriers arrivés',
  courriers_depart: 'Courriers départ',
  documents: 'Documents',
  utilisateurs: 'Utilisateurs',
  roles: 'Rôles',
  grades: 'Grades',
  journal: 'Journal des activités'
}

function getPermissionPage(permission) {
  const code =
    permission?.code_permission || ''

  return code.split('.')[0] || 'autres'
}

function getPermissionLabel(page) {
  return (
    permissionPageLabels[page] ||
    page.charAt(0).toUpperCase() +
      page.slice(1).replaceAll('_', ' ')
  )
}

/* ============================================================
   PERMISSIONS GROUPING
============================================================ */

// Permissions Grades tsy aseho amin'ny formulaire
const visiblePermissions = computed(() => {
  return allPermissions.value.filter(permission => {
    const code = String(
      permission.code_permission || ''
    ).toLowerCase()

    return !code.startsWith('grades.')
  })
})

// Compteur permissions hita amin'ny formulaire
const visiblePermissionsCount = computed(() => {
  return visiblePermissions.value.length
})

const permissionsByPage = computed(() => {
  const groups = {}

  for (const permission of visiblePermissions.value) {
    const page = getPermissionPage(permission)

    if (!groups[page]) {
      groups[page] = []
    }

    groups[page].push(permission)
  }

  return Object.entries(groups)
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([page, permissions]) => ({
      page,
      label: getPermissionLabel(page),
      permissions: permissions.sort(
        (a, b) =>
          String(a.code_permission)
            .localeCompare(String(b.code_permission))
      )
    }))
})

function getPermissionIcon(page) {
  const icons = {
    dashboard: LayoutDashboard,
    courriers_arrives: Mail,
    courriers_depart: Send,
    documents: FileArchive,
    utilisateurs: UserCog,
    roles: ShieldCheck,
    grades: GraduationCap,
    journal: Activity,
    database: Database,
    system: Settings,
    autres: Globe
  }

  return icons[page] || Settings
}

/* ============================================================
   LOAD ROLES
============================================================ */

async function loadRoles() {
  loading.value = true

  try {
    const response =
      await api.get('/roles')

    roles.value =
      Array.isArray(response?.data)
        ? response.data
        : []

  } catch (error) {

    console.error(
      'Erreur chargement rôles:',
      error
    )

    if (error?.status === 403) {
      showAlert(
        'Vous n’avez pas la permission de consulter les rôles.',
        'error'
      )
    } else {
      showAlert(
        error?.data?.message ||
        error?.message ||
        'Impossible de charger les rôles.',
        'error'
      )
    }

    roles.value = []

  } finally {
    loading.value = false
  }
}

/* ============================================================
   LOAD PERMISSIONS
============================================================ */

async function loadPermissions() {
  permissionsLoading.value = true

  try {

    const response =
      await api.get('/permissions')

    allPermissions.value =
      Array.isArray(response?.data)
        ? response.data
        : []

  } catch (error) {

    console.error(
      'Erreur chargement permissions:',
      error
    )

    showAlert(
      error?.data?.message ||
      error?.message ||
      'Impossible de charger les permissions.',
      'error'
    )

  } finally {
    permissionsLoading.value = false
  }
}

/* ============================================================
   FORM RESET
============================================================ */

function resetForm() {
  roleForm.nom_role = ''
  roleForm.description = ''
  roleForm.actif = true

  selectedPermissionIds.value = []

  formError.value = ''

  formErrors.nom_role = ''
  formErrors.description = ''
  formErrors.permissions = ''
}

/* ============================================================
   CREATE MODAL
============================================================ */

function openCreateModal() {

  if (!canCreate.value) {
    showAlert(
      'Vous n’avez pas la permission de créer un rôle.',
      'error'
    )
    return
  }

  editingRole.value = null

  resetForm()

  showRoleModal.value = true
}

/* ============================================================
   CLOSE ROLE MODAL
   IMPORTANT:
   force=true permet de fermer après sauvegarde
============================================================ */

function closeRoleModal(force = false) {

  if (saving.value && !force) {
    return
  }

  showRoleModal.value = false
  editingRole.value = null

  formError.value = ''

  formErrors.nom_role = ''
  formErrors.description = ''
  formErrors.permissions = ''

  loadingRolePermissions.value = false
}

/* ============================================================
   EDIT ROLE
============================================================ */

async function openEditModal(role) {

  if (!canUpdate.value) {
    showAlert(
      'Vous n’avez pas la permission de modifier les rôles.',
      'error'
    )
    return
  }

  if (role.systeme) {
    showAlert(
      'Le rôle système ne peut pas être modifié.',
      'error'
    )
    return
  }

  editingRole.value = role

  resetForm()

  roleForm.nom_role =
    role.nom_role || ''

  roleForm.description =
    role.description || ''

  roleForm.actif =
    Boolean(role.actif)

  selectedPermissionIds.value = []

  showRoleModal.value = true

  loadingRolePermissions.value = true

  try {

    const response =
      await api.get(
        `/roles/${role.id_role}/permissions`
      )

    const permissions =
      response?.data?.permissions ||
      response?.data?.role?.permissions ||
      []

    selectedPermissionIds.value =
      permissions
        .map(permission =>
          Number(permission.id_permission)
        )
        .filter(id => Number.isInteger(id))

  } catch (error) {

    console.error(
      'Erreur chargement permissions du rôle:',
      error
    )

    formError.value =
      error?.data?.message ||
      error?.message ||
      'Impossible de récupérer les permissions actuelles.'

  } finally {
    loadingRolePermissions.value = false
  }
}

/* ============================================================
   VALIDATION FORM
============================================================ */

function validateRoleForm() {

  formErrors.nom_role = ''
  formErrors.description = ''
  formErrors.permissions = ''
  formError.value = ''

  const name =
    roleForm.nom_role.trim()

  if (!name) {
    formErrors.nom_role =
      'Le nom du rôle est obligatoire.'
    return false
  }

  if (name.length < 2) {
    formErrors.nom_role =
      'Le nom du rôle doit contenir au moins 2 caractères.'
    return false
  }

  if (name.length > 100) {
    formErrors.nom_role =
      'Le nom du rôle ne peut pas dépasser 100 caractères.'
    return false
  }

  return true
}

/* ============================================================
   HANDLE LARAVEL ERRORS
============================================================ */

function handleValidationError(error) {

  const data =
    error?.data

  if (error?.status === 422) {

    const errors =
      data?.errors || {}

    formErrors.nom_role =
      firstValidationMessage(
        errors.nom_role
      )

    formErrors.description =
      firstValidationMessage(
        errors.description
      )

    formErrors.permissions =
      firstValidationMessage(
        errors.permissions
      )

    formError.value =
      data?.message ||
      'Veuillez corriger les erreurs du formulaire.'

    return
  }

  if (error?.status === 403) {

    formError.value =
      data?.message ||
      'Vous n’avez pas l’autorisation d’effectuer cette action.'

    return
  }

  if (error?.status === 409) {

    formError.value =
      data?.message ||
      'Cette opération ne peut pas être effectuée.'

    return
  }

  formError.value =
    data?.message ||
    error?.message ||
    'Une erreur est survenue.'
}

function firstValidationMessage(value) {

  if (Array.isArray(value)) {
    return value[0] || ''
  }

  return value || ''
}

/* ============================================================
   SAVE ROLE
============================================================ */

async function saveRole() {

  if (saving.value) {
    return
  }

  if (loadingRolePermissions.value) {
    showAlert(
      'Veuillez attendre le chargement des permissions.',
      'error'
    )
    return
  }

  if (!validateRoleForm()) {
    return
  }

  saving.value = true
  formError.value = ''

  try {

    const payload = {
      nom_role:
        roleForm.nom_role.trim(),

      description:
        roleForm.description.trim() || null,

      actif:
        Boolean(roleForm.actif),

      permissions:
        selectedPermissionIds.value.map(
          id => Number(id)
        )
    }

    let response

    if (editingRole.value) {

      response =
        await api.put(
          `/roles/${editingRole.value.id_role}`,
          payload
        )

      showAlert(
        response?.message ||
        'Rôle modifié avec succès.',
        'success'
      )

    } else {

      response =
        await api.post(
          '/roles',
          payload
        )

      showAlert(
        response?.message ||
        'Rôle créé avec succès.',
        'success'
      )
    }

    /*
     * IMPORTANT:
     * On force la fermeture car saving=true ici.
     */
    closeRoleModal(true)

    /*
     * Recharge les données depuis Laravel
     * pour garantir que la modification affichée
     * correspond réellement à la base de données.
     */
    await loadRoles()

  } catch (error) {

    console.error(
      'Erreur sauvegarde rôle:',
      error
    )

    handleValidationError(error)

  } finally {

    saving.value = false
  }
}

/* ============================================================
   GET ROLE PERMISSIONS
============================================================ */

async function getRolePermissionIds(roleId) {

  try {

    const response =
      await api.get(
        `/roles/${roleId}/permissions`
      )

    const permissions =
      response?.data?.permissions ||
      response?.data?.role?.permissions ||
      []

    return permissions
      .map(permission =>
        Number(permission.id_permission)
      )
      .filter(id =>
        Number.isInteger(id)
      )

  } catch (error) {

    console.error(
      'Erreur récupération permissions:',
      error
    )

    return []
  }
}

/* ============================================================
   TOGGLE STATUS
============================================================ */

async function toggleRoleStatus(role) {

  if (!canUpdate.value) {
    return
  }

  if (role.systeme) {
    showAlert(
      'Le rôle système ne peut pas être désactivé.',
      'error'
    )
    return
  }

  if (statusUpdatingId.value) {
    return
  }

  const nextStatus =
    !role.actif

  statusUpdatingId.value =
    role.id_role

  try {

    const permissionIds =
      await getRolePermissionIds(
        role.id_role
      )

    const response =
      await api.put(
        `/roles/${role.id_role}`,
        {
          nom_role:
            role.nom_role,

          description:
            role.description || null,

          actif:
            nextStatus,

          permissions:
            permissionIds
        }
      )

    /*
     * Mise à jour immédiate
     */
    role.actif = nextStatus

    showAlert(
      response?.message ||
      (
        nextStatus
          ? 'Rôle activé avec succès.'
          : 'Rôle désactivé avec succès.'
      ),
      'success'
    )

    await loadRoles()

  } catch (error) {

    console.error(
      'Erreur changement statut:',
      error
    )

    showAlert(
      error?.data?.message ||
      error?.message ||
      'Impossible de modifier le statut du rôle.',
      'error'
    )

  } finally {

    statusUpdatingId.value = null
  }
}

/* ============================================================
   PERMISSION GROUP HELPERS
============================================================ */

function isPermissionGroupFullySelected(group) {

  if (!group.permissions.length) {
    return false
  }

  return group.permissions.every(
    permission =>
      selectedPermissionIds.value.includes(
        permission.id_permission
      )
  )
}

function togglePermissionGroup(group) {

  if (saving.value) {
    return
  }

  const ids =
    group.permissions.map(
      permission =>
        permission.id_permission
    )

  const allSelected =
    ids.every(id =>
      selectedPermissionIds.value.includes(id)
    )

  if (allSelected) {

    selectedPermissionIds.value =
      selectedPermissionIds.value.filter(
        id => !ids.includes(id)
      )

  } else {

    const merged = new Set([
      ...selectedPermissionIds.value,
      ...ids
    ])

    selectedPermissionIds.value =
      Array.from(merged)
  }
}

function isPermissionGroupFullySelectedModal(group) {

  if (!group.permissions.length) {
    return false
  }

  return group.permissions.every(
    permission =>
      permissionModalSelectedIds.value.includes(
        permission.id_permission
      )
  )
}

function togglePermissionGroupModal(group) {

  if (savingPermissions.value) {
    return
  }

  const ids =
    group.permissions.map(
      permission =>
        permission.id_permission
    )

  const allSelected =
    ids.every(id =>
      permissionModalSelectedIds.value.includes(id)
    )

  if (allSelected) {

    permissionModalSelectedIds.value =
      permissionModalSelectedIds.value.filter(
        id => !ids.includes(id)
      )

  } else {

    const merged = new Set([
      ...permissionModalSelectedIds.value,
      ...ids
    ])

    permissionModalSelectedIds.value =
      Array.from(merged)
  }
}

/* ============================================================
   PERMISSION MODAL
============================================================ */

async function openPermissionModal(role) {

  if (!canUpdate.value) {
    showAlert(
      'Vous n’avez pas la permission de modifier les permissions.',
      'error'
    )
    return
  }

  if (role.systeme) {
    showAlert(
      'Les permissions du rôle système ne peuvent pas être modifiées.',
      'error'
    )
    return
  }

  selectedRole.value = role

  permissionModalError.value = ''
  permissionModalSelectedIds.value = []

  showPermissionModal.value = true

  permissionsLoading.value = true

  try {

    const response =
      await api.get(
        `/roles/${role.id_role}/permissions`
      )

    const permissions =
      response?.data?.permissions ||
      response?.data?.role?.permissions ||
      []

    permissionModalSelectedIds.value =
      permissions
        .map(permission =>
          Number(permission.id_permission)
        )
        .filter(id =>
          Number.isInteger(id)
        )

  } catch (error) {

    console.error(
      'Erreur permissions rôle:',
      error
    )

    permissionModalError.value =
      error?.data?.message ||
      error?.message ||
      'Impossible de charger les permissions.'

  } finally {

    permissionsLoading.value = false
  }
}

function closePermissionModal() {

  if (savingPermissions.value) {
    return
  }

  showPermissionModal.value = false
  selectedRole.value = null
  permissionModalError.value = ''
  permissionModalSelectedIds.value = []
}

async function savePermissions() {

  if (
    savingPermissions.value ||
    !selectedRole.value ||
    permissionsLoading.value
  ) {
    return
  }

  if (selectedRole.value.systeme) {
    return
  }

  savingPermissions.value = true
  permissionModalError.value = ''

  try {

    const response =
      await api.put(
        `/roles/${selectedRole.value.id_role}/permissions`,
        {
          permissions:
            permissionModalSelectedIds.value.map(
              id => Number(id)
            )
        }
      )

    showAlert(
      response?.message ||
      'Permissions mises à jour avec succès.',
      'success'
    )

    /*
     * Fermeture avant le refresh.
     */
    showPermissionModal.value = false
    selectedRole.value = null
    permissionModalSelectedIds.value = []

    await loadRoles()

  } catch (error) {

    console.error(
      'Erreur sauvegarde permissions:',
      error
    )

    permissionModalError.value =
      error?.data?.message ||
      error?.message ||
      'Impossible de sauvegarder les permissions.'

  } finally {

    savingPermissions.value = false
  }
}

/* ============================================================
   DETAILS
============================================================ */

async function openDetailsModal(role) {
  if (!role || !role.id_role) {
    showAlert(
      'Impossible d’ouvrir les détails de ce rôle.',
      'error'
    )
    return
  }

  // Initialisation propre du modal
  selectedRole.value = role
  selectedRoleDetails.value = null
  detailsLoading.value = true
  showDetailsModal.value = true

  try {
    const response = await api.get(
      `/roles/${role.id_role}`
    )

    /*
     * Laravel retourne normalement :
     *
     * {
     *   success: true,
     *   message: "...",
     *   data: {...}
     * }
     */

    const details = response?.data

    if (details && typeof details === 'object') {
      selectedRoleDetails.value = details
    } else {
      /*
       * Fallback :
       * si l'API ne retourne pas de détails complets,
       * on garde les informations déjà présentes dans
       * le tableau.
       */
      selectedRoleDetails.value = {
        ...role,
        permissions: []
      }
    }

  } catch (error) {
    console.error(
      'Erreur détails rôle:',
      error
    )

    /*
     * Important :
     * l'erreur ne doit jamais provoquer un écran blanc.
     * On affiche au minimum les données déjà disponibles.
     */
    selectedRoleDetails.value = {
      ...role,
      permissions: []
    }

    if (error?.status === 403) {
      showAlert(
        'Vous n’avez pas la permission de consulter les détails de ce rôle.',
        'error'
      )
    } else if (error?.status === 404) {
      showAlert(
        'Le rôle demandé est introuvable.',
        'error'
      )
    } else {
      showAlert(
        error?.data?.message ||
        error?.message ||
        'Impossible de charger les détails du rôle.',
        'error'
      )
    }

  } finally {
    detailsLoading.value = false
  }
}

function closeDetailsModal() {

  if (detailsLoading.value) {
    return
  }

  showDetailsModal.value = false
  selectedRoleDetails.value = null
  selectedRole.value = null
}

function editFromDetails() {

  if (!selectedRoleDetails.value) {
    return
  }

  const role =
    selectedRoleDetails.value

  closeDetailsModal()

  openEditModal(role)
}

/* ============================================================
   DELETE
============================================================ */

function openDeleteModal(role) {

  if (!canDelete.value) {
    showAlert(
      'Vous n’avez pas la permission de supprimer un rôle.',
      'error'
    )
    return
  }

  if (role.systeme) {
    showAlert(
      'Le rôle système ne peut pas être supprimé.',
      'error'
    )
    return
  }

  selectedRole.value = role
  deleteError.value = ''
  showDeleteModal.value = true
}

/*
 * IMPORTANT:
 * force=true permet de fermer après suppression,
 * même si deleting=true.
 */
function closeDeleteModal(force = false) {

  if (deleting.value && !force) {
    return
  }

  showDeleteModal.value = false
  selectedRole.value = null
  deleteError.value = ''
}

async function deleteRole() {

  if (
    deleting.value ||
    !selectedRole.value
  ) {
    return
  }

  deleting.value = true
  deleteError.value = ''

  const deletedId =
    selectedRole.value.id_role

  try {

    const response =
      await api.delete(
        `/roles/${deletedId}`
      )

    /*
     * Suppression immédiate de la ligne.
     */
    roles.value =
      roles.value.filter(
        role =>
          role.id_role !== deletedId
      )

    /*
     * IMPORTANT:
     * closeDeleteModal() normal ne fonctionne pas
     * ici car deleting=true.
     * On utilise force=true.
     */
    closeDeleteModal(true)

    showAlert(
      response?.message ||
      'Rôle supprimé avec succès.',
      'success'
    )

    /*
     * Correction pagination.
     */
    if (
      currentPage.value >
      totalPages.value
    ) {
      currentPage.value =
        totalPages.value
    }

    /*
     * Synchronisation finale avec le backend.
     */
    await loadRoles()

  } catch (error) {

    console.error(
      'Erreur suppression rôle:',
      error
    )

    if (error?.status === 409) {

      deleteError.value =
        error?.data?.message ||
        'Ce rôle ne peut pas être supprimé car il est encore utilisé.'

    } else if (error?.status === 403) {

      deleteError.value =
        error?.data?.message ||
        'Vous n’avez pas la permission de supprimer ce rôle.'

    } else {

      deleteError.value =
        error?.data?.message ||
        error?.message ||
        'Impossible de supprimer le rôle.'
    }

  } finally {

    deleting.value = false
  }
}

/* ============================================================
   DATE
============================================================ */

function formatDate(value) {

  if (!value) {
    return '—'
  }

  const date =
    new Date(value)

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

/* ============================================================
   WATCHERS
============================================================ */

watch(
  [
    search,
    statusFilter,
    typeFilter,
    perPage
  ],
  () => {
    currentPage.value = 1
  }
)

watch(
  totalPages,
  value => {
    if (
      currentPage.value > value
    ) {
      currentPage.value = value
    }
  }
)

/* ============================================================
   INIT
============================================================ */

onMounted(async () => {

  await Promise.all([
    loadRoles(),
    loadPermissions()
  ])

})
</script>


<style scoped>
/* ============================================================
   PAGE
============================================================ */

.roles-page {
  min-height: 100%;
  padding: 28px;
  background: #f6f8fb;
}

/* ============================================================
   HEADER
============================================================ */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-icon {
  width: 52px;
  height: 52px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  background: #eaf2ff;
  color: #2563eb;
}

.page-header h1 {
  margin: 0;
  font-size: 25px;
  font-weight: 750;
  color: #172033;
}

.page-header p {
  margin: 5px 0 0;
  color: #697386;
  font-size: 14px;
}

/* ============================================================
   ALERT
============================================================ */

.global-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 13px 15px;
  margin-bottom: 20px;
  border-radius: 10px;
  border: 1px solid;
  font-size: 14px;
}

.alert-success {
  background: #ecfdf5;
  color: #047857;
  border-color: #a7f3d0;
}

.alert-error {
  background: #fef2f2;
  color: #b91c1c;
  border-color: #fecaca;
}

.alert-close {
  margin-left: auto;
  border: 0;
  background: transparent;
  cursor: pointer;
  color: inherit;
}

/* ============================================================
   STATS
============================================================ */

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 18px;
  background: #fff;
  border: 1px solid #e7ebf1;
  border-radius: 14px;
  box-shadow: 0 2px 8px rgb(15 23 42 / 3%);
}

.stat-icon {
  width: 43px;
  height: 43px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 11px;
}

.stat-blue {
  background: #eff6ff;
  color: #2563eb;
}

.stat-green {
  background: #ecfdf5;
  color: #059669;
}

.stat-orange {
  background: #fff7ed;
  color: #ea580c;
}

.stat-purple {
  background: #f5f3ff;
  color: #7c3aed;
}

.stat-content {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.stat-label {
  color: #6b7280;
  font-size: 12px;
}

.stat-content strong {
  color: #172033;
  font-size: 22px;
}

/* ============================================================
   BUTTONS
============================================================ */

.btn {
  min-height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 15px;
  border: 1px solid transparent;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 650;
  cursor: pointer;
  transition: .18s ease;
}

.btn:disabled {
  opacity: .55;
  cursor: not-allowed;
  transform: none !important;
}


.btn-primary {
  background: #2563eb;
  color: #fff;
  border: 0;
  border-radius: 12px;
  height: 46px;
  font-size: 15px;
  font-weight: 600;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
  box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
  transform: translateY(-1px);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
}


.btn-secondary {
  background: #fff;
  color: #374151;
  border-color: #d9dee7;
}

.btn-secondary:hover:not(:disabled) {
  background: #f8fafc;
}

.btn-danger {
  background: #dc2626;
  color: #fff;
  border-color: #dc2626;
}

.btn-danger:hover:not(:disabled) {
  background: #b91c1c;
}

/* ============================================================
   FILTERS
============================================================ */

.filters-card {
  padding: 16px;
  margin-bottom: 20px;
  background: #fff;
  border: 1px solid #e7ebf1;
  border-radius: 14px;
}

.filters-top {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-wrapper {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-icon {
  position: absolute;
  left: 13px;
  top: 50%;
  transform: translateY(-50%);
  color: #8a94a6;
  pointer-events: none;
}

.search-input {
  width: 100%;
  height: 42px;
  padding: 0 42px 0 40px;
  border: 1px solid #dfe4ec;
  border-radius: 9px;
  outline: none;
  color: #172033;
  font-size: 13px;
  background: #fff;
  box-sizing: border-box;
}

.search-input:focus {
  border-color: #60a5fa;
  box-shadow: 0 0 0 3px rgb(37 99 235 / 9%);
}

.search-clear {
  position: absolute;
  right: 9px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: 7px;
  background: #f1f5f9;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.filter-actions {
  display: flex;
  align-items: center;
  gap: 9px;
}

.select-wrapper {
  height: 42px;
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 0 10px;
  border: 1px solid #dfe4ec;
  border-radius: 9px;
  background: #fff;
  color: #64748b;
}

.select-wrapper select {
  border: 0;
  outline: none;
  background: transparent;
  color: #374151;
  font-size: 13px;
}

.results-info {
  padding-top: 13px;
  color: #64748b;
  font-size: 12px;
}

/* ============================================================
   TABLE
============================================================ */

.table-card {
  overflow: hidden;
  background: #fff;
  border: 1px solid #e7ebf1;
  border-radius: 14px;
  box-shadow: 0 2px 8px rgb(15 23 42 / 3%);
}

.table-responsive {
  overflow-x: auto;
}

.roles-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 950px;
}

.roles-table th {
  padding: 13px 16px;
  text-align: left;
  background: #f8fafc;
  color: #64748b;
  border-bottom: 1px solid #e7ebf1;
  font-size: 11px;
  font-weight: 750;
  text-transform: uppercase;
  letter-spacing: .04em;
}

.roles-table td {
  padding: 15px 16px;
  border-bottom: 1px solid #edf0f4;
  vertical-align: middle;
  color: #374151;
  font-size: 13px;
}

.roles-table tbody tr:hover {
  background: #fbfdff;
}

.actions-column {
  text-align: right !important;
}

.role-cell {
  display: flex;
  align-items: center;
  gap: 11px;
  min-width: 240px;
}

.role-avatar {
  width: 39px;
  height: 39px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  border-radius: 10px;
}

.role-system {
  background: #eef2ff;
  color: #4f46e5;
}

.role-custom {
  background: #eff6ff;
  color: #2563eb;
}

.role-info {
  min-width: 0;
}

.role-name {
  display: flex;
  align-items: center;
  gap: 7px;
  font-weight: 700;
  color: #172033;
}

.role-description {
  max-width: 290px;
  margin-top: 3px;
  color: #7b8494;
  font-size: 12px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.system-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 7px;
  border-radius: 999px;
  background: #eef2ff;
  color: #4f46e5;
  font-size: 10px;
  font-weight: 700;
}

.type-badge,
.status-button {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  width: fit-content;
  padding: 6px 9px;
  border-radius: 999px;
  border: 1px solid transparent;
  font-size: 11px;
  font-weight: 700;
}

.type-system {
  background: #eef2ff;
  color: #4f46e5;
  border-color: #e0e7ff;
}

.type-custom {
  background: #eff6ff;
  color: #2563eb;
  border-color: #dbeafe;
}

.status-button {
  cursor: pointer;
}

.status-active {
  background: #ecfdf5;
  color: #047857;
  border-color: #a7f3d0;
}

.status-inactive {
  background: #f8fafc;
  color: #64748b;
  border-color: #e2e8f0;
}

button.status-button {
  cursor: pointer;
}

button.status-button:disabled {
  cursor: wait;
  opacity: .65;
}

.count-cell {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  color: #64748b;
}

.count-cell strong {
  color: #1f2937;
}

.permission-count {
  color: #7c3aed;
}

.date-cell {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #6b7280;
  white-space: nowrap;
}

.actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 6px;
}

.icon-btn,
.system-lock {
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.icon-btn {
  border: 1px solid #e2e8f0;
  background: #fff;
  cursor: pointer;
  transition: .18s ease;
}

.icon-btn:hover {
  transform: translateY(-1px);
}

.icon-btn.view {
  color: #2563eb;
}

.icon-btn.edit {
  color: #7c3aed;
}

.icon-btn.permissions {
  color: #0891b2;
}

.icon-btn.delete {
  color: #dc2626;
}

.system-lock {
  color: #94a3b8;
}

/* ============================================================
   LOADING / EMPTY
============================================================ */

.table-loading,
.empty-state {
  min-height: 330px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 11px;
  color: #64748b;
}

.empty-icon {
  width: 62px;
  height: 62px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #f1f5f9;
  color: #94a3b8;
}

.empty-state h3 {
  margin: 4px 0 0;
  color: #334155;
  font-size: 17px;
}

.empty-state p {
  margin: 0 0 5px;
  font-size: 13px;
}

/* ============================================================
   PAGINATION
============================================================ */

.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  padding: 13px 16px;
  border-top: 1px solid #edf0f4;
}

.pagination-info {
  color: #64748b;
  font-size: 12px;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 7px;
}

.per-page {
  height: 34px;
  padding: 0 9px;
  border: 1px solid #dfe4ec;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 12px;
  outline: none;
}

.page-btn {
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dfe4ec;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  cursor: pointer;
}

.page-btn:disabled {
  opacity: .4;
  cursor: not-allowed;
}

.page-number {
  min-width: 65px;
  text-align: center;
  color: #475569;
  font-size: 12px;
  font-weight: 650;
}

/* ============================================================
   MODAL
============================================================ */

.modal-overlay {
  position: fixed;
  z-index: 1000;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 22px;
  background: rgb(15 23 42 / 55%);
  backdrop-filter: blur(3px);
}

.modal {
  width: min(760px, 100%);
  max-height: calc(100vh - 44px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 24px 70px rgb(15 23 42 / 22%);
}

.role-modal {
  width: min(820px, 100%);
}

.permission-modal {
  width: min(850px, 100%);
}

.details-modal {
  width: min(700px, 100%);
}

.delete-modal {
  width: min(450px, 100%);
  padding: 28px;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  background: #08264d;
  padding: 20px 22px;
  border-bottom: 1px solid #edf0f4;
}

.modal-title-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-icon {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 11px;
  background: #eff6ff;
  color: #1202f3;
}

.permission-icon {
  background: #f5f3ff;
  color: #7c3aed;
}

.modal-header h2 {
  margin: 0;
  color: #fafafc;
  font-size: 18px;
}

.modal-header p {
  margin: 4px 0 0;
  color: #ebeef3;
  font-size: 12px;
}

.modal-close {
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 8px;
  background: #f8fafc;
  color: #64748b;
  cursor: pointer;
}

.modal-close:hover:not(:disabled) {
  background: #f1f5f9;
  color: #1f2937;
}

.modal-body {
  overflow-y: auto;
  padding: 22px;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 9px;
  padding: 16px 22px;
  border-top: 1px solid #edf0f4;
  background: #fbfcfe;
}

/* ============================================================
   FORM
============================================================ */

.form-group {
  margin-bottom: 19px;
}

.form-group > label {
  display: block;
  margin-bottom: 7px;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
}

.form-group > label span {
  color: #dc2626;
}

.form-input,
.form-textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #dce2ea;
  border-radius: 9px;
  outline: none;
  background: #fff;
  color: #172033;
  font-size: 13px;
  transition: .18s ease;
}

.form-input {
  height: 42px;
  padding: 0 12px;
}

.form-textarea {
  padding: 11px 12px;
  resize: vertical;
  min-height: 95px;
  font-family: inherit;
}

.form-input:focus,
.form-textarea:focus {
  border-color: #60a5fa;
  box-shadow: 0 0 0 3px rgb(37 99 235 / 8%);
}

.form-input.invalid,
.form-textarea.invalid {
  border-color: #fca5a5;
}

.field-error {
  display: block;
  margin-top: 5px;
  color: #dc2626;
  font-size: 11px;
}

.textarea-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 4px;
  color: #94a3b8;
  font-size: 11px;
}

.form-alert {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  padding: 11px 12px;
  margin-bottom: 17px;
  border-radius: 9px;
  font-size: 12px;
  line-height: 1.5;
}

.form-alert.error {
  color: #b91c1c;
  background: #fef2f2;
  border: 1px solid #fecaca;
}

/* ============================================================
   SWITCH
============================================================ */

.switch-row {
  display: flex !important;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.switch-row input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.switch {
  position: relative;
  width: 42px;
  height: 23px;
  flex: 0 0 auto;
  border-radius: 999px;
  background: #cbd5e1;
  transition: .2s;
}

.switch span {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 17px;
  height: 17px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 3px rgb(15 23 42 / 25%);
  transition: .2s;
}

.switch-row input:checked + .switch {
  background: #2563eb;
}

.switch-row input:checked + .switch span {
  transform: translateX(19px);
}

.switch-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.switch-text strong {
  color: #334155;
  font-size: 12px;
}

.switch-text small {
  color: #94a3b8;
  font-size: 11px;
}

/* ============================================================
   PERMISSIONS
============================================================ */

.permission-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 15px;
  margin-bottom: 9px;
}

.permission-header label {
  display: block;
  margin-bottom: 3px;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
}

.permission-header small {
  color: #94a3b8;
  font-size: 11px;
}

.permission-total {
  padding: 5px 9px;
  border-radius: 999px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 11px;
  font-weight: 700;
}

.permissions-loading,
.permissions-empty {
  min-height: 130px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  color: #64748b;
  border: 1px dashed #dbe2ea;
  border-radius: 10px;
}

.permissions-empty {
  flex-direction: column;
}

.permissions-groups {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.permission-group {
  overflow: hidden;
  border: 1px solid #e5e9f0;
  border-radius: 10px;
}

.permission-group-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 12px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e9f0;
}

.permission-page {
  display: flex;
  align-items: center;
  gap: 7px;
  color: #334155;
  font-size: 12px;
}

.select-group-btn {
  border: 0;
  background: transparent;
  color: #2563eb;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.permission-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.permission-item {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 9px;
  padding: 11px 12px;
  border-bottom: 1px solid #f0f2f5;
  cursor: pointer;
  transition: .15s;
}

.permission-item:nth-last-child(-n + 2) {
  border-bottom: 0;
}

.permission-item:hover {
  background: #f8fbff;
}

.permission-item input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.permission-check {
  width: 18px;
  height: 18px;
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 1px;
  border: 1px solid #cbd5e1;
  border-radius: 5px;
  color: transparent;
  background: #fff;
}

.permission-item.selected .permission-check {
  border-color: #2563eb;
  background: #2563eb;
  color: #fff;
}

.permission-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}

.permission-info strong {
  color: #334155;
  font-size: 11px;
  word-break: break-word;
}

.permission-info small {
  color: #94a3b8;
  font-size: 10px;
}

/* ============================================================
   DETAILS
============================================================ */

.details-loading {
  min-height: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: #64748b;
}

.details-hero {
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 16px;
  margin-bottom: 16px;
  border-radius: 12px;
  background: #f8fafc;
}

.details-avatar {
  width: 52px;
  height: 52px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 13px;
  background: #eff6ff;
  color: #2563eb;
}

.details-hero h3 {
  margin: 0 0 7px;
  color: #172033;
  font-size: 18px;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-bottom: 18px;
}

.detail-box {
  padding: 13px;
  border: 1px solid #e8ecf1;
  border-radius: 10px;
  background: #fff;
}

.detail-box span {
  display: block;
  margin-bottom: 4px;
  color: #94a3b8;
  font-size: 10px;
  text-transform: uppercase;
}

.detail-box strong {
  color: #334155;
  font-size: 13px;
}

.text-success {
  color: #059669 !important;
}

.text-danger {
  color: #dc2626 !important;
}

.details-section-title {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 9px;
  color: #334155;
  font-size: 13px;
  font-weight: 750;
}

.details-description {
  padding-bottom: 17px;
  margin-bottom: 17px;
  border-bottom: 1px solid #edf0f4;
}

.details-description p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  line-height: 1.6;
}

.no-permissions {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px;
  border-radius: 9px;
  background: #f8fafc;
  color: #94a3b8;
  font-size: 12px;
}

.permission-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
}

.permission-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 8px;
  border-radius: 7px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 10px;
  font-weight: 650;
}

/* ============================================================
   DELETE
============================================================ */

.danger-overlay {
  background: rgb(127 29 29 / 20%);
}

.delete-modal {
  text-align: center;
}

.delete-icon {
  width: 58px;
  height: 58px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 15px;
  border-radius: 50%;
  background: #fef2f2;
  color: #dc2626;
}

.delete-content h2 {
  margin: 0 0 9px;
  color: #172033;
  font-size: 19px;
}

.delete-content p {
  margin: 0 auto 7px;
  max-width: 350px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.55;
}

.delete-warning {
  color: #dc2626 !important;
  font-size: 12px !important;
}

.delete-modal .form-alert {
  text-align: left;
  margin-top: 17px;
}

.delete-footer {
  margin-top: 22px;
  padding: 0;
  border: 0;
  background: transparent;
}

/* ============================================================
   TRANSITIONS
============================================================ */

.modal-enter-active,
.modal-leave-active {
  transition: opacity .2s ease;
}

.modal-enter-active .modal,
.modal-leave-active .modal {
  transition:
    transform .2s ease,
    opacity .2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal,
.modal-leave-to .modal {
  transform: translateY(10px) scale(.98);
  opacity: 0;
}

.alert-enter-active,
.alert-leave-active {
  transition: .2s ease;
}

.alert-enter-from,
.alert-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

/* ============================================================
   SPINNER
============================================================ */

.spin {
  animation: spin 1s linear infinite;
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

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .filters-top {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-actions {
    flex-wrap: wrap;
  }

}

@media (max-width: 760px) {

  .roles-page {
    padding: 16px;
  }

  .page-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .page-header .btn {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .filter-actions {
    display: grid;
    grid-template-columns: 1fr;
  }

  .select-wrapper,
  .filter-actions .btn {
    width: 100%;
  }

  .pagination {
    flex-direction: column;
    align-items: stretch;
  }

  .pagination-controls {
    justify-content: space-between;
  }

  .permission-list {
    grid-template-columns: 1fr;
  }

  .permission-item:nth-last-child(-n + 2) {
    border-bottom: 1px solid #f0f2f5;
  }

  .permission-item:last-child {
    border-bottom: 0;
  }

  .details-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .modal-overlay {
    padding: 10px;
  }

  .modal {
    max-height: calc(100vh - 20px);
  }

  .modal-header,
  .modal-body {
    padding: 16px;
  }

  .modal-footer {
    padding: 13px 16px;
  }

}

@media (max-width: 480px) {

  .header-left {
    align-items: flex-start;
  }

  .header-icon {
    width: 44px;
    height: 44px;
  }

  .page-header h1 {
    font-size: 21px;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }

  .modal-footer {
    flex-direction: column-reverse;
  }

  .modal-footer .btn {
    width: 100%;
  }

  .delete-footer {
    flex-direction: column-reverse;
  }

}
</style>