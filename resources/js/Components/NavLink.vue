<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() => {
    return props.active 
        ? "nav-link active text-primary fw-bold border-bottom border-primary border-2" 
        : "nav-link text-secondary";
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
