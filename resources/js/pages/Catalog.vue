<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ref, reactive, computed } from 'vue';
import CustomCursor from '@/components/CustomCursor.vue';


// --- Состояние ---
const showQuiz = ref(true); 
const currentStep = ref(0);

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

// --- Логика Квиза (Функционал) ---
const quizQuestions = [
    {
        key: 'room',
        question: "Куда подбираем шторы?",
        options: [
            { label: "Спальня", value: "bedroom", icon: "🛏️" },
            { label: "Гостиная", value: "living", icon: "🛋️" },
            { label: "Кухня", value: "kitchen", icon: "🍳" },
            { label: "Детская", value: "kids", icon: "🧸" }
        ]
    },
    {
        key: 'opacity',
        question: "Насколько важна темнота?",
        options: [
            { label: "Полный мрак (Blackout)", value: "blackout", icon: "🌑" },
            { label: "Сильное затемнение (Dimout)", value: "dimout", icon: "🌘" },
            { label: "Мягкий свет", value: "light", icon: "🌥️" },
            { label: "Прозрачный тюль", value: "sheer", icon: "☀️" }
        ]
    },
    {
        key: 'style', // Просто для предпочтения, возможно влияет на Материал/Цвет
        question: "Предпочтительный стиль?",
        options: [
            { label: "Минимализм", value: "minimal", icon: "⬜" },
            { label: "Современный", value: "modern", icon: "🏙️" },
            { label: "Классика", value: "classic", icon: "🏛️" },
            { label: "Лофт", value: "loft", icon: "🧱" }
        ]
    }
];

const selectQuizOption = (questionIndex: number, value: string) => {
    const question = quizQuestions[questionIndex];
    
    // Авто-применение фильтра
    if (question.key === 'room') {
        if (!filters.room.includes(value)) filters.room.push(value);
    } else if (question.key === 'opacity') {
        if (!filters.opacity.includes(value)) filters.opacity.push(value);
    }
    
    // Следующий шаг
    if (currentStep.value < quizQuestions.length - 1) {
        currentStep.value++;
    } else {
        finishQuiz();
    }
};

const finishQuiz = () => {
    // Анимация выхода
    gsap.to('.quiz-overlay', { opacity: 0, duration: 0.5, onComplete: () => { showQuiz.value = false; } });
};

const skipQuiz = () => {
    gsap.to('.quiz-overlay', { opacity: 0, duration: 0.5, onComplete: () => { showQuiz.value = false; } });
};

// --- Моковые данные ---
const categories = [
    { id: 'all', name: 'Все категории' },
    { id: 'curtains', name: 'Портьеры' },
    { id: 'tulle', name: 'Тюль' },
    { id: 'roman', name: 'Римские' },
    { id: 'electro', name: 'Электрокарнизы' },
];

const filterOptions = {
    opacity: [
        { id: 'blackout', name: 'Blackout (100%)' },
        { id: 'dimout', name: 'Dimout (70-90%)' },
        { id: 'light', name: 'Светопроницаемые' },
    ],
    room: [
        { id: 'bedroom', name: 'Спальня' },
        { id: 'living', name: 'Гостиная' },
        { id: 'kitchen', name: 'Кухня' },
        { id: 'kids', name: 'Детская' },
    ],
    color: [
        { id: 'beige', name: 'Бежевый', hex: '#d6d3d1' },
        { id: 'grey', name: 'Серый', hex: '#52525b' },
        { id: 'black', name: 'Черный', hex: '#18181b' },
        { id: 'white', name: 'Белый', hex: '#ffffff' },
        { id: 'blue', name: 'Синий', hex: '#1e3a8a' },
    ]
};

const products = ref([
    { id: 1, name: 'Moonlight Silence', price: '15 900 ₽', category: 'curtains', opacity: 'blackout', room: 'bedroom', image: '/images/product-1.jpg' },
    { id: 2, name: 'Morning Breeze', price: '8 500 ₽', category: 'tulle', opacity: 'sheer', room: 'living', image: '/images/product-2.jpg' },
    { id: 3, name: 'Somfy Glydea Ultra', price: '28 000 ₽', category: 'electro', opacity: 'n/a', room: 'living', image: '/images/product-motor.jpg' },
    { id: 4, name: 'Velvet Touch', price: '22 000 ₽', category: 'curtains', opacity: 'dimout', room: 'living', image: '/images/product-3.jpg' },
    { id: 5, name: 'Linen Eco', price: '12 400 ₽', category: 'curtains', opacity: 'light', room: 'kitchen', image: '/images/product-4.jpg' },
    { id: 6, name: 'Kids Dream', price: '9 900 ₽', category: 'curtains', opacity: 'dimout', room: 'kids', image: '/images/product-1.jpg' },
]);

// Вычисляемые отфильтрованные товары
const filteredProducts = computed(() => {
    return products.value.filter(p => {
        if (filters.category !== 'all' && p.category !== filters.category) return false;
        if (filters.opacity.length && !filters.opacity.includes(p.opacity)) return false;
        if (filters.room.length && !filters.room.includes(p.room)) return false;
        // Заглушка логики поиска
        return true;
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
