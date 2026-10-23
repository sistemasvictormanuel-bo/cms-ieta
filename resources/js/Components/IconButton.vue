<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    as: {
        type: String,
        default: 'button',
        validator: (value) => ['button', 'Link', 'a'].includes(value),
    },
    href: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'primary', 'warning', 'danger', 'success', 'subtle'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    title: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'size-7.5 text-xs rounded-lg';
        case 'lg':
            return 'size-10 text-base rounded-xl';
        case 'md':
        default:
            return 'size-8.5 text-sm rounded-xl';
    }
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'text-[#35449F] bg-indigo-50/80 hover:bg-indigo-100/90 border-indigo-200/80 hover:border-indigo-300 focus:ring-indigo-500/20';
        case 'warning':
            return 'text-amber-700 bg-amber-50/80 hover:bg-amber-100/90 border-amber-200/80 hover:border-amber-300 focus:ring-amber-500/20';
        case 'danger':
            return 'text-rose-700 bg-rose-50/80 hover:bg-rose-100/90 border-rose-200/80 hover:border-rose-300 focus:ring-rose-500/20';
        case 'success':
            return 'text-emerald-700 bg-emerald-50/80 hover:bg-emerald-100/90 border-emerald-200/80 hover:border-emerald-300 focus:ring-emerald-500/20';
        case 'subtle':
            return 'text-slate-500 hover:text-slate-800 bg-transparent hover:bg-slate-100 border-transparent focus:ring-slate-400/20';
        case 'default':
        default:
            return 'text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border-slate-200/90 hover:border-slate-300 focus:ring-[#35449F]/20 shadow-2xs';
    }
});

const commonClasses = computed(() => [
    'inline-flex items-center justify-center border transition-all duration-150 focus:outline-none focus:ring-2 disabled:opacity-40 disabled:pointer-events-none cursor-pointer shrink-0',
    sizeClasses.value,
    variantClasses.value,
]);
</script>

<template>
    <Link
        v-if="as === 'Link'"
        :href="href"
        :class="commonClasses"
        :title="title"
        :aria-label="title"
    >
        <slot />
    </Link>
    <a
        v-else-if="as === 'a'"
        :href="href"
        :class="commonClasses"
        :title="title"
        :aria-label="title"
    >
        <slot />
    </a>
    <button
        v-else
        :type="type"
        :disabled="disabled"
        :class="commonClasses"
        :title="title"
        :aria-label="title"
    >
        <slot />
    </button>
</template>
