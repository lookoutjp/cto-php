import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // サイト（テナント）ごとのテーマカラー。実際の色はレイアウトが
            // <style> で出力する CSS 変数（App\Support\ThemePalette）で決まる。
            colors: {
                brand: {
                    DEFAULT: 'var(--brand, #374151)',
                    dark: 'var(--brand-dark, #1f2937)',
                    light: 'var(--brand-light, #9ca3af)',
                    bg: 'var(--brand-bg, #f3f4f6)',
                    fg: 'var(--brand-fg, #ffffff)',
                },
            },
        },
    },

    plugins: [forms, typography],
};
