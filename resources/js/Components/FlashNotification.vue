<script setup>
import { ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const show = ref(false);
const message = ref("");
const type = ref("success");

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            message.value = flash.success;
            type.value = "success";
            show.value = true;
            autoHide();
        } else if (flash?.error) {
            message.value = flash.error;
            type.value = "danger";
            show.value = true;
            autoHide();
        }
    },
    { immediate: true, deep: true }
);

const autoHide = () => {
    setTimeout(() => {
        show.value = false;
    }, 5000);
};

const close = () => {
    show.value = false;
};
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            :class="`alert alert-${type} alert-dismissible fade show position-fixed`"
            style="
                top: 80px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                max-width: 500px;
            "
            role="alert"
        >
            <i
                class="bi"
                :class="
                    type === 'success'
                        ? 'bi-check-circle-fill'
                        : 'bi-exclamation-triangle-fill'
                "
            ></i>
            {{ message }}
            <button
                type="button"
                class="btn-close"
                @click="close"
                aria-label="Close"
            ></button>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateX(50px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateX(50px);
}
</style>
