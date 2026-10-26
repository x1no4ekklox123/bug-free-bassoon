import { css } from 'styled-components/macro';

// Shared "glass card" surface used by the cards, boxes and rows across the UI.
export const glassSurface = css`
    background-color: rgba(17, 13, 34, 0.78);
    background-image: radial-gradient(
            102.26% 102.26% at -2.26% 21.43%,
            rgba(255, 255, 255, 0.07) 0,
            rgba(153, 153, 153, 0.01)
        ),
        linear-gradient(104.59deg, rgba(40, 30, 74, 0.6), rgba(10, 7, 21, 0.72) 60.33%);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 12px 32px -14px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.04);
    -webkit-backdrop-filter: blur(14px);
    backdrop-filter: blur(14px);
`;
