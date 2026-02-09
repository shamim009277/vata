<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePermission } from '@/Composables/usePermission';

const { hasPermission } = usePermission();

const props = defineProps({
    users: Array,
    roles: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.roles = [];
    showModal.value = true;
};

const openEditModal = (user) => {
    isEditing.value = true;
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.password_confirmation = '';
    form.roles = user.roles.map(r => r.name);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.users.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.destroy', id));
    }
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <i class="fadeIn animated bx bx-user"></i> ইউজার ম্যানেজমেন্ট
                            </h6>
                            <button v-if="hasPermission('admin.users.create')" @click="openCreateModal" class="btn btn-primary btn-sm"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i> নতুন ইউজার</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>SL</th>
                                        <th>নাম</th>
                                        <th>ইমেইল</th>
                                        <th>রোল</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user, index) in users" :key="user.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ user.name }}</td>
                                        <td>{{ user.email }}</td>
                                        <td>
                                            <span v-for="role in user.roles" :key="role.id" class="badge bg-secondary me-1">{{ role.name }}</span>
                                        </td>
                                        <td>
                                            <a v-if="hasPermission('admin.users.edit')" @click="openEditModal(user)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                            <a v-if="hasPermission('admin.users.destroy')" @click="deleteUser(user.id)" class="text-danger ms-2" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'ইউজার এডিট করুন' : 'নতুন ইউজার তৈরি করুন' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">নাম</label>
                                    <input v-model="form.name" type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ইমেইল</label>
                                    <input v-model="form.email" type="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">পাসওয়ার্ড</label>
                                    <input v-model="form.password" type="password" class="form-control" :required="!isEditing">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">পাসওয়ার্ড নিশ্চিত করুন</label>
                                    <input v-model="form.password_confirmation" type="password" class="form-control" :required="!isEditing">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">রোল সিলেক্ট করুন</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="form-check" v-for="role in roles" :key="role.id">
                                            <input class="form-check-input" type="checkbox" :value="role.name" v-model="form.roles" :id="'role_' + role.id">
                                            <label class="form-check-label" :for="'role_' + role.id">
                                                {{ role.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="button" class="btn btn-secondary me-2" @click="closeModal"><i class="bx bx-x"></i> বন্ধ করুন</button>
                                <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> {{ isEditing ? 'আপডেট করুন' : 'সেভ করুন' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
