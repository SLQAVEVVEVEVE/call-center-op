<script setup lang="ts">
import type { Message } from '@/stores/chats'

defineProps<{ message: Message }>()
</script>

<template>
  <div
    class="bubble-wrap"
    :class="message.direction === 'out' ? 'bubble-right' : 'bubble-left'"
  >
    <div
      class="bubble"
      :class="message.direction === 'out' ? 'bubble-op' : 'bubble-user'"
    >
      {{ message.body }}
    </div>
  </div>
</template>

<style scoped>
.bubble-wrap {
  display: flex;
  margin: 0.25rem 0;
}
.bubble-left {
  justify-content: flex-start;
}
.bubble-right {
  justify-content: flex-end;
}
.bubble {
  max-width: 70%;
  padding: 0.625rem 1rem;
  border-radius: 1rem;
  font-size: 0.9375rem;
  line-height: 1.4;
}
.bubble-user {
  background: #e2e8f0;
  color: #1e293b;
  border-bottom-left-radius: 0.25rem;
}
.bubble-op {
  background: #2563eb;
  color: #fff;
  border-bottom-right-radius: 0.25rem;
}
</style>
