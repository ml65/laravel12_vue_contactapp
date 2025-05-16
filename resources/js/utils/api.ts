import axios from 'axios';

// Создаем экземпляр axios с базовым URL
const api = axios.create({
    baseURL: '/api',
});

// Добавляем интерсептор для автоматического добавления токена к запросам
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('api_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Добавляем интерсептор для обработки ошибок аутентификации
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Если токен истек или недействителен, удаляем его и перенаправляем на страницу входа
            localStorage.removeItem('api_token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default api; 