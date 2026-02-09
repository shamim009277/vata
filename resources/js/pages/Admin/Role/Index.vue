<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { usePermission } from '@/Composables/usePermission';

const { hasPermission } = usePermission();

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

// Smart permission validation
const permissionValidation = computed(() => {
    const totalPermissions = props.permissions.length;
    const selectedCount = form.permissions.length;
    const selectedPercentage = totalPermissions > 0 ? Math.round((selectedCount / totalPermissions) * 100) : 0;
    
    // Check if role has admin permissions
    const hasAdminPermissions = form.permissions.some(perm => 
        perm.includes('menus.') || 
        perm.includes('roles.') || 
        perm.includes('permissions.') || 
        perm.includes('users.')
    );
    
    // Check if role has operational permissions
    const hasOperationalPermissions = form.permissions.some(perm => 
        !perm.includes('menus.') && 
        !perm.includes('roles.') && 
        !perm.includes('permissions.') && 
        !perm.includes('users.')
    );
    
    return {
        totalPermissions,
        selectedCount,
        selectedPercentage,
        hasAdminPermissions,
        hasOperationalPermissions,
        isSuperAdmin: selectedPercentage >= 90,
        isLimitedRole: selectedPercentage <= 30,
        isBalancedRole: selectedPercentage >= 40 && selectedPercentage <= 70,
        roleType: getRoleType(selectedPercentage, hasAdminPermissions, hasOperationalPermissions)
    };
});

// Determine role type based on permissions
const getRoleType = (percentage, hasAdmin, hasOperational) => {
    if (percentage >= 90) return 'সুপার অ্যাডমিন';
    if (percentage >= 70 && hasAdmin) return 'অ্যাডমিন';
    if (percentage >= 50 && hasOperational) return 'ম্যানেজার';
    if (percentage >= 30 && hasOperational) return 'অপারেটর';
    if (percentage > 0) return 'ভিউয়ার';
    return 'কোনো পারমিশন নাই';
};

// Get permission recommendations based on role name
const getPermissionRecommendations = computed(() => {
    const roleName = form.name.toLowerCase();
    const recommendations = [];
    
    if (roleName.includes('admin') || roleName.includes('অ্যাডমিন')) {
        recommendations.push(
            ...Object.keys(groupedPermissions.value).filter(menu => 
                ['অ্যাডমিনিস্ট্রেশন', 'মেনু', 'রোল', 'ইউজার', 'পারমিশন'].includes(menu)
            )
        );
    }
    
    if (roleName.includes('manager') || roleName.includes('ম্যানেজার')) {
        recommendations.push(
            ...Object.keys(groupedPermissions.value).filter(menu => 
                !['অ্যাডমিনিস্ট্রেশন', 'মেনু', 'রোল', 'ইউজার', 'পারমিশন'].includes(menu)
            )
        );
    }
    
    if (roleName.includes('production') || roleName.includes('প্রোডাকশন')) {
        recommendations.push('কাঁচা ইট প্রোডাকশন', 'আজকের প্রোডাকশন', 'সব প্রোডাকশন');
    }
    
    return recommendations;
});

// Helper functions for UI
const getProgressBarClass = () => {
    const percentage = permissionValidation.value.selectedPercentage;
    if (percentage >= 90) return 'bg-danger';
    if (percentage >= 70) return 'bg-warning';
    if (percentage >= 40) return 'bg-info';
    if (percentage > 0) return 'bg-primary';
    return 'bg-secondary';
};

const getRoleBadgeClass = () => {
    const percentage = permissionValidation.value.selectedPercentage;
    if (percentage >= 90) return 'bg-danger';
    if (percentage >= 70) return 'bg-warning text-dark';
    if (percentage >= 40) return 'bg-info';
    if (percentage > 0) return 'bg-primary';
    return 'bg-secondary';
};

// Group permissions by menu
const groupedPermissions = computed(() => {
    const grouped = {};
    
    props.permissions.forEach(perm => {
        const menuTitle = perm.menu ? perm.menu.title : 'অন্যান্য';
        if (!grouped[menuTitle]) {
            grouped[menuTitle] = [];
        }
        grouped[menuTitle].push(perm);
    });
    
    return grouped;
});

// Toggle all permissions in a menu group
const toggleMenuPermissions = (menuTitle, event) => {
    const isChecked = event.target.checked;
    const menuPermissions = groupedPermissions.value[menuTitle];
    
    if (isChecked) {
        // Add all permissions from this menu
        menuPermissions.forEach(perm => {
            if (!form.permissions.includes(perm.name)) {
                form.permissions.push(perm.name);
            }
        });
    } else {
        // Remove all permissions from this menu
        menuPermissions.forEach(perm => {
            const index = form.permissions.indexOf(perm.name);
            if (index > -1) {
                form.permissions.splice(index, 1);
            }
        });
    }
};

// Check if all permissions in a menu are selected
const isMenuAllSelected = (menuTitle) => {
    const menuPermissions = groupedPermissions.value[menuTitle];
    if (menuPermissions.length === 0) return false;
    
    return menuPermissions.every(perm => form.permissions.includes(perm.name));
};

// Check if some permissions in a menu are selected (for indeterminate state)
const isMenuSomeSelected = (menuTitle) => {
    const menuPermissions = groupedPermissions.value[menuTitle];
    const selectedCount = menuPermissions.filter(perm => form.permissions.includes(perm.name)).length;
    return selectedCount > 0 && selectedCount < menuPermissions.length;
};

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

// Assign all permissions from a specific menu
const assignMenuPermissions = (menuTitle) => {
    const menuPermissions = groupedPermissions.value[menuTitle] || [];
    const menuPermissionNames = menuPermissions.map(perm => perm.name);
    
    // Add menu permissions to form (avoid duplicates)
    menuPermissionNames.forEach(permName => {
        if (!form.permissions.includes(permName)) {
            form.permissions.push(permName);
        }
    });
};

// Remove all permissions from a specific menu
const removeMenuPermissions = (menuTitle) => {
    const menuPermissions = groupedPermissions.value[menuTitle] || [];
    const menuPermissionNames = menuPermissions.map(perm => perm.name);
    
    // Remove menu permissions from form
    menuPermissionNames.forEach(permName => {
        const index = form.permissions.indexOf(permName);
        if (index > -1) {
            form.permissions.splice(index, 1);
        }
    });
};

// Toggle all permissions (select all or unselect all)
const toggleAllPermissions = () => {
    const allPermissionNames = props.permissions.map(perm => perm.name);
    
    if (form.permissions.length === allPermissionNames.length) {
        // If all permissions are selected, unselect all
        form.permissions = [];
    } else {
        // Select all permissions
        form.permissions = [...allPermissionNames];
    }
};

// Check if all permissions are selected
const isAllPermissionsSelected = computed(() => {
    const allPermissionNames = props.permissions.map(perm => perm.name);
    return allPermissionNames.length > 0 && allPermissionNames.every(perm => form.permissions.includes(perm));
});

// Check if some permissions are selected (for indeterminate state)
const isSomePermissionsSelected = computed(() => {
    const allPermissionNames = props.permissions.map(perm => perm.name);
    const selectedCount = allPermissionNames.filter(perm => form.permissions.includes(perm)).length;
    return selectedCount > 0 && selectedCount < allPermissionNames.length;
});

const deleteRole = (id) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('admin.roles.destroy', id));
    }
};

// Watch for changes in permissions to update indeterminate states
const updateIndeterminateStates = () => {
    // Update menu-specific checkboxes
    Object.keys(groupedPermissions.value).forEach(menuTitle => {
        const checkbox = document.getElementById('select_all_' + menuTitle.replace(/\s+/g, '_'));
        if (checkbox) {
            checkbox.indeterminate = isMenuSomeSelected(menuTitle);
        }
    });
    
    // Update main select all checkbox
    const mainCheckbox = document.getElementById('select_all_permissions');
    if (mainCheckbox) {
        mainCheckbox.indeterminate = isSomePermissionsSelected.value;
    }
};

// Update indeterminate states when permissions change
watch(() => form.permissions, () => {
    nextTick(() => {
        updateIndeterminateStates();
    });
}, { deep: true });

// Group permissions by menu for display
const getGroupedPermissions = (permissions) => {
    const grouped = {};
    
    permissions.forEach(perm => {
        const menuTitle = perm.menu ? perm.menu.title : 'অন্যান্য';
        if (!grouped[menuTitle]) {
            grouped[menuTitle] = [];
        }
        grouped[menuTitle].push(perm);
    });
    
    return grouped;
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
                            <button v-if="hasPermission('admin.roles.create')" @click="openCreateModal" class="btn btn-primary btn-sm"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i> নতুন রোল</button>
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
                                            <div v-if="role.permissions && role.permissions.length > 0">
                                                <div v-for="(menuPerms, menuTitle) in getGroupedPermissions(role.permissions)" :key="menuTitle" class="mb-3">
                                                    <!-- Menu Header with Icon and Structure -->
                                                    <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                                                        <i class="bx bx-file-blank text-primary me-2"></i>
                                                        <strong class="text-primary">{{ menuTitle }}</strong>
                                                        <span class="badge bg-primary ms-2">{{ menuPerms.length }}</span>
                                                        <span class="badge bg-success ms-1">Active</span>
                                                    </div>
                                                    
                                                    <!-- Permission List with Route Info -->
                                                    <div class="ms-4 p-2 bg-white rounded border">
                                                        <div v-for="perm in menuPerms" :key="perm.id" class="d-flex align-items-center mb-1">
                                                            <span class="badge bg-secondary me-2">{{ perm.name }}</span>
                                                            <small class="text-muted">{{ perm.name }}.index</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Summary -->
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-muted">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        মোট {{ Object.keys(getGroupedPermissions(role.permissions)).length }} টি মেনু, {{ role.permissions.length }} টি পারমিশন
                                                    </small>
                                                </div>
                                            </div>
                                            <div v-else class="text-muted p-3 bg-light rounded">
                                                <i class="bx bx-x-circle me-2"></i>
                                                <small>কোনো পারমিশন নাই</small>
                                            </div>
                                        </td>
                                        <td>
                                            <a v-if="hasPermission('admin.roles.edit')" @click="openEditModal(role)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                            <a v-if="hasPermission('admin.roles.destroy')" @click="deleteRole(role.id)" class="text-danger ms-2" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
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
                                
                                <!-- Smart Recommendations -->
                                <div v-if="form.name && getPermissionRecommendations.length > 0" class="mt-2">
                                    <div class="alert alert-info d-flex align-items-center" role="alert">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <div class="flex-grow-1">
                                            <strong>পারমিশন সুপারিশ:</strong> 
                                            <span class="d-block small">{{ getPermissionRecommendations.join(', ') }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" @click="applyRecommendedPermissions">
                                                <i class="bx bx-check-circle me-1"></i>
                                                সুপারিশ অনুযায়ী পারমিশন যোগ করুন
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Permission Validation Summary -->
                            <div v-if="form.permissions.length > 0" class="mb-3">
                                <div class="card border-info">
                                    <div class="card-body py-2">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="progress" style="width: 120px; height: 8px;">
                                                            <div class="progress-bar" :class="getProgressBarClass()" :style="{width: permissionValidation.selectedPercentage + '%'}"></div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <strong>{{ permissionValidation.roleType }}</strong>
                                                        <div class="small text-muted">
                                                            {{ permissionValidation.selectedCount }} / {{ permissionValidation.totalPermissions }} পারমিশন
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge" :class="getRoleBadgeClass()">
                                                    {{ permissionValidation.selectedPercentage }}%
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Permission Type Indicators -->
                                        <div class="mt-2">
                                            <span v-if="permissionValidation.hasAdminPermissions" class="badge bg-warning me-1">
                                                <i class="bx bx-shield"></i> অ্যাডমিন
                                            </span>
                                            <span v-if="permissionValidation.hasOperationalPermissions" class="badge bg-success me-1">
                                                <i class="bx bx-cog"></i> অপারেশনাল
                                            </span>
                                            <span v-if="permissionValidation.isSuperAdmin" class="badge bg-danger me-1">
                                                <i class="bx bx-crown"></i> সুপার অ্যাডমিন
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Menu Selection for Quick Permission Assignment -->
                            <div class="mb-3">
                                <label class="form-label">দ্রুত পারমিশন যোগ করুন</label>
                                <select class="form-select" @change="handleMenuSelection">
                                    <option value="">মেনু নির্বাচন করুন</option>
                                    <option v-for="(permissions, menuTitle) in groupedPermissions" :key="menuTitle" :value="menuTitle">
                                        {{ menuTitle }} ({{ permissions.length }})
                                    </option>
                                </select>
                                <div v-if="selectedMenu" class="mt-2">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-success" @click="assignMenuPermissions(selectedMenu)">
                                            <i class="bx bx-plus-circle me-1"></i>
                                            সবগুলো পারমিশন যোগ করুন
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" @click="removeMenuPermissions(selectedMenu)">
                                            <i class="bx bx-minus-circle me-1"></i>
                                            সবগুলো পারমিশন বাদ দিন
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <span v-if="selectedMenuPermissions.length > 0">
                                            উপলো পারমিশন: {{ selectedMenuPermissions.join(', ') }}
                                        </span>
                                        <span v-else>এই পারমিশন নাই</span>
                                    </small>
                                </div>
                            </div>
                            <!-- Permission Selection Section -->
                            <div class="mb-3">
                                <!-- Global Select All Checkbox -->
                                <div class="mb-3 p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">সবগুলো পারমিশন</h6>
                                            <small class="text-muted">মোট {{ Object.keys(groupedPermissions).length }} টি মেনু থেকে {{ props.permissions.length }} টি পারমিশন</small>
                                        </div>
                                        <div class="form-check">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="select_all_permissions"
                                                :checked="isAllPermissionsSelected"
                                                :indeterminate="isSomePermissionsSelected"
                                                @change="toggleAllPermissions"
                                            >
                                            <label class="form-check-label" for="select_all_permissions">
                                                সবগুলো সিলেক্ট করুন ({{ props.permissions.length }})
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Permission Accordion -->
                                <div class="accordion" id="permissionsAccordion">
                                    <div v-for="(permissions, menuTitle) in groupedPermissions" :key="menuTitle" class="accordion-item mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapse_' + menuTitle.replace(/\s+/g, '_')" aria-expanded="true">
                                                <i class="bx bx-folder me-2"></i>{{ menuTitle }}
                                                <span class="badge bg-primary ms-2">{{ permissions.length }}</span>
                                            </button>
                                        </h2>
                                        <div :id="'collapse_' + menuTitle.replace(/\s+/g, '_')" class="accordion-collapse collapse show" data-bs-parent="#permissionsAccordion">
                                            <div class="accordion-body">
                                                <!-- Select All Checkbox -->
                                                <div class="mb-3 pb-2 border-bottom">
                                                    <div class="form-check">
                                                        <input 
                                                            class="form-check-input" 
                                                            type="checkbox" 
                                                            :id="'select_all_' + menuTitle.replace(/\s+/g, '_')"
                                                            :checked="isMenuAllSelected(menuTitle)"
                                                            :indeterminate="isMenuSomeSelected(menuTitle)"
                                                            @change="toggleMenuPermissions(menuTitle, $event)"
                                                        >
                                                        <label class="form-check-label fw-bold" :for="'select_all_' + menuTitle.replace(/\s+/g, '_')">
                                                            সবগুলো সিলেক্ট করুন ({{ permissions.length }})
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <!-- Individual Permissions -->
                                                <div class="row">
                                                    <div class="col-md-6" v-for="perm in permissions" :key="perm.id">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" :value="perm.name" v-model="form.permissions" :id="'perm_' + perm.id">
                                                            <label class="form-check-label" :for="'perm_' + perm.id">
                                                                {{ perm.name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
