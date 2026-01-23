<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Navigation from '@/Components/Navigation.vue';
import CustomCursor from '@/Components/CustomCursor.vue';

// --- State ---
const showQuiz = ref(true); // Показывать ли квиз при входе
const currentStep = ref(0);

// --- Quiz Logic ---
const quizQuestions = [
    {
        question: "Для какой комнаты ищете шторы?",
        options: ["Спальня", "Гостиная", "Кухня", "Детская", "Офис/Кабинет"]
    },
    {
        question: "Важна ли полная темнота (Blackout)?",
        options: ["Да, полная темнота", "Нет, нужен рассеянный свет", "Не принципиально"]
    },
    {
        question: "Какой стиль интерьера?",
        options: ["Минимализм", "Классика", "Лофт", "Скандинавский", "Хай-тек"]
    }
];

const answers = ref<string[]>([]);

const selectOption = (option: string) => {
    answers.value[currentStep.value] = option;
    if (currentStep.value < quizQuestions.length - 1) {
        currentStep.value++;
    } else {
        finishQuiz();
    }
};

const finishQuiz = () => {
    // Здесь будет логика фильтрации на основе ответов
    console.log("Answers:", answers.value);
    showQuiz.value = false;
};

const skipQuiz = () => {
    showQuiz.value = false;
};

// --- Catalog Data (Mock) ---
const categories = [
    { id: 'all', name: 'Все товары', icon: 'Squares2X2Icon' },
    { id: 'curtains', name: 'Шторы', icon: 'CurtainIcon' },
    { id: 'cornices', name: 'Карнизы', icon: 'Bars3Icon' },
    { id: 'fabrics', name: 'Ткани', icon: 'SwatchIcon' },
    { id: 'accessories', name: 'Аксессуары', icon: 'TagIcon' },
];

const activeCategory = ref('all');

const products = ref([
    { id: 1, name: 'Blackout Eclipse', price: '15 000 ₽', image: '/images/product-1.jpg', category: 'curtains' },
    { id: 2, name: 'Soft Linen Beige', price: '12 000 ₽', image: '/images/product-2.jpg', category: 'curtains' },
    { id: 3, name: 'Smart Motor Somfy', price: '25 000 ₽', image: '/images/product-motor.jpg', category: 'cornices' },
    { id: 4, name: 'Velvet Royal Red', price: '18 500 ₽', image: '/images/product-3.jpg', category: 'curtains' },
    { id: 5, name: 'Sheer Silk White', price: '8 000 ₽', image: '/images/product-4.jpg', category: 'fabrics' },
    { id: 6, name: 'Aluminum Track', price: '4 500 ₽', image: '/images/product-track.jpg', category: 'cornices' },
    { id: 7, name: 'Blackout Eclipse', price: '15 000 ₽', image: '/images/product-1.jpg', category: 'curtains' }, // Duplicate for grid demo
    { id: 8, name: 'Soft Linen Beige', price: '12 000 ₽', image: '/images/product-2.jpg', category: 'curtains' },
]);

</script>

<template>
    <Head title="Каталог" />
    <Navigation />
    <CustomCursor />

    <div class="min-h-screen bg-[#0f0f11] text-white selection:bg-white selection:text-black overflow-x-hidden pt-20">
        
        <!-- QUIZ OVERLAY -->
        <transition name="fade">
            <div v-if="showQuiz" class="fixed inset-0 z-40 bg-black/95 backdrop-blur-xl flex items-center justify-center p-4">
                <div class="max-w-2xl w-full text-center">
                    <!-- Progress -->
                    <div class="flex justify-center gap-2 mb-12">
                        <div 
                            v-for="(q, index) in quizQuestions" 
                            :key="index"
                            class="h-1 w-16 rounded-full transition-all duration-500"
                            :class="index <= currentStep ? 'bg-white' : 'bg-white/20'"
                        ></div>
                    </div>

                    <!-- Question -->
                    <h2 class="text-4xl md:text-5xl font-bold mb-12 tracking-tight">
                        {{ quizQuestions[currentStep].question }}
                    </h2>

                    <!-- Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12">
                        <button 
                            v-for="option in quizQuestions[currentStep].options" 
                            :key="option"
                            @click="selectOption(option)"
                            class="p-6 rounded-2xl border border-white/10 hover:bg-white hover:text-black transition-all duration-300 text-lg font-medium group text-left flex justify-between items-center cursor-hover"
                        >
                            <span>{{ option }}</span>
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                        </button>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-center">
                        <button @click="skipQuiz" class="text-white/40 hover:text-white text-sm uppercase tracking-widest cursor-hover transition-colors">
                            Пропустить и открыть каталог
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- MAIN CATALOG LAYOUT -->
        <div class="flex h-[calc(100vh-80px)]">
            
            <!-- Sidebar (Left) -->
            <aside class="w-64 hidden lg:flex flex-col border-r border-white/5 bg-[#0f0f11] p-6 gap-8">
                <!-- Search -->
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="Поиск..." 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 pl-10 text-sm focus:outline-none focus:border-white/30 transition-colors"
                    >
                    <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Navigation -->
                <nav class="space-y-2">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="activeCategory = cat.id"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all cursor-hover text-left"
                        :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-gray-400 hover:bg-white/5 hover:text-white'"
                    >
                        <!-- Icon Placeholder (Simple shapes) -->
                        <div class="w-5 h-5 bg-current rounded-sm opacity-50"></div>
                        <span class="font-medium">{{ cat.name }}</span>
                    </button>
                </nav>

                <!-- Filters Placeholder -->
                <div class="mt-auto">
                    <h3 class="text-xs uppercase tracking-widest text-gray-600 mb-4">Фильтры</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-sm text-gray-400">
                            <span>Цена</span>
                            <span>Slider</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-400">
                            <span>Наличие</span>
                            <span>Switch</span>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Grid Content (Right) -->
            <main class="flex-1 overflow-y-auto p-6 md:p-10 custom-scrollbar">
                
                <!-- Sort / Breadcrumbs Toolbar -->
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Каталог <span class="text-gray-500 text-sm ml-2">{{ products.length }} товаров</span></h1>
                    <div class="flex gap-4">
                        <!-- Sort Dropdown Mock -->
                        <button class="flex items-center gap-2 px-4 py-2 bg-white/5 rounded-lg text-sm text-gray-300 hover:bg-white/10 cursor-hover">
                            <span>По популярности</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                         <button class="bg-blue-600 px-6 py-2 rounded-lg text-sm font-bold shadow-lg shadow-blue-900/20 hover:bg-blue-500 cursor-hover">
                            + Создать (Demo)
                        </button>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div 
                        v-for="product in products" 
                        :key="product.id"
                        class="group relative bg-[#141416] rounded-2xl p-4 border border-white/5 hover:border-white/20 transition-all duration-300 hover:-translate-y-1"
                    >
                        <!-- Image Area -->
                        <div class="aspect-square bg-[#0f0f11] rounded-xl mb-4 overflow-hidden relative">
                             <!-- Placeholder Image Gradient if no image -->
                             <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-black"></div>
                             
                             <!-- Action Buttons (Hover) -->
                             <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col gap-2">
                                <button class="w-8 h-8 rounded-full bg-white/10 backdrop-blur flex items-center justify-center hover:bg-white hover:text-black cursor-hover text-white">
                                    ♥
                                </button>
                             </div>
                        </div>

                        <!-- Info -->
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-lg leading-tight group-hover:text-blue-400 transition-colors">{{ product.name }}</h3>
                                <!-- Category Badge -->
                                <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5"></div>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">{{ product.category }}</p>
                            
                            <div class="flex items-center justify-between border-t border-white/5 pt-4">
                                <div>
                                    <span class="text-xs text-gray-500 block">Цена от</span>
                                    <span class="font-mono text-lg">{{ product.price }}</span>
                                </div>
                                <button class="px-3 py-1.5 text-xs bg-white/5 rounded-lg hover:bg-white hover:text-black transition-colors cursor-hover">
                                    В корзину
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>

    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.2);
}
</style>
