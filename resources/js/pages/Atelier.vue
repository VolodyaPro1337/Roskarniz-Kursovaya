<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import jsPDF from 'jspdf';
import { onMounted, ref, computed, nextTick } from 'vue';


gsap.registerPlugin(ScrollTrigger);

// --- CSS Noise (Data URI to fix 404) ---
const noiseBg = `url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.05'/%3E%3C/svg%3E")`;

// --- Данные для истории прокрутки (Процесс) ---
const processSteps = [
    { id: 1, title: 'Замер', description: 'Наши эксперты приезжают с образцами и лазерной точностью.', icon: '📏', image: '/images/atelier/measurement.png' },
    { id: 2, title: 'Эскиз', description: 'Создаем визуализацию в интерьере до начала работ.', icon: '✏️', image: '/images/atelier/sketch.png' },
    { id: 3, title: 'Пошив', description: 'Ручная работа в нашем цеху. Идеальные швы.', icon: '🧵', image: '/images/atelier/sewing.png' },
    { id: 4, title: 'Монтаж', description: 'Чистая установка карнизов и развеска штор.', icon: '🔨', image: '/images/atelier/installation.png' }
];

const processContainer = ref<HTMLElement | null>(null);

// --- Состояние портфолио (До/После) ---
const sliderValue = ref(50);

// --- WIZARD STATE ---
const step = ref(1);
const isGeneratingPDF = ref(false);

const selections = ref({
    styles: [] as string[],
    fabrics: [] as string[],
    colors: [] as string[],
    cornice: 'profile' // Default single selection
});

// --- Data ---
const wizardData = ref({
    styles: [] as any[],
    fabrics: [] as any[],
    colors: [] as any[],
    cornices: [] as any[]
});

onMounted(async () => {
    try {
        const response = await fetch('/api/materials');
        const data = await response.json();
        wizardData.value = data;
    } catch (e) {
        console.error('Failed to load materials', e);
    }

    gsap.from('.wizard-card', {
        y: 30,
        opacity: 0,
        duration: 0.8,
        stagger: 0.1,
        ease: 'power3.out'
    });
});
/*
// Old Hardcoded Data Removed
*/

// --- Computed Helpers ---
const selectedStylesData = computed(() => wizardData.value.styles.filter((s: any) => selections.value.styles.includes(s.id)));
const selectedFabricsData = computed(() => wizardData.value.fabrics.filter((f: any) => selections.value.fabrics.includes(f.id)));
const selectedColorsData = computed(() => wizardData.value.colors.filter((c: any) => selections.value.colors.includes(c.id)));
const selectedCorniceData = computed(() => wizardData.value.cornices.find((c: any) => c.id === selections.value.cornice));

// --- Динамический расчет цены ---
const PREPAYMENT_PERCENT = 50; // Процент предоплаты
const BASE_WORK_PRICE = 15000; // Базовая стоимость работ (пошив, ВТО)
const INSTALLATION_PRICE = 10000; // Монтаж и навеска

const priceBreakdown = computed(() => {
    // Сумма цен выбранных тканей
    const fabricsTotal = selectedFabricsData.value.reduce((sum: number, f: any) => {
        return sum + (f.properties?.price || 5000);
    }, 0);
    
    // Цена карниза
    const cornicePrice = selectedCorniceData.value?.properties?.price || 15000;
    
    // Наценка за стили (каждый стиль +10% к работе)
    const styleMultiplier = 1 + (selectedStylesData.value.length * 0.1);
    
    // Наценка за цвета (сложная палитра = больше работы)
    const colorBonus = selectedColorsData.value.length > 3 ? 5000 : 0;
    
    const materials = fabricsTotal || 25000; // Минимум для расчета
    const cornices = cornicePrice;
    const work = Math.round(BASE_WORK_PRICE * styleMultiplier) + colorBonus;
    const installation = INSTALLATION_PRICE;
    const total = materials + cornices + work + installation;
    const deposit = Math.round(total * (PREPAYMENT_PERCENT / 100));
    
    return {
        materials,
        cornices,
        work,
        installation,
        total,
        deposit,
        prepaymentPercent: PREPAYMENT_PERCENT
    };
});

// --- Logic ---
const toggleSelection = (category: 'styles' | 'fabrics' | 'colors', id: string) => {
    const list = selections.value[category];
    const index = list.indexOf(id);
    if (index > -1) {
        list.splice(index, 1);
    } else {
        list.push(id);
    }
};

const setCornice = (id: string) => {
    selections.value.cornice = id;
};

const nextStep = () => {
    if (step.value < 4) step.value++;
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

// --- PDF Generation (Native jsPDF without iframe/html2canvas) ---
const generatePDF = async () => {
    isGeneratingPDF.value = true;
    
    try {
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });
        
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 20;
        let y = margin;
        
        // --- Шапка ---
        pdf.setFillColor(0, 0, 0);
        pdf.rect(0, 0, pageWidth, 45, 'F');
        
        pdf.setTextColor(255, 255, 255);
        pdf.setFontSize(28);
        pdf.setFont('helvetica', 'bold');
        pdf.text('ROSKARNIZ', margin, 25);
        
        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        pdf.text('ATELIER & DESIGN STUDIO', margin, 35);
        
        // Дата справа
        const date = new Date().toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
        pdf.text(date, pageWidth - margin, 25, { align: 'right' });
        pdf.text(`ID: ${Math.floor(Math.random() * 10000)}`, pageWidth - margin, 35, { align: 'right' });
        
        y = 60;
        pdf.setTextColor(0, 0, 0);
        
        // --- Заголовок документа ---
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        pdf.text('Концепция интерьера', margin, y);
        y += 15;
        
        // --- Выбранные стили ---
        pdf.setFontSize(12);
        pdf.setFont('helvetica', 'bold');
        pdf.text('Стили:', margin, y);
        pdf.setFont('helvetica', 'normal');
        const stylesText = selectedStylesData.value.map((s: any) => s.name).join(', ') || 'Не выбраны';
        pdf.text(stylesText, margin + 25, y);
        y += 10;
        
        // --- Ткани ---
        pdf.setFont('helvetica', 'bold');
        pdf.text('Ткани:', margin, y);
        pdf.setFont('helvetica', 'normal');
        const fabricsText = selectedFabricsData.value.map((f: any) => f.name).join(', ') || 'Не выбраны';
        pdf.text(fabricsText, margin + 25, y);
        y += 10;
        
        // --- Цвета ---
        pdf.setFont('helvetica', 'bold');
        pdf.text('Цвета:', margin, y);
        pdf.setFont('helvetica', 'normal');
        const colorsText = selectedColorsData.value.map((c: any) => c.name).join(', ') || 'Не выбраны';
        pdf.text(colorsText, margin + 25, y);
        y += 10;
        
        // --- Карниз ---
        pdf.setFont('helvetica', 'bold');
        pdf.text('Карниз:', margin, y);
        pdf.setFont('helvetica', 'normal');
        pdf.text(selectedCorniceData.value?.name || 'Стандарт', margin + 30, y);
        y += 20;
        
        // --- Линия разделитель ---
        pdf.setDrawColor(200, 200, 200);
        pdf.line(margin, y, pageWidth - margin, y);
        y += 15;
        
        // --- Смета ---
        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('Предварительная смета', margin, y);
        y += 12;
        
        pdf.setFontSize(11);
        pdf.setFont('helvetica', 'normal');
        
        const prices = priceBreakdown.value;
        const priceItems = [
            ['Материалы и ткани', prices.materials],
            ['Карнизы и фурнитура', prices.cornices],
            ['Пошив и ВТО', prices.work],
            ['Монтаж и навеска', prices.installation]
        ];
        
        priceItems.forEach(([label, price]) => {
            pdf.text(label as string, margin, y);
            pdf.text(`${(price as number).toLocaleString('ru-RU')} ₽`, pageWidth - margin, y, { align: 'right' });
            y += 8;
        });
        
        // Итого
        y += 5;
        pdf.setDrawColor(0, 0, 0);
        pdf.line(margin, y, pageWidth - margin, y);
        y += 10;
        
        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('ИТОГО:', margin, y);
        pdf.text(`${prices.total.toLocaleString('ru-RU')} ₽`, pageWidth - margin, y, { align: 'right' });
        y += 12;
        
        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        pdf.setTextColor(100, 100, 100);
        pdf.text(`Предоплата ${prices.prepaymentPercent}%: ${prices.deposit.toLocaleString('ru-RU')} ₽`, margin, y);
        y += 20;
        
        // --- Контакты ---
        pdf.setTextColor(0, 0, 0);
        pdf.setFillColor(245, 245, 245);
        pdf.rect(margin, y, pageWidth - margin * 2, 25, 'F');
        
        pdf.setFontSize(11);
        pdf.setFont('helvetica', 'bold');
        pdf.text('Готовы начать?', margin + 5, y + 10);
        pdf.setFont('helvetica', 'normal');
        pdf.text('Свяжитесь с нами для вызова замерщика', margin + 5, y + 18);
        
        pdf.setFont('helvetica', 'bold');
        pdf.text('+7 (495) 000-00-00', pageWidth - margin - 5, y + 10, { align: 'right' });
        pdf.setFont('helvetica', 'normal');
        pdf.text('hello@roskarniz.ru', pageWidth - margin - 5, y + 18, { align: 'right' });
        
        // --- Футер ---
        pdf.setFontSize(8);
        pdf.setTextColor(150, 150, 150);
        pdf.text('© 2024 Roskarniz Atelier. Все права защищены.', margin, pageHeight - 10);
        pdf.text('г. Москва', pageWidth - margin, pageHeight - 10, { align: 'right' });
        
        // Сохранение
        pdf.save(`Roskarniz_Concept_${Date.now()}.pdf`);
        
    } catch (e) {
        console.error(e);
        alert('Ошибка генерации PDF: ' + e);
    } finally {
        isGeneratingPDF.value = false;
    }
};

onMounted(() => {
    nextTick(() => {
        if (processContainer.value) {
            gsap.to(processContainer.value, {
                x: () => -(processContainer.value!.scrollWidth - window.innerWidth),
                ease: "none",
                scrollTrigger: {
                    trigger: "#process-section",
                    pin: true,
                    scrub: 1,
                    end: () => "+=" + processContainer.value!.scrollWidth,
                    invalidateOnRefresh: true
                }
            });
        }
    });
});
</script>

<script lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';

export default {
    layout: MainLayout
}
</script>

<template>
    <Head title="Ателье" />
    <CustomCursor />

    <div class="bg-[#050505] text-white selection:bg-white selection:text-black overflow-x-hidden font-sans">
        
        <!-- ... HERO & PROCESS Sections Unchanged ... -->
        <!-- Just referencing them to keep context, but focusing edit on Wizard Section -->

        <!-- ГЛАВНЫЙ ЭКРАН (Keep existing code) -->
        <div class="h-screen flex items-center justify-center relative overflow-hidden">
             <!-- ... -->
             <div class="absolute inset-0 bg-[url('/images/atelier/hero.png')] bg-cover bg-center opacity-30"></div>
             <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-transparent to-black/50"></div>
             <div class="text-center relative z-10 px-4">
                 <h1 class="text-7xl md:text-9xl font-black tracking-tighter mix-blend-difference mb-8">АТЕЛЬЕ</h1>
                 <button @click="processContainer?.scrollIntoView({ behavior: 'smooth' })" class="px-8 py-3 rounded-full border border-white/20 uppercase tracking-widest hover:bg-white hover:text-black transition-all">Start</button>
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

                    <!-- Визуальная заглушка (Фоновое изображение) -->
                    <div class="absolute inset-0 opacity-40 pointer-events-none mix-blend-overlay">
                        <img :src="step.image" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500" alt="" />
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
                <!-- Изображение ПОСЛЕ -->
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/images/atelier/after.png');">
                    <div class="absolute top-10 right-10 bg-black/50 backdrop-blur px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wider">После</div>
                </div>

                <!-- Изображение ДО -->
                <div 
                    class="absolute inset-0 bg-cover bg-center border-r-2 border-white"
                    :style="{ width: sliderValue + '%', backgroundImage: 'url(\'/images/atelier/before.png\')' }"
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

        <!-- 3. КОНСТРУКТОР МУДБОРДА (Updated Wizard) -->
        <section id="wizard-section" class="py-32 bg-[#0a0a0a] border-t border-white/5 relative overflow-hidden min-h-screen flex flex-col justify-center">
            
            <div class="max-w-7xl mx-auto px-4 w-full relative z-10">
                <!-- Wizard Header -->
                <div class="flex justify-between items-end mb-12">
                    <div>
                         <span class="text-red-500 text-sm font-bold tracking-widest uppercase mb-2 block">Конструктор идей</span>
                         <h2 class="text-4xl md:text-6xl font-bold">
                            <span v-if="step === 1">1. Стиль</span>
                            <span v-else-if="step === 2">2. Ткани</span>
                            <span v-else-if="step === 3">3. Детали</span>
                            <span v-else>4. Итог</span>
                         </h2>
                    </div>
                    <!-- Steps Indicator -->
                    <div class="flex gap-2">
                        <div v-for="i in 4" :key="i" class="w-12 h-1 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-red-500 transition-all duration-500" :class="step >= i ? 'w-full' : 'w-0'"></div>
                        </div>
                    </div>
                </div>

                <!-- STEP 1: STYLES (Grid 4x2) -->
                <div v-if="step === 1" class="grid grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in">
                    <div 
                        v-for="style in wizardData.styles" 
                        :key="style.id"
                        @click="toggleSelection('styles', style.id)"
                        class="aspect-[3/4] rounded-2xl relative overflow-hidden cursor-pointer group border-2 transition-all"
                        :class="selections.styles.includes(style.id) ? 'border-red-500' : 'border-white/10 hover:border-white/30'"
                    >
                         <img :src="style.image" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity duration-700" alt="">
                         <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                         <div class="absolute bottom-6 left-6">
                            <h3 class="text-xl font-bold">{{ style.name }}</h3>
                         </div>
                         <div v-if="selections.styles.includes(style.id)" class="absolute top-4 right-4 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white font-bold">✓</div>
                    </div>
                </div>

                <!-- STEP 2: FABRICS (Expanded with Gradients) -->
                <div v-else-if="step === 2" class="grid grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in">
                    <div 
                        v-for="fabric in wizardData.fabrics" 
                        :key="fabric.id"
                        @click="toggleSelection('fabrics', fabric.id)"
                        class="aspect-square rounded-2xl p-6 border-2 transition-all cursor-pointer group flex flex-col justify-between overflow-hidden relative"
                        :class="selections.fabrics.includes(fabric.id) ? 'border-red-500' : 'border-white/10 hover:border-white/30'"
                        :style="{ background: fabric.gradient }"
                    >
                        <div class="z-10 mix-blend-difference text-white">
                            <div class="text-lg font-bold mb-1">{{ fabric.name }}</div>
                            <div class="text-xs opacity-80">{{ fabric.desc }}</div>
                        </div>
                         <div v-if="selections.fabrics.includes(fabric.id)" class="absolute top-4 right-4 w-8 h-8 bg-white text-black rounded-full flex items-center justify-center font-bold z-10 shadow-lg">✓</div>
                    </div>
                </div>

                <!-- STEP 3: DETAILS (Colors + Cornice) -->
                <div v-else-if="step === 3" class="animate-fade-in space-y-12">
                    
                    <!-- Colors -->
                    <div>
                        <h3 class="text-xl font-bold mb-6 text-gray-400">Палитра</h3>
                        <div class="grid grid-cols-4 md:grid-cols-8 gap-4">
                            <div 
                                v-for="color in wizardData.colors" 
                                :key="color.id"
                                @click="toggleSelection('colors', color.id)"
                                class="aspect-square rounded-full border-2 transition-all cursor-pointer flex items-center justify-center relative group"
                                :class="selections.colors.includes(color.id) ? 'border-white scale-110' : 'border-transparent hover:scale-105'"
                                :style="{ backgroundColor: color.hex }"
                            >
                                <div v-if="selections.colors.includes(color.id)" class="w-8 h-8 bg-black/50 backdrop-blur rounded-full flex items-center justify-center text-white">✓</div>
                            </div>
                        </div>
                    </div>

                    <!-- Cornice Type -->
                    <div>
                         <h3 class="text-xl font-bold mb-6 text-gray-400">Карнизная система</h3>
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                             <div 
                                v-for="cornice in wizardData.cornices"
                                :key="cornice.id"
                                @click="setCornice(cornice.id)"
                                class="p-6 rounded-2xl border-2 cursor-pointer transition-all bg-white/5 hover:bg-white/10"
                                :class="selections.cornice === cornice.id ? 'border-red-500' : 'border-white/10'"
                             >
                                <svg class="w-8 h-8 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="cornice.icon"></svg>
                                <div class="font-bold mb-1">{{ cornice.name }}</div>
                                <div class="text-xs text-gray-500">{{ cornice.desc }}</div>
                             </div>
                         </div>
                    </div>

                </div>

                <!-- STEP 4: SUMMARY -->
                <div v-else class="max-w-4xl mx-auto text-center animate-fade-in">
                    <div class="bg-[#111] border border-white/10 rounded-3xl p-12 mb-8 relative overflow-hidden">
                        <div class="absolute inset-0 opacity-10 mix-blend-overlay" :style="{ backgroundImage: noiseBg }"></div>
                        
                        <h3 class="text-2xl font-bold mb-8">Ваша концепция собрана</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-left mb-12 relative z-10">
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Стили</div>
                                <div class="font-bold text-lg leading-tight">{{ selectedStylesData.map(s => s.name).join(', ') || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Ткани</div>
                                <div class="font-bold text-lg leading-tight">{{ selectedFabricsData.map(f => f.name).join(', ') || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Карниз</div>
                                <div class="font-bold text-lg leading-tight">{{ selectedCorniceData?.name }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Бюджет</div>
                                <div class="font-bold text-lg leading-tight text-red-500">~ 145 000 ₽</div>
                            </div>
                        </div>

                        <button 
                            @click="generatePDF"
                            :disabled="isGeneratingPDF"
                            class="w-full py-5 bg-white text-black font-bold text-lg rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center gap-3 disabled:opacity-50 relative z-20 cursor-pointer"
                        >
                            <span v-if="isGeneratingPDF" class="animate-spin w-5 h-5 border-2 border-black border-t-transparent rounded-full"></span>
                            <span v-else>📄 Скачать PDF-Презентацию</span>
                        </button>
                        <p class="text-xs text-gray-500 mt-4 relative z-10">Файл содержит полный мудборд, смету и таймлайн работ.</p>
                    </div>
                    
                    <button @click="step = 1; selections = { styles: [], fabrics: [], colors: [], cornice: 'profile' }" class="text-sm text-gray-500 hover:text-white border-b border-transparent hover:border-white transition-all relative z-20 cursor-pointer">
                        Собрать другую концепцию
                    </button>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-12 border-t border-white/10 pt-8" v-if="step < 4">
                    <button 
                         @click="prevStep" 
                         class="text-sm font-bold uppercase tracking-widest text-gray-500 hover:text-white transition-colors disabled:opacity-0"
                         :disabled="step === 1"
                    >
                        Назад
                    </button>
                    <button 
                        @click="nextStep"
                        class="px-8 py-3 bg-red-600 rounded-full text-sm font-bold uppercase tracking-widest hover:bg-red-500 transition-colors"
                    >
                        Далее
                    </button>
                </div>
            </div>
        </section>

    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
