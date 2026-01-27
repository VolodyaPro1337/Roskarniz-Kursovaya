<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CustomCursor from '@/components/CustomCursor.vue';


// --- State ---
const activeCategory = ref('all');
const email = ref('');


// --- Data ---
const categories = [
    { id: 'all', name: 'Все' },
    { id: 'trends', name: 'Тренды 2026' },
    { id: 'tech', name: 'Технологии' },
    { id: 'care', name: 'Уход' }
];

const articles = [
    {
        id: 1,
        title: 'Умный дом: Будущее на вашем карнизе',
        excerpt: 'Как голосовое управление и автоматизация меняют сценарии жизни. Полный обзор экосистемы Somfy и Aqara.',
        category: 'tech',
        date: '24 Января',
        image: '/images/blog/smart-home.png',
        readTime: '6 мин'
    },
    {
        id: 2,
        title: 'Эстетика Минимализма',
        excerpt: 'Почему мы выбираем "меньше, но лучше". Философия пустого пространства в современном интерьере.',
        category: 'trends',
        date: '20 Января',
        image: '/images/atelier/style_scandi.png',
        readTime: '4 мин'
    },
    {
        id: 3,
        title: 'Бархат: Королевский текстиль',
        excerpt: 'Секреты ухода за самыми капризными тканями. Как сохранить ворс идеальным на долгие годы.',
        category: 'care',
        date: '15 Января',
        image: '/images/blog/velvet-care.png',
        readTime: '8 мин'
    },
    {
        id: 4,
        title: 'Киберпанк в интерьере',
        excerpt: 'Неоновая подсветка штор и металлизированные ткани. Тренды будущего уже сегодня.',
        category: 'trends',
        date: '10 Января',
        image: '/images/blog/trends.png',
        readTime: '5 мин'
    }
];

// --- Computed ---
const filteredArticles = computed(() => {
    if (activeCategory.value === 'all') return articles;
    return articles.filter(a => a.category === activeCategory.value);
});
</script>

<script lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
export default {
    layout: MainLayout
}
</script>

<template>
    <Head title="Журнал" />
    <CustomCursor />

    <div class="bg-[#050505] text-white selection:bg-red-500 selection:text-white overflow-x-hidden font-sans min-h-screen">
        
        <!-- HERO HEADER (80vh to guarantee separation) -->
        <header class="h-[80vh] flex flex-col items-center justify-center relative px-4 overflow-hidden">
            <!-- Background Elements -->
            <div class="absolute inset-0 pointer-events-none">
                 <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[60vw] h-[60vw] bg-white/5 rounded-full blur-[100px] opacity-30 animate-pulse-slow"></div>
            </div>

            <div class="relative z-10 text-center space-y-8 max-w-4xl mx-auto">
                

                
                <h1 class="text-6xl md:text-9xl font-black tracking-tighter leading-none">
                    ROSKARNIZ<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-600">MEDIA</span>
                </h1>

                <p class="text-xl md:text-2xl text-gray-400 font-light max-w-2xl mx-auto leading-relaxed">
                    Гид по стилю, технологиям и культуре оформления пространства.
                </p>
            </div>
        </header>

        <!-- STICKY FILTERS -->
        <div class="sticky top-0 z-40 bg-[#050505]/80 backdrop-blur-xl border-y border-white/5 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-center gap-8 py-6 overflow-x-auto custom-scrollbar">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="activeCategory = cat.id"
                        class="text-sm font-bold uppercase tracking-widest transition-colors whitespace-nowrap relative group py-2"
                        :class="activeCategory === cat.id ? 'text-white' : 'text-gray-500 hover:text-white'"
                    >
                        {{ cat.name }}
                        <span class="absolute bottom-0 left-0 w-full h-[2px] bg-red-500 transform scale-x-0 transition-transform duration-300" :class="activeCategory === cat.id ? 'scale-x-100' : 'group-hover:scale-x-50'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN FEED -->
        <main class="max-w-5xl mx-auto px-4 md:px-8 py-32 space-y-40">
            
            <!-- First Half of Articles -->
            <template v-for="article in filteredArticles.slice(0, 2)" :key="article.id">
                <article class="group relative flex flex-col items-center">
                    <!-- Image Wrapper -->
                    <div class="w-full relative cursor-pointer overflow-hidden rounded-[2rem]">
                        <div class="aspect-[16/10] md:aspect-[21/9]">
                            <img 
                                :src="article.image" 
                                class="w-full h-full object-cover transition-transform duration-[1.5s] ease-out group-hover:scale-105"
                                :class="{'grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100': true}"
                                alt=""
                            >
                        </div>
                        <div class="absolute top-6 left-6 z-10">
                            <div class="px-4 py-2 bg-black/60 backdrop-blur-md border border-white/10 rounded-lg text-xs font-bold uppercase tracking-widest text-white shadow-xl">
                                {{ categories.find(c => c.id === article.category)?.name }}
                            </div>
                        </div>
                    </div>

                    <!-- Text Content -->
                    <div class="max-w-3xl text-center -mt-10 relative z-10 px-4">
                        <div class="bg-[#0a0a0a] border border-white/10 p-8 md:p-12 rounded-3xl shadow-2xl transition-transform duration-500 group-hover:-translate-y-4">
                            <div class="flex items-center justify-center gap-4 text-xs font-mono text-gray-500 mb-6 uppercase tracking-widest">
                                <span>{{ article.date }}</span>
                                <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                <span>{{ article.readTime }}</span>
                            </div>
                            <h2 class="text-3xl md:text-5xl font-bold mb-6 leading-tight group-hover:text-red-500 transition-colors">
                                {{ article.title }}
                            </h2>
                            <p class="text-gray-400 text-lg leading-relaxed mb-8 font-light">
                                {{ article.excerpt }}
                            </p>
                            <button class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest border-b border-white/20 pb-1 hover:border-red-500 hover:text-red-500 transition-all">
                                Читать полностью
                            </button>
                        </div>
                    </div>
                </article>
            </template>

            <!-- INTERRUPT BLOCK: QUOTE -->
            <section class="py-20 text-center relative overflow-hidden" v-if="activeCategory === 'all'">
                <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none select-none">
                     <span class="text-[20vw] font-black leading-none tracking-tighter mix-blend-difference">VISION</span>
                </div>
                <div class="relative z-10 max-w-4xl mx-auto px-4">
                    <svg class="w-12 h-12 text-red-500 mx-auto mb-8 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.896 14.389 15.953 15.122 15.171C15.855 14.389 16.794 14 17.964 14C18.232 14 18.604 14.053 19.06 14.152L19.06 13C19.06 10.968 18.389 9.387 17.027 8.256C15.665 7.124 13.911 6.643 11.758 6.784L11.758 4C15.341 3.511 18.15 4.542 20.198 7.152C22.256 9.752 23.275 14.062 23.275 19.969L23.275 21L14.017 21ZM5.01172 21L5.01172 18C5.01172 16.896 5.37872 15.953 6.11172 15.171C6.84472 14.389 7.78872 14 8.94872 14C9.22172 14 9.59372 14.053 10.0497 14.152L10.0497 13C10.0497 10.968 9.37872 9.387 8.01672 8.256C6.65472 7.124 4.90072 6.643 2.74772 6.784L2.74772 4C6.33072 3.511 9.13972 4.542 11.1877 7.152C13.2457 9.752 14.2647 14.062 14.2647 19.969L14.2647 21L5.01172 21Z"/></svg>
                    <blockquote class="text-3xl md:text-5xl font-light leading-tight mb-8">
                        "Текстиль — это не просто ткань. Это <span class="text-white font-bold decoration-red-500 underline decoration-2 underline-offset-8">язык</span>, на котором говорит ваш дом."
                    </blockquote>
                    <cite class="text-sm font-mono uppercase tracking-[0.3em] text-gray-500 not-italic">
                        — ROSKARNIZ DESIGN TEAM
                    </cite>
                </div>
            </section>

            <!-- Second Half of Articles -->
            <template v-for="article in filteredArticles.slice(2)" :key="article.id">
                <article class="group relative flex flex-col items-center">
                    <div class="w-full relative cursor-pointer overflow-hidden rounded-[2rem]">
                        <div class="aspect-[16/10] md:aspect-[21/9]">
                            <img 
                                :src="article.image" 
                                class="w-full h-full object-cover transition-transform duration-[1.5s] ease-out group-hover:scale-105"
                                :class="{'grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100': true}"
                                alt=""
                            >
                        </div>
                        <div class="absolute top-6 left-6 z-10">
                            <div class="px-4 py-2 bg-black/60 backdrop-blur-md border border-white/10 rounded-lg text-xs font-bold uppercase tracking-widest text-white shadow-xl">
                                {{ categories.find(c => c.id === article.category)?.name }}
                            </div>
                        </div>
                    </div>
                    <div class="max-w-3xl text-center -mt-10 relative z-10 px-4">
                        <div class="bg-[#0a0a0a] border border-white/10 p-8 md:p-12 rounded-3xl shadow-2xl transition-transform duration-500 group-hover:-translate-y-4">
                            <div class="flex items-center justify-center gap-4 text-xs font-mono text-gray-500 mb-6 uppercase tracking-widest">
                                <span>{{ article.date }}</span>
                                <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                <span>{{ article.readTime }}</span>
                            </div>
                            <h2 class="text-3xl md:text-5xl font-bold mb-6 leading-tight group-hover:text-red-500 transition-colors">
                                {{ article.title }}
                            </h2>
                            <p class="text-gray-400 text-lg leading-relaxed mb-8 font-light">
                                {{ article.excerpt }}
                            </p>
                            <button class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest border-b border-white/20 pb-1 hover:border-red-500 hover:text-red-500 transition-all">
                                Читать полностью
                            </button>
                        </div>
                    </div>
                </article>
            </template>
        </main>

        <!-- SUBSCRIBE -->
        <section class="py-32 border-t border-white/5 bg-[#080808] relative overflow-hidden">
             <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-red-900/10 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="max-w-xl mx-auto px-4 text-center relative z-10">
                <h3 class="text-4xl font-bold mb-4">Оставайтесь в тренде</h3>
                <p class="text-gray-500 mb-10">Еженедельная подборка лучших интерьерных решений.</p>
                
                <form @submit.prevent class="relative">
                    <input 
                        v-model="email"
                        type="email" 
                        placeholder="example@mail.com" 
                        class="w-full bg-[#111] border border-white/10 rounded-full py-4 pl-6 pr-32 focus:outline-none focus:border-red-500 transition-colors text-white"
                    >
                    <button class="absolute top-1 right-1 bottom-1 px-8 bg-white text-black rounded-full text-xs font-bold uppercase tracking-widest hover:bg-gray-200 transition-colors">
                        Join
                    </button>
                </form>
            </div>
        </section>


    </div>
</template>

<style scoped>
.animate-pulse-slow {
    animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.1; }
}
</style>
