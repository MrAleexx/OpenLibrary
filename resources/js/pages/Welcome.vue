<script setup lang="ts">
import CtaSection from '@/components/landing/CtaSection.vue';
import FeaturesSection from '@/components/landing/FeaturesSection.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import HowItWorksSection from '@/components/landing/HowItWorksSection.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import { Head } from '@inertiajs/vue3';
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
const isVisible = ref(false);

// Seguimiento del mouse para efectos parallax
const handleMouseMove = (event: MouseEvent) => {
    mousePosition.value = {
        x: (event.clientX / window.innerWidth) * 100,
        y: (event.clientY / window.innerHeight) * 100,
    };
};

// Intersection Observer para animaciones al hacer scroll
let observer: IntersectionObserver;

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove);

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
    // Usamos setTimeout para asegurar que el DOM esté listo
    setTimeout(() => {
        document.querySelectorAll('[data-animate]').forEach((el) => {
            observer.observe(el);
        });
    }, 100);

    // Efecto de aparición inicial
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
    if (observer) observer.disconnect();
});
</script>

<template>

    <Head title="OpenLibrary - Tu Biblioteca Digital Universitaria">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        <meta name="description"
            content="Accede a miles de libros digitales y físicos. Tu biblioteca universitaria en línea con recursos académicos, investigación y literatura." />
    </Head>

    <div class="min-h-screen overflow-hidden bg-background">
        <!-- Efectos de fondo animados -->
        <div class="fixed inset-0 pointer-events-none">
            <div class="absolute inset-0 opacity-30 transition-transform duration-1000" :style="{
                transform: `translate(${(mousePosition.x - 50) * 0.02}px, ${(mousePosition.y - 50) * 0.02}px)`,
            }">
                <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-primary/10 rounded-full blur-3xl animate-pulse">
                </div>
                <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-secondary/10 rounded-full blur-3xl animate-pulse"
                    style="animation-delay: 2s"></div>
            </div>
        </div>

        <LandingHeader :can-register="canRegister" />

        <HeroSection :mouse-position="mousePosition" />

        <FeaturesSection />

        <HowItWorksSection />

        <CtaSection v-if="!$page.props.auth.user" />

        <LandingFooter />
    </div>
</template>

<style>
/* Animaciones personalizadas - Globales para que funcionen en los componentes hijos */
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
