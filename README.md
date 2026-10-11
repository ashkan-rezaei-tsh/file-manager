# LaraStorage — Google Drive-like Cloud File Manager

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 10">
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Inertia.js-1.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Vite-4.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/Docker-Sail-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker Sail">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0">
</p>

---

## 📖 Overview

**LaraStorage** is a modern, responsive, full-featured cloud file manager inspired by **Google Drive**. Built as a monolithic Single Page Application (SPA) using **Laravel 10**, **Inertia.js**, and **Vue 3**, it delivers a desktop-like user experience with seamless cloud storage operations, drag-and-drop file/folder uploads, sharing capabilities, and dynamic on-the-fly zip archiving.

---

## ✨ Features

- 📁 **Hierarchical Folder Structure**:
  - Infinite nested folder hierarchy powered by the Modified Preorder Tree Traversal (MPTT) algorithm via `kalnoy/nestedset`.
  - Dynamic breadcrumb navigation for jumping through ancestor folders.
  - Double-click folder navigation.
  - Dedicated personal root directory automatically initialized upon user registration.

- 📤 **Versatile File & Directory Uploads**:
  - Single and multi-file uploading.
  - **Full directory upload** (`webkitdirectory`) preserving complete folder tree hierarchies recursively.
  - **Drag & drop uploads** with visual full-page dropzone overlay.
  - Real-time upload progress tracking and toast notifications.

- 👥 **File & Folder Sharing**:
  - Share files and directories with other registered users via email address.
  - Asynchronous background queue job (`SendShareFileNotificationJob`) dispatches notification emails (`SharedFilesMail`).
  - Dedicated views for **"Shared with Me"** and **"Shared by Me"**.

- ⭐ **Favorites / Starred Files**:
  - Star important files and folders for quick access.
  - Dedicated **"My Favorites"** section.

- 🗑️ **Two-Stage Trash & Recovery**:
  - Safe deletion: files move to Trash first (soft deleted).
  - One-click file and folder restoration.
  - Permanent purge (`deleteForever`) with automatic disk cleanup (`Storage::delete`).

- 📦 **Bulk Operations & Automatic Zip Archiving**:
  - Multi-selection support (single click selection, checkbox toggle, and "Select All").
  - On-the-fly packaging of selected files or entire folder trees into `.zip` archives via `ZipArchive`.
  - Batch download, batch sharing, and batch deletion.

- 🔍 **Search & Infinite Scrolling**:
  - Real-time debounced file searching across names and paths.
  - Smooth infinite scroll pagination using the browser's native **Intersection Observer API**.

- 🎨 **Polished Google Drive-like Interface**:
  - Clean UI built with **Tailwind CSS**.
  - Accessible dropdown menus and modals using **Headless UI**.
  - File-type specific icon previews (Images, PDFs, Word, Excel, Audio, Video, Zip archives, Text/Code).
  - Decoupled frontend reactive communication powered by **Mitt** (event bus).

- 🔐 **Authentication & Profile Management**:
  - Authentication scaffolding via **Laravel Breeze** (Registration, Login, Password Reset, Email Verification).
  - User profile editing and account deletion.

---

## 🛠️ Technology Stack

### Backend
| Technology | Description |
|---|---|
| **[PHP 8.2](https://www.php.net/)** | Core programming language |
| **[Laravel 10](https://laravel.com/)** | Full-stack PHP web framework |
| **[Inertia.js (Laravel Adapter)](https://inertiajs.com/)** | Server-driven SPA bridge between Laravel and Vue 3 |
| **[Kalnoy NestedSet](https://github.com/lazychaser/laravel-nestedset)** | Nested set model implementation for trees / nested folder hierarchies |
| **[Laravel Sanctum](https://laravel.com/docs/10.x/sanctum)** | API and session authentication |
| **[Laravel Breeze](https://laravel.com/docs/10.x/starter-kits#laravel-breeze)** | Authentication scaffolding |
| **[Tighten Ziggy](https://github.com/tighten/ziggy)** | Exposes Laravel routes directly to JavaScript / Vue |
| **[PHP ZipArchive](https://www.php.net/manual/en/class.ziparchive.php)** | Dynamic compression of directories and multi-file downloads |

### Frontend
| Technology | Description |
|---|---|
| **[Vue 3](https://vuejs.org/)** | Reactive frontend framework (Composition API with `<script setup>`) |
| **[Inertia.js (Vue 3 Adapter)](https://inertiajs.com/)** | Client-side routing and page state management |
| **[Vite 4](https://vitejs.dev/)** | Fast frontend bundler and HMR dev server |
| **[Tailwind CSS 3](https://tailwindcss.com/)** | Utility-first CSS styling and form styling plugin |
| **[Headless UI](https://headlessui.com/)** | Unstyled, accessible UI components (Menus, Modals, Transitions) |
| **[Heroicons](https://heroicons.com/)** | Modern SVG icons |
| **[Mitt](https://github.com/developit/mitt)** | Microscopic 200b functional event emitter / event bus |
| **[Axios](https://axios-http.com/)** | Promise-based HTTP client for asynchronous background requests |

### Infrastructure & Services (Docker / Laravel Sail)
| Service | Image | Purpose |
|---|---|---|
| **App Service** | `sail-8.2/app` | PHP 8.2 container running Laravel & Vite |
| **Database** | `mysql/mysql-server:8.0` | Primary relational database |
| **Cache & Queue** | `redis:alpine` | Fast in-memory key-value store for queues and caching |
| **Search Engine** | `getmeili/meilisearch:latest` | Fast, open-source search engine |
| **Email Testing** | `axllent/mailpit:latest` | Local SMTP server and webmail dashboard |
| **E2E Testing** | `selenium/standalone-chrome` | Chrome browser automation for testing |

---

## 🚀 Getting Started

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine on Linux)
- [WSL2](https://learn.microsoft.com/en-us/windows/wsl/install) (if running on Windows)
- [Git](https://git-scm.com/)

---

### Installation via Laravel Sail (Recommended)

1. **Clone the repository**:
   ```bash
   git clone https://github.com/ashkan-rezaei-tsh/file-manager.git
   cd file-manager
   ```

2. **Configure environment variables**:
   ```bash
   cp .env.example .env
   ```

3. **Install Composer dependencies** (if `vendor/` is missing):
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php82-composer:latest \
       composer install --ignore-platform-reqs
   ```

4. **Start the Docker environment**:
   ```bash
   ./vendor/bin/sail up -d
   ```
   *(Optionally, configure a shell alias: `alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'`)*

5. **Generate application key**:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. **Run database migrations**:
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. **Create the storage symlink**:
   ```bash
   ./vendor/bin/sail artisan storage:link
   ```

8. **Install frontend dependencies and start Vite dev server**:
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```

9. **(Optional) Run queue worker for email notifications**:
   ```bash
   ./vendor/bin/sail artisan queue:work
   ```

10. **Open the application**:
    - **App**: [http://localhost](http://localhost) (or port defined in `APP_PORT`)
    - **Mailpit Web UI**: [http://localhost:8025](http://localhost:8025)

---

### ⚠️ Windows WSL Troubleshooting (`winnat` Port Conflict)

If MySQL fails to bind to port `3306` on Windows with WSL2 due to Windows NAT (`winnat`) port reservations:

1. Open a terminal window as **Administrator** on Windows.
2. Run the following sequence:
   ```cmd
   net stop winnat
   ```
3. In your WSL terminal, start Sail:
   ```bash
   ./vendor/bin/sail up -d
   ```
4. Back in the Administrator Windows terminal, restart `winnat`:
   ```cmd
   net start winnat
   ```

---

## 📂 Project Structure Highlights

```text
file-manager/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── FileController.php           # Core controller (CRUD, tree uploads, zip download, share)
│   │   │   └── Auth/RegisteredUserController.php # Creates user's dedicated root directory
│   │   ├── Requests/                        # Form request validations (StoreFile, FileAction, etc.)
│   │   └── Resources/FileResource.php       # JSON API serialization for files and folders
│   ├── Jobs/
│   │   └── SendShareFileNotificationJob.php # Queued job for file sharing notifications
│   ├── Mail/
│   │   └── SharedFilesMail.php              # Email template sent when files are shared
│   └── Models/
│       ├── File.php                         # NestedSet model, soft deletes, trash, paths
│       ├── FileShare.php                    # File share pivot model
│       └── StarredFile.php                  # Favorite / starred files model
├── database/
│   └── migrations/                          # Files table with nestedSet columns, shares, starred
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   └── app/                         # File manager components (modals, buttons, icons, nav)
│   │   ├── Helper/
│   │   │   ├── file-helper.js               # File type detection & icon mappings
│   │   │   └── http-helper.js               # Axios helper for async requests
│   │   ├── Layouts/
│   │   │   └── AuthenticatedLayout.vue      # App layout with fullscreen drag-and-drop dropzone
│   │   ├── Pages/
│   │   │   ├── MyFiles.vue                  # Main file manager view with breadcrumbs & table
│   │   │   ├── MyFavorites.vue              # Starred files view
│   │   │   ├── SharedPage.vue               # Shared with me / Shared by me views
│   │   │   └── Trash.vue                    # Soft-deleted files recovery & purge view
│   │   └── event-bus.js                     # Mitt event bus (search, upload progress, notifications)
└── docker-compose.yml                       # Docker Sail services (Laravel, MySQL, Redis, Mailpit)
```

---

## 📜 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
