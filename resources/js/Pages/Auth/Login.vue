<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
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
    <Head title="Log in — Dev Command Center" />

    <div class="min-h-screen flex items-center justify-center p-4" style="background:#0a0a0f;">
        <!-- Background glow effect -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] rounded-full opacity-20 bg-brand-500 blur-[60px]"></div>
        </div>

        <div class="w-full max-w-sm relative z-10">
            <!-- Logo -->
            <div class="text-center mb-8 flex flex-col items-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-4 bg-white/5 border border-white/10 shadow-2xl backdrop-blur-md overflow-hidden relative group">
                    <div class="absolute inset-0 bg-gradient-to-tr from-brand-500/20 to-brand-700/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <img src="/logo.png" alt="Dev Command Center Logo" class="w-12 h-12 object-contain relative z-10 filter drop-shadow-[0_0_8px_rgba(255,255,255,0.3)] transition-transform duration-500 group-hover:scale-110" />
                </div>
                <h1 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 tracking-tight">Dev Command Center</h1>
                <p class="text-sm mt-2 font-medium" style="color:rgba(255,255,255,0.4);">Sign in to your admin workspace</p>
            </div>

            <!-- Card -->
            <div class="rounded-2xl p-7" style="background:#151520; border:1px solid rgba(255,255,255,0.07);">
                <div v-if="status" class="mb-4 text-sm px-3 py-2 rounded-lg" style="background:rgba(52,211,153,0.1); color:#34d399; border:1px solid rgba(52,211,153,0.2);">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-xs font-medium mb-1.5 text-gray-400">Email address</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required autofocus autocomplete="username"
                            class="w-full rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition-all bg-white/5 border border-white/10 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                            placeholder="you@example.com"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-400">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-medium text-gray-400">Password</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">
                                Forgot password?
                            </Link>
                        </div>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required autocomplete="current-password"
                            class="w-full rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition-all bg-white/5 border border-white/10 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-400">{{ form.errors.password }}</p>
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" v-model="form.remember" class="rounded border-gray-600 bg-gray-900 text-brand-500 focus:ring-brand-500" />
                        <span class="text-xs text-gray-400 group-hover:text-gray-300 transition-colors">Remember me for 30 days</span>
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-lg text-sm font-semibold text-white transition-all duration-300 bg-gradient-to-br from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 shadow-lg shadow-brand-500/20 hover:shadow-brand-500/40 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                    >
                        <span v-if="form.processing">Signing in...</span>
                        <span v-else>Sign in</span>
                    </button>
                </form>
            </div>

            <p class="text-center text-xs mt-5" style="color:rgba(255,255,255,0.35);">
                Don't have an account?
                <Link :href="route('register')" class="hover:text-white transition-colors" style="color:rgba(99,102,241,0.8);">Create account</Link>
            </p>
        </div>
    </div>
</template>
