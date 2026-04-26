<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{ send: [body: string] }>()
const body = ref('')

function submit() {
  const text = body.value.trim()
  if (!text) return
  emit('send', text)
  body.value = ''
}
</script>

<template>
  <form class="msg-input" @submit.prevent="submit">
    <input
      v-model="body"
      class="msg-field"
      placeholder="Type a message…"
      @keydown.enter.exact.prevent="submit"
    />
    <button type="submit" class="msg-send" :disabled="!body.trim()">Send</button>
  </form>
</template>

<style scoped>
.msg-input {
  display: flex;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.8);
  border-top: 1px solid #e2e8f0;
}
.msg-field {
  flex: 1;
  padding: 0.625rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.75rem;
  font-size: 0.9375rem;
  outline: none;
  font-family: inherit;
}
.msg-field:focus {
  border-color: #2563eb;
}
.msg-send {
  padding: 0.625rem 1.25rem;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9375rem;
}
.msg-send:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
