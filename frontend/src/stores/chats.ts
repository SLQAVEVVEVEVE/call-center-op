import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/api/axios'

export interface TelegramUser {
  id: number
  first_name: string
  last_name?: string
  username?: string
}

export interface Message {
  id: number
  chat_id: number
  body: string
  direction: 'in' | 'out'
  created_at: string
}

export interface Chat {
  id: number
  telegram_user?: TelegramUser
  last_message?: Message
  updated_at?: string
}

export const useChatsStore = defineStore('chats', () => {
  const list = ref<Chat[]>([])
  const messagesByChat = ref<Record<number, Message[]>>({})

  async function fetchChats() {
    const { data } = await api.get('/chats')
    list.value = data.data
  }

  async function fetchMessages(chatId: number) {
    const { data } = await api.get(`/chats/${chatId}/messages`)
    messagesByChat.value[chatId] = data.data
  }

  async function sendMessage(chatId: number, body: string) {
    const { data } = await api.post(`/chats/${chatId}/messages`, { body })
    pushMessage(chatId, data.data)
  }

  function upsertChat(chat: Chat) {
    const index = list.value.findIndex((c) => c.id === chat.id)
    if (index >= 0) {
      list.value[index] = { ...list.value[index], ...chat }
    } else {
      list.value.unshift(chat)
    }
  }

  function pushMessage(chatId: number, msg: Message) {
    if (!messagesByChat.value[chatId]) {
      messagesByChat.value[chatId] = []
    }
    const exists = messagesByChat.value[chatId].some((m) => m.id === msg.id)
    if (!exists) {
      messagesByChat.value[chatId].push(msg)
    }
  }

  return { list, messagesByChat, fetchChats, fetchMessages, sendMessage, upsertChat, pushMessage }
})
