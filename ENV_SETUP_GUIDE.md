# Joomla Environment-Based Setup Guide

This guide shows you how to use the new environment-based setup system for running multiple Joomla projects locally with Apache Virtual Hosts.

## 🚀 Quick Start

### 1. Setup Current Project

```bash
# Configure your current project
php setup_joomla_env.php
```

### 2. Create New Projects

```bash
# Create a new project
php create_project.php MyNewSite

# Setup the new project
cd /home/lolou/MyNewSite
php setup_joomla_env.php
```

### 3. Manage All Projects

```bash
# List all projects
php manage_projects.php list

# Setup a specific project
php manage_projects.php setup MyNewSite

# Show project details
php manage_projects.php show MyNewSite
```

## 📁 Environment Configuration

Each project uses a `.env` file for configuration. The system automatically generates these files with appropriate values.

### Example .env file:

```env
# Project Information
PROJECT_NAME=joomla1
PROJECT_DISPLAY_NAME="Joomla Project 1"
PROJECT_DIR=/home/lolou/Joomla-Purity-Sample-J4
PROJECT_URL=joomla1.local
PROJECT_ALIAS=www.joomla1.local

# Database Configuration
DB_HOST=localhost
DB_NAME=joomla_db
DB_USER=joomla_user
DB_PASSWORD=JoomlaDB123Pass!
DB_PREFIX=jos_
DB_TYPE=mysqli

# Joomla Configuration
JOOMLA_SECRET=GmTpDxdC8sS9nrOF
JOOMLA_OFFLINE=false
JOOMLA_DEBUG=false
JOOMLA_CACHING=0
JOOMLA_GZIP=false

# Mail Configuration
MAIL_FROM=joomla_user@localhost.com
MAIL_FROM_NAME="Joomla Project 1"
MAIL_METHOD=mail
MAIL_SMTP_HOST=localhost
MAIL_SMTP_PORT=25
MAIL_SMTP_AUTH=false
MAIL_SMTP_USER=
MAIL_SMTP_PASS=
MAIL_SMTP_SECURE=none

# Paths
TMP_PATH=/home/lolou/Joomla-Purity-Sample-J4/tmp
LOG_PATH=/home/lolou/Joomla-Purity-Sample-J4/administrator/logs

# Apache Configuration
APACHE_ERROR_LOG=/var/log/apache2/joomla1_error.log
APACHE_ACCESS_LOG=/var/log/apache2/joomla1_access.log

# Development Settings
DEVELOPMENT_MODE=true
AUTO_RELOAD=true
```

## 🔧 Available Scripts

### 1. `setup_joomla_env.php`

Sets up the current project based on its `.env` file.

**Features:**

- Creates Joomla configuration.php from .env
- Creates Apache virtual host configuration
- Sets up MySQL database and user
- Sets proper file permissions
- Generates security headers

**Usage:**

```bash
php setup_joomla_env.php
```

### 2. `create_project.php`

Creates a new Joomla project with environment configuration.

**Features:**

- Creates project directory
- Copies Joomla files
- Generates .env file with unique settings
- Creates database and user
- Generates virtual host configuration

**Usage:**

```bash
php create_project.php MyNewSite
```

### 3. `manage_projects.php`

Manages multiple Joomla projects.

**Commands:**

- `list` - List all available projects
- `create <name>` - Create a new project
- `setup <name>` - Setup an existing project
- `show <name>` - Show project details
- `help` - Show help message

**Usage:**

```bash
php manage_projects.php list
php manage_projects.php create MyNewSite
php manage_projects.php setup MyNewSite
php manage_projects.php show MyNewSite
```

## 🌐 Virtual Host Configuration

Each project gets its own Apache virtual host with:

- **PHP Support**: Proper PHP handling
- **URL Rewriting**: Joomla-friendly URLs
- **Security Headers**: XSS protection, content type options
- **Caching**: Static file caching
- **Logging**: Separate error and access logs

### Example Virtual Host:

```apache
<VirtualHost *:80>
    ServerName joomla1.local
    ServerAlias www.joomla1.local
    DocumentRoot /home/lolou/Joomla-Purity-Sample-J4

    <Directory /home/lolou/Joomla-Purity-Sample-J4>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        # PHP Configuration
        <FilesMatch \.php$>
            SetHandler application/x-httpd-php
        </FilesMatch>

        # Enable mod_rewrite for Joomla
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.php$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.php [L]
    </Directory>

    # Logging
    ErrorLog /var/log/apache2/joomla1_error.log
    CustomLog /var/log/apache2/joomla1_access.log combined

    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"

    # Cache control for static files
    <LocationMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg)$">
        ExpiresActive On
        ExpiresDefault "access plus 1 month"
    </LocationMatch>
</VirtualHost>
```

## 🗄️ Database Management

Each project gets its own database with:

- Unique database name: `{project_name}_db`
- Unique user: `{project_name}_user`
- Unique password: `{project_name}_pass123`
- UTF8MB4 character set for full Unicode support

## 📋 Complete Setup Workflow

### For Current Project:

```bash
# 1. Setup current project
php setup_joomla_env.php

# 2. Copy virtual host to Apache
sudo cp joomla1.local.conf /etc/apache2/sites-available/

# 3. Enable site
sudo a2ensite joomla1.local.conf

# 4. Add to hosts file
echo "127.0.0.1 joomla1.local" | sudo tee -a /etc/hosts

# 5. Restart Apache
sudo systemctl restart apache2

# 6. Access your site
# http://joomla1.local
```

### For New Projects:

```bash
# 1. Create new project
php create_project.php MyNewSite

# 2. Navigate to project
cd /home/lolou/MyNewSite

# 3. Setup project
php setup_joomla_env.php

# 4. Copy virtual host
sudo cp mynewsite.local.conf /etc/apache2/sites-available/

# 5. Enable site
sudo a2ensite mynewsite.local.conf

# 6. Add to hosts
echo "127.0.0.1 mynewsite.local" | sudo tee -a /etc/hosts

# 7. Restart Apache
sudo systemctl restart apache2

# 8. Access site
# http://mynewsite.local
```

## 🔍 Troubleshooting

### Project Not Found

```bash
# List all projects
php manage_projects.php list

# Check project directory
ls -la /home/lolou/
```

### Database Connection Issues

```bash
# Test database connection
mysql -u {project_name}_user -p {project_name}_db

# Check MySQL service
sudo systemctl status mysql
```

### Virtual Host Not Working

```bash
# Check Apache configuration
sudo apache2ctl configtest

# Check if site is enabled
sudo a2ensite {project_name}.local.conf

# Restart Apache
sudo systemctl restart apache2
```

### Permission Issues

```bash
# Fix permissions for project
sudo chown -R www-data:www-data /home/lolou/{project_name}
sudo chmod -R 755 /home/lolou/{project_name}
sudo chmod -R 777 /home/lolou/{project_name}/cache/
sudo chmod -R 777 /home/lolou/{project_name}/tmp/
```

## ✨ Benefits of This Approach

1. **Environment-Based**: All configuration in .env files
2. **Automated**: Scripts handle database, virtual hosts, permissions
3. **Isolated**: Each project has its own database and configuration
4. **Scalable**: Easy to add new projects
5. **Maintainable**: Centralized project management
6. **Secure**: Proper security headers and permissions
7. **Professional**: Production-like setup for development

## 🎯 Next Steps

1. Run `php setup_joomla_env.php` to setup your current project
2. Use `php create_project.php <name>` to create additional projects
3. Use `php manage_projects.php` to manage all your projects
4. Access your projects at their respective local URLs
5. Customize .env files as needed for each project
