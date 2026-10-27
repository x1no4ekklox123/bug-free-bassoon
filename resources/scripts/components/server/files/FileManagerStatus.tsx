import React, { useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { ServerContext } from '@/state/server';
import { CloudUploadIcon, XIcon } from '@heroicons/react/solid';
import { Dialog, DialogProps } from '@/components/elements/dialog';
import { Button } from '@/components/elements/button/index';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import Code from '@/components/elements/Code';
import { useSignal } from '@preact/signals-react';

const svgProps = {
    cx: 16,
    cy: 16,
    r: 14,
    strokeWidth: 3,
    fill: 'none',
    stroke: 'currentColor',
};

const Spinner = ({ progress, className }: { progress: number; className?: string }) => (
    <svg viewBox={'0 0 32 32'} className={className}>
        <circle {...svgProps} className={'opacity-25'} />
        <circle
            {...svgProps}
            stroke={'white'}
            strokeDasharray={28 * Math.PI}
            className={'rotate-[-90deg] origin-[50%_50%] transition-[stroke-dashoffset] duration-300'}
            style={{ strokeDashoffset: ((100 - progress) / 100) * 28 * Math.PI }}
        />
    </svg>
);

const FileUploadList = ({ close }: { close: () => void }) => {
    const { t } = useTranslation('files');
    const cancelFileUpload = ServerContext.useStoreActions((actions) => actions.files.cancelFileUpload);
    const clearFileUploads = ServerContext.useStoreActions((actions) => actions.files.clearFileUploads);
    const uploads = ServerContext.useStoreState((state) =>
        Object.entries(state.files.uploads).sort(([a], [b]) => a.localeCompare(b))
    );

    return (
        <div className={'space-y-2 mt-6'}>
            {uploads.map(([name, file]) => (
                <div
                    key={name}
                    className={'flex items-center space-x-3 bg-gray-700 border border-white/10 p-3 rounded-lg'}
                >
                    <Tooltip content={`${Math.floor((file.loaded / file.total) * 100)}%`} placement={'left'}>
                        <div className={'flex-shrink-0'}>
                            <Spinner progress={(file.loaded / file.total) * 100} className={'w-6 h-6'} />
                        </div>
                    </Tooltip>
                    <Code className={'flex-1 truncate'}>{name}</Code>
                    <button
                        onClick={cancelFileUpload.bind(this, name)}
                        className={'text-gray-500 hover:text-gray-200 transition-colors duration-75'}
                    >
                        <XIcon className={'w-5 h-5'} />
                    </button>
                </div>
            ))}
            <Dialog.Footer>
                <Button.Danger variant={Button.Variants.Secondary} onClick={() => clearFileUploads()}>
                    {t('uploads.cancel')}
                </Button.Danger>
                <Button.Text onClick={close}>{t('common:close')}</Button.Text>
            </Dialog.Footer>
        </div>
    );
};

// Not built with asDialog: its title is fixed when the module loads, before the language is known.
const FileUploadListDialog = ({ open, onClose }: DialogProps) => {
    const { t } = useTranslation('files');

    return (
        <Dialog open={open} onClose={onClose} title={t('uploads.title')} description={t('uploads.description')}>
            <FileUploadList close={onClose} />
        </Dialog>
    );
};

export default () => {
    const { t } = useTranslation('files');
    const open = useSignal(false);

    const count = ServerContext.useStoreState((state) => Object.keys(state.files.uploads).length);
    const progress = ServerContext.useStoreState((state) => ({
        uploaded: Object.values(state.files.uploads).reduce((count, file) => count + file.loaded, 0),
        total: Object.values(state.files.uploads).reduce((count, file) => count + file.total, 0),
    }));

    useEffect(() => {
        if (count === 0) {
            open.value = false;
        }
    }, [count]);

    return (
        <>
            {count > 0 && (
                <Tooltip content={t('uploads.in_progress', { count })}>
                    <button
                        className={'flex items-center justify-center w-10 h-10'}
                        onClick={() => (open.value = true)}
                    >
                        <Spinner progress={(progress.uploaded / progress.total) * 100} className={'w-8 h-8'} />
                        <CloudUploadIcon className={'h-3 absolute mx-auto animate-pulse'} />
                    </button>
                </Tooltip>
            )}
            <FileUploadListDialog open={open.value} onClose={() => (open.value = false)} />
        </>
    );
};
