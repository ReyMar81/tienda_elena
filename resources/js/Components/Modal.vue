<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: "2xl",
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["close"]);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = "hidden";
            showSlot.value = true;
            dialog.value?.showModal();
        } else {
            document.body.style.overflow = null;
            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    }
);

const close = () => {
    if (props.closeable) {
        emit("close");
    }
};

const closeOnEscape = (e) => {
    if (e.key === "Escape" && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener("keydown", closeOnEscape));

onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        sm: "modal-sm",
        md: "",
        lg: "modal-lg",
        xl: "modal-xl",
        "2xl": "modal-xl",
    }[props.maxWidth];
});
</script>

<template>
    <dialog
        class="modal fade"
        :class="{ 'show d-block': show }"
        ref="dialog"
        tabindex="-1"
        style="
            background: transparent;
            border: none;
            padding: 0;
            max-width: none;
            max-height: none;
        "
    >
        <div
            class="modal-dialog modal-dialog-centered modal-dialog-scrollable"
            :class="maxWidthClass"
        >
            <div class="modal-content" v-if="showSlot">
                <slot />
            </div>
        </div>
    </dialog>
    <div
        class="modal-backdrop fade"
        :class="{ show: show }"
        v-if="show"
        @click="close"
    ></div>
</template>
