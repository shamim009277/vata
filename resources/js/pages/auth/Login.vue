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
    <AuthBase title="Sign in" description="Don't have an account yet?" login-text="Sign up here" :login-url="route('register')">

        <Head title="Log in" />
        <div class="form-body">
            <form @submit.prevent="submit" class="row g-3">
                <div class="col-12">
                    <Label for="email">Email Address</Label>
                    <Input id="email" type="email" :class="[form.errors.email ?'border-danger mb-1':'']" required autofocus :tabindex="1" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="col-12">
                    <Label for="password">Enter Password</Label>
                    <div class="input-group" id="show_hide_password">
                        <Input id="password" type="password" class="border-end-0" :class="[form.errors.password ?'border-danger mb-1':'']" required :tabindex="2" autocomplete="current-password" v-model="form.password" placeholder="Password" />
                        <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" v-model="form.remember" id="flexSwitchCheckChecked" checked>
                        <label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <Link :href="route('password.request')">Forgot Password ?</Link>
                </div>
                <div class="col-12">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <i v-else class="bx bxs-lock-open"></i>
                            Sign in
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthBase>
</template>

<style></style>
