/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0D63A5',
        'primary-dark': '#083D68',
        secondary: '#F9F9F9',
        slatebody: '#494A5E',
        accent: '#FFD717',
        dark: '#212121',
        borderline: '#E5E7EB',
        success: {
          DEFAULT: '#16A34A',
          bg: '#DCFCE7',
        },
        warning: {
          DEFAULT: '#D97706',
          bg: '#FEF3C7',
        },
        danger: {
          DEFAULT: '#DC2626',
          bg: '#FEE2E2',
        },
      },
      fontFamily: {
        heading: ['Nunito', 'sans-serif'],
        sans: ['Inter', 'sans-serif'],
      },
      keyframes: {
        marquee: {
          '0%': { transform: 'translateX(0%)' },
          '100%': { transform: 'translateX(-50%)' },
        },
        'marquee-reverse': {
          '0%': { transform: 'translateX(-50%)' },
          '100%': { transform: 'translateX(0%)' },
        },
      },
      animation: {
        marquee: 'marquee 30s linear infinite',
        'marquee-reverse': 'marquee-reverse 30s linear infinite',
      },
    },
  },
  plugins: [],
}
