import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ServerContext } from '@/state/server';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/components/elements/Field';
import { join, normalize } from 'pathe';
import { object, string } from 'yup';
import createDirectory from '@/api/server/files/createDirectory';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { FileObject } from '@/api/server/files/loadDirectory';
import { useFlashKey } from '@/plugins/useFlash';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { WithClassname } from '@/components/types';
import FlashMessageRender from '@/components/FlashMessageRender';
import { Dialog, DialogProps } from '@/components/elements/dialog';
import Code from '@/components/elements/Code';
import i18n from '@/i18n';

interface Values {
    directoryName: string;
}

const schema = object().shape({
    directoryName: string().required(() => i18n.t('files:directory.name_required')),
});

const displayNameForDirectory = (name: string): string =>
    normalize(name)
        .replace(/^(\.\.\/|\/)+/, '')
        .split('/', 1)[0] || name;

const generateDirectoryData = (name: string): FileObject => {
    const displayName = displayNameForDirectory(name);

    return {
        key: `dir_${displayName}`,
        name: displayName,
        mode: 'drwxr-xr-x',
        modeBits: '0755',
        size: 0,
        isFile: false,
        isSymlink: false,
        mimetype: '',
        createdAt: new Date(),
        modifiedAt: new Date(),
        isArchiveType: () => false,
        isEditable: () => false,
    };
};

const NewDirectoryForm = ({ close }: { close: () => void }) => {
    const { t } = useTranslation('files');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    const { mutate } = useFileManagerSwr();
    const { clearAndAddHttpError } = useFlashKey('files:directory-modal');

    useEffect(() => {
        return () => {
            clearAndAddHttpError();
        };
    }, []);

    const submit = ({ directoryName }: Values, { setSubmitting }: FormikHelpers<Values>) => {
        createDirectory(uuid, directory, directoryName)
            .then(() => mutate((data) => [...data, generateDirectoryData(directoryName)], false))
            .then(() => close())
            .catch((error) => {
                setSubmitting(false);
                clearAndAddHttpError(error);
            });
    };

    return (
        <Formik onSubmit={submit} validationSchema={schema} initialValues={{ directoryName: '' }}>
            {({ submitForm, values }) => (
                <>
                    <FlashMessageRender key={'files:directory-modal'} />
                    <Form css={tw`m-0`}>
                        <Field autoFocus id={'directoryName'} name={'directoryName'} label={t('directory.name')} />
                        <p css={tw`mt-2 text-sm md:text-base break-all`}>
                            <span css={tw`text-neutral-200`}>{t('directory.created_as')}&nbsp;</span>
                            <Code>
                                /home/container/
                                <span css={tw`text-cyan-200`}>
                                    {join(directory, values.directoryName).replace(/^(\.\.\/|\/)+/, '')}
                                </span>
                            </Code>
                        </p>
                    </Form>
                    <Dialog.Footer>
                        <Button.Text className={'w-full sm:w-auto'} onClick={close}>
                            {t('common:cancel')}
                        </Button.Text>
                        <Button className={'w-full sm:w-auto'} onClick={submitForm}>
                            {t('common:create')}
                        </Button>
                    </Dialog.Footer>
                </>
            )}
        </Formik>
    );
};

// Not built with asDialog: its title is fixed when the module loads, before the language is known.
const NewDirectoryDialog = ({ open, onClose }: DialogProps) => {
    const { t } = useTranslation('files');

    return (
        <Dialog open={open} onClose={onClose} title={t('directory.create')}>
            <NewDirectoryForm close={onClose} />
        </Dialog>
    );
};

export default ({ className }: WithClassname) => {
    const { t } = useTranslation('files');
    const [open, setOpen] = useState(false);

    return (
        <>
            <NewDirectoryDialog open={open} onClose={setOpen.bind(this, false)} />
            <Button.Text onClick={setOpen.bind(this, true)} className={className}>
                {t('directory.create')}
            </Button.Text>
        </>
    );
};
