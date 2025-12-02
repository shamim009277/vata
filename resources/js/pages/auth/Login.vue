<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { LoaderCircle } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
<template>
    <AuthBase title="আপনাকে স্বাগতম !" description="আপনার ব্যবসা পরিচালনার জন্য লগইন করুন"
        :login-url="route('register')">

        <Head title="লগইন" />
        <div class="form-body">
            <form @submit.prevent="submit" class="row g-3">
                <div class="col-12 position-relative">
                    <Label for="email">ইউজারনেম</Label>

                    <div class="position-relative mb-1">
                        <Input id="email" type="email" v-model="form.email" required autofocus autocomplete="email"
                            placeholder="ইউজারনেম" :tabindex="1" :class="[
                                'form-control pe-5',
                                form.errors.email ? 'is-invalid border-danger' : ''
                            ]" />

                        <i v-if="form.errors.email"
                            class="bx bx-error-circle text-danger position-absolute top-50 end-0 translate-middle-y me-3"
                            style="pointer-events: none;"></i>
                    </div>

                    <InputError :message="form.errors.email" />
                </div>
                <div class="col-12">
                    <Label for="password">পাসওয়ার্ড</Label>
                    <div class="input-group" id="show_hide_password">
                        <Input id="password" type="password" class="border-end-0"
                            :class="[form.errors.password ? 'border-danger mb-1' : '']" required :tabindex="2"
                            autocomplete="current-password" v-model="form.password" placeholder="পাসওয়ার্ড" />
                        <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" v-model="form.remember"
                            id="flexSwitchCheckChecked" checked>
                        <label class="form-check-label" for="flexSwitchCheckChecked">আমাকে মনে রেখো</label>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <Link :href="route('password.request')">পাসওয়ার্ড ভুলে গেছেন ?</Link>
                </div>
                <div class="col-12">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <i v-else class="bx bxs-lock-open"></i>
                            লগইন
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthBase>
</template>
