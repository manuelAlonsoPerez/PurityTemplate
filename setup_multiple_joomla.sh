#!/bin/bash

# Multiple Joomla Projects Setup Script for WSL
echo "Setting up multiple Joomla projects locally..."

# Function to create a new Joomla project
create_joomla_project() {
    local project_name=$1
    local project_dir="/home/lolou/$project_name"
    local db_name="${project_name}_db"
    local db_user="${project_name}_user"
    local db_pass="${project_name}_pass123"
    local site_url="${project_name}.local"
    
    echo "Creating project: $project_name"
    
    # Create project directory
    mkdir -p "$project_dir"
    
    # Copy current Joomla files to new project
    cp -r /home/lolou/Joomla-Purity-Sample-J4/* "$project_dir/"
    
    # Create database
    echo "Creating database for $project_name..."
    mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -u root -p -e "CREATE USER IF NOT EXISTS '$db_user'@'localhost' IDENTIFIED BY '$db_pass';"
    mysql -u root -p -e "GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user'@'localhost';"
    mysql -u root -p -e "FLUSH PRIVILEGES;"
    
    # Create configuration file
    cp "$project_dir/installation/configuration.php-dist" "$project_dir/configuration.php"
    
    # Update configuration with project-specific settings
    sed -i "s/public \$sitename = 'Joomla!';/public \$sitename = '$project_name';/" "$project_dir/configuration.php"
    sed -i "s/public \$host = 'localhost';/public \$host = 'localhost';/" "$project_dir/configuration.php"
    sed -i "s/public \$user = '';/public \$user = '$db_user';/" "$project_dir/configuration.php"
    sed -i "s/public \$password = '';/public \$password = '$db_pass';/" "$project_dir/configuration.php"
    sed -i "s/public \$db = '';/public \$db = '$db_name';/" "$project_dir/configuration.php"
    sed -i "s/public \$dbprefix = 'jos_';/public \$dbprefix = '${project_name}_';/" "$project_dir/configuration.php"
    
    # Generate secret key
    SECRET=$(openssl rand -base64 32)
    sed -i "s/public \$secret = '';/public \$secret = '$SECRET';/" "$project_dir/configuration.php"
    
    # Update paths
    sed -i "s|public \$log_path = '/administrator/logs';|public \$log_path = '$project_dir/administrator/logs';|" "$project_dir/configuration.php"
    sed -i "s|public \$tmp_path = '/tmp';|public \$tmp_path = '$project_dir/tmp';|" "$project_dir/configuration.php"
    
    # Set permissions
    chmod -R 755 "$project_dir"
    chmod -R 777 "$project_dir/cache/"
    chmod -R 777 "$project_dir/tmp/"
    chmod -R 777 "$project_dir/administrator/logs/"
    
    echo "Project $project_name created successfully!"
    echo "Database: $db_name"
    echo "Username: $db_user"
    echo "Password: $db_pass"
    echo "URL: http://$site_url"
    echo "Directory: $project_dir"
    echo ""
}

# Create virtual host configuration
create_virtual_host() {
    local project_name=$1
    local project_dir="/home/lolou/$project_name"
    local site_url="${project_name}.local"
    
    cat > "/tmp/${project_name}.local.conf" << EOF
<VirtualHost *:80>
    ServerName $site_url
    ServerAlias www.$site_url
    DocumentRoot $project_dir
    
    <Directory $project_dir>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/${project_name}_error.log
    CustomLog \${APACHE_LOG_DIR}/${project_name}_access.log combined
</VirtualHost>
EOF
    
    echo "Virtual host configuration created for $site_url"
}

# Main setup
echo "Setting up multiple Joomla projects..."

# Create projects
create_joomla_project "Joomla-Project-2"
create_joomla_project "Joomla-Project-3"

# Create virtual hosts
create_virtual_host "joomla1"
create_virtual_host "joomla2" 
create_virtual_host "joomla3"

echo ""
echo "Virtual host configurations created in /tmp/"
echo "To complete setup:"
echo "1. Copy virtual host files: sudo cp /tmp/*.local.conf /etc/apache2/sites-available/"
echo "2. Enable sites: sudo a2ensite *.local.conf"
echo "3. Add to /etc/hosts:"
echo "   127.0.0.1 joomla1.local"
echo "   127.0.0.1 joomla2.local" 
echo "   127.0.0.1 joomla3.local"
echo "4. Restart Apache: sudo systemctl restart apache2"
echo ""
echo "Then access your projects at:"
echo "- http://joomla1.local (current project)"
echo "- http://joomla2.local"
echo "- http://joomla3.local"
