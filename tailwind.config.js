import defaultTheme from 'tailwindcss/defaultTheme';
const colors = require('tailwindcss/colors')

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
        theme: {
            screens: {
                sm: '480px',
                md: '768px',
                lg: '976px',
                xl: '1440px',
            },
            colors: {
                colors,
            'white': '#ffffff',
            'blue': '#1fb6ff',
            'purple': '#7e5bef',
            'pink': '#ff49db',
            'orange': '#ff7849',
            'green': '#13ce66',
            'yellow': '#ffc82c',
            'gray-dark': '#273444',
            'gray': '#8492a6',
            'gray-light': '#d3dce6',
            'slate' : {
                50:  '#f8fafc',
                100: '#f1f5f9',
                200: '#e2e8f0',
                300: '#cbd5e1',
                400: '#94a3b8',
                500: '#64748b',
                600: '#475569',
                700: '#334155',
                800: '#1e293b',
                900: '#0f172a',
                950: '#020617',
                },
            'lime': {
                50:  '#f7fee7',
                100: '#ecfccb',
                200: '#d9f99d',
                300: '#bef264',
                400: '#a3e635',
                500: '#84cc16',
                600: '#65a30d',
                700: '#4d7c0f',
                800: '#3f6212',
                900: '#365314',
                950: '#1a2e05',
            },
            },
            fontFamily: {
                sans: ['Graphik', 'sans-serif'],
                serif: ['Merriweather', 'serif'],
            },
            extend: {
                spacing: {
                    '128': '32rem',
                    '144': '36rem',
                },
                fontSize: {
                  'xs': '.75rem',  // 12px
                  'sm': '.875rem', // 14px
                  'base': '1rem',  // 16px
                'lg': '1.125rem',// 18px
                  'xl': '1.25rem', // 20px
                  '2xl': '1.5rem', // 24px
                '3xl': '1.875rem',// 30px
                  '4xl': '2.25rem', // 36px
                  '5xl': '3rem',   // 48px
                  '6xl': '4rem',   // 64px
                },
                borderRadius: {
                    '4xl': '2rem',
                }
            }
        },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
