/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                navy: {
                    50: '#eef1f8',
                    100: '#d6ddef',
                    200: '#aebbdf',
                    300: '#8299cf',
                    400: '#4f6bb0',
                    500: '#2a4593',
                    600: '#1c3170',
                    700: '#15265a',
                    800: '#101c44',
                    900: '#0a1330',
                    950: '#060c1f',
                },
                gold: {
                    50: '#fbf8ee',
                    100: '#f5ecc9',
                    200: '#ecd98e',
                    300: '#e3c358',
                    400: '#dcae3b',
                    500: '#d4af37',
                    600: '#b1892a',
                    700: '#8a6822',
                    800: '#6b501f',
                    900: '#57421c',
                },
            },
            boxShadow: {
                premium: '0 20px 60px -15px rgba(10, 19, 48, 0.35)',
                'premium-lg': '0 30px 90px -20px rgba(10, 19, 48, 0.45)',
            },
        },
    },

    plugins: [forms, typography],
};
