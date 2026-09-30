/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 *
 * Brand palette matches Oku Lands' own marketing flyers: midnight navy +
 * royal/sky blue as the primary identity, with a single sharp red accent
 * (the location-pin color) used sparingly. Ivory page background keeps the
 * light-mode-first, premium feel.
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
                /* The light sky-blue from the logo mark and flyers (#5b9bf0). */
                sky: {
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
                /* Rare, sharp accent: the flyer's location-pin red. Used only
                   for pins and the smallest of highlight touches. */
                flag: {
                    500: '#dc2626',
                    600: '#b91c1c',
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
