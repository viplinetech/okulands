/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 *
 * Brand palette matches Oku Lands' own marketing flyers: midnight navy +
 * royal/sky blue as the primary identity, with a single sharp red accent
 * (the location-pin color) used sparingly. A cool, blue-tinted paper tone
 * (never warm cream) is used for light sections against the dark navy.
 */

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    // Classes toggled from resources/js/app.js (not scanned, since JS isn't in `content`).
    safelist: ['opacity-0', 'opacity-70', 'ring-2', 'bg-ink', 'text-page', 'border-ink'],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                serif: ['Instrument Serif', ...defaultTheme.fontFamily.serif],
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
                /* Semantic theme tokens (values live in app.css, flipped by html.dark).
                   Light is the default; every public page uses these, never raw colours. */
                page: 'rgb(var(--page) / <alpha-value>)',
                card: 'rgb(var(--card) / <alpha-value>)',
                soft: 'rgb(var(--soft) / <alpha-value>)',
                ink: 'rgb(var(--ink) / <alpha-value>)',
                mute: 'rgb(var(--mute) / <alpha-value>)',
                brand: {
                    DEFAULT: 'rgb(var(--brand) / <alpha-value>)',
                    fg: 'rgb(var(--brand-fg) / <alpha-value>)',
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
