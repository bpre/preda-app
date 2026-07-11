# Single Database Post-Cutover Checklist

## Current state

- Application code on branch `single-database` uses one MySQL connection.
- Local runtime points to `preda_app`.
- Full source backups exist in `storage/app/private/db-backups/2026-03-29-single-database/`.
- Source and target row counts were verified after migration.

## Observation window

- Keep `preda_users` and `preda_website` read-only for 7-14 days if possible.
- Watch application logs, login flow, Filament panels, homepage, blog, and office pages.
- Confirm no background process still writes to the old databases.

## Safe shutdown steps

1. Take one more final backup of `preda_users` and `preda_website`.
2. Confirm `preda_app` row counts and a few key pages still match expectations.
3. Revoke app access to `preda_users` and `preda_website`.
4. Optionally rename old databases to `preda_users_archive_YYYYMMDD` and `preda_website_archive_YYYYMMDD`.
5. Keep archives for the agreed retention period.
6. Drop archived databases only after retention ends and rollback is no longer needed.

## Rollback

- Restore `.env` to the previous database target.
- Switch the deployed code away from `single-database`.
- Restore from the SQL backups if any source database changed unexpectedly.
