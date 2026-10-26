import styled from 'styled-components/macro';
import tw from 'twin.macro';
import { glassSurface } from '@/assets/css/glass';

export default styled.div<{ $hoverable?: boolean }>`
    ${glassSurface};
    ${tw`flex rounded-xl no-underline text-neutral-200 items-center p-4 transition-all duration-150 overflow-hidden`};

    ${(props) =>
        props.$hoverable !== false &&
        `
        &:hover {
            border-color: rgba(166, 134, 255, 0.45);
            box-shadow: 0 12px 32px -14px rgba(0, 0, 0, 0.65), 0 0 24px -8px rgba(139, 98, 255, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
    `};

    & .icon {
        ${tw`rounded-full w-16 flex items-center justify-center p-3 text-primary-300`};
        background-image: linear-gradient(135deg, rgba(139, 98, 255, 0.28), rgba(112, 0, 255, 0.12));
        border: 1px solid rgba(166, 134, 255, 0.25);
    }
`;
