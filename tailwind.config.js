/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
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
