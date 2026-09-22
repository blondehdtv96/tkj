import defaultTheme from 'tailwindcss/defaultTheme';
import typography from '@tailwindcss/typography';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#eff9ff',
                    100: '#dbf0ff',
                    200: '#b8e3ff',
                    300: '#7ecdff',
                    400: '#3bb0ff',
                    500: '#1090f5',
                    600: '#0472d2',
                    700: '#045bab',
                    800: '#084e8c',
                    900: '#0d4274',
                },
                teal: {
                    50: '#effcf9',
                    100: '#c9f7ec',
                    200: '#94eeda',
                    300: '#5adcc3',
                    400: '#2cc1a8',
                    500: '#15a38c',
                    600: '#0d8272',
                    700: '#0e685c',
                    800: '#10524a',
                    900: '#11443e',
                },
            },
        },
    },
    plugins: [typography, forms],
};
