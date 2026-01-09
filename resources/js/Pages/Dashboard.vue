<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    upcomingMeetings: Array, // รับตัวแปรใหม่
    popularRooms: Array,
    roomStatus: Array // รับข้อมูลสถานะห้อง
});

// Helper แปลงเวลา
const formatDateTime = (dateStr) => {
    const d = new Date(dateStr);
    const today = new Date();
    const isToday = d.toDateString() === today.toDateString();

    const time = d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
    const date = d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });

    return isToday ? `วันนี้ ${time}` : `${date} ${time}`;
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard ภาพรวม</h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl text-2xl">📅</div>
                        <div>
                            <div class="text-sm text-gray-500 font-bold">การจองของฉัน</div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.mine }} <span class="text-sm font-normal text-gray-400">ครั้ง</span></div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl text-2xl">📊</div>
                        <div>
                            <div class="text-sm text-gray-500 font-bold">การจองทั้งหมดวันนี้ </div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.total }} <span class="text-sm font-normal text-gray-400">ครั้ง</span></div>
                        </div>
                    </div>

                    <Link :href="route('bookings.select_room')" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center justify-between group">
                        <div>
                            <div class="font-bold text-lg">จองห้องประชุมทันที</div>
                            <div class="text-indigo-100 text-sm">คลิกเพื่อเลือกห้อง</div>
                        </div>
                        <span class="text-3xl group-hover:translate-x-1 transition">➜</span>
                    </Link>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        🔴 สถานะห้องประชุมขณะนี้ (Live Status)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div v-for="room in roomStatus" :key="room.id"
                             class="p-4 rounded-xl border flex items-center justify-between transition"
                             :class="room.current_status === 'busy' ? 'bg-red-50 border-red-100' : 'bg-green-50 border-green-100'">

                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full animate-pulse" :class="room.current_status === 'busy' ? 'bg-red-500' : 'bg-green-500'"></div>
                                <span class="font-bold text-gray-700 text-sm">{{ room.name }}</span>
                            </div>

                            <span class="text-xs font-bold px-2 py-1 rounded-lg"
                                  :class="room.current_status === 'busy' ? 'text-red-600 bg-red-100' : 'text-green-600 bg-green-100'">
                                {{ room.current_status === 'busy' ? 'ไม่ว่าง' : 'ว่าง' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            ⏰ การนัดหมายของคุณ (เร็วๆ นี้)
                        </h3>
                        <div v-if="upcomingMeetings.length > 0" class="space-y-3">
                            <div v-for="meeting in upcomingMeetings" :key="meeting.id" class="flex gap-4 p-3 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 transition">
                                <div class="flex flex-col items-center justify-center bg-white px-3 py-1 rounded-lg border border-gray-200 min-w-[80px]">
                                    <span class="text-indigo-600 font-bold text-xs text-center">{{ formatDateTime(meeting.start_time) }}</span>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800 line-clamp-1 text-sm">{{ meeting.title }}</div>
                                    <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: meeting.room.color }"></span>
                                        {{ meeting.room.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-10 text-gray-400">
                            ยังไม่มีนัดหมายเร็วๆ นี้ สบายตัว! 🎉
                        </div>

                        <div class="mt-4 text-center">
                             <Link :href="route('bookings.index')" class="text-sm text-indigo-600 hover:text-indigo-800 font-bold">ดูประวัติทั้งหมด ➜</Link>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4">🏆 ห้องยอดนิยม</h3>
                        <div class="space-y-4">
                            <div v-for="(room, index) in popularRooms" :key="room.id" class="flex items-center justify-between border-b border-gray-50 pb-2 last:border-0">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 flex items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">{{ index + 1 }}</span>
                                    <span class="font-medium text-gray-700 text-sm">{{ room.name }}</span>
                                </div>
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-md font-bold">{{ room.bookings_count }} ครั้ง</span>
                            </div>
                            <div v-if="popularRooms.length === 0" class="text-gray-400 text-sm italic">ยังไม่มีข้อมูลสถิติ</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
