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
        class="inline-flex items-center justify-center px-3.5 py-2 bg-white border border-slate-200 hover:border-slate-300 rounded-xl font-bold text-sm text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:ring-offset-1 disabled:opacity-40 transition ease-in-out duration-150 cursor-pointer whitespace-nowrap"
    >
        <slot />
    </button>
</template>
