<?php
/**
 * Joomla Projects Management Script
 * Manages multiple Joomla projects with environment-based configuration
 */

class JoomlaProjectManager {
    private $baseDir;
    private $projects = [];
    
    public function __construct() {
        $this->baseDir = dirname(__FILE__);
        $this->scanProjects();
    }
    
    /**
     * Scan for existing projects
     */
    private function scanProjects() {
        $parentDir = dirname($this->baseDir);
        $dirs = glob($parentDir . '/Joomla-*', GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            $projectName = basename($dir);
            $envFile = $dir . '/.env';
            
            if (file_exists($envFile)) {
                $this->projects[$projectName] = $this->loadProjectConfig($envFile);
            }
        }
    }
    
    /**
     * Load project configuration from .env file
     */
    private function loadProjectConfig($envFile) {
        $config = [];
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue;
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $config[trim($key)] = trim($value, '"\'');
            }
        }
        
        return $config;
    }
    
    /**
     * List all projects
     */
    public function listProjects() {
        echo "📋 Available Joomla Projects:\n\n";
        
        if (empty($this->projects)) {
            echo "No projects found.\n";
            return;
        }
        
        foreach ($this->projects as $name => $config) {
            $status = $this->getProjectStatus($config);
            echo sprintf("• %-20s | %-30s | %s\n", 
                $name, 
                $config['PROJECT_URL'], 
                $status
            );
        }
    }
    
    /**
     * Get project status
     */
    private function getProjectStatus($config) {
        $projectDir = $config['PROJECT_DIR'];
        $configFile = $projectDir . '/configuration.php';
        
        if (!is_dir($projectDir)) {
            return "❌ Directory missing";
        }
        
        if (!file_exists($configFile)) {
            return "⚠️  Not configured";
        }
        
        return "✅ Ready";
    }
    
    /**
     * Setup a specific project
     */
    public function setupProject($projectName) {
        if (!isset($this->projects[$projectName])) {
            echo "❌ Project '$projectName' not found!\n";
            return;
        }
        
        $config = $this->projects[$projectName];
        $projectDir = $config['PROJECT_DIR'];
        
        if (!is_dir($projectDir)) {
            echo "❌ Project directory not found: $projectDir\n";
            return;
        }
        
        // Change to project directory and run setup
        $originalDir = getcwd();
        chdir($projectDir);
        
        if (file_exists('setup_joomla_env.php')) {
            include 'setup_joomla_env.php';
            $setup = new JoomlaEnvSetup();
            $setup->runSetup();
        } else {
            echo "❌ Setup script not found in project directory\n";
        }
        
        chdir($originalDir);
    }
    
    /**
     * Create a new project
     */
    public function createProject($projectName) {
        if (file_exists('create_project.php')) {
            include 'create_project.php';
            $creator = new JoomlaProjectCreator($projectName);
            $creator->createProject();
        } else {
            echo "❌ Project creation script not found\n";
        }
    }
    
    /**
     * Show project details
     */
    public function showProject($projectName) {
        if (!isset($this->projects[$projectName])) {
            echo "❌ Project '$projectName' not found!\n";
            return;
        }
        
        $config = $this->projects[$projectName];
        
        echo "📊 Project Details: $projectName\n";
        echo str_repeat("=", 50) . "\n";
        echo "Display Name: " . $config['PROJECT_DISPLAY_NAME'] . "\n";
        echo "Directory: " . $config['PROJECT_DIR'] . "\n";
        echo "URL: http://" . $config['PROJECT_URL'] . "\n";
        echo "Database: " . $config['DB_NAME'] . "\n";
        echo "DB User: " . $config['DB_USER'] . "\n";
        echo "Status: " . $this->getProjectStatus($config) . "\n";
    }
    
    /**
     * Show help
     */
    public function showHelp() {
        echo "🔧 Joomla Projects Manager\n";
        echo str_repeat("=", 30) . "\n\n";
        echo "Usage: php manage_projects.php <command> [options]\n\n";
        echo "Commands:\n";
        echo "  list                    List all available projects\n";
        echo "  create <name>           Create a new project\n";
        echo "  setup <name>            Setup an existing project\n";
        echo "  show <name>             Show project details\n";
        echo "  help                    Show this help message\n\n";
        echo "Examples:\n";
        echo "  php manage_projects.php list\n";
        echo "  php manage_projects.php create MyNewSite\n";
        echo "  php manage_projects.php setup MyNewSite\n";
        echo "  php manage_projects.php show MyNewSite\n";
    }
    
    /**
     * Run command
     */
    public function run($args) {
        if (empty($args)) {
            $this->showHelp();
            return;
        }
        
        $command = $args[0];
        
        switch ($command) {
            case 'list':
                $this->listProjects();
                break;
                
            case 'create':
                if (isset($args[1])) {
                    $this->createProject($args[1]);
                } else {
                    echo "❌ Please specify project name\n";
                }
                break;
                
            case 'setup':
                if (isset($args[1])) {
                    $this->setupProject($args[1]);
                } else {
                    echo "❌ Please specify project name\n";
                }
                break;
                
            case 'show':
                if (isset($args[1])) {
                    $this->showProject($args[1]);
                } else {
                    echo "❌ Please specify project name\n";
                }
                break;
                
            case 'help':
            default:
                $this->showHelp();
                break;
        }
    }
}

// Run if called from command line
if (php_sapi_name() === 'cli') {
    $manager = new JoomlaProjectManager();
    $manager->run(array_slice($argv, 1));
}
