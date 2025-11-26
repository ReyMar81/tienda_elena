<script setup>
defineProps({
    type: {
        type: String,
        default: "button",
    },
});
</script>

<template>
    <button :type="type" class="btn btn-danger text-uppercase fw-semibold">
        <slot />
    </button>
</template>
