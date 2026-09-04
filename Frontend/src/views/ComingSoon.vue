
<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
  <div class="coming">
    <div class="card">
      <h1>Module en cours de développement</h1>

      <p>
        Cette fonctionnalité sera disponible prochainement.
      </p>

      <button @click="router.push('/dashboard')">
        Retour au Dashboard
      </button>
    </div>
  </div>
</template>

<style scoped>
.coming {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f7fb;
  padding: 20px;
}

.card {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 16px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

h1 {
  margin-bottom: 15px;
}

p {
  color: #666;
  margin-bottom: 25px;
}

button {
  border: none;
  padding: 12px 22px;
  border-radius: 8px;
  cursor: pointer;
  background: #1f4b99;
  color: white;
}
</style>