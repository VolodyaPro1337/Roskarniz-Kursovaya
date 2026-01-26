<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, computed, watch } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

import Navigation from '@/components/Navigation.vue';
import CustomCursor from '@/components/CustomCursor.vue';

gsap.registerPlugin(ScrollTrigger);

const heroText = ref<HTMLElement | null>(null);
const glassCard = ref<HTMLElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null); 
// Логика последовательности удалена (Заглушка)
const horizontalContainer = ref<HTMLElement | null>(null);

// --- Реактивный стейт конфигуратора ---
const windowWidth = ref(3.0); // Метры
const curtainHeight = ref(2.8); // Метры
const openPercentage = ref(0); // 0 = Закрыто, 100 = Открыто
const selectedColor = ref('#1a1a1a');
const motorType = ref('somfy'); // 'somfy', 'xiaomi', 'manual'
const isAnimating = ref(false);

// Константы цен
const PRICES = {
    fabric: { black: 1500, red: 2500, beige: 1200, blue: 1800 },
    motor: { somfy: 15000, xiaomi: 8000, manual: 0 },
    work: 5000
};

const colors = [
    { value: 'black', hex: '#1a1a1a', name: 'Blackout Night' },
    { value: 'red', hex: '#7f1d1d', name: 'Royal Velvet' }, 
    { value: 'beige', hex: '#d6d3d1', name: 'Soft Linen' },
    { value: 'blue', hex: '#1e3a8a', name: 'Deep Ocean' },
];

const motors = [
    { id: 'somfy', name: 'Somfy (Франция)', speed: 0.15 }, // Скорость м/с
    { id: 'xiaomi', name: 'Xiaomi (Smart)', speed: 0.12 },
    { id: 'manual', name: 'Ручное управление', speed: 0.6 } 
];

// --- Расчет цены (Computed) ---
const calculatePrice = computed(() => {
    const area = windowWidth.value * curtainHeight.value;
    const fabricCost = area * (PRICES.fabric[selectedColor.value as keyof typeof PRICES.fabric] || 1500);
    const motorCost = PRICES.motor[motorType.value as keyof typeof PRICES.motor];
    return (fabricCost * 2 + motorCost + PRICES.work).toLocaleString('ru-RU'); // x2 коэф. складки
});

// --- Логика реалистичной анимации ---
const visualOpenness = ref(0); // То, что видим на экране

watch(openPercentage, (newVal) => {
    if (motorType.value === 'manual') {
        visualOpenness.value = newVal; // "Drag" эффект для ручного
        return;
    }

    const currentMeters = (visualOpenness.value / 100) * (windowWidth.value / 2);
    const targetMeters = (newVal / 100) * (windowWidth.value / 2);
    const distance = Math.abs(targetMeters - currentMeters);
    
    // Считаем время на основе скорости мотора
    const motor = motors.find(m => m.id === motorType.value);
    const speed = motor ? motor.speed : 0.1; 
    const duration = distance / speed; // секунды

    gsap.to(visualOpenness, {
        value: newVal,
        duration: duration,
        // Более плавная и "тяжелая" физика для премиум штор
        ease: "power2.inOut", 
        onStart: () => { isAnimating.value = true; },
        onComplete: () => { isAnimating.value = false; }
    });
});

onMounted(() => {
    // 1. Параллакс (Мышь)
    window.addEventListener('mousemove', (e) => {
        if (!glassCard.value) return;
        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;
        gsap.to(glassCard.value, { x: x, y: y, duration: 0.5, ease: "power2.out" });
    });

    // 2. Анимация заголовка
    gsap.from(heroText.value, { y: 100, opacity: 0, duration: 2, ease: "expo.out", delay: 0.5 });
    
    // 3. Горизонтальный скролл (Магнит)
    if (horizontalContainer.value) {
        const sections = 4;
        gsap.to(horizontalContainer.value, {
            x: () => -(horizontalContainer.value!.scrollWidth - window.innerWidth),
            ease: "none",
            scrollTrigger: {
                trigger: "#horizontal-wrapper",
                start: "top top",
                end: () => "+=" + (window.innerWidth * (sections - 1)),
                pin: true,
                scrub: 1,
                snap: {
                    snapTo: 1 / (sections - 1),
                    duration: { min: 0.2, max: 0.6 },
                    delay: 0.1,
                    ease: "power2.inOut" // Более плавный магнит
                }
            }
        });
    }
});
</script>

<script lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
export default {
    layout: MainLayout
}
</script>

<template>
    <Head title="Шторы" />

    <CustomCursor />


    <div class="bg-[#0f0f11] text-white selection:bg-red-500 selection:text-white overflow-x-hidden cursor-none">
        
        <!-- СЕКЦИЯ 1: Главный экран -->
        <div class="relative h-screen w-full overflow-hidden"> 
            <img src="/images/hero-bg.png" alt="Background" class="absolute inset-0 h-full w-full object-cover z-0 opacity-50" />
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/90 z-0"></div>
            <div class="absolute inset-0 z-10 flex flex-col items-center justify-center pointer-events-none">
                 <h1 ref="heroText" class="text-center mix-blend-difference">
                    <span class="block text-[15vw] font-black leading-none tracking-tighter text-white opacity-80">
                        ШТОРЫ
                    </span>
                    <span class="block text-xl font-light tracking-[1em] text-white opacity-60 uppercase mt-[-2vw]">
                        искусство тьмы
                    </span>
                </h1>
            </div>
            <div class="absolute bottom-10 w-full text-center z-20 animate-bounce">
                <p class="text-xs uppercase tracking-widest text-white/50">Скролльте вниз</p>
                <svg class="w-6 h-6 mx-auto text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </div>
        </div>

        <!-- СЕКЦИЯ 2: Горизонтальный скролл -->
        <div id="horizontal-wrapper" class="relative h-screen overflow-hidden bg-white text-black">
            <div ref="horizontalContainer" class="flex h-full w-[400vw]">
                
                <!-- Слайд 1: Вступление -->
                <div class="flex h-full w-screen items-center justify-center bg-black text-white relative border-r border-white/10">
                    <div class="absolute top-20 left-20 text-9xl font-bold opacity-10">01</div>
                    <div class="max-w-4xl px-10 relative z-10">
                        <h2 class="text-6xl font-bold mb-8 leading-tight">
                            Не просто текстиль. <br>
                            <span class="text-red-600">Архитектура</span> окна.
                        </h2>
                        <p class="text-xl text-gray-400 max-w-xl">
                            Мы отказались от шаблонов. Ваши окна — это портал во внешний мир, 
                            и мы создаем для них идеальную оправу.
                        </p>
                    </div>
                </div>

                <!-- Слайд 2: Галерея -->
                <div class="relative flex h-full w-screen items-center bg-[#1a1a1a] text-white overflow-hidden">
                     <div class="absolute top-10 right-10 text-9xl font-bold opacity-10">02</div>
                     <div class="absolute top-[20%] left-[10%] w-[400px] p-8 rounded-3xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <h3 class="text-3xl font-bold mb-2">Blackout</h3>
                        <p class="text-gray-400 text-sm">Полная изоляция от света для идеального сна.</p>
                     </div>
                     <div class="absolute bottom-[20%] right-[15%] w-[350px] p-8 rounded-3xl bg-red-600/10 border border-red-500/20 backdrop-blur-md">
                        <h3 class="text-3xl font-bold mb-2 text-red-500">Smart</h3>
                        <p class="text-gray-300 text-sm">Управляйте шторами голосом.</p>
                     </div>
                     <div class="mx-auto text-center transform rotate-[-5deg]">
                        <span class="text-[10rem] font-black opacity-20 stroke-text">БЛЭК<br>АУТ</span>
                     </div>
                </div>

                <!-- Слайд 3: Продвинутый интерактивный симулятор -->
                <div class="flex h-full w-screen items-center justify-center bg-gray-900 text-white relative">
                    <div class="absolute top-10 left-10 text-9xl font-bold opacity-10">03</div>
                    
                    <div class="flex w-full max-w-7xl gap-16 items-start h-[70vh]">
                        
                        <!-- Панель управления -->
                        <div class="w-1/3 h-full flex flex-col justify-between bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-sm overflow-y-auto custom-scrollbar">
                            <div>
                                <h3 class="text-3xl font-bold mb-2">Конфигуратор</h3>
                                <p class="text-sm text-gray-400 mb-8">Создайте идеальный сценарий</p>
                                
                                <!-- 1. Размеры -->
                                <div class="mb-8 p-4 bg-black/20 rounded-2xl border border-white/5">
                                    <label class="block text-xs uppercase tracking-widest text-gray-500 mb-4">Размеры окна (м)</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-xs text-gray-400 block mb-1">Ширина</span>
                                            <input type="number" step="0.1" v-model="windowWidth" class="w-full bg-black/50 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-red-500 outline-none">
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-400 block mb-1">Высота</span>
                                            <input type="number" step="0.1" v-model="curtainHeight" class="w-full bg-black/50 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-red-500 outline-none">
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Управление -->
                                <div class="mb-8">
                                    <div class="flex justify-between mb-2">
                                        <label class="text-sm text-white font-medium">Положение штор</label>
                                        <span class="text-xs font-mono" :class="isAnimating ? 'text-red-400 animate-pulse' : 'text-green-400'">
                                            {{ isAnimating ? '• MOTORS ACTIVE' : '• STANDBY' }}
                                        </span>
                                    </div>
                                    
                                    <div class="relative h-12 bg-black/40 rounded-xl border border-white/10 flex items-center px-4 mb-2">
                                        <input 
                                            type="range" 
                                            v-model.number="openPercentage" 
                                            min="0" 
                                            max="100" 
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                        >
                                        <div class="flex-1 h-1 bg-gray-700 rounded-full overflow-hidden relative">
                                            <div class="absolute h-full bg-red-600 transition-all duration-300" :style="{ width: openPercentage + '%' }"></div>
                                        </div>
                                        <span class="ml-4 font-mono text-sm w-12 text-right">{{ openPercentage }}%</span>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-gray-500 uppercase font-mono">
                                        <span>Закрыто</span>
                                        <span>Открыто</span>
                                    </div>
                                </div>

                                <!-- 3. Опции -->
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-3">Ткань</label>
                                        <div class="grid grid-cols-4 gap-2">
                                            <button 
                                                v-for="color in colors" 
                                                :key="color.value"
                                                @click="selectedColor = color.value"
                                                class="aspect-square rounded-xl border-2 transition-all duration-300 relative overflow-hidden group shadow-lg"
                                                :class="selectedColor === color.value ? 'border-white scale-105 shadow-white/20' : 'border-transparent opacity-60 hover:opacity-100'"
                                            >
                                                <div class="absolute inset-0" :style="{ backgroundColor: color.hex }"></div>
                                                <!-- Текстура на кнопке -->
                                                <div class="absolute inset-0 bg-[url('/images/noise.png')] opacity-20 mix-blend-overlay"></div>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-3">Привод</label>
                                        <div class="grid grid-cols-1 gap-2">
                                             <button 
                                                v-for="motor in motors"
                                                :key="motor.id"
                                                @click="motorType = motor.id"
                                                class="text-left px-4 py-3 rounded-xl border transition-all flex justify-between items-center"
                                                :class="motorType === motor.id ? 'bg-white/10 border-white text-white' : 'border-white/5 text-gray-500 hover:bg-white/5'"
                                             >
                                                <span class="text-sm font-medium">{{ motor.name }}</span>
                                                <span v-if="motorType === motor.id" class="text-xs text-red-500 font-bold">●</span>
                                             </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Футер -->
                            <div class="mt-8 pt-6 border-t border-white/10">
                                <div class="flex justify-between items-end">
                                    <div>
                                        <span class="text-xs text-gray-500 block">Итоговая стоимость</span>
                                        <span class="text-3xl font-bold text-white tracking-tight">{{ calculatePrice }} ₽</span>
                                    </div>
                                    <button class="px-6 py-3 bg-red-600 rounded-xl font-bold text-sm hover:bg-red-500 transition-colors shadow-lg shadow-red-900/40 transform hover:-translate-y-1">
                                        В корзину
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Визуализация -->
                        <div class="flex-1 h-full relative group">
                            <!-- Контейнер комнаты -->
                            <div class="h-full w-full bg-gray-800 rounded-3xl overflow-hidden shadow-2xl border-4 border-gray-700 relative">
                                 <!-- Фон: Город -->
                                 <div class="absolute inset-0 bg-cover bg-center" 
                                      style="background-image: url('/images/welcome-room.png'); filter: brightness(0.6);">
                                 </div>
                                 
                                 <!-- Рама (Стена) -->
                                 <div class="absolute inset-0 border-[40px] border-[#1a1a1a] pointer-events-none z-20 shadow-inner"></div>

                                 <!-- ВИЗУАЛЬНЫЕ РАЗМЕРЫ (Линейки) -->
                                 <div class="absolute top-[10px] left-[50px] right-[50px] h-[20px] z-30 flex items-center justify-center border-b border-white/20">
                                     <span class="bg-[#1a1a1a] px-2 text-[10px] text-gray-400 font-mono">{{ windowWidth }} м</span>
                                 </div>
                                 <div class="absolute top-[50px] left-[10px] bottom-[50px] w-[20px] z-30 flex items-center justify-center border-r border-white/20 writing-mode-vertical">
                                     <span class="bg-[#1a1a1a] py-2 text-[10px] text-gray-400 font-mono rotate-180 writing-mode-vertical">{{ curtainHeight }} м</span>
                                 </div>

                                 <!-- Контейнер штор -->
                                 <div class="absolute inset-0 flex justify-center items-start pt-[40px] z-10 overflow-hidden px-[40px]">
                                     
                                     <!-- Левая штора -->
                                     <div 
                                        class="h-full bg-cover relative shadow-[10px_0_40px_rgba(0,0,0,0.6)] transition-none will-change-transform origin-left"
                                        :style="{ 
                                            backgroundColor: colors.find(c => c.value === selectedColor)?.hex || selectedColor, 
                                            width: (50 - visualOpenness / 2) + '%',
                                            marginRight: 'auto'
                                        }"
                                     >
                                        <!-- Улучшенная текстура складок -->
                                        <div class="absolute inset-0 bg-[repeating-linear-gradient(90deg,transparent,transparent_4%,rgba(0,0,0,0.2)_5%,rgba(255,255,255,0.05)_8%)] opacity-50 mix-blend-multiply"></div>
                                        <div class="absolute inset-0 bg-[repeating-linear-gradient(90deg,rgba(0,0,0,0.1),transparent_10%)] opacity-30"></div>
                                        
                                        <!-- Нижний край (подшив) -->
                                        <div class="absolute bottom-0 w-full h-[100px] bg-gradient-to-t from-black/60 to-transparent"></div>
                                     </div>

                                     <!-- Правая штора -->
                                     <div 
                                        class="h-full bg-cover relative shadow-[-10px_0_40px_rgba(0,0,0,0.6)] transition-none will-change-transform origin-right"
                                        :style="{ 
                                            backgroundColor: colors.find(c => c.value === selectedColor)?.hex || selectedColor, 
                                            width: (50 - visualOpenness / 2) + '%',
                                            marginLeft: 'auto'
                                        }"
                                     >
                                         <div class="absolute inset-0 bg-[repeating-linear-gradient(90deg,transparent,transparent_4%,rgba(0,0,0,0.2)_5%,rgba(255,255,255,0.05)_8%)] opacity-50 mix-blend-multiply"></div>
                                         <div class="absolute inset-0 bg-[repeating-linear-gradient(90deg,rgba(0,0,0,0.1),transparent_10%)] opacity-30"></div>
                                         <div class="absolute bottom-0 w-full h-[100px] bg-gradient-to-t from-black/60 to-transparent"></div>
                                     </div>

                                 </div>

                                 <!-- Индикатор мотора -->
                                 <div v-if="isAnimating && motorType !== 'manual'" class="absolute top-8 right-8 px-3 py-1 bg-black/80 rounded-full flex items-center gap-2 z-30 border border-white/10 backdrop-blur">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-ping"></div>
                                    <span class="text-[10px] font-mono uppercase text-gray-300 tracking-wider">Привод активен</span>
                                 </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Слайд 4: Призыв к действию (Переработанный) -->
                <div class="flex h-full w-screen items-center justify-center bg-[#0a0a0a] text-white relative border-l border-white/5">
                    <!-- Фоновые визуалы -->
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="absolute -top-[20%] -left-[10%] w-[60vw] h-[60vw] bg-red-900/10 rounded-full blur-[120px] animate-pulse"></div>
                        <div class="absolute bottom-0 right-0 w-[40vw] h-[40vw] bg-blue-900/5 rounded-full blur-[100px]"></div>
                    </div>

                    <div class="text-center relative z-10">
                        <h2 class="text-[6vw] font-black leading-none mb-4 mix-blend-difference cursor-hover" data-movement="20">
                            ВАШ ДОМ <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-400 to-gray-600">ЗАСЛУЖИВАЕТ</span>
                        </h2>
                        
                        <p class="text-gray-400 max-w-xl mx-auto mb-12 text-lg">
                            Индивидуальный пошив, моторизация и монтаж под ключ. <br>
                            Оставьте заявку, и мы привнесем магию в ваш интерьер.
                        </p>

                         <button class="group relative px-12 py-6 bg-transparent rounded-full overflow-hidden border border-white/20 hover:border-white/50 transition-colors cursor-hover">
                            <div class="absolute inset-0 bg-white translate-y-[100%] group-hover:translate-y-0 transition-transform duration-500 ease-in-out z-0"></div>
                            <span class="relative z-10 text-xl font-bold group-hover:text-black transition-colors duration-300">ОФОРМИТЬ ЗАЯВКУ</span>
                        </button>

                        <div class="mt-12 flex gap-8 justify-center opacity-30 text-xs tracking-[0.2em]">
                            <span>PREMIUM TEXTILES</span> <span>•</span> <span>SMART SYSTEMS</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Наложение шума -->
        <div class="fixed inset-0 pointer-events-none z-[9999] opacity-[0.03] mix-blend-overlay" 
             style="background-image: url('/images/noise.png');">
        </div>

    </div>
</template>

<style scoped>
.stroke-text {
    -webkit-text-stroke: 2px rgba(255, 255, 255, 0.5);
    color: transparent;
}
</style>
