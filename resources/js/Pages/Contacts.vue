<template>
  <div>
    <MainMenu />
    <div class="container mx-auto p-4">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Contacts</h1>
        <button @click="openCreateModal" class="bg-green-500 text-white px-4 py-2 rounded">
          Создать
        </button>
      </div>

      <!-- Модальное окно -->
      <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">{{ editing ? 'Edit Contact' : 'Create Contact' }}</h2>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
              <span class="text-2xl">&times;</span>
            </button>
          </div>
          <form @submit.prevent="saveContact">
            <div class="mb-2">
              <label class="block">Name</label>
              <input v-model="form.name" class="border p-2 w-full" required />
              <div v-if="errors.name" class="text-red-500">{{ errors.name[0] }}</div>
            </div>
            <div class="mb-2">
              <label class="block">Email</label>
              <input v-model="form.email" class="border p-2 w-full" type="email" required />
              <div v-if="errors.email" class="text-red-500">{{ errors.email[0] }}</div>
            </div>
            <div class="mb-2">
              <label class="block">Phone</label>
              <input v-model="form.phone" class="border p-2 w-full" />
            </div>
            <div class="mb-2">
              <label class="block">Tags</label>
              <select v-model="form.tags" multiple class="border p-2 w-full">
                <option v-for="tag in availableTags" :key="tag.id" :value="tag.id">
                  {{ tag.name }}
                </option>
              </select>
            </div>
            <div class="flex justify-end space-x-2">
              <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                {{ editing ? 'Update' : 'Create' }}
              </button>
              <button type="button" @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Список контактов -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2">Name</th>
            <th class="p-2">Email</th>
            <th class="p-2">Phone</th>
            <th class="p-2">Tags</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="contact in contacts" :key="contact.id">
            <td class="p-2">{{ contact.name }}</td>
            <td class="p-2">{{ contact.email }}</td>
            <td class="p-2">{{ contact.phone }}</td>
            <td class="p-2">
              <span v-for="tag in contact.tags" :key="tag.id" class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2">
                {{ tag.name }}
              </span>
            </td>
            <td class="p-2">
              <button @click="editContact(contact)" class="text-blue-500 mr-2">Edit</button>
              <button @click="deleteContact(contact)" class="text-red-500">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import MainMenu from '@/Components/MainMenu.vue';

export default {
  props: {
    contacts: Array,
    tags: Array,
  },
  components: { MainMenu },
  setup(props) {
    const form = ref({
      id: null,
      name: '',
      email: '',
      phone: '',
      tags: [],
    });
    const editing = ref(false);
    const errors = ref({});
    const showModal = ref(false);
    const availableTags = computed(() => props.tags || []);

    const isAuthenticated = computed(() => !!localStorage.getItem('api_token'));

    // Редирект если не авторизован
    onMounted(() => {
      if (!localStorage.getItem('api_token')) {
        window.location.href = '/login';
      }
    });

    const openCreateModal = () => {
      editing.value = false;
      form.value = { id: null, name: '', email: '', phone: '', tags: [] };
      showModal.value = true;
    };

    const closeModal = () => {
      showModal.value = false;
      editing.value = false;
      form.value = { id: null, name: '', email: '', phone: '', tags: [] };
      errors.value = {};
    };

    const saveContact = async () => {
      try {
        const method = editing.value ? 'put' : 'post';
        const url = editing.value ? `/api/contacts/${form.value.id}` : '/api/contacts';
        const token = localStorage.getItem('api_token');
        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
          body: JSON.stringify(form.value),
        });
        if (!response.ok) {
          const data = await response.json();
          errors.value = data.errors || {};
          return;
        }
        errors.value = {};
        closeModal();
        Inertia.reload();
      } catch (error) {
        console.error(error);
      }
    };

    const editContact = (contact) => {
      // Преобразуем теги в массив ID для корректной работы с select
      const tagIds = contact.tags.map(tag => tag.id);
      form.value = {
        ...contact,
        tags: tagIds
      };
      editing.value = true;
      showModal.value = true;
    };

    const deleteContact = async (contact) => {
      if (confirm('Are you sure?')) {
        const token = localStorage.getItem('api_token');
        await fetch(`/api/contacts/${contact.id}`, {
          method: 'delete',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });
        Inertia.reload();
      }
    };

    return { 
      form, 
      editing, 
      errors, 
      showModal,
      saveContact, 
      editContact, 
      deleteContact, 
      openCreateModal,
      closeModal,
      availableTags 
    };
  },
};
</script>
  