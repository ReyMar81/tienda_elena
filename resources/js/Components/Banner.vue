<script setup>
import { ref, watchEffect } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const show = ref(true);
const style = ref("success");
const message = ref("");

watchEffect(async () => {
    style.value = page.props.jetstream.flash?.bannerStyle || "success";
    message.value = page.props.jetstream.flash?.banner || "";
    show.value = true;
});
</script>

<template>
    <div>
        <div
            v-if="show && message"
            class="alert alert-dismissible fade show mb-0 rounded-0"
            :class="{
                'alert-success': style == 'success',
                'alert-danger': style == 'danger',
            }"
            role="alert"
        >
            <div class="container">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="me-2">
                            <svg
                                v-if="style == 'success'"
                                class="bi"
                                style="width: 1.25rem; height: 1.25rem"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>

                            <svg
                                v-if="style == 'danger'"
                                class="bi"
                                style="width: 1.25rem; height: 1.25rem"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                                />
                            </svg>
                        </span>

                        <span class="fw-medium small">{{ message }}</span>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        @click.prevent="show = false"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</template>
