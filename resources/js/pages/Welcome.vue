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

// Reactive State for Curtain Simulator
const curtainOpen = ref(20);
const selectedColor = ref('#1a1a1a'); // Default Black
import { computed } from 'vue';

const colors = [
    { value: 'black', hex: '#1a1a1a' },
    { value: 'red', hex: '#dc2626' },
    { value: 'beige', hex: '#e5e5e5' },
    { value: 'blue', hex: '#1e3a8a' },
];

const calculatePrice = computed(() => {
    // Basic reactive math: Base price + (Material cost relative to color)
    let base = 15000;
    if (selectedColor.value === '#dc2626') base += 5000; // Red is premium
    if (selectedColor.value === '#1e3a8a') base += 2000;
    return (base * (1 + curtainOpen.value / 1000)).toFixed(0); 
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

    // 4. #Gsap Горизонтальный скролл (Нестандартный блок)
    if (horizontalContainer.value) {
        gsap.to(horizontalContainer.value, {
            x: () => -(horizontalContainer.value!.scrollWidth - window.innerWidth),
            ease: "none",
            scrollTrigger: {
                trigger: "#horizontal-wrapper",
                start: "top top",
                end: "+=2000", // Длина скролла
                pin: true, // Закрепляем экран
                scrub: 1,
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

                <!-- Slide 3: Interactive Playground (Functional Reactivity) -->
                <div class="flex h-full w-screen items-center justify-center bg-gray-900 text-white relative">
                    <div class="absolute top-10 left-10 text-9xl font-bold opacity-10">03</div>
                    
                    <div class="flex w-full max-w-6xl gap-12 items-center">
                        
                        <!-- Controls -->
                        <div class="w-1/3 bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-sm">
                            <h3 class="text-3xl font-bold mb-8">Умный дом</h3>
                            
                            <div class="mb-8">
                                <label class="block text-sm text-gray-400 mb-2">Открытие штор: {{ curtainOpen }}%</label>
                                <input 
                                    type="range" 
                                    v-model="curtainOpen" 
                                    min="0" 
                                    max="100" 
                                    class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-red-600"
                                >
                            </div>

                            <div class="mb-8">
                                <label class="block text-sm text-gray-400 mb-4">Цвет ткани</label>
                                <div class="flex gap-4">
                                    <button 
                                        v-for="color in colors" 
                                        :key="color.value"
                                        @click="selectedColor = color.value"
                                        class="w-10 h-10 rounded-full border-2 transition-all duration-300"
                                        :class="selectedColor === color.value ? 'border-white scale-110' : 'border-transparent opacity-50 hover:opacity-100'"
                                        :style="{ backgroundColor: color.hex }"
                                    ></button>
                                </div>
                            </div>

                            <div class="p-4 bg-black/30 rounded-xl">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium">Стоимость</span>
                                    <span class="text-xl font-bold text-red-500">{{ calculatePrice }} ₽</span>
                                </div>
                                <p class="text-xs text-gray-500">Примерный расчет для вашего окна</p>
                            </div>
                        </div>

                        <!-- Visualization -->
                        <div class="w-2/3 h-[600px] relative bg-gray-800 rounded-3xl overflow-hidden shadow-2xl border-4 border-gray-700">
                             <!-- Window View (Night City) -->
                             <div class="absolute inset-0 bg-cover bg-center transition-all duration-1000" 
                                  style="background-image: url('https://images.unsplash.com/photo-1519608487953-e999c9dc296f?q=80&w=2574&auto=format&fit=crop');">
                             </div>

                             <!-- Curtains (React to Width) -->
                             <div class="absolute inset-y-0 left-0 bg-cover transition-all duration-500 shadow-2xl z-10"
                                  :style="{ width: (50 - curtainOpen / 2) + '%', backgroundColor: selectedColor }">
                                  <div class="w-full h-full bg-gradient-to-r from-black/50 to-transparent"></div> <!-- Fold shadow -->
                             </div>
                             <div class="absolute inset-y-0 right-0 bg-cover transition-all duration-500 shadow-2xl z-10"
                                  :style="{ width: (50 - curtainOpen / 2) + '%', backgroundColor: selectedColor }">
                                  <div class="w-full h-full bg-gradient-to-l from-black/50 to-transparent"></div> <!-- Fold shadow -->
                             </div>

                             <!-- Info Overlay -->
                             <div class="absolute bottom-6 left-6 px-4 py-2 bg-black/60 backdrop-blur-md rounded-lg z-20">
                                <span class="text-xs font-mono uppercase tracking-wider text-green-400">● System Online</span>
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
