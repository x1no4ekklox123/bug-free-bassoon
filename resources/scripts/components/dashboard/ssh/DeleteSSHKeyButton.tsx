import tw from 'twin.macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import React, { useState } from 'react';
import { useFlashKey } from '@/plugins/useFlash';
import { deleteSSHKey, useSSHKeys } from '@/api/account/ssh-keys';
import { Dialog } from '@/components/elements/dialog';
import Code from '@/components/elements/Code';
import { Trans, useTranslation } from 'react-i18next';

export default ({ name, fingerprint }: { name: string; fingerprint: string }) => {
    const { t } = useTranslation('account');
    const { clearAndAddHttpError } = useFlashKey('account');
    const [visible, setVisible] = useState(false);
    const { mutate } = useSSHKeys();

    const onClick = () => {
        clearAndAddHttpError();

        Promise.all([
            mutate((data) => data?.filter((value) => value.fingerprint !== fingerprint), false),
            deleteSSHKey(fingerprint),
        ]).catch((error) => {
            mutate(undefined, true).catch(console.error);
            clearAndAddHttpError(error);
        });
    };

    return (
        <>
            <Dialog.Confirm
                open={visible}
                title={t('ssh.delete.title')}
                confirm={t('ssh.delete.confirm')}
                onConfirmed={onClick}
                onClose={() => setVisible(false)}
            >
                <Trans t={t} i18nKey={'ssh.delete.message'} values={{ name }} components={{ code: <Code /> }} />
            </Dialog.Confirm>
            <button css={tw`ml-4 p-2 text-sm`} onClick={() => setVisible(true)}>
                <FontAwesomeIcon
                    icon={faTrashAlt}
                    css={tw`text-neutral-400 hover:text-red-400 transition-colors duration-150`}
                />
            </button>
        </>
    );
};
