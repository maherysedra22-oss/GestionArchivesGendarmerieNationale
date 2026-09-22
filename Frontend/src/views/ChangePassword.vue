<template>

  <div class="change-password-page">

    <!-- =========================================================
         BOUTON FERMER
    ========================================================== -->

    <button
      type="button"
      class="close-button"
      aria-label="Fermer"
      title="Fermer"
      @click="closeChangePassword"
    >
      <X :size="20" />
    </button>

    <!-- =========================================================
         BACKDROP / PAGE BLOQUÉE
    ========================================================== -->

    <div class="page-backdrop"></div>

    <!-- =========================================================
         CHANGE PASSWORD CARD
    ========================================================== -->

    <main class="change-password-card">

      <!-- Icon -->

      <div class="security-icon">

        <div class="security-icon-inner">

          <ShieldCheck
            :size="27"
            stroke-width="2"
          />

        </div>

      </div>

      <!-- Header -->

      <header class="card-header">

        <span class="eyebrow">

          <LockKeyhole :size="13" />

          Sécurité du compte

        </span>

        <h1>Changer le mot de passe</h1>

        <p>
          Pour continuer, veuillez définir un nouveau mot de passe
          sécurisé pour votre compte.
        </p>

      </header>

      <!-- Form -->

      <form
        class="password-form"
        @submit.prevent="changePassword"
      >

        <!-- Nouveau mot de passe -->

        <div class="form-group">

          <label for="newPassword">
            Nouveau mot de passe
          </label>

          <div
            class="input-wrapper"
            :class="{
              'input-error':
                errorMessage && form.newPassword,
              'input-disabled': loading
            }"
          >

            <LockKeyhole
              class="input-icon"
              :size="18"
              stroke-width="1.9"
            />

            <input
              ref="newPasswordInput"
              id="newPassword"
              v-model="form.newPassword"
              :type="
                showNewPassword
                  ? 'text'
                  : 'password'
              "
              autocomplete="new-password"
              minlength="8"
              maxlength="255"
              placeholder="Saisissez votre nouveau mot de passe"
              required
              :disabled="loading"
            />

            <button
              type="button"
              class="password-toggle"
              :aria-label="
                showNewPassword
                  ? 'Masquer le mot de passe'
                  : 'Afficher le mot de passe'
              "
              :disabled="loading"
              @click="
                showNewPassword =
                  !showNewPassword
              "
            >

              <EyeOff
                v-if="showNewPassword"
                :size="18"
              />

              <Eye
                v-else
                :size="18"
              />

            </button>

          </div>

          <span class="field-hint">

            <ShieldCheck :size="13" />

            Minimum 8 caractères

          </span>

        </div>

        <!-- Confirmation -->

        <div class="form-group">

          <label for="confirmPassword">
            Confirmer le nouveau mot de passe
          </label>

          <div
            class="input-wrapper"
            :class="{
              'input-success':
                form.confirmPassword &&
                form.newPassword ===
                  form.confirmPassword,

              'input-error':
                form.confirmPassword &&
                form.newPassword !==
                  form.confirmPassword,

              'input-disabled': loading
            }"
          >

            <LockKeyhole
              class="input-icon"
              :size="18"
              stroke-width="1.9"
            />

            <input
              id="confirmPassword"
              v-model="form.confirmPassword"
              :type="
                showConfirmPassword
                  ? 'text'
                  : 'password'
              "
              autocomplete="new-password"
              minlength="8"
              maxlength="255"
              placeholder="Confirmez votre nouveau mot de passe"
              required
              :disabled="loading"
            />

            <button
              type="button"
              class="password-toggle"
              :aria-label="
                showConfirmPassword
                  ? 'Masquer le mot de passe'
                  : 'Afficher le mot de passe'
              "
              :disabled="loading"
              @click="
                showConfirmPassword =
                  !showConfirmPassword
              "
            >

              <EyeOff
                v-if="showConfirmPassword"
                :size="18"
              />

              <Eye
                v-else
                :size="18"
              />

            </button>

          </div>

          <span
            v-if="
              form.confirmPassword &&
              form.newPassword ===
                form.confirmPassword
            "
            class="field-hint success-hint"
          >

            <CheckCircle2 :size="13" />

            Les mots de passe correspondent

          </span>

          <span
            v-else-if="
              form.confirmPassword &&
              form.newPassword !==
                form.confirmPassword
            "
            class="field-hint error-hint"
          >

            <AlertCircle :size="13" />

            Les mots de passe ne correspondent pas

          </span>

        </div>

        <!-- Error -->

        <div
          v-if="errorMessage"
          class="message-box error-box"
          role="alert"
        >

          <div class="message-icon">

            <AlertCircle :size="18" />

          </div>

          <div class="message-content">

            <strong>
              Modification impossible
            </strong>

            <span>
              {{ errorMessage }}
            </span>

          </div>

        </div>

        <!-- Success -->

        <div
          v-if="successMessage"
          class="message-box success-box"
          role="status"
        >

          <div class="message-icon">

            <CheckCircle2 :size="18" />

          </div>

          <div class="message-content">

            <strong>
              Modification réussie
            </strong>

            <span>
              {{ successMessage }}
            </span>

          </div>

        </div>

        <!-- Submit -->

        <button
          type="submit"
          class="submit-button"
          :disabled="loading"
        >

          <LoaderCircle
            v-if="loading"
            class="loading-icon"
            :size="18"
          />

          <LockKeyhole
            v-else
            :size="18"
            stroke-width="2"
          />

          <span>

            {{
              loading
                ? 'Enregistrement...'
                : 'Enregistrer le nouveau mot de passe'
            }}

          </span>

        </button>

      </form>

      <!-- Footer -->

      <footer class="card-footer">

        <ShieldCheck :size="14" />

        <span>
          Votre mot de passe est protégé et stocké de manière sécurisée.
        </span>

      </footer>

    </main>

  </div>

</template>

<script setup>

import {
  reactive,
  ref,
  nextTick,
  onMounted
} from 'vue'

import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

import {
  ShieldCheck,
  LockKeyhole,
  Eye,
  EyeOff,
  CheckCircle2,
  AlertCircle,
  LoaderCircle,
  X
} from 'lucide-vue-next'

import api from '@/api/api'

const router = useRouter()

const authStore = useAuthStore()

const newPasswordInput = ref(null)

const form = reactive({
  newPassword: '',
  confirmPassword: ''
})

const loading = ref(false)

const errorMessage = ref('')

const successMessage = ref('')

const showNewPassword = ref(false)

const showConfirmPassword = ref(false)


// ============================================================
// FERMER LA FENÊTRE
// ============================================================

const closeChangePassword = () => {

  if (loading.value) {
    return
  }

  /*
   * Le changement de mot de passe est obligatoire.
   *
   * Si l'utilisateur ne souhaite pas le faire,
   * on le déconnecte afin qu'il ne puisse pas
   * accéder au Dashboard avec un mot de passe
   * temporaire / imposé.
   */

  authStore.logout()

  router.replace('/login')
}


// ============================================================
// CHANGER LE MOT DE PASSE
// ============================================================

const changePassword = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (loading.value) {
    return
  }

  if (!form.newPassword) {
    errorMessage.value =
      'Le nouveau mot de passe est obligatoire.'
    return
  }

  if (form.newPassword.length < 8) {
    errorMessage.value =
      'Le mot de passe doit contenir au moins 8 caractères.'
    return
  }

  if (form.newPassword.length > 255) {
    errorMessage.value =
      'Le mot de passe ne doit pas dépasser 255 caractères.'
    return
  }

  if (!form.confirmPassword) {
    errorMessage.value =
      'La confirmation du mot de passe est obligatoire.'
    return
  }

  if (form.newPassword !== form.confirmPassword) {
    errorMessage.value =
      'Les mots de passe ne correspondent pas.'
    return
  }

  loading.value = true

  try {
    const response = await api.patch(
      '/utilisateurs/change-password',
      {
        mot_de_passe: form.newPassword,
        mot_de_passe_confirmation: form.confirmPassword
      }
    )

    authStore.updateUtilisateur({
      doit_changer_mdp: false
    })

    successMessage.value =
      response?.message ||
      'Mot de passe modifié avec succès.'

    form.newPassword = ''
    form.confirmPassword = ''

    setTimeout(() => {
      router.replace('/dashboard')
    }, 900)

  } catch (error) {
    console.error(
      'Erreur changement mot de passe :',
      error
    )

    errorMessage.value =
      error?.message ||
      'Une erreur est survenue lors du changement du mot de passe.'
  } finally {
    loading.value = false
  }
}

// ============================================================
// FOCUS AUTOMATIQUE
// ============================================================

onMounted(async () => {

  await nextTick()

  newPasswordInput.value?.focus()

})

</script>

<style scoped>

/* ============================================================
   PAGE
============================================================ */

.change-password-page {

  position: fixed;

  inset: 0;

  z-index: 9999;

  display: flex;

  align-items: center;

  justify-content: center;

  padding: 24px;

  overflow: hidden;

  background-color: rgba(0, 0, 0, 0.5);

}


/* ============================================================
   BACKDROP
============================================================ */

.page-backdrop {

  position: absolute;

  inset: 0;

  z-index: 0;

  background: rgba(0, 0, 0, 0.5);

  backdrop-filter: blur(12px);

  -webkit-backdrop-filter: blur(12px);

  pointer-events: auto;

}


/* ============================================================
   CLOSE BUTTON
============================================================ */

.close-button {

  position: absolute;

  top: 22px;

  right: 24px;

  z-index: 10;

  width: 40px;

  height: 40px;

  display: flex;

  align-items: center;

  justify-content: center;

  color: #ffffff;

  background: rgba(255, 255, 255, 0.12);

  border: 1px solid rgba(255, 255, 255, 0.25);

  border-radius: 10px;

  cursor: pointer;

  backdrop-filter: blur(8px);

  -webkit-backdrop-filter: blur(8px);

  transition:
    background 0.2s ease,
    transform 0.15s ease,
    border-color 0.2s ease;

}

.close-button:hover {

  background: rgba(255, 255, 255, 0.22);

  border-color: rgba(255, 255, 255, 0.4);

  transform: translateY(-1px);

}

.close-button:active {

  transform: translateY(0);

}

.close-button:disabled {

  opacity: 0.5;

  cursor: not-allowed;

}


/* ============================================================
   CARD
============================================================ */

.change-password-card {

  position: relative;

  z-index: 2;

  width: 100%;

  max-width: 430px;

  padding: 30px 32px 22px;

  background: rgba(255, 255, 255, 0.97);

  border: 1px solid rgba(255, 255, 255, 0.9);

  border-radius: 18px;

  box-shadow:
    0 24px 70px rgba(15, 23, 42, 0.16),
    0 6px 20px rgba(15, 23, 42, 0.08);

  box-sizing: border-box;

  pointer-events: auto;

  animation: cardAppear 0.35s ease-out;

}

@keyframes cardAppear {

  from {

    opacity: 0;

    transform: translateY(10px) scale(0.98);

  }

  to {

    opacity: 1;

    transform: translateY(0) scale(1);

  }

}


/* ============================================================
   SECURITY ICON
============================================================ */

.security-icon {

  display: flex;

  justify-content: center;

  margin-bottom: 16px;

}

.security-icon-inner {

  width: 54px;

  height: 54px;

  display: flex;

  align-items: center;

  justify-content: center;

  color: #1f4e79;

  background: #edf4fb;

  border: 1px solid #d7e5f2;

  border-radius: 15px;

  box-shadow:
    0 6px 18px rgba(31, 78, 121, 0.09);

}


/* ============================================================
   HEADER
============================================================ */

.card-header {

  text-align: center;

  margin-bottom: 24px;

}

.eyebrow {

  display: inline-flex;

  align-items: center;

  gap: 6px;

  margin-bottom: 9px;

  color: #1f4e79;

  font-size: 11px;

  font-weight: 700;

  letter-spacing: 0.08em;

  text-transform: uppercase;

}

.card-header h1 {

  margin: 0;

  color: #172033;

  font-size: 22px;

  line-height: 1.3;

  font-weight: 700;

}

.card-header p {

  max-width: 350px;

  margin: 8px auto 0;

  color: #6b7280;

  font-size: 13px;

  line-height: 1.6;

}


/* ============================================================
   FORM
============================================================ */

.password-form {

  display: flex;

  flex-direction: column;

  gap: 0;

}

.form-group {

  margin-bottom: 17px;

}

.form-group label {

  display: block;

  margin-bottom: 7px;

  color: #263246;

  font-size: 13px;

  font-weight: 600;

}


/* ============================================================
   INPUT
============================================================ */

.input-wrapper {

  position: relative;

  display: flex;

  align-items: center;

  height: 46px;

  background: #ffffff;

  border: 1px solid #d7dde6;

  border-radius: 10px;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease;

}

.input-wrapper:focus-within {

  border-color: #1f4e79;

  box-shadow:
    0 0 0 3px rgba(31, 78, 121, 0.10);

}

.input-wrapper.input-success {

  border-color: #35a36f;

}

.input-wrapper.input-error {

  border-color: #dc5a5a;

}

.input-wrapper.input-disabled {

  background: #f5f6f8;

}


/* ============================================================
   INPUT ICON
============================================================ */

.input-icon {

  flex-shrink: 0;

  margin-left: 13px;

  color: #7b8798;

}

.input-wrapper:focus-within .input-icon {

  color: #1f4e79;

}

.input-wrapper input {

  flex: 1;

  width: 100%;

  height: 100%;

  min-width: 0;

  padding: 0 10px;

  color: #172033;

  background: transparent;

  border: 0;

  outline: 0;

  font-size: 13px;

}

.input-wrapper input::placeholder {

  color: #a1a9b5;

}

.input-wrapper input:disabled {

  cursor: not-allowed;

}


/* ============================================================
   PASSWORD TOGGLE
============================================================ */

.password-toggle {

  width: 38px;

  height: 38px;

  flex-shrink: 0;

  display: flex;

  align-items: center;

  justify-content: center;

  margin-right: 4px;

  color: #7b8798;

  background: transparent;

  border: 0;

  border-radius: 8px;

  cursor: pointer;

  transition:
    color 0.2s ease,
    background 0.2s ease;

}

.password-toggle:hover:not(:disabled) {

  color: #1f4e79;

  background: #edf4fb;

}

.password-toggle:disabled {

  cursor: not-allowed;

  opacity: 0.5;

}


/* ============================================================
   FIELD HINT
============================================================ */

.field-hint {

  display: flex;

  align-items: center;

  gap: 5px;

  margin-top: 6px;

  color: #8a94a3;

  font-size: 11px;

}

.success-hint {

  color: #21865a;

}

.error-hint {

  color: #c24141;

}


/* ============================================================
   MESSAGES
============================================================ */

.message-box {

  display: flex;

  align-items: flex-start;

  gap: 10px;

  padding: 11px 12px;

  margin-bottom: 16px;

  border-radius: 10px;

  font-size: 12px;

  line-height: 1.45;

}

.message-icon {

  flex-shrink: 0;

  display: flex;

  align-items: center;

  justify-content: center;

}

.message-content {

  display: flex;

  flex-direction: column;

  gap: 2px;

}

.message-content strong {

  font-size: 12px;

}

.message-content span {

  font-size: 11px;

}

.error-box {

  color: #9f3030;

  background: #fff3f3;

  border: 1px solid #f4cccc;

}

.success-box {

  color: #176b48;

  background: #effaf4;

  border: 1px solid #c9ecd9;

}


/* ============================================================
   SUBMIT BUTTON
============================================================ */

.submit-button {

  width: 100%;

  height: 46px;

  display: flex;

  align-items: center;

  justify-content: center;

  gap: 8px;

  margin-top: 2px;

  color: #ffffff;

  background: #2563EB;

  border: 0;

  border-radius: 12px;

  font-size: 15px;

  font-weight: 600;

  cursor: pointer;

  box-shadow:
    0 4px 10px rgba(37, 99, 235, 0.2);

  transition:
    background 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;

}

.submit-button:hover:not(:disabled) {

  background: #1D4ED8;

  box-shadow:
    0 6px 14px rgba(37, 99, 235, 0.25);

  transform: translateY(-1px);

}

.submit-button:active:not(:disabled) {

  transform: translateY(0);

}

.submit-button:disabled {

  opacity: 0.6;

  cursor: not-allowed;

  transform: none;

}


/* ============================================================
   LOADING
============================================================ */

.loading-icon {

  animation: spin 0.9s linear infinite;

}

@keyframes spin {

  to {

    transform: rotate(360deg);

  }

}


/* ============================================================
   FOOTER
============================================================ */

.card-footer {

  display: flex;

  align-items: center;

  justify-content: center;

  gap: 6px;

  margin-top: 18px;

  padding-top: 15px;

  color: #98a1af;

  border-top: 1px solid #edf0f3;

  font-size: 10px;

  line-height: 1.4;

  text-align: center;

}

.card-footer svg {

  flex-shrink: 0;

  color: #6f8eaa;

}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 520px) {

  .change-password-page {

    padding: 16px;

  }

  .close-button {

    top: 14px;

    right: 14px;

    width: 36px;

    height: 36px;

  }

  .change-password-card {

    max-width: 100%;

    padding: 25px 21px 19px;

    border-radius: 16px;

  }

  .security-icon-inner {

    width: 50px;

    height: 50px;

  }

  .card-header h1 {

    font-size: 20px;

  }

  .card-header p {

    font-size: 12px;

  }

}


/* ============================================================
   SMALL HEIGHT
============================================================ */

@media (max-height: 650px) {

  .change-password-page {

    align-items: flex-start;

    overflow-y: auto;

    padding-top: 20px;

    padding-bottom: 20px;

  }

  .change-password-card {

    margin: auto 0;

  }

}


/* ============================================================
   HIDE NATIVE PASSWORD ICONS
============================================================ */

input[type="password"]::-ms-reveal,

input[type="password"]::-ms-clear {

  display: none;

}

input[type="password"]::-webkit-credentials-auto-fill-button {

  visibility: hidden;

  pointer-events: none;

  position: absolute;

  right: 0;

}


/* ============================================================
   BACKGROUND BLUR + DARK OVERLAY
============================================================ */

.page-backdrop {

  position: absolute;

  inset: 0;

  z-index: 0;

  background: rgba(0, 0, 0, 0.5);

  backdrop-filter: blur(12px);

  -webkit-backdrop-filter: blur(12px);

  pointer-events: auto;

}

</style>