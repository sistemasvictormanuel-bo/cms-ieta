<script setup>
defineProps({
    processing: Boolean,
    type: { type: String, default: 'submit' },
});
</script>

<template>
    <button
        :type="type"
        :disabled="processing"
        class="h-[34px] w-[140px] rounded-[5px] border border-[#2b2a8c] bg-[#05004e] text-[17px] font-medium text-white transition hover:bg-[#0b0766] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0b0766] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
    >
        <slot />
    </button>
</template>
