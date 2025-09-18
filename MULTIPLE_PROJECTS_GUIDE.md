# Running Multiple Joomla Projects Locally

This guide shows you how to run multiple Joomla projects simultaneously on your WSL system using Apache virtual hosts.

## Method 1: Quick Setup (Automated)

Run the setup script to create additional Joomla projects:

```bash
cd /home/lolou/Joomla-Purity-Sample-J4
./setup_multiple_joomla.sh
```

This will create:

- `Joomla-Project-2` at http://joomla2.local
- `Joomla-Project-3` at http://joomla3.local
- Virtual host configurations
- Separate databases for each project

## Method 2: Manual Setup

### Step 1: Create Project Directory

```bash
# Create a new project directory
mkdir /home/lolou/Joomla-Project-2
cd /home/lolou/Joomla-Project-2

# Copy Joomla files
cp -r /home/lolou/Joomla-Purity-Sample-J4/* .
```

### Step 2: Create Database

```bash
mysql -u root -p
```

In MySQL:

```sql
CREATE DATABASE joomla2_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'joomla2_user'@'localhost' IDENTIFIED BY 'joomla2_pass123';
GRANT ALL PRIVILEGES ON joomla2_db.* TO 'joomla2_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Configure Joomla

```bash
# Create configuration file
cp installation/configuration.php-dist configuration.php

# Edit configuration
nano configuration.php
```

Update these settings:

```php
public $sitename = 'Joomla Project 2';
public $user = 'joomla2_user';
public $password = 'joomla2_pass123';
public $db = 'joomla2_db';
public $dbprefix = 'joomla2_';
```

### Step 4: Create Virtual Host

Create `/etc/apache2/sites-available/joomla2.local.conf`:

```apache
<VirtualHost *:80>
    ServerName joomla2.local
    ServerAlias www.joomla2.local
    DocumentRoot /home/lolou/Joomla-Project-2

    <Directory /home/lolou/Joomla-Project-2>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/joomla2_error.log
    CustomLog ${APACHE_LOG_DIR}/joomla2_access.log combined
</VirtualHost>
```

### Step 5: Enable Virtual Host

```bash
# Enable the site
sudo a2ensite joomla2.local.conf

# Add to hosts file
echo "127.0.0.1 joomla2.local" | sudo tee -a /etc/hosts

# Restart Apache
sudo systemctl restart apache2
```

## Current Project Configuration

Your current project is already configured and can be accessed at:

- **URL**: http://joomla1.local (after setting up virtual host)
- **Directory**: `/home/lolou/Joomla-Purity-Sample-J4`
- **Database**: `joomla_db`
- **User**: `joomla_user`

## Virtual Host Setup for Current Project

To set up a virtual host for your current project:

```bash
# Copy the virtual host config
sudo cp /home/lolou/Joomla-Purity-Sample-J4/joomla1.local.conf /etc/apache2/sites-available/

# Enable the site
sudo a2ensite joomla1.local.conf

# Add to hosts file
echo "127.0.0.1 joomla1.local" | sudo tee -a /etc/hosts

# Restart Apache
sudo systemctl restart apache2
```

## Accessing Your Projects

After setup, you can access your projects at:

- **Project 1**: http://joomla1.local (current project)
- **Project 2**: http://joomla2.local
- **Project 3**: http://joomla3.local

## Database Management

Each project has its own database:

| Project | Database   | Username     | Password         |
| ------- | ---------- | ------------ | ---------------- |
| joomla1 | joomla_db  | joomla_user  | JoomlaDB123Pass! |
| joomla2 | joomla2_db | joomla2_user | joomla2_pass123  |
| joomla3 | joomla3_db | joomla3_user | joomla3_pass123  |

## Troubleshooting

### Virtual Host Not Working

```bash
# Check if site is enabled
sudo a2ensite joomla1.local.conf

# Check Apache configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### Permission Issues

```bash
# Fix permissions for each project
sudo chown -R www-data:www-data /home/lolou/Joomla-Project-*
sudo chmod -R 755 /home/lolou/Joomla-Project-*
sudo chmod -R 777 /home/lolou/Joomla-Project-*/cache/
sudo chmod -R 777 /home/lolou/Joomla-Project-*/tmp/
```

### Database Connection Issues

```bash
# Test database connection
mysql -u joomla2_user -p joomla2_db
```

## Adding More Projects

To add more projects, repeat the manual setup process or modify the setup script to include additional project names.

## Benefits of This Setup

1. **Isolation**: Each project has its own database and configuration
2. **Easy Development**: Work on multiple projects simultaneously
3. **Real URLs**: Use proper domain names instead of localhost paths
4. **Independent**: Changes to one project don't affect others
5. **Professional**: Mimics production environment setup
