<script setup lang="ts">
defineProps<{
  modelValue: string
  type?: string
  placeholder?: string
  required?: boolean
}>()
defineEmits<{ 'update:modelValue': [value: string] }>()
</script>

<template>
  <input
    :value="modelValue"
    :type="type ?? 'text'"
    :placeholder="placeholder"
    :required="required"
    class="w-full py-3 px-4 rounded-xl text-base bg-slate-50 border border-slate-200 outline-none focus:border-[var(--color-brand-500)] focus:bg-white transition"
    @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
  />
</template>
