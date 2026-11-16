<script setup lang="ts">
import { computed } from 'vue'
import SafeHtml from './SafeHtml.vue'
import { mediaUrl } from '@/api/response'

interface RenderBlock {
  key: string
  html: string
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value)
}

function escapeHtml(value: string): string {
  return value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;')
}

function renderList(items: unknown, ordered: boolean, depth = 0): string {
  if (!Array.isArray(items) || depth > 8) return ''

  const tag = ordered ? 'ol' : 'ul'
  const listItems = items
    .map((item) => {
      if (typeof item === 'string') return `<li>${item}</li>`
      if (!isRecord(item)) return ''

      const text = typeof item.content === 'string' ? item.content : ''
      const children = renderList(item.items, ordered, depth + 1)
      return `<li>${text}${children}</li>`
    })
    .join('')

  return `<${tag}>${listItems}</${tag}>`
}

function normalizeString(content: string): RenderBlock[] {
  const trimmed = content.trim()
  if (!trimmed) return []

  const html = /<\/?[a-z][\s\S]*>/i.test(trimmed)
    ? trimmed
    : trimmed
        .split(/\n+/)
        .filter(Boolean)
        .map((line) => `<p>${escapeHtml(line)}</p>`)
        .join('')

  return [{ key: 'text-content', html }]
}

function normalizeEditorJs(content: unknown): RenderBlock[] {
  if (typeof content === 'string') return normalizeString(content)
  if (!isRecord(content) || !Array.isArray(content.blocks)) return []

  return content.blocks.flatMap((block, index) => {
    if (!isRecord(block) || !isRecord(block.data)) return []

    const { type, data } = block
    const key = typeof block.id === 'string' ? block.id : `editor-block-${index}`

    if (type === 'paragraph' && typeof data.text === 'string') {
      return [{ key, html: `<p>${data.text}</p>` }]
    }

    if (type === 'header' && typeof data.text === 'string') {
      const level = typeof data.level === 'number' ? Math.min(Math.max(data.level, 2), 6) : 2
      return [{ key, html: `<h${level}>${data.text}</h${level}>` }]
    }

    if (type === 'list') {
      const ordered = data.style === 'ordered'
      const html = renderList(data.items, ordered)
      return html ? [{ key, html }] : []
    }

    if (type === 'image' && isRecord(data.file) && typeof data.file.url === 'string') {
      const rawUrl = data.file.url
      const resolvedUrl = mediaUrl(rawUrl) || rawUrl
      const imageUrl = escapeHtml(resolvedUrl)
      const caption = typeof data.caption === 'string' ? escapeHtml(data.caption) : ''
      const html = `<figure class="editor-figure editor-figure--image"><img src="${imageUrl}" alt="${caption}" loading="lazy" />${caption ? `<figcaption>${caption}</figcaption>` : ''}</figure>`
      return [{ key, html }]
    }

    if (type === 'embed' && data.service === 'youtube' && typeof data.embed === 'string') {
      try {
        const embedUrl = new URL(data.embed)
        const isYoutubeEmbed =
          embedUrl.protocol === 'https:' &&
          [
            'youtube.com',
            'www.youtube.com',
            'youtube-nocookie.com',
            'www.youtube-nocookie.com',
          ].includes(embedUrl.hostname) &&
          /^\/embed\/[\w-]+$/.test(embedUrl.pathname)

        if (!isYoutubeEmbed) return []

        const caption =
          typeof data.caption === 'string' ? escapeHtml(data.caption) : 'Video de YouTube'
        const html = `<figure class="editor-figure editor-figure--video"><div class="editor-video-container"><iframe src="${escapeHtml(embedUrl.href)}" title="${caption}" allowfullscreen loading="lazy"></iframe></div></figure>`
        return [{ key, html }]
      } catch {
        return []
      }
    }

    return []
  })
}

const props = defineProps<{
  content: unknown
  apiBaseUrl?: string
}>()

const blocks = computed(() => normalizeEditorJs(props.content))
</script>

<template>
  <div class="editor-content">
    <SafeHtml
      v-for="block in blocks"
      :key="block.key"
      :content="block.html"
      :api-base-url="apiBaseUrl"
    />
  </div>
</template>

<style scoped>
.editor-content {
  display: grid;
  gap: var(--space-2);
  width: 100%;
}

.editor-content :deep(p) {
  font-size: 1.05rem;
  line-height: 1.8;
  color: #3f475e;
  margin-bottom: 1rem;
}

.editor-content :deep(h2),
.editor-content :deep(h3),
.editor-content :deep(h4) {
  color: var(--color-indigo);
  font-weight: 700;
  line-height: 1.25;
  margin-top: var(--space-4);
  margin-bottom: var(--space-2);
}

.editor-content :deep(h2) {
  font-size: clamp(1.5rem, 2.8vw, 2.1rem);
}

.editor-content :deep(h3) {
  font-size: clamp(1.3rem, 2.4vw, 1.75rem);
}

.editor-content :deep(h4) {
  font-size: clamp(1.15rem, 2vw, 1.4rem);
}

.editor-content :deep(a) {
  color: var(--color-rosa-carmin);
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 3px;
  transition: color 0.15s ease;
}

.editor-content :deep(a:hover) {
  color: var(--color-indigo);
}

.editor-content :deep(ul),
.editor-content :deep(ol) {
  margin: var(--space-2) 0 var(--space-4);
  padding-left: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  color: #3f475e;
  font-size: 1.025rem;
  line-height: 1.7;
}

.editor-content :deep(li) {
  padding-left: 0.25rem;
}

/* Figures (Images & Videos) */
.editor-content :deep(figure) {
  margin: var(--space-4) auto;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.editor-content :deep(img) {
  max-width: 100%;
  max-height: 520px;
  width: auto;
  height: auto;
  border-radius: var(--radius-md);
  box-shadow: 0 10px 30px rgba(27, 20, 75, 0.1);
  border: 1px solid rgba(220, 224, 235, 0.85);
  display: block;
  object-fit: cover;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.editor-content :deep(img:hover) {
  transform: translateY(-2px);
  box-shadow: 0 14px 36px rgba(27, 20, 75, 0.14);
}

/* Video Responsive Container & Iframe */
.editor-content :deep(.editor-video-container) {
  position: relative;
  width: 100%;
  max-width: 760px;
  aspect-ratio: 16 / 9;
  margin-inline: auto;
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: 0 14px 36px rgba(27, 20, 75, 0.14);
  border: 1px solid rgba(220, 224, 235, 0.9);
  background: #0f121d;
}

.editor-content :deep(.editor-video-container iframe),
.editor-content :deep(iframe) {
  width: 100% !important;
  max-width: 100%;
  aspect-ratio: 16 / 9;
  height: 100% !important;
  border: 0;
  display: block;
}

.editor-content :deep(figcaption) {
  margin-top: 0.65rem;
  color: #626982;
  font-size: var(--text-small-size);
  font-style: italic;
  text-align: center;
}
</style>
