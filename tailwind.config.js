import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Plus Jakarta Sans", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: "#007774",
                    50: "#e0f5f4",
                    100: "#b3e6e5",
                    200: "#80d4d2",
                    300: "#4dc2bf",
                    400: "#26b3b0",
                    500: "#007774",
                    600: "#006b68",
                    700: "#005c5a",
                    800: "#004d4b",
                    900: "#003533",
                },
                secondary: {
                    DEFAULT: "#81bd41",
                    50: "#f2f9e8",
                    100: "#dff0c6",
                    200: "#c9e6a0",
                    300: "#b3dc7a",
                    400: "#a2d55e",
                    500: "#81bd41",
                    600: "#73a93a",
                    700: "#629132",
                    800: "#51792a",
                    900: "#3a571e",
                },
            },
        },
    },

    plugins: [forms],
};
