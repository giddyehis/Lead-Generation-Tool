<?php
class Config {
    // Rate limiting settings
    public $rateLimit = [
        'requests_per_minute' => 10,
        'delay_between_requests' => 3,
        'max_concurrent_requests' => 2
    ];

    // User agents for web scraping
    public $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:89.0) Gecko/20100101 Firefox/89.0'
    ];

    // API endpoints and configurations
    public $apis = [
        'google_maps' => [
            'base_url' => 'https://maps.googleapis.com/maps/api/place',
            'api_key' => '', // Add your Google Maps API key here
            'max_results' => 20
        ],
        'linkedin' => [
            'base_url' => 'https://api.linkedin.com/v2',
            'access_token' => '', // Add your LinkedIn API access token here
            'max_results' => 50
        ],
        'yellow_pages' => [
            'base_url' => 'https://www.yellowpages.com',
            'max_results' => 30
        ]
    ];

    // Database configuration (if using database)
    public $database = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'leads_db',
        'charset' => 'utf8mb4'
    ];

    // File storage settings
    public $storage = [
        'export_path' => 'exports/',
        'log_path' => 'logs/',
        'temp_path' => 'temp/',
        'max_file_size' => 10485760 // 10MB
    ];

    // Validation settings
    public $validation = [
        'min_name_length' => 2,
        'max_name_length' => 100,
        'min_company_length' => 2,
        'max_company_length' => 200,
        'email_domains_blacklist' => [
            'example.com',
            'test.com',
            'temp.com',
            'fake.com'
        ]
    ];

    // Scraping settings
    public $scraping = [
        'timeout' => 30,
        'follow_redirects' => true,
        'max_redirects' => 5,
        'verify_ssl' => false,
        'retry_attempts' => 3,
        'retry_delay' => 5
    ];

    // Logging settings
    public $logging = [
        'enabled' => true,
        'level' => 'INFO', // DEBUG, INFO, WARNING, ERROR
        'file' => 'leads_generator.log',
        'max_file_size' => 5242880, // 5MB
        'max_files' => 5
    ];

    // Export settings
    public $export = [
        'csv_delimiter' => ',',
        'csv_enclosure' => '"',
        'excel_creator' => 'Lead Generator',
        'excel_title' => 'Generated Leads',
        'json_pretty_print' => true
    ];

    public function __construct() {
        // Create necessary directories
        $this->createDirectories();
    }

    private function createDirectories() {
        $directories = [
            $this->storage['export_path'],
            $this->storage['log_path'],
            $this->storage['temp_path']
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    public function getRandomUserAgent() {
        return $this->userAgents[array_rand($this->userAgents)];
    }

    public function getApiConfig($api) {
        return $this->apis[$api] ?? null;
    }

    public function log($message, $level = 'INFO') {
        if (!$this->logging['enabled']) {
            return;
        }

        $logFile = $this->storage['log_path'] . $this->logging['file'];
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
?> 