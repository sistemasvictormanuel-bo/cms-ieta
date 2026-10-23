<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-[14px] font-semibold text-rose-600 mt-1">
            {{ message }}
        </p>
    </div>
</template>
