<template>
  <div>
    <UButton @click="scrollToTop" v-if="showButton"
      class="fixed bottom-10 right-10 p-4 bg-teal-600 dark:text-white rounded-full w-12 h-12 flex items-center justify-center shadow-xl"
      icon="i-heroicons-arrow-up" size="md" />
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, onUnmounted } from "vue";

const showButton = ref(false);

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleScroll = () => {
  showButton.value = window.scrollY > window.innerHeight * 0.05;
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});
</script>
