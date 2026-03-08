<script lang="ts" setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head } from '@inertiajs/vue3';
</script>
<template>
    <Head title="Dashbaord" />
    <AppLayout1>
        <div class="row g-2 align-items-center mb-4">
            <!-- 🔹 LEFT SIDE : Buttons -->
            <div class="col-lg-8 col-md-12">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary shadow-sm">
                        <i class='bx bx-calendar-check me-1'></i> আজকের হিসাব
                    </button>

                    <button class="btn btn-success shadow-sm">
                        <i class='bx bx-time-five me-1'></i> শেষ ৭ দিনের হিসাব
                    </button>

                    <button class="btn btn-warning text-dark shadow-sm">
                        <i class='bx bx-calendar me-1'></i> শেষ ১৫ দিনের হিসাব
                    </button>

                    <button class="btn btn-danger shadow-sm">
                        <i class='bx bx-layer me-1'></i> সিজন অনুযায়ী হিসাব
                    </button>
                </div>
            </div>

            <!-- 🔹 RIGHT SIDE : Date & Month -->
            <div class="col-lg-4 col-md-12">
                <div class="d-flex gap-2 justify-content-lg-end">
                    <div class="input-group input-group-sm w-auto shadow-sm">
                        <span class="input-group-text bg-white"><i class='bx bx-calendar'></i></span>
                        <input type="date" class="form-control border-start-0">
                    </div>
                    <div class="input-group input-group-sm w-auto shadow-sm">
                        <span class="input-group-text bg-white"><i class='bx bx-calendar-event'></i></span>
                        <input type="month" class="form-control border-start-0">
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
                <div class="card radius-4 border-start border-0 border-3 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">মোট বিক্রয় (ভাড়া সহ)</p>
                                <h4 class="my-1 text-info">৳ 4805</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-4 border-start border-0 border-3 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">নগদ বিক্রয়</p>
                                <h4 class="my-1 text-danger">৳ 84,245</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-4 border-start border-0 border-3 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">বাকি বিক্রয়</p>
                                <h4 class="my-1 text-success">৳ 34.6</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-4 border-start border-0 border-3 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">মোট পেমেন্ট</p>
                                <h4 class="my-1 text-warning">৳ 8</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white fw-bold text-center">চালান</div>
                    <div class="card-body text-center p-1">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="table-success">
                                    <tr>
                                        <th class="text-start">শ্রেণি</th>
                                        <th class="text-start">চালান</th>
                                        <th class="text-end">পরিমাণ</th>
                                        <th class="text-end">মোট মূল্য</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white fw-bold text-center">পেমেন্ট</div>
                    <div class="card-body text-center p-1">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="table-danger">
                                    <tr>
                                        <th class="text-start">খতিয়ান</th>
                                        <th class="text-end">পরিমাণ</th>
                                        <th class="text-end">খতিয়ান দেওয়া</th>
                                    </tr>
                                </thead>
                                <tbody>
                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                <div class="card border-danger">
                    <div class="card-header bg-primary text-white fw-bold text-center">প্রোডাকশন</div>
                    <div class="card-body text-center p-1">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="table-danger">
                                    <tr>
                                        <th class="text-start">মাঠ</th>
                                        <th class="text-end">পরিমাণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
               <div class="card border-info">
                    <div class="card-header bg-info text-white fw-bold text-center">ডেলিভারি</div>
                    <div class="card-body text-center p-1">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="table-info">
                                    <tr>
                                        <th class="text-start">শ্রেণি</th>
                                        <th class="text-end">পরিমাণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white fw-bold text-center">লোড</div>
                    <div class="card-body text-center p-1">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="table-success">
                                    <tr>
                                        <th class="text-start">বিবরণ</th>
                                        <th class="text-end">পরিমাণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white fw-bold text-center">আনলোড</div>
                    <div class="card-body text-center p-1">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th class="text-start">শ্রেণি</th>
                                        <th class="text-end">পরিমাণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout1>
</template>

<style scoped>
.table>:not(:last-child)>:last-child>* {
    border: none;
}
</style>
