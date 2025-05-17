<template>
  <div>
    <MainMenu />
    <div class="container mx-auto p-4">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">{{ t('contacts') }}</h1>
        <button @click="openCreateModal" class="bg-green-500 text-white px-4 py-2 rounded">
          {{ t('create') }}
        </button>
      </div>

      <!-- Модальное окно -->
      <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">{{ editing ? t('edit') : t('create') }}</h2>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
              <span class="text-2xl">&times;</span>
            </button>
          </div>

          <!-- Область сообщений -->
          <div v-if="notification.message" 
               :class="['p-3 mb-4 rounded', 
                       notification.type === 'error' ? 'bg-red-100 text-red-700' : 
                       notification.type === 'success' ? 'bg-green-100 text-green-700' : 
                       'bg-blue-100 text-blue-700']">
            {{ notification.message }}
          </div>

          <form @submit.prevent="saveContact">
            <div class="mb-2">
              <label class="block">{{ t('name') }}</label>
              <input v-model="form.name" class="border p-2 w-full" required />
              <div v-if="errors.name" class="text-red-500">{{ errors.name[0] }}</div>
            </div>
            <div class="mb-2">
              <label class="block">{{ t('email') }}</label>
              <input v-model="form.email" class="border p-2 w-full" type="email" required />
              <div v-if="errors.email" class="text-red-500">{{ errors.email[0] }}</div>
            </div>
            <div class="mb-2">
              <label class="block">{{ t('phone') }}</label>
              <input 
                v-model="form.phone" 
                @input="formatPhone"
                placeholder="+7 (___) ___-__-__"
                class="border p-2 w-full" 
              />
              <div v-if="errors.phone" class="text-red-500">{{ errors.phone[0] }}</div>
            </div>
            <div class="mb-2">
              <label class="block">{{ t('tags') }}</label>
              <select v-model="form.tags" multiple class="border p-2 w-full">
                <option v-for="tag in availableTags" :key="tag.id" :value="tag.id">
                  {{ tag.name }}
                </option>
              </select>
            </div>
            <div class="flex justify-end space-x-2">
              <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                {{ editing ? t('update') : t('create') }}
              </button>
              <button type="button" @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded">
                {{ t('cancel') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Список контактов -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2">
              <div class="flex items-center justify-between cursor-pointer" @click="sortBy('name')">
                <div class="mb-2">{{ t('name') }}</div>
                <span v-if="sortKey === 'name'" class="ml-2">
                  {{ sortDirection === 'asc' ? '↑' : '↓' }}
                </span>
              </div>
              <input 
                v-model="filters.name" 
                @input="filterContacts"
                :placeholder="t('filterByName')"
                class="w-full p-1 text-sm border rounded"
              />
            </th>
            <th class="p-2">
              <div class="flex items-center justify-between cursor-pointer" @click="sortBy('email')">
                <div class="mb-2">{{ t('email') }}</div>
                <span v-if="sortKey === 'email'" class="ml-2">
                  {{ sortDirection === 'asc' ? '↑' : '↓' }}
                </span>
              </div>
              <input 
                v-model="filters.email" 
                @input="filterContacts"
                :placeholder="t('filterByEmail')"
                class="w-full p-1 text-sm border rounded"
              />
            </th>
            <th class="p-2">
              <div class="flex items-center justify-between cursor-pointer" @click="sortBy('phone')">
                <div class="mb-2">{{ t('phone') }}</div>
                <span v-if="sortKey === 'phone'" class="ml-2">
                  {{ sortDirection === 'asc' ? '↑' : '↓' }}
                </span>
              </div>
              <input 
                v-model="filters.phone" 
                @input="filterContacts"
                :placeholder="t('filterByPhone')"
                class="w-full p-1 text-sm border rounded"
              />
            </th>
            <th class="p-2">
              <div class="mb-2">{{ t('tags') }}</div>
              <select 
                v-model="filters.tag" 
                @change="filterContacts"
                class="w-full p-1 text-sm border rounded"
              >
                <option value="">{{ t('allTags') }}</option>
                <option v-for="tag in availableTags" :key="tag.id" :value="tag.id">
                  {{ tag.name }}
                </option>
              </select>
            </th>
            <th class="p-2">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="contact in sortedAndFilteredContacts" :key="contact.id">
            <td class="p-2">{{ contact.name }}</td>
            <td class="p-2">{{ contact.email }}</td>
            <td class="p-2">{{ contact.phone }}</td>
            <td class="p-2">
              <span v-for="tag in contact.tags" :key="tag.id" class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2">
                {{ tag.name }}
              </span>
            </td>
            <td class="p-2">
              <button @click="editContact(contact)" class="text-blue-500 mr-2">{{ t('edit') }}</button>
              <button @click="deleteContact(contact)" class="text-red-500">{{ t('delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import MainMenu from '@/Components/MainMenu.vue';
import { parsePhoneNumber, isValidPhoneNumber, AsYouType } from 'libphonenumber-js';
import { useI18n } from '@/composables/useI18n';

export default {
  props: {
    contacts: Array,
    tags: Array,
  },
  components: { MainMenu },
  setup(props) {
    const { t } = useI18n();
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
    const phoneFormatter = new AsYouType('RU');

    // Состояние для сортировки
    const sortKey = ref('');
    const sortDirection = ref('asc');

    // Добавляем состояние для фильтров
    const filters = ref({
      name: '',
      email: '',
      phone: '',
      tag: '',
    });

    // Добавляем состояние для уведомлений
    const notification = ref({
      message: '',
      type: 'info' // 'info', 'success', 'error'
    });

    // Метод сортировки
    const sortBy = (key) => {
      if (sortKey.value === key) {
        // Если уже сортируем по этому полю, меняем направление
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
      } else {
        // Если сортируем по новому полю, устанавливаем направление по умолчанию
        sortKey.value = key;
        sortDirection.value = 'asc';
      }
    };

    // Фильтрация контактов
    const filteredContacts = computed(() => {
      return props.contacts.filter(contact => {
        const nameMatch = contact.name.toLowerCase().includes(filters.value.name.toLowerCase());
        const emailMatch = contact.email.toLowerCase().includes(filters.value.email.toLowerCase());
        const phoneMatch = contact.phone?.toLowerCase().includes(filters.value.phone.toLowerCase()) ?? true;
        
        let tagMatch = true;
        if (filters.value.tag) {
          tagMatch = contact.tags.some(tag => tag.id === parseInt(filters.value.tag));
        }

        return nameMatch && emailMatch && phoneMatch && tagMatch;
      });
    });

    // Сортировка и фильтрация контактов
    const sortedAndFilteredContacts = computed(() => {
      const contacts = [...filteredContacts.value];
      
      if (!sortKey.value) return contacts;

      return contacts.sort((a, b) => {
        let aValue = a[sortKey.value] || '';
        let bValue = b[sortKey.value] || '';

        if (typeof aValue === 'string') {
          aValue = aValue.toLowerCase();
          bValue = bValue.toLowerCase();
        }

        if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
      });
    });

    // Метод для сброса фильтров
    const resetFilters = () => {
      filters.value = {
        name: '',
        email: '',
        phone: '',
        tag: '',
      };
    };

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
      notification.value = { message: '', type: 'info' };
    };

    // Форматирование телефона при вводе
    const formatPhone = (event) => {
      const input = event.target;
      const cursorPosition = input.selectionStart;
      const oldValue = form.value.phone;
      const oldLength = oldValue.length;
      
      // Форматируем номер
      const formattedNumber = phoneFormatter.input(input.value);
      form.value.phone = formattedNumber;

      // Восстанавливаем позицию курсора
      this.$nextTick(() => {
        const newLength = formattedNumber.length;
        const newPosition = cursorPosition + (newLength - oldLength);
        input.setSelectionRange(newPosition, newPosition);
      });
    };

    // Валидация формы
    const validateForm = () => {
      const errors = {};
      
      // Проверка email на уникальность
      const emailExists = props.contacts.some(contact => 
        contact.email.toLowerCase() === form.value.email.toLowerCase() && 
        (!editing.value || contact.id !== form.value.id)
      );
      
      if (emailExists) {
        errors.email = ['Этот email уже используется'];
      }

      // Проверка телефона
      if (form.value.phone) {
        try {
          const phoneNumber = parsePhoneNumber(form.value.phone, 'RU');
          
          if (!isValidPhoneNumber(form.value.phone, 'RU')) {
            errors.phone = ['Неверный формат номера телефона'];
          } else if (!phoneNumber.isValid()) {
            errors.phone = ['Номер телефона недействителен'];
          }
        } catch (error) {
          errors.phone = ['Неверный формат номера телефона'];
        }
      }

      return errors;
    };

    const saveContact = async () => {
      try {
        errors.value = {};
        notification.value = { message: '', type: 'info' };

        const validationErrors = validateForm();
        if (Object.keys(validationErrors).length > 0) {
          errors.value = validationErrors;
          notification.value = {
            message: t('fixErrors'),
            type: 'error'
          };
          return;
        }

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

        const data = await response.json();

        if (!response.ok) {
          errors.value = data.errors || {};
          notification.value = {
            message: t('errorSaving'),
            type: 'error'
          };
          return;
        }

        notification.value = {
          message: editing.value ? t('contactUpdated') : t('contactCreated'),
          type: 'success'
        };

        setTimeout(() => {
          closeModal();
          Inertia.reload();
        }, 1500);

      } catch (error) {
        console.error(error);
        notification.value = {
          message: t('errorSaving'),
          type: 'error'
        };
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
      if (confirm(t('confirmDelete'))) {
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
      availableTags,
      filters,
      filteredContacts,
      sortedAndFilteredContacts,
      resetFilters,
      sortBy,
      sortKey,
      sortDirection,
      notification,
      formatPhone,
      t
    };
  },
};
</script>
  