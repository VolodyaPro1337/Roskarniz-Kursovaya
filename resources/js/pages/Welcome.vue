<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const heroText = ref<HTMLElement | null>(null);
const glassCard = ref<HTMLElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const sequenceContainer = ref<HTMLElement | null>(null);
const horizontalContainer = ref<HTMLElement | null>(null);

// --- Reactive State for Advanced Simulator ---
const windowWidth = ref(3.0); // Meters
const curtainHeight = ref(2.8); // Meters
const openPercentage = ref(0); // 0 = Closed, 100 = Fully Open
const selectedColor = ref('#1a1a1a');
const motorType = ref('somfy'); // 'somfy', 'xiaomi', 'manual'
const isAnimating = ref(false);

import { computed, watch } from 'vue';

// Pricing Constants
const PRICES = {
    fabric: { black: 1500, red: 2500, beige: 1200, blue: 1800 },
    motor: { somfy: 15000, xiaomi: 8000, manual: 0 },
    work: 5000
};

const colors = [
    { value: 'black', hex: '#1a1a1a', name: 'Blackout Night' },
    { value: 'red', hex: '#7f1d1d', name: 'Royal Velvet' }, // Darker red for realism
    { value: 'beige', hex: '#d6d3d1', name: 'Soft Linen' },
    { value: 'blue', hex: '#1e3a8a', name: 'Deep Ocean' },
];

const motors = [
    { id: 'somfy', name: 'Somfy (France)', speed: 0.15 }, // Speed in m/s
    { id: 'xiaomi', name: 'Xiaomi (Smart)', speed: 0.12 },
    { id: 'manual', name: 'Без мотора', speed: 0.5 } // Hand speed variable
];

// --- Computed Logic ---
const calculatePrice = computed(() => {
    const area = windowWidth.value * curtainHeight.value;
    const fabricCost = area * (PRICES.fabric[selectedColor.value as keyof typeof PRICES.fabric] || 1500);
    const motorCost = PRICES.motor[motorType.value as keyof typeof PRICES.motor];
    return (fabricCost * 2 + motorCost + PRICES.work).toLocaleString('ru-RU'); // *2 for fold factor
});

// --- Realistic Animation Logic ---
const visualOpenness = ref(0); // The value actually shown on screen (0-100)

// Watch target change and animate smoothly
watch(openPercentage, (newVal) => {
    if (motorType.value === 'manual') {
        visualOpenness.value = newVal; // Instant for manual preview (or drag)
        return;
    }

    const currentMeters = (visualOpenness.value / 100) * (windowWidth.value / 2);
    const targetMeters = (newVal / 100) * (windowWidth.value / 2);
    const distance = Math.abs(targetMeters - currentMeters);
    
    // Calculate realistic duration based on motor speed
    const motor = motors.find(m => m.id === motorType.value);
    const speed = motor ? motor.speed : 0.1; 
    const duration = distance / speed; // seconds

    gsap.to(visualOpenness, {
        value: newVal,
        duration: duration,
        ease: "power2.inOut", // Motor acceleration/deceleration curve
        onStart: () => isAnimating.value = true,
        onComplete: () => isAnimating.value = false
    });
});

onMounted(() => {
    // 1. #Gsap Параллакс эффект (Мышь)
    window.addEventListener('mousemove', (e) => {
        if (!glassCard.value) return;
        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;
        gsap.to(glassCard.value, { x: x, y: y, duration: 0.5, ease: "power2.out" });
    });

    // 2. #Gsap Анимация появления заголовка
    gsap.from(heroText.value, { y: 100, opacity: 0, duration: 2, ease: "expo.out", delay: 0.5 });

    // 3. #Gsap "Открытие штор" (Секвенция)
    const context = canvasRef.value?.getContext("2d");
    
    // Helper must be inside or context passed
    const updateImage = (index: number) => {
        if (!canvasRef.value || !context) return;
        
        // Format: frame-001.jpg
        const paddedIndex = String(index + 1).padStart(3, '0'); 
        const img = new Image();
        img.src = `/images/sequence/frame-${paddedIndex}.jpg`;
        
        img.onload = () => {
            if (canvasRef.value) {
                // Cover fit logic (mimic object-cover)
                const canvas = canvasRef.value;
                const ctx = context;
                const dpr = window.devicePixelRatio || 1;
                
                // Optional: handle resize if needed, for now draw simple
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            }
        };
    };

    if (canvasRef.value && context) {
        canvasRef.value.width = window.innerWidth;
        canvasRef.value.height = window.innerHeight;

        // Load first frame
        updateImage(0);

        gsap.to(currentFrame, {
            index: frameCount - 1,
            snap: "index",
            scrollTrigger: {
                trigger: sequenceContainer.value,
                start: "top top",
                end: "bottom bottom",
                scrub: 0.5,
                pin: true, 
            },
            onUpdate: () => {
                updateImage(currentFrame.index);
            }
        });
    }

    // 3. #Gsap Горизонтальный скролл с МАГНИТОМ
    if (horizontalContainer.value) {
        const sections = 4; // Total slides
        gsap.to(horizontalContainer.value, {
            x: () => -(horizontalContainer.value!.scrollWidth - window.innerWidth),
            ease: "none",
            scrollTrigger: {
                trigger: "#horizontal-wrapper",
                start: "top top",
                end: () => "+=" + (window.innerWidth * (sections - 1)), // Exact length
                pin: true,
                scrub: 1,
                snap: {
                    snapTo: 1 / (sections - 1), // Snap to each section
                    duration: { min: 0.2, max: 0.5 },
                    delay: 0.1,
                    ease: "power1.inOut"
                }
            }
        });
    }
});
</script>

<template>
    <Head title="Шторы" />

    <div class="bg-[#0f0f11] text-white selection:bg-red-500 selection:text-white overflow-x-hidden">
        
        <!-- СЕКЦИЯ 1: "Шторы" (Canvas Sequence) -->
        <!-- Высота 200vh дает место для разгона анимации -->
        <!-- СЕКЦИЯ 1: "Шторы" (Static Hero) -->
        <div class="relative h-screen w-full overflow-hidden"> 
            <!-- Background Image -->
            <img src="/images/hero-bg.png" alt="Background" class="absolute inset-0 h-full w-full object-cover z-0 opacity-50" />
            
            <!-- Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/90 z-0"></div>

            <!-- Контент -->
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

        <!-- СЕКЦИЯ 2: Горизонтальный скролл (Авангардный стиль) -->
        <div id="horizontal-wrapper" class="relative h-screen overflow-hidden bg-white text-black">
            <div ref="horizontalContainer" class="flex h-full w-[400vw]">
                
                <!-- Slide 1: Intro -->
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

                <!-- Slide 2: Asymmetrical Gallery -->
                <div class="relative flex h-full w-screen items-center bg-[#1a1a1a] text-white overflow-hidden">
                     <div class="absolute top-10 right-10 text-9xl font-bold opacity-10">02</div>
                     
                     <!-- Floating Cards (Glass) -->
                     <div class="absolute top-[20%] left-[10%] w-[400px] p-8 rounded-3xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <h3 class="text-3xl font-bold mb-2">Blackout</h3>
                        <p class="text-gray-400 text-sm">Полная изоляция от света для идеального сна.</p>
                     </div>

                     <div class="absolute bottom-[20%] right-[15%] w-[350px] p-8 rounded-3xl bg-red-600/10 border border-red-500/20 backdrop-blur-md">
                        <h3 class="text-3xl font-bold mb-2 text-red-500">Smart</h3>
                        <p class="text-gray-300 text-sm">Управляйте шторами голосом. Алиса, открой окно.</p>
                     </div>

                     <!-- Center Focus -->
                     <div class="mx-auto text-center transform rotate-[-5deg]">
                        <span class="text-[10rem] font-black opacity-20 stroke-text">БЛЭК<br>АУТ</span>
                     </div>
                </div>

                <!-- Slide 3: ADVANCED INTERACTIVE SIMULATOR -->
                <div class="flex h-full w-screen items-center justify-center bg-gray-900 text-white relative">
                    <div class="absolute top-10 left-10 text-9xl font-bold opacity-10">03</div>
                    
                    <div class="flex w-full max-w-7xl gap-16 items-start h-[70vh]">
                        
                        <!-- Controls Panel -->
                        <div class="w-1/3 h-full flex flex-col justify-between bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-sm overflow-y-auto custom-scrollbar">
                            <div>
                                <h3 class="text-3xl font-bold mb-2">Конфигуратор</h3>
                                <p class="text-sm text-gray-400 mb-8">Создайте идеальный сценарий</p>
                                
                                <!-- 1. Dimensions -->
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

                                <!-- 2. Controls (Realistic) -->
                                <div class="mb-8">
                                    <div class="flex justify-between mb-2">
                                        <label class="text-sm text-white font-medium">Положение штор</label>
                                        <span class="text-xs font-mono" :class="isAnimating ? 'text-red-400 animate-pulse' : 'text-green-400'">
                                            {{ isAnimating ? 'MOVING...' : 'IDLE' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Custom Range Slider -->
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

                                <!-- 3. Options -->
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-3">Ткань</label>
                                        <div class="grid grid-cols-4 gap-2">
                                            <button 
                                                v-for="color in colors" 
                                                :key="color.value"
                                                @click="selectedColor = color.value"
                                                class="aspect-square rounded-xl border-2 transition-all duration-300 relative overflow-hidden group"
                                                :class="selectedColor === color.value ? 'border-white' : 'border-transparent opacity-60 hover:opacity-100'"
                                            >
                                                <div class="absolute inset-0" :style="{ backgroundColor: color.hex }"></div>
                                                <div v-if="selectedColor === color.value" class="absolute inset-0 flex items-center justify-center bg-black/20">
                                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                                </div>
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
                                                <span v-if="motorType === motor.id" class="text-xs text-red-500">Selected</span>
                                             </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Price Footer -->
                            <div class="mt-8 pt-6 border-t border-white/10">
                                <div class="flex justify-between items-end">
                                    <div>
                                        <span class="text-xs text-gray-500 block">Итоговая стоимость</span>
                                        <span class="text-3xl font-bold text-white tracking-tight">{{ calculatePrice }} ₽</span>
                                    </div>
                                    <button class="px-6 py-3 bg-red-600 rounded-xl font-bold text-sm hover:bg-red-500 transition-colors shadow-lg shadow-red-900/40">
                                        В корзину
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Visualizer Area -->
                        <div class="flex-1 h-full relative">
                            <!-- Room Container -->
                            <div class="h-full w-full bg-gray-800 rounded-3xl overflow-hidden shadow-2xl border-4 border-gray-700 relative">
                                 <!-- Background: Night City -->
                                 <div class="absolute inset-0 bg-cover bg-center" 
                                      style="background-image: url('https://images.unsplash.com/photo-1519608487953-e999c9dc296f?q=80&w=2574&auto=format&fit=crop'); filter: brightness(0.7);">
                                 </div>
                                 
                                 <!-- Window Frame Override (Simulate Wall) -->
                                 <div class="absolute inset-0 border-[40px] border-[#1a1a1a] pointer-events-none z-20"></div>

                                 <!-- Curtains Container: Center Width Reference -->
                                 <div class="absolute inset-0 flex justify-center items-start pt-[40px] z-10 overflow-hidden px-[40px]">
                                     
                                     <!-- Left Curtain -->
                                     <div 
                                        class="h-full bg-cover relative shadow-[10px_0_30px_rgba(0,0,0,0.5)] transition-none will-change-transform origin-left"
                                        :style="{ 
                                            backgroundColor: colors.find(c => c.value === selectedColor)?.hex || selectedColor, // Safe lookup instead of just selectedColor
                                            width: (50 - visualOpenness / 2) + '%',
                                            marginRight: 'auto'
                                        }"
                                     >
                                        <!-- Realistic Texture/Folds -->
                                        <div class="absolute inset-0 opacity-40 bg-[repeating-linear-gradient(90deg,transparent,transparent_20px,rgba(0,0,0,0.3)_25px,rgba(255,255,255,0.05)_40px)]"></div>
                                        <!-- Bottom Hem -->
                                        <div class="absolute bottom-0 w-full h-24 bg-gradient-to-t from-black/40 to-transparent"></div>
                                     </div>

                                     <!-- Right Curtain -->
                                     <div 
                                        class="h-full bg-cover relative shadow-[-10px_0_30px_rgba(0,0,0,0.5)] transition-none will-change-transform origin-right"
                                        :style="{ 
                                            backgroundColor: colors.find(c => c.value === selectedColor)?.hex || selectedColor, 
                                            width: (50 - visualOpenness / 2) + '%',
                                            marginLeft: 'auto'
                                        }"
                                     >
                                         <div class="absolute inset-0 opacity-40 bg-[repeating-linear-gradient(90deg,transparent,transparent_20px,rgba(0,0,0,0.3)_25px,rgba(255,255,255,0.05)_40px)]"></div>
                                         <div class="absolute bottom-0 w-full h-24 bg-gradient-to-t from-black/40 to-transparent"></div>
                                     </div>

                                 </div>

                                 <!-- Motor Sound Indicator -->
                                 <div v-if="isAnimating && motorType !== 'manual'" class="absolute top-8 right-8 px-3 py-1 bg-black/80 rounded-full flex items-center gap-2 z-30">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-ping"></div>
                                    <span class="text-[10px] font-mono uppercase text-gray-300">Motor Active</span>
                                 </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Slide 4: Call to Action -->
                <div class="flex h-full w-screen items-center justify-center bg-red-600 text-white relative">
                    <div class="text-center">
                        <h2 class="text-[8vw] font-black leading-none mb-10">ХОЧУ <br> ТАК ЖЕ</h2>
                         <button class="px-12 py-6 bg-black text-white rounded-full text-2xl font-bold hover:scale-110 transition-transform">
                            Оформить заявку
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer (Simple) -->
        <div class="bg-black py-20 text-center text-white/30 text-sm">
            <p>ROSKARNIZ IS © 2026. DESIGNED BY ANTIGRAVITY.</p>
        </div>

    </div>
</template>

<style scoped>
.stroke-text {
    -webkit-text-stroke: 2px rgba(255, 255, 255, 0.5);
    color: transparent;
}
</style>
