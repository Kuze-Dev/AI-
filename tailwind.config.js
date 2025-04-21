/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./app/FilamentTenant/**/*.php",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: "DM Sans, Helvetica, Arial, sans-serif",
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}
