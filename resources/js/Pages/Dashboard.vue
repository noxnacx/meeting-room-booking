<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    rooms: Array
});
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
                         class="bg-white overflow-hidden shadow-sm hover:shadow-lg rounded-2xl border border-gray-100 transition duration-300 flex flex-col h-full group">

                        <div class="h-48 w-full bg-gray-200 relative overflow-hidden">
                            <img
                                v-if="room.image_path"
                                :src="`/storage/${room.image_path}`"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                alt="Room Image"
                            >
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>

                            <div class="absolute top-3 right-3">
                                <span v-if="room.status === 'active'" class="bg-green-500/90 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm backdrop-blur-sm">
                                    พร้อมใช้งาน
                                </span>
                                <span v-else class="bg-gray-800/80 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm backdrop-blur-sm flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" /></svg>
                                    ปิดปรับปรุง
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-grow flex flex-col">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-3 h-3 rounded-full shadow-sm" :style="{ backgroundColor: room.color }"></div>
                                <h3 class="text-lg font-bold text-gray-800">{{ room.name }}</h3>
                            </div>

                            <p class="text-gray-500 text-sm mb-4 line-clamp-2 flex-grow h-10">
                                {{ room.description || 'ห้องประชุมมาตรฐาน พร้อมอุปกรณ์ครบครัน' }}
                            </p>

                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-50">
                                <span class="bg-gray-50 text-gray-600 text-xs px-3 py-1.5 rounded-lg font-medium flex items-center gap-1 border border-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 2.636.818.818 0 01-.36.98A7.465 7.465 0 0114.5 16z" /></svg>
                                    จุได้ {{ room.capacity }} คน
                                </span>

                                <Link
                                    v-if="room.status === 'active'"
                                    :href="route('bookings.create', room.id)"
                                    class="bg-indigo-600 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 hover:shadow-lg transition transform active:scale-95 flex items-center gap-2"
                                >
                                    จองห้องนี้ ➜
                                </Link>

                                <button
                                    v-else
                                    disabled
                                    class="bg-gray-100 text-gray-400 px-5 py-2 rounded-xl text-sm font-bold cursor-not-allowed border border-gray-200 flex items-center gap-2 select-none"
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
