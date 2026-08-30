import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                gray: {
                    50: '#fafafa',
                    100: '#f5f5f5',
                    200: '#e5e5e5',
                    300: '#d4d4d4',
                    400: '#a3a3a3',
                    500: '#737373',
                    600: '#525252',
                    700: '#404040',
                    800: '#171717',
                    900: '#000000',
                    950: '#000000',
                },
                brand: {
                    50: '#fdf2f4',
                    100: '#fbe4e8',
                    200: '#f6cdd5',
                    300: '#f1a6b6',
                    400: '#e8728a',
                    500: '#d94462',
                    600: '#c12646',
                    700: '#a01a35',
                    800: '#a00526', // base logo color
                    900: '#730d22',
                    950: '#400210',
                }
            }
        },
    },

    plugins: [forms],
};
