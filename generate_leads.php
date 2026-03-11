<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration and helper files
require_once 'config.php';
require_once 'helpers.php';
require_once 'validators.php';

class LeadGenerator {
    private $config;
    private $helpers;
    private $validators;
    private $leads = [];
    private $stats = [
        'total_leads' => 0,
        'valid_emails' => 0,
        'valid_phones' => 0,
        'sources_used' => 0
    ];

    public function __construct() {
        $this->config = new Config();
        $this->helpers = new Helpers();
        $this->validators = new Validators();
    }

    public function generateLeads($params) {
        try {
            // Validate input parameters
            $this->validateInput($params);
            
            // Initialize results
            $this->leads = [];
            $this->stats = [
                'total_leads' => 0,
                'valid_emails' => 0,
                'valid_phones' => 0,
                'sources_used' => 0
            ];

            // Process each selected data source
            $sources = $params['sources'] ?? ['business_directories'];
            
            foreach ($sources as $source) {
                $this->processSource($source, $params);
                $this->stats['sources_used']++;
                
                // Rate limiting between sources
                sleep(2);
            }

            // Remove duplicates and validate
            $this->removeDuplicates();
            $this->validateLeads();
            
            // Limit results
            $maxResults = (int)($params['max_results'] ?? 100);
            $this->leads = array_slice($this->leads, 0, $maxResults);
            
            // Update final stats
            $this->stats['total_leads'] = count($this->leads);
            $this->stats['valid_emails'] = count(array_filter($this->leads, function($lead) {
                return !empty($lead['email']) && $this->validators->isValidEmail($lead['email']);
            }));
            $this->stats['valid_phones'] = count(array_filter($this->leads, function($lead) {
                return !empty($lead['phone']) && $this->validators->isValidPhone($lead['phone']);
            }));

            // Calculate quality distribution
            $qualityDistribution = $this->calculateQualityDistribution();

            return [
                'success' => true,
                'leads' => $this->leads,
                'total_leads' => $this->stats['total_leads'],
                'valid_emails' => $this->stats['valid_emails'],
                'valid_phones' => $this->stats['valid_phones'],
                'sources_used' => $this->stats['sources_used'],
                'quality_distribution' => $qualityDistribution
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function validateInput($params) {
        if (empty($params['industry']) && empty($params['location']) && empty($params['keywords'])) {
            throw new Exception('Please provide at least one search parameter (industry, location, or keywords)');
        }
    }

    private function processSource($source, $params) {
        switch ($source) {
            case 'business_directories':
                $this->scrapeBusinessDirectories($params);
                break;
            case 'social_media':
                $this->scrapeSocialMedia($params);
                break;
            case 'google_maps':
                $this->scrapeGoogleMaps($params);
                break;
            case 'yellow_pages':
                $this->scrapeYellowPages($params);
                break;
            case 'linkedin':
                $this->scrapeLinkedIn($params);
                break;
            case 'websites':
                $this->scrapeCompanyWebsites($params);
                break;
        }
    }

    private function scrapeBusinessDirectories($params) {
        // Try real scraping first, fallback to sample data
        $directories = [
            'https://www.yellowpages.com',
            'https://www.whitepages.com',
            'https://www.superpages.com'
        ];

        $leadsFound = false;
        foreach ($directories as $directory) {
            try {
                $leads = $this->helpers->scrapeWebsite($directory, $params);
                if (!empty($leads)) {
                    foreach ($leads as $lead) {
                        $this->addLead($lead, 'Business Directory');
                    }
                    $leadsFound = true;
                }
            } catch (Exception $e) {
                $this->config->log("Error scraping $directory: " . $e->getMessage(), 'WARNING');
            }

            // Rate limiting
            sleep(1);
        }

        // If no real leads found, generate sample data
        if (!$leadsFound) {
            $sampleLeads = $this->helpers->generateSampleLeads($params, 15);
            foreach ($sampleLeads as $lead) {
                $this->addLead($lead, 'Business Directory (Sample)');
            }
        }
    }

    private function scrapeSocialMedia($params) {
        // Try real scraping first, fallback to sample data
        $socialPlatforms = [
            'linkedin' => 'https://www.linkedin.com',
            'twitter' => 'https://twitter.com',
            'facebook' => 'https://www.facebook.com'
        ];

        $leadsFound = false;
        foreach ($socialPlatforms as $platform => $url) {
            try {
                $leads = $this->helpers->scrapeSocialMedia($platform, $params);
                if (!empty($leads)) {
                    foreach ($leads as $lead) {
                        $this->addLead($lead, ucfirst($platform));
                    }
                    $leadsFound = true;
                }
            } catch (Exception $e) {
                $this->config->log("Error scraping $platform: " . $e->getMessage(), 'WARNING');
            }

            // Rate limiting
            sleep(2);
        }

        // If no real leads found, generate sample data
        if (!$leadsFound) {
            $sampleLeads = $this->helpers->generateSampleLeads($params, 12);
            foreach ($sampleLeads as $lead) {
                $this->addLead($lead, 'Social Media (Sample)');
            }
        }
    }

    private function scrapeGoogleMaps($params) {
        // Try real scraping first, fallback to sample data
        try {
            $leads = $this->helpers->scrapeGoogleMaps($params);
            if (!empty($leads)) {
                foreach ($leads as $lead) {
                    $this->addLead($lead, 'Google Maps');
                }
                return;
            }
        } catch (Exception $e) {
            $this->config->log("Error scraping Google Maps: " . $e->getMessage(), 'WARNING');
        }

        // Generate sample data
        $sampleLeads = $this->helpers->generateSampleLeads($params, 10);
        foreach ($sampleLeads as $lead) {
            $this->addLead($lead, 'Google Maps (Sample)');
        }
    }

    private function scrapeYellowPages($params) {
        // Try real scraping first, fallback to sample data
        try {
            $leads = $this->helpers->scrapeYellowPages($params);
            if (!empty($leads)) {
                foreach ($leads as $lead) {
                    $this->addLead($lead, 'Yellow Pages');
                }
                return;
            }
        } catch (Exception $e) {
            $this->config->log("Error scraping Yellow Pages: " . $e->getMessage(), 'WARNING');
        }

        // Generate sample data
        $sampleLeads = $this->helpers->generateSampleLeads($params, 8);
        foreach ($sampleLeads as $lead) {
            $this->addLead($lead, 'Yellow Pages (Sample)');
        }
    }

    private function scrapeLinkedIn($params) {
        // Try real scraping first, fallback to sample data
        try {
            $leads = $this->helpers->scrapeLinkedIn($params);
            if (!empty($leads)) {
                foreach ($leads as $lead) {
                    $this->addLead($lead, 'LinkedIn');
                }
                return;
            }
        } catch (Exception $e) {
            $this->config->log("Error scraping LinkedIn: " . $e->getMessage(), 'WARNING');
        }

        // Generate sample data
        $sampleLeads = $this->helpers->generateSampleLeads($params, 15);
        foreach ($sampleLeads as $lead) {
            $this->addLead($lead, 'LinkedIn (Sample)');
        }
    }

    private function scrapeCompanyWebsites($params) {
        // Try real scraping first, fallback to sample data
        try {
            $leads = $this->helpers->scrapeCompanyWebsites($params);
            if (!empty($leads)) {
                foreach ($leads as $lead) {
                    $this->addLead($lead, 'Company Website');
                }
                return;
            }
        } catch (Exception $e) {
            $this->config->log("Error scraping Company Websites: " . $e->getMessage(), 'WARNING');
        }

        // Generate sample data
        $sampleLeads = $this->helpers->generateSampleLeads($params, 10);
        foreach ($sampleLeads as $lead) {
            $this->addLead($lead, 'Company Website (Sample)');
        }
    }

    private function addLead($lead, $source) {
        $lead['source'] = $source;
        $lead['timestamp'] = date('Y-m-d H:i:s');
        
        // Basic validation
        if (!empty($lead['email']) || !empty($lead['phone']) || !empty($lead['name'])) {
            $this->leads[] = $lead;
        }
    }

    private function removeDuplicates() {
        $unique = [];
        $seen = [];

        foreach ($this->leads as $lead) {
            $key = strtolower(trim($lead['email'] ?? $lead['phone'] ?? $lead['name'] ?? ''));
            if (!empty($key) && !isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $lead;
            }
        }

        $this->leads = $unique;
    }

    private function validateLeads() {
        foreach ($this->leads as &$lead) {
            // Validate email
            if (!empty($lead['email']) && !$this->validators->isValidEmail($lead['email'])) {
                $lead['email'] = '';
            }

            // Validate phone
            if (!empty($lead['phone']) && !$this->validators->isValidPhone($lead['phone'])) {
                $lead['phone'] = '';
            }

            // Clean and format data
            $lead['name'] = $this->helpers->cleanText($lead['name'] ?? '');
            $lead['company'] = $this->helpers->cleanText($lead['company'] ?? '');
            $lead['position'] = $this->helpers->cleanText($lead['position'] ?? '');
            $lead['location'] = $this->helpers->cleanText($lead['location'] ?? '');
        }
    }

    private function calculateQualityDistribution() {
        $distribution = [
            'premium' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];

        foreach ($this->leads as $lead) {
            $qualityScore = $this->validators->calculateQualityScore($lead);
            $category = $this->validators->getQualityCategory($lead);
            
            switch ($category) {
                case 'Premium':
                    $distribution['premium']++;
                    break;
                case 'High':
                    $distribution['high']++;
                    break;
                case 'Medium':
                    $distribution['medium']++;
                    break;
                case 'Low':
                    $distribution['low']++;
                    break;
            }
        }

        return $distribution;
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generator = new LeadGenerator();
    $result = $generator->generateLeads($_POST);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 