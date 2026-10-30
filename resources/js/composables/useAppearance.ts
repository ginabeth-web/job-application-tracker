import { computed, ref } from 'vue';

export type Appearance = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'appearance';

function readStored(): Appearance {
    try {
        const value = localStorage.getItem(STORAGE_KEY);
        if (value === 'light' || value === 'dark' || value === 'system') {
            return value;
        }
    } catch {
        // storage unavailable
    }
    return 'system';
}

const appearance = ref<Appearance>(
    typeof window === 'undefined' ? 'system' : readStored(),
);

const systemDark = ref(
    typeof window !== 'undefined' &&
        window.matchMedia('(prefers-color-scheme: dark)').matches,
);

export const resolvedAppearance = computed<'light' | 'dark'>(() =>
    appearance.value === 'system'
        ? systemDark.value
            ? 'dark'
            : 'light'
        : appearance.value,
);

function apply(value: Appearance): void {
    const dark =
        value === 'dark' ||
        (value === 'system' &&
            window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);
}

let listening = false;

function listen(): void {
    if (listening || typeof window === 'undefined') {
        return;
    }
    listening = true;
    window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', (event) => {
            systemDark.value = event.matches;
            if (appearance.value === 'system') {
                apply('system');
            }
        });
}

export function useAppearance() {
    listen();

    function setAppearance(value: Appearance): void {
        appearance.value = value;
        try {
            localStorage.setItem(STORAGE_KEY, value);
        } catch {
            // storage unavailable
        }
        apply(value);
    }

    return { appearance, setAppearance };
}
