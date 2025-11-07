<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, Users, Download, Clock, Search, BookMarked, Library, GraduationCap, ArrowRight, Shield, Globe, BookText, Sparkles, Star, Zap } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

// Estado para efectos de movimiento
const mousePosition = ref({ x: 0, y: 0 });
const scrollY = ref(0);
const isVisible = ref(false);

// Seguimiento del mouse para efectos parallax
const handleMouseMove = (event: MouseEvent) => {
    mousePosition.value = {
        x: (event.clientX / window.innerWidth) * 100,
        y: (event.clientY / window.innerHeight) * 100
    };
};

// Seguimiento del scroll
const handleScroll = () => {
    scrollY.value = window.scrollY;
};

// Intersection Observer para animaciones al hacer scroll
let observer: IntersectionObserver;

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('scroll', handleScroll);
    
    // Configurar Intersection Observer
    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, { threshold: 0.1 });
    
    // Observar elementos con data-animate
    document.querySelectorAll('[data-animate]').forEach(el => {
        observer.observe(el);
    });

    // Efecto de aparición inicial
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('scroll', handleScroll);
    if (observer) observer.disconnect();
});
</script>

<template>
    <Head title="OpenLibrary - Tu Biblioteca Digital Universitaria">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        <meta name="description" content="Accede a miles de libros digitales y físicos. Tu biblioteca universitaria en línea con recursos académicos, investigación y literatura." />
    </Head>

    <div class="min-h-screen bg-background overflow-hidden">
        <!-- Efectos de fondo animados -->
        <div class="fixed inset-0 pointer-events-none">
            <div 
                class="absolute inset-0 opacity-30 transition-transform duration-1000"
                :style="{
                    transform: `translate(${(mousePosition.x - 50) * 0.02}px, ${(mousePosition.y - 50) * 0.02}px)`
                }"
            >
                <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-primary/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-secondary/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
            </div>
        </div>

        <!-- Header/Navigation -->
        <header 
            class="sticky top-0 z-50 bg-background/80 backdrop-blur-md border-b border-border transition-all duration-300"
            :class="{
                'bg-background/95 shadow-lg': scrollY > 50
            }"
        >
            <div class="container mx-auto px-4 py-4">
                <nav class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 group cursor-pointer">
                        <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shadow-lg shadow-primary/20 group-hover:shadow-primary/30 transition-all duration-300 group-hover:scale-110">
                            <BookOpen class="w-5 h-5 text-primary-foreground group-hover:rotate-12 transition-transform duration-300" />
                        </div>
                        <div>
                            <span class="text-xl font-bold text-foreground">OpenLibrary</span>
                            <span class="block text-xs text-muted-foreground">Biblioteca Digital</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-all duration-300 shadow-lg shadow-primary/20 hover:shadow-primary/30 flex items-center gap-2 hover:gap-3 group"
                        >
                            <BookText class="w-4 h-4 group-hover:scale-110 transition-transform" />
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="rounded-lg border border-border bg-card px-6 py-2.5 text-sm font-medium text-foreground hover:bg-accent transition-all duration-300 hover:shadow-md hover:scale-105"
                            >
                                Iniciar Sesión
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-all duration-300 shadow-lg shadow-primary/20 hover:shadow-primary/30 flex items-center gap-2 hover:gap-3 group"
                            >
                                Registrarse
                                <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </template>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-secondary/5 to-background" />
            
            <!-- Partículas flotantes -->
            <div class="absolute inset-0 pointer-events-none">
                <div 
                    v-for="i in 15" :key="i"
                    class="absolute w-2 h-2 bg-primary/20 rounded-full animate-float"
                    :style="{
                        left: `${Math.random() * 100}%`,
                        top: `${Math.random() * 100}%`,
                        animationDelay: `${Math.random() * 5}s`,
                        animationDuration: `${3 + Math.random() * 4}s`
                    }"
                ></div>
            </div>

            <div class="container mx-auto px-4 py-20 relative">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-16">
                    <div 
                        class="flex-1 text-center lg:text-left px-15"
                        data-animate
                    >
                        <div class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-medium mb-6 border border-primary/20 animate-fade-in">
                            <Sparkles class="w-4 h-4 animate-pulse" />
                            Plataforma académica
                            <Star class="w-3 h-3 fill-current" />
                        </div>
                            <h1 class="text-4xl lg:text-6xl font-bold text-foreground mb-6 leading-tight animate-slide-up">
                            Descubre el conocimiento 
                            <span class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text gradient-text animate-gradient">sin límites</span>
                        </h1>
                        <p class="text-xl text-muted-foreground mb-8 max-w-2xl leading-relaxed animate-slide-up" style="animation-delay: 0.1s;">
                            Accede a una colección curada de miles de libros digitales y físicos. 
                            Tu biblioteca universitaria en línea con los mejores recursos académicos, 
                            materiales de investigación y literatura especializada.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-slide-up" style="animation-delay: 0.2s;">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="register()"
                                class="group relative rounded-xl bg-primary px-8 py-4 text-lg font-semibold text-primary-foreground hover:bg-primary/90 transition-all duration-300 shadow-2xl shadow-primary/25 hover:shadow-primary/40 flex items-center justify-center gap-3 overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                                <BookOpen class="w-5 h-5 group-hover:scale-110 transition-transform duration-300 z-10" />
                                <span class="z-10">Comenzar Gratis</span>
                                <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300 z-10" />
                            </Link>
                            <Link
                                :href="login()"
                                class="group rounded-xl border border-border bg-card px-8 py-4 text-lg font-semibold text-foreground hover:bg-accent transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-3 hover:scale-105"
                            >
                                <Search class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                                Explorar Catálogo
                            </Link>
                        </div>
                        
                        <!-- Stats animadas -->
                        <div class="flex flex-wrap gap-8 mt-12 justify-center lg:justify-start animate-slide-up" style="animation-delay: 0.3s;">
                            <div 
                                v-for="(stat, index) in [
                                    { number: '10,000+', label: 'Libros Digitales' },
                                    { number: '5,000+', label: 'Usuarios Activos' },
                                    { number: '24/7', label: 'Disponible' }
                                ]" 
                                :key="index"
                                class="text-center lg:text-left group"
                            >
                                <div class="text-2xl font-bold text-foreground group-hover:scale-110 transition-transform duration-300">
                                    {{ stat.number }}
                                </div>
                                <div class="text-sm text-muted-foreground group-hover:text-foreground transition-colors duration-300">
                                    {{ stat.label }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hero Visual con animaciones 3D -->
                    <div class="flex-1 relative animate-float" style="animation-duration: 6s;">
                        <div class="relative">
                            <div 
                                class="w-full aspect-square max-w-md mx-auto bg-gradient-to-br from-primary/20 to-secondary/20 rounded-3xl shadow-2xl flex items-center justify-center backdrop-blur-sm border border-border/50 hover:shadow-3xl transition-all duration-500"
                                :style="{
                                    transform: `rotate3d(${(mousePosition.y - 50) / 50}, ${-(mousePosition.x - 50) / 50}, 0, 5deg)`
                                }"
                            >
                                <div class="relative">
                                    <Library class="w-32 h-32 text-primary animate-pulse" style="animation-duration: 3s;" />
                                    <div class="absolute -inset-4 bg-primary/10 rounded-full blur-xl animate-ping" style="animation-duration: 4s;" />
                                </div>
                            </div>
                            
                            <!-- Floating Cards con animaciones individuales -->
                            <div class="absolute -top-4 -left-4 w-20 h-20 bg-card rounded-2xl shadow-lg border border-border flex items-center justify-center transform rotate-12 animate-float hover:scale-110 transition-transform duration-300 group"
                                style="animation-delay: 1s;"
                            >
                                <BookMarked class="w-8 h-8 text-primary group-hover:scale-110 transition-transform duration-300" />
                                <div class="absolute inset-0 bg-primary/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            <div class="absolute -bottom-4 -right-4 w-16 h-16 bg-card rounded-2xl shadow-lg border border-border flex items-center justify-center transform -rotate-12 animate-float hover:scale-110 transition-transform duration-300 group"
                                style="animation-delay: 2s;"
                            >
                                <GraduationCap class="w-6 h-6 text-secondary group-hover:scale-110 transition-transform duration-300" />
                                <div class="absolute inset-0 bg-secondary/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            <div class="absolute top-1/2 -right-8 w-14 h-14 bg-card rounded-2xl shadow-lg border border-border flex items-center justify-center transform rotate-6 animate-float hover:scale-110 transition-transform duration-300 group"
                                style="animation-delay: 3s;"
                            >
                                <Globe class="w-5 h-5 text-primary group-hover:scale-110 transition-transform duration-300" />
                                <div class="absolute inset-0 bg-primary/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-card/50 relative overflow-hidden">
            <div class="container mx-auto px-4">
                <div class="text-center max-w-3xl mx-auto mb-16" data-animate>
                    <h2 class="text-3xl lg:text-4xl font-bold text-foreground mb-4 animate-slide-up">
                        Todo lo que necesitas para tu 
                        <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">éxito académico</span>
                    </h2>
                    <p class="text-lg text-muted-foreground animate-slide-up" style="animation-delay: 0.1s;">
                        Una plataforma diseñada específicamente para la comunidad universitaria 
                        con herramientas y recursos que facilitan tu aprendizaje e investigación.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div 
                        v-for="(feature, index) in [
                            { icon: BookOpen, title: 'Catálogo Completo', description: 'Miles de libros digitales y físicos organizados por categorías, autores y temas específicos para tu área de estudio.', color: 'primary' },
                            { icon: Download, title: 'Descargas Ilimitadas', description: 'Acceso ilimitado a contenido digital con límites generosos diarios para que nunca te quedes sin material de estudio.', color: 'secondary' },
                            { icon: Clock, title: 'Préstamos Flexibles', description: 'Sistema inteligente de préstamos con renovaciones automáticas y notificaciones para que nunca devuelvas tarde.', color: 'primary' },
                            { icon: Users, title: 'Comunidad Activa', description: 'Conecta con estudiantes, profesores e investigadores que comparten tus mismos intereses académicos.', color: 'secondary' }
                        ]" 
                        :key="index"
                        class="group p-8 rounded-2xl bg-background border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-2xl hover:-translate-y-3 animate-slide-up"
                        :style="{
                            animationDelay: `${index * 0.1 + 0.2}s`
                        }"
                        data-animate
                    >
                        <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 group-hover:rotate-12"
                            :class="{
                                'bg-primary/10': feature.color === 'primary',
                                'bg-secondary/10': feature.color === 'secondary'
                            }"
                        >
                            <component 
                                :is="feature.icon" 
                                class="w-8 h-8 transition-colors duration-300"
                                :class="{
                                    'text-primary': feature.color === 'primary',
                                    'text-secondary': feature.color === 'secondary'
                                }"
                            />
                        </div>
                        <h3 class="text-xl font-semibold text-foreground mb-3 group-hover:translate-x-1 transition-transform duration-300">
                            {{ feature.title }}
                        </h3>
                        <p class="text-muted-foreground leading-relaxed group-hover:text-foreground/80 transition-colors duration-300">
                            {{ feature.description }}
                        </p>
                        <div class="absolute bottom-0 left-0 w-0 h-1 bg-gradient-to-r from-primary to-secondary group-hover:w-full transition-all duration-500 rounded-full"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-20 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-card/30 pointer-events-none"></div>
            <div class="container mx-auto px-4">
                <div class="text-center max-w-3xl mx-auto mb-16" data-animate>
                    <h2 class="text-3xl lg:text-4xl font-bold text-foreground mb-4 animate-slide-up">
                        Comienza en 
                        <span class="text-primary">3 simples pasos</span>
                    </h2>
                    <p class="text-lg text-muted-foreground animate-slide-up" style="animation-delay: 0.1s;">
                        Nuestra plataforma está diseñada para ser intuitiva y fácil de usar, 
                        para que puedas concentrarte en lo que realmente importa: aprender.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div 
                        v-for="(step, index) in [
                            { number: '1', title: 'Regístrate', description: 'Crea tu cuenta con tu correo institucional en menos de 2 minutos.' },
                            { number: '2', title: 'Explora', description: 'Busca en nuestro catálogo por título, autor, categoría o palabra clave.' },
                            { number: '3', title: 'Disfruta', description: 'Descarga contenido digital o reserva ejemplares físicos al instante.' }
                        ]" 
                        :key="index"
                        class="relative text-center group animate-slide-up"
                        :style="{
                            animationDelay: `${index * 0.2 + 0.3}s`
                        }"
                        data-animate
                    >
                        <div class="relative z-10">
                            <div class="w-20 h-20 bg-primary text-primary-foreground rounded-2xl flex items-center justify-center mx-auto mb-6 text-xl font-bold shadow-lg shadow-primary/25 group-hover:scale-110 transition-transform duration-300 group-hover:shadow-primary/40 relative overflow-hidden">
                                <span class="z-10">{{ step.number }}</span>
                                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                            </div>
                            <h3 class="text-xl font-semibold text-foreground mb-3 group-hover:text-primary transition-colors duration-300">
                                {{ step.title }}
                            </h3>
                            <p class="text-muted-foreground group-hover:text-foreground/80 transition-colors duration-300">
                                {{ step.description }}
                            </p>
                        </div>
                        <div 
                            v-if="index < 2"
                            class="absolute top-10 left-1/2 w-32 h-px bg-gradient-to-r from-primary to-transparent md:block hidden group-hover:scale-x-150 transition-transform duration-500"
                            :class="{
                                'animate-pulse': index === 0
                            }"
                        ></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-primary relative overflow-hidden">
            <!-- Efectos de fondo animados -->
            <div class="absolute inset-0">
                <div class="absolute top-0 left-0 w-72 h-72 bg-white/5 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
            </div>
            
            <div class="container mx-auto px-4 text-center relative z-10" data-animate>
                <h2 class="text-3xl lg:text-4xl font-bold text-primary-foreground mb-4 animate-slide-up">
                    ¿Listo para transformar tu 
                    <span class="bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">experiencia académica?</span>
                </h2>
                <p class="text-primary-foreground/80 mb-8 text-xl max-w-2xl mx-auto leading-relaxed animate-slide-up" style="animation-delay: 0.1s;">
                    Únete a miles de estudiantes, investigadores y profesores que ya están 
                    descubriendo nuevas formas de acceder al conocimiento.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up" style="animation-delay: 0.2s;">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="group relative rounded-xl bg-primary-foreground px-8 py-4 text-lg font-semibold text-primary hover:bg-primary-foreground/90 transition-all duration-300 shadow-2xl hover:shadow-2xl hover:shadow-primary-foreground/20 flex items-center justify-center gap-3 overflow-hidden"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                        <Zap class="w-5 h-5 group-hover:scale-110 transition-transform duration-300 z-10" />
                        <span class="z-10">Comenzar Ahora</span>
                        <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300 z-10" />
                    </Link>
                    <Link
                        :href="login()"
                        class="group rounded-xl border border-primary-foreground/30 bg-transparent px-8 py-4 text-lg font-semibold text-primary-foreground hover:bg-primary-foreground/10 transition-all duration-300 flex items-center justify-center gap-3 hover:scale-105 backdrop-blur-sm"
                    >
                        <Sparkles class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                        Acceder a Mi Cuenta
                    </Link>
                </div>
                <p class="text-primary-foreground/60 text-sm mt-6 animate-fade-in" style="animation-delay: 0.4s;">
                    Sin costos ocultos • Cancelación en cualquier momento • Soporte 24/7
                </p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-muted border-t border-border py-16 relative overflow-hidden">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div data-animate>
                        <div class="flex items-center space-x-3 mb-4 group cursor-pointer">
                            <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shadow-lg shadow-primary/20 group-hover:shadow-primary/30 transition-all duration-300 group-hover:scale-110">
                                <BookOpen class="w-5 h-5 text-primary-foreground group-hover:rotate-12 transition-transform duration-300" />
                            </div>
                            <div>
                                <span class="text-xl font-bold text-foreground">OpenLibrary</span>
                                <span class="block text-xs text-muted-foreground">Biblioteca Digital</span>
                            </div>
                        </div>
                        <p class="text-muted-foreground leading-relaxed">
                            Tu biblioteca digital universitaria. Conectando conocimiento, 
                            personas y oportunidades de aprendizaje.
                        </p>
                    </div>
                    
                    <div data-animate style="animation-delay: 0.1s;">
                        <h4 class="text-lg font-semibold text-foreground mb-4">Enlaces Rápidos</h4>
                        <ul class="space-y-3">
                            <li>
                                <Link :href="login()" class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                    <ArrowRight class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                    Iniciar Sesión
                                </Link>
                            </li>
                            <li>
                                <Link :href="register()" class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                    <ArrowRight class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                    Registrarse
                                </Link>
                            </li>
                            <li>
                                <a href="#" class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                    <ArrowRight class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                    Catálogo Completo
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div data-animate style="animation-delay: 0.2s;">
                        <h4 class="text-lg font-semibold text-foreground mb-4">Recursos</h4>
                        <ul class="space-y-3">
                            <li>
                                <a href="#" class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                    <ArrowRight class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                    Guías de Uso
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                    <ArrowRight class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                    Preguntas Frecuentes
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-muted-foreground hover:text-foreground transition-all duration-300 flex items-center gap-2 group hover:translate-x-1">
                                    <ArrowRight class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-1" />
                                    Políticas de Uso
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div data-animate style="animation-delay: 0.3s;">
                        <h4 class="text-lg font-semibold text-foreground mb-4">Contacto</h4>
                        <ul class="space-y-3 text-muted-foreground">
                            <li class="flex items-center gap-2 group hover:text-foreground transition-colors duration-300">
                                <div class="w-2 h-2 bg-primary rounded-full group-hover:scale-150 transition-transform duration-300" />
                                soporte@openlibrary.edu
                            </li>
                            <li class="flex items-center gap-2 group hover:text-foreground transition-colors duration-300">
                                <div class="w-2 h-2 bg-secondary rounded-full group-hover:scale-150 transition-transform duration-300" />
                                +1 (555) 123-4567
                            </li>
                            <li class="flex items-center gap-2 group hover:text-foreground transition-colors duration-300">
                                <div class="w-2 h-2 bg-primary rounded-full group-hover:scale-150 transition-transform duration-300" />
                                Lunes a Viernes 8:00 - 18:00
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-border mt-12 pt-8 flex flex-col md:flex-row justify-between items-center" data-animate style="animation-delay: 0.4s;">
                    <p class="text-muted-foreground text-sm">
                        &copy; {{ new Date().getFullYear() }} Matt Innova Solucion. Todos los derechos reservados.
                    </p>
                    <div class="flex gap-6 mt-4 md:mt-0">
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors duration-300 text-sm hover:scale-105">Términos</a>
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors duration-300 text-sm hover:scale-105">Privacidad</a>
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors duration-300 text-sm hover:scale-105">Cookies</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Animaciones personalizadas */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}

.animate-slide-up {
    animation: slide-up 0.8s ease-out;
}

.animate-gradient {
    background: linear-gradient(-45deg, #2563eb, #3b82f6, #059669, #10b981);
    background-size: 400% 400%;
    animation: gradient 6s ease infinite;
}

/* Asegura que el gradiente se recorte al texto en navegadores WebKit */
.gradient-text {
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Para elementos que aparecen al hacer scroll */
[data-animate] {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease-out;
}

[data-animate].animate-in {
    opacity: 1;
    transform: translateY(0);
}
</style>