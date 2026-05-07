# 📝 Job Yaari - Full Stack Blog Management System 🚀

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white)
![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)

A complete, full-stack blog management system built with PHP (Laravel), MySQL, and a dynamic frontend powered by Bootstrap 5, jQuery, and AJAX. This project provides a clean interface for users to read blog posts and a secure, powerful dashboard for administrators to manage all content.

## ✨ Overview

Job Yaari is a content management system focused on simplicity and efficiency. It features a public-facing blog where users can browse articles by category, date, or search terms. The heart of the system is the admin panel, a single-page application (SPA-like) experience built with AJAX, allowing administrators to perform CRUD operations on blogs and categories without ever needing to refresh the page.

## 🌟 Features

**Public (User-Facing):**
- 📰 Clean, responsive blog interface.
- 🔍 **Live Search:** Instantly find blogs with an AJAX-powered search bar.
- 🗂️ **Filter by Category:** Browse posts by specific categories.
- 📅 **Filter by Date:** Find posts from a particular date.
- 📖 **Related Blogs:** See other posts from the same category on the blog detail page.
- 📱 **Responsive Design:** Great reading experience on desktop, tablets, and mobile devices.

**Admin Panel:**
- 🔐 **Secure Token-Based Auth:** Admin access is protected via a login and Bearer token.
- 🚀 **AJAX-Powered Dashboard:** Manage blogs and categories seamlessly without page reloads.
- ✍️ **Rich Text Editor:** Create and edit blog content with a full-featured CKEditor 5 integration, including image uploads.
- 🖼️ **Featured Image Uploads:** Easily add a primary image to any blog post.
- ⚙️ **Full CRUD Operations:** Create, Read, Update, and Delete blog posts and categories.
- slug **Automatic Slug Generation:** SEO-friendly URLs are created automatically from titles.

## 🛠️ Tech Stack

| Category      | Technology                               |
|---------------|------------------------------------------|
| **Backend**   | PHP 8+, Laravel 10                       |
| **Frontend**  | HTML5, Bootstrap 5, jQuery 3.x           |
| **Database**  | MySQL                                    |
| **Editor**    | CKEditor 5 (Super-build)                 |
| **Dev Tools** | Composer, Artisan                        |

## 📁 Folder Structure

Here's a high-level overview of the most relevant directories:

```
jobyaari/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/         # All API controllers (Admin, Public, Auth)
│   │   └── Web/         # Web route controllers
│   ├── Models/          # Eloquent models (Blog, Category, Admin)
│   └── Support/         # Helper classes (e.g., Slug generator)
├── config/              # Application configuration files
├── database/
│   ├── migrations/      # Database schema migrations
│   └── seeders/         # Database seeders (e.g., for the admin user)
├── public/              # Publicly accessible files (index.php, assets)
├── resources/
│   └── views/           # Blade templates for all pages
├── routes/
│   ├── api.php          # API routes for admin and public endpoints
│   └── web.php          # Web routes for serving Blade views
└── .env.example         # Example environment file
```

## 🚀 Getting Started: Installation

Follow these steps to get the project running on your local machine.

### 1. Clone the Repository
```bash
git clone https://github.com/vu1f2324001/Jobyaari.git
cd jobyaari
```

### 2. Install Dependencies
Make sure you have [Composer](<https://getcomposer.org/)> installed.
```bash
composer install
```

### 3. Environment Setup
Create your environment file by copying the example.
```bash
cp .env.example .env
```

Now, generate your application key.
```bash
php artisan key:generate
```

### 4. Database Setup
1.  Open the `.env` file you just created.
2.  Update the database credentials (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) to match your local MySQL setup.

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=jobyaari
    DB_USERNAME=root
    DB_PASSWORD=
    ```

3.  Run the migrations to create the necessary tables and then seed the database to create the default admin user.

    ```bash
    php artisan migrate --seed
    ```

### 5. Create Storage Link
To make uploaded images (like featured images and content images) publicly accessible, create the storage symlink.
```bash
php artisan storage:link
```

### 6. Run the Development Server
You're all set! Start the Laravel development server.
```bash
php artisan serve
```
The application will be available at `http://127.0.0.1:8000`.

## 📸 Screenshots

*(This is where you would add screenshots of your application)*

**Homepage**
!Homepage Screenshot

**Admin Dashboard**
!Admin Dashboard Screenshot

**Blog Editor**
!Blog Editor Screenshot

## � Admin Login

To access the admin panel, navigate to `/admin/login`.

- **URL:** `http://127.0.0.1:8000/admin/login`
- **Email:** `admin@example.com`
- **Password:** `password`

These default credentials are set in the `database/seeders/AdminSeeder.php` file.

## ⚡ AJAX-Powered Features

This application heavily uses AJAX to provide a fast, modern user experience, especially in the admin panel.

- **Token-Based Authentication:** The admin login is handled via an AJAX call that returns a Bearer token. This token is stored in `localStorage` and sent with every subsequent API request for authorization.
- **Dynamic Data Tables:** The blog and category lists are fetched from the API and rendered on the client-side with jQuery. This allows for instant updates after creating, editing, or deleting an item.
- **Client-Side Actions:** All CRUD operations (Create, Update, Delete) are performed by making API calls in the background. The UI is then updated dynamically to reflect the changes without a full page reload.

## 🛡️ Security Features

- **CSRF Protection:** While the API is stateless, standard Laravel web routes are protected.
- **Input Validation:** All incoming data from forms and API requests is validated using Laravel's robust validation rules.
- **SQL Injection Prevention:** Eloquent ORM uses parameter binding to protect against SQL injection attacks.
- **Cross-Site Scripting (XSS) Prevention:** User input is escaped before being rendered in Blade templates. The API returns JSON, and the frontend script properly handles data to prevent XSS.
- **Authentication Middleware:** Admin API routes are protected by Laravel Sanctum's authentication middleware, ensuring only logged-in admins can access them.

## � Deployment

Here are some guidelines for deploying the application.

### On XAMPP (Local)
1.  Place the project folder inside the `htdocs` directory of your XAMPP installation.
2.  Create a new database in phpMyAdmin.
3.  Update your `.env` file with the database name and your MySQL credentials (usually `root` with no password).
4.  Run `php artisan migrate --seed` and `php artisan storage:link` from the project's terminal.
5.  Access the site via `http://localhost/jobyaari/public`.

### On Render (Cloud Hosting)
1.  Create a new "Web Service" on Render and connect your GitHub repository.
2.  Set the **Build Command** to `composer install`.
3.  Set the **Start Command** to `php artisan serve --host 0.0.0.0 --port $PORT`.
4.  Add a Render database ("PostgreSQL" or "MySQL") and add the credentials to your service's **Environment Variables**.
5.  In your service's settings, add a **Rewrite Rule** for all requests to point to `/index.php`.
6.  After the first deploy, use the shell to run `php artisan migrate --seed` and `php artisan storage:link`.

### On InfinityFree (Shared Hosting)
1.  Upload all project files (except `vendor` and `node_modules`) to `htdocs`.
2.  Create a database and update your `.env` file.
3.  Shared hosting typically does not provide shell access, which makes running `composer` and `artisan` commands difficult. You may need to:
    -   Run `composer install` locally and upload the `vendor` directory.
    -   Manually import your database schema via phpMyAdmin.
    -   Manually create the `public/storage` symlink if possible, or adjust file paths.
    -   *Note: Deployment on limited shared hosting can be challenging.*

## 🔮 Future Improvements

- [ ] **User Roles & Permissions:** Introduce different roles (e.g., Editor, Author).
- [ ] **Blog Comments:** Allow registered users to comment on posts.
- [ ] **Social Sharing:** Add buttons to share posts on social media.
- [ ] **API Rate Limiting:** Protect the API from abuse.
- [ ] **Unit & Feature Tests:** Increase code coverage with PHPUnit tests.

## 🤝 Contributing

Contributions are welcome! If you have a suggestion or want to fix a bug, please follow these steps:

1.  Fork the repository.
2.  Create a new branch (`git checkout -b feature/YourFeature` or `bugfix/YourBug`).
3.  Make your changes.
4.  Commit your changes (`git commit -m 'Add some feature'`).
5.  Push to the branch (`git push origin feature/YourFeature`).
6.  Open a Pull Request.

## � License

This project is open-source and licensed under the MIT License.

---

### 👨‍💻 Author

**Akshada**
- GitHub: @vu1f23224001

Made with ❤️ and code.
