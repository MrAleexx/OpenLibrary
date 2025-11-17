<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookMarked,
    BookOpen,
    BookText,
    Clock,
    Download,
    Globe,
    GraduationCap,
    Library,
    Search,
    Sparkles,
    Star,
    Users,
    Zap,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

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
        y: (event.clientY / window.innerHeight) * 100,
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
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        },
        { threshold: 0.1 },
    );

    // Observar elementos con data-animate
    document.querySelectorAll('[data-animate]').forEach((el) => {
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
        <meta
            name="description"
            content="Accede a miles de libros digitales y físicos. Tu biblioteca universitaria en línea con recursos académicos, investigación y literatura."
        />
    </Head>

    <div class="min-h-screen overflow-hidden bg-background">
        <!-- Efectos de fondo animados -->
        <div class="pointer-events-none fixed inset-0">
            <div
                class="absolute inset-0 opacity-30 transition-transform duration-1000"
                :style="{
                    transform: `translate(${(mousePosition.x - 50) * 0.02}px, ${(mousePosition.y - 50) * 0.02}px)`,
                }"
            >
                <div
                    class="absolute top-1/4 left-1/4 h-64 w-64 animate-pulse rounded-full bg-primary/10 blur-3xl"
                ></div>
                <div
                    class="absolute right-1/4 bottom-1/4 h-96 w-96 animate-pulse rounded-full bg-secondary/10 blur-3xl"
                    style="animation-delay: 2s"
                ></div>
            </div>
        </div>

        <!-- Header/Navigation -->
        <header
            class="sticky top-0 z-50 border-b border-border bg-background/80 backdrop-blur-md transition-all duration-300"
            :class="{
                'bg-background/95 shadow-lg': scrollY > 50,
            }"
        >
            <div class="container mx-auto px-4 py-4">
                <nav class="flex items-center justify-between">
                    <div
                        class="group flex cursor-pointer items-center space-x-3"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary shadow-lg shadow-primary/20 transition-all duration-300 group-hover:scale-110 group-hover:shadow-primary/30"
                        >
                            <BookOpen
                                class="h-5 w-5 text-primary-foreground transition-transform duration-300 group-hover:rotate-12"
                            />
                        </div>
                        <div>
                            <span class="text-xl font-bold text-foreground"
                                >OpenLibrary</span
                            >
                            <span class="block text-xs text-muted-foreground"
                                >Biblioteca Digital</span
                            >
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="group flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-lg shadow-primary/20 transition-all duration-300 hover:gap-3 hover:bg-primary/90 hover:shadow-primary/30"
                        >
                            <BookText
                                class="h-4 w-4 transition-transform group-hover:scale-110"
                            />
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="rounded-lg border border-border bg-card px-6 py-2.5 text-sm font-medium text-foreground transition-all duration-300 hover:scale-105 hover:bg-accent hover:shadow-md"
                            >
                                Iniciar Sesión
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="group flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground shadow-lg shadow-primary/20 transition-all duration-300 hover:gap-3 hover:bg-primary/90 hover:shadow-primary/30"
                            >
                                Registrarse
                                <ArrowRight
                                    class="h-4 w-4 transition-transform group-hover:translate-x-1"
                                />
                            </Link>
                        </template>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary/5 via-secondary/5 to-background"
            />

            <!-- Partículas flotantes -->
            <div class="pointer-events-none absolute inset-0">
                <div
                    v-for="i in 15"
                    :key="i"
                    class="animate-float absolute h-2 w-2 rounded-full bg-primary/20"
                    :style="{
                        left: `${Math.random() * 100}%`,
                        top: `${Math.random() * 100}%`,
                        animationDelay: `${Math.random() * 5}s`,
                        animationDuration: `${3 + Math.random() * 4}s`,
                    }"
                ></div>
            </div>

            <div class="relative container mx-auto px-4 py-20">
                <div
                    class="flex flex-col items-center justify-between gap-16 lg:flex-row"
                >
                    <div
                        class="flex-1 px-15 text-center lg:text-left"
                        data-animate
                    >
                        <div
                            class="animate-fade-in mb-6 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary"
                        >
                            <Sparkles class="h-4 w-4 animate-pulse" />
                            Plataforma académica
                            <Star class="h-3 w-3 fill-current" />
                        </div>
                        <h1
                            class="animate-slide-up mb-6 text-4xl leading-tight font-bold text-foreground lg:text-6xl"
                        >
                            Descubre el conocimiento
                            <span
                                class="gradient-text animate-gradient bg-gradient-to-r from-primary to-primary/60 bg-clip-text"
                                >sin límites</span
                            >
                        </h1>
                        <p
                            class="animate-slide-up mb-8 max-w-2xl text-xl leading-relaxed text-muted-foreground"
                            style="animation-delay: 0.1s"
                        >
                            Accede a una colección curada de miles de libros
                            digitales y físicos. Tu biblioteca universitaria en
                            línea con los mejores recursos académicos,
                            materiales de investigación y literatura
                            especializada.
                        </p>
                        <div
                            class="animate-slide-up flex flex-col justify-center gap-4 sm:flex-row lg:justify-start"
                            style="animation-delay: 0.2s"
                        >
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="register()"
                                class="group relative flex items-center justify-center gap-3 overflow-hidden rounded-xl bg-primary px-8 py-4 text-lg font-semibold text-primary-foreground shadow-2xl shadow-primary/25 transition-all duration-300 hover:bg-primary/90 hover:shadow-primary/40"
                            >
                                <div
                                    class="absolute inset-0 translate-x-[-100%] -skew-x-12 bg-gradient-to-r from-transparent via-white/10 to-transparent transition-transform duration-1000 group-hover:translate-x-[100%]"
                                ></div>
                                <BookOpen
                                    class="z-10 h-5 w-5 transition-transform duration-300 group-hover:scale-110"
                                />
                                <span class="z-10">Comenzar Gratis</span>
                                <ArrowRight
                                    class="z-10 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                                />
                            </Link>
                            <Link
                                :href="login()"
                                class="group flex items-center justify-center gap-3 rounded-xl border border-border bg-card px-8 py-4 text-lg font-semibold text-foreground transition-all duration-300 hover:scale-105 hover:bg-accent hover:shadow-lg"
                            >
                                <Search
                                    class="h-5 w-5 transition-transform duration-300 group-hover:scale-110"
                                />
                                Explorar Catálogo
                            </Link>
                        </div>

                        <!-- Stats animadas -->
                        <div
                            class="animate-slide-up mt-12 flex flex-wrap justify-center gap-8 lg:justify-start"
                            style="animation-delay: 0.3s"
                        >
                            <div
                                v-for="(stat, index) in [
                                    {
                                        number: '10,000+',
                                        label: 'Libros Digitales',
                                    },
                                    {
                                        number: '5,000+',
                                        label: 'Usuarios Activos',
                                    },
                                    { number: '24/7', label: 'Disponible' },
                                ]"
                                :key="index"
                                class="group text-center lg:text-left"
                            >
                                <div
                                    class="text-2xl font-bold text-foreground transition-transform duration-300 group-hover:scale-110"
                                >
                                    {{ stat.number }}
                                </div>
                                <div
                                    class="text-sm text-muted-foreground transition-colors duration-300 group-hover:text-foreground"
                                >
                                    {{ stat.label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Visual con animaciones 3D -->
                    <div
                        class="animate-float relative flex-1"
                        style="animation-duration: 6s"
                    >
                        <div class="relative">
                            <div
                                class="hover:shadow-3xl mx-auto flex aspect-square w-full max-w-md items-center justify-center rounded-3xl border border-border/50 bg-gradient-to-br from-primary/20 to-secondary/20 shadow-2xl backdrop-blur-sm transition-all duration-500"
                                :style="{
                                    transform: `rotate3d(${(mousePosition.y - 50) / 50}, ${-(mousePosition.x - 50) / 50}, 0, 5deg)`,
                                }"
                            >
                                <div class="relative">
                                    <Library
                                        class="h-32 w-32 animate-pulse text-primary"
                                        style="animation-duration: 3s"
                                    />
                                    <div
                                        class="absolute -inset-4 animate-ping rounded-full bg-primary/10 blur-xl"
                                        style="animation-duration: 4s"
                                    />
                                </div>
                            </div>

                            <!-- Floating Cards con animaciones individuales -->
                            <div
                                class="animate-float group absolute -top-4 -left-4 flex h-20 w-20 rotate-12 transform items-center justify-center rounded-2xl border border-border bg-card shadow-lg transition-transform duration-300 hover:scale-110"
                                style="animation-delay: 1s"
                            >
                                <BookMarked
                                    class="h-8 w-8 text-primary transition-transform duration-300 group-hover:scale-110"
                                />
                                <div
                                    class="absolute inset-0 rounded-2xl bg-primary/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                ></div>
                            </div>
                            <div
                                class="animate-float group absolute -right-4 -bottom-4 flex h-16 w-16 -rotate-12 transform items-center justify-center rounded-2xl border border-border bg-card shadow-lg transition-transform duration-300 hover:scale-110"
                                style="animation-delay: 2s"
                            >
                                <GraduationCap
                                    class="h-6 w-6 text-secondary transition-transform duration-300 group-hover:scale-110"
                                />
                                <div
                                    class="absolute inset-0 rounded-2xl bg-secondary/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                ></div>
                            </div>
                            <div
                                class="animate-float group absolute top-1/2 -right-8 flex h-14 w-14 rotate-6 transform items-center justify-center rounded-2xl border border-border bg-card shadow-lg transition-transform duration-300 hover:scale-110"
                                style="animation-delay: 3s"
                            >
                                <Globe
                                    class="h-5 w-5 text-primary transition-transform duration-300 group-hover:scale-110"
                                />
                                <div
                                    class="absolute inset-0 rounded-2xl bg-primary/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="relative overflow-hidden bg-card/50 py-20">
            <div class="container mx-auto px-4">
                <div class="mx-auto mb-16 max-w-3xl text-center" data-animate>
                    <h2
                        class="animate-slide-up mb-4 text-3xl font-bold text-foreground lg:text-4xl"
                    >
                        Todo lo que necesitas para tu
                        <span
                            class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent"
                            >éxito académico</span
                        >
                    </h2>
                    <p
                        class="animate-slide-up text-lg text-muted-foreground"
                        style="animation-delay: 0.1s"
                    >
                        Una plataforma diseñada específicamente para la
                        comunidad universitaria con herramientas y recursos que
                        facilitan tu aprendizaje e investigación.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        v-for="(feature, index) in [
                            {
                                icon: BookOpen,
                                title: 'Catálogo Completo',
                                description:
                                    'Miles de libros digitales y físicos organizados por categorías, autores y temas específicos para tu área de estudio.',
                                color: 'primary',
                            },
                            {
                                icon: Download,
                                title: 'Descargas Ilimitadas',
                                description:
                                    'Acceso ilimitado a contenido digital con límites generosos diarios para que nunca te quedes sin material de estudio.',
                                color: 'secondary',
                            },
                            {
                                icon: Clock,
                                title: 'Préstamos Flexibles',
                                description:
                                    'Sistema inteligente de préstamos con renovaciones automáticas y notificaciones para que nunca devuelvas tarde.',
                                color: 'primary',
                            },
                            {
                                icon: Users,
                                title: 'Comunidad Activa',
                                description:
                                    'Conecta con estudiantes, profesores e investigadores que comparten tus mismos intereses académicos.',
                                color: 'secondary',
                            },
                        ]"
                        :key="index"
                        class="group animate-slide-up rounded-2xl border border-border bg-background p-8 transition-all duration-500 hover:-translate-y-3 hover:border-primary/30 hover:shadow-2xl"
                        :style="{
                            animationDelay: `${index * 0.1 + 0.2}s`,
                        }"
                        data-animate
                    >
                        <div
                            class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-12"
                            :class="{
                                'bg-primary/10': feature.color === 'primary',
                                'bg-secondary/10':
                                    feature.color === 'secondary',
                            }"
                        >
                            <component
                                :is="feature.icon"
                                class="h-8 w-8 transition-colors duration-300"
                                :class="{
                                    'text-primary': feature.color === 'primary',
                                    'text-secondary':
                                        feature.color === 'secondary',
                                }"
                            />
                        </div>
                        <h3
                            class="mb-3 text-xl font-semibold text-foreground transition-transform duration-300 group-hover:translate-x-1"
                        >
                            {{ feature.title }}
                        </h3>
                        <p
                            class="leading-relaxed text-muted-foreground transition-colors duration-300 group-hover:text-foreground/80"
                        >
                            {{ feature.description }}
                        </p>
                        <div
                            class="absolute bottom-0 left-0 h-1 w-0 rounded-full bg-gradient-to-r from-primary to-secondary transition-all duration-500 group-hover:w-full"
                        ></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="relative py-20">
            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-b from-transparent to-card/30"
            ></div>
            <div class="container mx-auto px-4">
                <div class="mx-auto mb-16 max-w-3xl text-center" data-animate>
                    <h2
                        class="animate-slide-up mb-4 text-3xl font-bold text-foreground lg:text-4xl"
                    >
                        Comienza en
                        <span class="text-primary">3 simples pasos</span>
                    </h2>
                    <p
                        class="animate-slide-up text-lg text-muted-foreground"
                        style="animation-delay: 0.1s"
                    >
                        Nuestra plataforma está diseñada para ser intuitiva y
                        fácil de usar, para que puedas concentrarte en lo que
                        realmente importa: aprender.
                    </p>
                </div>

                <div
                    class="mx-auto grid max-w-5xl grid-cols-1 gap-8 md:grid-cols-3"
                >
                    <div
                        v-for="(step, index) in [
                            {
                                number: '1',
                                title: 'Regístrate',
                                description:
                                    'Crea tu cuenta con tu correo institucional en menos de 2 minutos.',
                            },
                            {
                                number: '2',
                                title: 'Explora',
                                description:
                                    'Busca en nuestro catálogo por título, autor, categoría o palabra clave.',
                            },
                            {
                                number: '3',
                                title: 'Disfruta',
                                description:
                                    'Descarga contenido digital o reserva ejemplares físicos al instante.',
                            },
                        ]"
                        :key="index"
                        class="group animate-slide-up relative text-center"
                        :style="{
                            animationDelay: `${index * 0.2 + 0.3}s`,
                        }"
                        data-animate
                    >
                        <div class="relative z-10">
                            <div
                                class="relative mx-auto mb-6 flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl bg-primary text-xl font-bold text-primary-foreground shadow-lg shadow-primary/25 transition-transform duration-300 group-hover:scale-110 group-hover:shadow-primary/40"
                            >
                                <span class="z-10">{{ step.number }}</span>
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"
                                ></div>
                            </div>
                            <h3
                                class="mb-3 text-xl font-semibold text-foreground transition-colors duration-300 group-hover:text-primary"
                            >
                                {{ step.title }}
                            </h3>
                            <p
                                class="text-muted-foreground transition-colors duration-300 group-hover:text-foreground/80"
                            >
                                {{ step.description }}
                            </p>
                        </div>
                        <div
                            v-if="index < 2"
                            class="absolute top-10 left-1/2 hidden h-px w-32 bg-gradient-to-r from-primary to-transparent transition-transform duration-500 group-hover:scale-x-150 md:block"
                            :class="{
                                'animate-pulse': index === 0,
                            }"
                        ></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative overflow-hidden bg-primary py-20">
            <!-- Efectos de fondo animados -->
            <div class="absolute inset-0">
                <div
                    class="absolute top-0 left-0 h-72 w-72 animate-pulse rounded-full bg-white/5 blur-3xl"
                ></div>
                <div
                    class="absolute right-0 bottom-0 h-96 w-96 animate-pulse rounded-full bg-white/5 blur-3xl"
                    style="animation-delay: 2s"
                ></div>
            </div>

            <div
                class="relative z-10 container mx-auto px-4 text-center"
                data-animate
            >
                <h2
                    class="animate-slide-up mb-4 text-3xl font-bold text-primary-foreground lg:text-4xl"
                >
                    ¿Listo para transformar tu
                    <span
                        class="bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent"
                        >experiencia académica?</span
                    >
                </h2>
                <p
                    class="animate-slide-up mx-auto mb-8 max-w-2xl text-xl leading-relaxed text-primary-foreground/80"
                    style="animation-delay: 0.1s"
                >
                    Únete a miles de estudiantes, investigadores y profesores
                    que ya están descubriendo nuevas formas de acceder al
                    conocimiento.
                </p>
                <div
                    class="animate-slide-up flex flex-col justify-center gap-4 sm:flex-row"
                    style="animation-delay: 0.2s"
                >
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="group relative flex items-center justify-center gap-3 overflow-hidden rounded-xl bg-primary-foreground px-8 py-4 text-lg font-semibold text-primary shadow-2xl transition-all duration-300 hover:bg-primary-foreground/90 hover:shadow-2xl hover:shadow-primary-foreground/20"
                    >
                        <div
                            class="absolute inset-0 translate-x-[-100%] -skew-x-12 bg-gradient-to-r from-transparent via-primary/10 to-transparent transition-transform duration-1000 group-hover:translate-x-[100%]"
                        ></div>
                        <Zap
                            class="z-10 h-5 w-5 transition-transform duration-300 group-hover:scale-110"
                        />
                        <span class="z-10">Comenzar Ahora</span>
                        <ArrowRight
                            class="z-10 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                        />
                    </Link>
                    <Link
                        :href="login()"
                        class="group flex items-center justify-center gap-3 rounded-xl border border-primary-foreground/30 bg-transparent px-8 py-4 text-lg font-semibold text-primary-foreground backdrop-blur-sm transition-all duration-300 hover:scale-105 hover:bg-primary-foreground/10"
                    >
                        <Sparkles
                            class="h-5 w-5 transition-transform duration-300 group-hover:scale-110"
                        />
                        Acceder a Mi Cuenta
                    </Link>
                </div>
                <p
                    class="animate-fade-in mt-6 text-sm text-primary-foreground/60"
                    style="animation-delay: 0.4s"
                >
                    Sin costos ocultos • Cancelación en cualquier momento •
                    Soporte 24/7
                </p>
            </div>
        </section>

        <!-- Footer -->
        <footer
            class="relative overflow-hidden border-t border-border bg-muted py-16"
        >
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    <div data-animate>
                        <div
                            class="group mb-4 flex cursor-pointer items-center space-x-3"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary shadow-lg shadow-primary/20 transition-all duration-300 group-hover:scale-110 group-hover:shadow-primary/30"
                            >
                                <BookOpen
                                    class="h-5 w-5 text-primary-foreground transition-transform duration-300 group-hover:rotate-12"
                                />
                            </div>
                            <div>
                                <span class="text-xl font-bold text-foreground"
                                    >OpenLibrary</span
                                >
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >Biblioteca Digital</span
                                >
                            </div>
                        </div>
                        <p class="leading-relaxed text-muted-foreground">
                            Tu biblioteca digital universitaria. Conectando
                            conocimiento, personas y oportunidades de
                            aprendizaje.
                        </p>
                    </div>

                    <div data-animate style="animation-delay: 0.1s">
                        <h4 class="mb-4 text-lg font-semibold text-foreground">
                            Enlaces Rápidos
                        </h4>
                        <ul class="space-y-3">
                            <li>
                                <Link
                                    :href="login()"
                                    class="group flex items-center gap-2 text-muted-foreground transition-all duration-300 hover:translate-x-1 hover:text-foreground"
                                >
                                    <ArrowRight
                                        class="h-3 w-3 opacity-0 transition-all duration-300 group-hover:translate-x-1 group-hover:opacity-100"
                                    />
                                    Iniciar Sesión
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="register()"
                                    class="group flex items-center gap-2 text-muted-foreground transition-all duration-300 hover:translate-x-1 hover:text-foreground"
                                >
                                    <ArrowRight
                                        class="h-3 w-3 opacity-0 transition-all duration-300 group-hover:translate-x-1 group-hover:opacity-100"
                                    />
                                    Registrarse
                                </Link>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="group flex items-center gap-2 text-muted-foreground transition-all duration-300 hover:translate-x-1 hover:text-foreground"
                                >
                                    <ArrowRight
                                        class="h-3 w-3 opacity-0 transition-all duration-300 group-hover:translate-x-1 group-hover:opacity-100"
                                    />
                                    Catálogo Completo
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div data-animate style="animation-delay: 0.2s">
                        <h4 class="mb-4 text-lg font-semibold text-foreground">
                            Recursos
                        </h4>
                        <ul class="space-y-3">
                            <li>
                                <a
                                    href="#"
                                    class="group flex items-center gap-2 text-muted-foreground transition-all duration-300 hover:translate-x-1 hover:text-foreground"
                                >
                                    <ArrowRight
                                        class="h-3 w-3 opacity-0 transition-all duration-300 group-hover:translate-x-1 group-hover:opacity-100"
                                    />
                                    Guías de Uso
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="group flex items-center gap-2 text-muted-foreground transition-all duration-300 hover:translate-x-1 hover:text-foreground"
                                >
                                    <ArrowRight
                                        class="h-3 w-3 opacity-0 transition-all duration-300 group-hover:translate-x-1 group-hover:opacity-100"
                                    />
                                    Preguntas Frecuentes
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="group flex items-center gap-2 text-muted-foreground transition-all duration-300 hover:translate-x-1 hover:text-foreground"
                                >
                                    <ArrowRight
                                        class="h-3 w-3 opacity-0 transition-all duration-300 group-hover:translate-x-1 group-hover:opacity-100"
                                    />
                                    Políticas de Uso
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div data-animate style="animation-delay: 0.3s">
                        <h4 class="mb-4 text-lg font-semibold text-foreground">
                            Contacto
                        </h4>
                        <ul class="space-y-3 text-muted-foreground">
                            <li
                                class="group flex items-center gap-2 transition-colors duration-300 hover:text-foreground"
                            >
                                <div
                                    class="h-2 w-2 rounded-full bg-primary transition-transform duration-300 group-hover:scale-150"
                                />
                                soporte@openlibrary.edu
                            </li>
                            <li
                                class="group flex items-center gap-2 transition-colors duration-300 hover:text-foreground"
                            >
                                <div
                                    class="h-2 w-2 rounded-full bg-secondary transition-transform duration-300 group-hover:scale-150"
                                />
                                +1 (555) 123-4567
                            </li>
                            <li
                                class="group flex items-center gap-2 transition-colors duration-300 hover:text-foreground"
                            >
                                <div
                                    class="h-2 w-2 rounded-full bg-primary transition-transform duration-300 group-hover:scale-150"
                                />
                                Lunes a Viernes 8:00 - 18:00
                            </li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-12 flex flex-col items-center justify-between border-t border-border pt-8 md:flex-row"
                    data-animate
                    style="animation-delay: 0.4s"
                >
                    <p class="text-sm text-muted-foreground">
                        &copy; {{ new Date().getFullYear() }} Matt Innova
                        Solucion. Todos los derechos reservados.
                    </p>
                    <div class="mt-4 flex gap-6 md:mt-0">
                        <a
                            href="#"
                            class="text-sm text-muted-foreground transition-colors duration-300 hover:scale-105 hover:text-foreground"
                            >Términos</a
                        >
                        <a
                            href="#"
                            class="text-sm text-muted-foreground transition-colors duration-300 hover:scale-105 hover:text-foreground"
                            >Privacidad</a
                        >
                        <a
                            href="#"
                            class="text-sm text-muted-foreground transition-colors duration-300 hover:scale-105 hover:text-foreground"
                            >Cookies</a
                        >
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Animaciones personalizadas */
@keyframes float {
    0%,
    100% {
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
