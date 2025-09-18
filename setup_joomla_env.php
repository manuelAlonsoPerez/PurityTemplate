<?php
/**
 * Joomla Environment-Based Setup Script
 * Reads configuration from .env file and sets up Joomla projects
 */

class JoomlaEnvSetup {
    private $envFile;
    private $config;
    
    public function __construct($envFile = '.env') {
        $this->envFile = $envFile;
        $this->loadEnv();
    }
    
    /**
     * Load environment variables from .env file
     */
    private function loadEnv() {
        if (!file_exists($this->envFile)) {
            throw new Exception("Environment file {$this->envFile} not found!");
        }
        
        $lines = file($this->envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->config = [];
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue; // Skip comments
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $this->config[trim($key)] = trim($value, '"\'');
            }
        }
    }
    
    /**
     * Get configuration value
     */
    public function get($key, $default = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
    
    /**
     * Create Joomla configuration file
     */
    public function createJoomlaConfig() {
        $configTemplate = file_get_contents('installation/configuration.php-dist');
        if (!$configTemplate) {
            // Fallback to creating a basic configuration
            $configTemplate = $this->generateBasicConfig();
        }
        
        // Replace configuration values
        $replacements = [
            'public $sitename = \'Joomla!\';' => 'public $sitename = \'' . $this->get('PROJECT_DISPLAY_NAME') . '\';',
            'public $host = \'localhost\';' => 'public $host = \'' . $this->get('DB_HOST') . '\';',
            'public $user = \'\';' => 'public $user = \'' . $this->get('DB_USER') . '\';',
            'public $password = \'\';' => 'public $password = \'' . $this->get('DB_PASSWORD') . '\';',
            'public $db = \'\';' => 'public $db = \'' . $this->get('DB_NAME') . '\';',
            'public $dbprefix = \'jos_\';' => 'public $dbprefix = \'' . $this->get('DB_PREFIX') . '\';',
            'public $dbtype = \'mysqli\';' => 'public $dbtype = \'' . $this->get('DB_TYPE') . '\';',
            'public $secret = \'\';' => 'public $secret = \'' . $this->get('JOOMLA_SECRET') . '\';',
            'public $offline = false;' => 'public $offline = ' . ($this->get('JOOMLA_OFFLINE') === 'true' ? 'true' : 'false') . ';',
            'public $debug = false;' => 'public $debug = ' . ($this->get('JOOMLA_DEBUG') === 'true' ? 'true' : 'false') . ';',
            'public $caching = 0;' => 'public $caching = ' . $this->get('JOOMLA_CACHING') . ';',
            'public $gzip = false;' => 'public $gzip = ' . ($this->get('JOOMLA_GZIP') === 'true' ? 'true' : 'false') . ';',
            'public $mailfrom = \'\';' => 'public $mailfrom = \'' . $this->get('MAIL_FROM') . '\';',
            'public $fromname = \'\';' => 'public $fromname = \'' . $this->get('MAIL_FROM_NAME') . '\';',
            'public $mailer = \'mail\';' => 'public $mailer = \'' . $this->get('MAIL_METHOD') . '\';',
            'public $smtphost = \'localhost\';' => 'public $smtphost = \'' . $this->get('MAIL_SMTP_HOST') . '\';',
            'public $smtpport = 25;' => 'public $smtpport = ' . $this->get('MAIL_SMTP_PORT') . ';',
            'public $smtpauth = false;' => 'public $smtpauth = ' . ($this->get('MAIL_SMTP_AUTH') === 'true' ? 'true' : 'false') . ';',
            'public $smtpuser = \'\';' => 'public $smtpuser = \'' . $this->get('MAIL_SMTP_USER') . '\';',
            'public $smtppass = \'\';' => 'public $smtppass = \'' . $this->get('MAIL_SMTP_PASS') . '\';',
            'public $smtpsecure = \'none\';' => 'public $smtpsecure = \'' . $this->get('MAIL_SMTP_SECURE') . '\';',
            'public $log_path = \'/administrator/logs\';' => 'public $log_path = \'' . $this->get('LOG_PATH') . '\';',
            'public $tmp_path = \'/tmp\';' => 'public $tmp_path = \'' . $this->get('TMP_PATH') . '\';',
        ];
        
        foreach ($replacements as $search => $replace) {
            $configTemplate = str_replace($search, $replace, $configTemplate);
        }
        
        file_put_contents('configuration.php', $configTemplate);
        echo "✓ Joomla configuration created\n";
    }
    
    /**
     * Create Apache virtual host configuration
     */
    public function createVirtualHost() {
        $projectName = $this->get('PROJECT_NAME');
        $projectDir = $this->get('PROJECT_DIR');
        $projectUrl = $this->get('PROJECT_URL');
        $projectAlias = $this->get('PROJECT_ALIAS');
        $errorLog = $this->get('APACHE_ERROR_LOG');
        $accessLog = $this->get('APACHE_ACCESS_LOG');
        
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
        $dbName = $this->get('DB_NAME');
        $dbUser = $this->get('DB_USER');
        $dbPass = $this->get('DB_PASSWORD');
        $dbHost = $this->get('DB_HOST');
        
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
     * Set file permissions
     */
    public function setPermissions() {
        $projectDir = $this->get('PROJECT_DIR');
        
        // Set directory permissions
        $this->chmodRecursive($projectDir, 0755);
        
        // Set writable permissions for specific directories
        $writableDirs = ['cache', 'tmp', 'administrator/logs'];
        foreach ($writableDirs as $dir) {
            $fullPath = $projectDir . '/' . $dir;
            if (is_dir($fullPath)) {
                $this->chmodRecursive($fullPath, 0777);
                echo "✓ Set writable permissions for $dir\n";
            }
        }
    }
    
    /**
     * Recursively change file permissions
     */
    private function chmodRecursive($path, $permissions) {
        if (is_dir($path)) {
            chmod($path, $permissions);
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $this->chmodRecursive($path . '/' . $file, $permissions);
                }
            }
        } else {
            chmod($path, $permissions);
        }
    }
    
    /**
     * Generate hosts file entry
     */
    public function generateHostsEntry() {
        $projectUrl = $this->get('PROJECT_URL');
        return "127.0.0.1 $projectUrl";
    }
    
    /**
     * Generate basic configuration template
     */
    private function generateBasicConfig() {
        return '<?php
class JConfig {
    public $offline = false;
    public $offline_message = \'This site is down for maintenance.<br>Please check back again soon.\';
    public $display_offline_message = 1;
    public $offline_image = \'\';
    public $sitename = \'Joomla!\';
    public $editor = \'tinymce\';
    public $captcha = \'0\';
    public $list_limit = 20;
    public $access = 1;
    public $debug = false;
    public $debug_lang = false;
    public $debug_lang_const = true;
    public $dbtype = \'mysqli\';
    public $host = \'localhost\';
    public $user = \'\';
    public $password = \'\';
    public $db = \'\';
    public $dbprefix = \'jos_\';
    public $dbencryption = 0;
    public $dbsslverifyservercert = false;
    public $dbsslkey = \'\';
    public $dbsslcert = \'\';
    public $dbsslca = \'\';
    public $dbsslcipher = \'\';
    public $force_ssl = 0;
    public $live_site = \'\';
    public $secret = \'\';
    public $gzip = false;
    public $error_reporting = \'default\';
    public $helpurl = \'https://help.joomla.org/proxy?keyref=Help{major}{minor}:{keyref}&lang={langcode}\';
    public $offset = \'UTC\';
    public $mailonline = true;
    public $mailer = \'mail\';
    public $mailfrom = \'\';
    public $fromname = \'\';
    public $sendmail = \'/usr/sbin/sendmail\';
    public $smtpauth = false;
    public $smtpuser = \'\';
    public $smtppass = \'\';
    public $smtphost = \'localhost\';
    public $smtpsecure = \'none\';
    public $smtpport = 25;
    public $caching = 0;
    public $cache_handler = \'file\';
    public $cachetime = 15;
    public $cache_platformprefix = false;
    public $MetaDesc = \'\';
    public $MetaAuthor = true;
    public $MetaVersion = false;
    public $robots = \'\';
    public $sef = true;
    public $sef_rewrite = false;
    public $sef_suffix = false;
    public $unicodeslugs = false;
    public $feed_limit = 10;
    public $feed_email = \'none\';
    public $log_path = \'/administrator/logs\';
    public $tmp_path = \'/tmp\';
    public $lifetime = 15;
    public $session_handler = \'database\';
    public $shared_session = false;
    public $session_metadata = true;
}';
    }
    
    /**
     * Run complete setup
     */
    public function runSetup() {
        echo "🚀 Setting up Joomla project: " . $this->get('PROJECT_DISPLAY_NAME') . "\n";
        echo "📁 Project directory: " . $this->get('PROJECT_DIR') . "\n";
        echo "🌐 Project URL: http://" . $this->get('PROJECT_URL') . "\n\n";
        
        try {
            $this->createDatabase();
            $this->createJoomlaConfig();
            $this->createVirtualHost();
            $this->setPermissions();
            
            echo "\n✅ Setup completed successfully!\n\n";
            echo "Next steps:\n";
            echo "1. Copy virtual host to Apache: sudo cp " . $this->get('PROJECT_NAME') . ".local.conf /etc/apache2/sites-available/\n";
            echo "2. Enable site: sudo a2ensite " . $this->get('PROJECT_NAME') . ".local.conf\n";
            echo "3. Add to hosts file: echo '" . $this->generateHostsEntry() . "' | sudo tee -a /etc/hosts\n";
            echo "4. Restart Apache: sudo systemctl restart apache2\n";
            echo "5. Access your site: http://" . $this->get('PROJECT_URL') . "\n";
            
        } catch (Exception $e) {
            echo "❌ Setup failed: " . $e->getMessage() . "\n";
        }
    }
}

// Run setup if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $setup = new JoomlaEnvSetup();
    $setup->runSetup();
}
