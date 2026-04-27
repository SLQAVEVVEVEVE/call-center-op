<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import type { ChatStatus } from '@/stores/chats'
import { STATUS_LABELS } from '@/stores/dashboard'

const props = defineProps<{ modelValue: ChatStatus; disabled?: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: ChatStatus] }>()

const open = ref(false)
const root = ref<HTMLElement | null>(null)

const STATUSES = Object.keys(STATUS_LABELS) as ChatStatus[]

const currentLabel = computed(() => STATUS_LABELS[props.modelValue])

function toggle(e: MouseEvent) {
  e.stopPropagation()
  if (props.disabled) return
  open.value = !open.value
}

function pick(s: ChatStatus, e: MouseEvent) {
  e.stopPropagation()
  open.value = false
  if (s !== props.modelValue) emit('update:modelValue', s)
}

function onDocClick(e: MouseEvent) {
  if (!root.value) return
  if (!root.value.contains(e.target as Node)) open.value = false
}

function onKey(e: KeyboardEvent) {
  if (e.key === 'Escape') open.value = false
}

onMounted(() => {
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onKey)
})
onUnmounted(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onKey)
})
</script>

<template>
  <div ref="root" class="relative inline-block">
    <button
      type="button"
      class="flex items-center gap-1.5 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white transition"
      :class="[
        open ? 'border-[var(--color-brand-500)] ring-2 ring-[var(--color-brand-500)]/20' : '',
        disabled ? 'opacity-50 cursor-not-allowed' : 'hover:border-slate-300 cursor-pointer',
      ]"
      :disabled="disabled"
      @click="toggle"
    >
      <span :class="['inline-block w-1.5 h-1.5 rounded-full', `dot-${modelValue}`]"></span>
      <span>{{ currentLabel }}</span>
      <svg
        width="12"
        height="12"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="transition-transform"
        :class="open ? 'rotate-180' : ''"
      >
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </button>

    <Transition name="pop">
      <ul
        v-if="open"
        class="absolute z-20 left-0 mt-1.5 w-44 rounded-xl border border-slate-200 bg-white py-1 shadow-lg overflow-hidden"
      >
        <li v-for="s in STATUSES" :key="s">
          <button
            type="button"
            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left hover:bg-slate-50 transition"
            :class="
              s === modelValue
                ? 'text-[var(--color-brand-600)] font-semibold bg-[var(--color-brand-500)]/5'
                : 'text-[var(--color-ink)]'
            "
            @click="pick(s, $event)"
          >
            <span :class="['inline-block w-1.5 h-1.5 rounded-full', `dot-${s}`]"></span>
            {{ STATUS_LABELS[s] }}
            <svg
              v-if="s === modelValue"
              class="ml-auto"
              width="14"
              height="14"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="20 6 9 17 4 12" />
            </svg>
          </button>
        </li>
      </ul>
    </Transition>
  </div>
</template>

<style scoped>
.dot-new {
  background: var(--color-brand-500);
}
.dot-in_progress {
  background: #f59e0b;
}
.dot-resolved {
  background: #10b981;
}

.pop-enter-active,
.pop-leave-active {
  transition:
    opacity 0.12s ease,
    transform 0.12s ease;
  transform-origin: top left;
}
.pop-enter-from,
.pop-leave-to {
  opacity: 0;
  transform: scale(0.96) translateY(-4px);
}
</style>
