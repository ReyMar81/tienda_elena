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
const root = ref(null);

const closeOnEscape = (e) => {
    if (open.value && e.key === "Escape") {
        open.value = false;
    }
};

const closeOnClickOutside = (e) => {
    if (open.value && root.value && !root.value.contains(e.target)) {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener("keydown", closeOnEscape);
    document.addEventListener("click", closeOnClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.removeEventListener("click", closeOnClickOutside);
});

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
    <div class="dropdown position-static" ref="root">
        <div @click="open = !open" class="d-inline-block">
            <slot name="trigger" />
        </div>

        <ul
            class="dropdown-menu"
            :class="[alignmentClasses, { show: open }]"
            @click="open = false"
            style="z-index: 1050;"
        >
            <slot name="content" />
        </ul>
    </div>
</template>
