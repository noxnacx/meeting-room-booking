<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import thLocale from '@fullcalendar/core/locales/th';
import { ref } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';

// --- Config ปฏิทิน ---
const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: thLocale,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    buttonText: {
        today: 'วันนี้',
        month: 'เดือน',
        week: 'สัปดาห์',
        day: 'วัน'
    },
    // ดึงข้อมูลตามช่วงเวลา start/end
    events: (info, successCallback, failureCallback) => {
        axios.get(route('api.calendar-events'), {
            params: {
                start: info.startStr,
                end: info.endStr
            }
        })
        .then(response => {
            successCallback(response.data);
        })
        .catch(error => {
            console.error('Error loading events:', error);
            failureCallback(error);
        });
    },
    eventClick: handleEventClick,
    height: 'auto',
    dayMaxEvents: true,
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false
    }
});

// --- Modal Logic ---
const isModalOpen = ref(false);
const selectedEvent = ref(null);

function handleEventClick(info) {
    selectedEvent.value = {
        title: info.event.extendedProps.topic,
        room: info.event.extendedProps.room_name,
        host: info.event.extendedProps.host_name,
        host_avatar: info.event.extendedProps.host_avatar,
        participants: info.event.extendedProps.participants,
        start: info.event.start,
        end: info.event.end,
        color: info.event.backgroundColor
    };
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    selectedEvent.value = null;
}

const formatThaiDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('th-TH', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};
</script>

<template>
    <Head title="ปฏิทินการใช้ห้อง" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-indigo-900 leading-tight">ปฏิทินการใช้ห้องประชุม</h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-indigo-100 p-6">
                    <FullCalendar :options="calendarOptions" class="calendar-custom" />
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div v-if="selectedEvent" class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: selectedEvent.color }"></span>
                            <span class="text-sm font-bold text-gray-500 uppercase tracking-wide">{{ selectedEvent.room }}</span>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ selectedEvent.title }}</h2>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex gap-3 items-start bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                        <div class="mt-1 text-indigo-600">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-indigo-900">เวลาเริ่ม</p>
                            <p class="text-indigo-700">{{ formatThaiDateTime(selectedEvent.start) }}</p>
                            <div class="h-2 border-l-2 border-indigo-200 ml-1 my-1"></div>
                            <p class="text-sm font-bold text-indigo-900">เวลาสิ้นสุด</p>
                            <p class="text-indigo-700">{{ formatThaiDateTime(selectedEvent.end) }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-2">ผู้จอง</h3>
                        <div class="flex items-center gap-3">
                            <img :src="selectedEvent.host_avatar ? `/storage/${selectedEvent.host_avatar}` : `https://ui-avatars.com/api/?name=${selectedEvent.host}`" class="w-10 h-10 rounded-full border border-gray-200">
                            <span class="text-gray-700 font-medium">{{ selectedEvent.host }}</span>
                        </div>
                    </div>
                    <div v-if="selectedEvent.participants && selectedEvent.participants.length > 0">
                        <h3 class="text-sm font-bold text-gray-900 mb-2">ผู้เข้าร่วม ({{ selectedEvent.participants.length }})</h3>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="(p, index) in selectedEvent.participants" :key="index" class="flex items-center gap-2 bg-white border border-gray-200 rounded-full px-3 py-1 shadow-sm">
                                <img :src="p.avatar ? `/storage/${p.avatar}` : `https://ui-avatars.com/api/?name=${p.name}`" class="w-6 h-6 rounded-full">
                                <span class="text-sm text-gray-600">{{ p.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button @click="closeModal" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition">ปิด</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
/* Font */
.calendar-custom {
    font-family: 'Sarabun', sans-serif;
    --fc-border-color: #e0e7ff; /* เส้นตารางสีฟ้าอ่อน */
}

/* 1. ปรับหัวตาราง (วัน จ-อา) เป็นสีฟ้า */
.fc-theme-standard th {
    background-color: #eef2ff; /* bg-indigo-50 */
    border-color: #e0e7ff;
}
.fc-col-header-cell-cushion {
    color: #4338ca; /* text-indigo-700 */
    font-weight: 800;
    padding: 10px 0;
    text-decoration: none !important;
}

/* 2. วันปัจจุบัน (Today) */
.fc-day-today {
    background-color: #f5f3ff !important; /* สีม่วง/ฟ้าจางๆ */
}

/* 3. ปรับปุ่มกด (Soft UI Blue Theme) */
.fc .fc-button-primary {
    background-color: #ffffff;
    border: 1px solid #c7d2fe; /* border-indigo-200 */
    color: #4f46e5; /* text-indigo-600 */
    padding: 0.5rem 1rem;
    font-weight: 700;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.2s;
}

.fc .fc-button-primary:hover {
    background-color: #eef2ff;
    border-color: #818cf8;
    color: #4338ca;
}

/* ปุ่มที่ถูกเลือก (Active) */
.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background-color: #4f46e5 !important;
    border-color: #4f46e5 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
}

/* ชื่อเดือน (Title) */
.fc .fc-toolbar-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #312e81; /* text-indigo-900 */
}

/* ลบระยะห่างระหว่างปุ่มกลุ่ม */
.fc .fc-button-group > .fc-button {
    margin: 0;
}
.fc .fc-button-group {
    gap: 5px;
}

/* Event Styles */
.fc-event {
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: none;
    padding: 3px 6px;
    margin-bottom: 2px;
}
</style>
