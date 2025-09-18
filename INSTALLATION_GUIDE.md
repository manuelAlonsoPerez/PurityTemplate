# Joomla 4.x Local Installation Guide for WSL

## Prerequisites

- ✅ PHP 8.1.33 (installed)
- ✅ Apache2 (running)
- ✅ MySQL (running)

## Step 1: Set up MySQL Database

First, you need to configure MySQL authentication:

```bash
# Set up MySQL (run this first)
sudo mysql_secure_installation
```

When prompted:

- Set root password: `your_password_here`
- Remove anonymous users: `Y`
- Disallow root login remotely: `Y`
- Remove test database: `Y`
- Reload privilege tables: `Y`

Then create the Joomla database:

```bash
# Access MySQL with the password you set
mysql -u root -p

# In MySQL, run these commands:
CREATE DATABASE joomla_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'joomla_user'@'localhost' IDENTIFIED BY 'joomla_password';
GRANT ALL PRIVILEGES ON joomla_db.* TO 'joomla_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 2: Copy Joomla Files to Web Directory

```bash
# Copy all Joomla files to Apache's web directory
sudo cp -r /home/lolou/Joomla-Purity-Sample-J4/* /var/www/html/

# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/

# Set proper permissions
sudo chmod -R 755 /var/www/html/
sudo chmod -R 777 /var/www/html/cache/
sudo chmod -R 777 /var/www/html/tmp/
sudo chmod -R 777 /var/www/html/logs/
```

## Step 3: Create Joomla Configuration

```bash
# Copy the configuration template
sudo cp /var/www/html/installation/configuration.php-dist /var/www/html/configuration.php

# Edit the configuration file
sudo nano /var/www/html/configuration.php
```

Update these lines in the configuration file:

```php
public $host = 'localhost';
public $user = 'joomla_user';
public $password = 'joomla_password';
public $db = 'joomla_db';
public $dbprefix = 'jos_';
```

Generate a secret key:

```bash
# Generate a random secret key
openssl rand -base64 32
```

Add the generated key to the `$secret` field in configuration.php.

## Step 4: Access Joomla

Open your web browser and go to:

- **Frontend**: http://localhost
- **Administrator**: http://localhost/administrator

## Step 5: Complete Installation

1. Go to http://localhost/administrator
2. You should see the Joomla installation wizard
3. Follow the setup wizard to complete the installation
4. Create an admin user account
5. Set your site name and other preferences

## Alternative: Use PHP Built-in Server

If you prefer not to use Apache, you can use PHP's built-in server:

```bash
# Navigate to your Joomla directory
cd /home/lolou/Joomla-Purity-Sample-J4

# Start PHP server
php -S localhost:8000

# Access at: http://localhost:8000
```

## Troubleshooting

### Permission Issues

```bash
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
```

### Database Connection Issues

- Verify MySQL is running: `ps aux | grep mysql`
- Check database credentials in configuration.php
- Test MySQL connection: `mysql -u joomla_user -p joomla_db`

### Apache Issues

- Check Apache status: `sudo systemctl status apache2`
- Restart Apache: `sudo systemctl restart apache2`
- Check Apache error logs: `sudo tail -f /var/log/apache2/error.log`

## Database Credentials

- **Database**: joomla_db
- **Username**: joomla_user
- **Password**: joomla_password
- **Host**: localhost
