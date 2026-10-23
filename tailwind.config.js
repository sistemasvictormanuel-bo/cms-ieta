import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ieta: {
                    indigo: '#35449F',
                    magenta: '#F50088',
                    carmin: '#F30060',
                    violeta: '#71589F',
                    purpura: '#3E2987',
                    resaltador: '#6C81C7',
                    tinta: '#14161C',
                    desactivado: '#E0E0E0',
                    superficie: '#F5F5F8',
                },
            },
        },
    },

    plugins: [forms, typography],
};
