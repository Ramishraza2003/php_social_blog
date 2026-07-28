# SocialSphere — PHP Social Blogging Platform

An advanced-level PHP + MySQL social/blogging platform built with vanilla PHP (PDO), no framework — designed to demonstrate core backend engineering skills: authentication, security, relational data modeling, and CRUD architecture.

## Features

- **Authentication** — Register/login with bcrypt password hashing, session-based auth, CSRF-protected forms
- **Posts** — Create, edit, delete posts with image uploads (validated: type, size, real image check)
- **Comments** — Threaded per-post comments
- **Likes** — Toggleable like/unlike system
- **Follow system** — Follow/unfollow users, follower/following counts
- **Profiles** — Public profile pages showing a user's posts and stats
- **Search** — Search across posts and users
- **Admin panel** — Manage users (ban/unban, promote/demote) and moderate posts
- **Security** — Prepared statements everywhere (PDO, no raw SQL concatenation), CSRF tokens on all state-changing forms, output escaping (XSS protection), session regeneration on login

## Tech Stack

- PHP 8+ (procedural + PDO, no framework — shows raw fundamentals)
- MySQL / MariaDB
- Vanilla CSS (no framework)
- Vanilla JS (minimal, progressive enhancement only)

## Setup

1. **Requirements**: PHP 8+, MySQL/MariaDB, a local server (XAMPP, WAMP, MAMP, or `php -S`)

2. **Database**:
   - Create the schema by importing `sql/schema.sql` (via phpMyAdmin or CLI):
     ```
     mysql -u root -p < sql/schema.sql
     ```
   - This creates the `social_blog` database, all tables, and a seed admin account.

3. **Configure DB credentials** in `config/database.php` if different from defaults (`root` / no password).

4. **Uploads folders**: Make sure `public/uploads/posts` and `public/uploads/avatars` are writable by the web server.

5. **Run**:
   - If using XAMPP/WAMP: place this folder in `htdocs`/`www` and visit `http://localhost/php_social_blog/public/`
   - Or run PHP's built-in server from the project root:
     ```
     php -S localhost:8000
     ```
     then visit `http://localhost:8000/public/`

6. **Default admin login**:
   - Username: `admin`
   - Password: `Admin@123`
   - **Change this immediately** — the seed hash in `schema.sql` is for demo purposes only.

## Project Structure

```
php_social_blog/
├── admin/              # Admin-only pages (dashboard, user mgmt, post mgmt)
├── config/
│   └── database.php    # PDO connection
├── includes/
│   ├── bootstrap.php   # Session start + dependency loader
│   ├── auth.php        # Login/register/session logic
│   ├── functions.php   # Helpers: CSRF, sanitization, uploads, time formatting
│   ├── header.php       # Shared nav/layout top
│   └── footer.php       # Shared layout bottom
├── public/              # User-facing pages (feed, posts, profile, auth)
│   └── uploads/          # User-uploaded images
├── assets/
│   ├── css/style.css     # All styling
│   └── js/main.js        # Minor UX polish
└── sql/schema.sql        # Full DB schema + seed admin
```

## Notes for Your Portfolio Write-up

Suggested description points to use when showcasing this project:
- Built a full-stack social platform from scratch using vanilla PHP and PDO (no framework), covering authentication, relational data modeling, and secure CRUD operations
- Implemented CSRF protection, password hashing, prepared statements, and input validation/output escaping throughout
- Designed a normalized MySQL schema (users, posts, comments, likes, follows) with foreign keys and unique constraints
- Built an admin moderation panel for user/content management

## Possible Extensions (mention as "future improvements" if asked)

- REST API layer (JSON endpoints) for a mobile client
- Notifications system (likes/comments/follows)
- Private messaging
- Email verification on registration
