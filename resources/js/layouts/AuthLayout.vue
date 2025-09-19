<template>
    <div class="bg-login">
      <!--wrapper-->
      <div class="wrapper">
        <div
          class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0"
        >
          <div class="container-fluid">
            <div class="row row-cols-1 ls-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-3">
              <div class="col mx-auto">
                <div class="mb-4 text-center">
                  <img :src="AppLogo" width="100" alt="App Image" />
                </div>
                <div class="card">
                  <div class="card-body">
                    <div class="border p-4 rounded">
                      <div class="text-center">
                        <h3 class="">{{ title }}</h3>
                        <p>
                          {{ description }}
                          <Link :href="loginUrl">{{ loginText }}</Link>
                        </p>
                      </div>
                      <slot />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!--end row-->
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/image/logo-icon.png';
defineProps({
    title: String,
    description: String,
    loginText: String,
    loginUrl: String
})
</script>

<style></style>
