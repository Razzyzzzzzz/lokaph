<script setup>defineEmits(['close'])</script>
<template>
  <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/50 md:items-center" @click.self="$emit('close')">
    <div class="max-h-[90vh] w-full space-y-3 overflow-auto rounded-t-3xl bg-white p-5 md:max-w-md md:rounded-3xl"><slot /></div>
  </div>
</template>
