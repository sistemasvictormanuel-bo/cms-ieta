<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: [String, Number],
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value && input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value && input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="border border-[#CBD5E1] bg-white text-[#14161C] focus:border-[#35449F] focus:ring-[#35449F] rounded-xl shadow-2xs text-[15.5px] font-medium py-3 px-4 transition w-full placeholder:text-slate-400 placeholder:text-[14.5px]"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
