#!/bin/bash

# Joomla Local Setup Script for WSL
echo "Setting up Joomla locally..."

# Create MySQL database
echo "Creating MySQL database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS joomla_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "CREATE USER IF NOT EXISTS 'joomla_user'@'localhost' IDENTIFIED BY 'joomla_password';"
mysql -u root -e "GRANT ALL PRIVILEGES ON joomla_db.* TO 'joomla_user'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# Copy Joomla files to web directory
echo "Copying Joomla files to web directory..."
sudo cp -r /home/lolou/Joomla-Purity-Sample-J4/* /var/www/html/

# Set proper permissions
echo "Setting file permissions..."
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
sudo chmod -R 777 /var/www/html/cache/
sudo chmod -R 777 /var/www/html/tmp/
sudo chmod -R 777 /var/www/html/logs/

# Create configuration file
echo "Creating Joomla configuration..."
sudo cp /var/www/html/installation/configuration.php-dist /var/www/html/configuration.php

# Update configuration with database settings
sudo sed -i "s/public \$host = 'localhost';/public \$host = 'localhost';/" /var/www/html/configuration.php
sudo sed -i "s/public \$user = '';/public \$user = 'joomla_user';/" /var/www/html/configuration.php
sudo sed -i "s/public \$password = '';/public \$password = 'joomla_password';/" /var/www/html/configuration.php
sudo sed -i "s/public \$db = '';/public \$db = 'joomla_db';/" /var/www/html/configuration.php

# Generate a secret key
SECRET=$(openssl rand -base64 32)
sudo sed -i "s/public \$secret = '';/public \$secret = '$SECRET';/" /var/www/html/configuration.php

echo "Setup complete!"
echo "You can now access Joomla at: http://localhost"
echo "Database: joomla_db"
echo "Username: joomla_user"
echo "Password: joomla_password"
