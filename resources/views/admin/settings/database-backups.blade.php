@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'database-backups'])

@section('title')
    Database Backups
@endsection

@section('content-header')
    <h1>Database Backups<small>Store dumps of server databases in S3 compatible storage.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Settings</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <div class="callout callout-info">
                <p>
                    When enabled, users with the <code>database.backup</code> permission can create, download and restore backups
                    of their server databases from the <strong>Databases</strong> tab. Dumps are created by the Panel's queue worker
                    (<code>pteroq</code>), so it must be running. The database host user needs the same privileges described in the
                    official documentation (<code>ALL PRIVILEGES ... WITH GRANT OPTION</code>) so views, triggers and routines can be restored.
                </p>
            </div>
        </div>
        <div class="col-xs-12">
            <form action="{{ route('admin.settings.database-backups') }}" method="POST">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">General</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">Status</label>
                                <div>
                                    <select class="form-control" name="pterodactyl:database_backups:enabled">
                                        <option value="false">Disabled</option>
                                        <option value="true" @if(old('pterodactyl:database_backups:enabled', $enabled ? 'true' : 'false') === 'true') selected @endif>Enabled</option>
                                    </select>
                                    <p class="text-muted small">
                                        @if($enabled && $configured)
                                            <span class="text-success"><i class="fa fa-check"></i> Database backups are currently available to users.</span>
                                        @else
                                            Database backups are currently not available to users.
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Backups per Database</label>
                                <div>
                                    <input type="number" min="0" max="1000" required class="form-control" name="pterodactyl:database_backups:limit" value="{{ old('pterodactyl:database_backups:limit', config('pterodactyl.database_backups.limit')) }}">
                                    <p class="text-muted small">Maximum number of stored backups for a single database. Set to <code>0</code> for no limit.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">S3 Storage</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">Endpoint <span class="field-optional"></span></label>
                                <div>
                                    <input type="text" class="form-control" name="pterodactyl:database_backups:s3:endpoint" placeholder="https://s3.eu-central-1.amazonaws.com" value="{{ old('pterodactyl:database_backups:s3:endpoint', config('pterodactyl.database_backups.s3.endpoint')) }}">
                                    <p class="text-muted small">Leave empty for AWS S3. Set it for MinIO, Cloudflare R2, Backblaze B2, Wasabi, Selectel, Timeweb and other S3 compatible providers.</p>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Region</label>
                                <div>
                                    <input type="text" class="form-control" name="pterodactyl:database_backups:s3:region" placeholder="us-east-1" value="{{ old('pterodactyl:database_backups:s3:region', config('pterodactyl.database_backups.s3.region')) }}">
                                    <p class="text-muted small">Use <code>auto</code> for Cloudflare R2.</p>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Path Style Endpoint</label>
                                <div>
                                    <select class="form-control" name="pterodactyl:database_backups:s3:use_path_style_endpoint">
                                        <option value="false">Disabled</option>
                                        <option value="true" @if(old('pterodactyl:database_backups:s3:use_path_style_endpoint', filter_var(config('pterodactyl.database_backups.s3.use_path_style_endpoint'), FILTER_VALIDATE_BOOL) ? 'true' : 'false') === 'true') selected @endif>Enabled</option>
                                    </select>
                                    <p class="text-muted small">Required by MinIO and some self-hosted providers.</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">Bucket</label>
                                <div>
                                    <input type="text" class="form-control" name="pterodactyl:database_backups:s3:bucket" value="{{ old('pterodactyl:database_backups:s3:bucket', config('pterodactyl.database_backups.s3.bucket')) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">Path Prefix <span class="field-optional"></span></label>
                                <div>
                                    <input type="text" class="form-control" name="pterodactyl:database_backups:s3:prefix" value="{{ old('pterodactyl:database_backups:s3:prefix', config('pterodactyl.database_backups.s3.prefix')) }}">
                                    <p class="text-muted small">Objects are stored as <code>prefix/server-uuid/database/backup-uuid.sql.gz</code>.</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">Access Key ID</label>
                                <div>
                                    <input type="text" class="form-control" autocomplete="off" name="pterodactyl:database_backups:s3:key" value="{{ old('pterodactyl:database_backups:s3:key', config('pterodactyl.database_backups.s3.key')) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">Secret Access Key</label>
                                <div>
                                    <input type="password" class="form-control" autocomplete="new-password" name="pterodactyl:database_backups:s3:secret" placeholder="{{ $hasSecret ? 'Leave empty to keep the current secret' : '' }}">
                                    <p class="text-muted small">Stored encrypted with the Panel's application key.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {{ csrf_field() }}
                        <button type="submit" name="_method" value="PATCH" class="btn btn-sm btn-primary pull-right">Save</button>
                        <button type="submit" form="database-backups-test" class="btn btn-sm btn-default pull-right" style="margin-right: 8px;" @if(!$configured) disabled @endif>Test Connection</button>
                    </div>
                </div>
            </form>
            <form id="database-backups-test" action="{{ route('admin.settings.database-backups.test') }}" method="POST">
                {{ csrf_field() }}
            </form>
        </div>
    </div>
@endsection
