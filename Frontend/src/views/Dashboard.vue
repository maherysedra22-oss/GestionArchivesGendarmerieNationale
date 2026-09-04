```vue
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ======================================================
// UTILISATEUR CONNECTÉ
// ======================================================

const utilisateur = ref(null)

onMounted(() => {
  const data =
    localStorage.getItem('utilisateur') ||
    sessionStorage.getItem('utilisateur')

  if (data) {
    try {
      utilisateur.value = JSON.parse(data)
    } catch (error) {
      console.error(
        'Erreur lors de la lecture de l’utilisateur :',
        error
      )

      utilisateur.value = null
    }
  }
})

// ======================================================
// INFORMATIONS UTILISATEUR
// ======================================================

const prenom = computed(() => {
  return utilisateur.value?.prenom || 'Utilisateur'
})

const nom = computed(() => {
  return utilisateur.value?.nom || ''
})

const nomComplet = computed(() => {
  const value = `${prenom.value} ${nom.value}`.trim()

  return value || 'Utilisateur'
})

const role = computed(() => {
  return (
    utilisateur.value?.role?.nom_role ||
    utilisateur.value?.role?.name ||
    utilisateur.value?.role ||
    'Administrateur'
  )
})

const grade = computed(() => {
  return (
    utilisateur.value?.grade?.nom_grade ||
    utilisateur.value?.grade?.libelle ||
    utilisateur.value?.grade ||
    'Grade non défini'
  )
})

// ======================================================
// ACCÈS RAPIDES
// ======================================================

const allerVers = (route) => {
  router.push(route)
}
</script>


<template>

  <div class="dashboard-content">

    <!-- ==================================================
         WELCOME
    ================================================== -->

    <section class="welcome-card">

      <div class="welcome-content">

        <div class="welcome-label">
          ESPACE {{ role.toUpperCase() }}
        </div>

        <h2>
          Bienvenue,
          <span>{{ prenom }}</span>
        </h2>

        <p>
          Vous êtes connecté au système de gestion
          des archives administratives de la
          Gendarmerie Nationale.
        </p>

        <div class="welcome-meta">

          <div class="account-status">

            <span class="status-dot"></span>

            Compte actif

          </div>

          <div class="grade-badge">
            {{ grade }}
          </div>

        </div>

      </div>


      <!-- EMBLÈME -->

      <div class="welcome-emblem">

        <div class="emblem-circle">
          GN
        </div>

        <span>
          SERVICE<br />
          &amp; ARCHIVES
        </span>

      </div>

    </section>


    <!-- ==================================================
         STATISTIQUES
    ================================================== -->

    <section class="section">

      <div class="section-heading">

        <div>

          <h2>
            Vue générale
          </h2>

          <p>
            Aperçu de l'activité du système
          </p>

        </div>

      </div>


      <div class="statistics">

        <!-- COURRIERS ARRIVÉS -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon">
              ↓
            </div>

            <span class="stat-badge">
              Arrivée
            </span>

          </div>

          <div class="stat-number">
            0
          </div>

          <h3>
            Courriers arrivés
          </h3>

          <p>
            Courriers reçus
          </p>

        </article>


        <!-- COURRIERS DÉPART -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon">
              ↑
            </div>

            <span class="stat-badge">
              Départ
            </span>

          </div>

          <div class="stat-number">
            0
          </div>

          <h3>
            Courriers départ
          </h3>

          <p>
            Courriers envoyés
          </p>

        </article>


        <!-- DOCUMENTS -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon">
              ▤
            </div>

            <span class="stat-badge">
              Archives
            </span>

          </div>

          <div class="stat-number">
            0
          </div>

          <h3>
            Documents numériques
          </h3>

          <p>
            Documents archivés
          </p>

        </article>


        <!-- UTILISATEURS -->

        <article class="stat-card">

          <div class="stat-top">

            <div class="stat-icon">
              ♙
            </div>

            <span class="stat-badge">
              Comptes
            </span>

          </div>

          <div class="stat-number">
            0
          </div>

          <h3>
            Utilisateurs
          </h3>

          <p>
            Comptes enregistrés
          </p>

        </article>

      </div>

    </section>


    <!-- ==================================================
         ACTIVITÉS + ACCÈS RAPIDES
    ================================================== -->

    <section class="dashboard-grid">

      <!-- ACTIVITÉS -->

      <div class="panel">

        <div class="panel-header">

          <div>

            <h2>
              Activités récentes
            </h2>

            <p>
              Dernières opérations effectuées
            </p>

          </div>

          <span class="panel-icon">
            ◷
          </span>

        </div>


        <div class="empty-state">

          <div class="empty-state-icon">
            ◷
          </div>

          <h3>
            Aucune activité récente
          </h3>

          <p>
            Les opérations effectuées dans le système
            apparaîtront ici.
          </p>

        </div>

      </div>


      <!-- ACCÈS RAPIDES -->

      <div class="panel">

        <div class="panel-header">

          <div>

            <h2>
              Accès rapides
            </h2>

            <p>
              Actions fréquentes
            </p>

          </div>

          <span class="panel-icon">
            +
          </span>

        </div>


        <div class="quick-actions">

          <!-- COURRIER ARRIVÉ -->

          <button
            class="quick-action"
            type="button"
            @click="allerVers('/courriers-arrives')"
          >

            <span class="quick-icon">
              ↓
            </span>

            <span class="quick-text">

              <strong>
                Courrier arrivé
              </strong>

              <small>
                Enregistrer un courrier reçu
              </small>

            </span>

            <span class="quick-arrow">
              →
            </span>

          </button>


          <!-- COURRIER DÉPART -->

          <button
            class="quick-action"
            type="button"
            @click="allerVers('/courriers-depart')"
          >

            <span class="quick-icon">
              ↑
            </span>

            <span class="quick-text">

              <strong>
                Courrier départ
              </strong>

              <small>
                Enregistrer un courrier envoyé
              </small>

            </span>

            <span class="quick-arrow">
              →
            </span>

          </button>


          <!-- DOCUMENTS -->

          <button
            class="quick-action"
            type="button"
            @click="allerVers('/documents')"
          >

            <span class="quick-icon">
              ▤
            </span>

            <span class="quick-text">

              <strong>
                Documents numériques
              </strong>

              <small>
                Consulter les documents archivés
              </small>

            </span>

            <span class="quick-arrow">
              →
            </span>

          </button>

        </div>

      </div>

    </section>


    <!-- ==================================================
         INFORMATION
    ================================================== -->

    <section class="information">

      <div class="information-icon">
        GN
      </div>

      <div class="information-content">

        <h2>
          Système de gestion des archives
        </h2>

        <p>
          Cette plateforme permet de centraliser,
          organiser et consulter les courriers et
          documents administratifs de la Gendarmerie
          Nationale.
        </p>

      </div>

    </section>

  </div>

</template>


<style scoped>

/* ======================================================
   BASE
====================================================== */

.dashboard-content {
  width: 100%;
  max-width: 1400px;

  margin: 0 auto;

  padding: 30px;
}


/* ======================================================
   WELCOME
====================================================== */

.welcome-card {
  min-height: 185px;

  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 30px 34px;

  background:
    linear-gradient(
      120deg,
      #08264d 0%,
      #103c68 100%
    );

  border-radius: 13px;

  overflow: hidden;

  position: relative;

  box-shadow:
    0 10px 30px rgba(8, 38, 77, 0.13);
}

.welcome-card::after {
  content: "";

  position: absolute;

  width: 260px;
  height: 260px;

  right: -100px;
  top: -100px;

  border-radius: 50%;

  border:
    1px solid rgba(213, 180, 92, 0.18);
}

.welcome-content {
  position: relative;

  z-index: 2;
}

.welcome-label {
  color: #d5b45c;

  font-size: 9px;

  font-weight: 900;

  letter-spacing: 2px;
}

.welcome-card h2 {
  margin: 9px 0 7px;

  color: white;

  font-size: 28px;

  font-weight: 800;
}

.welcome-card h2 span {
  color: #d5b45c;
}

.welcome-card p {
  max-width: 650px;

  margin: 0;

  color: #b9c9da;

  font-size: 12px;

  line-height: 1.7;
}


/* ======================================================
   WELCOME META
====================================================== */

.welcome-meta {
  display: flex;

  align-items: center;

  gap: 10px;

  margin-top: 18px;
}

.account-status,
.grade-badge {
  display: inline-flex;

  align-items: center;

  gap: 7px;

  padding: 7px 11px;

  border-radius: 20px;

  font-size: 9px;

  font-weight: 700;
}

.account-status {
  background:
    rgba(255, 255, 255, 0.09);

  color: #dbe7f2;
}

.status-dot {
  width: 7px;
  height: 7px;

  border-radius: 50%;

  background: #42c875;

  box-shadow:
    0 0 0 4px rgba(66, 200, 117, 0.12);
}

.grade-badge {
  background:
    rgba(213, 180, 92, 0.13);

  color: #e6ca7c;
}


/* ======================================================
   EMBLÈME
====================================================== */

.welcome-emblem {
  position: relative;

  z-index: 2;

  display: flex;

  flex-direction: column;

  align-items: center;

  gap: 8px;

  margin-right: 25px;
}

.emblem-circle {
  width: 90px;
  height: 90px;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: #d5b45c;

  color: #08264d;

  border:
    5px solid rgba(255, 255, 255, 0.85);

  font-size: 23px;

  font-weight: 900;

  box-shadow:
    0 8px 25px rgba(0, 0, 0, 0.2);
}

.welcome-emblem > span {
  color: #d5b45c;

  font-size: 7px;

  font-weight: 800;

  text-align: center;

  letter-spacing: 1px;

  line-height: 1.5;
}


/* ======================================================
   SECTION
====================================================== */

.section {
  margin-top: 28px;
}

.section-heading {
  margin-bottom: 14px;
}

.section-heading h2 {
  margin: 0;

  color: #102a43;

  font-size: 17px;
}

.section-heading p {
  margin: 4px 0 0;

  color: #829ab1;

  font-size: 10px;
}


/* ======================================================
   STATISTICS
====================================================== */

.statistics {
  display: grid;

  grid-template-columns:
    repeat(4, minmax(0, 1fr));

  gap: 17px;
}

.stat-card {
  padding: 19px;

  background: white;

  border:
    1px solid #e4eaf0;

  border-radius: 10px;

  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.stat-card:hover {
  transform:
    translateY(-3px);

  box-shadow:
    0 10px 25px rgba(8, 38, 77, 0.08);
}

.stat-top {
  display: flex;

  align-items: center;

  justify-content: space-between;
}

.stat-icon {
  width: 43px;
  height: 43px;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 8px;

  background: #eaf1f8;

  color: #174d7d;

  font-size: 18px;

  font-weight: 900;
}

.stat-badge {
  padding: 5px 8px;

  border-radius: 12px;

  background: #f4f7fa;

  color: #829ab1;

  font-size: 8px;

  font-weight: 700;
}

.stat-number {
  margin-top: 18px;

  color: #08264d;

  font-size: 28px;

  font-weight: 900;
}

.stat-card h3 {
  margin: 3px 0 0;

  color: #243b53;

  font-size: 11px;
}

.stat-card p {
  margin: 5px 0 0;

  color: #9aa9b8;

  font-size: 9px;
}


/* ======================================================
   DASHBOARD GRID
====================================================== */

.dashboard-grid {
  display: grid;

  grid-template-columns:
    1.35fr 1fr;

  gap: 18px;

  margin-top: 20px;
}

.panel {
  min-height: 270px;

  padding: 22px;

  background: white;

  border:
    1px solid #e4eaf0;

  border-radius: 10px;
}

.panel-header {
  display: flex;

  justify-content: space-between;

  padding-bottom: 15px;

  border-bottom:
    1px solid #edf1f5;
}

.panel-header h2 {
  margin: 0;

  color: #102a43;

  font-size: 14px;
}

.panel-header p {
  margin: 4px 0 0;

  color: #829ab1;

  font-size: 9px;
}

.panel-icon {
  width: 31px;
  height: 31px;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 7px;

  background: #eaf1f8;

  color: #174d7d;

  font-size: 13px;

  font-weight: 800;
}


/* ======================================================
   EMPTY STATE
====================================================== */

.empty-state {
  min-height: 180px;

  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  text-align: center;
}

.empty-state-icon {
  width: 43px;
  height: 43px;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: #f1f5f8;

  color: #829ab1;

  font-size: 19px;
}

.empty-state h3 {
  margin: 10px 0 4px;

  color: #486581;

  font-size: 12px;
}

.empty-state p {
  max-width: 270px;

  margin: 0;

  color: #9aa9b8;

  font-size: 9px;

  line-height: 1.6;
}


/* ======================================================
   QUICK ACTIONS
====================================================== */

.quick-actions {
  display: flex;

  flex-direction: column;

  gap: 9px;

  margin-top: 15px;
}

.quick-action {
  width: 100%;

  display: flex;

  align-items: center;

  gap: 10px;

  padding: 10px;

  border:
    1px solid #e7edf2;

  border-radius: 7px;

  background: white;

  color: #243b53;

  text-align: left;

  cursor: pointer;

  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.quick-action:hover {
  background: #f8fafc;

  border-color: #d5b45c;

  transform:
    translateX(2px);
}

.quick-icon {
  width: 34px;
  height: 34px;

  flex-shrink: 0;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 6px;

  background: #eaf1f8;

  color: #174d7d;

  font-weight: 900;
}

.quick-text {
  min-width: 0;

  display: flex;

  flex-direction: column;

  flex: 1;
}

.quick-text strong {
  font-size: 10px;
}

.quick-text small {
  margin-top: 3px;

  color: #829ab1;

  font-size: 8px;
}

.quick-arrow {
  color: #9aa9b8;

  font-size: 13px;
}


/* ======================================================
   INFORMATION
====================================================== */

.information {
  display: flex;

  align-items: center;

  gap: 15px;

  margin-top: 20px;

  padding: 20px;

  border-radius: 10px;

  background: #08264d;

  color: white;
}

.information-icon {
  width: 45px;
  height: 45px;

  flex-shrink: 0;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: #d5b45c;

  color: #08264d;

  font-size: 12px;

  font-weight: 900;
}

.information-content h2 {
  margin: 0;

  font-size: 14px;
}

.information-content p {
  max-width: 800px;

  margin: 5px 0 0;

  color: #aebfd0;

  font-size: 9px;

  line-height: 1.7;
}


/* ======================================================
   TABLET
====================================================== */

@media (max-width: 1100px) {

  .dashboard-content {
    padding: 25px;
  }

  .statistics {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }

}


/* ======================================================
   MOBILE
====================================================== */

@media (max-width: 800px) {

  .dashboard-content {
    padding: 20px 16px;
  }

  .welcome-card {
    padding: 25px;

    min-height: auto;
  }

  .welcome-emblem {
    display: none;
  }

}


/* ======================================================
   SMALL MOBILE
====================================================== */

@media (max-width: 600px) {

  .dashboard-content {
    padding: 16px 13px;
  }

  .welcome-card {
    padding: 22px 20px;

    border-radius: 10px;
  }

  .welcome-label {
    font-size: 8px;
  }

  .welcome-card h2 {
    font-size: 23px;
  }

  .welcome-card p {
    font-size: 10px;
  }

  .welcome-meta {
    align-items: flex-start;

    flex-direction: column;
  }

  .statistics {
    grid-template-columns: 1fr;

    gap: 12px;
  }

  .stat-card {
    padding: 16px;
  }

  .stat-number {
    font-size: 25px;
  }

  .dashboard-grid {
    gap: 13px;
  }

  .panel {
    padding: 17px;

    min-height: auto;
  }

  .information {
    align-items: flex-start;

    padding: 17px;
  }

  .information-icon {
    width: 38px;
    height: 38px;
  }

}


/* ======================================================
   VERY SMALL MOBILE
====================================================== */

@media (max-width: 380px) {

  .welcome-card h2 {
    font-size: 20px;
  }

  .quick-text small {
    display: none;
  }

}

</style>
```
