<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, watch, onMounted } from 'vue'; // ไม่ต้องใช้ ref แล้ว

const props = defineProps({
    user: Object,
    divisions: Array
});

const form = useForm({
    _method: 'put',
    name: props.user.name,
    nickname: props.user.nickname,
    role: props.user.role,
    department_id: props.user.department_id,
    division_id: props.user.division_id, // ✅ รับค่า division_id มาแก้ไข
});

// ✅ ไม่ใช้ ref(null) แล้ว แต่ใช้ form.division_id แทน
// const selectedDivisionId = ref(null); <--- ลบอันนี้ทิ้งได้เลย

// 1. ฟังก์ชันหา "กอง" (ถ้า User มีแต่ Department แต่ไม่มี Division ใน DB ระบบจะ Auto-fill ให้)
const initDivision = () => {
    // ถ้ามี division_id อยู่แล้ว ก็ใช้ได้เลย
    if (form.division_id) return;

    // ถ้าไม่มี division_id แต่มี department_id ให้ลองไปค้นดูว่าอยู่กองไหน
    if (props.user.department_id) {
        const foundDiv = props.divisions.find(div =>
            div.departments.some(dept => dept.id === props.user.department_id)
        );
        if (foundDiv) {
            form.division_id = foundDiv.id;
        }
    }
};

onMounted(() => {
    initDivision();
});

// 2. กรองแผนกตาม form.division_id
const availableDepartments = computed(() => {
    if (!form.division_id) return [];
    const div = props.divisions.find(d => d.id === form.division_id);
    return div ? div.departments : [];
});

// 3. Watcher: ถ้าเปลี่ยนกอง ให้เช็คว่าแผนกเดิมยังเข้ากันได้ไหม
watch(() => form.division_id, (newVal) => {
    const isDeptValid = props.divisions
        .find(div => div.id === newVal)
        ?.departments.some(dept => dept.id === form.department_id);

    if (!isDeptValid) {
        form.department_id = null; // ถ้าแผนกเดิมไม่อยู่ในกองใหม่ ให้เคลียร์แผนกทิ้ง
    }
});

const submit = () => {
    form.post(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head title="แก้ไขผู้ใช้งาน" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">แก้ไขข้อมูลผู้ใช้: {{ user.name }}</h2></template>
        <div class="py-12 bg-gray-50">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow-sm rounded-2xl border border-gray-100">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">ชื่อ - นามสกุล</label>
                            <input v-model="form.name" type="text" class="w-full rounded-xl border-gray-300 shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">ชื่อเล่น</label>
                            <input v-model="form.nickname" type="text" class="w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>

                        <hr class="border-gray-100">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">1. เลือกกอง (Division)</label>
                                <select v-model="form.division_id" class="w-full rounded-xl border-gray-300 shadow-sm bg-gray-50">
                                    <option :value="null">- กรุณาเลือกกอง -</option>
                                    <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">2. เลือกแผนก (Dept)</label>
                                <select v-model="form.department_id" :disabled="!form.division_id" class="w-full rounded-xl border-gray-300 shadow-sm disabled:bg-gray-100">
                                    <option :value="null">- ไม่ระบุแผนก -</option>
                                    <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">ตำแหน่ง</label>
                            <select v-model="form.role" class="w-full rounded-xl border-gray-300 shadow-sm">
                                <option value="user">User</option>
                                <option value="sub_admin">Sub Admin</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                            <Link :href="route('admin.users.index')" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-50">ยกเลิก</Link>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg" :disabled="form.processing">บันทึก</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
