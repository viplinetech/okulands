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
                /* Brand accent: the light sky-blue from the Oku Lands logo mark
                   and marketing flyers (#5b9bf0), not gold, matching the
                   established navy/blue identity. */
                accent: {
                    50: '#eef5ff',
                    100: '#d9ebff',
                    200: '#b3d4ff',
                    300: '#85b8fb',
                    400: '#5b9bf0',
                    500: '#3d7de0',
                    600: '#2a5fc4',
                    700: '#20489a',
                    800: '#1a3a7a',
                    900: '#152f5f',
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
