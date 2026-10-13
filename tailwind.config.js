import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/View/Components/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                '2xs': ['13px', { lineHeight: '1.4' }],
                xs: ['15px', { lineHeight: '1.45' }],
                sm: ['15px', { lineHeight: '1.45' }],
                base: ['17px', { lineHeight: '1.5' }],
                lg: ['17px', { lineHeight: '1.5' }],
                xl: ['22px', { lineHeight: '1.35' }],
                '2xl': ['22px', { lineHeight: '1.35' }],
                '3xl': ['30px', { lineHeight: '1.2' }],
                '4xl': ['44px', { lineHeight: '1.1' }],
            },
            colors: {
                // Background & surface
                bg: {
                    DEFAULT: '#F4F6F5',
                    dark: '#111827',
                },
                surface: {
                    DEFAULT: '#FFFFFF',
                    dark: '#1F2937',
                },
                // Text
                content: {
                    primary: '#1B2430',
                    secondary: '#5B6672',
                    'primary-dark': '#F3F4F6',
                    'secondary-dark': '#9CA3AF',
                },
                // Primary teal
                primary: {
                    DEFAULT: '#0E7C86',
                    hover: '#0A646C',
                    50: '#E6F4F5',
                    100: '#C2E4E6',
                    500: '#0E7C86',
                    600: '#0A646C',
                    900: '#063D42',
                },
                // Accent yellow-marigold
                accent: {
                    DEFAULT: '#F2B230',
                    hover: '#D99B20',
                    text: '#1B2430',
                },
                // Semantic
                success: {
                    DEFAULT: '#2E9E5B',
                    50: '#E7F6ED',
                    500: '#2E9E5B',
                },
                warning: {
                    DEFAULT: '#D98A1F',
                    50: '#FDF3E3',
                    500: '#D98A1F',
                },
                danger: {
                    DEFAULT: '#D64545',
                    50: '#FDE8E8',
                    500: '#D64545',
                },
                // Border
                border: {
                    DEFAULT: '#E5E8E7',
                    dark: '#374151',
                },
            },
            borderRadius: {
                input: '8px',
                card: '14px',
                dialog: '20px',
            },
            spacing: {
                '4.5': '18px',
                '18': '72px',
            },
            boxShadow: {
                dialog: '0 8px 40px rgba(0,0,0,0.15)',
                panel: '0 2px 12px rgba(0,0,0,0.08)',
                card: '0 1px 4px rgba(0,0,0,0.05)',
            },
            minHeight: {
                touch: '44px',
            },
            minWidth: {
                touch: '44px',
            },
        },
    },

    plugins: [forms],
};
