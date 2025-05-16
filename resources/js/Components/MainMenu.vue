<template>
  <nav class="bg-gray-800 text-white px-4 py-2 flex items-center space-x-6 mb-6">
    <a href="/" class="hover:underline">Главная</a>
    <a href="/" class="hover:underline">Контакты</a>
    <a href="/information" class="hover:underline">О проекте</a>
    <template v-if="isAuthenticated">
      <form :action="logoutUrl" method="POST" class="ml-auto" @submit="clearAuth">
        <input type="hidden" name="_token" :value="csrf" />
        <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">Выйти</button>
      </form>
    </template>
    <template v-else>
      <a href="/login" class="ml-auto bg-green-500 hover:bg-green-600 px-3 py-1 rounded">Войти</a>
    </template>
  </nav>
</template>

<script setup>
import { computed, ref } from 'vue';

const isAuthenticated = computed(() => !!localStorage.getItem('api_token'));
const logoutUrl = '/logout';

// Получаем CSRF-токен из cookie
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
}
const csrf = ref(getCookie('XSRF-TOKEN'));

// Очищаем localStorage после выхода
function clearAuth() {
  localStorage.removeItem('api_token');
}
</script> 