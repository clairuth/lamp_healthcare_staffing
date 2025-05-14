# Healthcare Staffing Platform (LAMP Stack)

This document provides instructions for setting up and running the Healthcare Staffing Platform on a local Ubuntu LAMP (Linux, Apache, MySQL, PHP) stack.

## 1. Overview

This platform is a web application designed to connect healthcare professionals with healthcare facilities for staffing needs. It includes features for user registration, profile management, credential uploads, shift posting and application, skills assessment, and payment processing with escrow functionality.

**Technology Stack:**

*   **Linux:** Ubuntu (or any Linux distribution supporting Apache, MySQL, PHP)
*   **Apache:** Web server
*   **MySQL:** Database server
*   **PHP:** Server-side scripting language
*   **JavaScript:** Client-side scripting (plain JS, no frameworks like React/Vue/Angular)
*   **HTML/CSS:** Frontend structure and styling

This project **does not** use Laravel, React, Node.js, or any related frameworks or package managers like Composer (for PHP dependencies beyond what might be manually included) or npm/yarn.

## 2. Prerequisites

Ensure your Ubuntu system has the following installed:

*   **Apache2:**
    ```bash
    sudo apt update
    sudo apt install apache2
    sudo systemctl start apache2
    sudo systemctl enable apache2
    ```
*   **MySQL Server:**
    ```bash
    sudo apt install mysql-server
    sudo systemctl start mysql
    sudo systemctl enable mysql
    ```
    During installation, you might be prompted to set a root password. Secure your MySQL installation:
    ```bash
    sudo mysql_secure_installation
    ```
*   **PHP:** (PHP 7.4 or newer recommended, e.g., PHP 8.0, 8.1, 8.2)
    ```bash
    sudo apt install php libapache2-mod-php php-mysql php-curl php-gd php-mbstring php-xml php-zip php-intl php-json
    ```
    Verify PHP installation:
    ```bash
    php -v
    ```
*   **Git (Optional, for cloning):**
    ```bash
    sudo apt install git
    ```

## 3. Project Setup

1.  **Download or Clone Project Files:**
    Place the project files (the `lamp_healthcare_staffing` directory) into a suitable location on your server. For Apache, a common location is `/var/www/html/`.

    Example using Git (if you have a repository):
    ```bash
    cd /var/www/html/
    # git clone <your-repository-url> lamp_healthcare_staffing
    ```
    If you have the files as a ZIP, extract them to `/var/www/html/lamp_healthcare_staffing`.

2.  **Directory Structure:**
    The main application code is expected to be within the `lamp_healthcare_staffing/implementation/` directory. The web server should point to `lamp_healthcare_staffing/implementation/public/` as the document root.

## 4. Database Setup

1.  **Log in to MySQL:**
    ```bash
    sudo mysql -u root -p
    ```
    Enter your MySQL root password.

2.  **Create Database and User:**
    Execute the following SQL commands. Replace `your_db_user` and `your_db_password` with secure credentials.

    ```sql
    CREATE DATABASE healthcare_staffing_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER 'your_db_user'@'localhost' IDENTIFIED BY 'your_db_password';
    GRANT ALL PRIVILEGES ON healthcare_staffing_db.* TO 'your_db_user'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;
    ```

3.  **Import Database Schema:**
    The database schema is provided in `lamp_healthcare_staffing/database/lamp_database_schema.md` (which describes the tables) and potentially a `database.sql` file (if one was generated with `CREATE TABLE` statements).

    If you have a `database.sql` file, import it:
    ```bash
    mysql -u your_db_user -p healthcare_staffing_db < /path/to/lamp_healthcare_staffing/database/database.sql
    ```
    If you only have the schema description, you will need to manually create the tables based on `lamp_database_schema.md` using SQL commands or a tool like phpMyAdmin.

## 5. Application Configuration

1.  **Edit `config.php`:**
    Navigate to the configuration file located at `lamp_healthcare_staffing/implementation/app/config.php`.

    Update the following settings:

    *   **Database Credentials:**
        ```php
        define("DB_HOST", "localhost");
        define("DB_USER", "your_db_user");      // Replace with your created DB user
        define("DB_PASS", "your_db_password");  // Replace with your created DB password
        define("DB_NAME", "healthcare_staffing_db");
        ```

    *   **Site URL (Important for local setup):**
        The `SITE_URL` constant needs to be correctly set for your local environment. If you are serving the `public` directory as the root of a virtual host (e.g., `http://healthcare.local`), then `SITE_URL` should reflect that.
        The current auto-detection logic in `config.php` might need adjustment based on your specific Apache setup. For a simple setup where you access it via `http://localhost/lamp_healthcare_staffing/implementation/public/`, ensure `SITE_URL` resolves correctly.

    *   **API Keys (Placeholders):**
        Replace placeholder API keys with your actual sandbox/test credentials for PayPal, Square, and Coinbase Commerce:
        ```php
        define("PAYPAL_CLIENT_ID", "YOUR_PAYPAL_SANDBOX_CLIENT_ID");
        define("PAYPAL_CLIENT_SECRET", "YOUR_PAYPAL_SANDBOX_CLIENT_SECRET");
        // ... and other API keys
        ```

    *   **Upload Directory:**
        Ensure the `UPLOAD_DIR` is correctly defined and writable by the web server:
        ```php
        define("UPLOAD_DIR", BASE_PATH . "/implementation/public/uploads/");
        ```
        Create this directory if it doesn't exist and set permissions:
        ```bash
        sudo mkdir -p /var/www/html/lamp_healthcare_staffing/implementation/public/uploads
        sudo chown www-data:www-data /var/www/html/lamp_healthcare_staffing/implementation/public/uploads
        sudo chmod 775 /var/www/html/lamp_healthcare_staffing/implementation/public/uploads 
        ```
        (Adjust path if your project is not in `/var/www/html/`)

## 6. Apache Configuration (Virtual Host)

It's recommended to set up an Apache virtual host for the application.

1.  **Create a Virtual Host File:**
    ```bash
    sudo nano /etc/apache2/sites-available/healthcare-staffing.conf
    ```
    Paste the following configuration, adjusting `ServerAdmin`, `ServerName`, `DocumentRoot`, and directory paths as needed:

    ```apache
    <VirtualHost *:80>
        ServerAdmin webmaster@localhost
        ServerName healthcare.local  # Or your preferred local domain/localhost
        DocumentRoot /var/www/html/lamp_healthcare_staffing/implementation/public

        <Directory /var/www/html/lamp_healthcare_staffing/implementation/public/>
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/healthcare-staffing-error.log
        CustomLog ${APACHE_LOG_DIR}/healthcare-staffing-access.log combined
    </VirtualHost>
    ```

2.  **Enable the Site and Rewrite Module:**
    ```bash
    sudo a2ensite healthcare-staffing.conf
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    ```

3.  **Update Hosts File (Optional, for custom domain like `healthcare.local`):**
    Edit your local hosts file:
    ```bash
    sudo nano /etc/hosts
    ```
    Add the line:
    ```
    127.0.0.1   healthcare.local
    ```

## 7. File Permissions

Ensure the web server (usually `www-data` user on Ubuntu) has appropriate read access to project files and write access to specific directories like `uploads/` and any session/cache directories if used.

```bash
cd /var/www/html/lamp_healthcare_staffing
sudo chown -R www-data:www-data .
sudo find . -type d -exec chmod 775 {} \;
sudo find . -type f -exec chmod 664 {} \;
```
Be more restrictive with permissions in a production environment.

## 8. Accessing the Application

Open your web browser and navigate to the `ServerName` you configured (e.g., `http://healthcare.local`) or `http://localhost/lamp_healthcare_staffing/implementation/public/` if not using a virtual host pointing directly to the public folder.

## 9. Troubleshooting

*   **Check Apache Error Logs:** `/var/log/apache2/healthcare-staffing-error.log` (or `error.log`).
*   **Check PHP Errors:** Ensure `display_errors` is `On` in `php.ini` (e.g., `/etc/php/8.1/apache2/php.ini`) or enabled in your application's `config.php` for development.
*   **Database Connection:** Double-check `DB_USER`, `DB_PASS`, `DB_NAME` in `config.php`.
*   **.htaccess:** The `implementation/public/.htaccess` file is important for routing. Ensure `AllowOverride All` is set in your Apache directory configuration.

This guide provides a general setup. Specific paths and configurations might vary slightly based on your Ubuntu version and existing setup.

