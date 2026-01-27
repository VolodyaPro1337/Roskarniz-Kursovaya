<script setup lang="ts">
import { onMounted, ref } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import AuthModal from '@/components/AuthModal.vue';
import MegaFooter from '@/components/MegaFooter.vue';
import Navigation from '@/components/Navigation.vue';

gsap.registerPlugin(ScrollTrigger);

const isAuthModalOpen = ref(false);

onMounted(() => {
    // Disable lag smoothing to prevent jumps
    gsap.ticker.lagSmoothing(0);
});
</script>

<template>
    <div class="main-layout bg-[#050505] min-h-screen flex flex-col">
        <!-- Global Navigation -->
        <Navigation @open-auth="isAuthModalOpen = true" />

        <!-- Global Auth Modal -->
        <AuthModal :show="isAuthModalOpen" @close="isAuthModalOpen = false" />

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>
        
        <!-- Global Footer -->
        <MegaFooter />
    </div>
</template>

