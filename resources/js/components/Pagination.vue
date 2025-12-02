<template>
  <div class="row">
    <div class="col-sm-12 col-md-5">
      <div class="dataTables_info" role="status" aria-live="polite">
        Showing {{ modules.from }} to {{ modules.to }} of {{ modules.total }} entries
      </div>
    </div>
    <div class="col-sm-12 col-md-7">
      <div class="dataTables_paginate paging_simple_numbers">
        <ul class="pagination" style="display: flex; gap: 4px;">
          <li v-for="link in modules.links" :key="link.label" class="paginate_button page-item"
            :class="{ active: link.active, disabled: !link.url }">
            <Link :href="link.url || '#'" v-html="link.label" class="page-link"
              :class="{ 'bg-primary text-white': link.active, 'text-muted': !link.url }" />
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
    modules: Object,
});
</script>
