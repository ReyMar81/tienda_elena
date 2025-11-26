import { ref } from 'vue';
import { debounce } from 'lodash';

export function useSearch() {
    const searchQuery = ref(localStorage.getItem('lastSearch') || '');
    const searchResults = ref({ productos: [], promociones: [] });
    const isSearching = ref(false);
    const showResults = ref(false);

    const performSearch = debounce(async (query) => {
        if (!query || query.length < 2) {
            searchResults.value = { productos: [], promociones: [] };
            showResults.value = false;
            return;
        }

        isSearching.value = true;
        
        try {
            const response = await fetch(route('api.search') + '?q=' + encodeURIComponent(query), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                credentials: 'include',
            });

            if (response.ok) {
                const data = await response.json();
                searchResults.value = data;
                showResults.value = true;
            }
        } catch (error) {
            console.error('Error en búsqueda:', error);
            searchResults.value = { productos: [], promociones: [] };
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
        searchResults.value = { productos: [], promociones: [] };
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
