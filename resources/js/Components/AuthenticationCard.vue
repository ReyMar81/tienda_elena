<template>
    <div
        class="min-vh-100 d-flex flex-column justify-content-center align-items-center bg-light py-5"
    >
        <div class="mb-3">
            <slot name="logo" />
        </div>

        <div class="card shadow-sm" style="width: 100%; max-width: 28rem">
            <div class="card-body p-4">
                <slot />
            </div>
        </div>
    </div>
</template>
