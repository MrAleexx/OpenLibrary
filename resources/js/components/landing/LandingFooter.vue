<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { login, register } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
</script>

<template>
    <footer class="bg-muted border-t border-border py-16 relative overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div data-animate>
                    <AppLogo class="mb-4" />
                    <p class="text-muted-foreground leading-relaxed">
                        Tu biblioteca digital universitaria. Conectando
                        conocimiento, personas y oportunidades de aprendizaje.
                    </p>
                </div>

                <div data-animate style="animation-delay: 0.1s">
                    <h4 class="text-lg font-semibold text-foreground mb-4">
                        Enlaces Rápidos
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <Link :href="login()"
                                class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                            <ArrowRight
                                class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                            Iniciar Sesión
                            </Link>
                        </li>
                        <li>
                            <Link :href="register()"
                                class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                            <ArrowRight
                                class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                            Registrarse
                            </Link>
                        </li>
                        <li>
                            <a href="#"
                                class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                <ArrowRight
                                    class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                Catálogo Completo
                            </a>
                        </li>
                    </ul>
                </div>

                <div data-animate style="animation-delay: 0.2s">
                    <h4 class="text-lg font-semibold text-foreground mb-4">
                        Recursos
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="#"
                                class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                <ArrowRight
                                    class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                Guías de Uso
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                <ArrowRight
                                    class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                Preguntas Frecuentes
                            </a>
                        </li>
                        <li>
                            <Link href="/usage-policies"
                                class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                            <ArrowRight
                                class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                            Políticas de Uso
                            </Link>
                        </li>
                    </ul>
                </div>

                <div data-animate style="animation-delay: 0.3s">
                    <h4 class="text-lg font-semibold text-foreground mb-4">
                        Contacto
                    </h4>
                    <ul class="space-y-3 text-muted-foreground">
                        <li class="flex items-center gap-2 group hover:text-foreground transition-colors duration-300">
                            <div
                                class="w-2 h-2 bg-primary rounded-full group-hover:scale-150 transition-transform duration-300" />
                            soporte@openlibrary.edu
                        </li>
                        <li class="flex items-center gap-2 group hover:text-foreground transition-colors duration-300">
                            <div
                                class="w-2 h-2 bg-secondary rounded-full group-hover:scale-150 transition-transform duration-300" />
                            +1 (555) 123-4567
                        </li>
                        <li class="flex items-center gap-2 group hover:text-foreground transition-colors duration-300">
                            <div
                                class="w-2 h-2 bg-primary rounded-full group-hover:scale-150 transition-transform duration-300" />
                            Lunes a Viernes 8:00 - 18:00
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-border mt-12 pt-8 flex flex-col md:flex-row justify-between items-center"
                data-animate style="animation-delay: 0.4s">
                <p class="text-muted-foreground text-sm">
                    &copy; {{ new Date().getFullYear() }} Matt Innova Solucion.
                    Todos los derechos reservados.
                </p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <Link href="/terms"
                        class="text-muted-foreground hover:text-foreground transition-colors duration-300 text-sm hover:scale-105">
                    Términos</Link>
                    <Link href="/privacy"
                        class="text-muted-foreground hover:text-foreground transition-colors duration-300 text-sm hover:scale-105">
                    Privacidad</Link>
                    <Link href="/cookies"
                        class="text-muted-foreground hover:text-foreground transition-colors duration-300 text-sm hover:scale-105">
                    Cookies</Link>
                </div>
            </div>
        </div>
    </footer>
</template>
