```vue
<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  ShieldCheck,
  Mail,
  LockKeyhole,
  Eye,
  EyeOff,
  ArrowRight,
  Check,
  AlertCircle,
  LoaderCircle,
  Building2,
  FileText,
  Activity,
  Database,
  Sparkles,
} from 'lucide-vue-next'

import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

/* =========================================================
   ÉTAT DU FORMULAIRE
========================================================= */

const emailInput = ref(null)
const username = ref('')
const password = ref('')
const remember = ref(false)

const loading = ref(false)
const errorMessage = ref('')
const showPassword = ref(false)
const capsLockOn = ref(false)

const currentYear = new Date().getFullYear()

/* =========================================================
   VALIDATION
========================================================= */

const canSubmit = computed(() => {
  return (
    username.value.trim().length > 0 &&
    password.value.length > 0 &&
    !loading.value
  )
})

/* =========================================================
   CAPS LOCK
========================================================= */

const detectCapsLock = (event) => {
  if (typeof event.getModifierState === 'function') {
    capsLockOn.value = event.getModifierState('CapsLock')
  }
}

/* =========================================================
   CONNEXION
========================================================= */

const handleLogin = async () => {
  errorMessage.value = ''

  if (!username.value.trim() || !password.value) {
    errorMessage.value =
      'Veuillez renseigner votre adresse e-mail et votre mot de passe.'

    await nextTick()
    emailInput.value?.focus()
    return
  }

  if (loading.value) return

  const email = username.value.trim()
  const motDePasse = password.value

  /*
   * Nettoyage immédiat des champs
   * après récupération des valeurs.
   */
  username.value = ''
  password.value = ''

  await nextTick()
  emailInput.value?.focus()

  loading.value = true

  try {
    const data = await auth.login(
      email,
      motDePasse,
      remember.value
    )

    console.log('LOGIN USER:', data.utilisateur)

    const doitChangerMotDePasse =
      data.utilisateur?.doit_changer_mdp === true ||
      data.utilisateur?.doit_changer_mdp === 1 ||
      data.utilisateur?.doit_changer_mdp === '1'

    if (doitChangerMotDePasse) {
      router.push('/change-password')
    } else {
      router.push('/dashboard')
    }
  } catch (error) {
    console.error('Erreur de connexion:', error)

    errorMessage.value =
      error.message ||
      'Impossible de contacter le serveur.'

    username.value = ''
    password.value = ''

    await nextTick()
    emailInput.value?.focus()
  } finally {
    loading.value = false
  }
}

/* =========================================================
   MOUNT
========================================================= */

onMounted(() => {
  nextTick(() => {
    emailInput.value?.focus()
  })
})
</script>

<template>
  <div class="login-page">

    <!-- =====================================================
         BACKGROUND DECORATION
    ====================================================== -->

    <div class="background-decoration" aria-hidden="true">
      <div class="glow glow-one"></div>
      <div class="glow glow-two"></div>
      <div class="grid-pattern"></div>
    </div>

    <!-- =====================================================
         BRAND / INSTITUTION PANEL
    ====================================================== -->

    <aside class="institution-panel">

      <div class="institution-overlay"></div>

      <div class="institution-content">

        <!-- Brand -->
        <div class="brand-block">

          <div class="brand-mark">
            <div class="brand-mark-inner">
              <img src="@/assets/logoGn.jpg" alt="Gendarmerie Nationale" />
            </div>

          </div>

          <div class="brand-text">
            <span class="brand-country">
              RÉPUBLIQUE DE MADAGASCAR
            </span>

            <strong>
              GENDARMERIE NATIONALE
            </strong>

            <span>
              Service des archives
            </span>
          </div>

        </div>

        <!-- Main presentation -->
        <div class="institution-hero">

          <div class="hero-badge">
            <Sparkles :size="14" />
            Plateforme administrative
          </div>

          <h1>
            Gestion des
            <span>archives administratives.</span>
          </h1>

          <p>
            Une plateforme centralisée pour organiser,
            sécuriser et assurer la traçabilité des
            courriers et documents administratifs.
          </p>

        </div>

        <!-- Features -->
        <div class="feature-grid">

          <div class="feature-card">
            <div class="feature-icon">
              <Database :size="18" />
            </div>

            <div>
              <strong>Centralisé</strong>
              <span>Documents organisés</span>
            </div>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <ShieldCheck :size="18" />
            </div>

            <div>
              <strong>Sécurisé</strong>
              <span>Accès contrôlé</span>
            </div>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <Activity :size="18" />
            </div>

            <div>
              <strong>Traçable</strong>
              <span>Activités journalisées</span>
            </div>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <FileText :size="18" />
            </div>

            <div>
              <strong>Structuré</strong>
              <span>Courriers et pièces</span>
            </div>
          </div>

        </div>

        <!-- Bottom -->
        <div class="institution-bottom">

          <div class="bottom-security">
            <ShieldCheck :size="15" />
            <span>
              Système interne sécurisé
            </span>
          </div>

          <span class="bottom-version">
            Administration numérique
          </span>

        </div>

      </div>
    </aside>

    <!-- =====================================================
         LOGIN AREA
    ====================================================== -->

    <main class="login-section">

      <div class="login-container">

        <!-- Mobile brand -->
        <div class="mobile-brand">

          <div class="mobile-brand-mark">
            <img src="@/assets/logoGn.jpg" alt="Gendarmerie Nationale" />
          </div>

          <div class="mobile-brand-text">
            <strong>Gendarmerie Nationale</strong>
            <span>Gestion des archives</span>
          </div>

          <div class="mobile-security">
            <ShieldCheck :size="17" />
          </div>

        </div>

        <!-- Login card -->
        <section class="login-card">

          <!-- Top status -->
          <div class="card-top">

            <div class="secure-badge">
              <span class="secure-dot"></span>
              <ShieldCheck :size="14" />
              Accès sécurisé
            </div>

            <span class="system-label">
              ESPACE INTERNE
            </span>

          </div>

          <!-- Header -->
          <header class="login-header">

            <div class="welcome-icon">
              <LockKeyhole :size="22" />
            </div>

            <div>
              <span class="login-eyebrow">
                Authentification
              </span>

              <h2>
                Bienvenue
              </h2>

              <p>
                Connectez-vous à votre espace de gestion
                des archives administratives.
              </p>
            </div>

          </header>

          <!-- Error -->
          <Transition name="alert">

            <div
              v-if="errorMessage"
              class="alert-error"
              role="alert"
              aria-live="assertive"
            >

              <div class="alert-icon">
                <AlertCircle :size="18" />
              </div>

              <div class="alert-content">

                <strong>
                  Connexion impossible
                </strong>

                <p>
                  {{ errorMessage }}
                </p>

              </div>

            </div>

          </Transition>

          <!-- Form -->
          <form
            @submit.prevent="handleLogin"
            novalidate
          >

            <!-- Email -->
            <div class="form-group">

              <label for="email">
                Adresse e-mail
              </label>

              <div class="input-wrapper">

                <div class="input-icon">
                  <Mail :size="18" />
                </div>

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

                <label for="password">
                  Mot de passe
                </label>

                <a
                  href="#"
                  class="forgot-link"
                  tabindex="0"
                  @click.prevent
                >
                  Mot de passe oublié ?
                </a>

              </div>

              <div class="input-wrapper">

                <div class="input-icon">
                  <LockKeyhole :size="18" />
                </div>

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
                  :disabled="loading"
                  :aria-label="
                    showPassword
                      ? 'Masquer le mot de passe'
                      : 'Afficher le mot de passe'
                  "
                  :aria-pressed="showPassword"
                  @click="showPassword = !showPassword"
                >

                  <EyeOff
                    v-if="showPassword"
                    :size="18"
                  />

                  <Eye
                    v-else
                    :size="18"
                  />

                </button>

              </div>

              <Transition name="caps">

                <p
                  v-if="capsLockOn"
                  id="capslock-hint"
                  class="capslock-hint"
                >
                  <AlertCircle :size="14" />
                  Verr. Maj est activé
                </p>

              </Transition>

            </div>

            <!-- Remember -->
            <label class="remember-row">

              <span class="checkbox-wrapper">

                <input
                  v-model="remember"
                  type="checkbox"
                  :disabled="loading"
                />

                <span class="custom-checkbox">
                  <Check :size="12" />
                </span>

              </span>

              <span class="remember-text">
                Rester connecté sur cet appareil
              </span>

            </label>

            <!-- Submit -->
            <button
              type="submit"
              class="login-button"
              :disabled="!canSubmit"
            >

              <template v-if="loading">

                <LoaderCircle
                  class="button-spinner"
                  :size="18"
                />

                <span>
                  Connexion en cours...
                </span>

              </template>

              <template v-else>

                <span>
                  Se connecter
                </span>

              </template>

            </button>

          </form>

          <!-- Security -->
          <div class="security-info">

            <div class="security-check">
              <ShieldCheck :size="17" />
            </div>

            <div>
              <strong>
                Accès réservé
              </strong>

              <p>
                Seuls les utilisateurs autorisés
                peuvent accéder à cette plateforme.
              </p>
            </div>

          </div>

        </section>

        <!-- Footer -->
        <footer class="login-footer">

          <div class="footer-main">
            <span>
              © {{ currentYear }} Gendarmerie Nationale
            </span>

            <span class="footer-dot">
              •
            </span>

            <span>
              Gestion des archives
            </span>
          </div>

          <span class="footer-system">
            Système administratif interne
          </span>

        </footer>

      </div>
    </main>

  </div>
</template>

<style scoped>

/* =========================================================
   DESIGN TOKENS
========================================================= */

* {
  box-sizing: border-box;
}

.login-page {
  --navy-950: #041426;
  --navy-900: #061b32;
  --navy-800: #092846;
  --navy-700: #0d3a61;
  --navy-600: #145584;
  --navy-500: #1d6d9d;

  --gold: #d6b45d;
  --gold-light: #e5ca82;

  --white: #ffffff;
  --surface: #ffffff;
  --surface-soft: #f8fafc;

  --ink: #172b3f;
  --ink-soft: #4a6075;
  --muted: #8093a6;

  --border: #dce5ed;
  --border-light: #e9eef3;

  --danger: #b42318;
  --danger-bg: #fff7f6;
  --danger-border: #f1cfcb;

  --success: #247a4a;
  --success-bg: #eef9f2;

  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 18px;
  --radius-xl: 24px;

  min-height: 100dvh;
  display: flex;

  background:
    radial-gradient(
      circle at 85% 15%,
      rgba(31, 108, 158, 0.06),
      transparent 30%
    ),
    #f5f8fb;

  color: var(--ink);

  font-family:
    Inter,
    "Segoe UI",
    Roboto,
    Arial,
    sans-serif;

  overflow: hidden;
}

/* =========================================================
   BACKGROUND
========================================================= */

.background-decoration {
  position: fixed;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 0;
}

.glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(90px);
  opacity: 0.35;
}

.glow-one {
  width: 300px;
  height: 300px;
  top: -120px;
  right: 12%;
  background: rgba(27, 92, 137, 0.08);
}

.glow-two {
  width: 250px;
  height: 250px;
  bottom: -120px;
  right: 25%;
  background: rgba(214, 180, 93, 0.08);
}

.grid-pattern {
  position: absolute;
  inset: 0;

  background-image:
    linear-gradient(
      rgba(8, 38, 77, 0.025) 1px,
      transparent 1px
    ),
    linear-gradient(
      90deg,
      rgba(8, 38, 77, 0.025) 1px,
      transparent 1px
    );

  background-size: 36px 36px;

  mask-image: linear-gradient(
    to bottom,
    black,
    transparent 70%
  );
}

/* =========================================================
   INSTITUTION PANEL
========================================================= */

.institution-panel {
  position: relative;
  z-index: 1;

  width: 47%;
  min-width: 500px;

  min-height: 100dvh;

  display: flex;
  align-items: center;

  padding:
    clamp(40px, 6vw, 78px)
    clamp(40px, 6vw, 82px);

  overflow: hidden;

  color: white;

  background:
    radial-gradient(
      circle at 80% 10%,
      rgba(38, 112, 157, 0.42),
      transparent 34%
    ),
    linear-gradient(
      145deg,
      var(--navy-950) 0%,
      var(--navy-900) 42%,
      var(--navy-700) 100%
    );
}

.institution-panel::before {
  content: "";

  position: absolute;

  width: 560px;
  height: 560px;

  right: -300px;
  top: -250px;

  border-radius: 50%;

  border: 1px solid rgba(214, 180, 93, 0.16);
}

.institution-panel::after {
  content: "";

  position: absolute;

  width: 460px;
  height: 460px;

  left: -310px;
  bottom: -270px;

  border-radius: 50%;

  border: 1px solid rgba(255, 255, 255, 0.05);
}

.institution-overlay {
  position: absolute;
  inset: 0;

  background:
    linear-gradient(
      120deg,
      transparent 30%,
      rgba(255, 255, 255, 0.025) 50%,
      transparent 70%
    );
}

.institution-content {
  position: relative;
  z-index: 2;

  width: 100%;
  max-width: 650px;

  margin: auto;

  animation: fade-up 0.7s ease both;
}

/* =========================================================
   BRAND
========================================================= */

.brand-block {
  display: flex;
  align-items: center;
  gap: 17px;
}

.brand-mark {
  position: relative;

  width: 62px;
  height: 62px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border-radius: 18px;

  background: white;
}

.brand-mark-inner {
  width: 48px;
  height: 48px;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 1px solid white;
  border-radius: 14px;

  color: var(--navy-900);

  font-size: 15px;
  font-weight: 900;

  transform: rotate(0deg);
}


.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-mark-inner img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.mobile-brand-mark img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.brand-country {
  margin-bottom: 5px;

  color: rgba(224, 235, 244, 0.62);

  font-size: 10px;
  font-weight: 700;

  letter-spacing: 1.4px;
}

.brand-text strong {
  color: white;

  font-size: 15px;
  font-weight: 750;

  letter-spacing: 0.3px;
}

.brand-text span:last-child {
  margin-top: 4px;

  color: var(--gold-light);

  font-size: 11px;
  font-weight: 600;

  letter-spacing: 0.5px;
}

/* =========================================================
   HERO
========================================================= */

.institution-hero {
  margin-top: clamp(80px, 10vh, 115px);
}

.hero-badge {
  width: fit-content;

  display: inline-flex;
  align-items: center;
  gap: 7px;

  padding: 7px 11px;

  border: 1px solid rgba(214, 180, 93, 0.25);
  border-radius: 999px;

  background: rgba(214, 180, 93, 0.08);

  color: var(--gold-light);

  font-size: 11px;
  font-weight: 650;

  backdrop-filter: blur(8px);
}

.institution-hero h1 {
  max-width: 590px;

  margin: 20px 0 17px;

  color: white;

  font-size: clamp(36px, 4vw, 54px);
  line-height: 1.04;

  font-weight: 750;

  letter-spacing: -1.8px;
}

.institution-hero h1 span {
  display: block;

  color: var(--gold-light);
}

.institution-hero p {
  max-width: 510px;

  margin: 0;

  color: rgba(218, 229, 239, 0.72);

  font-size: 14px;
  line-height: 1.8;
}

/* =========================================================
   FEATURE CARDS
========================================================= */

.feature-grid {
  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 10px;

  margin-top: 42px;

  max-width: 570px;
}

.feature-card {
  display: flex;
  align-items: center;

  gap: 11px;

  padding: 13px;

  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 12px;

  background: rgba(255, 255, 255, 0.045);

  backdrop-filter: blur(10px);

  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.feature-card:hover {
  transform: translateY(-2px);

  background: rgba(255, 255, 255, 0.07);

  border-color: rgba(214, 180, 93, 0.2);
}

.feature-icon {
  width: 35px;
  height: 35px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border-radius: 9px;

  background: rgba(214, 180, 93, 0.1);

  color: var(--gold-light);
}

.feature-card div:last-child {
  display: flex;
  flex-direction: column;
}

.feature-card strong {
  color: #f1f5f8;

  font-size: 12px;
  font-weight: 650;
}

.feature-card span {
  margin-top: 3px;

  color: rgba(190, 207, 220, 0.58);

  font-size: 10.5px;
}

/* =========================================================
   INSTITUTION BOTTOM
========================================================= */

.institution-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 20px;

  margin-top: 55px;
  padding-top: 20px;

  border-top: 1px solid rgba(255, 255, 255, 0.07);
}

.bottom-security {
  display: flex;
  align-items: center;
  gap: 7px;

  color: rgba(207, 222, 233, 0.65);

  font-size: 10.5px;
}

.bottom-security svg {
  color: #65c78b;
}

.bottom-version {
  color: rgba(182, 200, 214, 0.4);

  font-size: 10px;
}

/* =========================================================
   LOGIN SECTION
========================================================= */

.login-section {
  position: relative;
  z-index: 2;

  flex: 1;

  min-height: 100dvh;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 50px 40px;
}

.login-container {
  width: 100%;
  max-width: 470px;

  animation: fade-up 0.6s ease both;
  animation-delay: 0.08s;
}

/* =========================================================
   MOBILE BRAND
========================================================= */

.mobile-brand {
  display: none;
}

/* =========================================================
   LOGIN CARD
========================================================= */

.login-card {
  position: relative;

  padding: 34px;

  border: 1px solid rgba(215, 225, 234, 0.95);
  border-radius: var(--radius-xl);

  background: rgba(255, 255, 255, 0.94);

  box-shadow:
    0 24px 70px rgba(6, 27, 50, 0.09),
    0 4px 16px rgba(6, 27, 50, 0.04);

  backdrop-filter: blur(18px);
}

.login-card::before {
  content: "";

  position: absolute;

  left: 28px;
  right: 28px;
  top: 0;

  height: 1px;

  background:
    linear-gradient(
      90deg,
      transparent,
      rgba(214, 180, 93, 0.5),
      transparent
    );
}

/* =========================================================
   CARD TOP
========================================================= */

.card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;

  margin-bottom: 27px;
}

.secure-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;

  padding: 6px 9px;

  border: 1px solid #d8e9de;
  border-radius: 999px;

  background: #f3faf5;

  color: #277449;

  font-size: 10.5px;
  font-weight: 700;
}

.secure-dot {
  width: 6px;
  height: 6px;

  border-radius: 50%;

  background: #43a76b;

  box-shadow:
    0 0 0 3px rgba(67, 167, 107, 0.1);
}

.system-label {
  color: #94a5b5;

  font-size: 9px;
  font-weight: 750;

  letter-spacing: 1.2px;
}

/* =========================================================
   LOGIN HEADER
========================================================= */

.login-header {
  display: flex;
  align-items: flex-start;

  gap: 14px;

  margin-bottom: 25px;
}

.welcome-icon {
  width: 44px;
  height: 44px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border-radius: 12px;

  background:
    linear-gradient(
      145deg,
      #edf4f9,
      #e6eff6
    );

  color: var(--navy-700);

  box-shadow:
    inset 0 1px 0 white;
}

.login-eyebrow {
  display: block;

  margin-bottom: 5px;

  color: var(--navy-600);

  font-size: 10.5px;
  font-weight: 750;

  letter-spacing: 1px;
  text-transform: uppercase;
}

.login-header h2 {
  margin: 0;

  color: var(--navy-950);

  font-size: 28px;
  line-height: 1.15;

  font-weight: 760;

  letter-spacing: -0.7px;
}

.login-header p {
  max-width: 350px;

  margin: 7px 0 0;

  color: var(--muted);

  font-size: 12.5px;
  line-height: 1.65;
}

/* =========================================================
   ERROR
========================================================= */

.alert-error {
  display: flex;
  align-items: flex-start;

  gap: 10px;

  margin-bottom: 19px;
  padding: 12px 13px;

  border: 1px solid var(--danger-border);
  border-radius: 11px;

  background: var(--danger-bg);
}

.alert-icon {
  width: 28px;
  height: 28px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border-radius: 8px;

  background: #fee9e7;

  color: var(--danger);
}

.alert-content strong {
  display: block;

  color: #8f241d;

  font-size: 12px;
}

.alert-content p {
  margin: 3px 0 0;

  color: #b34b42;

  font-size: 11.5px;
  line-height: 1.5;
}

.alert-enter-active,
.alert-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}

.alert-enter-from,
.alert-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

/* =========================================================
   FORM
========================================================= */

.form-group {
  margin-bottom: 19px;
}

.form-group label {
  display: block;

  margin-bottom: 7px;

  color: #344b60;

  font-size: 12px;
  font-weight: 700;
}

.label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 10px;
}

.forgot-link {
  color: var(--navy-600);

  font-size: 11px;
  font-weight: 650;

  text-decoration: none;
}

.forgot-link:hover {
  color: var(--navy-800);
  text-decoration: underline;
}

/* =========================================================
   INPUT
========================================================= */

.input-wrapper {
  position: relative;

  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;

  display: flex;

  color: #8da0b2;

  pointer-events: none;

  transition: color 0.2s ease;
}

.input-wrapper:focus-within .input-icon {
  color: var(--navy-600);
}

.input-wrapper input {
  width: 100%;
  height: 50px;

  padding:
    0 14px 0 42px;

  border: 1px solid #d7e1e9;
  border-radius: 10px;

  outline: none;

  background: #fbfcfd;

  color: var(--ink);

  font-family: inherit;
  font-size: 14px;

  transition:
    border-color 0.2s ease,
    background 0.2s ease,
    box-shadow 0.2s ease;
}

.input-wrapper input::placeholder {
  color: #a4b3c1;
  font-size: 12.5px;
}

.input-wrapper input:hover {
  border-color: #c1cfdb;
  background: white;
}

.input-wrapper input:focus {
  border-color: var(--navy-600);

  background: white;

  box-shadow: none;
}

.input-wrapper input:disabled {
  background: #f3f6f8;
  cursor: not-allowed;
}

.input-wrapper input[type="password"],
.input-wrapper input[type="text"] {
  padding-right: 48px;
}

/* =========================================================
   PASSWORD BUTTON
========================================================= */

.password-toggle {
  position: absolute;

  right: 7px;

  width: 35px;
  height: 35px;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 0;
  border-radius: 8px;

  background: transparent;

  color: #7890a4;

  cursor: pointer;

  transition:
    background 0.2s ease,
    color 0.2s ease;
}

.password-toggle:hover:not(:disabled) {
  background: #edf3f7;
  color: var(--navy-600);
}

.password-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* =========================================================
   CAPS LOCK
========================================================= */

.capslock-hint {
  display: flex;
  align-items: center;
  gap: 5px;

  margin: 7px 0 0;

  color: #a15c00;

  font-size: 11px;
  font-weight: 650;
}

.caps-enter-active,
.caps-leave-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}

.caps-enter-from,
.caps-leave-to {
  opacity: 0;
  transform: translateY(-3px);
}

/* =========================================================
   REMEMBER
========================================================= */

.remember-row {
  display: flex;
  align-items: center;

  gap: 8px;

  margin: 1px 0 22px;

  color: #53687b;

  font-size: 11.5px;

  cursor: pointer;
  user-select: none;
}

.checkbox-wrapper {
  position: relative;

  width: 17px;
  height: 17px;

  display: flex;
}

.checkbox-wrapper input {
  position: absolute;

  width: 1px;
  height: 1px;

  opacity: 0;
}

.custom-checkbox {
  width: 17px;
  height: 17px;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 1px solid #c7d4df;
  border-radius: 5px;

  background: white;

  color: white;

  transition:
    background 0.2s ease,
    border-color 0.2s ease;
}

.custom-checkbox svg {
  opacity: 0;
}

.checkbox-wrapper input:checked + .custom-checkbox {
  border-color: var(--navy-600);

  background: var(--navy-600);
}

.checkbox-wrapper input:checked + .custom-checkbox svg {
  opacity: 1;
}

.checkbox-wrapper input:focus-visible + .custom-checkbox {
  outline: 3px solid rgba(214, 180, 93, 0.35);
  outline-offset: 2px;
}

.remember-text {
  line-height: 1.3;
}

/* =========================================================
   LOGIN BUTTON
========================================================= */

.login-button {
  position: relative;

  width: 100%;
  height: 51px;

  display: flex;
  align-items: center;
  justify-content: center;

  gap: 9px;

  border: 0;
  border-radius: 10px;

  background:
    linear-gradient(
      135deg,
      var(--navy-950),
      var(--navy-700)
    );

  color: white;

  font-family: inherit;
  font-size: 13px;
  font-weight: 750;

  cursor: pointer;

  box-shadow:
    0 9px 22px rgba(6, 27, 50, 0.17);

  overflow: hidden;

  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    opacity 0.2s ease;
}

.login-button::before {
  content: "";

  position: absolute;
  inset: 0;

  background:
    linear-gradient(
      110deg,
      transparent 25%,
      rgba(255, 255, 255, 0.09) 50%,
      transparent 75%
    );

  transform: translateX(-100%);

  transition: transform 0.5s ease;
}

.login-button:hover:not(:disabled)::before {
  transform: translateX(100%);
}

.login-button:hover:not(:disabled) {
  transform: translateY(-1px);

  box-shadow:
    0 13px 27px rgba(6, 27, 50, 0.22);
}

.login-button:active:not(:disabled) {
  transform: translateY(0);
}

.login-button:disabled {
  opacity: 0.52;

  cursor: not-allowed;

  box-shadow: none;
}

.button-arrow {
  width: 27px;
  height: 27px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 7px;

  background: rgba(255, 255, 255, 0.1);
}

.button-spinner {
  animation: spin 0.75s linear infinite;
}

/* =========================================================
   SECURITY
========================================================= */

.security-info {
  display: flex;
  align-items: center;

  gap: 10px;

  margin-top: 20px;
  padding-top: 18px;

  border-top: 1px solid #edf1f4;
}

.security-check {
  width: 32px;
  height: 32px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border-radius: 9px;

  background: var(--success-bg);

  color: var(--success);
}

.security-info strong {
  display: block;

  color: #52687a;

  font-size: 10.5px;
  font-weight: 750;
}

.security-info p {
  max-width: 340px;

  margin: 3px 0 0;

  color: #94a3b1;

  font-size: 10.5px;
  line-height: 1.45;
}

/* =========================================================
   FOOTER
========================================================= */

.login-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;

  gap: 12px;

  margin-top: 17px;
  padding: 0 4px;

  color: #98a8b7;

  font-size: 10px;
}

.footer-main {
  display: flex;
  align-items: center;
  gap: 7px;
}

.footer-dot {
  color: var(--gold);
}

.footer-system {
  color: #b1bdc8;
}

/* =========================================================
   ANIMATIONS
========================================================= */

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes fade-up {
  from {
    opacity: 0;
    transform: translateY(12px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* =========================================================
   ACCESSIBILITY
========================================================= */

button:focus-visible,
input:focus-visible,
a:focus-visible {
  outline: none;
}

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

/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1100px) {

  .institution-panel {
    width: 44%;
    min-width: 400px;

    padding-left: 45px;
    padding-right: 45px;
  }

  .institution-hero h1 {
    font-size: 40px;
  }

  .feature-grid {
    grid-template-columns: 1fr;
  }

  .login-section {
    padding-left: 28px;
    padding-right: 28px;
  }
}

/* =========================================================
   SMALL TABLET / MOBILE
========================================================= */

@media (max-width: 850px) {

  .login-page {
    display: block;

    min-height: 100dvh;

    overflow: auto;

    background:
      radial-gradient(
        circle at 50% 0%,
        rgba(27, 92, 137, 0.08),
        transparent 30%
      ),
      #f4f7fa;
  }

  .institution-panel {
    display: none;
  }

  .login-section {
    min-height: 100dvh;

    display: flex;
    align-items: center;

    padding:
      max(24px, env(safe-area-inset-top))
      18px
      max(24px, env(safe-area-inset-bottom));
  }

  .login-container {
    max-width: 460px;
  }

  .mobile-brand {
    display: flex;
    align-items: center;

    gap: 11px;

    margin-bottom: 17px;
  }

  .mobile-brand-mark {
    width: 43px;
    height: 43px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background:
      linear-gradient(
        145deg,
        var(--gold-light),
        var(--gold)
      );

    color: var(--navy-900);

    font-size: 11px;
    font-weight: 900;

    box-shadow:
      0 7px 18px rgba(6, 27, 50, 0.12);
  }
  .brand-mark {
  display: flex;
  align-items: center;
  justify-content: center;

  width: 64px;
  height: 64px;

  overflow: hidden;
  transform: none;
  animation: none;
}

.brand-mark img {
  width: 100%;
  height: 100%;
  object-fit: contain;

  transform: none;
  animation: none;
}

  .mobile-brand-text {
    display: flex;
    flex-direction: column;
  }

  .mobile-brand-text strong {
    color: var(--navy-900);

    font-size: 12px;
    font-weight: 750;
  }

  .mobile-brand-text span {
    margin-top: 2px;

    color: #8193a5;

    font-size: 10px;
  }

  .mobile-security {
    width: 31px;
    height: 31px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-left: auto;

    border-radius: 9px;

    background: #edf8f1;

    color: #28774b;
  }

  .login-card {
    padding: 27px 22px;

    border-radius: 18px;

    box-shadow:
      0 18px 50px rgba(6, 27, 50, 0.09);
  }

  .login-footer {
    flex-direction: column;

    justify-content: center;

    margin-top: 14px;
  }

  .footer-system {
    display: none;
  }
}

/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 430px) {

  .login-section {
    padding:
      max(18px, env(safe-area-inset-top))
      13px
      max(18px, env(safe-area-inset-bottom));
  }

  .login-card {
    padding: 23px 17px;

    border-radius: 16px;
  }

  .card-top {
    margin-bottom: 23px;
  }

  .system-label {
    display: none;
  }

  .login-header {
    gap: 11px;
  }

  .welcome-icon {
    width: 40px;
    height: 40px;
  }

  .login-header h2 {
    font-size: 25px;
  }

  .login-header p {
    font-size: 11.5px;
  }

  .form-group {
    margin-bottom: 17px;
  }

  .label-row {
    align-items: flex-start;
  }

  .forgot-link {
    font-size: 10px;
  }

  .input-wrapper input {
    height: 48px;
  }

  .login-button {
    height: 49px;
  }

  .security-info {
    align-items: flex-start;
  }

  .security-info p {
    font-size: 10px;
  }

  .footer-main {
    flex-direction: column;
    gap: 2px;
  }

  .footer-dot {
    display: none;
  }
}

/* =========================================================
   REDUCED MOTION
========================================================= */

@media (prefers-reduced-motion: reduce) {

  .login-page *,
  .login-page *::before,
  .login-page *::after {
    animation-duration: 0.001ms !important;
    animation-iteration-count: 1 !important;

    transition-duration: 0.001ms !important;
    scroll-behavior: auto !important;
  }
}

</style>
```
