<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

// 1. รับค่า amenitiesOptions จาก Controller
const props = defineProps({
    room: Object,
    amenitiesOptions: Array
});

const form = useForm({
    _method: 'put',
    name: props.room.name,
    capacity: props.room.capacity,
    description: props.room.description,
    status: props.room.status,
    color: props.room.color,
    image: null,
    amenities: props.room.amenities || [],
});

const submit = () => {
    form.post(route('admin.rooms.update', props.room.id), { forceFormData: true });
};
</script>

<template>
    <Head title="แก้ไขห้องประชุม" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">แก้ไข: {{ room.name }}</h2></template>
        <div class="py-12 bg-gray-50">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div v-if="room.image_path" class="relative group">
                            <img :src="`/storage/${room.image_path}`" class="w-full h-48 object-cover rounded-xl border border-gray-200 shadow-sm">
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition rounded-xl pointer-events-none font-bold">
                                รูปปัจจุบัน
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">เปลี่ยนรูปภาพ (ถ้ามี)</label>
                            <input type="file" @change="e => form.image = e.target.files[0]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">ชื่อห้อง</label>
                                <input v-model="form.name" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">ความจุ (คน)</label>
                                <input v-model="form.capacity" type="number" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">สีประจำห้อง</label>
                                <div class="flex items-center gap-2">
                                    <input v-model="form.color" type="color" class="h-10 w-14 rounded cursor-pointer border-0 p-0 shadow-sm">
                                    <span class="text-xs text-gray-500">{{ form.color }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">สถานะ</label>
                                <select v-model="form.status" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                    <option value="active">🟢 เปิดใช้งาน (Active)</option>
                                    <option value="maintenance">🔴 ปิดปรับปรุง (Maintenance)</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-3">สิ่งอำนวยความสะดวก</label>

                            <div v-if="amenitiesOptions && amenitiesOptions.length > 0" class="grid grid-cols-2 gap-3">
                                <label v-for="item in amenitiesOptions" :key="item.id" class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded-lg transition border border-transparent hover:border-gray-200">
                                    <input type="checkbox" :value="item.id" v-model="form.amenities" class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 w-5 h-5">
                                    <span class="text-xl">{{ item.icon }}</span>
                                    <span class="text-sm text-gray-700 font-medium">{{ item.name }}</span>
                                </label>
                            </div>

                            <div v-else class="text-center text-gray-400 text-sm py-2">
                                ยังไม่มีข้อมูลอุปกรณ์ (ไปเพิ่มที่เมนูจัดการสิ่งอำนวยความสะดวก)
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">รายละเอียดเพิ่มเติม</label>
                            <textarea v-model="form.description" rows="3" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <Link :href="route('admin.rooms.index')" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-50">ยกเลิก</Link>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200" :disabled="form.processing">บันทึกการแก้ไข</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
