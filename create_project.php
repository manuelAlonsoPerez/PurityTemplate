<?php
/**
 * Create New Joomla Project Script
 * Creates a new Joomla project with environment-based configuration
 */

class JoomlaProjectCreator {
    private $baseDir;
    private $projectName;
    private $projectConfig;
    
    public function __construct($projectName) {
        $this->baseDir = dirname(__FILE__);
        $this->projectName = $projectName;
        $this->projectConfig = $this->generateProjectConfig();
    }
    
    /**
     * Generate project configuration based on project name
     */
    private function generateProjectConfig() {
        $projectDir = "/home/lolou/{$this->projectName}";
        $dbName = strtolower($this->projectName) . "_db";
        $dbUser = strtolower($this->projectName) . "_user";
        $dbPass = strtolower($this->projectName) . "_pass123";
        $projectUrl = strtolower($this->projectName) . ".local";
        
        return [
            'PROJECT_NAME' => strtolower($this->projectName),
            'PROJECT_DISPLAY_NAME' => ucwords(str_replace('-', ' ', $this->projectName)),
            'PROJECT_DIR' => $projectDir,
            'PROJECT_URL' => $projectUrl,
            'PROJECT_ALIAS' => "www.{$projectUrl}",
            'DB_HOST' => 'localhost',
            'DB_NAME' => $dbName,
            'DB_USER' => $dbUser,
            'DB_PASSWORD' => $dbPass,
            'DB_PREFIX' => strtolower($this->projectName) . '_',
            'DB_TYPE' => 'mysqli',
            'JOOMLA_SECRET' => base64_encode(random_bytes(32)),
            'JOOMLA_OFFLINE' => 'false',
            'JOOMLA_DEBUG' => 'true',
            'JOOMLA_CACHING' => '0',
            'JOOMLA_GZIP' => 'false',
            'MAIL_FROM' => "{$dbUser}@localhost.com",
            'MAIL_FROM_NAME' => ucwords(str_replace('-', ' ', $this->projectName)),
            'MAIL_METHOD' => 'mail',
            'MAIL_SMTP_HOST' => 'localhost',
            'MAIL_SMTP_PORT' => '25',
            'MAIL_SMTP_AUTH' => 'false',
            'MAIL_SMTP_USER' => '',
            'MAIL_SMTP_PASS' => '',
            'MAIL_SMTP_SECURE' => 'none',
            'TMP_PATH' => "{$projectDir}/tmp",
            'LOG_PATH' => "{$projectDir}/administrator/logs",
            'APACHE_ERROR_LOG' => "/var/log/apache2/{$this->projectName}_error.log",
            'APACHE_ACCESS_LOG' => "/var/log/apache2/{$this->projectName}_access.log",
            'DEVELOPMENT_MODE' => 'true',
            'AUTO_RELOAD' => 'true'
        ];
    }
    
    /**
     * Create project directory and copy files
     */
    public function createProjectDirectory() {
        $projectDir = $this->projectConfig['PROJECT_DIR'];
        
        if (is_dir($projectDir)) {
            throw new Exception("Project directory $projectDir already exists!");
        }
        
        // Create project directory
        mkdir($projectDir, 0755, true);
        
        // Copy Joomla files
        $this->copyDirectory($this->baseDir, $projectDir, ['.env', '.env.example', '*.conf', '*.php', '*.md', '*.sh']);
        
        echo "✓ Project directory created: $projectDir\n";
    }
    
    /**
     * Copy directory excluding certain files
     */
    private function copyDirectory($src, $dst, $exclude = []) {
        $dir = opendir($src);
        @mkdir($dst);
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;
                
                // Check if file should be excluded
                $excluded = false;
                foreach ($exclude as $pattern) {
                    if (fnmatch($pattern, $file)) {
                        $excluded = true;
                        break;
                    }
                }
                
                if (!$excluded) {
                    if (is_dir($srcFile)) {
                        $this->copyDirectory($srcFile, $dstFile, $exclude);
                    } else {
                        copy($srcFile, $dstFile);
                    }
                }
            }
        }
        closedir($dir);
    }
    
    /**
     * Create .env file for the project
     */
    public function createEnvFile() {
        $projectDir = $this->projectConfig['PROJECT_DIR'];
        $envFile = $projectDir . '/.env';
        
        $envContent = "# Joomla Project Configuration\n";
        $envContent .= "# Generated for project: {$this->projectName}\n\n";
        
        foreach ($this->projectConfig as $key => $value) {
            $envContent .= "$key=$value\n";
        }
        
        file_put_contents($envFile, $envContent);
        echo "✓ Environment file created: $envFile\n";
    }
    
    /**
     * Create virtual host configuration
     */
    public function createVirtualHost() {
        $projectName = $this->projectConfig['PROJECT_NAME'];
        $projectDir = $this->projectConfig['PROJECT_DIR'];
        $projectUrl = $this->projectConfig['PROJECT_URL'];
        $projectAlias = $this->projectConfig['PROJECT_ALIAS'];
        $errorLog = $this->projectConfig['APACHE_ERROR_LOG'];
        $accessLog = $this->projectConfig['APACHE_ACCESS_LOG'];
        
        $virtualHost = <<<EOF
<VirtualHost *:80>
    ServerName $projectUrl
    ServerAlias $projectAlias
    DocumentRoot $projectDir
    
    <Directory $projectDir>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # PHP Configuration
        <FilesMatch \\.php$>
            SetHandler application/x-httpd-php
        </FilesMatch>
        
        # Enable mod_rewrite for Joomla
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\\.php$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.php [L]
    </Directory>
    
    # Logging
    ErrorLog $errorLog
    CustomLog $accessLog combined
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Cache control for static files
    <LocationMatch "\\.(css|js|png|jpg|jpeg|gif|ico|svg)$">
        ExpiresActive On
        ExpiresDefault "access plus 1 month"
    </LocationMatch>
</VirtualHost>
EOF;
        
        $vhostFile = "{$projectName}.local.conf";
        file_put_contents($vhostFile, $virtualHost);
        echo "✓ Virtual host configuration created: $vhostFile\n";
        
        return $vhostFile;
    }
    
    /**
     * Create database
     */
    public function createDatabase() {
        $dbName = $this->projectConfig['DB_NAME'];
        $dbUser = $this->projectConfig['DB_USER'];
        $dbPass = $this->projectConfig['DB_PASSWORD'];
        $dbHost = $this->projectConfig['DB_HOST'];
        
        try {
            $pdo = new PDO("mysql:host=$dbHost", 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Create user
            $pdo->exec("CREATE USER IF NOT EXISTS '$dbUser'@'$dbHost' IDENTIFIED BY '$dbPass'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUser'@'$dbHost'");
            $pdo->exec("FLUSH PRIVILEGES");
            
            echo "✓ Database '$dbName' created successfully\n";
            echo "✓ User '$dbUser' created with access to '$dbName'\n";
            
        } catch (PDOException $e) {
            echo "✗ Database creation failed: " . $e->getMessage() . "\n";
            echo "Please ensure MySQL is running and root user is accessible\n";
        }
    }
    
    /**
     * Run complete project creation
     */
    public function createProject() {
        echo "🚀 Creating new Joomla project: {$this->projectName}\n";
        echo "📁 Project directory: {$this->projectConfig['PROJECT_DIR']}\n";
        echo "🌐 Project URL: http://{$this->projectConfig['PROJECT_URL']}\n";
        echo "🗄️  Database: {$this->projectConfig['DB_NAME']}\n\n";
        
        try {
            $this->createProjectDirectory();
            $this->createEnvFile();
            $this->createDatabase();
            $this->createVirtualHost();
            
            echo "\n✅ Project created successfully!\n\n";
            echo "Next steps:\n";
            echo "1. Navigate to project: cd {$this->projectConfig['PROJECT_DIR']}\n";
            echo "2. Run setup: php setup_joomla_env.php\n";
            echo "3. Copy virtual host: sudo cp {$this->projectConfig['PROJECT_NAME']}.local.conf /etc/apache2/sites-available/\n";
            echo "4. Enable site: sudo a2ensite {$this->projectConfig['PROJECT_NAME']}.local.conf\n";
            echo "5. Add to hosts: echo '127.0.0.1 {$this->projectConfig['PROJECT_URL']}' | sudo tee -a /etc/hosts\n";
            echo "6. Restart Apache: sudo systemctl restart apache2\n";
            echo "7. Access site: http://{$this->projectConfig['PROJECT_URL']}\n";
            
        } catch (Exception $e) {
            echo "❌ Project creation failed: " . $e->getMessage() . "\n";
        }
    }
}

// Run if called from command line
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Usage: php create_project.php <project-name>\n";
        echo "Example: php create_project.php MyJoomlaSite\n";
        exit(1);
    }
    
    $projectName = $argv[1];
    $creator = new JoomlaProjectCreator($projectName);
    $creator->createProject();
}
