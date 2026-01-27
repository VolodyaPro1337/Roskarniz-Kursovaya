<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import MainLayout from '@/layouts/MainLayout.vue';



// Mock Data
const user = {
    name: 'Александр',
    status: 'Premium Client',
    points: 1250
};

const activeOrder = {
    id: '#ORDER-2491',
    status: 'Пошив',
    progress: 60,
    items: ['Шторы Blackout (Спалья)', 'Тюль (Гостиная)'],
    date: '24.01.2026',
    estimated: '05.02.2026'
};

const moodboards = [
    { id: 1, name: 'Сканди Гостиная', items: 4, date: '20.01', image: '/images/atelier/style_scandi.png' },
    { id: 2, name: 'Лофт Кухня', items: 2, date: '18.01', image: '/images/atelier/style_loft.png' },
    { id: 3, name: 'Спальня Dark', items: 6, date: '15.01', image: '/images/atelier/style_minimal.png' },
];

const timeline = [
    { status: 'Оформление', done: true },
    { status: 'Замер', done: true },
    { status: 'Пошив', done: false, active: true }, // Current
    { status: 'Доставка', done: false },
    { status: 'Монтаж', done: false }
];
</script>

<script lang="ts">
export default {
    layout: MainLayout
}
</script>

<template>
    <Head title="Личный кабинет" />

    <!-- Padding for fixed Nav -->
    <div class="pt-32 px-4 md:px-12 min-h-screen bg-[#050505] text-white font-sans pb-20">
        
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-end mb-16 border-b border-white/10 pb-8">
                <div>
                    <h1 class="text-4xl md:text-6xl font-black mb-2 tracking-tight">
                        <span class="text-gray-500 font-light">Welcome back,</span> <br>
                        {{ user.name }}
                    </h1>
                    <div class="flex gap-4 items-center text-sm font-mono mt-4">
                        <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10 text-gray-300">{{ user.status }}</span>
                        <span class="text-red-500">⚜ {{ user.points }} pts</span>
                    </div>
                </div>

                <div class="flex gap-4 mt-8 md:mt-0">
                    <button class="px-6 py-3 border border-white/20 rounded-full hover:bg-white hover:text-black transition-all text-xs font-bold uppercase tracking-wider">
                        Настройки
                    </button>
                    <button class="px-6 py-3 bg-red-600 rounded-full hover:bg-red-500 transition-all text-xs font-bold uppercase tracking-wider">
                        + Новый проект
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 md:gap-16">
                
                <!-- LEFT COLUMN: Active Order -->
                <div class="lg:col-span-2 space-y-12">
                    
                    <!-- Active Order Card -->
                    <section>
                        <h2 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-6">Активный заказ</h2>
                        <div class="bg-[#0a0a0a] border border-white/10 rounded-3xl p-8 relative overflow-hidden group">
                            <!-- Background Graph/Pattern -->
                            <div class="absolute inset-0 bg-[url('/images/noise.png')] opacity-5 mix-blend-overlay"></div>
                            <div class="absolute top-0 right-0 w-64 h-64 bg-red-900/10 rounded-full blur-[80px]"></div>

                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-8">
                                    <div>
                                        <div class="text-3xl font-bold mb-1">{{ activeOrder.id }}</div>
                                        <div class="text-gray-400 text-sm">Ожидаемая готовность: <span class="text-white">{{ activeOrder.estimated }}</span></div>
                                    </div>
                                    <div class="px-3 py-1 bg-red-500/20 text-red-500 border border-red-500/30 rounded-full text-xs font-bold uppercase animate-pulse">
                                        В работе
                                    </div>
                                </div>

                                <!-- Timeline Visual -->
                                <div class="relative py-8">
                                    <div class="absolute top-1/2 left-0 w-full h-[2px] bg-white/5 -translate-y-1/2"></div>
                                    <div class="absolute top-1/2 left-0 h-[2px] bg-red-600 -translate-y-1/2 transition-all duration-1000" :style="{ width: activeOrder.progress + '%' }"></div>
                                    
                                    <div class="flex justify-between relative z-10">
                                        <div v-for="(step, idx) in timeline" :key="idx" class="flex flex-col items-center gap-4">
                                            <div 
                                                class="w-4 h-4 rounded-full border-2 transition-all duration-300 bg-[#0a0a0a]"
                                                :class="[
                                                    step.done || step.active ? 'border-red-500 bg-red-500 shadow-[0_0_10px_rgba(220,38,38,0.5)]' : 'border-gray-700'
                                                ]"
                                            ></div>
                                            <span 
                                                class="text-[10px] uppercase font-bold tracking-wider transition-colors"
                                                :class="step.active ? 'text-white' : 'text-gray-600'"
                                            >
                                                {{ step.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items List -->
                                <div class="mt-8 pt-8 border-t border-white/5">
                                    <div class="flex gap-4 overflow-x-auto pb-2 no-scrollbar">
                                        <div v-for="item in activeOrder.items" :key="item" class="flex items-center gap-3 bg-white/5 px-4 py-2 rounded-xl text-sm whitespace-nowrap">
                                            <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                            {{ item }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Saved Moodboards -->
                    <section>
                        <div class="flex justify-between items-end mb-6">
                            <h2 class="text-xs font-mono uppercase tracking-widest text-gray-500">Ваши проекты</h2>
                            <button class="text-xs text-white border-b border-white hover:text-red-500 hover:border-red-500 transition-colors">Показать все</button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="board in moodboards" :key="board.id" class="group relative aspect-[4/3] rounded-2xl overflow-hidden bg-[#111] border border-white/5 hover:border-white/30 transition-all cursor-pointer">
                                <img :src="board.image" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:scale-105 transition-transform duration-700" alt="">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 to-transparent"></div>
                                
                                <div class="absolute bottom-6 left-6 right-6">
                                    <h3 class="text-xl font-bold mb-1">{{ board.name }}</h3>
                                    <div class="flex justify-between items-center text-xs text-gray-400">
                                        <span>{{ board.items }} элементов</span>
                                        <span>{{ board.date }}</span>
                                    </div>
                                </div>

                                <!-- Quick Actions overlay -->
                                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
                                     <button class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center hover:scale-110 transition-transform" title="Скачать PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </button>
                                    <button class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center hover:scale-110 transition-transform" title="Открыть в Ателье">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>

                <!-- RIGHT COLUMN: Services & Manager -->
                <div class="space-y-8">
                     <!-- Personal Manager -->
                     <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-full bg-gray-700"></div> <!-- Avatar Placeholder -->
                            <div>
                                <div class="font-bold">Екатерина</div>
                                <div class="text-xs text-gray-400">Ваш персональный менеджер</div>
                            </div>
                        </div>
                        <button class="w-full py-3 bg-white text-black font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition-colors mb-2">
                             Написать в WhatsApp
                        </button>
                        <button class="w-full py-3 border border-white/10 text-white font-bold text-xs uppercase rounded-xl hover:bg-white/10 transition-colors">
                             Позвонить
                        </button>
                     </div>

                     <!-- Quick Actions -->
                     <section>
                        <h3 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-4">Быстрые действия</h3>
                        <div class="space-y-2">
                            <button class="w-full text-left p-4 hover:bg-white/5 rounded-xl transition-colors flex justify-between group">
                                <span class="text-sm font-medium">Вызвать замерщика</span>
                                <span class="text-gray-500 group-hover:text-white">→</span>
                            </button>
                            <button class="w-full text-left p-4 hover:bg-white/5 rounded-xl transition-colors flex justify-between group">
                                <span class="text-sm font-medium">Мои адреса</span>
                                <span class="text-gray-500 group-hover:text-white">→</span>
                            </button>
                            <button class="w-full text-left p-4 hover:bg-white/5 rounded-xl transition-colors flex justify-between group">
                                <span class="text-sm font-medium">История платежей</span>
                                <span class="text-gray-500 group-hover:text-white">→</span>
                            </button>
                        </div>
                     </section>
                </div>

            </div>

        </div>
    </div>
</template>
