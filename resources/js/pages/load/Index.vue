<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';

const props = defineProps({
    filters: Object,
    rounds: Array,
    items: Array,
    fields: Array,
});

const date = ref(props.filters?.date || new Date().toISOString().split('T')[0]);
const round = ref('');

const showLoad = ref(false);
const loading = ref(true);
const spinBtn = ref(false);
const isEditing = ref(false);

// show/hide form toggle
const newLoad = () => {
    showLoad.value = !showLoad.value;
};

const roundslimit = computed(() => {
    return props.rounds.slice(0, 10);
});

const form = useForm({
    loading_date: date.value,
    load_type:"",
    field_list_id:"",
    item_id:"",
    quantity:"",
    round_id:"",
});

function submit() {
    spinBtn.value = true;

    if (isEditing.value) {
        form.put(route("loads.update", form.id), {
            onSuccess: () => {
                spinBtn.value = false;
                showLoad.value = false;
            },
            onError: () => {
                spinBtn.value = false;
            }
        });
    } else {
        form.post(route("loads.store"), {
            onSuccess: () => {
                spinBtn.value = false;
                showLoad.value = false;
                form.reset();
            },
            onError: () => {
                spinBtn.value = false;
            }
        });
    }
}

watch([date,round], () => {
    router.get(route('loads.index'), {
        date: date.value,
        round: round.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

// Simulate loading
onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 500);
});

</script>

<template>

    <Head title="লোড খাতা" />
    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-0">

                        <!-- Title Left -->
                        <h6 class="mb-0 text-primary d-flex align-items-center text-truncate" style="max-width: 250px;">
                            <i class="fadeIn animated bx bx-list-ul me-2"></i> লোড খাতা
                        </h6>

                        <!-- Right-side Controls -->
                        <div class="d-flex flex-wrap align-items-center gap-2 justify-content-end w-100 w-md-auto">

                            <!-- Date Filter -->
                            <input type="date" v-model="date"
                                class="form-control form-control-sm flex-grow-1 flex-md-grow-0"
                                style="min-width: 140px; max-width: 160px;" />

                            <!-- Select Filter -->
                            <select v-model="round" class="form-select form-select-sm flex-grow-1 flex-md-grow-0"
                                style="min-width: 120px; max-width: 120px;" aria-label="Default select example">
                                <option selected>সিলেক্ট রাউন্ড</option>
                                <option v-for="(item, index) in rounds" :key="index" :value="item.id">{{ item.round }}</option>
                            </select>

                            <!-- Buttons -->
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button
                                    class="btn btn-primary btn-sm d-flex align-items-center gap-1 justify-content-center"
                                    @click="">
                                    <i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>
                                    রিপোর্ট
                                </button>

                                <button
                                    class="btn btn-primary btn-sm d-flex align-items-center gap-1 justify-content-center"
                                    @click="newLoad">
                                    <i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>
                                    নতুন লোড
                                </button>
                            </div>
                        </div>
                    </div>



                    <div class="card-body">
                        <div v-if="loading" class="text-center my-5">
                            <i class="bx bx-loader bx-spin" style="font-size: 40px;"></i>
                        </div>
                        <div v-else>
                            <!-- Payment Form -->
                            <div class="table-responsive" v-if="showLoad">
                                <form @submit.prevent="submit">
                                    <table class="table table-striped table-bordered" style="width: 100%; font-size: small;">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <label class="form-label">তারিখ</label>
                                                    <input type="date" v-model="form.loading_date"class="form-control form-control-sm" :class="{ 'border-danger mb-1': form.errors.loading_date }" style="height: 32px; font-size: 12px;" />
                                                    <InputError :message="form.errors.loading_date" />
                                                </td>
                                                <td>
                                                    <label for="exampleDataList" class="form-label">সিলেক্ট রাউন্ড</label>
                                                    <input v-model="form.round_id" class="form-control form-control-sm" list="datalistOptions" id="exampleDataList" placeholder="টাইপ করুন...">

                                                    <datalist id="datalistOptions">
                                                        <option v-for="(item, index) in roundslimit" :key="index" :value="item.round"></option>
                                                    </datalist>
                                                    <InputError :message="form.errors.payment_type" />
                                                </td>
                                                <td>
                                                    <label class="form-label">লোডের বিবরন</label>
                                                    <select v-model="form.load_type"
                                                            class="form-select form-select-sm flex-grow-1 flex-md-grow-0"
                                                            :class="{ 'border-danger mb-1': form.errors.payment_details }">

                                                        <option value="">সিলেক্ট রাউন্ড</option>
                                                        <option value="1">মাঠ থাকে লোড</option>
                                                        <option value="2">পাকা ইট লোড</option>
                                                    </select>
                                                    <InputError :message="form.errors.payment_details" />
                                                </td>
                                                <td v-show ="form.load_type == 1">
                                                    <label class="form-label">সিলেক্ট মাঠ</label>
                                                    <select v-model="form.field_list_id" class="form-select form-select-sm flex-grow-1 flex-md-grow-0" :class="{ 'border-danger mb-1': form.errors.payment_details }" aria-label="Default select example">
                                                        <option value="0" selected>সিলেক্ট মাঠ</option>
                                                        <option v-for="(item, index) in fields" :key="index" :value="item.id">{{ item.name }}</option>
                                                    </select>
                                                    <InputError :message="form.errors.payment_details" />
                                                </td>
                                                <td v-show="form.load_type == 2">
                                                    <label class="form-label">ইটের ধরন</label>
                                                    <select v-model="form.item_id" class="form-select form-select-sm flex-grow-1 flex-md-grow-0" :class="{ 'border-danger mb-1': form.errors.payment_details }" aria-label="Default select example">
                                                        <option value="0" selected>সিলেক্ট ইটে</option>
                                                        <option v-for="(item, index) in items" :key="index" :value="item.id">{{ item.name }}</option>
                                                    </select>
                                                    <InputError :message="form.errors.payment_details" />
                                                </td>
                                                <td>
                                                    <label class="form-label">পরিমাণ</label>
                                                    <Input type="number" v-model="form.quantity" min="0"class="form-control form-control-sm" :class="{ 'border-danger mb-1': form.errors.quantity }" :readonly="form.payment_type === 'advance'" placeholder="পরিমাণ" />
                                                    <InputError :message="form.errors.quantity" />
                                                </td>
                                                <td>
                                                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-4">
                                                        <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                                        <i v-else class="fadeIn animated bx bx-plus-medical me-1"
                                                            style="font-size: small;"></i>
                                                        {{ isEditing ? 'আপডেট' : 'সেভ করুন' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </form>
                            </div>

                            <!-- Payment Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered"
                                    style="width: 100%; font-size: small;">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th>তারিখ</th>
                                            <th>রাউন্ড</th>
                                            <th>লোড বিবরন</th>
                                            <th>পরিমাণ</th>
                                            <th>বাটন</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>

                                <div class="row align-items-center">
                                    <!-- Pagination Info -->
                                    <div class="col-sm-12 col-md-5 mb-2 mb-md-0">
                                        <div class="dataTables_info" role="status" aria-live="polite">
                                            Showing to of entries
                                        </div>
                                    </div>

                                    <!-- Pagination Links -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>


<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.modal {
    position: fixed;
    inset: 0;
    overflow: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.challan-info {
    line-height: 0.5;
}

.company-info {
    line-height: 0.5;
    text-align: right;
}

.company-info h4 {
    font-size: 16px;
    font-weight: bold;
}

.customer {
    background: linear-gradient(145deg, #ffffff, #f9fafb);
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13px;
    line-height: 1.4;
    color: #333;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    position: relative;
}

.customer-danger {
    background: linear-gradient(145deg, #ffffff, #f9fafb);
    border: 1px solid rgb(234, 45, 45);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13px;
    line-height: 1.0;
    color: #333;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    position: relative;
}

.customer:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.customer::before {
    position: absolute;
    top: -10px;
    left: 14px;
    background: #fff;
    font-size: 12px;
    font-weight: 600;
    color: #004882;
    padding: 0 6px;
    border-radius: 4px;
}

.customer p {
    margin: 3px 0;
}
</style>
