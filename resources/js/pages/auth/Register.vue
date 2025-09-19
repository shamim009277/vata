<script lang="ts" setup>
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    business_name: '',
    store_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Sign Up" description="Already have an account?" login-text="Sign in here"
        :login-url="route('login')">

        <Head title="Register" />

        <div class="form-body">
            <form @submit.prevent="submit" class="row g-2">
                <div class="col-sm-12">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" :class="[form.errors.name ? 'border-danger mb-1' : '']" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="John" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="col-sm-12">
                    <Label for="business_name">Business/Companu Name</Label>
                    <Input id="business_name" type="text" :class="[form.errors.business_name ? 'border-danger mb-1' : '']" required autofocus :tabindex="1" autocomplete="off" v-model="form.business_name" placeholder="" />
                    <InputError :message="form.errors.business_name" />
                </div>
                <div class="col-sm-12">
                    <Label for="store_name">Store Name</Label>
                    <Input id="store_name" type="text" :class="[form.errors.store_name ? 'border-danger mb-1' : '']" required autofocus :tabindex="1" autocomplete="off" v-model="form.store_name" placeholder="" />
                    <InputError :message="form.errors.store_name" />
                </div>
                <div class="col-sm-12">
                    <Label for="phone">Phone</Label>
                    <Input id="phone" type="text" :class="[form.errors.phone ? 'border-danger mb-1' : '']" required autofocus :tabindex="1" autocomplete="off" v-model="form.phone" placeholder="Phone Number" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="col-12">
                    <Label for="email">Email Address</Label>
                    <Input id="email" type="email" :class="[form.errors.email ? 'border-danger mb-1' : '']" required autofocus :tabindex="1" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="col-12">
                    <Label for="password">Password</Label>
                    <div class="input-group" id="show_hide_password">
                        <Input id="password" type="password" class="border-end-0" :class="[form.errors.password ? 'border-danger mb-1' : '']" required :tabindex="2" autocomplete="current-password" v-model="form.password" placeholder="Password" />
                        <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>
                <div class="col-12">
                    <Label for="password_confirmation">Confirm password</Label>
                    <div class="input-group" id="show_hide_password">
                        <Input id="password_confirmation" class="border-end-0" :class="[form.errors.password_confirmation ? 'border-danger mb-1' : '']" type="password" required :tabindex="4" autocomplete="new-password" v-model="form.password_confirmation" placeholder="Confirm password" />
                        <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
                        <label class="form-check-label" for="flexSwitchCheckChecked">I read and agree to Terms & Conditions</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <i v-else class='bx bx-user'></i>
                            Sign up
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthBase>
</template>

<style></style>
