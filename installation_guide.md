# Healthcare Staffing Platform Installation Guide

This document provides detailed instructions for installing the Healthcare Staffing Platform on your HostGator shared hosting environment.

## Package Contents

The deployment package contains the following:

1. `healthcare_staffing_platform.zip` - Complete application files
2. `database.sql` - Database structure and initial data
3. `.env.example` - Example environment configuration file
4. `deployment_guide.md` - Step-by-step deployment instructions
5. `user_guide.pdf` - User manual for the platform

## System Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer (if available)
- Node.js (for future updates)
- SSL certificate (recommended for security)

## Pre-Installation Checklist

Before beginning the installation, ensure you have:

- [ ] HostGator shared hosting account credentials
- [ ] cPanel access
- [ ] Domain (clairuth.com) pointed to your hosting
- [ ] FTP credentials (if using FTP for file upload)
- [ ] Sufficient disk space (minimum 500MB recommended)

## Installation Steps

### 1. Prepare Your Hosting Environment

1. Log in to your HostGator cPanel account
2. Check PHP version:
   - In cPanel, find "Software" section
   - Click on "Select PHP Version"
   - Ensure PHP 8.0 or higher is selected
   - Enable required extensions:
     - pdo_mysql
     - mbstring
     - tokenizer
     - xml
     - ctype
     - json
     - fileinfo
     - openssl

### 2. Create Database

1. In cPanel, find "Databases" section
2. Click on "MySQL Databases"
3. Create a new database:
   - Database Name: `clairuth_healthcare` (or your preferred name)
   - Click "Create Database"
4. Create a database user:
   - Username: `clairuth_admin` (or your preferred username)
   - Password: Use a strong password (use the generator)
   - Click "Create User"
5. Add user to database:
   - Select the user and database you created
   - Grant "ALL PRIVILEGES"
   - Click "Make Changes"
6. Note your database details:
   - Database Name: `clairuth_healthcare`
   - Username: `clairuth_admin`
   - Password: (your password)
   - Host: localhost

### 3. Upload Application Files

#### Using File Manager:

1. In cPanel, click on "File Manager"
2. Navigate to `public_html` directory
3. Click "Upload" and select `healthcare_staffing_platform.zip`
4. Once uploaded, select the file and click "Extract"
5. Extract to `public_html` directory

#### Using FTP:

1. Connect to your hosting using FTP:
   - Host: ftp.clairuth.com
   - Username: (your cPanel username)
   - Password: (your cPanel password)
   - Port: 21
2. Navigate to `public_html` directory
3. Upload all files from the extracted `healthcare_staffing_platform.zip`

### 4. Import Database

1. In cPanel, click on "phpMyAdmin"
2. Select your database from the left sidebar
3. Click on "Import" tab
4. Click "Choose File" and select `database.sql`
5. Click "Go" to import the database structure and initial data

### 5. Configure Application

1. Rename `.env.example` to `.env`:
   - In File Manager, locate `.env.example`
   - Right-click and select "Rename"
   - Change to `.env`
2. Edit the `.env` file:
   - Right-click and select "Edit"
   - Update database settings:
     ```
     DB_CONNECTION=mysql
     DB_HOST=localhost
     DB_PORT=3306
     DB_DATABASE=clairuth_healthcare
     DB_USERNAME=clairuth_admin
     DB_PASSWORD=your_password
     ```
   - Update application settings:
     ```
     APP_NAME="Healthcare Staffing Platform"
     APP_ENV=production
     APP_KEY=base64:generate_a_key_or_use_provided_key
     APP_DEBUG=false
     APP_URL=https://clairuth.com
     ```
   - Update payment integration settings with your accounts:
     ```
     PAYPAL_ACCOUNT=cubeloid@gmail.com
     CASHAPP_ACCOUNT=clairuth
     COINBASE_ACCOUNT=cubeloid@gmail.com
     ZELLE_ACCOUNT=cubeloid@gmail.com
     ```
   - Save changes

### 6. Set File Permissions

In File Manager:

1. Select the `storage` directory
2. Click "Permissions" (or right-click and select "Change Permissions")
3. Set permissions to 755 recursively
4. Repeat for `bootstrap/cache` directory
5. For `.env` file, set permissions to 644

### 7. Set Up SSL Certificate

1. In cPanel, find "Security" section
2. Click on "SSL/TLS"
3. Select "Install and Manage SSL for your site (HTTPS)"
4. Select your domain (clairuth.com)
5. Click "Install Certificate"
6. Follow the prompts to complete installation

### 8. Configure Cron Jobs

1. In cPanel, click on "Cron Jobs"
2. Add a new cron job:
   - Command: `php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1`
   - Replace "username" with your cPanel username
   - Schedule: Every minute (or as needed)

### 9. Final Verification

1. Visit your website: https://clairuth.com
2. Verify the homepage loads correctly
3. Test user registration and login
4. Check admin access:
   - URL: https://clairuth.com/admin
   - Default admin credentials:
     - Email: admin@example.com
     - Password: admin123
   - **Important**: Change the default admin password immediately after login

## Post-Installation Steps

### Security Recommendations

1. Change default admin password immediately
2. Update all default credentials in the system
3. Enable Two-Factor Authentication for admin accounts
4. Regularly back up your database using cPanel's backup tools
5. Keep your application updated with security patches

### Initial Configuration

1. Update company information:
   - Log in as admin
   - Go to Settings > Company Profile
   - Update company details, logo, and contact information
2. Configure payment settings:
   - Go to Settings > Payment Integration
   - Verify your payment account details
   - Test payment processing
3. Set up skills assessments:
   - Go to Skills > Manage Skills
   - Review and customize the default skills and assessments
4. Create initial facility types:
   - Go to Facilities > Facility Types
   - Add common facility types for your region

## Troubleshooting

### Common Issues and Solutions

1. **White Screen / 500 Error**
   - Check `.env` file configuration
   - Verify file permissions
   - Check error logs in cPanel (Logs section)

2. **Database Connection Error**
   - Verify database credentials in `.env` file
   - Check if database exists and user has proper permissions

3. **Missing Application Key**
   - Ensure `APP_KEY` is set in `.env` file
   - If missing, generate a 32-character random string

4. **File Upload Issues**
   - Check `upload_max_filesize` and `post_max_size` in PHP settings
   - Verify directory permissions

### Support Resources

If you encounter issues during installation:

1. Review the detailed deployment guide (`deployment_guide.md`)
2. Check the error logs in cPanel
3. Contact support with specific error messages and screenshots

## Next Steps

After successful installation:

1. Complete your company profile
2. Add your first healthcare facility
3. Create sample shifts
4. Test the credential verification system
5. Configure payment processing
6. Customize skills assessments

---

This installation guide is specifically tailored for deploying the Healthcare Staffing Platform to clairuth.com on a HostGator shared hosting environment. For additional assistance, please refer to the user guide or contact support.
