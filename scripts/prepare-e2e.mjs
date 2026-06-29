import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const host = process.env.PLAYWRIGHT_HOST ?? '127.0.0.1';
const port = process.env.PLAYWRIGHT_PORT ?? '8000';
const dbPath = path.resolve('database/e2e.sqlite');

if (! fs.existsSync(path.dirname(dbPath))) {
    fs.mkdirSync(path.dirname(dbPath), { recursive: true });
}

if (! fs.existsSync(dbPath)) {
    fs.closeSync(fs.openSync(dbPath, 'w'));
}

const env = {
    ...process.env,
    APP_ENV: process.env.APP_ENV ?? 'local',
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: dbPath,
    SESSION_DRIVER: 'file',
    CACHE_STORE: 'file',
    QUEUE_CONNECTION: 'sync',
    SUPERADMIN_PASSWORD: process.env.SUPERADMIN_PASSWORD ?? 'password123',
};

execSync('php artisan migrate:fresh --seed --force --no-interaction', {
    stdio: 'inherit',
    env,
});

execSync(`php artisan serve --host=${host} --port=${port} --no-reload`, {
    stdio: 'inherit',
    env,
});
