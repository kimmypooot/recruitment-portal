<template>
  <div class="relative">
    <!-- Bell icon button with unread count badge -->
    <button @click="isOpen = !isOpen" class="relative p-2 text-gray-400 hover:text-gray-600">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown notification list -->
    <div v-if="isOpen" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
      <div class="p-3 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-semibold text-gray-900">Notifications</h3>
        <button @click="markAllRead" class="text-xs text-blue-600 hover:underline">Mark all read</button>
      </div>
      <ul class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
        <li v-for="notif in notifications" :key="notif.id"
            :class="['p-3 hover:bg-gray-50', !notif.read_at ? 'bg-blue-50' : '']">
          <p class="text-sm text-gray-800">{{ notif.data.message }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ formatDate(notif.created_at) }}</p>
        </li>
        <li v-if="notifications.length === 0" class="p-4 text-center text-gray-400 text-sm">
          No notifications yet
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const isOpen       = ref(false);
const notifications = ref([]);
const unreadCount  = ref(0);

async function fetchNotifications() {
  const { data } = await axios.get('/api/notifications');
  notifications.value = data.notifications;
  unreadCount.value   = data.unread_count;
}

async function markAllRead() {
  await axios.post('/api/notifications/mark-all-read');
  notifications.value.forEach(n => n.read_at = new Date().toISOString());
  unreadCount.value = 0;
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleString('en-PH', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

onMounted(fetchNotifications);
// Poll every 60 seconds for new notifications
setInterval(fetchNotifications, 60000);
</script>
