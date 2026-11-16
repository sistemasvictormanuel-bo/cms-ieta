<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: '-- Seleccionar opción --',
    },
    searchPlaceholder: {
        type: String,
        default: 'Buscar por nombre o apellido...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const rootRef = ref(null);
const searchInputRef = ref(null);

const normalizeText = (text) => {
    if (!text) return '';
    return text
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
};

const normalizedOptions = computed(() => {
    return props.options.map((opt) => {
        if (typeof opt === 'object' && opt !== null) {
            return {
                value: opt.value ?? opt.id,
                label: opt.label ?? opt.nombre ?? String(opt),
                sublabel: opt.sublabel ?? opt.correo ?? '',
                badge: opt.badge ?? '',
            };
        }
        return {
            value: opt,
            label: String(opt),
            sublabel: '',
            badge: '',
        };
    });
});

const selectedOption = computed(() => {
    return normalizedOptions.value.find(
        (opt) => String(opt.value) === String(props.modelValue)
    );
});

const filteredOptions = computed(() => {
    const query = normalizeText(searchQuery.value);
    if (!query) {
        return normalizedOptions.value;
    }

    const queryWords = query.split(/\s+/).filter(Boolean);

    return normalizedOptions.value.filter((opt) => {
        const fullText = normalizeText(`${opt.label} ${opt.sublabel} ${opt.badge}`);
        return queryWords.every((word) => fullText.includes(word));
    });
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        nextTick(() => {
            searchInputRef.value?.focus();
        });
    }
};

const closeDropdown = () => {
    isOpen.value = false;
    searchQuery.value = '';
};

const selectOption = (opt) => {
    emit('update:modelValue', opt.value);
    emit('change', opt.value);
    closeDropdown();
};

const clearSelection = (e) => {
    e.stopPropagation();
    emit('update:modelValue', '');
    emit('change', '');
};

const handleClickOutside = (e) => {
    if (rootRef.value && !rootRef.value.contains(e.target)) {
        closeDropdown();
    }
};

const onKeydown = (e) => {
    if (e.key === 'Escape') {
        closeDropdown();
    } else if (e.key === 'Enter' && isOpen.value && filteredOptions.value.length > 0) {
        e.preventDefault();
        selectOption(filteredOptions.value[0]);
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div ref="rootRef" class="relative w-full text-left">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggleDropdown"
            :disabled="disabled"
            class="w-full text-left flex items-center justify-between text-sm font-semibold rounded-xl border border-gray-200 py-2.5 px-3.5 bg-white transition shadow-2xs focus:outline-none focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 cursor-pointer"
            :class="[
                disabled ? 'opacity-60 cursor-not-allowed bg-gray-50' : 'hover:border-gray-300',
                isOpen ? 'border-[#35449F] ring-2 ring-[#35449F]/10' : ''
            ]"
        >
            <div class="flex items-center gap-2 truncate flex-1 mr-2">
                <span
                    v-if="selectedOption"
                    class="text-[#14161C] font-bold truncate"
                >
                    {{ selectedOption.label }}
                </span>
                <span
                    v-else
                    class="text-gray-400 font-medium truncate"
                >
                    {{ placeholder }}
                </span>
            </div>

            <div class="flex items-center gap-1.5 shrink-0 text-gray-400">
                <!-- Botón de Limpiar Selección -->
                <button
                    v-if="selectedOption && !disabled"
                    type="button"
                    @click="clearSelection"
                    class="p-0.5 rounded-full hover:bg-gray-100 hover:text-gray-600 transition"
                    title="Limpiar selección"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Chevron Icon -->
                <svg
                    class="size-4 text-gray-400 transition-transform duration-200"
                    :class="{ 'rotate-180 text-[#35449F]': isOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <!-- Dropdown Flotante -->
        <div
            v-if="isOpen"
            class="absolute z-50 mt-1.5 w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden text-sm animate-in fade-in zoom-in-95 duration-100"
        >
            <!-- Buscador en tiempo real -->
            <div class="p-2.5 border-b border-gray-100 bg-gray-50/60 sticky top-0 z-10">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="w-full pl-9 pr-8 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium focus:outline-none focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 placeholder-gray-400 transition"
                        @click.stop
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="searchQuery = ''; searchInputRef?.focus();"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Lista de opciones filtradas -->
            <div class="max-h-60 overflow-y-auto divide-y divide-gray-50 py-1">
                <div
                    v-for="opt in filteredOptions"
                    :key="opt.value"
                    @click="selectOption(opt)"
                    class="px-3.5 py-2.5 cursor-pointer flex items-center justify-between gap-2 transition group"
                    :class="[
                        String(opt.value) === String(modelValue)
                            ? 'bg-[#35449F]/10 text-[#35449F] font-bold'
                            : 'hover:bg-indigo-50/60 text-gray-700'
                    ]"
                >
                    <div class="flex flex-col min-w-0 flex-1">
                        <span class="truncate font-medium text-xs leading-snug group-hover:text-[#35449F]" :class="{ 'font-bold text-[#35449F]': String(opt.value) === String(modelValue) }">
                            {{ opt.label }}
                        </span>
                        <span v-if="opt.sublabel" class="text-[11px] text-gray-400 truncate">
                            {{ opt.sublabel }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <span v-if="opt.badge" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-100 text-gray-600">
                            {{ opt.badge }}
                        </span>
                        <!-- Check icon if selected -->
                        <svg
                            v-if="String(opt.value) === String(modelValue)"
                            class="size-4 text-[#35449F]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <!-- Estado Vacío -->
                <div
                    v-if="filteredOptions.length === 0"
                    class="py-6 px-4 text-center text-xs text-gray-400"
                >
                    <svg class="size-6 mx-auto mb-1 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>No se encontraron autores que coincidan con "{{ searchQuery }}".</span>
                </div>
            </div>
        </div>
    </div>
</template>

