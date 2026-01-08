<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

defineProps({ amenities: Array });

const form = useForm({ name: '', icon: '' });

const submit = () => {
    form.post(route('admin.amenities.store'), {
        onSuccess: () => form.reset(),
    });
};

const deleteItem = (id) => {
    if(confirm('ลบรายการนี้?')) router.delete(route('admin.amenities.destroy', id));
};
</script>

<template>
    <Head title="จัดการสิ่งอำนวยความสะดวก" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">จัดการสิ่งอำนวยความสะดวก (Amenities)</h2></template>

        <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex gap-4 mb-6">
                <input v-model="form.icon" placeholder="ไอคอน (เช่น 📶)" class="w-20 rounded-xl border-gray-300">
                <input v-model="form.name" placeholder="ชื่ออุปกรณ์ (เช่น WiFi 5G)" class="flex-1 rounded-xl border-gray-300">
                <button @click="submit" class="bg-indigo-600 text-white px-4 rounded-xl font-bold hover:bg-indigo-700">เพิ่ม</button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div v-for="item in amenities" :key="item.id" class="flex justify-between p-4 border-b last:border-0 hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl bg-gray-100 w-10 h-10 flex items-center justify-center rounded-lg">{{ item.icon }}</span>
                        <span class="font-medium text-gray-700">{{ item.name }}</span>
                    </div>
                    <button @click="deleteItem(item.id)" class="text-red-500 hover:bg-red-50 p-2 rounded-lg">ลบ</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
