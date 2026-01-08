<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    user: Object,
    divisions: Array // รับข้อมูลโครงสร้างกอง+แผนกมา
});

const form = useForm({
    _method: 'put',
    name: props.user.name,
    nickname: props.user.nickname,
    role: props.user.role,
    department_id: props.user.department_id,
});

// --- Logic แยกเลือก กอง -> แผนก ---
const selectedDivisionId = ref(null);

// 1. ตอนโหลดหน้า: ถ้า User มีแผนกอยู่แล้ว ให้หาว่าอยู่กองไหน แล้ว Set ค่าเริ่มต้นให้
onMounted(() => {
    if (props.user.department_id) {
        // วนหาว่า department_id นี้ อยู่ใน division ไหน
        for (const div of props.divisions) {
            if (div.departments.find(d => d.id === props.user.department_id)) {
                selectedDivisionId.value = div.id;
                break;
            }
        }
    }
});

// 2. กรองแผนกที่จะแสดง ตามกองที่เลือก
const availableDepartments = computed(() => {
    if (!selectedDivisionId.value) return [];
    const div = props.divisions.find(d => d.id === selectedDivisionId.value);
    return div ? div.departments : [];
});

// 3. ถ้าเปลี่ยนกอง ให้ล้างค่าแผนกเดิมทิ้ง (กัน User ลืมเปลี่ยน)
watch(selectedDivisionId, (newVal, oldVal) => {
    // เช็คว่าไม่ใช่การโหลดครั้งแรก (oldVal !== undefined) เพื่อไม่ให้ล้างค่าตอน Edit
    if (oldVal !== undefined) {
        form.department_id = null;
    }
});
</script>

<template>
    <Head title="แก้ไขผู้ใช้งาน" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">แก้ไขข้อมูลผู้ใช้: {{ user.name }}</h2>
        </template>

        <div class="py-12 bg-gray-50">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow-sm rounded-2xl border border-gray-100">
                    <form @submit.prevent="form.post(route('admin.users.update', user.id))" class="space-y-6">

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">ชื่อ - นามสกุล</label>
                            <input v-model="form.name" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500" required />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">ชื่อเล่น (Nickname)</label>
                            <input v-model="form.nickname" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500" />
                        </div>

                        <hr class="border-gray-100">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">1. เลือกกอง (Division)</label>
                                <select v-model="selectedDivisionId" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 bg-gray-50">
                                    <option :value="null">- กรุณาเลือกกอง -</option>
                                    <option v-for="div in divisions" :key="div.id" :value="div.id">
                                        {{ div.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">2. เลือกแผนก (Department)</label>
                                <select v-model="form.department_id" :disabled="!selectedDivisionId" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                                    <option :value="null">- เลือกแผนก -</option>
                                    <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.department_id" class="text-red-500 text-sm mt-1">{{ form.errors.department_id }}</div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">ตำแหน่ง (Role)</label>
                            <select v-model="form.role" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                <option value="user">User (ผู้ใช้ทั่วไป)</option>
                                <option value="sub_admin">Sub Admin (ผู้ช่วยผู้ดูแล)</option>
                                <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                            </select>
                            <p class="mt-2 text-xs text-gray-500">
                                * <b>Admin:</b> จัดการได้ทุกอย่าง | <b>Sub Admin:</b> จัดการห้องได้ แก้ไขคนอื่นไม่ได้
                            </p>
                        </div>

                        <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                            <Link :href="route('admin.users.index')" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-50 transition">
                                ยกเลิก
                            </Link>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition" :disabled="form.processing">
                                บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
