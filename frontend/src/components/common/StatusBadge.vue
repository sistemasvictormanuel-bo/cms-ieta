<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

type CourseStatus = 'open' | 'closed' | 'finished'

const props = defineProps<{
  registrationStart?: string
  registrationEnd?: string
  courseEnd?: string
  today?: Date
}>()

const { t } = useI18n()

function parseDate(value?: string): Date | null {
  if (!value) return null

  const dateOnly = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value)
  const parsed = dateOnly
    ? new Date(Number(dateOnly[1]), Number(dateOnly[2]) - 1, Number(dateOnly[3]))
    : new Date(value)

  if (Number.isNaN(parsed.getTime())) return null
  parsed.setHours(0, 0, 0, 0)
  return parsed
}

const status = computed<CourseStatus>(() => {
  const today = new Date(props.today ?? new Date())
  today.setHours(0, 0, 0, 0)

  const registrationStart = parseDate(props.registrationStart)
  const registrationEnd = parseDate(props.registrationEnd)
  const courseEnd = parseDate(props.courseEnd)

  if (courseEnd && courseEnd < today) return 'finished'
  if (registrationStart && registrationStart > today) return 'closed'
  if (registrationEnd && registrationEnd < today) return 'closed'
  if (registrationStart || registrationEnd) return 'open'
  return 'closed'
})

const statusLabel = computed(() => t(`status.${status.value}`))
</script>

<template>
  <span class="status-badge" :class="`status-badge--${status}`" :data-status="status">
    {{ statusLabel }}
  </span>
</template>

<style scoped>
.status-badge {
  display: inline-flex;
  min-height: 1.75rem;
  align-items: center;
  padding: 0.25rem 0.625rem;
  border: 1px solid transparent;
  border-radius: var(--radius-sm);
  font-size: var(--text-small-size);
  font-weight: 600;
  line-height: 1.2;
}

.status-badge--open {
  border-color: var(--color-indigo);
  background: var(--color-superficie);
  color: var(--color-indigo);
}

.status-badge--closed {
  border-color: var(--color-desactivado);
  background: var(--color-superficie);
  color: var(--color-tinta);
}

.status-badge--finished {
  border-color: var(--color-violeta);
  background: var(--color-violeta);
  color: var(--color-blanco);
}
</style>
