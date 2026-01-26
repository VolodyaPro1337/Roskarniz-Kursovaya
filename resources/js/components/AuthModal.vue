<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const mode = ref<'login' | 'register'>('login');

const formLogin = useForm({
    email: '',
    password: '',
    remember: false
});

const formRegister = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const submitLogin = () => {
    formLogin.post(route('login'), {
        onFinish: () => formLogin.reset('password'),
        onSuccess: () => emit('close')
    });
};

const submitRegister = () => {
    formRegister.post(route('register'), {
        onFinish: () => formRegister.reset('password', 'password_confirmation'),
        onSuccess: () => emit('close')
    });
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
                     <div class="absolute -top-20 -right-20 w-64 h-64 bg-red-900/20 rounded-full blur-[80px]"></div>
                     <div class="absolute inset-0 bg-[url('/images/noise.png')] opacity-5 mix-blend-overlay"></div>
                </div>

                <div class="relative z-10">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-black tracking-tighter mb-2">ROSKARNIZ</h2>
                        <p class="text-gray-400 text-sm">Welcome to the Club</p>
                    </div>

                    <!-- Toggles -->
                    <div class="flex p-1 bg-white/5 rounded-xl mb-8 border border-white/5">
                        <button 
                            @click="mode = 'login'"
                            class="flex-1 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all"
                            :class="mode === 'login' ? 'bg-white text-black shadow-lg' : 'text-gray-500 hover:text-white'"
                        >
                            Вход
                        </button>
                        <button 
                            @click="mode = 'register'"
                            class="flex-1 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all"
                            :class="mode === 'register' ? 'bg-white text-black shadow-lg' : 'text-gray-500 hover:text-white'"
                        >
                            Регистрация
                        </button>
                    </div>

                    <!-- LOGIN FORM -->
                    <form v-if="mode === 'login'" @submit.prevent="submitLogin" class="space-y-4">
                        <div>
                            <input 
                                v-model="formLogin.email"
                                type="email" 
                                placeholder="Email"
                                class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 focus:border-red-500 focus:outline-none transition-colors text-white placeholder:text-gray-600"
                            >
                            <div v-if="formLogin.errors.email" class="text-red-500 text-xs mt-1">{{ formLogin.errors.email }}</div>
                        </div>
                        <div>
                            <input 
                                v-model="formLogin.password"
                                type="password" 
                                placeholder="Пароль"
                                class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 focus:border-red-500 focus:outline-none transition-colors text-white placeholder:text-gray-600"
                            >
                             <div v-if="formLogin.errors.password" class="text-red-500 text-xs mt-1">{{ formLogin.errors.password }}</div>
                        </div>
                        
                        <div class="flex items-center justify-between text-xs text-gray-500 pt-2">
                             <label class="flex items-center gap-2 cursor-pointer hover:text-white transition-colors">
                                <input type="checkbox" v-model="formLogin.remember" class="accent-red-500 rounded border-white/20 bg-transparent">
                                <span>Запомнить меня</span>
                            </label>
                            <a href="#" class="hover:text-white transition-colors">Забыли пароль?</a>
                        </div>

                        <button 
                            :disabled="formLogin.processing"
                            class="w-full py-4 bg-white text-black font-bold uppercase tracking-widest rounded-xl hover:bg-gray-200 transition-colors mt-4 disabled:opacity-50"
                        >
                            Войти
                        </button>
                    </form>

                    <!-- REGISTER FORM -->
                    <form v-else @submit.prevent="submitRegister" class="space-y-4">
                        <div>
                            <input 
                                v-model="formRegister.name"
                                type="text" 
                                placeholder="Имя"
                                class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 focus:border-red-500 focus:outline-none transition-colors text-white placeholder:text-gray-600"
                            >
                            <div v-if="formRegister.errors.name" class="text-red-500 text-xs mt-1">{{ formRegister.errors.name }}</div>
                        </div>
                        <div>
                            <input 
                                v-model="formRegister.email"
                                type="email" 
                                placeholder="Email"
                                class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 focus:border-red-500 focus:outline-none transition-colors text-white placeholder:text-gray-600"
                            >
                            <div v-if="formRegister.errors.email" class="text-red-500 text-xs mt-1">{{ formRegister.errors.email }}</div>
                        </div>
                        <div>
                            <input 
                                v-model="formRegister.password"
                                type="password" 
                                placeholder="Пароль"
                                class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 focus:border-red-500 focus:outline-none transition-colors text-white placeholder:text-gray-600"
                            >
                            <div v-if="formRegister.errors.password" class="text-red-500 text-xs mt-1">{{ formRegister.errors.password }}</div>
                        </div>
                         <div>
                            <input 
                                v-model="formRegister.password_confirmation"
                                type="password" 
                                placeholder="Повторите пароль"
                                class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 focus:border-red-500 focus:outline-none transition-colors text-white placeholder:text-gray-600"
                            >
                        </div>

                        <button 
                            :disabled="formRegister.processing"
                            class="w-full py-4 bg-red-600 text-white font-bold uppercase tracking-widest rounded-xl hover:bg-red-500 transition-colors mt-4 shadow-lg shadow-red-900/30 disabled:opacity-50"
                        >
                            Создать аккаунт
                        </button>
                    </form>

                    <div class="mt-8 text-center">
                         <button @click="emit('close')" class="text-xs text-gray-600 uppercase tracking-widest hover:text-white transition-colors">
                            Закрыть
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </Transition>
</template>
