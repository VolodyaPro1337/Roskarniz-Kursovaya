<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, computed, nextTick } from 'vue';
import Navigation from '@/components/Navigation.vue';
import CustomCursor from '@/components/CustomCursor.vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import MainLayout from '@/layouts/MainLayout.vue';

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

// Mock Data (Expanded)
const wizardData = {
    styles: [
        { id: 'minimal', name: 'Минимализм', image: '/images/atelier/style_minimal.png' },
        { id: 'classic', name: 'Классика', image: '/images/atelier/style_classic.png' },
        { id: 'loft', name: 'Лофт', image: '/images/atelier/style_loft.png' },
        { id: 'scandi', name: 'Сканди', image: '/images/atelier/style_scandi.png' }
    ],
    fabrics: [
        { id: 'velvet', name: 'Бархат Royal', desc: 'Плотный ворс, глубокий отлив', gradient: 'linear-gradient(135deg, #372828 0%, #684e4e 100%)' },
        { id: 'linen', name: 'Эко-Лен', desc: 'Грубая фактура, натуральность', gradient: 'linear-gradient(135deg, #e3dacd 0%, #b8ad9e 100%)' },
        { id: 'silk', name: 'Дикий Шелк', desc: 'Мягкий блеск, струящаяся ткань', gradient: 'linear-gradient(135deg, #f5f5f5 0%, #dcdcdc 100%)' },
        { id: 'blackout', name: 'Dimout 90%', desc: 'Защита от солнца и шума', gradient: 'linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%)' }
    ],
    colors: [
        { id: 'warm_beige', name: 'Песок', hex: '#d6cdb8' },
        { id: 'cold_grey', name: 'Графит', hex: '#374151' },
        { id: 'accent_ruby', name: 'Рубин', hex: '#9f1239' },
        { id: 'base_white', name: 'Молочный', hex: '#fafafa' }
    ],
    cornices: [
        { id: 'profile', name: 'Профильный (Скрытый)', desc: 'Минимализм, монтаж в нишу', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
        { id: 'decorative', name: 'Декоративный (Труба)', desc: 'Акцент на фурнитуру', icon: 'M2 12h20M2 12a2 2 0 012-2h16a2 2 0 012 2v0a2 2 0 01-2 2H4a2 2 0 01-2-2v0z' },
        { id: 'electro', name: 'Электрокарниз', desc: 'Управление с пульта/Умного дома', icon: 'M13 10V3L4 14h7v7l9-11h-7z' }
    ]
};

// --- Computed Helpers ---
const selectedStylesData = computed(() => wizardData.styles.filter(s => selections.value.styles.includes(s.id)));
const selectedFabricsData = computed(() => wizardData.fabrics.filter(f => selections.value.fabrics.includes(f.id)));
const selectedColorsData = computed(() => wizardData.colors.filter(c => selections.value.colors.includes(c.id)));
const selectedCorniceData = computed(() => wizardData.cornices.find(c => c.id === selections.value.cornice));

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

// --- PDF Generation ---
// --- PDF Generation (ROBUST IFRAME METHOD) ---
const generatePDF = async () => {
    isGeneratingPDF.value = true;
    
    try {
        // 1. Create a hidden iframe
        const iframe = document.createElement('iframe');
        Object.assign(iframe.style, {
            position: 'fixed',
            left: '-10000px',
            top: '0',
            width: '1200px',
            height: '1600px', // Taller for more info
            border: 'none',
            zIndex: '9999'
        });
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow?.document;
        if (!doc) throw new Error('Cannot access iframe');

        // 2. Prepare Data (Helpers for HTML)
        const date = new Date().toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
        const stylesHtml = selectedStylesData.value.map(s => `
            <div style="aspect-ratio: 4/3; position: relative; border: 1px solid #eee; background: #f9f9f9; overflow: hidden; border-radius: 8px;">
                <img src="${s.image}" style="width: 100%; height: 100%; object-fit: cover;" onload="this.setAttribute('loaded','true')">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.9); padding: 8px; font-size: 11px; font-weight: bold; text-transform: uppercase;">${s.name}</div>
            </div>
        `).join('') || '<div style="grid-column: span 2; padding: 20px; text-align: center; color: #999;">Стиль не выбран</div>';

        const fabricsHtml = selectedFabricsData.value.map(f => `
            <div style="display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 8px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: ${f.gradient}; border: 1px solid #ddd; flex-shrink: 0;"></div>
                <div>
                    <div style="font-weight: bold; font-size: 12px;">${f.name}</div>
                    <div style="font-size: 10px; color: #666;">${f.desc}</div>
                </div>
            </div>
        `).join('') || '<div style="color: #999;">Ткани не выбраны</div>';

        const colorsHtml = selectedColorsData.value.map(c => `
             <div style="text-align: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background-color: ${c.hex}; border: 1px solid #ddd; margin: 0 auto 4px auto;"></div>
                <div style="font-size: 9px; color: #666;">${c.name}</div>
             </div>
        `).join('') || '<div style="color: #999;">Цвета не выбраны</div>';

        const totalPrice = 145000;
        const deposit = Math.round(totalPrice * 0.7);

        // 3. Build Rich HTML Content
        const htmlContent = `
            <!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <style>
                    body { margin: 0; padding: 60px; font-family: 'Arial', sans-serif; background: #fff; color: #000; box-sizing: border-box; }
                    h1 { font-size: 42px; margin: 0; line-height: 1; letter-spacing: -1px; text-transform: uppercase; }
                    h2 { font-size: 18px; text-transform: uppercase; letter-spacing: 2px; border-bottom: 2px solid #000; padding-bottom: 10px; margin: 40px 0 20px 0; }
                    h3 { font-size: 14px; font-weight: bold; margin: 0 0 5px 0; color: #333; }
                    p { margin: 0 0 10px 0; font-size: 12px; color: #555; line-height: 1.5; }
                    .header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; border-bottom: 4px solid #000; padding-bottom: 20px; }
                    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
                    .card { background: #f8f8f8; padding: 20px; border-radius: 12px; }
                    .price-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; }
                    .price-row.total { font-weight: bold; font-size: 18px; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px; }
                    .footer { position: fixed; bottom: 40px; left: 60px; right: 60px; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 20px; display: flex; justify-content: space-between; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div>
                        <h1>Roskarniz</h1>
                        <p style="text-transform: uppercase; letter-spacing: 3px; font-size: 10px; margin-top: 5px;">Atelier & Design Studio</p>
                    </div>
                    <div style="text-align: right; font-size: 12px;">
                        <p><strong>Концепция интерьера</strong></p>
                        <p>${date}</p>
                        <p>ID: ${Math.floor(Math.random() * 10000)}</p>
                    </div>
                </div>

                <div class="grid">
                    <!-- LEFT COLUMN -->
                    <div>
                        <h2>Визуализация</h2>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
                            ${stylesHtml}
                        </div>

                        <h2>Палитра оттенков</h2>
                        <div class="card">
                             <div style="display: flex; gap: 15px; flex-wrap: wrap;">${colorsHtml}</div>
                        </div>

                        <h2>Заметки дизайнера</h2>
                        <div style="font-size: 12px; color: #444; background: #fff; border: 1px dashed #ccc; padding: 15px;">
                            <p>Рекомендуется использовать скрытый профильный карниз для сохранения чистоты линий интерьера. Ткани подобраны с учетом освещения: ${selectedFabricsData.value[0]?.name || 'Основная ткань'} обеспечит нужный уровень затемнения.</p>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div>
                        <h2>Материалы и Текстуры</h2>
                        <div class="card" style="margin-bottom: 30px;">
                            ${fabricsHtml}
                        </div>

                        <h2>Техническая спецификация</h2>
                        <div class="card" style="margin-bottom: 30px;">
                             <div class="price-row"><span>Система:</span> <strong>${selectedCorniceData.value?.name || 'Стандарт'}</strong></div>
                             <div class="price-row"><span>Тип сборки:</span> <strong>Ручная складка</strong></div>
                             <div class="price-row"><span>Коэффициент:</span> <strong>1:2.5 (Премиум)</strong></div>
                             <div class="price-row"><span>Подкладка:</span> <strong>Да (Сатин)</strong></div>
                        </div>

                        <h2>Предварительная смета</h2>
                        <div style="background: #000; color: #fff; padding: 25px; border-radius: 12px;">
                            <div class="price-row"><span>Материалы</span> <span>85 000 ₽</span></div>
                            <div class="price-row"><span>Карнизы и фурнитура</span> <span>35 000 ₽</span></div>
                            <div class="price-row"><span>Пошив и ВТО</span> <span>15 000 ₽</span></div>
                            <div class="price-row"><span>Монтаж и навеска</span> <span>10 000 ₽</span></div>
                            <div class="price-row total"><span>ИТОГО:</span> <span>~ ${totalPrice.toLocaleString()} ₽</span></div>
                            <p style="opacity: 0.6; font-size: 10px; margin-top: 10px;">Для старта работ требуется предоплата 70% (${deposit.toLocaleString()} ₽).</p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 40px; padding: 20px; border: 1px solid #000; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h3>Готовы начать?</h3>
                        <p style="margin: 0;">Свяжитесь с нами для вызова замерщика с образцами.</p>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 16px; font-weight: bold;">+7 (495) 000-00-00</div>
                        <div style="font-size: 12px;">hello@roskarniz.ru</div>
                    </div>
                </div>

                <div class="footer">
                    <div>© 2024 Roskarniz Atelier. Все права защищены.</div>
                    <div>г. Москва, ул. Примерная 12</div>
                </div>
            </body>
            </html>
        `;

        doc.open();
        doc.write(htmlContent);
        doc.close();

        // 4. Wait for images to load
        // A smarter wait: check all images in iframe
        await new Promise(resolve => {
            const images = doc.images;
            let loadedCount = 0;
            const total = images.length;
            if (total === 0) return resolve(true);

            const check = () => {
                if (loadedCount === total) resolve(true);
            };

            for (let i = 0; i < total; i++) {
                if (images[i].complete) {
                    loadedCount++;
                } else {
                    images[i].onload = () => { loadedCount++; check(); };
                    images[i].onerror = () => { loadedCount++; check(); };
                }
            }
            check();
            setTimeout(resolve, 2000); // Max wait
        });

        // 5. Capture & Save
        const canvas = await html2canvas(doc.body, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        });

        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF({
            orientation: 'p', // Portrait now, since we have more content
            unit: 'px',
            format: [canvas.width, canvas.height]
        });

        pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
        pdf.save(`Roskarniz_Concept_${Date.now()}.pdf`);

        document.body.removeChild(iframe);

    } catch (e) {
        console.error(e);
        alert('Ошибка PDF: ' + e);
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
