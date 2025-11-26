<template>
    <div class="mb-3">
        <div>
            <h3 class="h5 fw-medium">
                <slot name="title" />
            </h3>

            <p class="mt-1 text-muted small">
                <slot name="description" />
            </p>
        </div>

        <div class="mt-2">
            <slot name="aside" />
        </div>
    </div>
</template>
