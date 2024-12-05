<template>
  <div v-if="isAdminUser">
    <AppAdminPanel />
  </div>
  <div v-else>
    <AppJunkshopPanel />
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
const authStore = useAuthStore();
const router = useRouter();

// Ensure authStore is reactive
const user = computed(() => authStore.user);

// Determine if the user role is admin
const isAdminUser = computed(() => {
  return user.value?.roles?.includes("admin");
});

// Determine if the user role is user
const isUser = computed(() => {
  return user.value?.roles?.includes("user");
});

// If the role is user, redirect to /account/general
onMounted(() => {
  if (isUser.value === true) {
    router.push("/account/general");
  }
});

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
