<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import gsap from 'gsap';

const heroText = ref<HTMLElement | null>(null);
const glassCard = ref<HTMLElement | null>(null);

onMounted(() => {
    // Basic Mouse Parallax Effect
    window.addEventListener('mousemove', (e) => {
        if (!glassCard.value) return;
        
        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;

        gsap.to(glassCard.value, {
            x: x,
            y: y,
            duration: 0.5,
            ease: "power2.out"
        });
    });

    // Intro Animation
    gsap.from(heroText.value, {
        y: 50,
        opacity: 0,
        duration: 1.5,
        ease: "expo.out",
        delay: 0.2
    });
});
</script>

<template>
    <Head title="Шторы" />

    <div class="relative min-h-screen overflow-hidden bg-[#0f0f11] text-white selection:bg-red-500 selection:text-white">
        <!-- Abstract Background Blobs -->
        <div class="absolute top-[-20%] left-[-10%] h-[500px] w-[500px] rounded-full bg-purple-600/30 blur-[120px]"></div>
        <div class="absolute bottom-[-20%] right-[-10%] h-[600px] w-[600px] rounded-full bg-red-600/20 blur-[120px]"></div>

        <!-- Main Content -->
        <main class="relative z-10 flex min-h-screen flex-col items-center justify-center p-6">
            
            <!-- Glass Card -->
            <div 
                ref="glassCard"
                class="glass-panel relative flex aspect-[1.58/1] w-full max-w-4xl flex-col items-center justify-center overflow-hidden rounded-3xl border border-white/10 bg-white/5 shadow-2xl backdrop-blur-3xl"
            >
                <!-- Decorative Elements inside card -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-50"></div>
                
                <h1 
                    ref="heroText"
                    class="relative z-20 text-center font-bold tracking-tighter"
                >
                    <span class="block text-[120px] leading-none text-transparent bg-clip-text bg-gradient-to-br from-white to-white/50">
                        ШТОРЫ
                    </span>
                    <span class="block text-xl font-light tracking-[0.5em] text-white/60 mt-4 uppercase">
                        Информационная Система
                    </span>
                </h1>

                <!-- Call to Action / Navigation -->
                <div class="mt-12 flex gap-6 z-20 opacity-0 animate-fade-in-up" style="animation-delay: 1s; animation-fill-mode: forwards;">
                    <button class="group relative px-8 py-3 rounded-full bg-white/10 border border-white/10 hover:bg-white/20 transition-all duration-300">
                        <span class="text-sm font-medium tracking-wider">Каталог</span>
                        <div class="absolute -bottom-1 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-red-500 to-transparent scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
                    </button>
                    <button class="group relative px-8 py-3 rounded-full bg-red-600/80 hover:bg-red-600 transition-all duration-300 shadow-lg shadow-red-900/20">
                        <span class="text-sm font-medium tracking-wider text-white">Войти</span>
                    </button>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div class="absolute bottom-10 animate-bounce cursor-pointer opacity-50 hover:opacity-100 transition-opacity">
               <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </div>
        </main>

       <!-- Image Sequence Placeholder Section -->
       <section class="min-h-screen bg-black flex items-center justify-center">
            <h2 class="text-4xl font-bold text-white/20">Здесь будет анимация открытия штор (Image Sequence)</h2>
       </section>
    </div>
</template>

<style scoped>
.glass-panel {
    /* Advanced Glassmorphism Texture */
    background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation-name: fade-in-up;
    animation-duration: 0.8s;
    animation-timing-function: ease-out;
}
</style>
