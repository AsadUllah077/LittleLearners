# Little Learners

A Laravel 12 / Blade preschool learning application with Tailwind CSS, vanilla JavaScript, Lucide icons, and MySQL. Laravel 12 is retained for the installed PHP 8.2 environment; Laravel 13 requires PHP 8.3 ([official release notes](https://laravel.com/framework/docs/releases)).

## Local setup

Requirements: PHP 8.2+, Composer, Node.js, and MySQL/MariaDB. Enable PDO MySQL, SQLite (for tests), fileinfo, mbstring, and the standard Laravel PHP extensions.

```sh
composer install
npm ci
```

On a new checkout, copy `.env.example` to `.env`, generate a key with `php artisan key:generate`, and create an empty UTF-8 database. Set:

```dotenv
APP_NAME="Little Learners"
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kids_learning
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
LEARNING_SPEECH_LANGUAGE=en-US
LEARNING_CONTACT_EMAIL=
```

Then run:

```sh
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan learning:admin admin@example.com "Administrator"
php artisan serve
```

The administrator command securely prompts for a password of at least 12 characters. Sign in through `/login`, then open `/admin/dashboard`. No default administrator password is seeded. For frontend development, run `npm run dev` in another terminal.

This workspace is already configured for its local `kids_learning` MySQL database. Existing learning data was copied from SQLite and table counts verified. The original `database/database.sqlite` remains available; the previous environment file is retained privately under `storage/app/private/env-before-mysql.backup`. The legacy seeded administrator was deactivated because it used the old default password. Run the administrator command above to set a replacement password and reactivate it.

## Features

- Parent registration/login/logout, hashed passwords, session regeneration, login throttling, and inactive-account enforcement.
- Multiple child profiles, age, avatars or validated photo uploads, editable learning levels, password-confirmed profile deletion and progress reset.
- Child dashboard, category progress, resume position, stars, badges, recent activity, and daily/longest streaks.
- Complete A–Z uppercase/lowercase and numbers 1–100, 11 colors, 10 shapes, seven weekdays, twelve months, animals, fruits, vegetables, body parts, family members, and basic words.
- Item-by-item lessons with previous/next, item picker, image display, quantity representations, calendars, color swatches, and shape drawings.
- Uploaded pronunciation audio with browser speech synthesis fallback, configurable language, stop/replay, and playback feedback.
- Responsive pointer-based tracing with dotted guides, colors, brush size, undo, clear/retry, example view, and explicit completion.
- Picture matching, listening, counting, number identification, missing numbers, digit/name matching, lowercase matching, and ordering. Games provide feedback, replay, completion, and server-calculated scores.
- Generated number games suggest 1–5, 1–20, or 1–100 by learning level. Level 1 has fewer choices/questions; all lesson content remains accessible.
- Admin CRUD for categories, lessons, learning items, and games/activities; visual question editor, image/audio uploads, difficulty and active controls, searching, pagination, account deactivation, child editing, and reports.
- Child policies, CSRF protection, validation, safe upload extensions/sizes, foreign keys/indexes, transactions, serialized reward updates, and single-use game completion tokens.

## Progress and rewards

Opening a lesson never marks it complete. Navigation only saves the current position. A child explicitly finishes the lesson to earn one lesson star. Repeated submissions do not duplicate that reward. Tracing and games earn one practice star per activity per calendar day; each valid attempt is recorded. Scores are computed against the server session, not trusted from the browser. Category badges are awarded after all active lessons in the category are complete. Daily streaks encourage practice without removing earned stars.

Tracing checks that submitted points belong to an item in the lesson and contain a nontrivial drawn path. It does not recognize handwriting or certify that a shape/letter was accurately traced. A shown example does not count as a child's stroke.

## Content administration

Use `/admin/learning-items` to upload or replace pictures and pronunciation recordings. Audio supports MP3, WAV, OGG, and M4A up to 10 MB; images support JPEG, PNG, and WebP up to 4 MB (child photos: 2 MB). Uploaded files use Laravel's public disk.

Game questions are edited through labeled form fields; no source-code edits are needed. Choice answers must match an option. Ordering answers list all options in order separated by `|`. Leave questions empty to generate them from the lesson. An optional question image can be uploaded for the first question. Category and lesson visibility is checked on direct URLs as well as listings.

The content seeder is repeatable without duplicate records. It refreshes the bundled item text/order; images/audio remain intact. Rerun it deliberately after editing bundled text. Admin-created categories, lessons, and games are retained.

## Verification

```sh
php artisan test
npm run build
node scripts/browser-test.mjs
```

Feature tests use an isolated in-memory SQLite database. Browser tests create a separate temporary SQLite database, start their own server, and use installed Chrome or Edge. Set `BROWSER_PATH` for another Chromium installation. They cover registration, profile creation, saved position, lowercase, tracing rewards, lesson completion, game completion, and responsive layouts. Screenshots are saved in `storage/app/testing/`. No browser test accounts are added to the application database.

The schema has also been migrated successfully on local MariaDB 10.4 (MySQL-compatible), and the existing data import was verified. The original regression tests are retained.

## Deployment and remaining limits

Serve only the `public/` directory with HTTPS. Configure a dedicated database user/password, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, and secure session cookies. Keep `APP_KEY` stable, protect environment backups, and grant write access only to Laravel's storage/cache directories. Run migrations, build assets, create the storage link, and run `php artisan optimize` during deployment.

Bundled visuals use emoji and CSS/canvas illustrations, so their appearance depends on the device. Administrators can replace them with consistent artwork. Prerecorded pronunciation and animal-sound libraries are not bundled; pronunciation currently uses speech synthesis unless recordings are uploaded. Speech availability depends on browser/device voices. Tracing is basic practice validation, not handwriting recognition. Production hosting, real-device audio/touch acceptance testing, and deployment credentials remain environment-specific work.
