<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

defineProps({ departments: Array });

const form = useForm({ name: '' });
const submit = () => {
    form.post(route('admin.departments.store'), { onSuccess: () => form.reset() });
};
const updateDept = (dept) => {
    const name = prompt('แก้ไขชื่อแผนก:', dept.name);
    if (name && name !== dept.name) {
        router.put(route('admin.departments.update', dept.id), { name });
    }
};
const deleteDept = (id) => {
    if(confirm('ยืนยันลบแผนกนี้?')) router.delete(route('admin.departments.destroy', id));
};
</script>

<template>
    <Head title="จัดการแผนก" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">จัดการแผนก (Departments)</h2></template>
        <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6 flex gap-4">
                <input v-model="form.name" placeholder="ชื่อแผนก (เช่น ฝ่าย IT, ฝ่ายบัญชี)" class="flex-1 rounded-xl border-gray-300">
                <button @click="submit" class="bg-indigo-600 text-white px-4 rounded-xl font-bold hover:bg-indigo-700">เพิ่มแผนก</button>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div v-for="dept in departments" :key="dept.id" class="flex justify-between items-center p-4 border-b last:border-0 hover:bg-gray-50">
                    <div>
                        <span class="font-bold text-gray-800 text-lg">{{ dept.name }}</span>
                        <span class="ml-2 text-xs bg-gray-100 px-2 py-1 rounded-full text-gray-500">{{ dept.users_count }} คน</span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="updateDept(dept)" class="text-amber-500 hover:bg-amber-50 p-2 rounded-lg">แก้ไข</button>
                        <button @click="deleteDept(dept.id)" class="text-red-500 hover:bg-red-50 p-2 rounded-lg">ลบ</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
