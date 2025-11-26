import { ref } from 'vue';

export function useDashboard() {
    const kpis = ref(null);
    const graficos = ref(null);
    const indicadores = ref(null);
    const loading = ref(false);
    const periodo = ref('mes');

    const fetchDashboard = async () => {
        loading.value = true;
        try {
            // Los datos ya vienen con Inertia en las props
            // Este composable es para futuras actualizaciones dinámicas
        } catch (error) {
            console.error('Error cargando dashboard:', error);
        } finally {
            loading.value = false;
        }
    };

    const setPeriodo = (nuevoPeriodo) => {
        periodo.value = nuevoPeriodo;
        // Aquí se podría hacer refetch con el nuevo período
    };

    return {
        kpis,
        graficos,
        indicadores,
        loading,
        periodo,
        fetchDashboard,
        setPeriodo,
    };
}
