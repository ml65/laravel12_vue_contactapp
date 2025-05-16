<template>
  <div>
    <MainMenu />
    <div class="container mx-auto p-4">
      <h1 class="text-2xl font-bold mb-4">Contacts</h1>
      <!-- Форма создания/редактирования контакта -->
      <div class="mb-4 p-4 border rounded">
        <h2 class="text-lg font-semibold">{{ editing ? 'Edit Contact' : 'Create Contact' }}</h2>
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
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
            {{ editing ? 'Update' : 'Create' }}
          </button>
          <button v-if="editing" @click="cancelEdit" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded">
            Cancel
          </button>
        </form>
      </div>
      <!-- Список контактов -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2">Name</th>
            <th class="p-2">Email</th>
            <th class="p-2">Phone</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="contact in contacts" :key="contact.id">
            <td class="p-2">{{ contact.name }}</td>
            <td class="p-2">{{ contact.email }}</td>
            <td class="p-2">{{ contact.phone }}</td>
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
  },
  components: { MainMenu },
  setup(props) {
    const form = ref({
      id: null,
      name: '',
      email: '',
      phone: '',
    });
    const editing = ref(false);
    const errors = ref({});

    const isAuthenticated = computed(() => !!localStorage.getItem('api_token'));

    // Редирект если не авторизован
    onMounted(() => {
      if (!localStorage.getItem('api_token')) {
        window.location.href = '/login';
      }
    });

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
        form.value = { id: null, name: '', email: '', phone: '' };
        editing.value = false;
        Inertia.reload();
      } catch (error) {
        console.error(error);
      }
    };

    const editContact = (contact) => {
      form.value = { ...contact };
      editing.value = true;
    };

    const cancelEdit = () => {
      form.value = { id: null, name: '', email: '', phone: '' };
      editing.value = false;
      errors.value = {};
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

    // Логика выхода
    const logout = async () => {
      const token = localStorage.getItem('api_token');
      await fetch('/logout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
      });
      localStorage.removeItem('api_token');
      window.location.href = '/information';
    };

    return { form, editing, errors, saveContact, editContact, cancelEdit, deleteContact, logout, isAuthenticated };
  },
};
</script>
  