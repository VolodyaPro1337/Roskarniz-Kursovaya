<script setup lang="ts">
import { ref } from 'vue';

const links = [
    { name: 'Каталог', href: '/catalog' },
    { name: 'Ателье', href: '/atelier' },
    { name: 'Блог', href: '/blog' },
    { name: 'Контакты', href: '/contacts' },
];

const isMenuOpen = ref(false);
</script>

<template>
    <nav class="fixed top-0 left-0 w-full z-50 px-8 py-6 flex justify-between items-center mix-blend-difference text-white">
        <!-- Логотип -->
        <a href="/" class="text-2xl font-black tracking-tighter cursor-hover hover:scale-105 transition-transform">
            ROSKARNIZ
        </a>

        <!-- Меню для десктопа -->
        <div class="hidden md:flex gap-8 items-center bg-white/10 px-8 py-3 rounded-full backdrop-blur-md border border-white/10">
            <a 
                v-for="link in links" 
                :key="link.name" 
                :href="link.href" 
                class="text-sm font-medium uppercase tracking-wider hover:text-red-500 transition-colors cursor-hover"
            >
                {{ link.name }}
            </a>
        </div>

        <!-- Корзина / Меню -->
        <div class="flex gap-4 items-center">
            <button class="w-12 h-12 rounded-full border border-white/20 flex items-center justify-center hover:bg-white hover:text-black transition-all cursor-hover group relative">
                <span class="absolute -top-1 -right-1 flex h-4 w-4">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[8px] items-center justify-center font-bold text-white">0</span>
                </span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </button>
            <button class="md:hidden w-12 h-12 flex flex-col gap-1.5 items-center justify-center cursor-hover">
                <div class="w-6 h-0.5 bg-white"></div>
                <div class="w-6 h-0.5 bg-white"></div>
            </button>
        </div>
    </nav>
</template>

<style scoped>
/* Опционально: специфичные стили навигации если нужно */
</style>
