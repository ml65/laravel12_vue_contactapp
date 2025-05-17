import { ref, computed } from 'vue';
import { translations } from '@/i18n/translations';

const currentLang = ref(localStorage.getItem('language') || 'ru');

export function useI18n() {
    const setLanguage = (lang) => {
        currentLang.value = lang;
        localStorage.setItem('language', lang);
    };

    const t = (key) => {
        return translations[currentLang.value][key] || key;
    };

    const currentLanguage = computed(() => currentLang.value);

    return {
        t,
        setLanguage,
        currentLanguage
    };
} 