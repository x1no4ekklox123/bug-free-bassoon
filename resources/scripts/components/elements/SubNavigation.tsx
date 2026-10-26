import styled from 'styled-components/macro';
import tw, { theme } from 'twin.macro';

const SubNavigation = styled.div`
    ${tw`w-full overflow-x-auto`};
    background: rgba(17, 13, 34, 0.55);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    -webkit-backdrop-filter: blur(12px);
    backdrop-filter: blur(12px);

    & > div {
        ${tw`flex items-center text-sm mx-auto px-2`};
        max-width: 1200px;

        & > a,
        & > div {
            ${tw`inline-block py-3 px-4 text-neutral-300 no-underline whitespace-nowrap transition-all duration-150`};

            &:not(:first-of-type) {
                ${tw`ml-2`};
            }

            &:hover {
                ${tw`text-white`};
                background-image: linear-gradient(to top, rgba(139, 98, 255, 0.14), transparent);
            }

            &:active,
            &.active {
                ${tw`text-white`};
                background-image: linear-gradient(to top, rgba(139, 98, 255, 0.2), transparent);
                box-shadow: inset 0 -2px ${theme`colors.primary.500`.toString()};
            }
        }
    }
`;

export default SubNavigation;
