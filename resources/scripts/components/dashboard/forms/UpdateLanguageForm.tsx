import React, { useState } from 'react';
import { Actions, State, useStoreActions, useStoreState } from 'easy-peasy';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import updateAccountLanguage from '@/api/account/updateAccountLanguage';
import Label from '@/components/elements/Label';
import Select from '@/components/elements/Select';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import { Button } from '@/components/elements/button/index';

export default () => {
    const { t, i18n } = useTranslation('account');
    const current = useStoreState((state: State<ApplicationStore>) => state.user.data!.language);
    const languages = useStoreState((state: State<ApplicationStore>) => state.settings.data?.languages) || {
        en: 'English',
    };
    const updateUserData = useStoreActions((actions: Actions<ApplicationStore>) => actions.user.updateUserData);
    const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const [language, setLanguage] = useState(languages[current] ? current : Object.keys(languages)[0]);
    const [submitting, setSubmitting] = useState(false);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        clearFlashes('account:language');
        setSubmitting(true);

        updateAccountLanguage(language)
            .then(() => i18n.changeLanguage(language))
            .then(() => {
                updateUserData({ language });
                addFlash({ type: 'success', key: 'account:language', message: t('language.updated') });
            })
            .catch((error) =>
                addFlash({
                    type: 'error',
                    key: 'account:language',
                    title: t('common:error'),
                    message: httpErrorToHuman(error),
                })
            )
            .then(() => setSubmitting(false));
    };

    return (
        <form css={tw`m-0 relative`} onSubmit={submit}>
            <SpinnerOverlay size={'large'} visible={submitting} />
            <Label htmlFor={'account_language'}>{t('language.label')}</Label>
            <Select
                id={'account_language'}
                name={'language'}
                value={language}
                onChange={(e) => setLanguage(e.currentTarget.value)}
            >
                {Object.entries(languages).map(([code, name]) => (
                    <option key={code} value={code}>
                        {name}
                    </option>
                ))}
            </Select>
            <p css={tw`mt-2 text-xs text-neutral-400`}>{t('language.description')}</p>
            <div css={tw`mt-6`}>
                <Button type={'submit'} disabled={submitting || language === current}>
                    {t('language.save')}
                </Button>
            </div>
        </form>
    );
};
