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

// Placeholder for sequence frame count
const frameCount = 1; 
const currentFrame = { index: 0 };

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
    if (canvasRef.value && context) {
        canvasRef.value.width = window.innerWidth;
        canvasRef.value.height = window.innerHeight;

        const img = new Image();
        img.src = "/images/hero-bg.png"; 
        img.onload = () => {
            context.drawImage(img, 0, 0, canvasRef.value!.width, canvasRef.value!.height);
        };

        // Логика секвенции (когда будут кадры)
        // Сейчас просто зум и исчезновение
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: sequenceContainer.value, // Привязка к контейнеру 300vh
                start: "top top",
                end: "bottom bottom",
                scrub: 0.5,
                pin: true, // Закрепляем канвас пока скроллим
            }
        });

        tl.to(canvasRef.value, { scale: 1.1, duration: 1 }) // Зум
          .to(canvasRef.value, { opacity: 0, duration: 0.5 }, ">-0.5"); // Исчезновение в конце
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
        <div ref="sequenceContainer" class="relative h-[200vh] w-full"> 
            <canvas ref="canvasRef" class="absolute inset-0 h-full w-full object-cover z-0"></canvas>
            
            <!-- Контент, который висит поверх штор -->
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

            <div class="absolute bottom-10 w-full text-center z-20 animate-pulse">
                <p class="text-xs uppercase tracking-widest text-white/50">Скролльте, чтобы открыть</p>
            </div>
        </div>

        <!-- СЕКЦИЯ 2: Горизонтальный скролл (Авангардный стиль) -->
        <div id="horizontal-wrapper" class="relative h-screen overflow-hidden bg-white text-black">
            <div ref="horizontalContainer" class="flex h-full w-[300vw]">
                
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

                <!-- Slide 3: Call to Action -->
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
