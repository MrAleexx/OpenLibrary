import { router } from '@inertiajs/vue3';
import { useIntervalFn } from '@vueuse/core';
import { onUnmounted, ref } from 'vue';

/**
 * Composable para actualizar datos periódicamente via Inertia
 *
 * @param intervalMs Intervalo en milisegundos (default: 3000ms)
 * @param propsToReload Lista de props de Inertia a recargar
 */
export function usePolling(
    intervalMs: number = 3000,
    propsToReload: string[] = [],
) {
    const isPolling = ref(false);

    const isReloading = ref(false);

    const { pause, resume, isActive } = useIntervalFn(() => {
        // Verificamos visibilidad para evitar peticiones en segundo plano
        if (document.hidden) return;

        if (propsToReload.length === 0) return;

        // Evitar peticiones superpuestas
        if (isReloading.value) return;

        isReloading.value = true;

        router.reload({
            only: propsToReload,
            // @ts-ignore
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                isReloading.value = false;
                // console.log('Datos actualizados via polling');
            },
        });
    }, intervalMs);

    const startPolling = () => {
        resume();
        isPolling.value = true;
    };

    const stopPolling = () => {
        pause();
        isPolling.value = false;
    };

    // Detener polling al desmontar
    onUnmounted(() => {
        stopPolling();
    });

    return {
        isPolling,
        startPolling,
        stopPolling,
        isActive,
    };
}
