<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    rooms: Array,
    allAmenities: Object // รับข้อมูลอุปกรณ์ทั้งหมดมาเพื่อแปลง ID เป็น Icon
});

// Helper แปลง ID เป็น Icon/Name
const getAmenityData = (id) => {
    // ป้องกัน Error กรณีข้อมูลเก่า หรือหา ID ไม่เจอ
    if (!props.allAmenities || !props.allAmenities[id]) {
        return { icon: '❓', name: 'Unknown' };
    }
    return props.allAmenities[id];
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                เลือกห้องประชุมที่ต้องการจอง
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                    <div v-for="room in rooms" :key="room.id"
                         class="bg-white overflow-hidden shadow-sm hover:shadow-xl rounded-2xl border border-gray-100 transition-all duration-300 flex flex-col h-full group">

                        <div class="h-56 w-full bg-gray-200 relative overflow-hidden">
                            <img
                                v-if="room.image_path"
                                :src="`/storage/${room.image_path}`"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                            >
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 opacity-50"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                            </div>

                            <div class="absolute top-4 right-4">
                                <span v-if="room.status === 'active'" class="bg-white/90 text-green-700 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm backdrop-blur-sm flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> พร้อมใช้งาน
                                </span>
                                <span v-else class="bg-gray-900/90 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm backdrop-blur-sm flex items-center gap-1">
                                    ⛔ ปิดปรับปรุง
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-grow flex flex-col">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-3 h-3 rounded-full shadow-sm" :style="{ backgroundColor: room.color }"></div>
                                <h3 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ room.name }}</h3>
                            </div>

                            <p class="text-gray-500 text-sm mb-4 line-clamp-2 h-10 leading-relaxed">
                                {{ room.description || 'ห้องประชุมมาตรฐาน พร้อมอุปกรณ์ครบครัน' }}
                            </p>

                            <div class="flex flex-wrap gap-2 mb-6">
                                <template v-for="amId in room.amenities" :key="amId">
                                    <span v-if="allAmenities[amId]"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gray-50 text-xs font-semibold text-gray-600 border border-gray-100"
                                        :title="allAmenities[amId].name"
                                    >
                                        <span class="text-sm">{{ allAmenities[amId].icon }}</span>
                                        <span class="">{{ allAmenities[amId].name }}</span>
                                    </span>
                                </template>
                            </div>

                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                                <span class="bg-gray-50 text-gray-600 text-xs px-3 py-1.5 rounded-lg font-bold flex items-center gap-1 border border-gray-200">
                                    👤 {{ room.capacity }} ที่นั่ง
                                </span>

                                <Link
                                    v-if="room.status === 'active'"
                                    :href="route('bookings.create', room.id)"
                                    class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-indigo-200 hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2"
                                >
                                    จองห้องนี้ ➜
                                </Link>

                                <button
                                    v-else
                                    disabled
                                    class="bg-gray-100 text-gray-400 px-5 py-2.5 rounded-xl text-sm font-bold cursor-not-allowed border border-gray-200 flex items-center gap-2 select-none"
                                >
                                    ⛔ ปิดปรับปรุง
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
