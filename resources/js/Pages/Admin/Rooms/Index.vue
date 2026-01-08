<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue'; // เรียกใช้ Modal ของระบบ

const props = defineProps({
    rooms: Array,
    allAmenities: Object // รับข้อมูลอุปกรณ์ทั้งหมดมา
});

// --- Logic ลบห้อง ---
const deleteRoom = (id) => {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบห้องนี้? ข้อมูลการจองที่เกี่ยวข้องจะหายไปทั้งหมด')) {
        router.delete(route('admin.rooms.destroy', id));
    }
};

// --- Logic Modal ดูรายละเอียด (View) ---
const isViewModalOpen = ref(false);
const selectedRoom = ref(null);

const openViewModal = (room) => {
    selectedRoom.value = room;
    isViewModalOpen.value = true;
};

const closeViewModal = () => {
    isViewModalOpen.value = false;
    selectedRoom.value = null;
};

// Helper ดึงข้อมูลอุปกรณ์จาก ID
const getAmenityData = (id) => {
    return props.allAmenities[id] || { icon: '✨', name: 'Unknown' };
};
</script>

<template>
    <Head title="จัดการห้องประชุม" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">จัดการห้องประชุม</h2>

                <div class="flex gap-3">
                    <Link :href="route('admin.amenities.index')" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition flex items-center gap-2">
                        <span>⚡</span> จัดการสิ่งอำนวยความสะดวก
                    </Link>

                    <Link :href="route('admin.rooms.create')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition transform hover:-translate-y-0.5 flex items-center gap-2">
                        <span>+</span> เพิ่มห้องประชุม
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-[20px] border border-gray-100 p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">ห้องประชุม</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">สถานะ</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">ความจุ</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="room in rooms" :key="room.id" class="group hover:bg-gray-50 transition duration-200">

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 flex-shrink-0 relative">
                                                <img class="h-12 w-12 rounded-xl object-cover shadow-sm border border-gray-100"
                                                     :src="room.image_path ? `/storage/${room.image_path}` : `https://ui-avatars.com/api/?name=${room.name}&background=random`">
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white" :style="{ backgroundColor: room.color }"></div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ room.name }}</div>
                                                <div class="text-xs text-gray-400 truncate max-w-xs">{{ room.description || '-' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="room.status === 'active'" class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-lg bg-green-50 text-green-600 border border-green-100">
                                            พร้อมใช้งาน
                                        </span>
                                        <span v-else class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-lg bg-red-50 text-red-600 border border-red-100">
                                            ปิดปรับปรุง
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                        👤 {{ room.capacity }} ที่นั่ง
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">

                                            <button
                                                @click="openViewModal(room)"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition"
                                                title="ดูรายละเอียด"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>

                                            <Link
                                                :href="route('admin.rooms.edit', room.id)"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-100 hover:text-amber-600 transition"
                                                title="แก้ไข"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                            </Link>

                                            <button
                                                @click="deleteRoom(room.id)"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 transition"
                                                title="ลบ"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="isViewModalOpen" @close="closeViewModal">
            <div v-if="selectedRoom" class="p-0 overflow-hidden rounded-2xl">

                <div class="relative h-48 bg-gray-200">
                     <img v-if="selectedRoom.image_path" :src="`/storage/${selectedRoom.image_path}`" class="w-full h-full object-cover">
                     <div v-else class="w-full h-full flex items-center justify-center text-gray-400">ไม่มีรูปภาพ</div>

                     <button @click="closeViewModal" class="absolute top-3 right-3 bg-white/80 p-1 rounded-full hover:bg-white text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                     </button>
                </div>

                <div class="p-6">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-8 rounded-full" :style="{ backgroundColor: selectedRoom.color }"></div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ selectedRoom.name }}</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-4 my-4">
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <span class="block text-xs text-gray-500 font-bold uppercase">ความจุ</span>
                            <span class="text-lg font-bold text-gray-800">👤 {{ selectedRoom.capacity }} คน</span>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <span class="block text-xs text-gray-500 font-bold uppercase">สถานะ</span>
                            <span v-if="selectedRoom.status === 'active'" class="text-lg font-bold text-green-600">พร้อมใช้งาน</span>
                            <span v-else class="text-lg font-bold text-red-500">ปิดปรับปรุง</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-bold text-gray-700 mb-2">สิ่งอำนวยความสะดวก</h3>
                        <div v-if="selectedRoom.amenities && selectedRoom.amenities.length > 0" class="flex flex-wrap gap-2">
                            <span v-for="amId in selectedRoom.amenities" :key="amId"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 text-sm font-semibold text-indigo-700 border border-indigo-100">
                                <span>{{ getAmenityData(amId).icon }}</span>
                                <span>{{ getAmenityData(amId).name }}</span>
                            </span>
                        </div>
                        <div v-else class="text-gray-400 text-sm italic">ไม่มีข้อมูลอุปกรณ์</div>
                    </div>

                    <div class="mb-4">
                         <h3 class="font-bold text-gray-700 mb-1">รายละเอียด</h3>
                         <p class="text-gray-600 text-sm leading-relaxed">{{ selectedRoom.description || '-' }}</p>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <Link :href="route('admin.rooms.edit', selectedRoom.id)" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm flex items-center gap-1">
                            แก้ไขข้อมูลห้องนี้ ➜
                        </Link>
                    </div>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
