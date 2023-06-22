const colors = require('tailwindcss/colors')
const defaultTheme = require('tailwindcss/defaultTheme')

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/*/filament/**/*.blade.php",
        "./resources/*/filament/**/*.js",
        './vendor/filament/**/*.blade.php',
        "./app/Filament/**/*.php",
        "./app/FilamentTenant/**/*.php",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                danger: {
                    50: '#FFEBEB',
                    100: '#FFD9D9',
                    200: '#FFC0C0',
                    300: '#FF7979',
                    400: '#CC2727',
                    500: '#9E2020',
                    600: '#FD3030',
                    700: '#841E1E',
                    800: '#670F0F',
                    900: '#340505',
                },
                primary: {
                    50: '#F0FFFC',
                    100: '#D6FFF8',
                    200: '#86CFD4',
                    300: '#59C8F3',
                    400: '#27BBF5',
                    500: '#01648D',
                    600: '#0098D1',
                    700: '#1450AD',
                    800: '#0B367A',
                    900: '#0A1E3E',
                },
                success: {
                    50: '#EAEBBA',
                    100: '#E7E9A9',
                    200: '#DADD74',
                    300: '#E5EC67',
                    400: '#C9CF57',
                    500: '#707400',
                    600: '#959A04',
                    700: '#4B4E00',
                    800: '#40420C',
                    900: '#1B1C00',
                },
                warning: {
                    50: '#FFF2BC',
                    100: '#F6EACD',
                    200: '#F9D279',
                    300: '#F6BE3B',
                    400: '#FF850C',
                    500: '#FC652B',
                    600: '#F06305',
                    700: '#CD5100',
                    800: '#A63A06',
                    900: '#712906',
                },
            },
            fontFamily: {
                sans: ['"Source Sans 3"', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
