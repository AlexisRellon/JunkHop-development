<template>
  <!-- Header Title -->
  <div
    class="h-fit flex flex-col items-center justify-center text-center gap-5 py-[4rem] text-white w-full"
    style="
      background: linear-gradient(
          to bottom,
          rgba(0, 0, 0, 0.25),
          rgba(0, 0, 0, 0.5)
        ),
        url('/images/resources-hero.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
    "
  >
    <h1 class="text-5xl font-extrabold leading-tight">Educational Resources</h1>
    <p class="mt-2 text-xl">
      Discover a variety of video resources to enhance your learning experience
    </p>
  </div>
  <!-- Video Tutorial -->
  <div class="grid grid-cols-1 gap-6 p-6 mx-auto sm:grid-cols-2 lg:grid-cols-3">
    <LazyClientOnly>
      <div v-for="video in videos" :key="video.id" class="relative">
        <iframe
          :src="`https://www.youtube.com/embed/${video.youtubeId}`"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
          class="w-full h-64 rounded-lg shadow-md"
          loading="lazy"
        ></iframe>
        <div class="py-3 text-black dark:text-white">
          <p class="text-lg font-semibold">{{ video.title }}</p>
        </div>
      </div>
    </LazyClientOnly>
  </div>
</template>

<script lang="ts" setup>
const videos = ref([
  { id: 1, youtubeId: "cNPEH0GOhRw", title: "How to Recycle Plastic Bottles" },
  {
    id: 2,
    youtubeId: "6jQ7y_qQYUA",
    title: "Recycling 101: A Beginner's Guide",
  },
  {
    id: 3,
    youtubeId: "s4LZwCDaoQM",
    title: "The Recycling Process: From Curbside to New Product",
  },
  {
    id: 4,
    youtubeId: "iBGZtNJAt-M",
    title: "Recycling Electronics: What You Need to Know",
  },
  {
    id: 5,
    youtubeId: "xpAnLXc_bIU",
    title: "How to Recycle Paper and Cardboard",
  },
  {
    id: 6,
    youtubeId: "jsp7mgYv3aI",
    title: "Recycling Glass: A Step-by-Step Guide",
  },
  {
    id: 7,
    youtubeId: "IYi7mKL7I5k",
    title: "The Importance of Recycling: Why It Matters",
  },
]);
</script>

<style></style>
