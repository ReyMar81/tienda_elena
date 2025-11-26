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
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0">
                    <div
                        class="rounded-circle bg-danger bg-opacity-10 p-3 d-flex align-items-center justify-content-center"
                        style="width: 3rem; height: 3rem"
                    >
                        <svg
                            class="text-danger"
                            style="width: 1.5rem; height: 1.5rem"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                            />
                        </svg>
                    </div>
                </div>

                <div class="ms-3">
                    <slot name="content" />
                </div>
            </div>
        </div>

        <div class="modal-footer bg-light">
            <slot name="footer" />
        </div>
    </Modal>
</template>
