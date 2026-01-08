<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    room: Object,
    users: Array
});

const form = useForm({
    room_id: props.room.id,
    title: '',
    booking_date: new Date().toISOString().split('T')[0],
    start_time: '',
    end_time: '',
    participants: [],
});

const bookings = ref([]);
const loading = ref(false);

// ตั้งค่าเวลาทำการ (08:00 - 18:00)
const startHour = 8;
const endHour = 18;
const totalMinutes = (endHour - startHour) * 60;

// ดึงข้อมูลการจอง
const fetchBookings = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.get-bookings-by-date'), {
            params: { room_id: form.room_id, date: form.booking_date }
        });
        bookings.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

watch(() => form.booking_date, fetchBookings);
onMounted(fetchBookings);

// คำนวณ CSS สำหรับ Timeline
const calculateStyle = (start, end) => {
    const getMinutes = (time) => {
        const [h, m] = time.split(':').map(Number);
        return h * 60 + m;
    };
    const startMin = getMinutes(start);
    const endMin = getMinutes(end);
    const dayStartMin = startHour * 60;
    const top = ((startMin - dayStartMin) / totalMinutes) * 100;
    const height = ((endMin - startMin) / totalMinutes) * 100;
    return { top: `${top}%`, height: `${height}%` };
};

// --- Invite System ---
const searchQuery = ref('');
const selectedUsers = ref([]);

const filteredUsers = computed(() => {
    if (!searchQuery.value) return [];

    // Normalization สำหรับภาษาไทย และตัดช่องว่าง
    const q = searchQuery.value.trim().toLowerCase().normalize("NFC");

    return props.users.filter(u => {
        const isSelected = selectedUsers.value.some(s => s.id === u.id);
        if (isSelected) return false;

        const name = (u.name || '').toLowerCase().normalize("NFC");
        const email = (u.email || '').toLowerCase().normalize("NFC");
        const nickname = (u.nickname || '').toLowerCase().normalize("NFC");

        return name.includes(q) || email.includes(q) || nickname.includes(q);
    });
});

const selectUser = (u) => {
    selectedUsers.value.push(u);
    form.participants.push(u.id);
    searchQuery.value = '';
};

const removeUser = (uid) => {
    selectedUsers.value = selectedUsers.value.filter(u => u.id !== uid);
    form.participants = form.participants.filter(id => id !== uid);
};
</script>

<template>
    <Head title="จองห้องประชุม" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">จองห้อง: {{ room.name }}</h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">

                <div class="w-full md:w-1/2 bg-white p-4 rounded-2xl shadow-lg border border-gray-100 h-fit">
                    <h3 class="font-bold text-gray-700 mb-4 flex justify-between items-center">
                        <span>ตารางการใช้ห้อง</span>
                        <input v-model="form.booking_date" type="date" class="border-gray-200 rounded-lg text-sm shadow-sm py-1">
                    </h3>

                    <div class="relative w-full h-[600px] bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                        <div v-for="h in (endHour - startHour)" :key="h"
                             class="absolute w-full border-t border-gray-200 text-xs text-gray-400 pl-1"
                             :style="{ top: `${(h / (endHour - startHour)) * 100}%` }">
                            {{ startHour + h }}:00
                        </div>
                        <div class="absolute top-0 left-0 pl-1 text-xs text-gray-400">08:00</div>

                        <div v-for="booking in bookings" :key="booking.id"
                             class="absolute left-10 right-2 bg-red-100 border-l-4 border-red-500 rounded-r-md p-2 text-xs overflow-hidden shadow-sm z-10"
                             :style="calculateStyle(booking.start_time, booking.end_time)"
                             :title="`จองโดย ${booking.booked_by}`"
                        >
                            <div class="font-bold text-red-700">{{ booking.start_time }} - {{ booking.end_time }}</div>
                            <div class="text-red-600 truncate">{{ booking.title }}</div>
                            <div class="text-gray-500 text-[10px] leading-tight break-words">โดย {{ booking.booked_by }}</div>
                        </div>

                        <div v-if="form.start_time && form.end_time"
                             class="absolute left-10 right-2 bg-indigo-100 border-l-4 border-indigo-500 rounded-r-md p-2 text-xs flex items-center justify-center opacity-90 z-20 pointer-events-none shadow-md"
                             :style="calculateStyle(form.start_time, form.end_time)">
                            <span class="font-bold text-indigo-700">เวลาที่คุณเลือก</span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/2 bg-white p-6 rounded-2xl shadow-lg border border-gray-100 h-fit">
                    <form @submit.prevent="form.post(route('bookings.store'))" class="space-y-5">
                        <div class="text-indigo-600 font-bold text-lg border-b pb-2">รายละเอียดการจอง</div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">หัวข้อการประชุม</label>
                            <input v-model="form.title" type="text" class="w-full rounded-xl border-gray-200 shadow-sm focus:ring-indigo-500" required placeholder="ระบุหัวข้อ...">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">เริ่ม (เช่น 08:30)</label>
                                <input v-model="form.start_time" type="time" class="w-full rounded-xl border-gray-200 shadow-sm focus:ring-indigo-500 text-center font-bold" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">สิ้นสุด (เช่น 10:15)</label>
                                <input v-model="form.end_time" type="time" class="w-full rounded-xl border-gray-200 shadow-sm focus:ring-indigo-500 text-center font-bold" required>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">เชิญผู้เข้าร่วม</label>
                            <input v-model="searchQuery" type="text" placeholder="+ ค้นหาชื่อ/อีเมล" class="w-full rounded-xl border-dashed border-2 border-gray-300 py-2 text-center text-sm bg-gray-50 mb-2">

                            <div v-if="searchQuery && filteredUsers.length > 0" class="bg-white shadow-lg rounded-xl border max-h-40 overflow-auto mb-2">
                                <div v-for="user in filteredUsers" :key="user.id" @click="selectUser(user)" class="p-2 hover:bg-indigo-50 cursor-pointer text-sm flex items-center gap-2 border-b last:border-0">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                                         <img :src="user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${user.name}`" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-gray-800 font-medium">
                                            {{ user.name }}
                                            <span v-if="user.nickname" class="text-indigo-600">({{ user.nickname }})</span>
                                        </span>
                                        <span class="text-xs text-gray-400">{{ user.email }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span v-for="user in selectedUsers" :key="user.id" class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-sm flex items-center gap-2 border border-indigo-100">
                                    <img :src="user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${user.name}`" class="w-5 h-5 rounded-full object-cover">
                                    <span>
                                        {{ user.name }}
                                        <span v-if="user.nickname">({{ user.nickname }})</span>
                                    </span>
                                    <button type="button" @click="removeUser(user.id)" class="text-red-400 hover:text-red-600 font-bold ml-1 hover:bg-red-50 rounded-full px-1">×</button>
                                </span>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-between gap-3">
                            <button type="button" @click="$inertia.visit(route('bookings.index'))" class="w-1/3 py-3 rounded-xl border border-gray-300 text-gray-500 font-bold hover:bg-gray-50">ยกเลิก</button>
                            <button type="submit" class="w-2/3 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200" :disabled="form.processing">ยืนยันการจอง</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
