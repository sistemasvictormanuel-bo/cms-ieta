<script setup>
import SectionTitle from './SectionTitle.vue';
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="px-5 py-6 sm:p-7 bg-white border border-[#E0E0E0] rounded-2xl shadow-xs">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>
