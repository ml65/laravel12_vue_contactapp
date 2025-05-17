<template>
  <nav class="bg-gray-800 text-white px-4 py-2 flex items-center space-x-6 mb-6">
    <a href="/" class="hover:underline">{{ t('contacts') }}</a>
    <a href="/information" class="hover:underline">{{ t('about') }}</a>
    <div class="ml-auto flex items-center space-x-4">
      <template v-if="isAuthenticated">
        <button type="submit" @click="logout" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">{{ t('logout') }}</button>
      </template>
      <template v-else>
        <a href="/login" class="bg-green-500 hover:bg-green-600 px-3 py-1 rounded">{{ t('login') }}</a>
      </template>
      <div class="flex space-x-2 ml-4">
        <button 
          @click="setLanguage('en')" 
          :class="['px-2 py-1 rounded', currentLanguage === 'en' ? 'bg-blue-500' : 'bg-gray-600']"
        >
          EN
        </button>
        <button 
          @click="setLanguage('ru')" 
          :class="['px-2 py-1 rounded', currentLanguage === 'ru' ? 'bg-blue-500' : 'bg-gray-600']"
        >
          RU
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { useI18n } from '@/composables/useI18n';

const { t, setLanguage, currentLanguage } = useI18n();
const isAuthenticated = computed(() => !!localStorage.getItem('api_token'));

const logout = async () => {
  try {
    await Inertia.post('/logout');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};
</script> 