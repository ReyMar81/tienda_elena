import { ref, watch, onMounted } from 'vue';

export function useTheme() {
    const theme = ref(localStorage.getItem('theme') || 'adultos');
    const mode = ref(localStorage.getItem('mode') || 'auto');
    const fontSize = ref(localStorage.getItem('fontSize') || 'normal');
    const contrast = ref(localStorage.getItem('contrast') || 'normal');

    const detectAutoMode = () => {
        const hour = new Date().getHours();
        return (hour >= 6 && hour < 18) ? 'dia' : 'noche';
    };

    const applyTheme = () => {
        const root = document.documentElement;
        
        // Aplicar tema
        root.setAttribute('data-theme', theme.value);
        
        // Aplicar modo (día/noche)
        const currentMode = mode.value === 'auto' ? detectAutoMode() : mode.value;
        root.setAttribute('data-mode', currentMode);
        
        // Aplicar tamaño de fuente
        root.setAttribute('data-font-size', fontSize.value);
        
        // Aplicar contraste
        root.setAttribute('data-contrast', contrast.value);
    };

    const setTheme = (newTheme) => {
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);
        applyTheme();
    };

    const setMode = (newMode) => {
        mode.value = newMode;
        localStorage.setItem('mode', newMode);
        applyTheme();
    };

    const setFontSize = (size) => {
        fontSize.value = size;
        localStorage.setItem('fontSize', size);
        applyTheme();
    };

    const setContrast = (level) => {
        contrast.value = level;
        localStorage.setItem('contrast', level);
        applyTheme();
    };

    // Auto-actualizar cada minuto si está en modo auto
    let autoModeInterval;
    
    watch(mode, (newMode) => {
        if (newMode === 'auto') {
            autoModeInterval = setInterval(() => {
                applyTheme();
            }, 60000); // Revisar cada minuto
        } else if (autoModeInterval) {
            clearInterval(autoModeInterval);
        }
    });

    onMounted(() => {
        applyTheme();
        
        if (mode.value === 'auto') {
            autoModeInterval = setInterval(() => {
                applyTheme();
            }, 60000);
        }
    });

    return {
        theme,
        mode,
        fontSize,
        contrast,
        setTheme,
        setMode,
        setFontSize,
        setContrast,
    };
}
