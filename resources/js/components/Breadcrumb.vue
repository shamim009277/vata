<template>
  <div class="page-breadcrumb d-none d-sm-flex align-items-center my-2">
    <div class="breadcrumb-title pe-3">{{ title }}</div>
    <div class="ms-auto">
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="#"><i class="bx bx-home-alt"></i></a></li>
            <li v-for="item in breadcrumbItems" :key="item.title" class="breadcrumb-item" aria-current="page"> <a
                :href="item.href">{{ item.title }}</a> </li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
    breadcrumbItems: Array,
    title: String,
});

</script>

<style></style>
