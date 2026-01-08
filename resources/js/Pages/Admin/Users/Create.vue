<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    divisions: Array
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '', // ตัวแปรนี้ต้องมีเพื่อให้ Controller เช็ค confirmed
    nickname: '',
    role: 'user',
    department_id: null,
});

const selectedDivisionId = ref(null);
const availableDepartments = computed(() => {
    if (!selectedDivisionId.value) return [];
    const div = props.divisions.find(d => d.id === selectedDivisionId.value);
    return div ? div.departments : [];
});

watch(selectedDivisionId, () => { form.department_id = null; });

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>

<template>
    <Head title="เพิ่มผู้ใช้งานใหม่" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">เพิ่มผู้ใช้งานใหม่</h2></template>
        <div class="py-12 bg-gray-50">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-4">

                        <div>
                            <label class="block text-sm font-bold text-gray-700">ชื่อ - นามสกุล</label>
                            <input v-model="form.name" type="text" class="w-full rounded-xl border-gray-300 mt-1 focus:ring-indigo-500 focus:border-indigo-500" :class="{'border-red-500': form.errors.name}" required />
                            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1 font-bold">{{ form.errors.name }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">อีเมล (Login)</label>
                                <input v-model="form.email" type="email" class="w-full rounded-xl border-gray-300 mt-1 focus:ring-indigo-500 focus:border-indigo-500" :class="{'border-red-500': form.errors.email}" required />
                                <div v-if="form.errors.email" class="text-red-600 text-sm mt-1 font-bold">{{ form.errors.email }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">ชื่อเล่น</label>
                                <input v-model="form.nickname" type="text" class="w-full rounded-xl border-gray-300 mt-1" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">รหัสผ่าน (ขั้นต่ำ 8 ตัว)</label>
                                <input v-model="form.password" type="password" class="w-full rounded-xl border-gray-300 mt-1 focus:ring-indigo-500 focus:border-indigo-500" :class="{'border-red-500': form.errors.password}" required />
                                <div v-if="form.errors.password" class="text-red-600 text-sm mt-1 font-bold">{{ form.errors.password }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">ยืนยันรหัสผ่าน</label>
                                <input v-model="form.password_confirmation" type="password" class="w-full rounded-xl border-gray-300 mt-1 focus:ring-indigo-500 focus:border-indigo-500" required />
                                </div>
                        </div>

                        <hr class="border-gray-100">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">1. กอง (Division)</label>
                                <select v-model="selectedDivisionId" class="w-full rounded-xl border-gray-300 mt-1 bg-gray-50">
                                    <option :value="null">- เลือกกอง -</option>
                                    <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">2. แผนก (Department)</label>
                                <select v-model="form.department_id" :disabled="!selectedDivisionId" class="w-full rounded-xl border-gray-300 mt-1 disabled:bg-gray-100">
                                    <option :value="null">- เลือกแผนก -</option>
                                    <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">สิทธิ์การใช้งาน</label>
                            <select v-model="form.role" class="w-full rounded-xl border-gray-300 mt-1">
                                <option value="user">User</option>
                                <option value="sub_admin">Sub Admin</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link :href="route('admin.users.index')" class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 font-bold hover:bg-gray-200">ยกเลิก</Link>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-all" :disabled="form.processing">
                                <span v-if="form.processing">กำลังบันทึก...</span>
                                <span v-else>บันทึกผู้ใช้งาน</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
