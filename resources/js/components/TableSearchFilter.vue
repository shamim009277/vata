<template>
  <div class="row">
    <div class="col-sm-12 col-md-6">
      <div class="dataTables_length" id="example_length">
        <label>Show
          <select :value="perPage" @change="emit('update:perPage', $event.target.value)" name="example_length" aria-controls="example" class="form-select form-select-sm">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select> entries</label>
      </div>
    </div>
    <div class="col-sm-12 col-md-6">
      <div id="example_filter" class="dataTables_filter">
        <label>Search:<input :value="search" @input="emit('update:search', $event.target.value)" type="search" class="form-control form-control-sm"
            placeholder="Search ..." aria-controls="example"></label>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
    perPage: [Number, String],
    search: [String]
});

const emit = defineEmits([
    'update:perPage',
    'update:search'
]);

defineModel('perPage');
defineModel('search');

</script>
