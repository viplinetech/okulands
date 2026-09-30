/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 *
 * "Quiet luxury, Nigerian roots": midnight navy anchor, warm ivory page
 * background, champagne gold as the single sparing accent.
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
                    50: '#eef1f6',
                    100: '#d7dced',
                    200: '#aeb8d9',
                    300: '#8390bf',
                    400: '#57649b',
                    500: '#374374',
                    600: '#232c56',
                    700: '#171e42',
                    800: '#0f1d45',
                    900: '#0a1330',
                    950: '#060a22',
                },
                ivory: {
                    DEFAULT: '#FAF8F3',
                    100: '#FFFFFF',
                    200: '#F3EFE4',
                },
                gold: {
                    50: '#faf6ef',
                    100: '#f0e4cd',
                    200: '#e3cda1',
                    300: '#d4b483',
                    400: '#c3a06a',
                    500: '#b8935a',
                    600: '#9c7846',
                    700: '#7c5f38',
                    800: '#5f4a2c',
                    900: '#4a3a23',
                },
                slate: {
                    ...defaultTheme.colors.slate,
                },
            },
            boxShadow: {
                premium: '0 20px 50px -15px rgba(10, 19, 48, 0.25)',
                'premium-lg': '0 30px 80px -20px rgba(10, 19, 48, 0.35)',
            },
            letterSpacing: {
                tightest: '-0.04em',
            },
        },
    },

    plugins: [forms, typography],
};
