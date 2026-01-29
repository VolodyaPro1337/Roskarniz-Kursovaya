<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

// Auth User
const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Admin', status: 'Staff' });

// Orders State
const orders = ref<any[]>([]);
const isLoading = ref(true);

const fetchOrders = async () => {
    isLoading.value = true;
    try {
        const response = await fetch('/api/admin/orders', {
            headers: {
                'Accept': 'application/json',
                // Add Authorization header if needed, but cookies should handle it for Sanctum/Session
            }
        });
        if (response.ok) {
            const data = await response.json();
            orders.value = data.data; // Assuming pagination structure
        }
    } catch (e) {
        console.error('Failed to fetch orders', e);
    } finally {
        isLoading.value = false;
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const selectedOrder = ref<any>(null);

const openOrderModal = (order: any) => {
    selectedOrder.value = JSON.parse(JSON.stringify(order)); // Deep copy to avoid mutating table directly
};

const closeOrderModal = () => {
    selectedOrder.value = null;
};

const updateStatus = async (id: number, status: string) => {
    try {
        const response = await fetch(`/api/admin/orders/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status })
        });
        
        if (response.ok) {
            // Update local list
            const idx = orders.value.findIndex(o => o.id === id);
            if (idx !== -1) {
                orders.value[idx].status = status;
            }
        } else {
            console.error('Failed to update status');
        }
    } catch (e) {
        console.error('Error updating status', e);
    }
};

onMounted(() => {
    fetchOrders();
});
</script>

<script lang="ts">
export default {
    layout: MainLayout
}
</script>

<template>
    <Head title="Панель администратора" />

    <div class="pt-32 px-4 md:px-12 min-h-screen bg-[#050505] text-white font-sans pb-20">
        
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 border-b border-white/10 pb-8">
                <div>
                    <h1 class="text-4xl md:text-5xl font-black mb-2 tracking-tight">
                        Admin Dashboard
                    </h1>
                    <p class="text-gray-400">Управление заказами и заявками</p>
                </div>

                <div class="flex gap-4 mt-8 md:mt-0">
                    <div class="text-right">
                        <div class="text-sm font-bold">{{ user.name }}</div>
                        <div class="text-xs text-green-500"> Online</div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-[#0a0a0a] border border-white/10 rounded-3xl overflow-hidden">
                <div class="p-6 border-b border-white/10 flex justify-between items-center">
                    <h2 class="text-lg font-bold">Последние заказы</h2>
                    <button @click="fetchOrders" class="text-xs text-gray-500 hover:text-white transition-colors">
                        Обновить
                    </button>
                </div>

                <div v-if="isLoading" class="p-12 text-center text-gray-500">
                    Загрузка...
                </div>

                <div v-else-if="orders.length === 0" class="p-12 text-center text-gray-500">
                    Заказов пока нет.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/5 text-gray-400 font-mono text-xs uppercase">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Клиент</th>
                                <th class="px-6 py-4">Состав</th>
                                <th class="px-6 py-4">Сумма</th>
                                <th class="px-6 py-4">Статус</th>
                                <th class="px-6 py-4">Дата</th>
                                <th class="px-6 py-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="order in orders" :key="order.id" class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 font-mono text-gray-500">#{{ order.id }}</td>
                                <td class="px-6 py-4">
                                    <div v-if="order.user_id">ID: {{ order.user_id }}</div>
                                    <div v-else class="text-gray-400">Гость</div>
                                    <div v-if="order.guest_info" class="text-xs text-gray-500 mt-1">
                                        {{ order.guest_info.phone || order.guest_info.email || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="mat in order.materials" :key="mat.id" 
                                              class="px-2 py-1 bg-white/10 rounded text-xs whitespace-nowrap">
                                            {{ mat.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold">{{ Number(order.total_price).toLocaleString('ru-RU') }} ₽</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase"
                                          :class="{
                                              'bg-blue-500/20 text-blue-500': order.status === 'new',
                                              'bg-yellow-500/20 text-yellow-500': order.status === 'pending',
                                              'bg-green-500/20 text-green-500': order.status === 'paid',
                                              'bg-gray-500/20 text-gray-500': order.status === 'completed'
                                          }">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <button @click="openOrderModal(order)" class="text-xs border border-white/20 px-3 py-1 rounded hover:bg-white hover:text-black transition-colors">
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Order Details Modal -->
        <div v-if="selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closeOrderModal"></div>
            <div class="relative bg-[#111] border border-white/10 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-8 shadow-2xl">
                <button @click="closeOrderModal" class="absolute top-4 right-4 text-gray-500 hover:text-white">
                    ✕
                </button>

                <h2 class="text-2xl font-bold mb-6">Заказ #{{ selectedOrder.id }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-gray-500 text-xs uppercase mb-2">Клиент</h3>
                        <div class="bg-white/5 p-4 rounded-xl space-y-2 text-sm">
                            <div v-if="selectedOrder.guest_info?.name">Name: {{ selectedOrder.guest_info.name }}</div>
                            <div>Phone: {{ selectedOrder.guest_info?.phone || '-' }}</div>
                            <div>Email: {{ selectedOrder.guest_info?.email || '-' }}</div>
                            <div v-if="selectedOrder.guest_info?.address">Address: {{ selectedOrder.guest_info.address }}</div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-xs uppercase mb-2">Статус</h3>
                        <div class="bg-white/5 p-4 rounded-xl flex gap-2">
                             <select v-model="selectedOrder.status" @change="updateStatus(selectedOrder.id, selectedOrder.status)"
                                class="bg-black border border-white/20 text-white text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5">
                                <option value="new">New</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                     <h3 class="text-gray-500 text-xs uppercase mb-2">Материалы</h3>
                     <div class="bg-white/5 rounded-xl overflow-hidden">
                        <div v-for="item in selectedOrder.materials" :key="item.id" class="p-4 border-b border-white/5 last:border-0 flex items-center gap-4">
                            <div v-if="item.image_url" class="w-12 h-12 bg-gray-800 rounded bg-cover bg-center" :style="{ backgroundImage: `url(${item.image_url})` }"></div>
                            <div class="flex-1">
                                <div class="font-bold text-sm">{{ item.name }}</div>
                                <div class="text-xs text-gray-400 capitalize">{{ item.category }}</div>
                            </div>
                            <div class="text-sm">{{ Number(item.price).toLocaleString('ru-RU') }} ₽</div>
                        </div>
                     </div>
                </div>
                
                <div class="flex justify-between items-center border-t border-white/10 pt-6">
                    <div class="text-gray-400 text-sm">Итоговая сумма</div>
                    <div class="text-2xl font-bold">{{ Number(selectedOrder.total_price).toLocaleString('ru-RU') }} ₽</div>
                </div>

            </div>
        </div>
    </div>
</template>
