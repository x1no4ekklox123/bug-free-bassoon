import tw from 'twin.macro';
import { createGlobalStyle } from 'styled-components/macro';
// @ts-expect-error untyped font file
import fontLatin from '@fontsource-variable/onest/files/onest-latin-wght-normal.woff2';
// @ts-expect-error untyped font file
import fontCyrillic from '@fontsource-variable/onest/files/onest-cyrillic-wght-normal.woff2';

export default createGlobalStyle`
    @font-face {
        font-family: 'Onest';
        font-style: normal;
        font-display: swap;
        font-weight: 100 900;
        src: url(${fontCyrillic}) format('woff2-variations');
        unicode-range: U+0301,U+0400-045F,U+0490-0491,U+04B0-04B1,U+2116;
    }

    @font-face {
        font-family: 'Onest';
        font-style: normal;
        font-display: swap;
        font-weight: 100 900;
        src: url(${fontLatin}) format('woff2-variations');
        unicode-range: U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;
    }

    :root {
        color-scheme: dark;
    }

    html {
        background: #070707;
    }

    body {
        ${tw`font-sans text-neutral-200`};
        letter-spacing: 0.005em;
        min-height: 100vh;
        background-color: #070707;
        background-image:
            radial-gradient(900px 520px at 8% -6%, rgba(139, 98, 255, 0.16), transparent 70%),
            radial-gradient(720px 480px at 100% 0%, rgba(242, 96, 255, 0.07), transparent 70%),
            radial-gradient(900px 600px at 50% 120%, rgba(112, 0, 255, 0.14), transparent 70%),
            linear-gradient(95.06deg, #070707 4.06%, #070017 104.86%);
        background-attachment: fixed;
        background-repeat: no-repeat;
    }

    h1, h2, h3, h4, h5, h6 {
        ${tw`font-medium tracking-normal font-header`};
    }

    p {
        ${tw`text-neutral-200 leading-snug font-sans`};
    }

    a {
        transition: color 150ms ease;
    }

    form {
        ${tw`m-0`};
    }

    textarea, select, input, button, button:focus, button:focus-visible {
        ${tw`outline-none`};
    }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield !important;
    }

    ::selection {
        background: rgba(139, 98, 255, 0.45);
        color: #ffffff;
    }

    ::placeholder {
        color: rgba(255, 255, 255, 0.35);
    }

    /* Scroll Bar Style */
    ::-webkit-scrollbar {
        background: none;
        width: 14px;
        height: 14px;
    }

    ::-webkit-scrollbar-thumb {
        border: solid 0 rgb(0 0 0 / 0%);
        border-right-width: 4px;
        border-left-width: 4px;
        -webkit-border-radius: 9px 4px;
        -webkit-box-shadow: inset 0 0 0 1px rgba(139, 98, 255, 0.55), inset 0 0 0 4px rgba(112, 0, 255, 0.35);
    }

    ::-webkit-scrollbar-thumb:hover {
        -webkit-box-shadow: inset 0 0 0 1px rgba(166, 134, 255, 0.85), inset 0 0 0 4px rgba(139, 98, 255, 0.55);
    }

    ::-webkit-scrollbar-track-piece {
        margin: 4px 0;
    }

    ::-webkit-scrollbar-thumb:horizontal {
        border-right-width: 0;
        border-left-width: 0;
        border-top-width: 4px;
        border-bottom-width: 4px;
        -webkit-border-radius: 4px 9px;
    }

    ::-webkit-scrollbar-corner {
        background: transparent;
    }
`;
