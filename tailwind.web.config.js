/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/*/web/**/*.blade.php",
    "./resources/*/web/**/*.js",
    "./resources/*/web/**/*.vue",
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
