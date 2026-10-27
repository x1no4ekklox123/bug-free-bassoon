import * as React from 'react';
import ContentBox from '@/components/elements/ContentBox';
import UpdatePasswordForm from '@/components/dashboard/forms/UpdatePasswordForm';
import UpdateEmailAddressForm from '@/components/dashboard/forms/UpdateEmailAddressForm';
import ConfigureTwoFactorForm from '@/components/dashboard/forms/ConfigureTwoFactorForm';
import UpdateLanguageForm from '@/components/dashboard/forms/UpdateLanguageForm';
import PageContentBlock from '@/components/elements/PageContentBlock';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import MessageBox from '@/components/MessageBox';
import { useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

// The last child of a ContentBox is its card, stretch it so both cards in a row share the same height.
const Grid = styled.div`
    ${tw`grid grid-cols-1 lg:grid-cols-2 gap-8`};

    & > div {
        ${tw`flex flex-col min-w-0`};
    }

    & > div > div:last-child {
        ${tw`flex-1`};
    }
`;

export default () => {
    const { t } = useTranslation('account');
    const { state } = useLocation<undefined | { twoFactorRedirect?: boolean }>();

    return (
        <PageContentBlock title={t('overview.title')}>
            {state?.twoFactorRedirect && (
                <MessageBox title={t('overview.two_factor_required.title')} type={'error'}>
                    {t('overview.two_factor_required.message')}
                </MessageBox>
            )}

            <Grid css={[tw`mb-10`, state?.twoFactorRedirect ? tw`mt-4` : tw`mt-10`]}>
                <ContentBox title={t('password.title')} showFlashes={'account:password'}>
                    <UpdatePasswordForm />
                </ContentBox>
                <ContentBox title={t('email.title')} showFlashes={'account:email'}>
                    <UpdateEmailAddressForm />
                </ContentBox>
                <ContentBox title={t('two_factor.title')}>
                    <ConfigureTwoFactorForm />
                </ContentBox>
                <ContentBox title={t('language.title')} showFlashes={'account:language'}>
                    <UpdateLanguageForm />
                </ContentBox>
            </Grid>
        </PageContentBlock>
    );
};
