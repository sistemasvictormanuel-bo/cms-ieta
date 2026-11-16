<script setup lang="ts">
defineProps<{
  name: string
  role: string
  institution?: string
  email?: string
}>()
</script>

<template>
  <article class="editorial-member">
    <div>
      <h3>{{ name }}</h3>
      <p class="editorial-member__role">{{ role }}</p>
    </div>
    <p v-if="institution" class="editorial-member__institution">{{ institution }}</p>
    <a v-if="email" class="editorial-member__email" :href="`mailto:${email}`">{{ email }}</a>
  </article>
</template>

<style scoped>
.editorial-member {
  display: grid;
  min-width: 0;
  gap: 0.2rem;
  padding: var(--space-2);
  border-left: 4px solid #202e71;
  border-radius: 4px;
  background: #fff;
  box-shadow: 0 2px 8px rgb(20 22 28 / 9%);
}

.editorial-member h3 {
  color: #202e71;
  font-size: 1rem;
  line-height: 1.3;
  overflow-wrap: anywhere;
}

.editorial-member__role {
  color: #202e71;
  font-size: 0.8125rem;
}

.editorial-member__institution,
.editorial-member__email {
  color: #414351;
  font-size: 0.75rem;
  line-height: 1.45;
  overflow-wrap: anywhere;
}

.editorial-member__email {
  color: #f50088;
}
</style>
