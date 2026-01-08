<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    booking: Object
});

// ฟังก์ชันแปลงวันที่เวลาให้สวยงาม
const formatDateTime = (dateString) => {
    const options = {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('th-TH', options);
};
</script>

<template>
    <Head title="รายละเอียดการจอง" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                รายละเอียดการจอง
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100">

                    <div class="bg-indigo-600 p-6 text-white flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-bold">{{ booking.title }}</h3>
                            <div class="flex items-center gap-2 mt-2 text-indigo-100">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                    <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-lg font-medium">{{ booking.room.name }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                             <span class="bg-indigo-500 text-xs px-2 py-1 rounded-md border border-indigo-400">Booking ID: #{{ booking.id }}</span>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">

                        <div class="flex gap-4 border-b pb-6">
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 uppercase font-bold tracking-wider">เวลาเริ่ม</label>
                                <p class="text-gray-800 font-medium text-lg mt-1">
                                    {{ formatDateTime(booking.start_time) }}
                                </p>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500 uppercase font-bold tracking-wider">เวลาสิ้นสุด</label>
                                <p class="text-gray-800 font-medium text-lg mt-1">
                                    {{ formatDateTime(booking.end_time) }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 uppercase font-bold tracking-wider">ผู้จองห้องประชุม (Host)</label>
                            <div class="flex items-center gap-3 mt-3">
                                <img :src="booking.user.avatar ? `/storage/${booking.user.avatar}` : `https://ui-avatars.com/api/?name=${booking.user.name}`" class="w-12 h-12 rounded-full border border-gray-200">
                                <div>
                                    <p class="font-bold text-gray-900">{{ booking.user.name }}</p>
                                    <p class="text-sm text-gray-500">{{ booking.user.email }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-3 block">
                                ผู้เข้าร่วมประชุม ({{ booking.participants.length }} คน)
                            </label>

                            <div v-if="booking.participants.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="user in booking.participants" :key="user.id" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                    <img :src="user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${user.name}`" class="w-8 h-8 rounded-full">
                                    <div class="overflow-hidden">
                                        <p class="font-medium text-sm text-gray-900 truncate">{{ user.name }}</p>
                                        <p v-if="user.nickname" class="text-xs text-indigo-600">({{ user.nickname }})</p>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-400 italic text-sm">ไม่มีผู้เข้าร่วมอื่น</p>
                        </div>

                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-100">
                        <Link :href="route('bookings.index')" class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                            &larr; กลับหน้ารายการ
                        </Link>

                        <div v-if="$page.props.auth.user.id === booking.user_id || $page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'sub_admin'" class="space-x-3">
                            <Link :href="route('bookings.edit', booking.id)" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 shadow-sm">
                                แก้ไขข้อมูล
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
