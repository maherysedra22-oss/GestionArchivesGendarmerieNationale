<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const emailInput = ref(null)
const username = ref('')
const password = ref('')
const remember = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const showPassword = ref(false)
const capsLockOn = ref(false)

const currentYear = new Date().getFullYear()

const canSubmit = computed(() => {
  return username.value.trim().length > 0 && password.value.length > 0 && !loading.value
})

const detectCapsLock = (event) => {
  if (typeof event.getModifierState === 'function') {
    capsLockOn.value = event.getModifierState('CapsLock')
  }
}

const handleLogin = async () => {
  errorMessage.value = ''

  // Vérification
  if (!username.value.trim() || !password.value) {
    errorMessage.value =
      'Veuillez renseigner votre adresse e-mail et votre mot de passe.'

    await nextTick()
    emailInput.value?.focus()

    return
  }

  if (loading.value) return

  // Récupérer les valeurs avant de vider les champs
  const email = username.value.trim()
  const motDePasse = password.value

  // Vider immédiatement les champs
  username.value = ''
  password.value = ''

  // Remettre le focus sur Email
  await nextTick()
  emailInput.value?.focus()

  // Démarrer le chargement
  loading.value = true

  try {
    // Appel du Store Pinia
    const data = await auth.login(
      email,
      motDePasse,
      remember.value
    )

    // Vérifier si l'utilisateur doit changer son mot de passe
    if (data.utilisateur?.doit_changer_mdp) {
      router.push('/change-password')
    } else {
      router.push('/dashboard')
    }

  } catch (error) {
    console.error('Erreur de connexion:', error)

    errorMessage.value =
      error.message ||
      'Impossible de contacter le serveur.'

    // Champs restent vides
    username.value = ''
    password.value = ''

    // Retour focus Email
    await nextTick()
    emailInput.value?.focus()

  } finally {
    loading.value = false
  }
}


onMounted(() => {
  nextTick(() => emailInput.value?.focus())
})
</script>

<template>
  <div class="login-page">
    <!-- =====================================================
         PANNEAU INSTITUTIONNEL
    ====================================================== -->
    <section class="institution-panel" aria-hidden="true">
      <div class="institution-content">
        <div class="institution-emblem">GN</div>

        <div class="institution-name">
          <span>République de Madagascar</span>
          <h1>Gendarmerie<br />Nationale</h1>
          <div class="gold-line"></div>
          <p class="institution-service">Service des archives</p>
        </div>

        <div class="institution-description">
          <span class="eyebrow">Plateforme administrative</span>
          <h2>Gestion des archives administratives</h2>
          <p>
            Une plateforme sécurisée destinée à la gestion, la conservation et
            la consultation des courriers et documents administratifs.
          </p>
        </div>

        <ul class="institution-features">
          <li class="feature">
            <span class="feature-icon">✓</span>
            <div>
              <strong>Gestion centralisée</strong>
              <small>Courriers et documents</small>
            </div>
          </li>

          <li class="feature">
            <span class="feature-icon">✓</span>
            <div>
              <strong>Accès sécurisé</strong>
              <small>Authentification des utilisateurs</small>
            </div>
          </li>

          <li class="feature">
            <span class="feature-icon">✓</span>
            <div>
              <strong>Traçabilité</strong>
              <small>Journalisation des activités</small>
            </div>
          </li>
        </ul>

        <div class="institution-footer">Système interne de gestion des archives</div>
      </div>
    </section>

    <!-- =====================================================
         SECTION LOGIN
    ====================================================== -->
    <main class="login-section">
      <div class="login-container">
        <!-- Mobile logo -->
        <div class="mobile-brand">
          <div class="mobile-logo">GN</div>
          <div>
            <strong>Gendarmerie Nationale</strong>
            <span>Gestion des archives</span>
          </div>
        </div>

        <!-- Card -->
        <div class="login-card">
          <div class="login-header">
            <span class="login-eyebrow">Espace personnel</span>
            <h2>Bienvenue</h2>
            <p>Connectez-vous pour accéder à votre espace de gestion.</p>
          </div>

          <!-- Error -->
          <transition name="alert">
            <div v-if="errorMessage" class="alert-error" role="alert" aria-live="assertive">
              <span class="alert-icon" aria-hidden="true">!</span>
              <div>
                <strong>Connexion impossible</strong>
                <p>{{ errorMessage }}</p>
              </div>
            </div>
          </transition>

          <!-- Form -->
          <form @submit.prevent="handleLogin" novalidate>
            <!-- Email -->
            <div class="form-group">
              <label for="email">Adresse e-mail</label>

              <div class="input-wrapper">
                <span class="input-icon" aria-hidden="true">@</span>

                <input
                  id="email"
                  ref="emailInput"
                  v-model="username"
                  type="email"
                  placeholder="nom@organisation.mg"
                  autocomplete="off"
                  inputmode="email"
                  :disabled="loading"
                  :aria-invalid="!!errorMessage"
                  required
                />
              </div>
            </div>

            <!-- Password -->
            <div class="form-group">
              <div class="label-row">
                <label for="password">Mot de passe</label>
                <a href="#" class="forgot-link" tabindex="0">Mot de passe oublié ?</a>
              </div>

              <div class="input-wrapper">
                <span class="input-icon" aria-hidden="true">🔒</span>

                <input
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Entrez votre mot de passe"
                  autocomplete="off"
                  :disabled="loading"
                  :aria-invalid="!!errorMessage"
                  aria-describedby="capslock-hint"
                  required
                  @keyup="detectCapsLock"
                  @keydown="detectCapsLock"
                />

                <button
                  type="button"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                  :aria-pressed="showPassword"
                >
                  <svg v-if="showPassword" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <path fill="currentColor" d="M12 6c-5 0-9.27 3.11-11 7.5 1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 9.11 17 6 12 6zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <path fill="currentColor" d="M3.28 2.22 2.22 3.28l3.1 3.1C3.53 7.86 1.98 9.94 1 12.5c1.73 4.39 6 7.5 11 7.5 1.98 0 3.83-.49 5.44-1.34l3.28 3.28 1.06-1.06L3.28 2.22zM12 17a5 5 0 0 1-5-5c0-.73.16-1.42.44-2.04l1.55 1.55A3 3 0 0 0 12 15c.19 0 .38-.02.56-.05l1.55 1.55A4.94 4.94 0 0 1 12 17zm0-10c5 0 9.27 3.11 11 7.5a12.9 12.9 0 0 1-3.06 4.28l-1.43-1.43A10.9 10.9 0 0 0 21 12.5C19.4 8.86 15.94 6.5 12 6.5c-1.1 0-2.16.19-3.14.54L7.4 5.6A12.5 12.5 0 0 1 12 4.5z"/>
                  </svg>
                </button>
              </div>

              <p v-if="capsLockOn" id="capslock-hint" class="capslock-hint">
                Verr. Maj activé
              </p>
            </div>

            <!-- Remember me -->
            <label class="remember-row">
              <input v-model="remember" type="checkbox" :disabled="loading" />
              <span>Rester connecté sur cet appareil</span>
            </label>

            <!-- Submit -->
            <button type="submit" class="login-button" :disabled="!canSubmit">
              <span v-if="loading" class="spinner" aria-hidden="true"></span>
              <span>{{ loading ? 'Connexion en cours...' : 'Se connecter' }}</span>
              <span v-if="!loading" class="button-arrow" aria-hidden="true">→</span>
            </button>
          </form>

          <div class="security-info">
            <span class="security-icon" aria-hidden="true">✓</span>
            <p>Accès réservé aux utilisateurs autorisés du système.</p>
          </div>
        </div>

        <footer class="login-footer">
          <span>© {{ currentYear }} Gendarmerie Nationale</span>
          <span class="footer-separator" aria-hidden="true">•</span>
          <span>Gestion des archives</span>
        </footer>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* =========================================================
   RESET / TOKENS
========================================================= */

* {
  box-sizing: border-box;
}

.login-page {
  --navy-950: #061f3e;
  --navy-900: #08264d;
  --navy-800: #0b2d5c;
  --navy-700: #123f70;
  --navy-600: #1b4d80;
  --gold: #d5b45c;
  --ink: #243b53;
  --ink-soft: #334e68;
  --slate: #829ab1;
  --slate-light: #b9c9da;
  --border: #d9e2ec;
  --danger: #b42318;
  --danger-bg: #fff7f7;
  --danger-border: #f2cccc;
  --success-bg: #edf9f1;
  --success-ink: #26733f;
  --radius-lg: 14px;
  --radius-md: 8px;

  min-height: 100dvh;
  display: flex;
  background: #f5f7fa;
  color: var(--ink);
  font-family: Inter, "Segoe UI", Arial, sans-serif;
}

@media (prefers-reduced-motion: reduce) {
  .login-page * {
    animation-duration: 0.001ms !important;
    transition-duration: 0.001ms !important;
  }
}

/* =========================================================
   INSTITUTION PANEL
========================================================= */

.institution-panel {
  position: relative;
  width: 44%;
  min-width: 380px;
  min-height: 100dvh;
  display: flex;
  align-items: center;
  padding: clamp(32px, 4vw, 60px);
  overflow: hidden;
  background: linear-gradient(135deg, var(--navy-950) 0%, var(--navy-800) 55%, var(--navy-700) 100%);
  color: white;
}

.institution-panel::before {
  content: "";
  position: absolute;
  width: 500px;
  height: 500px;
  right: -250px;
  top: -220px;
  border-radius: 50%;
  border: 1px solid rgba(213, 180, 92, 0.15);
}

.institution-panel::after {
  content: "";
  position: absolute;
  width: 350px;
  height: 350px;
  left: -200px;
  bottom: -180px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.institution-content {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 600px;
  margin: auto;
  animation: fade-in-up 0.6s ease both;
}

.institution-emblem {
  width: 70px;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 24px;
  border-radius: 50%;
  background: var(--gold);
  color: var(--navy-900);
  border: 4px solid rgba(255, 255, 255, 0.9);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
  font-size: 20px;
  font-weight: 900;
}

.institution-name > span {
  color: #aebfd0;
  font-size: 12px;
  letter-spacing: 1px;
  font-weight: 600;
}

.institution-name h1 {
  margin: 10px 0 14px;
  color: white;
  font-size: clamp(24px, 2.4vw, 28px);
  line-height: 1.15;
  font-weight: 700;
}

.gold-line {
  width: 55px;
  height: 3px;
  background: var(--gold);
  margin-bottom: 12px;
}

.institution-service {
  margin: 0;
  color: var(--gold);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
}

.institution-description {
  margin-top: clamp(40px, 6vw, 75px);
}

.eyebrow {
  color: var(--gold);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
}

.institution-description h2 {
  max-width: 480px;
  margin: 14px 0;
  color: white;
  font-size: clamp(26px, 2.8vw, 34px);
  line-height: 1.2;
  font-weight: 700;
}

.institution-description p {
  max-width: 460px;
  margin: 0;
  color: var(--slate-light);
  font-size: 14px;
  line-height: 1.75;
}

.institution-features {
  display: flex;
  flex-wrap: wrap;
  gap: 22px;
  margin: 40px 0 0;
  padding: 0;
  list-style: none;
}

.feature {
  display: flex;
  align-items: center;
  gap: 10px;
}

.feature-icon {
  width: 27px;
  height: 27px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border-radius: 50%;
  background: rgba(213, 180, 92, 0.14);
  color: var(--gold);
  font-size: 11px;
  font-weight: 900;
}

.feature div {
  display: flex;
  flex-direction: column;
}

.feature strong {
  color: #e7eef5;
  font-size: 12px;
  font-weight: 600;
}

.feature small {
  margin-top: 3px;
  color: #829ab1;
  font-size: 11px;
}

.institution-footer {
  margin-top: 56px;
  color: #6f89a2;
  font-size: 11px;
  letter-spacing: 0.3px;
}

/* =========================================================
   LOGIN SECTION
========================================================= */

.login-section {
  flex: 1;
  min-height: 100dvh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 32px;
}

.login-container {
  width: 100%;
  max-width: 420px;
  animation: fade-in-up 0.5s ease both;
  animation-delay: 0.1s;
}

.mobile-brand {
  display: none;
}

/* =========================================================
   LOGIN CARD
========================================================= */

.login-card {
  width: 100%;
  padding: clamp(28px, 4vw, 38px);
  background: white;
  border: 1px solid #e4eaf0;
  border-radius: var(--radius-lg);
  box-shadow: 0 15px 45px rgba(8, 38, 77, 0.08);
}

.login-header {
  margin-bottom: 26px;
}

.login-eyebrow {
  color: var(--navy-600);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
}

.login-header h2 {
  margin: 10px 0 6px;
  color: var(--navy-900);
  font-size: clamp(24px, 3vw, 28px);
  font-weight: 700;
}

.login-header p {
  margin: 0;
  color: var(--slate);
  font-size: 13.5px;
  line-height: 1.6;
}

/* =========================================================
   ALERT
========================================================= */

.alert-error {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 20px;
  padding: 12px;
  border: 1px solid var(--danger-border);
  border-radius: var(--radius-md);
  background: var(--danger-bg);
  color: var(--danger);
}

.alert-icon {
  width: 22px;
  height: 22px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #fde8e8;
  font-size: 12px;
  font-weight: 900;
}

.alert-error strong {
  display: block;
  font-size: 13px;
}

.alert-error p {
  margin: 3px 0 0;
  color: #c0564a;
  font-size: 12.5px;
  line-height: 1.5;
}

.alert-enter-active,
.alert-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.alert-enter-from,
.alert-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* =========================================================
   FORM
========================================================= */

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  margin-bottom: 7px;
  color: var(--ink-soft);
  font-size: 13px;
  font-weight: 600;
}

.label-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
}

.forgot-link {
  color: var(--navy-600);
  font-size: 12.5px;
  font-weight: 600;
  text-decoration: none;
}

.forgot-link:hover {
  text-decoration: underline;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  color: var(--slate);
  font-size: 13px;
  pointer-events: none;
}

input[type="email"],
input[type="password"],
input[type="text"] {
  width: 100%;
  height: 49px;
  padding: 0 14px 0 40px;
  border: 1px solid var(--border);
  border-radius: 7px;
  background: #fff;
  color: var(--ink);
  font-family: inherit;
  /* 16px avoids iOS Safari's auto-zoom-on-focus */
  font-size: 16px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

input::placeholder {
  color: #9fb3c8;
  font-size: 13.5px;
}

input:hover {
  border-color: #bcccdc;
}

input:focus {
  outline: none;
  border-color: var(--navy-600);
  box-shadow: 0 0 0 3px rgba(27, 77, 128, 0.09);
}

input:disabled {
  background: #f5f7fa;
  cursor: not-allowed;
}

input[type="password"],
input[type="text"] {
  padding-right: 46px;
}

.password-toggle {
  position: absolute;
  right: 6px;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: var(--navy-600);
  cursor: pointer;
}

.password-toggle:hover {
  background: #edf3f8;
}

.password-toggle:focus-visible {
  outline: 2px solid var(--gold);
  outline-offset: 1px;
}

.capslock-hint {
  margin: 7px 0 0;
  color: #a15c00;
  font-size: 12px;
  font-weight: 600;
}

.remember-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 4px 0 22px;
  color: var(--ink-soft);
  font-size: 13px;
  cursor: pointer;
}

.remember-row input {
  width: 16px;
  height: 16px;
  accent-color: var(--navy-600);
  cursor: pointer;
}

/* =========================================================
   LOGIN BUTTON
========================================================= */

.login-button {
  width: 100%;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  border: 0;
  border-radius: 7px;
  background: linear-gradient(135deg, var(--navy-900), var(--navy-600));
  color: white;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 7px 18px rgba(8, 38, 77, 0.15);
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.login-button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 10px 23px rgba(8, 38, 77, 0.2);
}

.login-button:active:not(:disabled) {
  transform: translateY(0);
}

.login-button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

.button-arrow {
  font-size: 16px;
}

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.35);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* =========================================================
   SECURITY
========================================================= */

.security-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  padding-top: 17px;
  border-top: 1px solid #edf1f5;
}

.security-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--success-bg);
  color: var(--success-ink);
  font-size: 10px;
  font-weight: 900;
}

.security-info p {
  margin: 0;
  color: var(--slate);
  font-size: 12px;
  line-height: 1.5;
}

/* =========================================================
   FOOTER
========================================================= */

.login-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 18px;
  color: #9aa9b8;
  font-size: 12px;
  text-align: center;
}

.footer-separator {
  color: var(--gold);
}

/* =========================================================
   TABLET (≤ 1024px)
========================================================= */

@media (max-width: 1024px) {
  .institution-panel {
    width: 40%;
    min-width: 320px;
    padding: 36px;
  }

  .institution-description {
    margin-top: 46px;
  }

  .institution-features {
    flex-direction: column;
    gap: 14px;
  }

  .login-section {
    padding: 32px 24px;
  }
}

/* =========================================================
   MOBILE (≤ 800px) — stacked layout
========================================================= */

@media (max-width: 800px) {
  .login-page {
    display: block;
    min-height: 100dvh;
    background: linear-gradient(180deg, #eef3f7 0%, #f7f9fb 100%);
  }

  .institution-panel {
    display: none;
  }

  .login-section {
    min-height: 100dvh;
    padding: max(24px, env(safe-area-inset-top)) 18px max(24px, env(safe-area-inset-bottom));
  }

  .login-container {
    max-width: 440px;
  }

  .mobile-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 22px;
  }

  .mobile-logo {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--gold);
    color: var(--navy-900);
    border: 2px solid var(--navy-900);
    font-size: 12px;
    font-weight: 900;
  }

  .mobile-brand div:last-child {
    display: flex;
    flex-direction: column;
  }

  .mobile-brand strong {
    color: var(--navy-900);
    font-size: 13px;
  }

  .mobile-brand span {
    margin-top: 3px;
    color: var(--slate);
    font-size: 11.5px;
  }

  .login-card {
    padding: 26px 22px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(8, 38, 77, 0.08);
  }
}

/* =========================================================
   SMALL MOBILE (≤ 400px)
========================================================= */

@media (max-width: 400px) {
  .login-section {
    padding: 18px 14px;
  }

  .login-card {
    padding: 22px 16px;
  }

  .mobile-brand {
    margin-bottom: 16px;
  }

  .login-header {
    margin-bottom: 20px;
  }

  .label-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
  }

  input,
  .login-button {
    height: 47px;
  }

  .login-footer {
    flex-direction: column;
    gap: 3px;
  }

  .footer-separator {
    display: none;
  }
}

/* =========================================================
   ACCESSIBILITY
========================================================= */

button:focus-visible,
input:focus-visible,
a:focus-visible {
  outline: 3px solid rgba(213, 180, 92, 0.45);
  outline-offset: 2px;
}

/* =========================================================
   MASQUER L'ICÔNE PASSWORD AUTOMATIQUE DU NAVIGATEUR
   ========================================================= */

/* Microsoft Edge / navigateurs Chromium */
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
  display: none;
}

/* Certains navigateurs */
input[type="password"]::-webkit-credentials-auto-fill-button {
  visibility: hidden;
  pointer-events: none;
  position: absolute;
  right: 0;
}

</style>