<template>
  <div class="overflow-x-hidden">
    <div
      class="bg-no-repeat parallax"
      :style="{ backgroundPositionY: `${scrollY * 0.5 - 350}px` }"
    ></div>
    <div
      class="relative w-screen bg-white h-[50vh] -z-0"
    >

    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, onUnmounted } from "vue";

const scrollY = ref(0);

const handleScroll = () => {
  scrollY.value = window.scrollY;
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});
</script>

<style scoped>
.parallax {
  background-image: url("https://images.pexels.com/photos/29431251/pexels-photo-29431251/free-photo-of-mesmerizing-abstract-green-swirl-art-design.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1");
  background-attachment: fixed;
  background-size: cover;
  height: 85vh;
}
</style>
