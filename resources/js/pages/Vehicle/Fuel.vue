<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import VehicleNavbar from '@/components/VehicleNavbar.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    fuels: Object,
    vehicles: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    vehicle_id: '',
    date: new Date().toISOString().substr(0, 10),
    amount: '',
    quantity: '',
    fuel_type: 'Diesel',
    voucher_no: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.date = new Date().toISOString().substr(0, 10);
    showModal.value = true;
};

const openEditModal = (fuel) => {
    isEditing.value = true;
    form.id = fuel.id;
    form.vehicle_id = fuel.vehicle_id;
    form.date = fuel.date;
    form.amount = fuel.amount;
    form.quantity = fuel.quantity;
    form.fuel_type = fuel.fuel_type;
    form.voucher_no = fuel.voucher_no;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('vehicle-fuels.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('vehicle-fuels.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteFuel = (id) => {
    if (confirm('Are you sure you want to delete this fuel record?')) {
        router.delete(route('vehicle-fuels.destroy', id));
    }
};
</script>

<template>
    <Head title="Vehicle Fuel Cost" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-car"></i> গাড়ির হিসাব
                                </a>
                            </h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <VehicleNavbar />
                        
                        <div class="tab-content py-3">
                            <div class="tab-pane fade show active" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="mb-0">গাড়ির ফুয়েল কস্ট</h5>
                                    <button @click="openCreateModal" class="btn btn-primary px-5"><i class="bx bx-plus"></i> নতুন রেকর্ড</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>তারিখ</th>
                                                <th>গাড়ি</th>
                                                <th>পরিমাণ (লিটার)</th>
                                                <th>খরচ (টাকা)</th>
                                                <th>ফুয়েল টাইপ</th>
                                                <th>ভাউচার</th>
                                                <th>অ্যাকশন</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="fuel in fuels.data" :key="fuel.id">
                                                <td>{{ fuel.date }}</td>
                                                <td>{{ fuel.vehicle?.name }} ({{ fuel.vehicle?.number }})</td>
                                                <td>{{ fuel.quantity }}</td>
                                                <td>{{ fuel.amount }}</td>
                                                <td>{{ fuel.fuel_type }}</td>
                                                <td>{{ fuel.voucher_no || '-' }}</td>
                                                <td>
                                                    <button @click="openEditModal(fuel)" class="btn btn-sm btn-info me-2"><i class="bx bx-edit"></i></button>
                                                    <button @click="deleteFuel(fuel.id)" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr v-if="fuels.data.length === 0">
                                                <td colspan="7" class="text-center">কোনো রেকর্ড পাওয়া যায়নি</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'ফুয়েল রেকর্ড এডিট করুন' : 'নতুন ফুয়েল রেকর্ড' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">গাড়ি</label>
                                <select v-model="form.vehicle_id" class="form-select" required>
                                    <option value="" disabled>গাড়ি সিলেক্ট করুন</option>
                                    <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.name }} - {{ v.number }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">তারিখ</label>
                                <input v-model="form.date" type="date" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">পরিমাণ (লিটার/ইউনিট)</label>
                                    <input v-model="form.quantity" type="number" step="0.01" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">মোট খরচ (টাকা)</label>
                                    <input v-model="form.amount" type="number" step="0.01" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ফুয়েল টাইপ</label>
                                <input v-model="form.fuel_type" type="text" class="form-control" placeholder="Diesel, Octane, CNG...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ভাউচার নম্বর</label>
                                <input v-model="form.voucher_no" type="text" class="form-control">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    {{ isEditing ? 'আপডেট' : 'সেভ করুন' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
