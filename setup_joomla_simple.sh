#!/bin/bash

# Simple Joomla Setup - Manual Database Creation
echo "Setting up Joomla locally (simplified version)..."

# Check if we can access MySQL
echo "Testing MySQL connection..."
if mysql -u root -e "SELECT 1;" 2>/dev/null; then
    echo "MySQL accessible without password"
    MYSQL_CMD="mysql -u root"
elif mysql -u root -p -e "SELECT 1;" 2>/dev/null; then
    echo "MySQL requires password - you'll need to enter it"
    MYSQL_CMD="mysql -u root -p"
else
    echo "Cannot access MySQL. Please run: sudo mysql_secure_installation"
    echo "Then try: mysql -u root -p"
    exit 1
fi

# Create database and user
echo "Creating database and user..."
$MYSQL_CMD -e "CREATE DATABASE IF NOT EXISTS joomla_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$MYSQL_CMD -e "CREATE USER IF NOT EXISTS 'joomla_user'@'localhost' IDENTIFIED BY 'joomla_password';"
$MYSQL_CMD -e "GRANT ALL PRIVILEGES ON joomla_db.* TO 'joomla_user'@'localhost';"
$MYSQL_CMD -e "FLUSH PRIVILEGES;"

echo "Database setup complete!"
echo "Database: joomla_db"
echo "Username: joomla_user" 
echo "Password: joomla_password"
echo ""
echo "Now you need to:"
echo "1. Copy files to web directory: sudo cp -r /home/lolou/Joomla-Purity-Sample-J4/* /var/www/html/"
echo "2. Set permissions: sudo chown -R www-data:www-data /var/www/html/"
echo "3. Make directories writable: sudo chmod -R 777 /var/www/html/cache/ /var/www/html/tmp/"
echo "4. Create config: sudo cp /var/www/html/installation/configuration.php-dist /var/www/html/configuration.php"
echo "5. Edit /var/www/html/configuration.php with the database credentials above"
