<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center px-4 py-2 bg-[#F30060] hover:bg-[#d00052] active:bg-[#a80042] border border-transparent rounded-xl font-bold text-sm text-white tracking-tight shadow-xs hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#F30060] focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 cursor-pointer whitespace-nowrap"
    >
        <slot />
    </button>
</template>
