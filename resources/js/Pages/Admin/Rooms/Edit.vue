<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ room: Object });

const form = useForm({
    _method: 'put', // เทคนิค Laravel สำหรับ Upload file ใน Update method
    name: props.room.name,
    capacity: props.room.capacity,
    description: props.room.description,
    status: props.room.status,
    color: props.room.color,
    image: null,
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
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div v-if="room.image_path" class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">รูปปัจจุบัน</label>
                            <img :src="`/storage/${room.image_path}`" class="w-full h-48 object-cover rounded-lg border">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">เปลี่ยนรูปภาพ (ถ้ามี)</label>
                            <input type="file" @change="e => form.image = e.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-bold">ชื่อห้อง</label><input v-model="form.name" type="text" class="w-full rounded-md border-gray-300 shadow-sm"></div>
                            <div><label class="block text-sm font-bold">ความจุ</label><input v-model="form.capacity" type="number" class="w-full rounded-md border-gray-300 shadow-sm"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-bold">สี</label><input v-model="form.color" type="color" class="w-full h-10 rounded-md cursor-pointer border-gray-300"></div>
                            <div>
                                <label class="block text-sm font-bold">สถานะ</label>
                                <select v-model="form.status" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="active">Active</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>

                        <div><label class="block text-sm font-bold">รายละเอียด</label><textarea v-model="form.description" class="w-full rounded-md border-gray-300 shadow-sm"></textarea></div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link :href="route('admin.rooms.index')" class="px-4 py-2 bg-gray-200 rounded-md text-gray-700">ยกเลิก</Link>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
