<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ user: Object });

const form = useForm({
    name: props.user.name,
    nickname: props.user.nickname,
    role: props.user.role,
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head title="แก้ไขผู้ใช้งาน" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">แก้ไขข้อมูลผู้ใช้: {{ user.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">ชื่อ - นามสกุล</label>
                            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">ชื่อเล่น (Nickname)</label>
                            <input v-model="form.nickname" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <div v-if="form.errors.nickname" class="text-red-500 text-sm mt-1">{{ form.errors.nickname }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">ตำแหน่ง (Role)</label>
                            <select v-model="form.role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="user">User (ผู้ใช้ทั่วไป)</option>
                                <option value="sub_admin">Sub Admin (ผู้ช่วยผู้ดูแล)</option>
                                <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">* Admin: จัดการได้ทุกอย่าง / Sub Admin: จัดการห้องได้ แต่แก้ Role คนอื่นไม่ได้</p>
                        </div>

                        <div class="flex justify-end space-x-2 border-t pt-4">
                            <Link :href="route('admin.users.index')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">ยกเลิก</Link>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700" :disabled="form.processing">
                                บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
