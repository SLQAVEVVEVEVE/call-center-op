<script setup lang="ts">
defineProps<{
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
}>()
</script>

<template>
  <button
    :type="type ?? 'button'"
    :disabled="disabled"
    class="w-full py-3 px-6 rounded-2xl font-semibold text-base text-white bg-[var(--color-brand-500)] hover:bg-[var(--color-brand-600)] disabled:opacity-60 disabled:cursor-not-allowed transition"
  >
    <slot />
  </button>
</template>
