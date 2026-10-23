<script setup>
import { ref, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => ['autor', 'administrador'],
    },
    defaultRole: {
        type: String,
        default: 'autor',
    },
    user: {
        type: Object,
        default: null,
    },
    entityLabel: {
        type: String,
        default: 'Usuario',
    },
});

const showPassword = ref(false);
const copied = ref(false);

const hasExistingUser = computed(() => {
    return Boolean(props.user && props.user.id);
});

const generatePassword = () => {
    const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lowercase = 'abcdefghijkmnopqrstuvwxyz';
    const numbers = '23456789';
    const symbols = '!@#$%&*';
    const all = uppercase + lowercase + numbers + symbols;

    let pass = '';
    pass += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
    pass += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
    pass += numbers.charAt(Math.floor(Math.random() * numbers.length));
    pass += symbols.charAt(Math.floor(Math.random() * symbols.length));

    for (let i = 4; i < 12; i++) {
        pass += all.charAt(Math.floor(Math.random() * all.length));
    }

    // Shuffle characters
    props.form.password = pass.split('').sort(() => 0.5 - Math.random()).join('');
    showPassword.value = true;
};

const copyPassword = async () => {
    if (!props.form.password) return;
    try {
        await navigator.clipboard.writeText(props.form.password);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        // Fallback if clipboard API not available
    }
};

const roleLabels = {
    administrador: 'Administrador (Acceso total al sistema)',
    autor: 'Autor (Envío y seguimiento de manuscritos)',
};

const getRoleDescription = (role) => {
    return roleLabels[role] || `Rol: ${role}`;
};
</script>

<template>
    <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs space-y-5 transition-all">
        <!-- Encabezado de la Sección -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="size-11 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[#35449F] shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-[17px] font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <span>Credenciales y Acceso al Sistema</span>
                        <span
                            v-if="hasExistingUser"
                            class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60"
                        >
                            Cuenta activa vinculada
                        </span>
                        <span
                            v-else-if="form.crear_usuario"
                            class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-[#35449F] border border-indigo-200/60"
                        >
                            Alta directa
                        </span>
                    </h2>
                    <p class="text-[13.5px] text-gray-500 font-medium mt-0.5">
                        {{ hasExistingUser
                            ? 'Gestiona la contraseña, rol y estado de la cuenta de acceso vinculada.'
                            : 'Elige si deseas crear una cuenta de acceso al sistema para este registro en este mismo paso.' }}
                    </p>
                </div>
            </div>

            <!-- Switch / Toggle de Dar de Alta (si no tiene usuario existente) -->
            <div v-if="!hasExistingUser" class="flex items-center gap-3 bg-slate-50 border border-slate-200/80 rounded-xl p-2 sm:px-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="form.crear_usuario"
                        class="sr-only peer"
                    />
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#35449F]"></div>
                </label>
                <div class="text-xs">
                    <span class="font-bold text-gray-900 block">
                        {{ form.crear_usuario ? '¿Crear cuenta? Sí' : '¿Crear cuenta? No' }}
                    </span>
                    <span class="text-gray-500 text-[11px]">
                        {{ form.crear_usuario ? 'Se habilitará acceso al sistema' : 'Solo ficha en el sistema' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- CASO 1: CUENTA EXISTENTE VINCULADA (Modo Edición con usuario previo) -->
        <div v-if="hasExistingUser" class="space-y-4">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5">
                    <div class="size-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-600 font-bold">
                        @
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-[13px]">{{ user.email }}</p>
                        <p class="text-slate-500 text-[11.5px]">Usuario registrado como: {{ user.name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        v-for="role in (user.roles || [])"
                        :key="role.name || role"
                        class="px-2.5 py-1 rounded-md text-[11.5px] font-bold bg-indigo-50 text-[#35449F] border border-indigo-100"
                    >
                        {{ role.name || role }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <!-- Rol del Usuario -->
                <div>
                    <InputLabel for="account_role" value="Rol en el Sistema" class="text-[14px] font-bold text-gray-900" />
                    <select
                        id="account_role"
                        v-model="form.rol"
                        class="mt-1.5 block w-full rounded-xl border-gray-300 text-sm font-medium focus:border-[#35449F] focus:ring-[#35449F] bg-white shadow-2xs"
                    >
                        <option v-for="r in roles" :key="r" :value="r">
                            {{ getRoleDescription(r) }}
                        </option>
                    </select>
                    <InputError :message="form.errors.rol" class="mt-1" />
                </div>

                <!-- Estado del Usuario -->
                <div>
                    <InputLabel for="account_status" value="Estado de la Cuenta" class="text-[14px] font-bold text-gray-900" />
                    <div class="mt-2.5 flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.user_is_active"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                        <span class="text-xs font-bold" :class="form.user_is_active ? 'text-emerald-700' : 'text-gray-500'">
                            {{ form.user_is_active ? 'Cuenta habilitada para acceder' : 'Cuenta inactiva / suspendida' }}
                        </span>
                    </div>
                </div>

                <!-- Cambiar Contraseña (Opcional) -->
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <InputLabel for="edit_password" value="Cambiar Contraseña (Opcional)" class="text-[14px] font-bold text-gray-900" />
                        <button
                            type="button"
                            @click="generatePassword"
                            class="text-xs font-bold text-[#35449F] hover:text-indigo-800 transition cursor-pointer flex items-center gap-1"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Generar contraseña aleatoria
                        </button>
                    </div>

                    <div class="relative">
                        <TextInput
                            id="edit_password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full pr-20 text-[14.5px] font-medium"
                            placeholder="Dejar en blanco para mantener la contraseña actual"
                        />
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                            <button
                                v-if="form.password"
                                type="button"
                                @click="copyPassword"
                                class="p-1.5 text-gray-400 hover:text-[#35449F] transition rounded-lg"
                                title="Copiar contraseña"
                            >
                                <svg v-if="!copied" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span v-else class="text-[11px] font-bold text-emerald-600">¡Copiada!</span>
                            </button>
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="p-1.5 text-gray-400 hover:text-gray-700 transition rounded-lg"
                                :title="showPassword ? 'Ocultar' : 'Mostrar'"
                            >
                                <svg v-if="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="text-[12px] text-gray-500 mt-1">
                        Solo ingrese una contraseña si desea cambiar la actual (mínimo 8 caracteres).
                    </p>
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>
            </div>
        </div>

        <!-- CASO 2: CREACIÓN O EDICIÓN SIN USUARIO -> FORMULARIO DE ALTA -->
        <div v-else-if="form.crear_usuario" class="space-y-4 animate-in fade-in duration-200">
            <div class="p-3.5 rounded-xl bg-blue-50/60 border border-blue-100 flex items-start gap-3">
                <svg class="w-5 h-5 text-[#35449F] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-slate-700">
                    <span class="font-bold">Se creará automáticamente la cuenta de usuario.</span>
                    El usuario podrá iniciar sesión en la plataforma utilizando el correo
                    <span class="font-bold text-[#35449F] font-mono">{{ form.p_correo || '[correo ingresado arriba]' }}</span>
                    y la contraseña configurada a continuación.
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-1">
                <!-- Rol en el Sistema -->
                <div>
                    <InputLabel for="new_account_role" value="Rol Asignado *" class="text-[14px] font-bold text-gray-900" />
                    <select
                        id="new_account_role"
                        v-model="form.rol"
                        class="mt-1.5 block w-full rounded-xl border-gray-300 text-sm font-medium focus:border-[#35449F] focus:ring-[#35449F] bg-white shadow-2xs"
                        required
                    >
                        <option v-for="r in roles" :key="r" :value="r">
                            {{ getRoleDescription(r) }}
                        </option>
                    </select>
                    <InputError :message="form.errors.rol" class="mt-1" />
                </div>

                <!-- Estado Inicial de la Cuenta -->
                <div>
                    <InputLabel for="new_account_active" value="Estado de la Cuenta" class="text-[14px] font-bold text-gray-900" />
                    <div class="mt-2.5 flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.user_is_active"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                        <span class="text-xs font-bold" :class="form.user_is_active ? 'text-emerald-700' : 'text-gray-500'">
                            {{ form.user_is_active ? 'Habilitada para acceder de inmediato' : 'Inactiva (bloqueada)' }}
                        </span>
                    </div>
                </div>

                <!-- Contraseña de Acceso -->
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <InputLabel for="new_password" value="Contraseña de Acceso *" class="text-[14px] font-bold text-gray-900" />
                        <button
                            type="button"
                            @click="generatePassword"
                            class="text-xs font-bold text-[#35449F] hover:text-indigo-800 transition cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            🎲 Generar contraseña segura
                        </button>
                    </div>

                    <div class="relative">
                        <TextInput
                            id="new_password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full pr-24 text-[14.5px] font-medium"
                            placeholder="Mínimo 8 caracteres (o haz clic en Generar)"
                            required
                        />
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                            <button
                                v-if="form.password"
                                type="button"
                                @click="copyPassword"
                                class="px-2 py-1 text-gray-400 hover:text-[#35449F] transition rounded-lg text-xs"
                                title="Copiar contraseña"
                            >
                                <svg v-if="!copied" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span v-else class="text-[11px] font-bold text-emerald-600">¡Copiada!</span>
                            </button>
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="p-1.5 text-gray-400 hover:text-gray-700 transition rounded-lg"
                                :title="showPassword ? 'Ocultar' : 'Mostrar'"
                            >
                                <svg v-if="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="text-[12px] text-gray-500 mt-1">
                        La contraseña debe tener mínimo 8 caracteres para asegurar la protección de la cuenta.
                    </p>
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>
            </div>
        </div>

        <!-- CASO 3: DAR DE ALTA DESHABILITADO -->
        <div v-else class="p-4 rounded-xl bg-gray-50 border border-gray-200/80 flex items-center gap-3">
            <div class="size-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
            <div class="text-xs text-gray-600">
                <p class="font-bold text-gray-800">No se creará cuenta de usuario para este registro.</p>
                <p class="mt-0.5">Se guardará únicamente como ficha informativa en la base de datos sin credenciales de inicio de sesión.</p>
            </div>
        </div>
    </div>
</template>
