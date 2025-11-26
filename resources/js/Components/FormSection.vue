<script setup>
import { computed, useSlots } from "vue";
import SectionTitle from "./SectionTitle.vue";

defineEmits(["submitted"]);

const hasActions = computed(() => !!useSlots().actions);
</script>

<template>
    <div class="row mb-4">
        <div class="col-md-4">
            <SectionTitle>
                <template #title>
                    <slot name="title" />
                </template>
                <template #description>
                    <slot name="description" />
                </template>
            </SectionTitle>
        </div>

        <div class="col-md-8">
            <form @submit.prevent="$emit('submitted')">
                <div class="card">
                    <div class="card-body">
                        <slot name="form" />
                    </div>

                    <div
                        v-if="hasActions"
                        class="card-footer text-end bg-light"
                    >
                        <slot name="actions" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
