<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';

const props = defineProps({
    business_store: Object,
});

const spinBtn = ref(false);

const form = useForm({
    id: props.business_store.id??'',
    store_name: props.business_store.store_name??'',
    store_name_en: props.business_store.store_name_en??'',
    address: props.business_store.address??'',
    phone: props.business_store.phone??'',
    owner_name: props.business_store.owner_name??'',
    owner_phone: props.business_store.owner_phone??'',
});

const updateStore = () => {
    spinBtn.value = true;
    form.post(route('business-store.store'), {
        onSuccess: () => {
            spinBtn.value = false;
            showModal.value = false;
            form.reset();
            form.items = [];
        },
        onError: () => {
            spinBtn.value = false;
        },
    });
};


</script>
<template>

    <Head title="ভাটার তথ্য" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12 mx-auto">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title" style="margin-bottom: 0;">
                            <h4 class="mb-0 text-primary text-center">
                                ভাটার তথ্য
                            </h4>
                        </div>
                    </div>
                    <form @submit.prevent="updateStore" >
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ভাটার আইডি<span class="text-danger">*</span></label>
                                    <Input v-model="form.id" class="form-control form-control-sm" placeholder="ডেলিভারি নং" :class="[form.errors.id ? 'border-danger mb-1' : '']" required readonly/>
                                    <InputError :message="form.errors.id" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ভাটার নাম (বাংলায়)<span class="text-danger">*</span></label>
                                    <Input v-model="form.store_name" class="form-control form-control-sm" placeholder="ভাটার নাম (বাংলায়)" :class="[form.errors.store_name ? 'border-danger mb-1' : '']" required/>
                                    <InputError :message="form.errors.store_name" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ভাটার নাম (ইংরেজি)<span class="text-danger">*</span></label>
                                    <Input v-model="form.store_name_en" class="form-control form-control-sm" placeholder="ভাটার নাম (ইংরেজি)" :class="[form.errors.store_name_en ? 'border-danger mb-1' : '']" required/>
                                    <InputError :message="form.errors.store_name_en" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ভাটার ঠিকানা (চালানে রয়েছে)<span class="text-danger">*</span></label>
                                    <Input v-model="form.address" class="form-control form-control-sm" placeholder="ভাটার ঠিকানা" :class="[form.errors.address ? 'border-danger mb-1' : '']" required/>
                                    <InputError :message="form.errors.address" />
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">মালিকের নাম<span class="text-danger">*</span></label>
                                    <Input v-model="form.owner_name" class="form-control form-control-sm" placeholder="মালিকের নাম" :class="[form.errors.owner_name ? 'border-danger mb-1' : '']" required/>
                                    <InputError :message="form.errors.owner_name" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">মালিকের ফোন নম্বর<span class="text-danger">*</span></label>
                                    <Input v-model="form.owner_phone" class="form-control form-control-sm" placeholder="মালিকের ফোন নম্বর" :class="[form.errors.owner_phone ? 'border-danger mb-1' : '']" required/>
                                    <InputError :message="form.errors.id" />
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">ফোন নম্বর (চালানে রয়েছে) <span class="text-danger" style="font-size: 10px;">একটি নম্বর লেখার পর কমা (,) দিয়ে অন্য নম্বর লিখবেন</span></label>
                                    <Input v-model="form.phone" class="form-control form-control-sm" placeholder="ফোন নম্বর" :class="[form.errors.phone ? 'border-danger mb-1' : '']" required/>
                                    <InputError :message="form.errors.phone" />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="text-end mt-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                  <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                  <i v-else class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                  Update
                                </button>
                              </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
