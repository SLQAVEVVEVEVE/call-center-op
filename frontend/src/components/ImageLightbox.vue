<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'

const props = defineProps<{ src: string | null; open: boolean }>()
const emit = defineEmits<{ 'update:open': [value: boolean] }>()

function close() {
  emit('update:open', false)
}

function onKey(e: KeyboardEvent) {
  if (e.key === 'Escape') close()
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => window.removeEventListener('keydown', onKey))

watch(
  () => props.open,
  (v) => {
    document.body.style.overflow = v ? 'hidden' : ''
  },
)
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open && src"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 cursor-zoom-out"
      @click.self="close"
    >
      <img
        :src="src"
        class="max-w-full max-h-full object-contain rounded-md shadow-2xl"
        alt=""
      />
      <button
        type="button"
        class="absolute top-4 right-4 text-white/90 hover:text-white text-3xl leading-none w-10 h-10 flex items-center justify-center rounded-full bg-black/40 hover:bg-black/60 transition"
        aria-label="Close"
        @click="close"
      >
        ×
      </button>
    </div>
  </Teleport>
</template>
