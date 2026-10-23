<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block font-bold text-[15.5px] text-slate-900 tracking-tight">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
