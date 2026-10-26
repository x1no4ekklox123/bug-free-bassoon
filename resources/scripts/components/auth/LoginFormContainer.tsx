import React, { forwardRef } from 'react';
import { Form } from 'formik';
import styled from 'styled-components/macro';
import { useStoreState } from 'easy-peasy';
import FlashMessageRender from '@/components/FlashMessageRender';
import { ApplicationStore } from '@/state';
import { glassSurface } from '@/assets/css/glass';
import tw from 'twin.macro';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

const Container = styled.div`
    ${tw`w-full mx-auto px-4 py-10 min-h-screen flex flex-col justify-center`};
    max-width: 30rem;
`;

const Card = styled.div`
    ${glassSurface};
    ${tw`relative w-full rounded-2xl p-6 sm:p-8 mx-auto`};
    box-shadow: 0 30px 80px -30px rgba(112, 0, 255, 0.55), 0 12px 32px -14px rgba(0, 0, 0, 0.7),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);

    &::before {
        content: '';
        ${tw`absolute inset-x-8 top-0`};
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(166, 134, 255, 0.9), transparent);
    }
`;

const BrandName = styled.p`
    ${tw`text-3xl font-semibold text-center font-header mt-4`};
    background-image: linear-gradient(90deg, #ffffff 0%, #d3a6ff 55%, #8b62ff 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
`;

const BrandMark = () => (
    <svg viewBox={'0 0 64 64'} css={tw`block mx-auto w-16 h-16`} style={{ filter: 'drop-shadow(0 0 18px rgba(139, 98, 255, 0.7))' }}>
        <defs>
            <linearGradient id={'brand-mark'} x1={'0'} y1={'0'} x2={'1'} y2={'1'}>
                <stop offset={'0%'} stopColor={'#d3a6ff'} />
                <stop offset={'55%'} stopColor={'#8b62ff'} />
                <stop offset={'100%'} stopColor={'#7000ff'} />
            </linearGradient>
        </defs>
        <rect x={'4'} y={'4'} width={'56'} height={'56'} rx={'16'} fill={'url(#brand-mark)'} />
        <path
            d={'M14 44c6-14 12-22 18-22s12 8 18 22'}
            fill={'none'}
            stroke={'#fff'}
            strokeOpacity={'0.95'}
            strokeWidth={'4'}
            strokeLinecap={'round'}
        />
        <path
            d={'M22 44c3-7 6-11 10-11s7 4 10 11'}
            fill={'none'}
            stroke={'#fff'}
            strokeOpacity={'0.55'}
            strokeWidth={'3'}
            strokeLinecap={'round'}
        />
    </svg>
);

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => {
    const name = useStoreState((state: ApplicationStore) => state.settings.data?.name);

    return (
        <Container>
            <div css={tw`mb-6 select-none`}>
                <BrandMark />
                {name && <BrandName>{name}</BrandName>}
            </div>
            <FlashMessageRender css={tw`mb-3`} />
            <Form {...props} ref={ref}>
                <Card>
                    {title && <h2 css={tw`text-xl text-center text-neutral-100 font-medium mb-6`}>{title}</h2>}
                    <div css={tw`w-full`}>{props.children}</div>
                </Card>
            </Form>
            <p css={tw`text-center text-neutral-500 text-xs mt-6`}>
                &copy; 2015 - {new Date().getFullYear()}&nbsp;
                <a
                    rel={'noopener nofollow noreferrer'}
                    href={'https://pterodactyl.io'}
                    target={'_blank'}
                    css={tw`no-underline text-neutral-500 hover:text-primary-300`}
                >
                    Pterodactyl Software
                </a>
            </p>
        </Container>
    );
});
