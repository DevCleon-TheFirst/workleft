<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Student Portal Login" />

    <div class="min-h-screen bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
        
        <!-- Background Accents -->
        <div class="absolute top-0 inset-x-0 h-64 bg-brand-900/20 backdrop-blur-[100px] rounded-b-[100%]"></div>
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-brand-600/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-brand-600/10 rounded-full blur-3xl"></div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="flex justify-center mb-6">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-indigo-500 rounded-2xl blur opacity-25 group-hover:opacity-75 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative w-20 h-20 bg-gray-900 border border-gray-700/50 rounded-2xl flex items-center justify-center shadow-2xl backdrop-blur-sm overflow-hidden">
                        <img src="/logo.png" alt="Student Portal Logo" class="w-12 h-12 object-contain filter drop-shadow-[0_0_8px_rgba(99,102,241,0.5)] transition-transform duration-500 group-hover:scale-110" />
                    </div>
                </div>
            </div>
            <h2 class="text-center text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 tracking-tight">Student Portal</h2>
            <p class="mt-2 text-center text-sm font-medium text-gray-400">
                Log in to access your assignments and study materials
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-gray-800 py-8 px-4 shadow sm:rounded-2xl sm:px-10 border border-gray-700">
                
                <div v-if="status" class="mb-4 font-medium text-sm text-green-400 bg-green-400/10 p-3 rounded-lg border border-green-400/20">
                    {{ status }}
                </div>

                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300">
                            Email address
                        </label>
                        <div class="mt-1">
                            <input id="email" v-model="form.email" type="email" autocomplete="email" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-xl shadow-sm bg-gray-900 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-colors"
                                :class="{'border-red-500': form.errors.email}"
                                placeholder="student@example.com" />
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-400">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300">
                            Password
                        </label>
                        <div class="mt-1">
                            <input id="password" v-model="form.password" type="password" autocomplete="current-password" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-xl shadow-sm bg-gray-900 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-colors"
                                :class="{'border-red-500': form.errors.password}"
                                placeholder="••••••••" />
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-400">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" v-model="form.remember" type="checkbox"
                                class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-600 bg-gray-900 rounded" />
                            <label for="remember" class="ml-2 block text-sm text-gray-400">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 focus:ring-offset-gray-900 transition-colors disabled:opacity-50">
                            Sign in to Classroom
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-gray-800 text-gray-500">
                                Need help?
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 text-center text-sm text-gray-400">
                        Ask your instructor for your login credentials if you haven't received them.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
