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
                    50: '#FEB8B8',
                    100: '#FEA9A9',
                    200: '#FE8B8B',
                    300: '#FE6D6D',
                    400: '#FD4E4E',
                    500: '#FD3030',
                    600: '#FD0808',
                    700: '#D90202',
                    800: '#B10202',
                    900: '#880101',
                },
                primary: {
                    50: '#5ECFFF',
                    100: '#49C9FF',
                    200: '#20BDFF',
                    300: '#00ADF7',
                    400: '#0091CE',
                    500: '#0074A5',
                    600: '#00577C',
                    700: '#003B53',
                    800: '#001E2B',
                    900: '#000102',
                },
                success: {
                    50: '#F5FE00',
                    100: '#E6EE00',
                    200: '#C9D000',
                    300: '#ABB100',
                    400: '#8E9300',
                    500: '#707400',
                    600: '#5C6000',
                    700: '#494B00',
                    800: '#353700',
                    900: '#212200',
                },
                warning: {
                    50: '#FEC8B3',
                    100: '#FEBDA4',
                    200: '#FDA786',
                    300: '#FD9167',
                    400: '#FC7B49',
                    500: '#FC652B',
                    600: '#F14503',
                    700: '#BE3703',
                    800: '#8C2802',
                    900: '#5A1A01',
                },
            },
            fontFamily: {
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
