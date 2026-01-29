<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import html2canvas from 'html2canvas';
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

// Состояния загрузки и ошибок
const isLoadingMaterials = ref(true);
const loadError = ref('');

onMounted(async () => {
    try {
        const response = await fetch('/api/materials');
        if (!response.ok) {
            throw new Error(`Ошибка загрузки: ${response.status}`);
        }
        const data = await response.json();
        wizardData.value = data;
    } catch (e: any) {
        console.error('Failed to load materials', e);
        loadError.value = e.message || 'Не удалось загрузить материалы. Проверьте соединение.';
    } finally {
        isLoadingMaterials.value = false;
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

// --- Расчет сложности и таймлайна ---
const complexityLevel = computed(() => {
    const stylesCount = selectedStylesData.value.length;
    const fabricsCount = selectedFabricsData.value.length;
    const colorsCount = selectedColorsData.value.length;
    
    const score = stylesCount * 2 + fabricsCount * 1.5 + colorsCount;
    
    if (score <= 3) return { level: 'Простой', days: 7, color: '#22c55e' };
    if (score <= 6) return { level: 'Средний', days: 14, color: '#eab308' };
    if (score <= 10) return { level: 'Сложный', days: 21, color: '#f97316' };
    return { level: 'Премиум', days: 30, color: '#ef4444' };
});

const timeline = computed(() => {
    const complexity = complexityLevel.value;
    const today = new Date();
    
    const addDays = (date: Date, days: number) => {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    };
    
    const formatDate = (date: Date) => date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
    
    return [
        { stage: 'Замер и согласование', start: formatDate(today), end: formatDate(addDays(today, 2)), days: 2 },
        { stage: 'Закупка материалов', start: formatDate(addDays(today, 3)), end: formatDate(addDays(today, 5)), days: 3 },
        { stage: 'Пошив и ВТО', start: formatDate(addDays(today, 6)), end: formatDate(addDays(today, 6 + Math.ceil(complexity.days * 0.5))), days: Math.ceil(complexity.days * 0.5) },
        { stage: 'Монтаж и сдача', start: formatDate(addDays(today, 7 + Math.ceil(complexity.days * 0.5))), end: formatDate(addDays(today, complexity.days)), days: 2 }
    ];
});

// --- Сохранение заказа в БД ---
const saveOrder = async () => {
    // Собираем все выбранные материалы (slug'и)
    const allMaterials = [
        ...selections.value.styles,
        ...selections.value.fabrics,
        ...selections.value.colors,
        selections.value.cornice
    ].filter(Boolean);
    
    if (allMaterials.length === 0) {
        return null; // Нечего сохранять
    }
    
    try {
        const response = await fetch('/api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': document.cookie
                    .split('; ')
                    .find(row => row.startsWith('XSRF-TOKEN='))
                    ?.split('=')[1] || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                materials: allMaterials,
                total_price: priceBreakdown.value.total,
                complexity: complexityLevel.value.level,
                estimated_days: complexityLevel.value.days,
                guest_info: null // Можно добавить форму сбора контактов
            })
        });
        
        if (!response.ok) {
            const error = await response.json();
            console.error('Order save error:', error);
            return null;
        }
        
        const data = await response.json();
        console.log('Order saved:', data.order_id);
        return data.order_id;
    } catch (e) {
        console.error('Failed to save order:', e);
        return null;
    }
};

// --- PDF Generation (html2canvas + hidden div) ---
const generatePDF = async () => {
    isGeneratingPDF.value = true;
    
    // Сохраняем заказ в БД перед генерацией PDF
    const orderId = await saveOrder();
    
    try {
        // Создаем скрытый контейнер для рендера
        const container = document.createElement('div');
        container.id = 'pdf-render-container';
        Object.assign(container.style, {
            position: 'fixed',
            left: '-9999px',
            top: '0',
            width: '794px', // A4 width at 96dpi
            background: '#fff',
            fontFamily: 'system-ui, -apple-system, sans-serif',
            fontSize: '12px',
            color: '#000',
            padding: '0',
            zIndex: '-1'
        });
        document.body.appendChild(container);
        
        const date = new Date().toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
        const pdfOrderId = orderId || Math.floor(Math.random() * 10000); // Используем сохранённый ID или временный
        const prices = priceBreakdown.value;
        const complexity = complexityLevel.value;
        const timelineData = timeline.value;
        
        // Мудборд стили
        const stylesHtml = selectedStylesData.value.map((s: any) => `
            <div style="flex: 0 0 calc(50% - 8px); aspect-ratio: 4/3; position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e5e5e5;">
                <img src="${s.image}" style="width: 100%; height: 100%; object-fit: cover;" crossorigin="anonymous">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: #fff; padding: 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">${s.name}</div>
            </div>
        `).join('') || '<div style="flex: 1; padding: 40px; text-align: center; color: #999; background: #f5f5f5; border-radius: 8px;">Стили не выбраны</div>';
        
        // Палитра цветов
        const colorsHtml = selectedColorsData.value.map((c: any) => `
            <div style="text-align: center;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: ${c.hex}; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.15); margin: 0 auto 6px;"></div>
                <div style="font-size: 9px; color: #666;">${c.name}</div>
            </div>
        `).join('') || '<div style="color: #999;">Не выбраны</div>';
        
        // Ткани
        const fabricsHtml = selectedFabricsData.value.map((f: any) => `
            <div style="display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eee;">
                <div style="width: 44px; height: 44px; border-radius: 8px; background: ${f.gradient || '#ccc'}; flex-shrink: 0;"></div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 12px;">${f.name}</div>
                    <div style="font-size: 10px; color: #666;">${f.desc || 'Премиум ткань'}</div>
                </div>
                <div style="font-size: 11px; font-weight: 600;">${(f.properties?.price || 5000).toLocaleString()} ₽</div>
            </div>
        `).join('') || '<div style="padding: 20px; text-align: center; color: #999;">Ткани не выбраны</div>';
        
        // Таймлайн
        const timelineHtml = timelineData.map((t, i) => `
            <div style="display: flex; align-items: center; gap: 12px; padding: 8px 0; ${i < timelineData.length - 1 ? 'border-bottom: 1px dashed #ddd;' : ''}">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: ${complexity.color}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">${i + 1}</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 11px;">${t.stage}</div>
                    <div style="font-size: 10px; color: #888;">${t.start} — ${t.end}</div>
                </div>
                <div style="font-size: 10px; color: #666;">${t.days} дн.</div>
            </div>
        `).join('');
        
        container.innerHTML = `
            <!-- HEADER -->
            <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: #fff; padding: 30px 40px; display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <div style="font-size: 32px; font-weight: 900; letter-spacing: -1px;">ROSKARNIZ</div>
                    <div style="font-size: 10px; letter-spacing: 3px; opacity: 0.7; margin-top: 4px;">ATELIER & DESIGN STUDIO</div>
                </div>
                <div style="text-align: right; font-size: 11px;">
                    <div style="font-weight: 600;">Концепция интерьера</div>
                    <div style="opacity: 0.7; margin-top: 2px;">${date}</div>
                    <div style="opacity: 0.5; margin-top: 2px;">ID: #${pdfOrderId}</div>
                </div>
            </div>
            
            <div style="padding: 30px 40px;">
                <!-- MOODBOARD SECTION -->
                <div style="margin-bottom: 30px;">
                    <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #000;">Концепция стиля</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                        ${stylesHtml}
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <!-- LEFT: Colors + Fabrics -->
                    <div>
                        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; color: #555;">Палитра</div>
                        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
                            ${colorsHtml}
                        </div>
                        
                        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; color: #555;">Материалы</div>
                        <div style="background: #fafafa; border-radius: 12px; padding: 12px;">
                            ${fabricsHtml}
                        </div>
                    </div>
                    
                    <!-- RIGHT: Specs + Complexity -->
                    <div>
                        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; color: #555;">Спецификация</div>
                        <div style="background: #fafafa; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;"><span>Карниз:</span> <strong>${selectedCorniceData.value?.name || 'Профильный'}</strong></div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;"><span>Тип сборки:</span> <strong>Ручная складка</strong></div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;"><span>Коэффициент:</span> <strong>1:2.5</strong></div>
                            <div style="display: flex; justify-content: space-between;"><span>Подкладка:</span> <strong>Да (Сатин)</strong></div>
                        </div>
                        
                        <div style="background: ${complexity.color}15; border: 1px solid ${complexity.color}40; border-radius: 12px; padding: 16px; text-align: center;">
                            <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: ${complexity.color};">Сложность проекта</div>
                            <div style="font-size: 24px; font-weight: 800; color: ${complexity.color}; margin: 8px 0;">${complexity.level}</div>
                            <div style="font-size: 12px; color: #666;">Срок выполнения: <strong>${complexity.days} дней</strong></div>
                        </div>
                    </div>
                </div>
                
                <!-- TIMELINE -->
                <div style="margin-bottom: 30px;">
                    <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #000;">Таймлайн работ</div>
                    <div style="background: #fafafa; border-radius: 12px; padding: 16px;">
                        ${timelineHtml}
                    </div>
                </div>
                
                <!-- ESTIMATE -->
                <div style="background: #1a1a1a; color: #fff; border-radius: 16px; padding: 24px;">
                    <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; opacity: 0.7;">Предварительная смета</div>
                    
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #333; font-size: 13px;"><span>Материалы и ткани</span><span>${prices.materials.toLocaleString()} ₽</span></div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #333; font-size: 13px;"><span>Карнизы и фурнитура</span><span>${prices.cornices.toLocaleString()} ₽</span></div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #333; font-size: 13px;"><span>Пошив и ВТО</span><span>${prices.work.toLocaleString()} ₽</span></div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #333; font-size: 13px;"><span>Монтаж и навеска</span><span>${prices.installation.toLocaleString()} ₽</span></div>
                    
                    <div style="display: flex; justify-content: space-between; padding: 16px 0 8px; font-size: 20px; font-weight: 800;"><span>ИТОГО:</span><span>${prices.total.toLocaleString()} ₽</span></div>
                    <div style="font-size: 11px; opacity: 0.6;">Предоплата ${prices.prepaymentPercent}%: ${prices.deposit.toLocaleString()} ₽</div>
                </div>
                
                <!-- CTA -->
                <div style="margin-top: 24px; padding: 20px; border: 2px solid #000; display: flex; justify-content: space-between; align-items: center; border-radius: 12px;">
                    <div>
                        <div style="font-weight: 700; font-size: 14px;">Готовы начать?</div>
                        <div style="font-size: 11px; color: #666;">Свяжитесь с нами для вызова замерщика с образцами</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 16px; font-weight: 800;">+7 (495) 000-00-00</div>
                        <div style="font-size: 11px; color: #666;">hello@roskarniz.ru</div>
                    </div>
                </div>
            </div>
            
            <!-- FOOTER -->
            <div style="background: #f5f5f5; padding: 16px 40px; display: flex; justify-content: space-between; font-size: 9px; color: #999;">
                <div>© 2024 Roskarniz Atelier. Все права защищены.</div>
                <div>г. Москва | roskarniz.ru</div>
            </div>
        `;
        
        // Ждем загрузки изображений
        const images = container.getElementsByTagName('img');
        await Promise.all(Array.from(images).map(img => {
            if (img.complete) return Promise.resolve();
            return new Promise(resolve => {
                img.onload = resolve;
                img.onerror = resolve;
            });
        }));
        
        // Небольшая задержка для рендера стилей
        await new Promise(resolve => setTimeout(resolve, 300));
        
        // Рендерим в canvas
        const canvas = await html2canvas(container, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            // Фикс для oklch и других современных CSS функций
            onclone: (clonedDoc) => {
                // Сброс стилей, которые html2canvas не понимает
                const allElements = clonedDoc.querySelectorAll('*');
                allElements.forEach((el: Element) => {
                    const htmlEl = el as HTMLElement;
                    const computed = window.getComputedStyle(htmlEl);
                    // Если цвет содержит oklch, заменим на чёрный
                    if (computed.color.includes('oklch') || computed.color.includes('color(')) {
                        htmlEl.style.color = '#000000';
                    }
                    if (computed.backgroundColor.includes('oklch') || computed.backgroundColor.includes('color(')) {
                        htmlEl.style.backgroundColor = 'transparent';
                    }
                });
            }
        });
        
        // Создаем PDF
        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'px',
            format: [canvas.width / 2, canvas.height / 2]
        });
        
        pdf.addImage(imgData, 'JPEG', 0, 0, canvas.width / 2, canvas.height / 2);
        pdf.save(`Roskarniz_Concept_${pdfOrderId}.pdf`);
        
        // Удаляем контейнер
        document.body.removeChild(container);
        
    } catch (e) {
        console.error('PDF generation error:', e);
        alert('Ошибка генерации PDF. Попробуйте ещё раз.');
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
