import http from '@/api/http';

export type DatabaseBackupStatus = 'pending' | 'completed' | 'failed';
export type DatabaseBackupRestoreStatus = 'restoring' | 'completed' | 'failed' | null;

export interface DatabaseBackup {
    uuid: string;
    name: string;
    status: DatabaseBackupStatus;
    restoreStatus: DatabaseBackupRestoreStatus;
    bytes: number;
    checksum: string | null;
    error: string | null;
    createdAt: Date;
    completedAt: Date | null;
    restoredAt: Date | null;
}

export interface DatabaseBackupList {
    backups: DatabaseBackup[];
    enabled: boolean;
    limit: number;
}

export const rawDataToDatabaseBackup = (data: any): DatabaseBackup => ({
    uuid: data.uuid,
    name: data.name,
    status: data.status,
    restoreStatus: data.restore_status,
    bytes: data.bytes,
    checksum: data.checksum,
    error: data.error,
    createdAt: new Date(data.created_at),
    completedAt: data.completed_at ? new Date(data.completed_at) : null,
    restoredAt: data.restored_at ? new Date(data.restored_at) : null,
});

const endpoint = (uuid: string, database: string) => `/api/client/servers/${uuid}/databases/${database}/backups`;

export const getDatabaseBackups = async (uuid: string, database: string): Promise<DatabaseBackupList> => {
    const { data } = await http.get(endpoint(uuid, database));

    return {
        backups: (data.data || []).map((item: any) => rawDataToDatabaseBackup(item.attributes)),
        enabled: Boolean(data.meta?.enabled),
        limit: Number(data.meta?.limit || 0),
    };
};

export const createDatabaseBackup = async (uuid: string, database: string, name?: string): Promise<DatabaseBackup> => {
    const { data } = await http.post(endpoint(uuid, database), { name: name || null });

    return rawDataToDatabaseBackup(data.attributes);
};

export const getDatabaseBackupDownloadUrl = async (uuid: string, database: string, backup: string): Promise<string> => {
    const { data } = await http.get(`${endpoint(uuid, database)}/${backup}/download`);

    return data.attributes.url;
};

export const restoreDatabaseBackup = async (uuid: string, database: string, backup: string): Promise<void> => {
    await http.post(`${endpoint(uuid, database)}/${backup}/restore`);
};

export const deleteDatabaseBackup = async (uuid: string, database: string, backup: string): Promise<void> => {
    await http.delete(`${endpoint(uuid, database)}/${backup}`);
};
