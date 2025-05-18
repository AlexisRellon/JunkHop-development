<script lang="ts" setup>
const router = useRouter();
const form = ref();

const state = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const { refresh: onSubmit, status: registerStatus } = useFetch<any>("register", {
  method: "POST",
  body: state,
  immediate: false,
  watch: false,
  async onResponse({ response }) {
    if (response?.status === 422) {
      form.value.setErrors(response._data?.errors);
    } else if (response._data?.ok) {
      useToast().add({
        icon: "i-heroicons-check-circle-20-solid",
        title: "You have been registered successfully.",
        color: "emerald",
        actions: [
          {
            label: "Log In now",
            to: "/auth/login",
            color: "emerald",
          },
        ],
      });

      router.push("/auth/login");
    }
  }
});
</script>

<template>
  <div class="space-y-4">
    <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
      <UFormGroup label="Name" name="name" required>
        <UInput v-model="state.name" type="text" autofocus />
      </UFormGroup>

      <UFormGroup label="Email" name="email" required>
        <UInput
          v-model="state.email"
          placeholder="you@example.com"
          icon="i-heroicons-envelope"
          trailing
          type="email"
        />
      </UFormGroup>

      <UFormGroup
        label="Password"
        name="password"
        hint="min 8 characters"
        :ui="{ hint: 'text-xs text-gray-500 dark:text-gray-400' }"
        required
      >
        <div class="relative">
          <UInput
            v-model="state.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="off"
          />
          <UButton
            class="absolute right-0 top-0 bottom-0"
            color="gray"
            variant="ghost"
            :icon="showPassword ? 'i-heroicons-eye-slash' : 'i-heroicons-eye'"
            @click="showPassword = !showPassword"
          />
        </div>
      </UFormGroup>

      <UFormGroup label="Repeat Password" name="password_confirmation" required>
        <div class="relative">
          <UInput
            v-model="state.password_confirmation"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            autocomplete="off"
          />
          <UButton
            class="absolute right-0 top-0 bottom-0"
            color="gray"
            variant="ghost"
            :icon="showPasswordConfirmation ? 'i-heroicons-eye-slash' : 'i-heroicons-eye'"
            @click="showPasswordConfirmation = !showPasswordConfirmation"
          />
        </div>
      </UFormGroup>

      <div class="flex items-center justify-end space-x-4">
        <UButton type="submit" label="Sign Up" :loading="registerStatus === 'pending'" />
      </div>
    </UForm>

    <div class="text-sm">
      Already have an account?
      <NuxtLink class="text-sm" to="/auth/login">Login now</NuxtLink>
    </div>
  </div>
</template>
