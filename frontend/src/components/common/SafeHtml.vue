<script setup lang="ts">
import { computed } from 'vue'
import { sanitizeCmsHtml } from '@/utils/sanitize'

const props = defineProps<{
  content: string
  apiBaseUrl?: string
}>()

const cleanContent = computed(() => sanitizeCmsHtml(props.content, props.apiBaseUrl))
</script>

<template>
  <div class="safe-html-content" v-html="cleanContent"></div>
</template>
