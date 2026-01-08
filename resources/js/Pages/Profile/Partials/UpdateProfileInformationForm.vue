<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    nickname: user.nickname || '', // เพิ่ม nickname
    avatar: null, // เพิ่ม avatar (เป็นไฟล์)
});

const submit = () => {
    // ต้องส่งแบบ FormData เพื่ออัปโหลดไฟล์
    form.post(route('profile.update'), {
        _method: 'patch', // Laravel Resource ใช้ Patch แต่ Inertia Upload ต้องใช้ Post หลอก
        forceFormData: true,
    });
};

// ฟังก์ชันแสดงตัวอย่างรูปเมื่อเลือกไฟล์
const previewAvatar = (e) => {
    const file = e.target.files[0];
    form.avatar = file;
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">ข้อมูลโปรไฟล์</h2>
            <p class="mt-1 text-sm text-gray-600">
                อัปเดตข้อมูลบัญชี ชื่อเล่น และรูปโปรไฟล์ของคุณ
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6" enctype="multipart/form-data">

            <div>
                <InputLabel value="รูปโปรไฟล์ (Avatar)" />

                <div class="flex items-center space-x-4 mt-2">
                    <div class="shrink-0">
                        <img
                            class="h-16 w-16 object-cover rounded-full border"
                            :src="user.avatar ? `/storage/${user.avatar}` : 'https://ui-avatars.com/api/?name=' + user.name"
                            alt="Current profile photo"
                        />
                    </div>
                    <input
                        type="file"
                        @change="previewAvatar"
                        class="block w-full text-sm text-slate-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.avatar" />
            </div>

            <div>
                <InputLabel for="name" value="ชื่อ - นามสกุล" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="nickname" value="ชื่อเล่น (Nickname)" />
                <TextInput
                    id="nickname"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nickname"
                    placeholder="เช่น Boss, A, Somchai"
                />
                <InputError class="mt-2" :message="form.errors.nickname" />
            </div>

            <div>
                <InputLabel for="email" value="อีเมล" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>
                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">บันทึกข้อมูล</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">บันทึกเรียบร้อย.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
