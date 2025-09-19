<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p style="color:red;margin-bottom:0px;">
            {{ message }}
        </p>
    </div>
</template>
