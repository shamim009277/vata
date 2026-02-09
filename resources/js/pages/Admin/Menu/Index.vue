<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';
import { usePermission } from '@/Composables/usePermission';

const { hasPermission } = usePermission();

const props = defineProps({
    menus: Array,
    allMenus: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    title: '',
    url: '',
    route: '',
    icon: '',
    parent_id: null,
    order: 0,
    permission_name: '',
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.permission_name = ''; // Clear permission name when creating new menu
    showModal.value = true;
};

const openEditModal = (menu) => {
    isEditing.value = true;
    form.id = menu.id;
    form.title = menu.title;
    form.url = menu.url;
    form.route = menu.route;
    form.icon = menu.icon;
    form.parent_id = menu.parent_id;
    form.order = menu.order;
    form.permission_name = menu.permission_name;
    form.is_active = Boolean(menu.is_active);
    showModal.value = true;
};

// Auto-prefix permission names with menu title
const autoPrefixPermissions = () => {
    if (!form.title) return;
    
    const menuPrefix = form.title.toLowerCase().replace(/\s+/g, '_');
    const currentPermissions = form.permission_name ? form.permission_name.split(',').map(p => p.trim()) : [];
    
    // Remove existing prefixes from permissions
    const cleanedPermissions = currentPermissions.map(perm => {
        // Remove any existing menu prefix
        return perm.replace(/^[a-z0-9_]+_/, '');
    }).filter(p => p.length > 0);
    
    // Add menu prefix to all permissions
    const prefixedPermissions = cleanedPermissions.map(perm => {
        return `${menuPrefix}_${perm}`;
    });
    
    form.permission_name = prefixedPermissions.join(', ');
};

// Watch for title changes to update permission names
watch(() => form.title, () => {
    if (form.permission_name && !isEditing.value) {
        autoPrefixPermissions();
    }
});

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.menus.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.menus.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteMenu = (id) => {
    Swal.fire({
        title: 'আপনি কি নিশ্চিত?',
        text: "মুছে ফেললে এটি আর ফিরে পাওয়া যাবে না!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'হ্যাঁ, মুছে ফেলুন!',
        cancelButtonText: 'বাতিল',
        customClass: {
            title: 'swal-title-small',
            confirmButton: 'swal-btn-small swal-confirm-btn',
            cancelButton: 'swal-btn-small swal-cancel-btn',
        },
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        router.delete(route('admin.menus.destroy', id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                Swal.fire({
                    title: 'মুছে ফেলা হয়েছে!',
                    text: 'মেনু সফলভাবে মুছে ফেলা হয়েছে।',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: { title: 'swal-title-small' },
                });
            },
            onError: (errors) => {
                Swal.fire({
                    title: 'ত্রুটি!',
                    text: 'কিছু ভুল হয়েছে, দয়া করে আবার চেষ্টা করুন।',
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: { title: 'swal-title-small' },
                })
            },
        });
    });
};
</script>

<template>
    <Head title="Menu Management" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <i class="fadeIn animated bx bx-menu"></i> মেনু ম্যানেজমেন্ট
                            </h6>
                            <button v-if="hasPermission('admin.menus.create')" @click="openCreateModal" class="btn btn-primary btn-sm"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i> নতুন মেনু</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>টাইটেল</th>
                                        <th>URL/Route</th>
                                        <th>আইকন</th>
                                        <th>অর্ডার</th>
                                        <th>পারমিশন</th>
                                        <th>স্ট্যাটাস</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="menu in menus" :key="menu.id">
                                        <!-- Parent Menu -->
                                        <tr class="bg-light">
                                            <td class="fw-bold">{{ menu.title }}</td>
                                            <td>
                                                <span v-if="menu.route" class="badge bg-info text-dark">{{ menu.route }}</span>
                                                <span v-else>{{ menu.url }}</span>
                                            </td>
                                            <td><i :class="menu.icon" v-if="menu.icon"></i> {{ menu.icon }}</td>
                                            <td>{{ menu.order }}</td>
                                            <td>
                                                <div v-if="menu.permission_name" class="d-flex flex-wrap gap-1">
                                                    <span v-for="perm in menu.permission_name.split(',')" :key="perm" class="badge bg-primary text-white">
                                                        {{ perm.trim() }}
                                                    </span>
                                                </div>
                                                <span v-else class="text-muted">No permissions</span>
                                            </td>
                                            <td>
                                                <span v-if="menu.is_active" class="badge bg-success">Active</span>
                                                <span v-else class="badge bg-danger">Inactive</span>
                                            </td>
                                            <td>
                                                <a v-if="hasPermission('admin.menus.edit')" @click="openEditModal(menu)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                                <a v-if="hasPermission('admin.menus.destroy')" @click.prevent="deleteMenu(menu.id)" class="text-danger ms-2" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                            </td>
                                        </tr>
                                        <!-- Children Menus -->
                                        <tr v-for="child in menu.children" :key="child.id">
                                            <td class="ps-5">↳ {{ child.title }}</td>
                                            <td>
                                                <span v-if="child.route" class="badge bg-info text-dark">{{ child.route }}</span>
                                                <span v-else>{{ child.url }}</span>
                                            </td>
                                            <td><i :class="child.icon" v-if="child.icon"></i> {{ child.icon }}</td>
                                            <td>{{ child.order }}</td>
                                            <td>
                                                <div v-if="child.permission_name" class="d-flex flex-wrap gap-1">
                                                    <span v-for="perm in child.permission_name.split(',')" :key="perm" class="badge bg-primary text-white">
                                                        {{ perm.trim() }}
                                                    </span>
                                                </div>
                                                <span v-else class="text-muted">No permissions</span>
                                            </td>
                                            <td>
                                                <span v-if="child.is_active" class="badge bg-success">Active</span>
                                                <span v-else class="badge bg-danger">Inactive</span>
                                            </td>
                                            <td>
                                                <a v-if="hasPermission('admin.menus.edit')" @click="openEditModal(child)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                                <a v-if="hasPermission('admin.menus.destroy')" @click.prevent="deleteMenu(child.id)" class="text-danger ms-2" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                            </td>
                                        </tr>
                                    </template>
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
                    <div class="modal-header" style="border-top: 2px solid #004882;">
                        <h5 class="modal-title font-bold">
                            <i class="bx" :class="isEditing ? 'bx-edit' : 'bx-plus-circle'"></i>
                            {{ isEditing ? 'মেনু এডিট করুন' : 'নতুন মেনু তৈরি করুন' }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">টাইটেল</label>
                                    <input v-model="form.title" type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">প্যারেন্ট মেনু</label>
                                    <select v-model="form.parent_id" class="form-select">
                                        <option :value="null">কোন প্যারেন্ট নেই (Top Level)</option>
                                        <option v-for="m in allMenus" :key="m.id" :value="m.id">{{ m.title }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Route Name (Optional)</label>
                                    <input v-model="form.route" type="text" class="form-control" placeholder="e.g. dashboard">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">URL (Optional)</label>
                                    <input v-model="form.url" type="text" class="form-control" placeholder="e.g. /custom-url">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">আইকন (Boxicons class)</label>
                                    <input v-model="form.icon" type="text" class="form-control" placeholder="e.g. bx bx-home">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">অর্ডার</label>
                                    <input v-model="form.order" type="number" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">পারমিশন নাম <span class="text-muted text-sm">(কমা দিয়ে একাধিক লিখুন)</span></label>
                                    <input v-model="form.permission_name" type="text" class="form-control" placeholder="যেমন: create, edit, delete">
                                    <small class="form-text text-muted">
                                        একাধিক পারমিশন যুক্ত করতে কমা (,) ব্যবহার করুন।
                                        <span v-if="form.title" class="d-block mt-1">
                                            <strong>টিপ:</strong> {{ form.title.toLowerCase().replace(/\s+/g, '_') }}_view, {{ form.title.toLowerCase().replace(/\s+/g, '_') }}_edit
                                        </span>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input v-model="form.is_active" class="form-check-input" type="checkbox" id="isActive">
                                        <label class="form-check-label" for="isActive">অ্যাক্টিভ</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="button" class="btn btn-secondary btn-sm me-2 px-4" @click="closeModal">
                                    <i class="bx bx-x"></i> বন্ধ করুন
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    <i class="bx" :class="isEditing ? 'bx-up-arrow-alt' : 'bx-check'"></i>
                                    {{ isEditing ? 'আপডেট করুন' : 'সেভ করুন' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
