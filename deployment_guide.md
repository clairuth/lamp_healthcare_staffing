# Healthcare Staffing Platform Deployment Guide

This guide provides detailed instructions for deploying the Healthcare Staffing Platform to your HostGator shared hosting environment.

## Deployment Overview

The Healthcare Staffing Platform is built using:
- PHP/Laravel (Backend)
- MySQL (Database)
- React.js (Frontend)

This guide will walk you through:
1. Setting up the database
2. Uploading the application files
3. Configuring the application
4. Setting up domain and SSL

## Prerequisites

- HostGator shared hosting account
- cPanel access
- Domain: clairuth.com
- FTP client (FileZilla recommended)
- Basic knowledge of cPanel and MySQL

## Step 1: Database Setup

1. Log in to your HostGator cPanel account
2. Scroll down to the "Databases" section
3. Click on "MySQL Databases"
4. Create a new database:
   - Enter a name for your database (e.g., `clairuth_healthcare`)
   - Click "Create Database"
5. Create a database user:
   - Enter a username (e.g., `clairuth_admin`)
   - Enter a strong password (use the password generator for security)
   - Click "Create User"
6. Add the user to the database:
   - Select the user and database you just created
   - Click "Add"
   - Grant "ALL PRIVILEGES" to the user
   - Click "Make Changes"
7. Note down the database name, username, and password for later use

## Step 2: Upload Application Files

### Option 1: Using FTP

1. Download and install FileZilla or another FTP client
2. Connect to your HostGator account using your FTP credentials:
   - Host: ftp.clairuth.com
   - Username: Your cPanel username
   - Password: Your cPanel password
   - Port: 21
3. Navigate to the public_html directory (or a subdirectory if you want to install in a subfolder)
4. Upload all files from the deployment package to this directory

### Option 2: Using cPanel File Manager

1. Log in to your HostGator cPanel account
2. Click on "File Manager"
3. Navigate to the public_html directory
4. Click "Upload" and select all files from the deployment package
5. Extract any compressed files if necessary

## Step 3: Configure the Application

1. Rename the `.env.example` file to `.env`
2. Edit the `.env` file with your database information:
   ```
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=clairuth_healthcare
   DB_USERNAME=clairuth_admin
   DB_PASSWORD=your_password
   ```
3. Update other environment variables as needed:
   ```
   APP_NAME="Healthcare Staffing Platform"
   APP_URL=https://clairuth.com
   ```
4. Set up the application key:
   - Access your site via SSH (if available) and run: `php artisan key:generate`
   - Alternatively, you can manually add a 32-character random string to the `APP_KEY` variable

## Step 4: Database Migration and Seeding

1. If SSH access is available:
   - Connect to your hosting via SSH
   - Navigate to your application directory
   - Run: `php artisan migrate --seed`
2. If SSH access is not available:
   - Import the included SQL file (`database.sql`) using phpMyAdmin:
     - Log in to cPanel
     - Click on "phpMyAdmin"
     - Select your database
     - Click on "Import"
     - Choose the SQL file and click "Go"

## Step 5: Set Up Domain and SSL

1. If clairuth.com is a new domain:
   - In cPanel, go to "Domains" section
   - Click on "Domains"
   - Add your domain and point it to the directory where you uploaded the files
2. Set up SSL certificate:
   - In cPanel, find "Security" section
   - Click on "SSL/TLS"
   - Choose "Install and Manage SSL for your site (HTTPS)"
   - Select your domain
   - Click "Install Certificate"
   - Follow the prompts to complete installation

## Step 6: Final Configuration

1. Set up proper permissions:
   - storage directory: 755
   - bootstrap/cache directory: 755
   - .env file: 644
2. Configure cron job for scheduled tasks:
   - In cPanel, click on "Cron Jobs"
   - Add a new cron job:
     - Command: `php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1`
     - Schedule: Every minute (or as needed)
3. Test your application by visiting https://clairuth.com

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check .env file configuration
   - Verify file permissions
   - Check error logs in cPanel (Error Logs section)

2. **Database Connection Error**
   - Verify database credentials in .env file
   - Check if database user has proper permissions

3. **White Screen / Blank Page**
   - Enable error reporting in .env file: `APP_DEBUG=true`
   - Check PHP error logs

### Getting Help

If you encounter issues during deployment, please contact support with the following information:
- Specific error messages
- Steps you've already taken
- Screenshots of any error pages

## Maintenance

### Regular Updates

1. Back up your database regularly using cPanel's backup tools
2. Keep your application updated with security patches
3. Monitor disk space usage in cPanel

### Performance Optimization

1. Enable caching in .env file: `CACHE_DRIVER=file`
2. Consider using a CDN for static assets
3. Optimize database queries regularly

## Security Recommendations

1. Keep your cPanel password secure and change it regularly
2. Enable Two-Factor Authentication for cPanel if available
3. Regularly update all software components
4. Implement proper input validation and sanitization
5. Use HTTPS for all connections
6. Regularly back up your database and files

---

This deployment guide is specifically tailored for deploying the Healthcare Staffing Platform to clairuth.com on a HostGator shared hosting environment. If you have any questions or need further assistance, please don't hesitate to reach out for support.
