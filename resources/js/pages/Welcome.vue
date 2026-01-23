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

// Placeholder for sequence frame count
const frameCount = 1; 
const currentFrame = { index: 0 };

onMounted(() => {
    // #Gsap Параллакс эффект
    window.addEventListener('mousemove', (e) => {
        if (!glassCard.value) return;
        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;
        gsap.to(glassCard.value, { x: x, y: y, duration: 0.5, ease: "power2.out" });
    });

    // #Gsap Анимация появления
    gsap.from(heroText.value, { y: 50, opacity: 0, duration: 1.5, ease: "expo.out", delay: 0.2 });

    // #Gsap Скролл-анимация (Sequence)
    // Пока используем одну картинку, но логика готова для секвенции
    const context = canvasRef.value?.getContext("2d");
    if (canvasRef.value && context) {
        canvasRef.value.width = window.innerWidth;
        canvasRef.value.height = window.innerHeight;

        const img = new Image();
        img.src = "/images/hero-bg.png"; // Placeholder
        img.onload = () => {
            context.drawImage(img, 0, 0, canvasRef.value!.width, canvasRef.value!.height);
        };

        gsap.to(currentFrame, {
            index: frameCount - 1,
            snap: "index",
            scrollTrigger: {
                trigger: sequenceContainer.value,
                start: "top top",
                end: "bottom bottom",
                scrub: 0.5,
                // markers: true, // для отладки
            },
            onUpdate: () => {
                // Здесь будет логика для рисования frame_XXX.jpg
                // render(currentFrame.index); 
                // Пока просто зумим картинку для эмуляции "открытия"
                if (canvasRef.value) {
                     // Эмуляция: просто затемняем по мере скролла
                }
            }
        });
        
        // Псевдо-эффект открытия для демо
        gsap.to(canvasRef.value, {
            scale: 1.2,
            opacity: 0.3,
            scrollTrigger: {
                trigger: sequenceContainer.value,
                start: "top top",
                end: "bottom bottom",
                scrub: true
            }
        });
    }
});
</script>

<template>
    <Head title="Шторы" />

    <div class="relative min-h-screen bg-[#0f0f11] text-white selection:bg-red-500 selection:text-white">
        
        <!-- #Sequence Container: Закрепленный фон для анимации -->
        <div ref="sequenceContainer" class="relative h-[300vh]"> <!-- Высота для скролла -->
            <div class="sticky top-0 h-screen w-full overflow-hidden">
                <canvas ref="canvasRef" class="absolute inset-0 h-full w-full object-cover"></canvas>
                
                <!-- Overlay Gradient -->
                <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/90"></div>

                <!-- Hero Section Content (Появляется сразу) -->
                <div class="relative z-10 flex h-full flex-col items-center justify-center p-6">
                    <!-- Стеклянная карточка -->
                    <div 
                        ref="glassCard"
                        class="glass-panel relative flex aspect-[1.58/1] w-full max-w-4xl flex-col items-center justify-center overflow-hidden rounded-3xl border border-white/10 bg-white/5 shadow-2xl backdrop-blur-md"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-50"></div>
                        <h1 ref="heroText" class="relative z-20 text-center font-bold tracking-tighter">
                            <span class="block text-[120px] leading-none text-transparent bg-clip-text bg-gradient-to-br from-white to-white/50">
                                ШТОРЫ
                            </span>
                            <span class="block text-xl font-light tracking-[0.5em] text-white/60 mt-4 uppercase">
                                Информационная Система
                            </span>
                        </h1>
                        <!-- Buttons -->
                        <div class="mt-12 flex gap-6 z-20 opacity-0 animate-fade-in-up" style="animation-delay: 1s; animation-fill-mode: forwards;">
                             <button class="group relative px-8 py-3 rounded-full bg-white/10 border border-white/10 hover:bg-white/20 transition-all duration-300">
                                <span class="text-sm font-medium tracking-wider">Каталог</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="absolute bottom-10 animate-bounce text-white/50">
                        <p class="text-xs uppercase tracking-widest mb-2">Листайте вниз</p>
                        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- #Content Sections (Появляются ПОСЛЕ скролла) -->
        <div class="relative z-20 -mt-[50vh] bg-gradient-to-b from-transparent to-[#0f0f11] pt-32 pb-20">
             
             <!-- About Section -->
             <div class="container mx-auto px-6 mb-32">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-5xl font-bold mb-6 bg-clip-text text-transparent bg-gradient-to-r from-red-500 to-purple-600">
                            Атмосфера <br> уюта и стиля
                        </h2>
                        <p class="text-xl text-gray-400 leading-relaxed mb-8">
                            Мы создаем не просто шторы, а настроение вашего дома. 
                            Автоматизированные системы, премиальные ткани и индивидуальный подход 
                            к каждому окну.
                        </p>
                         <div class="flex gap-4">
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
                                <span class="block text-3xl font-bold text-white mb-1">500+</span>
                                <span class="text-sm text-gray-400">Видов тканей</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
                                <span class="block text-3xl font-bold text-white mb-1">10 лет</span>
                                <span class="text-sm text-gray-400">Гарантии</span>
                            </div>
                        </div>
                    </div>
                    <!-- Placeholder for Image -->
                    <div class="relative h-[500px] w-full rounded-3xl overflow-hidden border border-white/10 shadow-2xl group">
                         <div class="absolute inset-0 bg-gray-800 animate-pulse"></div> <!-- Placeholder -->
                         <div class="absolute inset-0 flex items-center justify-center text-gray-500 font-mono text-sm">
                            [Место для фото интерьера]
                         </div>
                    </div>
                </div>
             </div>

             <!-- Features Grid -->
             <div class="container mx-auto px-6">
                <h3 class="text-3xl font-light text-center mb-16 uppercase tracking-widest">Наши Преимущества</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="group p-8 rounded-3xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-500 hover:-translate-y-2">
                        <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center mb-6 text-red-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold mb-3">Электрокарнизы</h4>
                        <p class="text-gray-400 text-sm">Управление шторами с пульта или смартфона. Интеграция с Алисой и HomeKit.</p>
                    </div>
                     <!-- Feature 2 -->
                    <div class="group p-8 rounded-3xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-500 hover:-translate-y-2">
                        <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center mb-6 text-purple-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold mb-3">Дизайн-проект</h4>
                        <p class="text-gray-400 text-sm">Визуализация штор в вашем интерьере до начала пошива.</p>
                    </div>
                     <!-- Feature 3 -->
                    <div class="group p-8 rounded-3xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-500 hover:-translate-y-2">
                        <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center mb-6 text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold mb-3">Точность в срок</h4>
                        <p class="text-gray-400 text-sm">Собственное производство позволяет нам соблюдать сроки день в день.</p>
                    </div>
                </div>
             </div>

             <!-- Footer -->
             <footer class="mt-32 border-t border-white/10 pt-12 pb-6 text-center text-gray-500 text-sm">
                <p>&copy; 2026 Roskarniz. Все права защищены.</p>
             </footer>

        </div>

    </div>
</template>

<style scoped>
.glass-panel {
    background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
}

@keyframes fade-in-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
    animation-name: fade-in-up;
    animation-duration: 0.8s;
    animation-timing-function: ease-out;
}
</style>
