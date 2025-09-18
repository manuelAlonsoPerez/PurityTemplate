# 🚀 Joomla Environment-Based Setup System

A complete PHP-based setup system for running multiple Joomla projects locally with Apache Virtual Hosts and environment configuration.

## ✨ What's New

This system replaces the old shell scripts with a modern PHP-based approach that:

- **Uses .env files** for all configuration
- **Creates Apache Virtual Hosts** automatically
- **Manages multiple projects** easily
- **Handles database setup** automatically
- **Sets proper permissions** and security
- **Provides project management** tools

## 📁 Files Created

### Core Scripts

- `setup_joomla_env.php` - Setup current project from .env
- `create_project.php` - Create new Joomla projects
- `manage_projects.php` - Manage all projects
- `.env.example` - Environment template
- `.env` - Current project configuration

### Documentation

- `ENV_SETUP_GUIDE.md` - Complete setup guide
- `MULTIPLE_PROJECTS_GUIDE.md` - Multiple projects guide
- `INSTALLATION_GUIDE.md` - Basic installation guide

### Virtual Host Configs

- `joomla1.local.conf` - Current project virtual host
- `joomla2.local.conf` - Additional project template

## 🎯 Quick Start

### 1. Setup Current Project

```bash
php setup_joomla_env.php
```

### 2. Create New Project

```bash
php create_project.php MyNewSite
cd /home/lolou/MyNewSite
php setup_joomla_env.php
```

### 3. Manage Projects

```bash
php manage_projects.php list
php manage_projects.php setup MyNewSite
php manage_projects.php show MyNewSite
```

## 🔧 Features

### Environment Configuration

- All settings in `.env` files
- Automatic generation for new projects
- Easy customization per project

### Apache Virtual Hosts

- PHP support with proper handlers
- URL rewriting for Joomla
- Security headers
- Static file caching
- Separate logging per project

### Database Management

- Automatic database creation
- Unique users per project
- UTF8MB4 character set
- Proper permissions

### Project Management

- List all projects
- Create new projects
- Setup existing projects
- Show project details
- Centralized management

## 🌐 Project URLs

After setup, access your projects at:

- **Current Project**: http://joomla1.local
- **New Projects**: http://{project-name}.local

## 📋 Complete Workflow

### For Current Project:

```bash
# 1. Setup
php setup_joomla_env.php

# 2. Configure Apache
sudo cp joomla1.local.conf /etc/apache2/sites-available/
sudo a2ensite joomla1.local.conf
echo "127.0.0.1 joomla1.local" | sudo tee -a /etc/hosts
sudo systemctl restart apache2

# 3. Access
# http://joomla1.local
```

### For New Projects:

```bash
# 1. Create
php create_project.php MyNewSite

# 2. Setup
cd /home/lolou/MyNewSite
php setup_joomla_env.php

# 3. Configure Apache
sudo cp mynewsite.local.conf /etc/apache2/sites-available/
sudo a2ensite mynewsite.local.conf
echo "127.0.0.1 mynewsite.local" | sudo tee -a /etc/hosts
sudo systemctl restart apache2

# 4. Access
# http://mynewsite.local
```

## 🗄️ Database Structure

Each project gets:

- **Database**: `{project_name}_db`
- **User**: `{project_name}_user`
- **Password**: `{project_name}_pass123`
- **Prefix**: `{project_name}_`

## 🔍 Troubleshooting

### Common Issues

1. **Database Access**: Ensure MySQL is running and accessible
2. **Permissions**: Scripts handle most permission issues automatically
3. **Virtual Hosts**: Check Apache configuration and restart service
4. **Hosts File**: Ensure local domains are added to /etc/hosts

### Debug Commands

```bash
# Check projects
php manage_projects.php list

# Test database
mysql -u {project_name}_user -p {project_name}_db

# Check Apache
sudo apache2ctl configtest
sudo systemctl status apache2
```

## 🎉 Benefits

1. **Modern**: PHP-based with environment configuration
2. **Automated**: Handles database, virtual hosts, permissions
3. **Scalable**: Easy to add new projects
4. **Isolated**: Each project is completely independent
5. **Professional**: Production-like development environment
6. **Maintainable**: Centralized project management
7. **Secure**: Proper security headers and permissions

## 📚 Documentation

- `ENV_SETUP_GUIDE.md` - Complete environment setup guide
- `MULTIPLE_PROJECTS_GUIDE.md` - Multiple projects management
- `INSTALLATION_GUIDE.md` - Basic Joomla installation

## 🚀 Next Steps

1. Run `php setup_joomla_env.php` to setup your current project
2. Use `php create_project.php <name>` to create additional projects
3. Use `php manage_projects.php` to manage all projects
4. Customize `.env` files as needed for each project
5. Access your projects at their respective local URLs

---

**Happy Joomla Development! 🎉**
