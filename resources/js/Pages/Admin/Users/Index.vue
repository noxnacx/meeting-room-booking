<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: Array
});

// --- Logic การเรียงลำดับ (Sorting) ---
// กำหนดคะแนนความใหญ่ของ Role
const roleScore = {
    'admin': 3,
    'sub_admin': 2,
    'user': 1
};

// Computed property เพื่อเรียงลำดับ User ตาม Role (มากไปน้อย)
const sortedUsers = computed(() => {
    return [...props.users].sort((a, b) => {
        const scoreA = roleScore[a.role] || 0;
        const scoreB = roleScore[b.role] || 0;
        return scoreB - scoreA; // เรียงจากมากไปน้อย (Admin ขึ้นก่อน)
    });
});

// --- ฟังก์ชันลบ User ---
const deleteUser = (user) => {
    // เช็คว่าลบตัวเองหรือเปล่า
    if (user.id === usePage().props.auth.user.id) {
        alert('คุณไม่สามารถลบบัญชีตัวเองได้');
        return;
    }

    if (confirm(`คุณแน่ใจหรือไม่ที่จะลบผู้ใช้: ${user.name}?`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

// ฟังก์ชันแปลงชื่อ Role ให้สวยงาม
const formatRole = (role) => {
    switch(role) {
        case 'admin': return '👑 Administrator';
        case 'sub_admin': return '🛡️ Sub-Admin';
        case 'user': return '👤 User';
        default: return role;
    }
};
</script>

<template>
    <Head title="จัดการผู้ใช้งาน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">จัดการผู้ใช้งาน (Users & Roles)</h2>
                <Link :href="route('admin.users.create')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700 transition">
                    + เพิ่มผู้ใช้งาน
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ชื่อ - นามสกุล</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">อีเมล</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ระดับ (Role)</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in sortedUsers" :key="user.id" class="hover:bg-indigo-50/30 transition">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full object-cover border border-gray-200" :src="user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${user.name}&background=random`" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ user.name }}</div>
                                            <div class="text-xs text-gray-500">{{ user.nickname ? `(${user.nickname})` : '' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ user.email }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                        :class="{
                                            'bg-purple-100 text-purple-800 border-purple-200': user.role === 'admin',
                                            'bg-blue-100 text-blue-800 border-blue-200': user.role === 'sub_admin',
                                            'bg-gray-100 text-gray-800 border-gray-200': user.role === 'user'
                                        }"
                                    >
                                        {{ formatRole(user.role) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">

                                        <Link
                                            :href="route('admin.users.edit', user.id)"
                                            class="w-9 h-9 flex items-center justify-center rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-100 hover:text-amber-600 transition duration-200 border border-transparent hover:border-amber-200"
                                            title="แก้ไขข้อมูล"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </Link>

                                        <button
                                            @click="deleteUser(user)"
                                            class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 transition duration-200 border border-transparent hover:border-red-200"
                                            title="ลบผู้ใช้งาน"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
