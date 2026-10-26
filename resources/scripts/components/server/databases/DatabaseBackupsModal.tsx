import React, { useEffect, useState } from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import { format, formatDistanceToNow } from 'date-fns';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faCheckCircle,
    faCloudDownloadAlt,
    faExclamationTriangle,
    faHistory,
    faTrashAlt,
} from '@fortawesome/free-solid-svg-icons';
import Modal from '@/components/elements/Modal';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Spinner from '@/components/elements/Spinner';
import Can from '@/components/elements/Can';
import FlashMessageRender from '@/components/FlashMessageRender';
import { ServerContext } from '@/state/server';
import { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import { httpErrorToHuman } from '@/api/http';
import { bytesToString } from '@/lib/formatters';
import useFlash from '@/plugins/useFlash';
import {
    createDatabaseBackup,
    DatabaseBackup,
    deleteDatabaseBackup,
    getDatabaseBackupDownloadUrl,
    getDatabaseBackups,
    restoreDatabaseBackup,
} from '@/api/server/databases/backups';

interface Props {
    database: ServerDatabase;
    visible: boolean;
    onDismissed: () => void;
}

type PendingAction = { type: 'restore' | 'delete'; backup: DatabaseBackup } | null;

const FLASH_KEY = 'database:backups';

const isBusy = (backup: DatabaseBackup) => backup.status === 'pending' || backup.restoreStatus === 'restoring';

const StatusIcon = ({ backup }: { backup: DatabaseBackup }) => {
    if (isBusy(backup)) {
        return <Spinner size={'small'} />;
    }

    if (backup.status === 'failed' || backup.restoreStatus === 'failed') {
        return <FontAwesomeIcon icon={faExclamationTriangle} css={tw`text-red-400`} fixedWidth />;
    }

    return <FontAwesomeIcon icon={faCheckCircle} css={tw`text-green-400`} fixedWidth />;
};

const describe = (backup: DatabaseBackup): string => {
    if (backup.status === 'pending') return 'Creating backup…';
    if (backup.restoreStatus === 'restoring') return 'Restoring…';
    if (backup.status === 'failed') return 'Backup failed';
    if (backup.restoreStatus === 'failed') return 'Last restore failed';
    if (backup.restoreStatus === 'completed' && backup.restoredAt) {
        return `Restored ${formatDistanceToNow(backup.restoredAt, { addSuffix: true })}`;
    }

    return bytesToString(backup.bytes);
};

export default ({ database, visible, onDismissed }: Props) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useFlash();
    const [name, setName] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [pending, setPending] = useState<PendingAction>(null);
    const [polling, setPolling] = useState(false);

    const { data, error, mutate } = useSWR(
        visible ? ['server:database-backups', uuid, database.id] : null,
        () => getDatabaseBackups(uuid, database.id),
        {
            // Keep polling while a dump or restore is running in the queue.
            refreshInterval: polling ? 3000 : 0,
            revalidateOnFocus: false,
        }
    );

    useEffect(() => {
        setPolling(!!data?.backups.some(isBusy));
    }, [data]);

    const backups = data?.backups || [];
    const busy = backups.some(isBusy);
    const used = backups.filter((backup) => backup.status !== 'failed').length;
    const limitReached = !!data && data.limit > 0 && used >= data.limit;

    const run = (action: () => Promise<unknown>) => {
        clearFlashes(FLASH_KEY);
        setSubmitting(true);
        action()
            .then(() => mutate())
            .catch((error) => {
                console.error(error);
                addError({ key: FLASH_KEY, message: httpErrorToHuman(error) });
            })
            .then(() => {
                setSubmitting(false);
                setPending(null);
            });
    };

    const create = () =>
        run(() =>
            createDatabaseBackup(uuid, database.id, name.trim() || undefined).then(() => {
                setName('');
            })
        );

    const download = (backup: DatabaseBackup) =>
        run(() =>
            getDatabaseBackupDownloadUrl(uuid, database.id, backup.uuid).then((url) => {
                // @ts-expect-error this is valid
                window.location = url;
            })
        );

    const confirm = () => {
        if (!pending) return;

        const { type, backup } = pending;
        run(() =>
            type === 'restore'
                ? restoreDatabaseBackup(uuid, database.id, backup.uuid)
                : deleteDatabaseBackup(uuid, database.id, backup.uuid)
        );
    };

    return (
        <Modal
            visible={visible}
            onDismissed={() => {
                setPending(null);
                clearFlashes(FLASH_KEY);
                onDismissed();
            }}
            showSpinnerOverlay={submitting}
        >
            <div css={tw`flex items-center mb-1`}>
                <FontAwesomeIcon icon={faHistory} css={tw`text-primary-400 mr-3`} fixedWidth />
                <h3 css={tw`text-2xl break-all`}>Backups of {database.name}</h3>
            </div>
            <p css={tw`text-sm text-neutral-400 mb-6`}>
                Backups are compressed SQL dumps stored in S3.
                {data && data.limit > 0 && ` ${used} of ${data.limit} backups used.`}
            </p>
            <FlashMessageRender byKey={FLASH_KEY} css={tw`mb-4`} />
            {error && !data ? (
                <p css={tw`text-sm text-red-400`}>{httpErrorToHuman(error)}</p>
            ) : !data ? (
                <Spinner size={'large'} centered />
            ) : (
                <>
                    {!data.enabled && (
                        <p css={tw`mb-4 text-sm text-yellow-300`}>
                            Database backups are currently disabled by the administrator.
                        </p>
                    )}
                    <Can action={'database.backup'}>
                        <form
                            css={tw`flex flex-col sm:flex-row mb-6`}
                            onSubmit={(e) => {
                                e.preventDefault();
                                create();
                            }}
                        >
                            <Input
                                type={'text'}
                                value={name}
                                maxLength={191}
                                placeholder={'Backup name (optional)'}
                                onChange={(e) => setName(e.currentTarget.value)}
                                disabled={!data.enabled}
                            />
                            <Button
                                type={'submit'}
                                css={tw`mt-3 sm:mt-0 sm:ml-3 flex-shrink-0`}
                                disabled={!data.enabled || busy || limitReached || submitting}
                            >
                                Create Backup
                            </Button>
                        </form>
                        {limitReached && (
                            <p css={tw`-mt-4 mb-6 text-xs text-neutral-400`}>
                                The backup limit for this database has been reached, delete an older backup first.
                            </p>
                        )}
                    </Can>
                    {backups.length === 0 ? (
                        <p css={tw`text-center text-sm text-neutral-400 py-6`}>
                            There are no backups for this database yet.
                        </p>
                    ) : (
                        <div css={tw`space-y-2`}>
                            {backups.map((backup) => (
                                <div key={backup.uuid} css={tw`rounded-lg bg-neutral-700 px-4 py-3`}>
                                    <div css={tw`flex items-center`}>
                                        <div css={tw`w-6 flex-shrink-0 flex justify-center`}>
                                            <StatusIcon backup={backup} />
                                        </div>
                                        <div css={tw`ml-3 flex-1 min-w-0`}>
                                            <p css={tw`text-sm truncate`} title={backup.name}>
                                                {backup.name}
                                            </p>
                                            <p css={tw`text-xs text-neutral-400 mt-1`}>
                                                <span title={format(backup.createdAt, 'ddd, MMMM do, yyyy HH:mm:ss')}>
                                                    {formatDistanceToNow(backup.createdAt, { addSuffix: true })}
                                                </span>
                                                <span css={tw`mx-2`}>&middot;</span>
                                                {describe(backup)}
                                            </p>
                                        </div>
                                        <div css={tw`ml-3 flex flex-shrink-0`}>
                                            {backup.status === 'completed' && (
                                                <Can action={'database.backup'}>
                                                    <Button
                                                        size={'xsmall'}
                                                        isSecondary
                                                        title={'Download'}
                                                        disabled={!data.enabled}
                                                        onClick={() => download(backup)}
                                                    >
                                                        <FontAwesomeIcon icon={faCloudDownloadAlt} fixedWidth />
                                                    </Button>
                                                </Can>
                                            )}
                                            {backup.status === 'completed' && (
                                                <Can action={'database.backup_restore'}>
                                                    <Button
                                                        size={'xsmall'}
                                                        isSecondary
                                                        css={tw`ml-2`}
                                                        title={'Restore'}
                                                        disabled={!data.enabled || busy}
                                                        onClick={() => setPending({ type: 'restore', backup })}
                                                    >
                                                        <FontAwesomeIcon icon={faHistory} fixedWidth />
                                                    </Button>
                                                </Can>
                                            )}
                                            <Can action={'database.backup_delete'}>
                                                <Button
                                                    size={'xsmall'}
                                                    color={'red'}
                                                    isSecondary
                                                    css={tw`ml-2`}
                                                    title={'Delete'}
                                                    disabled={isBusy(backup)}
                                                    onClick={() => setPending({ type: 'delete', backup })}
                                                >
                                                    <FontAwesomeIcon icon={faTrashAlt} fixedWidth />
                                                </Button>
                                            </Can>
                                        </div>
                                    </div>
                                    {backup.error && (backup.status === 'failed' || backup.restoreStatus === 'failed') && (
                                        <p css={tw`mt-2 text-xs text-red-300`}>{backup.error}</p>
                                    )}
                                    {pending?.backup.uuid === backup.uuid && (
                                        <div css={tw`mt-3 rounded-md bg-neutral-800 p-3`}>
                                            <p css={tw`text-sm`}>
                                                {pending.type === 'restore'
                                                    ? `All current data in ${database.name} will be replaced with the contents of this backup. This cannot be undone.`
                                                    : 'This backup will be permanently deleted from storage.'}
                                            </p>
                                            <div css={tw`mt-3 flex justify-end`}>
                                                <Button size={'xsmall'} isSecondary onClick={() => setPending(null)}>
                                                    Cancel
                                                </Button>
                                                <Button
                                                    size={'xsmall'}
                                                    color={'red'}
                                                    css={tw`ml-2`}
                                                    disabled={submitting}
                                                    onClick={confirm}
                                                >
                                                    {pending.type === 'restore' ? 'Restore Backup' : 'Delete Backup'}
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </>
            )}
            <div css={tw`mt-6 text-right`}>
                <Button isSecondary onClick={onDismissed}>
                    Close
                </Button>
            </div>
        </Modal>
    );
};
