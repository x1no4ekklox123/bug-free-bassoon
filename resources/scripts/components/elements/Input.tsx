import styled, { css } from 'styled-components/macro';
import tw from 'twin.macro';

export interface Props {
    hasError?: boolean;
}

const checkboxStyle = css<Props>`
    ${tw`cursor-pointer appearance-none inline-block align-middle select-none flex-shrink-0 w-4 h-4 text-primary-500 border rounded`};
    background-color: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.25);
    color-adjust: exact;
    background-origin: border-box;
    transition: all 75ms linear, box-shadow 25ms linear;

    &:hover:not(:disabled) {
        border-color: rgba(166, 134, 255, 0.7);
    }

    &:checked {
        ${tw`bg-no-repeat bg-center`};
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M5.707 7.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4a1 1 0 0 0-1.414-1.414L7 8.586 5.707 7.293z'/%3e%3c/svg%3e");
        background-color: currentColor;
        background-size: 100% 100%;
        border-color: rgba(166, 134, 255, 0.9);
        box-shadow: 0 0 10px -2px rgba(139, 98, 255, 0.6);
    }

    &:focus {
        ${tw`outline-none`};
        border-color: rgba(166, 134, 255, 0.9);
        box-shadow: 0 0 0 3px rgba(139, 98, 255, 0.28);
    }

    &:disabled {
        ${tw`opacity-60 cursor-not-allowed`};
    }
`;

const inputStyle = css<Props>`
    // Reset to normal styling.
    resize: none;
    ${tw`appearance-none outline-none w-full min-w-0`};
    ${tw`p-3 border rounded-lg text-sm transition-all duration-150`};
    ${tw`text-neutral-100 shadow-none focus:ring-0`};
    background-color: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.1);

    &:hover:not(:disabled):not(:focus) {
        border-color: ${(props) => (props.hasError ? 'rgb(252, 165, 165)' : 'rgba(166, 134, 255, 0.4)')};
    }

    & + .input-help {
        ${tw`mt-1 text-xs`};
        ${(props) => (props.hasError ? tw`text-red-200` : tw`text-neutral-200`)};
    }

    &:required,
    &:invalid {
        ${tw`shadow-none`};
    }

    &:not(:disabled):not(:read-only):focus {
        ${tw`ring-2 ring-primary-500 ring-opacity-30`};
        background-color: rgba(139, 98, 255, 0.07);
        border-color: rgba(166, 134, 255, 0.85);
        ${(props) => props.hasError && tw`border-red-400 ring-red-400 ring-opacity-30`};
    }

    &:disabled {
        ${tw`opacity-75`};
    }

    ${(props) => props.hasError && tw`text-red-100 border-red-400 hover:border-red-300`};
`;

const Input = styled.input<Props>`
    &:not([type='checkbox']):not([type='radio']) {
        ${inputStyle};
    }

    &[type='checkbox'],
    &[type='radio'] {
        ${checkboxStyle};

        &[type='radio'] {
            ${tw`rounded-full`};
        }
    }
`;
const Textarea = styled.textarea<Props>`
    ${inputStyle}
`;

export { Textarea };
export default Input;
