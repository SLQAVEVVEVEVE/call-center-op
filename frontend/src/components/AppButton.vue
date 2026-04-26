<script setup lang="ts">
defineProps<{
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
}>()
</script>

<template>
  <button :type="type ?? 'button'" :disabled="disabled" class="app-btn">
    <slot />
  </button>
</template>

<style scoped>
.app-btn {
  width: 100%;
  padding: 0.75rem 1.5rem;
  background: #2563eb;
  color: white;
  font-weight: 600;
  border: none;
  border-radius: 1rem;
  cursor: pointer;
  font-size: 1rem;
  transition: opacity 0.2s;
}
.app-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.app-btn:not(:disabled):hover {
  opacity: 0.9;
}
</style>
