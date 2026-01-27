<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineEmits(['open-auth']);

const links = [
    { name: 'Каталог', href: '/catalog' },
    { name: 'Ателье', href: '/atelier' },
    { name: 'Блог', href: '/blog' },
    { name: 'Контакты', href: '/contacts' },
];
</script>

<template>
    <nav class="fixed top-0 left-0 w-full z-50 px-8 py-6 flex justify-between items-center mix-blend-difference text-white">
        <!-- Логотип -->
        <a href="/" class="text-2xl font-black tracking-tighter cursor-hover hover:scale-105 transition-transform">
            ROSKARNIZ
        </a>

        <!-- Меню для десктопа -->
        <div class="hidden md:flex gap-8 items-center bg-white/10 px-8 py-3 rounded-full backdrop-blur-md border border-white/10">
            <Link 
                v-for="link in links" 
                :key="link.name" 
                :href="link.href" 
                class="text-sm font-medium uppercase tracking-wider hover:text-red-500 transition-colors cursor-hover"
            >
                {{ link.name }}
            </Link>
        </div>

        <!-- Корзина / Меню -->
        <div class="flex gap-4 items-center">
            
            <!-- Auth / Dashboard Button (Temporary Bypass) -->
            <a 
                href="/dashboard"
                class="w-12 h-12 rounded-full border border-white/20 flex items-center justify-center hover:bg-white hover:text-black transition-all cursor-hover group relative"
                title="Личный кабинет"
            >
                <!-- Fake notification dot -->
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-black"></div>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </a>

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
