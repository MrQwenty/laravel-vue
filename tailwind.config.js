const defaultTheme = require('tailwindcss/defaultTheme');
const formKitTailwind = require('@formkit/themes/tailwindcss');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/css/**/*',
        './theme.js'
    ],

    theme: {
        extend: {
            colors: {
              primaryColor : {
                  'DEFAULT': "#61c3d7",
                  '50': '#effafc',
                  '100': '#d7f1f6',
                  '200': '#b4e4ed',
                  '300': '#61c3d7',
                  '400': '#46b1ca',
                  '500': '#2b95af',
                  '600': '#267994',
                  '700': '#256379',
                  '800': '#265264',
                  '900': '#244555',
                  '950': '#132c39',
              }
            },
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [require('@tailwindcss/forms'), formKitTailwind],
};
