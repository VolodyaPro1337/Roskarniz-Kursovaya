<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import MegaFooter from '@/components/MegaFooter.vue';
import Navigation from '@/components/Navigation.vue';
import AuthModal from '@/components/AuthModal.vue';
import Lenis from '@studio-freight/lenis';

let lenis: Lenis | null = null;
const isAuthModalOpen = ref(false);

onMounted(() => {
    // Initialize Smooth Scroll
    lenis = new Lenis({
        duration: 2, 
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), 
        orientation: 'vertical', 
        gestureOrientation: 'vertical',
        smoothWheel: true,
        wheelMultiplier: 1,
        touchMultiplier: 2,
    });

    function raf(time: number) {
        lenis?.raf(time);
        requestAnimationFrame(raf);
    }

    requestAnimationFrame(raf);
});

onUnmounted(() => {
    lenis?.destroy();
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

<style>
html.lenis {
  height: auto;
}

.lenis.lenis-smooth {
  scroll-behavior: auto;
}

.lenis.lenis-smooth [data-lenis-prevent] {
  overscroll-behavior: contain;
}

.lenis.lenis-stopped {
  overflow: hidden;
}

.lenis.lenis-scrolling iframe {
  pointer-events: none;
}
</style>
