<script setup lang="ts">
import { ref, reactive } from 'vue';
import axios from 'axios';

defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

// Состояние формы
const form = reactive({
    phone: '',
    password: '',
    remember: false
});

const isLoading = ref(false);
const error = ref('');

// Вход по телефону + пароль
const submitLogin = async () => {
    isLoading.value = true;
    error.value = '';

    try {
        const response = await axios.post('/api/auth/login', {
            phone: form.phone,
            password: form.password,
            remember: form.remember
        });

        if (response.data.success) {
            // Перезагружаем страницу для обновления состояния авторизации
            window.location.reload();
        }
    } catch (e: any) {
        if (e.response?.status === 422) {
            error.value = e.response.data.errors?.phone?.[0] || 'Неверные данные';
        } else {
            error.value = 'Ошибка сервера. Попробуйте позже.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="emit('close')"></div>

            <!-- Modal Content -->
            <div class="relative w-full max-w-md bg-[#0f0f11] border border-white/10 rounded-3xl p-8 shadow-2xl overflow-hidden">
                <!-- Noise & Glow -->
                <div class="absolute inset-0 pointer-events-none">
                     <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-900/20 rounded-full blur-[80px]"></div>
                     <div class="absolute inset-0 bg-[url('/images/noise.png')] opacity-5 mix-blend-overlay"></div>
                </div>

                <div class="relative z-10">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-black tracking-tighter mb-2">РОСКАРНИЗ</h2>
                        <p class="text-gray-400 text-sm">Вход в личный кабинет</p>
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 text-sm">
                        {{ error }}
                    </div>

                    <!-- LOGIN FORM -->
                    <form @submit.prevent="submitLogin" class="space-y-4">
                        <div>
                            <input 
                                v-model="form.phone"
                                type="tel" 
                                placeholder="Номер телефона (+79...)"
                                class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 focus:border-white focus:outline-none transition-colors text-white placeholder:text-gray-600"
                            >
                        </div>
                        <div>
                            <input 
                                v-model="form.password"
                                type="password" 
                                placeholder="Пароль"
                                class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 focus:border-white focus:outline-none transition-colors text-white placeholder:text-gray-600"
                            >
                        </div>
                        
                        <div class="flex items-center justify-between text-xs text-gray-500 pt-2">
                             <label class="flex items-center gap-2 cursor-pointer hover:text-white transition-colors">
                                <input type="checkbox" v-model="form.remember" class="accent-white rounded border-white/20 bg-transparent">
                                <span>Запомнить меня</span>
                            </label>
                        </div>

                        <button 
                            :disabled="isLoading"
                            class="w-full py-4 bg-white text-black font-bold uppercase tracking-widest rounded-xl hover:bg-gray-200 transition-colors mt-4 disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <svg v-if="isLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Войти</span>
                        </button>
                    </form>

                    <!-- Telegram Registration -->
                    <div class="mt-8 pt-6 border-t border-white/10">
                        <p class="text-center text-gray-500 text-sm mb-4">Нет аккаунта?</p>
                        <a 
                            href="https://t.me/RoskarnizBot" 
                            target="_blank"
                            class="w-full py-4 bg-[#0088cc] text-white font-bold uppercase tracking-widest rounded-xl hover:bg-[#006699] transition-colors flex items-center justify-center gap-3"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                            Регистрация через Telegram
                        </a>
                        <p class="text-center text-gray-600 text-xs mt-3">
                            Напишите боту, чтобы создать аккаунт
                        </p>
                    </div>

                    <div class="mt-6 text-center">
                         <button @click="emit('close')" class="text-xs text-gray-600 uppercase tracking-widest hover:text-white transition-colors">
                            Закрыть
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </Transition>
</template>
