<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    id: { type: String, required: true },
    modelValue: { type: String, default: '' },
    label: { type: String, required: true },
    type: { type: String, default: 'text' },
    error: String,
    autofocus: Boolean,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (props.autofocus) input.value?.focus();
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div>
        <label :for="id" class="mb-1.5 block text-[11px] text-gray-500">{{ label }}</label>
        <input
            :id="id"
            ref="input"
            :type="type"
            :value="modelValue"
            class="h-[34px] w-full rounded-md border bg-white px-3 text-sm text-gray-800 shadow-none outline-none transition focus:border-[#0b0766] focus:ring-2 focus:ring-[#0b0766]/20"
            :class="error ? 'border-red-400' : 'border-gray-300'"
            v-bind="$attrs"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="mt-1 text-[11px] text-red-600">{{ error }}</p>
    </div>
</template>

<script>
export default { inheritAttrs: false };
</script>
