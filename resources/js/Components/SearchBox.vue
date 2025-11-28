<template>
    <div class="search-box position-relative w-100">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search"></i>
            </span>
            <input
                type="search"
                class="form-control border-start-0"
                :placeholder="'Buscar productos, promociones o menús...'"
                v-model="searchQueryLocal"
                @input="onInput"
                @keydown.esc="clearAndClose"
                @focus="onFocus"
                aria-label="Búsqueda global"
            />
            <button
                v-if="isSearching"
                class="btn btn-outline-secondary"
                type="button"
                disabled
            >
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </button>
        </div>

        <!-- Renderizar resultados (componente separado) -->
        <SearchResults
            v-if="showResults"
            :productos="searchResults.productos || []"
            :promociones="searchResults.promociones || []"
            :menus="searchResults.menus || []"
            @close="closeResults"
        />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import SearchResults from '@/Components/SearchResults.vue';
import { useSearch } from '@/composables/useSearch';

const {
    searchQuery,
    searchResults,
    isSearching,
    showResults,
    search,
    clearSearch,
    closeResults,
} = useSearch();

// Local v-model proxy so we can debounce via composable
const searchQueryLocal = ref(searchQuery.value || '');

const onInput = (e) => {
    const q = e.target.value;
    search(q);
};

const onFocus = () => {
    if ((searchResults.value && (searchResults.value.productos?.length || searchResults.value.promociones?.length || searchResults.value.menus?.length))) {
        showResults.value = true;
    }
};

const clearAndClose = () => {
    clearSearch();
    closeResults();
};

// keep local synced if lastSearch exists
watch(searchQuery, (val) => {
    searchQueryLocal.value = val;
});
</script>

<style scoped>
.search-box {
    max-width: 400px;
}

/* Ensure the results card overlays correctly */
:deep(.search-results) {
    width: 100%;
}

@media (max-width: 575.98px) {
    .search-box {
        max-width: 100%;
    }
}
</style>
