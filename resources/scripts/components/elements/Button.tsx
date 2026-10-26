import React from 'react';
import styled, { css } from 'styled-components/macro';
import tw from 'twin.macro';
import Spinner from '@/components/elements/Spinner';

interface Props {
    isLoading?: boolean;
    size?: 'xsmall' | 'small' | 'large' | 'xlarge';
    color?: 'green' | 'red' | 'primary' | 'grey';
    isSecondary?: boolean;
}

const ButtonStyle = styled.button<Omit<Props, 'isLoading'>>`
    ${tw`relative inline-block rounded-lg p-2 uppercase tracking-wide text-sm font-medium transition-all duration-150 border`};

    ${(props) =>
        ((!props.isSecondary && !props.color) || props.color === 'primary') &&
        css<Props>`
            ${(props) =>
                !props.isSecondary &&
                css`
                    ${tw`border-white/10 text-white`};
                    background-image: linear-gradient(135deg, #8b62ff 0%, #7000ff 100%);
                    box-shadow: 0 6px 18px -6px rgba(139, 98, 255, 0.65);
                `};

            &:hover:not(:disabled) {
                ${(props) =>
                    !props.isSecondary &&
                    css`
                        filter: brightness(1.12);
                        box-shadow: 0 8px 24px -6px rgba(139, 98, 255, 0.85);
                    `};
            }
        `};

    ${(props) =>
        props.color === 'grey' &&
        css`
            ${tw`border-white/10 bg-white/5 text-neutral-100`};

            &:hover:not(:disabled) {
                ${tw`border-white/20 bg-white/10`};
            }
        `};

    ${(props) =>
        props.color === 'green' &&
        css<Props>`
            ${tw`border-green-500/40 bg-green-500/80 text-white`};

            &:hover:not(:disabled) {
                ${tw`bg-green-500 border-green-400/60`};
            }

            ${(props) =>
                props.isSecondary &&
                css`
                    &:active:not(:disabled) {
                        ${tw`bg-green-600 border-green-700`};
                    }
                `};
        `};

    ${(props) =>
        props.color === 'red' &&
        css<Props>`
            ${tw`border-red-500/40 bg-red-500/80 text-white`};

            &:hover:not(:disabled) {
                ${tw`bg-red-500 border-red-400/60`};
            }

            ${(props) =>
                props.isSecondary &&
                css`
                    &:active:not(:disabled) {
                        ${tw`bg-red-600 border-red-700`};
                    }
                `};
        `};

    ${(props) => props.size === 'xsmall' && tw`px-2 py-1 text-xs`};
    ${(props) => (!props.size || props.size === 'small') && tw`px-4 py-2`};
    ${(props) => props.size === 'large' && tw`p-4 text-sm`};
    ${(props) => props.size === 'xlarge' && tw`p-4 w-full`};

    ${(props) =>
        props.isSecondary &&
        css<Props>`
            ${tw`border-white/15 bg-transparent text-neutral-200`};

            &:hover:not(:disabled) {
                ${tw`border-white/30 text-white`};
                ${(props) => props.color === 'red' && tw`bg-red-500 border-red-400 text-white`};
                ${(props) => props.color === 'primary' && tw`bg-primary-500/90 border-primary-400 text-white`};
                ${(props) => props.color === 'green' && tw`bg-green-500 border-green-400 text-white`};
            }
        `};

    &:disabled {
        opacity: 0.5;
        cursor: default;
        box-shadow: none;
    }
`;

type ComponentProps = Omit<JSX.IntrinsicElements['button'], 'ref' | keyof Props> & Props;

const Button: React.FC<ComponentProps> = ({ children, isLoading, ...props }) => (
    <ButtonStyle {...props}>
        {isLoading && (
            <div css={tw`flex absolute justify-center items-center w-full h-full left-0 top-0`}>
                <Spinner size={'small'} />
            </div>
        )}
        <span css={isLoading ? tw`text-transparent` : undefined}>{children}</span>
    </ButtonStyle>
);

type LinkProps = Omit<JSX.IntrinsicElements['a'], 'ref' | keyof Props> & Props;

const LinkButton: React.FC<LinkProps> = (props) => <ButtonStyle as={'a'} {...props} />;

export { LinkButton, ButtonStyle };
export default Button;
