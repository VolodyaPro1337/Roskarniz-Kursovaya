<script setup lang="ts">
import gsap from 'gsap';
import { onMounted, onUnmounted, ref } from 'vue';

const cursor = ref<HTMLElement | null>(null);
const follower = ref<HTMLElement | null>(null);

const onMouseMove = (e: MouseEvent) => {
    // Основная точка следует мгновенно
    gsap.set(cursor.value, { x: e.clientX, y: e.clientY });
    
    // Фолловер следует с задержкой/сглаживанием
    gsap.to(follower.value, { 
        x: e.clientX, 
        y: e.clientY, 
        duration: 0.6, 
        ease: "power3.out" 
    });
};

const onMouseEnter = () => {
    gsap.to([cursor.value, follower.value], { opacity: 1, scale: 1 });
};

const onMouseLeave = () => {
    gsap.to([cursor.value, follower.value], { opacity: 0, scale: 0 });
};

const addHoverListeners = () => {
    const hoverables = document.querySelectorAll('.cursor-hover');
    hoverables.forEach(el => {
        el.addEventListener('mouseenter', () => {
            gsap.to(follower.value, { scale: 3, backgroundColor: 'rgba(255, 255, 255, 0.1)', borderColor: 'rgba(255, 255, 255, 0.5)' });
        });
        el.addEventListener('mouseleave', () => {
            gsap.to(follower.value, { scale: 1, backgroundColor: 'transparent', borderColor: 'rgba(255, 255, 255, 0.3)' });
        });
    });
};

onMounted(() => {
    window.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseenter', onMouseEnter);
    document.addEventListener('mouseleave', onMouseLeave);
    
    // Логика обновления слушателей при изменении DOM (простой таймаут для демонстрации)
    setTimeout(addHoverListeners, 1000);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseenter', onMouseEnter);
    document.removeEventListener('mouseleave', onMouseLeave);
});
</script>

<template>
    <div ref="cursor" class="fixed top-0 left-0 w-2 h-2 bg-red-500 rounded-full pointer-events-none z-[100] -translate-x-1/2 -translate-y-1/2 mix-blend-difference shadow-[0_0_10px_rgba(239,68,68,0.8)]"></div>
    <div ref="follower" class="fixed top-0 left-0 w-10 h-10 border border-white/30 rounded-full pointer-events-none z-[99] -translate-x-1/2 -translate-y-1/2 transition-colors duration-300"></div>
</template>
