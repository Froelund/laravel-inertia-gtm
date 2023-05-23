<script setup>
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const dataLayer = computed(() => usePage().props.data_layer);

watch(dataLayer, (nextDataLayer) => {
    if (!nextDataLayer) {
        return;
    }
    const nextEvents = JSON.parse(JSON.stringify(nextDataLayer));
    window.dataLayer = window.dataLayer || [];
    nextEvents.forEach((event) => {
        window.dataLayer.push(event);
    });
}, { immediate: true });
</script>
<template></template>
