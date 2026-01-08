<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue'; // ✅ Import Modal มาใช้

const props = defineProps({
    users: Array,
    selectedDate: String,
    divisions: Array,
    filters: Object
});

// --- State Variables ---
const date = ref(props.selectedDate);
const search = ref(props.filters.search || '');
const selectedDivision = ref(props.filters.division_id || '');
const selectedDepartment = ref(props.filters.department_id || '');

// --- Modal Logic ---
const isModalOpen = ref(false);
const selectedBooking = ref(null);

const openBookingDetails = (booking) => {
    selectedBooking.value = booking;
    isModalOpen.value = true;
};

// --- Filter Logic ---
const availableDepartments = computed(() => {
    if (!selectedDivision.value) return [];
    const div = props.divisions.find(d => d.id == selectedDivision.value);
    return div ? div.departments : [];
});

const applyFilter = () => {
    router.get(route('schedule.index'), {
        date: date.value,
        search: search.value,
        division_id: selectedDivision.value,
        department_id: selectedDepartment.value
    }, { preserveState: true, preserveScroll: true });
};

// Watchers
watch(date, applyFilter);
watch(selectedDivision, () => { selectedDepartment.value = ''; applyFilter(); });
watch([search, selectedDepartment], applyFilter); // พิมพ์ค้นหา หรือเปลี่ยนแผนก ให้โหลดใหม่

// --- Timeline Config (08:00 - 20:00) ---
// สร้าง array [8, 9, ..., 20] (รวม 13 ช่องเวลา)
const hours = Array.from({ length: 13 }, (_, i) => i + 8);

// Helper Format Date
const formatFullDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('th-TH', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="ตารางงานทีม" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800">📅 ตารางงานทีม (Schedule)</h2>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 font-bold">วันที่:</span>
                        <input type="date" v-model="date" class="rounded-lg border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold">
                    </div>
                </div>

                <div class="flex gap-3 flex-wrap items-center bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                    <div class="relative w-full sm:w-auto">
                        <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                        <input v-model.lazy="search" placeholder="ค้นหาชื่อ..." class="pl-9 rounded-lg border-gray-300 text-sm shadow-sm focus:ring-indigo-500 w-full sm:w-64">
                    </div>

                    <select v-model="selectedDivision" class="rounded-lg border-gray-300 text-sm shadow-sm focus:ring-indigo-500 min-w-[150px]">
                        <option value="">📂 ทุกกอง</option>
                        <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                    </select>

                    <select v-model="selectedDepartment" :disabled="!selectedDivision" class="rounded-lg border-gray-300 text-sm shadow-sm focus:ring-indigo-500 min-w-[150px] disabled:bg-gray-100 disabled:text-gray-400">
                        <option value="">📑 ทุกแผนก</option>
                        <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-6 bg-gray-50 min-h-screen overflow-x-auto">
            <div class="min-w-[1200px] max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">

                    <div class="flex border-b bg-gray-50">
                        <div class="w-64 p-4 font-bold text-gray-500 border-r bg-white sticky left-0 z-20 shadow-sm flex items-center">
                            รายชื่อพนักงาน
                        </div>
                        <div class="flex-1 relative h-10 bg-gray-50">
                            <div v-for="(h, i) in hours" :key="h"
                                class="absolute top-2 text-xs font-bold text-gray-400 border-l border-gray-300 pl-1 h-full select-none"
                                :style="{ left: `${(i / (hours.length - 1)) * 100}%` }">
                                {{ h }}:00
                            </div>
                        </div>
                    </div>

                    <div v-for="user in users" :key="user.id" class="flex border-b hover:bg-gray-50 transition relative group">

                        <div class="w-64 p-3 border-r bg-white sticky left-0 z-10 flex items-center gap-3 group-hover:bg-gray-50 transition-colors">
                            <img :src="user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${user.name}`"
                                 class="w-10 h-10 rounded-full object-cover border border-gray-100 shadow-sm">
                            <div class="overflow-hidden">
                                <div class="font-bold text-gray-800 text-sm truncate" :title="user.name">
                                    {{ user.name }}
                                    <span v-if="user.nickname" class="text-gray-400 text-xs font-normal">({{user.nickname}})</span>
                                </div>
                                <div class="text-xs text-gray-500 truncate" :title="user.department">{{ user.department }}</div>
                            </div>
                        </div>

                        <div class="flex-1 relative h-14 bg-white group-hover:bg-gray-50 transition-colors">

                            <div v-for="i in (hours.length - 1)" :key="i" class="absolute h-full border-l border-gray-100"
                                 :style="{ left: `${(i / (hours.length - 1)) * 100}%` }"></div>

                            <div v-for="(job, index) in user.schedule" :key="index"
                                @click="openBookingDetails(job)"
                                class="absolute top-2 bottom-2 bg-indigo-100 border-l-4 border-indigo-500 rounded-r text-[10px] px-2 py-0.5 overflow-hidden shadow-sm hover:z-30 hover:scale-105 hover:bg-indigo-200 hover:shadow-md transition cursor-pointer z-0 group/bar"
                                :style="job.style"
                            >
                                <div class="font-bold text-indigo-700 whitespace-nowrap">{{ job.start }}-{{ job.end }}</div>
                                <div class="truncate text-indigo-600 w-full group-hover/bar:whitespace-normal group-hover/bar:break-words leading-tight">
                                    {{ job.title }}
                                </div>
                            </div>

                        </div>
                    </div>

                    <div v-if="users.length === 0" class="p-10 text-center text-gray-400">
                        ไม่พบข้อมูลพนักงานตามเงื่อนไข
                    </div>
                </div>

                <div class="mt-4 text-xs text-gray-400 text-right">
                    * แสดงเฉพาะช่วงเวลาทำการ 08:00 - 20:00 น.
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <div class="p-6" v-if="selectedBooking">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-sm text-gray-500 font-bold uppercase">{{ selectedBooking.room }}</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ selectedBooking.title }}</h3>
                    </div>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">✕</button>
                </div>

                <div class="bg-indigo-50 rounded-xl p-4 mb-6 border border-indigo-100">
                    <div class="flex gap-4">
                        <div class="w-8 pt-1">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <div class="mb-2">
                                <div class="text-xs font-bold text-indigo-800 uppercase">เวลาเริ่ม</div>
                                <div class="text-indigo-900 font-medium">{{ formatFullDate(selectedBooking.full_start) }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-indigo-800 uppercase">เวลาสิ้นสุด</div>
                                <div class="text-indigo-900 font-medium">{{ formatFullDate(selectedBooking.full_end) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="text-sm font-bold text-gray-900 mb-2">ผู้จอง (Organizer)</h4>
                    <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-lg border border-gray-100">
                         <img :src="selectedBooking.organizer.avatar ? `/storage/${selectedBooking.organizer.avatar}` : `https://ui-avatars.com/api/?name=${selectedBooking.organizer.name}`"
                              class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <div class="font-bold text-gray-800">{{ selectedBooking.organizer.name }}</div>
                            <div class="text-xs text-gray-500">{{ selectedBooking.organizer.nickname }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-2">ผู้เข้าร่วม ({{ selectedBooking.participants.length }})</h4>
                    <div class="flex flex-wrap gap-2">
                        <div v-for="p in selectedBooking.participants" :key="p.id"
                             class="flex items-center gap-2 bg-gray-50 pl-1 pr-3 py-1 rounded-full border border-gray-100">
                            <img :src="p.avatar ? `/storage/${p.avatar}` : `https://ui-avatars.com/api/?name=${p.name}`" class="w-6 h-6 rounded-full object-cover">
                            <span class="text-xs font-medium text-gray-700">{{ p.name }}</span>
                        </div>
                        <span v-if="selectedBooking.participants.length === 0" class="text-sm text-gray-400 italic">- ไม่มีผู้เข้าร่วมอื่น -</span>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button @click="isModalOpen = false" class="bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-bold hover:bg-black transition">
                        ปิด
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
