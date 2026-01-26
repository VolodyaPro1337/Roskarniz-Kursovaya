<script setup lang="ts">
import { ref } from 'vue';

const email = ref('');
</script>

<template>
    <footer class="bg-black text-white pt-32 pb-12 overflow-hidden relative">
        <!-- Abstract Background -->
        <div class="absolute inset-0 z-0 opacity-20 pointer-events-none">
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-red-900/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-blue-900/5 rounded-full blur-[100px]"></div>
            <div class="absolute inset-0 bg-[url('/images/noise.png')] opacity-10 mix-blend-overlay"></div>
        </div>

        <div class="max-w-[1400px] mx-auto px-4 relative z-10">
            <!-- Top Section: CTA & Logo -->
            <div class="flex flex-col md:flex-row justify-between items-start mb-32 border-b border-white/10 pb-20">
                <div class="mb-12 md:mb-0">
                    <h2 class="text-[12vw] leading-[0.8] font-black tracking-tighter mix-blend-difference select-none text-transparent bg-clip-text bg-gradient-to-br from-white to-gray-800">
                        ROSKARNIZ
                    </h2>
                </div>
                
                <div class="w-full md:w-auto max-w-md">
                    <p class="text-xl text-gray-400 mb-8 font-light">
                        Оставайтесь в курсе последних трендов в мире текстильного дизайна.
                    </p>
                    <form @submit.prevent class="relative group">
                        <input 
                            v-model="email"
                            type="email" 
                            placeholder="Ваш email" 
                            class="w-full bg-white/5 border border-white/10 rounded-full py-4 pl-6 pr-16 focus:outline-none focus:bg-white/10 focus:border-white/30 transition-all text-white placeholder:text-gray-600"
                        >
                        <button class="absolute top-2 right-2 w-10 h-10 bg-white rounded-full flex items-center justify-center text-black hover:bg-red-500 hover:text-white transition-all transform group-hover:rotate-45">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Links Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-12 md:gap-8 mb-32">
                <!-- Column 1 -->
                <div>
                    <h3 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-8">Навигация</h3>
                    <nav class="flex flex-col gap-4">
                        <a href="/" class="hover:text-red-500 transition-colors">Главная</a>
                        <a href="/catalog" class="hover:text-red-500 transition-colors">Каталог</a>
                        <a href="/atelier" class="hover:text-red-500 transition-colors">Ателье</a>
                        <a href="/blog" class="hover:text-red-500 transition-colors">Журнал</a>
                    </nav>
                </div>

                <!-- Column 2 -->
                <div>
                    <h3 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-8">Услуги</h3>
                    <nav class="flex flex-col gap-4">
                        <a href="#" class="hover:text-gray-300 transition-colors">Выезд дизайнера</a>
                        <a href="#" class="hover:text-gray-300 transition-colors">Пошив штор</a>
                        <a href="#" class="hover:text-gray-300 transition-colors">Монтаж карнизов</a>
                        <a href="#" class="hover:text-gray-300 transition-colors">Химчистка</a>
                    </nav>
                </div>

                <!-- Column 3 -->
                <div>
                    <h3 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-8">Контакты</h3>
                    <nav class="flex flex-col gap-4">
                        <a href="tel:+74950000000" class="hover:text-white transition-colors">+7 (495) 000-00-00</a>
                        <a href="mailto:hello@roskarniz.ru" class="hover:text-white transition-colors">hello@roskarniz.ru</a>
                        <span class="text-gray-500">Москва, Кутузовский пр-т, 12</span>
                    </nav>
                </div>

                <!-- Column 4 -->
                <div>
                    <h3 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-8">Socials</h3>
                    <nav class="flex flex-col gap-4">
                        <a href="#" class="flex items-center gap-2 hover:text-blue-400 transition-colors">
                            <span>Telegram</span>
                            <svg class="w-3 h-3 -rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="#" class="flex items-center gap-2 hover:text-purple-400 transition-colors">
                            <span>Instagram</span>
                            <svg class="w-3 h-3 -rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="flex flex-col md:flex-row justify-between items-center text-xs text-gray-600 uppercase tracking-widest font-mono border-t border-white/5 pt-12">
                <p>© 2026 ROSKARNIZ. All rights reserved.</p>
                <div class="flex gap-8 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
</template>
