<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    users: Object, // รับเป็น Object เพราะ Pagination ส่งมาแบบนั้น
    divisions: Array,
    filters: Object
});

// --- Filter Logic ---
const search = ref(props.filters.search || '');
const selectedDivision = ref(props.filters.division_id || '');
const selectedDepartment = ref(props.filters.department_id || '');
const selectedRole = ref(props.filters.role || '');
const perPage = ref(props.filters.per_page || 10);

const availableDepartments = computed(() => {
    if (!selectedDivision.value) return [];
    const div = props.divisions.find(d => d.id == selectedDivision.value);
    return div ? div.departments : [];
});

const applyFilter = () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        division_id: selectedDivision.value,
        department_id: selectedDepartment.value,
        role: selectedRole.value,
        per_page: perPage.value
    }, { preserveState: true, replace: true });
};

watch(selectedDivision, () => { selectedDepartment.value = ''; applyFilter(); });
watch([search, selectedDepartment, selectedRole, perPage], () => applyFilter());


// --- Org Management Logic ---
const isOrgModalOpen = ref(false);
const activeTab = ref('division');

const divForm = useForm({ id: null, name: '' });
const submitDivision = () => {
    if (divForm.id) divForm.put(route('admin.divisions.update', divForm.id), { onSuccess: () => divForm.reset() });
    else divForm.post(route('admin.divisions.store'), { onSuccess: () => divForm.reset() });
};
const editDivision = (div) => { divForm.id = div.id; divForm.name = div.name; };
const deleteDivision = (id) => { if(confirm('ลบกองนี้?')) router.delete(route('admin.divisions.destroy', id)); };

const deptForm = useForm({ id: null, name: '', division_id: '' });
const submitDepartment = () => {
    if (deptForm.id) deptForm.put(route('admin.departments.update', deptForm.id), { onSuccess: () => { deptForm.reset(); deptForm.division_id = ''; } });
    else deptForm.post(route('admin.departments.store'), { onSuccess: () => { deptForm.name = ''; } });
};
const editDepartment = (dept) => { deptForm.id = dept.id; deptForm.name = dept.name; deptForm.division_id = dept.division_id; };
const deleteDepartment = (id) => { if(confirm('ลบแผนกนี้?')) router.delete(route('admin.departments.destroy', id)); };

const deleteUser = (id) => {
    if(confirm('ยืนยันลบผู้ใช้งาน?')) router.delete(route('admin.users.destroy', id));
};
</script>

<template>
    <Head title="จัดการผู้ใช้งาน & โครงสร้าง" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">จัดการผู้ใช้งาน (HR Dashboard)</h2></template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">

                    <div class="flex gap-3 flex-wrap items-center w-full md:w-auto">
                        <div class="relative w-full sm:w-auto">
                            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                            <input v-model="search" placeholder="ค้นหาชื่อ / อีเมล..." class="pl-9 rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 w-full sm:w-64">
                        </div>

                        <select v-model="selectedDivision" class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                            <option value="">📂 ทุกกอง</option>
                            <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                        </select>
                        <select v-model="selectedDepartment" :disabled="!selectedDivision" class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 disabled:bg-gray-100">
                            <option value="">📑 ทุกแผนก</option>
                            <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                        <select v-model="selectedRole" class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                            <option value="">👤 ทุก Role</option>
                            <option value="admin">Admin</option>
                            <option value="sub_admin">Sub Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>

                    <div class="flex gap-2 items-center flex-wrap justify-end w-full md:w-auto">
                        <div class="flex items-center gap-2 mr-2">
                            <span class="text-sm text-gray-500">แสดง:</span>
                            <select v-model="perPage" class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 py-1.5 text-sm">
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>

                        <button @click="isOrgModalOpen = true" class="bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-bold shadow hover:bg-black transition flex items-center gap-2">
                            <span>⚙️</span> <span class="hidden lg:inline">จัดการ กอง/แผนก</span>
                        </button>
                        <Link :href="route('admin.users.create')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow hover:bg-indigo-700 transition flex items-center gap-2">
                            <span>+</span> <span class="hidden lg:inline">เพิ่มผู้ใช้งาน</span>
                        </Link>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-[20px] border border-gray-100 flex flex-col">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">ชื่อ-สกุล (ชื่อเล่น)</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">กอง (Division)</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">แผนก (Dept)</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Role</th>
                                    <th class="px-6 py-4 text-right">จัดการ</th>
                                </tr> </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-800">
                                            {{ user.name }}
                                            <span v-if="user.nickname" class="text-gray-500 font-normal ml-1">({{ user.nickname }})</span>
                                        </div>
                                        <div class="text-xs text-gray-500">{{ user.email }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span v-if="user.department?.division" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            📂 {{ user.department.division.name }}
                                        </span>
                                        <span v-else class="text-gray-300">-</span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span v-if="user.department" class="text-gray-700 font-medium text-sm">📑 {{ user.department.name }}</span>
                                        <span v-else class="text-gray-400 text-xs italic">- ไม่ระบุ -</span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-md text-xs font-bold uppercase"
                                            :class="{'bg-purple-100 text-purple-700': user.role==='admin', 'bg-blue-100 text-blue-700': user.role==='sub_admin', 'bg-gray-100 text-gray-600': user.role==='user'}">
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        <Link :href="route('admin.users.edit', user.id)" class="bg-amber-50 text-amber-500 hover:bg-amber-100 p-2 rounded-lg transition">✏️</Link>
                                        <button @click="deleteUser(user.id)" class="bg-red-50 text-red-500 hover:bg-red-100 p-2 rounded-lg transition">🗑️</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="users.data.length > 0" class="p-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50">
                        <div class="text-xs text-gray-500">
                            แสดง {{ users.from }} ถึง {{ users.to }} จากทั้งหมด {{ users.total }} รายการ
                        </div>
                        <div class="flex gap-1">
                            <Link v-for="(link, index) in users.links" :key="index"
                                :href="link.url || '#'"
                                v-html="link.label"
                                class="px-3 py-1 text-sm rounded-md transition"
                                :class="{
                                    'bg-indigo-600 text-white shadow': link.active,
                                    'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100': !link.active && link.url,
                                    'text-gray-300 cursor-not-allowed': !link.url
                                }"
                            />
                        </div>
                    </div>
                    <div v-else class="p-10 text-center text-gray-400">ไม่พบข้อมูลผู้ใช้งาน</div>
                </div>

            </div>
        </div>

        <Modal :show="isOrgModalOpen" @close="isOrgModalOpen = false">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">จัดการโครงสร้างองค์กร</h2>
                    <button @click="isOrgModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="flex border-b border-gray-200 mb-6">
                    <button @click="activeTab = 'division'" :class="{'border-b-2 border-indigo-600 text-indigo-600': activeTab==='division'}" class="px-4 py-2 font-bold text-sm transition">1. จัดการกอง</button>
                    <button @click="activeTab = 'department'" :class="{'border-b-2 border-indigo-600 text-indigo-600': activeTab==='department'}" class="px-4 py-2 font-bold text-sm transition">2. จัดการแผนก</button>
                </div>

                <div v-if="activeTab === 'division'">
                    <div class="flex gap-2 mb-4">
                        <input v-model="divForm.name" placeholder="ชื่อกองใหม่..." class="flex-1 rounded-lg border-gray-300 text-sm">
                        <button @click="submitDivision" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700">
                            {{ divForm.id ? 'อัปเดต' : 'เพิ่ม' }}
                        </button>
                        <button v-if="divForm.id" @click="divForm.reset(); divForm.id=null" class="text-gray-500 px-2 text-sm">ยกเลิก</button>
                    </div>
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                        <div v-for="div in divisions" :key="div.id" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="font-bold text-gray-700 text-sm">{{ div.name }}</span>
                            <div class="flex gap-2 text-xs">
                                <button @click="editDivision(div)" class="text-amber-500 hover:underline">แก้ไข</button>
                                <button @click="deleteDivision(div.id)" class="text-red-500 hover:underline">ลบ</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'department'">
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <select v-model="deptForm.division_id" class="rounded-lg border-gray-300 text-sm w-full">
                            <option value="" disabled>-- เลือกกอง --</option>
                            <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                        </select>
                        <div class="flex gap-2">
                            <input v-model="deptForm.name" placeholder="ชื่อแผนก..." class="flex-1 rounded-lg border-gray-300 text-sm">
                            <button @click="submitDepartment" :disabled="!deptForm.division_id" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:bg-gray-300">
                                {{ deptForm.id ? 'Save' : '+' }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4 max-h-60 overflow-y-auto pr-2">
                        <div v-for="div in divisions" :key="div.id">
                            <div class="text-xs font-bold text-gray-400 uppercase mb-1 bg-gray-100 p-1 pl-2 rounded">{{ div.name }}</div>
                            <div v-for="dept in div.departments" :key="dept.id" class="flex justify-between items-center p-2 pl-4 border-b border-gray-100 last:border-0 hover:bg-gray-50">
                                <span class="text-gray-700 text-sm">{{ dept.name }}</span>
                                <div class="flex gap-2 text-xs">
                                    <button @click="editDepartment(dept)" class="text-amber-500 hover:underline">แก้ไข</button>
                                    <button @click="deleteDepartment(dept.id)" class="text-red-500 hover:underline">ลบ</button>
                                </div>
                            </div>
                            <div v-if="div.departments.length===0" class="text-xs text-gray-300 pl-4 italic">- ว่างเปล่า -</div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
