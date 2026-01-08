<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ rooms: Array });

const deleteRoom = (id) => {
    if (confirm('ยืนยันการลบห้องประชุมนี้?')) {
        router.delete(route('admin.rooms.destroy', id));
    }
};
</script>

<template>
    <Head title="จัดการห้องประชุม" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">จัดการห้องประชุม</h2>
                <Link :href="route('admin.rooms.create')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700">
                    + เพิ่มห้องประชุม
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">รูปภาพ</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">ชื่อห้อง</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">ความจุ</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">สถานะ</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="room in rooms" :key="room.id">
                                <td class="px-6 py-4">
                                    <img
                                        :src="room.image_path ? `/storage/${room.image_path}` : 'https://via.placeholder.com/150?text=No+Image'"
                                        class="h-12 w-20 object-cover rounded-lg border border-gray-200"
                                    >
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: room.color }"></div>
                                        {{ room.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ room.capacity }} คน</td>
                                <td class="px-6 py-4">
                                    <span v-if="room.status === 'active'" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">เปิดใช้งาน</span>
                                    <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ปิดปรับปรุง</span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Link :href="route('admin.rooms.edit', room.id)" class="text-indigo-600 hover:text-indigo-900">แก้ไข</Link>
                                    <button @click="deleteRoom(room.id)" class="text-red-600 hover:text-red-900">ลบ</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
