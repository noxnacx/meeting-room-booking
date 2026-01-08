<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    capacity: '',
    description: '',
    status: 'active',
    color: '#3B82F6', // Default สีฟ้า
    image: null,
});

const submit = () => {
    form.post(route('admin.rooms.store'), { forceFormData: true });
};

const handleImage = (e) => {
    form.image = e.target.files[0];
};
</script>

<template>
    <Head title="เพิ่มห้องประชุม" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">เพิ่มห้องประชุมใหม่</h2></template>
        <div class="py-12 bg-gray-50">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700">ชื่อห้อง</label>
                            <input v-model="form.name" type="text" class="w-full mt-1 rounded-md border-gray-300 shadow-sm" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">รูปภาพห้อง</label>
                            <input type="file" @change="handleImage" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">ความจุ (คน)</label>
                                <input v-model="form.capacity" type="number" class="w-full mt-1 rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">สีประจำห้อง</label>
                                <input v-model="form.color" type="color" class="w-full h-10 mt-1 rounded-md border-gray-300 shadow-sm cursor-pointer">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">รายละเอียดเพิ่มเติม</label>
                            <textarea v-model="form.description" class="w-full mt-1 rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">สถานะ</label>
                            <select v-model="form.status" class="w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                <option value="active">เปิดใช้งาน (Active)</option>
                                <option value="maintenance">ปิดปรับปรุง (Maintenance)</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link :href="route('admin.rooms.index')" class="px-4 py-2 bg-gray-200 rounded-md text-gray-700">ยกเลิก</Link>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700" :disabled="form.processing">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
