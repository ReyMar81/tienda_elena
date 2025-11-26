<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-danger small mt-1">
            {{ message }}
        </p>
    </div>
</template>
