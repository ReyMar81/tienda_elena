<script setup>
import Modal from "./Modal.vue";

const emit = defineEmits(["close"]);

defineProps({
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

const close = () => {
    emit("close");
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="modal-header">
            <h5 class="modal-title">
                <slot name="title" />
            </h5>
            <button
                type="button"
                class="btn-close"
                @click="close"
                aria-label="Close"
            ></button>
        </div>

        <div class="modal-body">
            <slot name="content" />
        </div>

        <div class="modal-footer bg-light">
            <slot name="footer" />
        </div>
    </Modal>
</template>
