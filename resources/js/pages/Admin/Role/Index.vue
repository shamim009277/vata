<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    roles: Array,
    permissions: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    name: '',
    permissions: [],
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.permissions = [];
    showModal.value = true;
};

const openEditModal = (role) => {
    isEditing.value = true;
    form.id = role.id;
    form.name = role.name;
    form.permissions = role.permissions.map(p => p.name);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.roles.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.roles.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteRole = (id) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('admin.roles.destroy', id));
    }
};
</script>

<template>
    <Head title="Role Management" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <i class="fadeIn animated bx bx-shield"></i> রোল ম্যানেজমেন্ট
                            </h6>
                            <button @click="openCreateModal" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> নতুন রোল</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>রোল নাম</th>
                                        <th>পারমিশন</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="role in roles" :key="role.id">
                                        <td>{{ role.name }}</td>
                                        <td>
                                            <span v-for="perm in role.permissions" :key="perm.id" class="badge bg-secondary me-1">{{ perm.name }}</span>
                                        </td>
                                        <td>
                                            <button @click="openEditModal(role)" class="btn btn-sm btn-info me-2"><i class="bx bx-edit"></i></button>
                                            <button @click="deleteRole(role.id)" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
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
                        <h5 class="modal-title">{{ isEditing ? 'রোল এডিট করুন' : 'নতুন রোল তৈরি করুন' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">রোল নাম</label>
                                <input v-model="form.name" type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">পারমিশন সিলেক্ট করুন</label>
                                <div class="row">
                                    <div class="col-md-3" v-for="perm in permissions" :key="perm.id">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" :value="perm.name" v-model="form.permissions" :id="'perm_' + perm.id">
                                            <label class="form-check-label" :for="'perm_' + perm.id">
                                                {{ perm.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="button" class="btn btn-secondary me-2" @click="closeModal">বন্ধ করুন</button>
                                <button type="submit" class="btn btn-primary">{{ isEditing ? 'আপডেট করুন' : 'সেভ করুন' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
