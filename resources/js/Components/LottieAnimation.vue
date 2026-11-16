<script setup>
import { computed } from 'vue';
import { DotLottieVue } from '@lottiefiles/dotlottie-vue';

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    data: {
        type: Object,
        default: null,
    },
    width: {
        type: [Number, String],
        default: '100%',
    },
    height: {
        type: [Number, String],
        default: '100%',
    },
    autoplay: {
        type: Boolean,
        default: true,
    },
    loop: {
        type: Boolean,
        default: true,
    },
    speed: {
        type: Number,
        default: 1,
    },
});

const styleObj = computed(() => {
    const w = typeof props.width === 'number' ? `${props.width}px` : props.width;
    const h = typeof props.height === 'number' ? `${props.height}px` : props.height;
    return {
        width: w,
        height: h,
    };
});
</script>

<template>
    <div class="inline-flex items-center justify-center overflow-hidden" :style="styleObj">
        <DotLottieVue
            :src="src"
            :data="data"
            :autoplay="autoplay"
            :loop="loop"
            :speed="speed"
            :style="styleObj"
        />
    </div>
</template>
