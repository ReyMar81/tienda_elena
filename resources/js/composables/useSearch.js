import { ref } from 'vue';
import { debounce } from 'lodash';

export function useSearch() {
    const searchQuery = ref(localStorage.getItem('lastSearch') || '');
    const searchResults = ref({ productos: [], promociones: [], menus: [], count: 0 });
    const isSearching = ref(false);
    const showResults = ref(false);

    const performSearch = debounce(async (query) => {
        console.log('🔍 Buscando:', query);
        
        if (!query || query.length < 2) {
            searchResults.value = { productos: [], promociones: [], menus: [], count: 0 };
            showResults.value = false;
            return;
        }

        isSearching.value = true;
        
        try {
            // Usar la ruta correcta del backend
            const url = `/api/search/all?q=${encodeURIComponent(query)}`;
            console.log('📡 URL:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
            });

            console.log('📥 Response status:', response.status);

            if (response.ok) {
                const data = await response.json();
                console.log('✅ Data recibida:', data);
                searchResults.value = data;
                showResults.value = true;
            } else {
                console.error('❌ Error en respuesta:', response.status);
                const errorText = await response.text();
                console.error('Error details:', errorText);
                searchResults.value = { productos: [], promociones: [], menus: [], count: 0 };
            }
        } catch (error) {
            console.error('❌ Error en búsqueda:', error);
            searchResults.value = { productos: [], promociones: [], menus: [], count: 0 };
        } finally {
            isSearching.value = false;
        }
    }, 400);

    const search = (query) => {
        searchQuery.value = query;
        localStorage.setItem('lastSearch', query);
        performSearch(query);
    };

    const clearSearch = () => {
        searchQuery.value = '';
        searchResults.value = { productos: [], promociones: [], menus: [], count: 0 };
        showResults.value = false;
        localStorage.removeItem('lastSearch');
    };

    const closeResults = () => {
        showResults.value = false;
    };

    return {
        searchQuery,
        searchResults,
        isSearching,
        showResults,
        search,
        clearSearch,
        closeResults,
    };
}
