<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import Navigation from '@/components/Navigation.vue';
import CustomCursor from '@/components/CustomCursor.vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

// --- Данные для истории прокрутки (Процесс) ---
const processSteps = [
    { id: 1, title: 'Замер', description: 'Наши эксперты приезжают с образцами и лазерной точностью.', icon: '📏' },
    { id: 2, title: 'Эскиз', description: 'Создаем визуализацию в интерьере до начала работ.', icon: '✏️' },
    { id: 3, title: 'Пошив', description: 'Ручная работа в нашем цеху. Идеальные швы.', icon: '🧵' },
    { id: 4, title: 'Монтаж', description: 'Чистая установка карнизов и развеска штор.', icon: '🔨' }
];

const processContainer = ref<HTMLElement | null>(null);

// --- Состояние портфолио (До/После) ---
const sliderValue = ref(50);

// --- Состояние Мудборда ---
const selectedStyles = ref<string[]>([]);
const styles = [
    { id: 'minimal', name: 'Минимализм', color: '#f5f5f5' },
    { id: 'classic', name: 'Классика', color: '#e5e7eb' },
    { id: 'loft', name: 'Лофт', color: '#525252' },
    { id: 'scandi', name: 'Сканди', color: '#d1d5db' }
];

const toggleStyle = (id: string) => {
    if (selectedStyles.value.includes(id)) {
        selectedStyles.value = selectedStyles.value.filter(s => s !== id);
    } else {
        selectedStyles.value.push(id);
    }
};

onMounted(() => {
    // Горизонтальная прокрутка процесса
    if (processContainer.value) {
        gsap.to(processContainer.value, {
            x: () => -(processContainer.value!.scrollWidth - window.innerWidth),
            ease: "none",
            scrollTrigger: {
                trigger: "#process-section",
                pin: true,
                scrub: 1,
                end: () => "+=" + processContainer.value!.scrollWidth,
            }
        });
    }
});
</script>

<template>
    <Head title="Ателье" />
    <Navigation />
    <CustomCursor />

    <div class="bg-[#050505] text-white selection:bg-white selection:text-black overflow-x-hidden font-sans">
        
        <!-- ГЛАВНЫЙ ЭКРАН -->
        <div class="h-screen flex items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1540553016722-983e48a2cd10?q=80&w=2544&auto=format&fit=crop')] bg-cover bg-center opacity-30"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-transparent to-black/50"></div>
            
            <div class="text-center relative z-10 px-4">
                <p class="text-xs tracking-[0.3em] font-mono text-gray-400 mb-4 uppercase">Individual Tailoring</p>
                <h1 class="text-7xl md:text-9xl font-black tracking-tighter mix-blend-difference mb-8">
                    АТЕЛЬЕ
                </h1>
                <p class="text-xl text-gray-300 max-w-2xl mx-auto font-light">
                    Искусство создания текстиля, который меняет пространство. <br>От первого эскиза до финальной складки.
                </p>
            </div>
        </div>

        <!-- 1. ИСТОРИЯ ПРОЦЕССА -->
        <section id="process-section" class="h-screen overflow-hidden flex flex-col justify-center border-t border-white/5 bg-[#0a0a0a] relative">
            <div class="absolute top-10 left-10 z-10">
                <h2 class="text-4xl font-bold">Процесс создания</h2>
            </div>
            
            <div ref="processContainer" class="flex h-[70vh] items-center pl-20 w-max">
                <div 
                    v-for="(step, index) in processSteps" 
                    :key="step.id"
                    class="w-[80vw] md:w-[60vw] h-full mx-10 flex flex-col justify-end p-10 border border-white/10 rounded-3xl bg-white/5 relative group hover:bg-white/10 transition-colors backdrop-blur-sm"
                >
                    <div class="absolute top-10 right-10 text-9xl opacity-10 font-black">{{ index + 1 }}</div>
                    
                    <!-- Content -->
                    <div class="max-w-xl relative z-10">
                        <div class="text-6xl mb-6">{{ step.icon }}</div>
                        <h3 class="text-5xl font-bold mb-4">{{ step.title }}</h3>
                        <p class="text-xl text-gray-400">{{ step.description }}</p>
                    </div>

                    <!-- Визуальная заглушка (Линейный рисунок) -->
                    <div class="absolute inset-0 opacity-20 pointer-events-none">
                        <!-- Добавить SVG-паттерны позже -->
                    </div>
                </div>
                <!-- Финальный отступ -->
                <div class="w-[20vw]"></div>
            </div>
        </section>

        <!-- 2. ПОРТФОЛИО ДО/ПОСЛЕ -->
        <section class="py-32 px-4 md:px-20 bg-black relative">
            <h2 class="text-4xl md:text-5xl font-bold mb-20 text-center">Преображение</h2>
            
            <div class="max-w-6xl mx-auto h-[600px] relative rounded-3xl overflow-hidden cursor-ew-resize select-none group border border-white/10">
                <!-- Изображение ПОСЛЕ (Базовое) -->
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1513694203232-719a280e022f?q=80&w=2669&auto=format&fit=crop');">
                    <div class="absolute top-10 right-10 bg-black/50 backdrop-blur px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wider">После</div>
                </div>

                <!-- Изображение ДО (Обрезанное) -->
                <div 
                    class="absolute inset-0 bg-cover bg-center border-r-2 border-white"
                    :style="{ width: sliderValue + '%', backgroundImage: 'url(\'https://images.unsplash.com/photo-1595428774223-ef52624120d2?q=80&w=2574&auto=format&fit=crop\')' }"
                >
                    <div class="absolute top-10 left-10 bg-black/50 backdrop-blur px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wider">До</div>
                </div>

                <!-- Ручка слайдера -->
                <input 
                    type="range" 
                    min="0" 
                    max="100" 
                    v-model="sliderValue" 
                    class="absolute inset-0 w-full h-full opacity-0 cursor-ew-resize z-20"
                >
                <div 
                    class="absolute top-1/2 -translate-y-1/2 w-10 h-10 bg-white rounded-full shadow-xl flex items-center justify-center pointer-events-none z-10 mix-blend-difference"
                    :style="{ left: `calc(${sliderValue}% - 20px)` }"
                >
                    <svg class="w-6 h-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" transform="rotate(90 12 12)"/></svg>
                </div>
            </div>
        </section>

        <!-- 3. КОНСТРУКТОР МУДБОРДА -->
        <section class="py-32 bg-[#0a0a0a] border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row gap-16">
                    <!-- Текст -->
                    <div class="md:w-1/3">
                        <h2 class="text-4xl font-bold mb-6">Создайте свое настроение</h2>
                        <p class="text-gray-400 mb-8">
                            Выберите стили, которые вас вдохновляют, и мы предложим идеальное решение.
                        </p>
                        
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/10">
                            <h3 class="text-sm uppercase tracking-widest text-gray-500 mb-4">Ваш выбор:</h3>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="style in selectedStyles" 
                                    :key="style"
                                    class="px-3 py-1 bg-white text-black rounded-full text-sm font-bold capitalize"
                                >
                                    {{ styles.find(s => s.id === style)?.name }}
                                </span>
                                <span v-if="selectedStyles.length === 0" class="text-gray-600 italic text-sm">Ничего не выбрано</span>
                            </div>
                            <button class="w-full mt-8 py-4 bg-white text-black font-bold rounded-xl hover:bg-gray-200 transition-colors">
                                Отправить заявку
                            </button>
                        </div>
                    </div>

                    <!-- Интерактивная сетка -->
                    <div class="md:w-2/3 grid grid-cols-2 gap-4">
                        <div 
                            v-for="style in styles" 
                            :key="style.id"
                            @click="toggleStyle(style.id)"
                            class="relative aspect-[4/3] rounded-2xl overflow-hidden cursor-pointer group border-2 transition-all duration-300"
                            :class="selectedStyles.includes(style.id) ? 'border-white scale-[0.98]' : 'border-transparent opacity-60 hover:opacity-100'"
                        >
                            <div class="absolute inset-0 bg-gray-800" :style="{ backgroundColor: style.color }"></div>
                             <!-- Заглушки паттернов -->
                             <div class="absolute inset-0 opacity-20 bg-[url('/images/noise.png')] mix-blend-overlay"></div>
                             
                             <div class="absolute bottom-6 left-6 z-10">
                                <span class="text-2xl font-bold mix-blend-difference">{{ style.name }}</span>
                             </div>

                             <div v-if="selectedStyles.includes(style.id)" class="absolute top-4 right-4 w-8 h-8 bg-white rounded-full flex items-center justify-center text-black">
                                ✓
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ФУТЕР -->
        <div class="bg-black py-20 text-center text-white/30 text-sm border-t border-white/5">
             <p>ROSKARNIZ ATELIER © 2026.</p>
        </div>

    </div>
</template>
