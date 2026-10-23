<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center px-4 py-2 bg-[#35449F] hover:bg-[#2a3680] active:bg-[#14161C] border border-transparent rounded-xl font-bold text-sm text-white tracking-tight shadow-xs hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#35449F] focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 cursor-pointer whitespace-nowrap"
    >
        <slot />
    </button>
</template>
