<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePhotoPicker } from '@/composables/usePhotoPicker'

const emit = defineEmits<{ send: [payload: { body: string; image?: Blob }] }>()
const body = ref('')
const image = ref<Blob | null>(null)
const previewUrl = ref<string | null>(null)
const sending = ref(false)
const menuOpen = ref(false)
const { isNative, pick } = usePhotoPicker()

const canSubmit = computed(() => !sending.value && (!!body.value.trim() || !!image.value))

async function chooseImage(source: 'library' | 'camera') {
  menuOpen.value = false
  const picked = await pick(source)
  if (!picked) return
  setImage(picked)
}

function setImage(blob: Blob) {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  image.value = blob
  previewUrl.value = URL.createObjectURL(blob)
}

function clearImage() {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  image.value = null
  previewUrl.value = null
}

async function submit() {
  if (!canSubmit.value) return
  sending.value = true
  try {
    emit('send', { body: body.value.trim(), image: image.value ?? undefined })
    body.value = ''
    clearImage()
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <form
    class="flex flex-col gap-2 px-4 py-3 bg-white/80 border-t border-slate-200"
    @submit.prevent="submit"
  >
    <div v-if="previewUrl" class="flex items-center gap-2">
      <div class="relative">
        <img :src="previewUrl" class="w-20 h-20 object-cover rounded-lg" alt="preview" />
        <button
          type="button"
          class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-slate-900 text-white text-sm leading-none flex items-center justify-center shadow"
          @click="clearImage"
          aria-label="Remove image"
        >
          ×
        </button>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <div class="relative">
        <button
          type="button"
          class="w-10 h-10 flex items-center justify-center rounded-xl text-[var(--color-ink-muted)] hover:bg-slate-100 transition"
          @click="isNative ? (menuOpen = !menuOpen) : chooseImage('library')"
          aria-label="Attach image"
        >
          <!-- paperclip icon -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
          </svg>
        </button>
        <div
          v-if="menuOpen"
          class="absolute bottom-12 left-0 bg-white shadow-lg rounded-xl border border-slate-200 overflow-hidden text-sm w-44"
        >
          <button
            type="button"
            class="w-full text-left px-4 py-2.5 hover:bg-slate-50"
            @click="chooseImage('camera')"
          >
            📷 Сделать фото
          </button>
          <button
            type="button"
            class="w-full text-left px-4 py-2.5 hover:bg-slate-50 border-t border-slate-100"
            @click="chooseImage('library')"
          >
            🖼️ Из галереи
          </button>
        </div>
      </div>

      <input
        v-model="body"
        type="text"
        class="flex-1 px-4 py-2.5 border-1.5 border-slate-200 rounded-xl text-[15px] outline-none focus:border-[var(--color-brand-500)] transition"
        placeholder="Введите сообщение…"
        @keydown.enter.exact.prevent="submit"
      />
      <button
        type="submit"
        class="px-5 py-2.5 bg-[var(--color-brand-500)] text-white font-semibold rounded-xl text-[15px] disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[var(--color-brand-600)] transition"
        :disabled="!canSubmit"
      >
        Отправить
      </button>
    </div>
  </form>
</template>
