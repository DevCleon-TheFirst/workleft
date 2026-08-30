<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const inputStyle = {
    background: 'rgba(255,255,255,0.05)',
    border: '1px solid rgba(255,255,255,0.1)',
};
</script>

<template>
    <Head title="Create Account — Dev Command Center" />

    <div class="min-h-screen flex items-center justify-center p-4" style="background:#0a0a0f;">
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] rounded-full opacity-20" style="background:radial-gradient(ellipse, #8b5cf6, transparent 70%); filter:blur(60px);"></div>
        </div>

        <div class="w-full max-w-sm relative z-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-7 h-7">
                        <path fill-rule="evenodd" d="M14.447 3.026a.75.75 0 0 1 .527.921l-4.5 16.5a.75.75 0 0 1-1.448-.394l4.5-16.5a.75.75 0 0 1 .921-.527ZM16.72 6.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L21.44 12l-4.72-4.72a.75.75 0 0 1 0-1.06Zm-9.44 0a.75.75 0 0 1 0 1.06L2.56 12l4.72 4.72a.75.75 0 1 1-1.06 1.06L.97 12.53a.75.75 0 0 1 0-1.06l5.25-5.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-white">Create your workspace</h1>
                <p class="text-sm mt-1" style="color:rgba(255,255,255,0.4);">Start your Dev Command Center journey</p>
            </div>

            <div class="rounded-2xl p-7" style="background:#151520; border:1px solid rgba(255,255,255,0.07);">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:rgba(255,255,255,0.6);">Full name</label>
                        <input id="name" type="text" v-model="form.name" required autofocus autocomplete="name"
                            class="w-full rounded-lg px-3.5 py-2.5 text-sm text-white outline-none transition-all"
                            style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);"
                            placeholder="John Doe"
                            @focus="$event.target.style.borderColor='rgba(99,102,241,0.5)'"
                            @blur="$event.target.style.borderColor='rgba(255,255,255,0.1)'" />
                        <p v-if="form.errors.name" class="mt-1.5 text-xs" style="color:#f87171;">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:rgba(255,255,255,0.6);">Email address</label>
                        <input id="email" type="email" v-model="form.email" required autocomplete="username"
                            class="w-full rounded-lg px-3.5 py-2.5 text-sm text-white outline-none transition-all"
                            style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);"
                            placeholder="you@example.com"
                            @focus="$event.target.style.borderColor='rgba(99,102,241,0.5)'"
                            @blur="$event.target.style.borderColor='rgba(255,255,255,0.1)'" />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs" style="color:#f87171;">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:rgba(255,255,255,0.6);">Password</label>
                        <input id="password" type="password" v-model="form.password" required autocomplete="new-password"
                            class="w-full rounded-lg px-3.5 py-2.5 text-sm text-white outline-none transition-all"
                            style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);"
                            placeholder="Min. 8 characters"
                            @focus="$event.target.style.borderColor='rgba(99,102,241,0.5)'"
                            @blur="$event.target.style.borderColor='rgba(255,255,255,0.1)'" />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs" style="color:#f87171;">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:rgba(255,255,255,0.6);">Confirm password</label>
                        <input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password"
                            class="w-full rounded-lg px-3.5 py-2.5 text-sm text-white outline-none transition-all"
                            style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);"
                            placeholder="••••••••"
                            @focus="$event.target.style.borderColor='rgba(99,102,241,0.5)'"
                            @blur="$event.target.style.borderColor='rgba(255,255,255,0.1)'" />
                        <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs" style="color:#f87171;">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full py-2.5 rounded-lg text-sm font-semibold text-white mt-2 transition-all duration-200"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"
                        :style="form.processing ? 'opacity:0.6;cursor:not-allowed;' : 'cursor:pointer;'"
                        onmouseover="if(!this.disabled){this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 24px rgba(99,102,241,0.4)'}"
                        onmouseout="this.style.transform='';this.style.boxShadow=''">
                        <span v-if="form.processing">Creating workspace...</span>
                        <span v-else>Create Workspace</span>
                    </button>
                </form>
            </div>

            <p class="text-center text-xs mt-5" style="color:rgba(255,255,255,0.35);">
                Already have an account?
                <Link :href="route('login')" class="hover:text-white transition-colors" style="color:rgba(99,102,241,0.8);">Sign in</Link>
            </p>
        </div>
    </div>
</template>
