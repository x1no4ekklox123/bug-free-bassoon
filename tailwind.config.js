// Dark violet-tinted neutrals: 900 is the deepest page background, 700 a card surface, 500/400 muted text
// and borders, 200/100 readable text.
const gray = {
    50: '#f6f4ff',
    100: '#ebe8f8',
    200: '#d4d0e8',
    300: '#aea9cc',
    400: '#8f89b3',
    500: '#6f699a',
    600: '#3b3461',
    700: '#1b1632',
    800: '#110d22',
    900: '#09061a',
};

// Violet scale centered on the Aurorix accent (#8B62FF at 500).
const violet = {
    50: '#f5f2ff',
    100: '#ece6ff',
    200: '#dacdff',
    300: '#c2adff',
    400: '#a686ff',
    500: '#8b62ff',
    600: '#7a48f5',
    700: '#6a35e0',
    800: '#5a2bbd',
    900: '#43208f',
};

// Lavender / fuchsia complement to the violet primary.
const accent = {
    50: '#fdf4ff',
    100: '#f9e6ff',
    200: '#f1ccff',
    300: '#e6adff',
    400: '#d98cff',
    500: '#e46bff',
    600: '#cc47f5',
    700: '#a92fcf',
    800: '#8626a6',
    900: '#661d7e',
};

module.exports = {
    content: [
        './resources/scripts/**/*.{js,ts,tsx}',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Onest', 'system-ui', '-apple-system', '"Segoe UI"', 'Roboto', 'sans-serif'],
                header: ['Onest', '"Roboto"', 'system-ui', 'sans-serif'],
            },
            colors: {
                black: '#050309',
                // "primary" and "neutral" are deprecated, prefer the use of "blue" and "gray"
                // in new code.
                primary: violet,
                blue: violet,
                gray: gray,
                neutral: gray,
                cyan: accent,
            },
            fontSize: {
                '2xs': '0.625rem',
            },
            opacity: {
                15: '0.15',
            },
            transitionDuration: {
                250: '250ms',
            },
            borderColor: theme => ({
                default: theme('colors.neutral.400', 'currentColor'),
            }),
            boxShadow: {
                glow: '0 0 24px -4px rgba(139, 98, 255, 0.55)',
                'glow-sm': '0 0 14px -2px rgba(139, 98, 255, 0.5)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/line-clamp'),
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
    ]
};
