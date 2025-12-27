<script setup lang="ts">
import { autoUpdate, computePosition, flip, offset, shift } from '@floating-ui/dom';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        align?: 'left' | 'right';
        width?: '48';
        contentClasses?: string;
    }>(),
    {
        align: 'right',
        width: '48',
        contentClasses: 'py-1 bg-white',
    },
);

const closeOnEscape = (e: KeyboardEvent) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        48: 'w-48',
    }[props.width.toString()];
});

const open = ref(false);
const triggerRef = ref<HTMLElement | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);
const floatingStyles = ref({ left: '0px', top: '0px' });
const cleanup = ref<(() => void) | null>(null);

const placement = computed(() => {
    return props.align === 'left' ? 'bottom-start' : 'bottom-end';
});

const updatePosition = async () => {
    if (!triggerRef.value || !dropdownRef.value) {
        return;
    }

    const { x, y } = await computePosition(triggerRef.value, dropdownRef.value, {
        placement: placement.value,
        strategy: 'fixed',
        middleware: [offset(8), flip(), shift({ padding: 8 })],
    });

    floatingStyles.value = {
        left: `${x}px`,
        top: `${y}px`,
    };
};

watch(open, (isOpen) => {
    if (!isOpen) {
        cleanup.value?.();
        cleanup.value = null;
        return;
    }

    nextTick(() => {
        updatePosition();
        if (!triggerRef.value || !dropdownRef.value) {
            return;
        }

        cleanup.value = autoUpdate(triggerRef.value, dropdownRef.value, updatePosition);
    });
});

onUnmounted(() => {
    cleanup.value?.();
});
</script>

<template>
    <div class="relative inline-block">
        <div ref="triggerRef" @click="open = !open">
            <slot name="trigger" :open="open" />
        </div>

        <Teleport to="body">
            <!-- Full Screen Dropdown Overlay -->
            <div
                v-show="open"
                class="fixed inset-0 z-40"
                @click="open = false"
            ></div>

            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-show="open"
                    ref="dropdownRef"
                    class="fixed z-50 rounded-md border border-gray-200 shadow-lg"
                    :class="widthClass"
                    :style="floatingStyles"
                    @click="open = false"
                >
                    <div
                        class="rounded-md"
                        :class="contentClasses"
                    >
                        <slot name="content" />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
