import { ref, computed } from 'vue'
import fr from '../locales/fr'
import en from '../locales/en'

const locale = ref('fr') // Français par défaut

const translations = {
    fr,
    en
}

export function useI18n() {
    const t = computed(() => {
        return (key) => {
            const keys = key.split('.')
            let value = translations[locale.value]

            for (const k of keys) {
                if (value && typeof value === 'object') {
                    value = value[k]
                } else {
                    return key
                }
            }

            return value || key
        }
    })

    const setLocale = (lang) => {
        if (translations[lang]) {
            locale.value = lang
            localStorage.setItem('locale', lang)
        }
    }

    const currentLocale = computed(() => locale.value)

    // Charger la langue sauvegardée au démarrage
    const savedLocale = localStorage.getItem('locale')
    if (savedLocale && translations[savedLocale]) {
        locale.value = savedLocale
    }

    return {
        t: t.value,
        setLocale,
        locale: currentLocale
    }
}
