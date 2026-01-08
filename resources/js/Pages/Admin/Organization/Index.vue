<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({ divisions: Array });

// --- State สำหรับ Modal ---
const isDivisionModalOpen = ref(false);
const isDepartmentModalOpen = ref(false);
const editingDivision = ref(null);
const editingDepartment = ref(null);
const targetDivisionId = ref(null); // ไว้จำว่าจะเพิ่มแผนกใส่กองไหน

// --- Forms ---
const divisionForm = useForm({ name: '' });
const departmentForm = useForm({ name: '', division_id: '' });

// --- Functions: Division (กอง) ---
const openCreateDivision = () => {
    editingDivision.value = null;
    divisionForm.reset();
    isDivisionModalOpen.value = true;
};
const openEditDivision = (div) => {
    editingDivision.value = div;
    divisionForm.name = div.name;
    isDivisionModalOpen.value = true;
};
const submitDivision = () => {
    if (editingDivision.value) {
        divisionForm.put(route('admin.divisions.update', editingDivision.value.id), {
            onSuccess: () => isDivisionModalOpen.value = false
        });
    } else {
        divisionForm.post(route('admin.divisions.store'), {
            onSuccess: () => isDivisionModalOpen.value = false
        });
    }
};
const deleteDivision = (id) => {
    if(confirm('ยืนยันลบ "กอง" นี้? (แผนกข้างในจะหายไปด้วย)')) router.delete(route('admin.divisions.destroy', id));
};

// --- Functions: Department (แผนก) ---
const openCreateDepartment = (divId) => {
    editingDepartment.value = null;
    targetDivisionId.value = divId;
    departmentForm.reset();
    departmentForm.division_id = divId;
    isDepartmentModalOpen.value = true;
};
const openEditDepartment = (dept, divId) => {
    editingDepartment.value = dept;
    targetDivisionId.value = divId; // เก็บไว้เผื่อใช้
    departmentForm.name = dept.name;
    departmentForm.division_id = divId;
    isDepartmentModalOpen.value = true;
};
const submitDepartment = () => {
    if (editingDepartment.value) {
        departmentForm.put(route('admin.departments.update', editingDepartment.value.id), {
            onSuccess: () => isDepartmentModalOpen.value = false
        });
    } else {
        departmentForm.post(route('admin.departments.store'), {
            onSuccess: () => isDepartmentModalOpen.value = false
        });
    }
};
const deleteDepartment = (id) => {
    if(confirm('ยืนยันลบแผนกนี้?')) router.delete(route('admin.departments.destroy', id));
};
</script>

<template>
    <Head title="จัดการโครงสร้างองค์กร" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">จัดการโครงสร้างองค์กร (กอง / แผนก)</h2>
                <button @click="openCreateDivision" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 flex items-center gap-2">
                    <span>+</span> เพิ่มกองใหม่
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div v-for="division in divisions" :key="division.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

                    <div class="bg-gray-100 px-6 py-4 flex justify-between items-center border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <span class="bg-indigo-600 text-white text-xs px-2 py-1 rounded font-bold">กอง</span>
                            <h3 class="font-bold text-lg text-gray-800">{{ division.name }}</h3>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openEditDivision(division)" class="text-gray-500 hover:text-indigo-600 p-1">✏️ แก้ชื่อกอง</button>
                            <button @click="deleteDivision(division.id)" class="text-gray-500 hover:text-red-600 p-1">🗑️ ลบกอง</button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div v-if="division.departments && division.departments.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="dept in division.departments" :key="dept.id"
                                class="flex justify-between items-center p-3 rounded-xl border border-gray-100 bg-gray-50 hover:border-indigo-200 hover:bg-white transition group">
                                <div>
                                    <div class="font-bold text-gray-700">{{ dept.name }}</div>
                                    <div class="text-xs text-gray-400">👤 {{ dept.users_count }} คน</div>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                    <button @click="openEditDepartment(dept, division.id)" class="text-amber-500 hover:bg-amber-100 p-1.5 rounded-lg">✏️</button>
                                    <button @click="deleteDepartment(dept.id)" class="text-red-500 hover:bg-red-100 p-1.5 rounded-lg">🗑️</button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-400 text-sm py-4 italic">
                            ยังไม่มีแผนกในกองนี้
                        </div>

                        <button @click="openCreateDepartment(division.id)" class="mt-4 w-full py-2 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 text-sm font-bold hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition flex justify-center items-center gap-2">
                            <span>+</span> เพิ่มแผนกใน{{ division.name }}
                        </button>
                    </div>
                </div>

                <div v-if="divisions.length === 0" class="text-center py-10 text-gray-500">
                    ยังไม่มีข้อมูล (กดปุ่ม "เพิ่มกองใหม่" มุมขวาบนเพื่อเริ่มต้น)
                </div>

            </div>
        </div>

        <Modal :show="isDivisionModalOpen" @close="isDivisionModalOpen = false">
            <div class="p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingDivision ? 'แก้ไขชื่อกอง' : 'เพิ่มกองใหม่' }}</h3>
                <input v-model="divisionForm.name" placeholder="ชื่อกอง (เช่น กองเทคโนโลยีสารสนเทศ)" class="w-full rounded-xl border-gray-300 mb-4" autofocus>
                <div class="flex justify-end gap-2">
                    <button @click="isDivisionModalOpen = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">ยกเลิก</button>
                    <button @click="submitDivision" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">บันทึก</button>
                </div>
            </div>
        </Modal>

        <Modal :show="isDepartmentModalOpen" @close="isDepartmentModalOpen = false">
            <div class="p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingDepartment ? 'แก้ไขชื่อแผนก' : 'เพิ่มแผนกใหม่' }}</h3>
                <input v-model="departmentForm.name" placeholder="ชื่อแผนก (เช่น แผนกพัฒนาระบบ)" class="w-full rounded-xl border-gray-300 mb-4" autofocus>
                <div class="flex justify-end gap-2">
                    <button @click="isDepartmentModalOpen = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">ยกเลิก</button>
                    <button @click="submitDepartment" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">บันทึก</button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
