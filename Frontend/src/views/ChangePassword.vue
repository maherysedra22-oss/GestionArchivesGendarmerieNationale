```vue
```vue
<template>
  <div class="change-password">
    <h1>Changer le mot de passe</h1>

    <form @submit.prevent="changePassword">
      <div>
        <label>Mot de passe actuel</label>
        <input
          v-model="form.currentPassword"
          type="password"
          required
        />
      </div>

      <div>
        <label>Nouveau mot de passe</label>
        <input
          v-model="form.newPassword"
          type="password"
          required
        />
      </div>

      <div>
        <label>Confirmer le nouveau mot de passe</label>
        <input
          v-model="form.confirmPassword"
          type="password"
          required
        />
      </div>

      <p v-if="errorMessage">
        {{ errorMessage }}
      </p>

      <p v-if="successMessage">
        {{ successMessage }}
      </p>

      <button type="submit">
        Changer le mot de passe
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

const form = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const errorMessage = ref('')
const successMessage = ref('')

const changePassword = () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (form.newPassword !== form.confirmPassword) {
    errorMessage.value =
      'Les mots de passe ne correspondent pas.'
    return
  }

  if (form.newPassword.length < 8) {
    errorMessage.value =
      'Le mot de passe doit contenir au moins 8 caractères.'
    return
  }

  successMessage.value =
    'Mot de passe modifié avec succès.'
}
</script>
```


<style scoped>
.change-password-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f4f6f8;
}

.change-password-card {
  width: 420px;
  padding: 30px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

h1 {
  margin-bottom: 25px;
  text-align: center;
}

.form-group {
  margin-bottom: 18px;
}

label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
}

input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
}

button {
  width: 100%;
  padding: 11px;
  border: none;
  border-radius: 6px;
  background: #1f4e79;
  color: white;
  font-size: 15px;
  cursor: pointer;
}

button:hover {
  background: #163a5c;
}

.error {
  margin-bottom: 15px;
  color: #dc2626;
}

.success {
  margin-bottom: 15px;
  color: #16a34a;
}
</style>
```
