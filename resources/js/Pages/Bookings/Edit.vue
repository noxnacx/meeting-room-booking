<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    booking: Object,
    rooms: Array,
    users: Array,
    divisions: Array // ✅ รับค่า divisions เพิ่มเข้ามา
});

// ฟังก์ชันแยก Date/Time จาก String
const splitDateTime = (dateTimeString) => {
    if (!dateTimeString) return { date: '', time: '' };
    const parts = dateTimeString.split(' ');
    const date = parts[0];
    const time = parts[1].substring(0, 5);
    return { date, time };
};

const start = splitDateTime(props.booking.start_time);
const end = splitDateTime(props.booking.end_time);

const form = useForm({
    room_id: props.booking.room_id,
    title: props.booking.title,
    booking_date: start.date,
    start_time: start.time,
    end_time: end.time,
    participants: props.booking.participants.map(u => u.id),
});

// --- Timeline Logic ---
const bookings = ref([]);
const loading = ref(false);

// ✅ ปรับเวลาเป็น 08:00 - 20:00
const startHour = 8;
const endHour = 20;
const totalMinutes = (endHour - startHour) * 60;

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

// ถ้าเปลี่ยนวัน หรือ เปลี่ยนห้อง ให้โหลดตารางใหม่
watch([() => form.booking_date, () => form.room_id], fetchBookings);
onMounted(fetchBookings);

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

// --- Invite System (Copy มาจาก Create.vue) ---
const searchQuery = ref('');
const selectedUsers = ref([...props.booking.participants]); // เริ่มต้นด้วยคนที่มีอยู่แล้ว

// ✅ ตัวแปรสำหรับ Dropdown 2 ชั้น
const selectedDivisionId = ref("");
const selectedDepartmentId = ref("");

// ✅ Computed: กรองแผนกตามกองที่เลือก
const availableDepartments = computed(() => {
    if (!selectedDivisionId.value) return [];
    const div = props.divisions.find(d => d.id === selectedDivisionId.value);
    return div ? div.departments : [];
});

watch(selectedDivisionId, () => { selectedDepartmentId.value = ""; });

const filteredUsers = computed(() => {
    if (!searchQuery.value) return [];
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
    if (!selectedUsers.value.some(user => user.id === u.id)) {
        selectedUsers.value.push(u);
        form.participants.push(u.id);
    }
    searchQuery.value = '';
};

const removeUser = (uid) => {
    selectedUsers.value = selectedUsers.value.filter(u => u.id !== uid);
    form.participants = form.participants.filter(id => id !== uid);
};

// ✅ ฟังก์ชัน: ชวนกลุ่ม (Logic เดียวกับ Create)
const inviteGroup = () => {
    if (!selectedDivisionId.value) return;
    let usersToAdd = [];

    if (selectedDepartmentId.value) {
        usersToAdd = props.users.filter(u => u.department_id == selectedDepartmentId.value);
    } else {
        const div = props.divisions.find(d => d.id === selectedDivisionId.value);
        const deptIdsInDiv = div ? div.departments.map(dept => dept.id) : [];
        usersToAdd = props.users.filter(u =>
            u.division_id == selectedDivisionId.value ||
            (u.department_id && deptIdsInDiv.includes(u.department_id))
        );
    }

    usersToAdd.forEach(u => selectUser(u));
    selectedDivisionId.value = "";
    selectedDepartmentId.value = "";
};
</script>

<template>
    <Head title="แก้ไขการจอง" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">แก้ไขการจอง: {{ booking.title }}</h2>
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

                        <div v-for="b in bookings" :key="b.id"
                             class="absolute left-10 right-2 border-l-4 rounded-r-md p-2 text-xs overflow-hidden shadow-sm z-10"
                             :class="b.id === booking.id ? 'bg-amber-100 border-amber-500 opacity-50' : 'bg-red-100 border-red-500'"
                             :style="calculateStyle(b.start_time, b.end_time)"
                        >
                            <div class="font-bold" :class="b.id === booking.id ? 'text-amber-700' : 'text-red-700'">
                                {{ b.start_time }} - {{ b.end_time }}
                            </div>
                            <div class="truncate" :class="b.id === booking.id ? 'text-amber-600' : 'text-red-600'">{{ b.title }}</div>
                            <div class="text-[10px]" :class="b.id === booking.id ? 'text-amber-600' : 'text-gray-500'">{{ b.id === booking.id ? '(รายการนี้)' : `โดย ${b.booked_by}` }}</div>
                        </div>

                        <div v-if="form.start_time && form.end_time"
                             class="absolute left-10 right-2 bg-indigo-100 border-l-4 border-indigo-500 rounded-r-md p-2 text-xs flex items-center justify-center opacity-90 z-20 pointer-events-none shadow-md"
                             :style="calculateStyle(form.start_time, form.end_time)">
                            <span class="font-bold text-indigo-700">เวลาใหม่</span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/2 bg-white p-6 rounded-2xl shadow-lg border border-gray-100 h-fit">
                    <form @submit.prevent="form.put(route('bookings.update', booking.id))" class="space-y-5">
                        <div class="text-indigo-600 font-bold text-lg border-b pb-2">แก้ไขรายละเอียด</div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">เปลี่ยนห้องประชุม</label>
                            <select v-model="form.room_id" class="w-full rounded-xl border-gray-200 shadow-sm focus:ring-indigo-500">
                                <option v-for="r in rooms" :key="r.id" :value="r.id">{{ r.name }} ({{ r.capacity }} คน)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">หัวข้อการประชุม</label>
                            <input v-model="form.title" type="text" class="w-full rounded-xl border-gray-200 shadow-sm focus:ring-indigo-500" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">เริ่ม</label>
                                <input v-model="form.start_time" type="time" class="w-full rounded-xl border-gray-200 shadow-sm font-bold text-center" required>
                                <p v-if="form.errors.start_time" class="text-red-500 text-xs mt-1">{{ form.errors.start_time }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">สิ้นสุด</label>
                                <input v-model="form.end_time" type="time" class="w-full rounded-xl border-gray-200 shadow-sm font-bold text-center" required>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">เชิญผู้เข้าร่วม</label>

                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <select v-model="selectedDivisionId" class="w-full rounded-xl border-gray-200 text-sm shadow-sm py-2">
                                    <option value="">📂 กรุณาเลือกกอง</option>
                                    <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                                </select>

                                <select v-model="selectedDepartmentId" :disabled="!selectedDivisionId" class="w-full rounded-xl border-gray-200 text-sm shadow-sm py-2 disabled:bg-gray-100">
                                    <option value="">📑 ทั้งกอง (หรือระบุแผนก)</option>
                                    <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>

                            <button type="button" @click="inviteGroup" :disabled="!selectedDivisionId" class="w-full mb-3 bg-indigo-100 text-indigo-700 py-2 rounded-xl text-sm font-bold hover:bg-indigo-200 disabled:opacity-50 transition">
                                <span v-if="!selectedDivisionId">+ เลือกกองเพื่อเชิญ</span>
                                <span v-else-if="!selectedDepartmentId">+ เชิญทั้งกอง {{ divisions.find(d => d.id === selectedDivisionId)?.name }}</span>
                                <span v-else>+ เชิญแผนก {{ availableDepartments.find(d => d.id === selectedDepartmentId)?.name }}</span>
                            </button>

                            <input v-model="searchQuery" type="text" placeholder="+ ค้นหาชื่อ/อีเมล เพิ่มเติม" class="w-full rounded-xl border-dashed border-2 border-gray-300 py-2 text-center text-sm bg-gray-50 mb-2">

                            <div v-if="form.errors.participants" class="bg-red-50 border border-red-200 rounded-xl p-3 mb-2 flex items-start gap-2">
                                <span class="text-xl">🚨</span>
                                <div>
                                    <div class="text-red-800 font-bold text-sm">ข้อขัดแย้งเวลา</div>
                                    <div class="text-red-600 text-xs">{{ form.errors.participants }}</div>
                                </div>
                            </div>

                            <div v-if="searchQuery && filteredUsers.length > 0" class="bg-white shadow-lg rounded-xl border max-h-40 overflow-auto mb-2">
                                <div v-for="user in filteredUsers" :key="user.id" @click="selectUser(user)" class="p-2 hover:bg-indigo-50 cursor-pointer text-sm flex items-center gap-2 border-b last:border-0">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                                         <img :src="user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${user.name}`" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="text-gray-800 font-medium">{{ user.name }}</div>
                                        <div class="text-xs text-gray-400">{{ user.email }}</div>
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
                            <Link :href="route('bookings.index')" class="w-1/3 py-3 rounded-xl border border-gray-300 text-gray-500 font-bold hover:bg-gray-50 text-center">ยกเลิก</Link>
                            <button type="submit" class="w-2/3 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg" :disabled="form.processing">บันทึกการแก้ไข</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
