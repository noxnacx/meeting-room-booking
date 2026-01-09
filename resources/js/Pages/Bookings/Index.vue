<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    bookings: Object, // เปลี่ยนเป็น Object เพราะมี Pagination
    divisions: Array,
    filters: Object
});

// --- Filter State ---
const search = ref(props.filters.search || '');
const selectedDivision = ref(props.filters.division_id || '');
const selectedDepartment = ref(props.filters.department_id || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

// Computed: กรองแผนกตามกองที่เลือก
const availableDepartments = computed(() => {
    if (!selectedDivision.value) return [];
    const div = props.divisions.find(d => d.id == selectedDivision.value);
    return div ? div.departments : [];
});

// Watchers: เมื่อค่าเปลี่ยน ให้รีโหลดหน้าพร้อมส่งค่าไป Filter
const applyFilter = () => {
    router.get(route('bookings.index'), {
        search: search.value,
        division_id: selectedDivision.value,
        department_id: selectedDepartment.value,
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true, replace: true, preserveScroll: true });
};

watch(selectedDivision, () => { selectedDepartment.value = ''; applyFilter(); });
watch([search, selectedDepartment, startDate, endDate], () => {
    // ใช้ debounce เล็กน้อยสำหรับ search ได้ถ้าต้องการ แต่ใส่ตรงนี้ก็ทำงานได้เลย
    applyFilter();
});

// --- Helpers ---
const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const cancelBooking = (id) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกการจองนี้?')) {
        router.delete(route('bookings.destroy', id));
    }
};

const canEdit = (booking) => {
    const user = usePage().props.auth.user;
    return booking.user_id === user.id || user.role === 'admin' || user.role === 'sub_admin';
};
</script>

<template>
    <Head title="ประวัติการจอง" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">ประวัติการจอง</h2>
                <Link :href="route('dashboard')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700 transition">
                    + จองห้องประชุม
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-6 flex flex-col xl:flex-row gap-4">

                    <div class="relative flex-1">
                        <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                        <input v-model.lazy="search" placeholder="ค้นหาหัวข้อ / ชื่อผู้จอง..." class="pl-9 w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="flex flex-col md:flex-row gap-3">
                        <select v-model="selectedDivision" class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 text-sm min-w-[160px]">
                            <option value="">📂 ทุกกอง</option>
                            <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                        </select>

                        <select v-model="selectedDepartment" :disabled="!selectedDivision" class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 text-sm min-w-[160px] disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="">📑 ทุกแผนก</option>
                            <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 bg-gray-50 p-1 rounded-xl border border-gray-200">
                        <input v-model="startDate" type="date" class="border-0 bg-transparent text-sm focus:ring-0 text-gray-600">
                        <span class="text-gray-400">➜</span>
                        <input v-model="endDate" type="date" class="border-0 bg-transparent text-sm focus:ring-0 text-gray-600">
                    </div>

                    <Link :href="route('bookings.index')" class="px-3 py-2 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-lg text-sm transition text-center whitespace-nowrap">
                        ล้างค่า
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">วัน-เวลา</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ห้อง / หัวข้อ</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ผู้จัดการประชุม</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-indigo-600">
                                            {{ formatDateTime(booking.start_time) }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            ถึง {{ formatDateTime(booking.end_time).split(' ')[3] }} </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-800">{{ booking.title }}</div>
                                        <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                            <span class="w-2 h-2 rounded-full bg-green-400"></span>
                                            {{ booking.room.name }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <img class="h-8 w-8 rounded-full object-cover border border-gray-200"
                                                     :src="booking.user.avatar ? `/storage/${booking.user.avatar}` : `https://ui-avatars.com/api/?name=${booking.user.name}`">
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ booking.user.name }}
                                                    <span v-if="booking.user.id === $page.props.auth.user.id" class="text-xs text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded-md ml-1">(คุณ)</span>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <span v-if="booking.user.division">📂 {{ booking.user.division.name }}</span>
                                                    <span v-if="booking.user.department" class="ml-1">📑 {{ booking.user.department.name }}</span>
                                                    <span v-if="!booking.user.division && !booking.user.department">- ไม่ระบุสังกัด -</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('bookings.show', booking.id)" class="text-gray-400 hover:text-indigo-600 bg-gray-50 hover:bg-indigo-50 p-2 rounded-lg transition" title="ดูรายละเอียด">
                                                📄
                                            </Link>

                                            <template v-if="canEdit(booking)">
                                                <Link :href="route('bookings.edit', booking.id)" class="text-amber-500 hover:text-amber-700 bg-amber-50 hover:bg-amber-100 p-2 rounded-lg transition" title="แก้ไข">
                                                    ✏️
                                                </Link>
                                                <button @click="cancelBooking(booking.id)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition" title="ยกเลิก">
                                                    🗑️
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="bookings.data.length > 0" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50">
                        <div class="text-xs text-gray-500">
                            แสดง {{ bookings.from }} ถึง {{ bookings.to }} จาก {{ bookings.total }} รายการ
                        </div>
                        <div class="flex gap-1">
                            <Link v-for="(link, i) in bookings.links" :key="i"
                                :href="link.url || '#'"
                                v-html="link.label"
                                class="px-3 py-1 rounded-md text-xs font-bold transition"
                                :class="{
                                    'bg-indigo-600 text-white shadow-sm': link.active,
                                    'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100': !link.active && link.url,
                                    'text-gray-300 cursor-not-allowed': !link.url
                                }"
                            />
                        </div>
                    </div>

                    <div v-else class="p-10 text-center">
                        <div class="text-4xl mb-3">📭</div>
                        <h3 class="text-gray-900 font-bold">ไม่พบประวัติการจอง</h3>
                        <p class="text-gray-500 text-sm">ลองเปลี่ยนเงื่อนไขการค้นหาดูนะครับ</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
