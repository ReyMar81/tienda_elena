<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
    align: {
        type: String,
        default: "right",
    },
    width: {
        type: String,
        default: "48",
    },
    contentClasses: {
        type: Array,
        default: () => ["py-1", "bg-white"],
    },
});

let open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === "Escape") {
        open.value = false;
    }
};

onMounted(() => document.addEventListener("keydown", closeOnEscape));
onUnmounted(() => document.removeEventListener("keydown", closeOnEscape));

const widthClass = computed(() => {
    return {
        48: "dropdown-menu-end",
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === "left") {
        return "dropdown-menu-start";
    }

    if (props.align === "right") {
        return "dropdown-menu-end";
    }

    return "";
});
</script>

<template>
    <div class="dropdown">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <ul
            class="dropdown-menu"
            :class="[alignmentClasses, { show: open }]"
            @click="open = false"
        >
            <slot name="content" />
        </ul>
    </div>
</template>
