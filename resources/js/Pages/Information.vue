<template>
  <div>
    <MainMenu />
    <div class="container mx-auto p-8">
      <h1 class="text-3xl font-bold mb-4">О проекте</h1>
      <div class="bg-white rounded shadow p-6">
        <p class="text-lg mb-2">
          <b>Контакт-менеджер</b> — это современное веб-приложение для хранения и управления вашими контактами.
        </p>
        <ul class="list-disc pl-6 mb-4">
          <li>Добавление, редактирование и удаление контактов</li>
          <li>Аутентификация по токену (Bearer)</li>
          <li>Защита API через Laravel Sanctum</li>
          <li>Интуитивно понятный интерфейс на Vue 3</li>
          <li>Безопасное хранение данных</li>
        </ul>
        <p>
          Проект создан для демонстрации современных подходов к разработке SPA-приложений на Laravel и Vue.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import MainMenu from '@/Components/MainMenu.vue';
</script> 