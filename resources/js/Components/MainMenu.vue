<template>
  <nav class="bg-gray-800 text-white px-4 py-2 flex items-center space-x-6 mb-6">
    <a href="/" class="hover:underline">Главная</a>
    <a href="/" class="hover:underline">Контакты</a>
    <a href="/information" class="hover:underline">О проекте</a>
    <template v-if="isAuthenticated">
        <button type="submit" @click="logout" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">Выйти</button>
    </template>
    <template v-else>
      <a href="/login" class="ml-auto bg-green-500 hover:bg-green-600 px-3 py-1 rounded">Войти</a>
    </template>
  </nav>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const isAuthenticated = computed(() => !!localStorage.getItem('api_token'));

const logout = async () => {
  try {
    await Inertia.post('/logout');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};
</script> 