<script setup lang="ts">
import { ModalDemo } from "#components";

const modal = useModal();
const router = useRouter();
const auth = useAuthStore();

function openDemoModal() {
  modal.open(ModalDemo);
}

function scrollToDetails() {
  const detailsSection = document.getElementById("details-section");
  if (detailsSection) {
    detailsSection.scrollIntoView({ behavior: "smooth" });
  }
}

useSeoMeta({
  title: "Home",
});
</script>

<template>
  <!-- <div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-3">
      <UCard>
        <div class="font-bold text-lg leading-tight tracking-tighter mb-4">Demo</div>

        <div class="flex gap-3">
          <UButton label="Modal" @click="openDemoModal" color="gray" />
          <UButton label="404 page" color="gray" @click="router.push('/404')" />
        </div>
      </UCard>
    </div>
    <div class="col-span-12 lg:col-span-9">
      <UCard>
        <div class="font-bold text-lg leading-tight tracking-tighter mb-4">
          User Object
        </div>
        <pre>{{ auth.user }}</pre>
      </UCard>
    </div>
  </div> -->

  <section
    class="hero-section bg-cover bg-center py-20 h-screen flex items-center justify-center absolute inset-0"
    style="background-image: linear-gradient(to top, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0)), url('/hero-image.jpg');"
  >
    <div class="container mx-auto text-center">
      <h1 class="text-4xl font-bold text-white mb-4">
        Empowering Communities for a <span class="text-teal-300">Cleaner Tomorrow</span>
      </h1>
      <p class="text-lg text-white mb-8">
        Join us in creating sustainable cities through smart waste management
        and community engagement.
      </p>
      <div class="flex justify-center gap-5">
        <UButton
          label="Get Started"
          @click="router.push('/auth/register')"
          color="teal"
          size="xl"
        />
        <UButton
          label="Learn More"
          @click="scrollToDetails"
          color="teal"
          variant="ghost"
        />
      </div>
    </div>
  </section>
</template>
