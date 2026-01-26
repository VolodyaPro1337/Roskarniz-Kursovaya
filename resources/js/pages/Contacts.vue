<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Navigation from '@/components/Navigation.vue';
import CustomCursor from '@/components/CustomCursor.vue';
</script>

<template>
    <Head title="Контакты" />
    <Navigation />
    <CustomCursor />

    <div class="bg-[#050505] text-white selection:bg-white selection:text-black overflow-x-hidden font-sans min-h-screen">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen">
            <!-- Left Info Column -->
            <div class="relative flex flex-col justify-center px-8 md:px-20 py-32 lg:py-0">
                <!-- Background Noise -->
                <div class="absolute inset-0 bg-[#050505]">
                    <div class="absolute inset-0 bg-[url('/images/noise.png')] opacity-10 mix-blend-overlay"></div>
                </div>

                <div class="relative z-10">
                    <h1 class="text-6xl md:text-8xl font-black mb-16 tracking-tighter">СВЯЗЬ</h1>

                    <div class="space-y-16">
                        <!-- Address -->
                        <div class="group cursor-pointer">
                            <span class="text-xs font-mono text-gray-500 uppercase tracking-widest mb-4 block group-hover:text-white transition-colors">Адрес</span>
                            <p class="text-2xl md:text-3xl font-light leading-snug">
                                Москва, Кутузовский пр-т, 12<br>
                                ЖК "Бадаевский"
                            </p>
                        </div>

                        <!-- Contact Info -->
                        <div class="flex flex-col gap-8">
                             <div class="group cursor-pointer">
                                <span class="text-xs font-mono text-gray-500 uppercase tracking-widest mb-2 block group-hover:text-white transition-colors">Телефон</span>
                                <a href="tel:+74950000000" class="text-2xl md:text-3xl font-light hover:text-gray-400 transition-colors">
                                    +7 (495) 000-00-00
                                </a>
                            </div>

                            <div class="group cursor-pointer">
                                <span class="text-xs font-mono text-gray-500 uppercase tracking-widest mb-2 block group-hover:text-white transition-colors">Email</span>
                                <a href="mailto:hello@roskarniz.ru" class="text-2xl md:text-3xl font-light hover:text-gray-400 transition-colors">
                                    hello@roskarniz.ru
                                </a>
                            </div>
                        </div>

                        <!-- Socials -->
                        <div>
                             <span class="text-xs font-mono text-gray-500 uppercase tracking-widest mb-6 block">Социальные сети</span>
                             <div class="flex gap-4">
                                <a href="#" class="px-6 py-3 border border-white/10 rounded-full hover:bg-white hover:text-black transition-all uppercase text-xs font-bold tracking-widest">Telegram</a>
                                <a href="#" class="px-6 py-3 border border-white/10 rounded-full hover:bg-white hover:text-black transition-all uppercase text-xs font-bold tracking-widest">Instagram</a>
                                <a href="#" class="px-6 py-3 border border-white/10 rounded-full hover:bg-white hover:text-black transition-all uppercase text-xs font-bold tracking-widest">WhatsApp</a>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Visual Column -->
            <div class="relative h-[50vh] lg:h-auto overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/images/contacts_showroom.png');">
                     <!-- 1. Global Darkening (Затемнение) -->
                     <div class="absolute inset-0 bg-black/40"></div>

                     <!-- Mobile Gradient -->
                     <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/50 to-transparent lg:hidden"></div>
                     
                     <!-- Desktop Gradient (Left to Right) - Wide Smooth Transition -->
                     <div class="hidden lg:block absolute inset-0 bg-gradient-to-r from-[#050505] via-[#050505] via-10% to-transparent to-60%"></div>
                </div>
                
                <!-- Map Overlay Hint -->
                <div class="absolute bottom-10 right-10 mix-blend-difference z-20 hidden lg:block">
                     <div class="w-32 h-32 rounded-full border border-white/30 flex items-center justify-center animate-spin-slow">
                        <svg class="w-full h-full p-2" viewBox="0 0 100 100">
                             <defs>
                                <path id="circle" d="M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0" />
                            </defs>
                            <text font-size="10" font-weight="bold" fill="white" letter-spacing="2">
                                <textPath href="#circle">
                                    ПОКАЗАТЬ НА КАРТЕ • SHOW ON MAP •
                                </textPath>
                            </text>
                        </svg>
                     </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 10s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
