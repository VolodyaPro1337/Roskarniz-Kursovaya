<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ref, reactive, computed, onMounted } from 'vue';
import CustomCursor from '@/components/CustomCursor.vue';
import axios from 'axios';
import { watchDebounced } from '@vueuse/core';
import MainLayout from '@/layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });



// --- Состояние ---
const showQuiz = ref(true); 
const currentStep = ref(0);
const isLoading = ref(false);

// --- Состояние фильтров ---
const filters = reactive({
    search: '',
    category: 'all',
    priceRange: [0, 50000],
    opacity: [] as string[],
    room: [] as string[],
    color: [] as string[],
    material: [] as string[]
});


// --- Данные квиза ---
const quizQuestions = [
    {
        question: 'Какая комната?',
        filterKey: 'room',
        options: [
            { value: 'living', label: 'Гостиная', icon: '🛋️' },
            { value: 'bedroom', label: 'Спальня', icon: '🛏️' },
            { value: 'kitchen', label: 'Кухня', icon: '🍳' },
            { value: 'office', label: 'Кабинет', icon: '💼' },
            { value: 'bathroom', label: 'Ванная', icon: '🛁' },
            { value: 'children', label: 'Детская', icon: '🧸' },
            { value: 'balcony', label: 'Балкон', icon: '🌅' },
            { value: 'any', label: 'Любая', icon: '🏠' }
        ]
    },
    {
        question: 'Нужна ли светозащита?',
        filterKey: 'opacity',
        options: [
            { value: 'transparent', label: 'Прозрачные', icon: '☀️' },
            { value: 'dimout', label: 'Димаут', icon: '🌤️' },
            { value: 'blackout', label: 'Блэкаут', icon: '🌙' },
            { value: 'any', label: 'Любая', icon: '✨' }
        ]
    },
    {
        question: 'Предпочитаемый цвет?',
        filterKey: 'color',
        options: [
            { value: 'white', label: 'Белый', icon: '⚪' },
            { value: 'beige', label: 'Бежевый', icon: '🟤' },
            { value: 'gray', label: 'Серый', icon: '🩶' },
            { value: 'brown', label: 'Коричневый', icon: '🟫' },
            { value: 'green', label: 'Зеленый', icon: '🟢' },
            { value: 'blue', label: 'Синий', icon: '🔵' },
            { value: 'any', label: 'Любой', icon: '🌈' }
        ]
    },
    {
        question: 'Какой материал?',
        filterKey: 'material',
        options: [
            { value: 'fabric', label: 'Ткань', icon: '🧵' },
            { value: 'plastic', label: 'Пластик', icon: '🪟' },
            { value: 'bamboo', label: 'Бамбук', icon: '🎋' },
            { value: 'wood', label: 'Дерево', icon: '🪵' },
            { value: 'any', label: 'Любой', icon: '✨' }
        ]
    }
];

// --- Категории ---
const categories = [
    { id: 'all', name: 'Все товары' },
    { id: 'roller', name: 'Рулонные шторы' },
    { id: 'roman', name: 'Римские шторы' },
    { id: 'pleated', name: 'Плиссе' },
    { id: 'jalousie', name: 'Жалюзи' },
    { id: 'vertical', name: 'Вертикальные жалюзи' },
    { id: 'bamboo', name: 'Бамбуковые шторы' },
    { id: 'day-night', name: 'День-Ночь' }
];

// --- Опции фильтров ---
const filterOptions = {
    opacity: [
        { id: 'transparent', name: 'Прозрачные' },
        { id: 'dimout', name: 'Димаут' },
        { id: 'blackout', name: 'Блэкаут' }
    ],
    room: [
        { id: 'living', name: 'Гостиная' },
        { id: 'bedroom', name: 'Спальня' },
        { id: 'kitchen', name: 'Кухня' },
        { id: 'office', name: 'Кабинет' },
        { id: 'bathroom', name: 'Ванная' },
        { id: 'children', name: 'Детская' },
        { id: 'balcony', name: 'Балкон' }
    ],
    color: [
        { id: 'white', name: 'Белый', hex: '#FFFFFF' },
        { id: 'beige', name: 'Бежевый', hex: '#D4C4A8' },
        { id: 'gray', name: 'Серый', hex: '#808080' },
        { id: 'brown', name: 'Коричневый', hex: '#8B4513' },
        { id: 'black', name: 'Черный', hex: '#1a1a1a' },
        { id: 'green', name: 'Зеленый', hex: '#2E7D32' },
        { id: 'blue', name: 'Синий', hex: '#1565C0' },
        { id: 'pink', name: 'Розовый', hex: '#E91E63' },
        { id: 'terracotta', name: 'Терракот', hex: '#C75B39' },
        { id: 'olive', name: 'Оливковый', hex: '#808000' }
    ],
    material: [
        { id: 'fabric', name: 'Ткань' },
        { id: 'plastic', name: 'Пластик' },
        { id: 'bamboo', name: 'Бамбук' },
        { id: 'wood', name: 'Дерево' },
        { id: 'aluminum', name: 'Алюминий' }
    ]
};

// --- Логика квиза ---
const selectQuizOption = (step: number, value: string) => {
    const question = quizQuestions[step];
    if (value !== 'any') {
        if (question.filterKey === 'room') {
            filters.room = [value];
        } else if (question.filterKey === 'opacity') {
            filters.opacity = [value];
        } else if (question.filterKey === 'color') {
            filters.color = [value];
        }
    }
    
    if (currentStep.value < quizQuestions.length - 1) {
        currentStep.value++;
    } else {
        showQuiz.value = false;
    }
};

const skipQuiz = () => {
    showQuiz.value = false;
};

// --- Очистка фильтров ---
const clearFilters = () => {
    filters.search = '';
    filters.category = 'all';
    filters.opacity = [];
    filters.room = [];
    filters.color = [];
    filters.material = [];
};

// --- Проверка активных фильтров ---
const hasActiveFilters = computed(() => {
    return filters.search !== '' ||
           filters.category !== 'all' ||
           filters.opacity.length > 0 ||
           filters.room.length > 0 ||
           filters.color.length > 0 ||
           filters.material.length > 0;
});

// --- Товары ---
const products = ref<any[]>([]);

const fetchProducts = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/products', { 
            params: {
                search: filters.search,
                category: filters.category,
                opacity: filters.opacity,
                room: filters.room,
                color: filters.color,
                // priceRange: filters.priceRange
            }
        });
        products.value = response.data;
    } catch (e) {
        console.error('Failed to fetch products:', e);
    } finally {
        isLoading.value = false;
    }
};

// Auto-fetch on filter change with debounce
watchDebounced(
    filters,
    () => { fetchProducts(); },
    { deep: true, debounce: 300 }
);

// Initial fetch
onMounted(() => {
    fetchProducts();
});

// Вычисляемые отфильтрованные товары (Now just a pass-through as backend handles filtering)
const filteredProducts = computed(() => products.value);
</script>



<template>
    <Head title="Каталог" />
    <CustomCursor />

    <div class="min-h-screen bg-[#050505] text-white selection:bg-white selection:text-black overflow-x-hidden pt-24 font-sans">
        
        <!-- ОВЕРЛЕЙ КВИЗА -->
        <div v-if="showQuiz" class="quiz-overlay fixed inset-0 z-[60] bg-black flex items-center justify-center p-4">
            <!-- Фоновые эффекты -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-0 left-1/4 w-[500px] h-[500px] bg-white/5 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-white/5 rounded-full blur-[120px]"></div>
                <!-- Шум -->
                <div class="absolute inset-0 bg-[url('/images/noise.png')] opacity-10 mix-blend-overlay"></div>
            </div>

            <div class="max-w-4xl w-full relative z-10">
                <!-- Прогресс -->
                <div class="flex items-center gap-4 mb-16">
                    <span class="text-xs font-mono text-gray-500">STEP 0{{ currentStep + 1 }}</span>
                    <div class="flex-1 h-[1px] bg-white/10 relative">
                         <div class="absolute left-0 top-0 h-full bg-white transition-all duration-500" :style="{ width: ((currentStep + 1) / quizQuestions.length) * 100 + '%' }"></div>
                    </div>
                    <span class="text-xs font-mono text-gray-500">03</span>
                </div>

                <!-- Контент -->
                <div class="space-y-12">
                     <h2 class="text-5xl md:text-7xl font-bold tracking-tighter leading-none">
                        {{ quizQuestions[currentStep].question }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <button 
                            v-for="option in quizQuestions[currentStep].options" 
                            :key="option.value"
                            @click="selectQuizOption(currentStep, option.value)"
                            class="group relative h-48 rounded-2xl border border-white/10 hover:border-white transition-all duration-300 p-6 flex flex-col justify-between items-start text-left bg-white/5 hover:bg-white/10 cursor-hover overflow-hidden"
                        >
                            <span class="text-4xl filter grayscale group-hover:grayscale-0 transition-all duration-300">{{ option.icon }}</span>
                            <div class="relative z-10">
                                <span class="block text-lg font-bold mb-1">{{ option.label }}</span>
                                <span class="text-xs text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity translate-y-2 group-hover:translate-y-0 duration-300 block">Выбрать</span>
                            </div>
                            <!-- Легкое свечение -->
                            <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                        </button>
                    </div>
                </div>

                <!-- Футер действия -->
                <div class="mt-16 flex justify-between items-center border-t border-white/10 pt-8">
                     <button @click="skipQuiz" class="text-sm text-gray-500 hover:text-white transition-colors cursor-hover uppercase tracking-widest text-[10px]">
                        Пропустить опрос
                    </button>
                    <div class="text-gray-600 text-[10px] uppercase tracking-widest">
                        Интеллектуальный подбор
                    </div>
                </div>
            </div>
        </div>

        <!-- ОСНОВНОЙ МАКЕТ -->
        <div class="flex min-h-[calc(100vh-96px)]">
            
            <!-- БОКОВАЯ ПАНЕЛЬ ФИЛЬТРОВ -->
            <aside data-lenis-prevent class="w-80 hidden xl:block border-r border-white/5 bg-[#050505] p-8 pb-32 overflow-y-auto fixed h-full z-10 custom-scrollbar">
                <div class="space-y-12">
                    
                    <!-- Поиск -->
                    <div class="relative group">
                        <input 
                            v-model="filters.search"
                            type="text" 
                            placeholder="Поиск..." 
                            class="w-full bg-transparent border-b border-white/10 py-3 text-sm focus:border-white focus:outline-none transition-colors placeholder:text-gray-600"
                        >
                        <svg class="w-4 h-4 text-gray-600 absolute right-0 top-1/2 -translate-y-1/2 group-focus-within:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <!-- Категории -->
                    <div>
                        <h3 class="text-xs font-mono text-gray-500 mb-6 uppercase tracking-wider">Категории</h3>
                        <div class="space-y-1">
                            <button 
                                v-for="cat in categories" 
                                :key="cat.id"
                                @click="filters.category = cat.id"
                                class="w-full text-left py-2 px-3 -mx-3 rounded-lg text-sm transition-all cursor-hover flex items-center justify-between group"
                                :class="filters.category === cat.id ? 'bg-white text-black font-bold' : 'text-gray-400 hover:text-white'"
                            >
                                {{ cat.name }}
                                <span v-if="filters.category === cat.id" class="text-[10px]">●</span>
                            </button>
                        </div>
                    </div>

                    <!-- Фильтр: Прозрачность -->
                    <div>
                        <h3 class="text-xs font-mono text-gray-500 mb-6 uppercase tracking-wider">Светопроницаемость</h3>
                        <div class="space-y-3">
                            <label v-for="opt in filterOptions.opacity" :key="opt.id" class="flex items-center gap-3 cursor-hover group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :value="opt.id" v-model="filters.opacity" class="peer appearance-none w-5 h-5 border border-white/20 rounded-md checked:bg-white checked:border-white transition-colors cursor-pointer relative z-10">
                                    <svg class="w-3 h-3 text-black absolute top-1 left-1 opacity-0 peer-checked:opacity-100 pointer-events-none z-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-sm text-gray-400 group-hover:text-white transition-colors">{{ opt.name }}</span>
                            </label>
                        </div>
                    </div>

                     <!-- Фильтр: Комната -->
                    <div>
                        <h3 class="text-xs font-mono text-gray-500 mb-6 uppercase tracking-wider">Комната</h3>
                        <div class="space-y-3">
                            <label v-for="opt in filterOptions.room" :key="opt.id" class="flex items-center gap-3 cursor-hover group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :value="opt.id" v-model="filters.room" class="peer appearance-none w-5 h-5 border border-white/20 rounded-md checked:bg-white checked:border-white transition-colors cursor-pointer relative z-10">
                                    <svg class="w-3 h-3 text-black absolute top-1 left-1 opacity-0 peer-checked:opacity-100 pointer-events-none z-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-sm text-gray-400 group-hover:text-white transition-colors">{{ opt.name }}</span>
                            </label>
                        </div>
                    </div>

                     <!-- Фильтр: Цвет -->
                    <div>
                        <h3 class="text-xs font-mono text-gray-500 mb-6 uppercase tracking-wider">Цвет</h3>
                        <div class="grid grid-cols-5 gap-2">
                            <button 
                                v-for="opt in filterOptions.color" 
                                :key="opt.id"
                                @click="filters.color.includes(opt.id) ? filters.color = filters.color.filter(c => c !== opt.id) : filters.color.push(opt.id)"
                                class="w-full aspect-square rounded-full border border-white/10 hover:border-white transition-all relative flex items-center justify-center cursor-hover"
                                :style="{ backgroundColor: opt.hex }"
                                :title="opt.name"
                            >
                                <span v-if="filters.color.includes(opt.id)" class="w-2 h-2 bg-white rounded-full shadow-sm mix-blend-difference"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Фильтр: Материал -->
                    <div>
                        <h3 class="text-xs font-mono text-gray-500 mb-6 uppercase tracking-wider">Материал</h3>
                        <div class="space-y-3">
                            <label v-for="opt in filterOptions.material" :key="opt.id" class="flex items-center gap-3 cursor-hover group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :value="opt.id" v-model="filters.material" class="peer appearance-none w-5 h-5 border border-white/20 rounded-md checked:bg-white checked:border-white transition-colors cursor-pointer relative z-10">
                                    <svg class="w-3 h-3 text-black absolute top-1 left-1 opacity-0 peer-checked:opacity-100 pointer-events-none z-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-sm text-gray-400 group-hover:text-white transition-colors">{{ opt.name }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Кнопка очистки фильтров -->
                    <button 
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="w-full py-3 px-4 border border-white/20 rounded-xl text-sm text-gray-400 hover:text-white hover:border-white transition-all flex items-center justify-center gap-2 cursor-hover"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Сбросить фильтры
                    </button>

                </div>
            </aside>

            <!-- ОБЛАСТЬ СЕТКИ -->
            <main class="flex-1 xl:ml-80 p-8 md:p-12 relative z-0">
                
                <!-- ЗАГОЛОВОК -->
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6 border-b border-white/5 pb-8">
                    <div>
                        <h1 class="text-4xl font-bold mb-2 tracking-tight">Каталог</h1>
                        <p class="text-gray-500 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Найдено {{ filteredProducts.length }} товаров
                        </p>
                    </div>
                    
                    <div class="flex gap-4">
                         <button class="flex items-center gap-2 px-5 py-3 border border-white/10 rounded-full text-sm hover:bg-white hover:text-black transition-all cursor-hover">
                            <span>Сортировка</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                </div>
                
                <!-- СЕТКА -->
                 <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-x-6 gap-y-12">
                    <div 
                        v-for="product in products" 
                        :key="product.id"
                        class="group cursor-hover"
                    >
                        <!-- Изображение -->
                        <div class="aspect-[3/4] bg-[#141416] rounded-xl overflow-hidden relative mb-6">
                            <div class="absolute inset-0 bg-white/5 animate-pulse" v-if="!product.image"></div>
                            <!-- Градиент наложения изображения -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                            
                            <!-- Бейджи -->
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span v-if="product.opacity === 'blackout'" class="px-2 py-1 bg-black/50 backdrop-blur border border-white/10 rounded text-[10px] uppercase font-bold tracking-wider">Blackout</span>
                            </div>

                            <!-- Кнопки -->
                            <button class="absolute bottom-4 right-4 w-10 h-10 bg-white text-black rounded-full flex items-center justify-center opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 shadow-lg hover:scale-110">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>

                        <!-- Информация -->
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="text-lg font-medium group-hover:underline decoration-1 underline-offset-4">{{ product.name }}</h3>
                                <span class="font-bold">{{ product.price }}</span>
                            </div>
                            <p class="text-sm text-gray-500 capitalize">{{ product.room }} • {{ product.category }}</p>
                        </div>
                    </div>
                 </div>

                 <!-- Пустое состояние -->
                 <div v-else class="py-20 text-center">
                    <p class="text-2xl font-bold text-gray-600 mb-4">Ничего не найдено</p>
                    <button @click="Object.assign(filters, { opacity: [], room: [], color: [], category: 'all' })" class="text-white border-b border-white pb-1 hover:text-gray-300 transition-colors">Сбросить фильтры</button>
                 </div>

            </main>
        </div>
    </div>
</template>
