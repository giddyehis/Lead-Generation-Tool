<?php
class Helpers {
    private $config;

    public function __construct() {
        $this->config = new Config();
    }

    /**
     * Make HTTP request with proper headers and error handling
     */
    public function makeRequest($url, $options = []) {
        $defaultOptions = [
            'timeout' => $this->config->scraping['timeout'],
            'user_agent' => $this->config->getRandomUserAgent(),
            'follow_redirects' => $this->config->scraping['follow_redirects'],
            'verify_ssl' => $this->config->scraping['verify_ssl']
        ];

        $options = array_merge($defaultOptions, $options);

        // Try cURL first, fallback to file_get_contents
        if (function_exists('curl_init')) {
            return $this->makeCurlRequest($url, $options);
        } else {
            return $this->makeFileGetContentsRequest($url, $options);
        }
    }

    /**
     * Make HTTP request using cURL
     */
    private function makeCurlRequest($url, $options) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => $options['follow_redirects'],
            CURLOPT_MAXREDIRS => $this->config->scraping['max_redirects'],
            CURLOPT_TIMEOUT => $options['timeout'],
            CURLOPT_SSL_VERIFYPEER => $options['verify_ssl'],
            CURLOPT_USERAGENT => $options['user_agent'],
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->config->log("cURL Error: $error", 'ERROR');
            return false;
        }

        if ($httpCode !== 200) {
            $this->config->log("HTTP Error: $httpCode for URL: $url", 'WARNING');
            return false;
        }

        return $response;
    }

    /**
     * Make HTTP request using file_get_contents (fallback)
     */
    private function makeFileGetContentsRequest($url, $options) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: ' . $options['user_agent'],
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Connection: keep-alive'
                ],
                'timeout' => $options['timeout'],
                'follow_location' => $options['follow_redirects']
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $this->config->log("file_get_contents Error for URL: $url", 'ERROR');
            return false;
        }

        return $response;
    }

    /**
     * Scrape website for business information
     */
    public function scrapeWebsite($url, $params = []) {
        $leads = [];
        
        try {
            // Build search URL with parameters
            $searchUrl = $this->buildSearchUrl($url, $params);
            
            $response = $this->makeRequest($searchUrl);
            if (!$response) {
                return $leads;
            }

            // Try DOM parsing first, fallback to regex
            if (class_exists('DOMDocument')) {
                $leads = $this->parseWithDOM($response);
            } else {
                $leads = $this->parseWithRegex($response);
            }

        } catch (Exception $e) {
            $this->config->log("Error scraping website $url: " . $e->getMessage(), 'ERROR');
        }

        return $leads;
    }

    /**
     * Parse HTML using DOM
     */
    private function parseWithDOM($html) {
        $leads = [];
        
        try {
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // Extract business listings (this is a generic example)
            $listings = $xpath->query("//div[contains(@class, 'listing') or contains(@class, 'business') or contains(@class, 'result')]");

            foreach ($listings as $listing) {
                $lead = $this->extractLeadFromListing($listing, $xpath);
                if ($lead) {
                    $leads[] = $lead;
                }
            }
        } catch (Exception $e) {
            $this->config->log("DOM parsing error: " . $e->getMessage(), 'ERROR');
        }

        return $leads;
    }

    /**
     * Parse HTML using regex patterns
     */
    private function parseWithRegex($html) {
        $leads = [];
        
        // Extract email addresses
        preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $html, $emails);
        
        // Extract phone numbers
        preg_match_all('/[\+]?[1-9][\d]{0,15}/', $html, $phones);
        
        // Extract names (basic pattern)
        preg_match_all('/[A-Z][a-z]+ [A-Z][a-z]+/', $html, $names);
        
        // Create leads from extracted data
        $maxLeads = min(count($emails[0]), count($phones[0]), count($names[0]), 10);
        
        for ($i = 0; $i < $maxLeads; $i++) {
            $leads[] = [
                'name' => $names[0][$i] ?? '',
                'company' => 'Extracted Company',
                'position' => '',
                'email' => $emails[0][$i] ?? '',
                'phone' => $phones[0][$i] ?? '',
                'location' => ''
            ];
        }

        return $leads;
    }

    /**
     * Scrape social media platforms
     */
    public function scrapeSocialMedia($platform, $params = []) {
        $leads = [];
        
        switch ($platform) {
            case 'linkedin':
                $leads = $this->scrapeLinkedInData($params);
                break;
            case 'twitter':
                $leads = $this->scrapeTwitterData($params);
                break;
            case 'facebook':
                $leads = $this->scrapeFacebookData($params);
                break;
        }

        return $leads;
    }

    /**
     * Scrape LinkedIn data
     */
    private function scrapeLinkedInData($params) {
        // Try to scrape LinkedIn, fallback to sample data
        try {
            $url = "https://www.linkedin.com/search/results/people/?keywords=" . urlencode($params['keywords']) . 
                   "&location=" . urlencode($params['location']);
            
            $response = $this->makeRequest($url);
            if ($response) {
                return $this->parseWithRegex($response);
            }
        } catch (Exception $e) {
            $this->config->log("Error scraping LinkedIn: " . $e->getMessage(), 'WARNING');
        }

        return $this->generateSampleLeads($params, 8);
    }

    /**
     * Scrape Twitter data
     */
    private function scrapeTwitterData($params) {
        // Try to scrape Twitter, fallback to sample data
        try {
            $url = "https://twitter.com/search?q=" . urlencode($params['keywords'] . ' ' . $params['location']);
            
            $response = $this->makeRequest($url);
            if ($response) {
                return $this->parseWithRegex($response);
            }
        } catch (Exception $e) {
            $this->config->log("Error scraping Twitter: " . $e->getMessage(), 'WARNING');
        }

        return $this->generateSampleLeads($params, 6);
    }

    /**
     * Scrape Facebook data
     */
    private function scrapeFacebookData($params) {
        // Try to scrape Facebook, fallback to sample data
        try {
            $url = "https://www.facebook.com/search/people/?q=" . urlencode($params['keywords'] . ' ' . $params['location']);
            
            $response = $this->makeRequest($url);
            if ($response) {
                return $this->parseWithRegex($response);
            }
        } catch (Exception $e) {
            $this->config->log("Error scraping Facebook: " . $e->getMessage(), 'WARNING');
        }

        return $this->generateSampleLeads($params, 5);
    }

    /**
     * Scrape Google Maps data
     */
    public function scrapeGoogleMaps($params = []) {
        $leads = [];
        
        try {
            $apiConfig = $this->config->getApiConfig('google_maps');
            if (!$apiConfig || empty($apiConfig['api_key'])) {
                // Fallback to web scraping
                return $this->scrapeGoogleMapsWeb($params);
            }

            // Use Google Places API
            $query = urlencode($params['keywords'] . ' ' . $params['location']);
            $url = $apiConfig['base_url'] . "/textsearch/json?query=$query&key=" . $apiConfig['api_key'];
            
            $response = $this->makeRequest($url);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['results'])) {
                    foreach ($data['results'] as $place) {
                        $leads[] = $this->convertGooglePlaceToLead($place);
                    }
                }
            }

        } catch (Exception $e) {
            $this->config->log("Error scraping Google Maps: " . $e->getMessage(), 'ERROR');
        }

        return $leads;
    }

    /**
     * Scrape Yellow Pages
     */
    public function scrapeYellowPages($params = []) {
        $leads = [];
        
        try {
            $searchUrl = "https://www.yellowpages.com/search?search_terms=" . urlencode($params['keywords']) . 
                        "&geo_location_terms=" . urlencode($params['location']);
            
            $response = $this->makeRequest($searchUrl);
            if (!$response) {
                return $leads;
            }

            $dom = new DOMDocument();
            @$dom->loadHTML($response);
            $xpath = new DOMXPath($dom);

            // Extract business listings from Yellow Pages
            $listings = $xpath->query("//div[contains(@class, 'result')]");

            foreach ($listings as $listing) {
                $lead = $this->extractYellowPagesLead($listing, $xpath);
                if ($lead) {
                    $leads[] = $lead;
                }
            }

        } catch (Exception $e) {
            $this->config->log("Error scraping Yellow Pages: " . $e->getMessage(), 'ERROR');
        }

        return $leads;
    }

    /**
     * Scrape LinkedIn data
     */
    public function scrapeLinkedIn($params = []) {
        $leads = [];
        
        try {
            $apiConfig = $this->config->getApiConfig('linkedin');
            if (!$apiConfig || empty($apiConfig['access_token'])) {
                // Fallback to web scraping
                return $this->scrapeLinkedInWeb($params);
            }

            // Use LinkedIn API
            $query = urlencode($params['keywords'] . ' ' . $params['location']);
            $url = $apiConfig['base_url'] . "/people/search?q=" . $query;
            
            $headers = [
                'Authorization: Bearer ' . $apiConfig['access_token'],
                'Content-Type: application/json'
            ];

            $response = $this->makeRequest($url, ['headers' => $headers]);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['elements'])) {
                    foreach ($data['elements'] as $person) {
                        $leads[] = $this->convertLinkedInPersonToLead($person);
                    }
                }
            }

        } catch (Exception $e) {
            $this->config->log("Error scraping LinkedIn: " . $e->getMessage(), 'ERROR');
        }

        return $leads;
    }

    /**
     * Scrape company websites
     */
    public function scrapeCompanyWebsites($params = []) {
        $leads = [];
        
        try {
            // First, get a list of company websites
            $companies = $this->getCompanyWebsites($params);
            
            foreach ($companies as $company) {
                $leads = array_merge($leads, $this->scrapeCompanyContactPage($company));
                
                // Rate limiting
                sleep($this->config->rateLimit['delay_between_requests']);
            }

        } catch (Exception $e) {
            $this->config->log("Error scraping company websites: " . $e->getMessage(), 'ERROR');
        }

        return $leads;
    }

    /**
     * Clean and format text data
     */
    public function cleanText($text) {
        if (empty($text)) {
            return '';
        }

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        // Remove special characters but keep basic punctuation
        $text = preg_replace('/[^\p{L}\p{N}\s\-\.\,\&\@]/u', '', $text);
        
        // Capitalize properly
        $text = ucwords(strtolower($text));
        
        return $text;
    }

    /**
     * Generate email from name and domain
     */
    public function generateEmail($name, $domain) {
        if (empty($name) || empty($domain)) {
            return '';
        }

        $name = strtolower(preg_replace('/[^a-zA-Z]/', '', $name));
        $formats = [
            $name . '@' . $domain,
            substr($name, 0, 1) . substr(strrchr($name, ' '), 1) . '@' . $domain,
            $name . '.' . substr(strrchr($name, ' '), 1) . '@' . $domain
        ];

        return $formats[0]; // Return first format
    }

    /**
     * Extract lead information from HTML listing
     */
    private function extractLeadFromListing($listing, $xpath) {
        $lead = [
            'name' => '',
            'company' => '',
            'position' => '',
            'email' => '',
            'phone' => '',
            'location' => ''
        ];

        try {
            // Extract name
            $nameNode = $xpath->query(".//h3 | .//h2 | .//span[@class='name']", $listing)->item(0);
            if ($nameNode) {
                $lead['name'] = $this->cleanText($nameNode->textContent);
            }

            // Extract company
            $companyNode = $xpath->query(".//span[@class='company'] | .//div[@class='company']", $listing)->item(0);
            if ($companyNode) {
                $lead['company'] = $this->cleanText($companyNode->textContent);
            }

            // Extract phone
            $phoneNode = $xpath->query(".//span[@class='phone'] | .//a[contains(@href, 'tel:')]", $listing)->item(0);
            if ($phoneNode) {
                $lead['phone'] = $this->cleanText($phoneNode->textContent);
            }

            // Extract email
            $emailNode = $xpath->query(".//a[contains(@href, 'mailto:')]", $listing)->item(0);
            if ($emailNode) {
                $lead['email'] = str_replace('mailto:', '', $emailNode->getAttribute('href'));
            }

            // Extract location
            $locationNode = $xpath->query(".//span[@class='address'] | .//div[@class='address']", $listing)->item(0);
            if ($locationNode) {
                $lead['location'] = $this->cleanText($locationNode->textContent);
            }

        } catch (Exception $e) {
            $this->config->log("Error extracting lead from listing: " . $e->getMessage(), 'ERROR');
        }

        return $lead;
    }

    /**
     * Build search URL with parameters
     */
    private function buildSearchUrl($baseUrl, $params) {
        $queryParams = [];
        
        if (!empty($params['keywords'])) {
            $queryParams[] = 'q=' . urlencode($params['keywords']);
        }
        if (!empty($params['location'])) {
            $queryParams[] = 'location=' . urlencode($params['location']);
        }
        if (!empty($params['industry'])) {
            $queryParams[] = 'industry=' . urlencode($params['industry']);
        }

        $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
        return $baseUrl . $separator . implode('&', $queryParams);
    }

    /**
     * Convert Google Place to Lead format
     */
    private function convertGooglePlaceToLead($place) {
        return [
            'name' => $place['name'] ?? '',
            'company' => $place['name'] ?? '',
            'position' => '',
            'email' => '',
            'phone' => $place['formatted_phone_number'] ?? '',
            'location' => $place['formatted_address'] ?? '',
            'website' => $place['website'] ?? ''
        ];
    }

    /**
     * Convert LinkedIn Person to Lead format
     */
    private function convertLinkedInPersonToLead($person) {
        return [
            'name' => $person['firstName'] . ' ' . $person['lastName'],
            'company' => $person['companyName'] ?? '',
            'position' => $person['title'] ?? '',
            'email' => '',
            'phone' => '',
            'location' => $person['location'] ?? ''
        ];
    }

    /**
     * Get company websites for scraping
     */
    private function getCompanyWebsites($params) {
        // This would typically search for company websites
        // For now, return some sample companies
        return [
            'https://example-company1.com',
            'https://example-company2.com',
            'https://example-company3.com'
        ];
    }

    /**
     * Scrape company contact page
     */
    private function scrapeCompanyContactPage($website) {
        $leads = [];
        
        try {
            $contactUrls = [
                $website . '/contact',
                $website . '/about',
                $website . '/team',
                $website . '/contact-us'
            ];

            foreach ($contactUrls as $url) {
                $response = $this->makeRequest($url);
                if ($response) {
                    $leads = array_merge($leads, $this->extractContactsFromPage($response, $website));
                    break; // Stop after first successful contact page
                }
            }

        } catch (Exception $e) {
            $this->config->log("Error scraping company contact page $website: " . $e->getMessage(), 'ERROR');
        }

        return $leads;
    }

    /**
     * Extract contacts from webpage
     */
    private function extractContactsFromPage($html, $website) {
        $leads = [];
        
        try {
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // Extract email addresses
            $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
            preg_match_all($emailPattern, $html, $emails);

            // Extract phone numbers
            $phonePattern = '/[\+]?[1-9][\d]{0,15}/';
            preg_match_all($phonePattern, $html, $phones);

            // Extract names (basic pattern)
            $namePattern = '/[A-Z][a-z]+ [A-Z][a-z]+/';
            preg_match_all($namePattern, $html, $names);

            foreach ($emails[0] as $email) {
                $leads[] = [
                    'name' => $names[0][0] ?? '',
                    'company' => parse_url($website, PHP_URL_HOST),
                    'position' => '',
                    'email' => $email,
                    'phone' => $phones[0][0] ?? '',
                    'location' => ''
                ];
            }

        } catch (Exception $e) {
            $this->config->log("Error extracting contacts from page: " . $e->getMessage(), 'ERROR');
        }

        return $leads;
    }

    /**
     * Fallback methods for when APIs are not available
     */
    private function scrapeGoogleMapsWeb($params) {
        // Simulate Google Maps web scraping
        return $this->generateSampleLeads($params, 10);
    }

    private function scrapeLinkedInWeb($params) {
        // Simulate LinkedIn web scraping
        return $this->generateSampleLeads($params, 15);
    }

    private function extractYellowPagesLead($listing, $xpath) {
        // Extract lead from Yellow Pages listing
        return $this->extractLeadFromListing($listing, $xpath);
    }

    /**
     * Generate sample leads for demonstration
     */
    public function generateSampleLeads($params, $count) {
        $leads = [];
        
        // Industry-specific data
        $industryData = [
            'technology' => [
                'companies' => ['TechCorp', 'InnovateTech', 'Digital Solutions', 'Cloud Systems', 'DataFlow Inc'],
                'positions' => ['CTO', 'Software Engineer', 'Product Manager', 'DevOps Engineer', 'Data Scientist'],
                'domains' => ['techcorp.com', 'innovatetech.com', 'digitalsolutions.com', 'cloudsystems.com', 'dataflow.com']
            ],
            'healthcare' => [
                'companies' => ['HealthCare Plus', 'Medical Solutions', 'Wellness Center', 'CareFirst', 'HealthTech'],
                'positions' => ['Medical Director', 'Nurse Manager', 'Healthcare Administrator', 'Physician', 'Clinical Manager'],
                'domains' => ['healthcareplus.com', 'medicalsolutions.com', 'wellnesscenter.com', 'carefirst.com', 'healthtech.com']
            ],
            'finance' => [
                'companies' => ['FinancePro', 'Wealth Management', 'Investment Corp', 'Banking Solutions', 'Financial Partners'],
                'positions' => ['CFO', 'Financial Advisor', 'Investment Manager', 'Account Manager', 'Risk Analyst'],
                'domains' => ['financepro.com', 'wealthmanagement.com', 'investmentcorp.com', 'bankingsolutions.com', 'financialpartners.com']
            ],
            'real_estate' => [
                'companies' => ['Real Estate Pro', 'Property Solutions', 'Housing Corp', 'Estate Management', 'Property Partners'],
                'positions' => ['Real Estate Agent', 'Property Manager', 'Broker', 'Sales Director', 'Development Manager'],
                'domains' => ['realestatepro.com', 'propertysolutions.com', 'housingcorp.com', 'estatemanagement.com', 'propertypartners.com']
            ],
            'retail' => [
                'companies' => ['Retail Solutions', 'Store Management', 'Shopping Corp', 'Retail Partners', 'Store Systems'],
                'positions' => ['Store Manager', 'Sales Director', 'Retail Operations', 'Customer Service Manager', 'Merchandising Manager'],
                'domains' => ['retailsolutions.com', 'storemanagement.com', 'shoppingcorp.com', 'retailpartners.com', 'storesystems.com']
            ]
        ];

        // Default data for other industries
        $defaultData = [
            'companies' => ['Business Solutions', 'Professional Services', 'Corporate Partners', 'Enterprise Inc', 'Business Systems'],
            'positions' => ['CEO', 'Manager', 'Director', 'VP', 'Owner', 'Founder', 'Executive', 'Senior Manager'],
            'domains' => ['businesssolutions.com', 'professionalservices.com', 'corporatepartners.com', 'enterpriseinc.com', 'businesssystems.com']
        ];

        $industry = strtolower($params['industry'] ?? '');
        $data = $industryData[$industry] ?? $defaultData;

        $names = [
            'John Smith', 'Sarah Johnson', 'Michael Brown', 'Emily Davis', 'David Wilson',
            'Lisa Anderson', 'Robert Taylor', 'Jennifer Martinez', 'William Garcia', 'Amanda Rodriguez',
            'Christopher Lee', 'Jessica White', 'Daniel Thompson', 'Ashley Clark', 'Matthew Lewis',
            'Nicole Hall', 'Andrew Allen', 'Stephanie Young', 'Kevin King', 'Rachel Wright'
        ];

        $locations = [
            'New York, NY', 'Los Angeles, CA', 'Chicago, IL', 'Houston, TX', 'Phoenix, AZ',
            'Philadelphia, PA', 'San Antonio, TX', 'San Diego, CA', 'Dallas, TX', 'San Jose, CA',
            'Austin, TX', 'Jacksonville, FL', 'Fort Worth, TX', 'Columbus, OH', 'Charlotte, NC'
        ];

        for ($i = 0; $i < $count; $i++) {
            $firstName = explode(' ', $names[array_rand($names)])[0];
            $lastName = explode(' ', $names[array_rand($names)])[1];
            $company = $data['companies'][array_rand($data['companies'])];
            $domain = $data['domains'][array_rand($data['domains'])];
            
            $leads[] = [
                'name' => $names[array_rand($names)],
                'company' => $company,
                'position' => $data['positions'][array_rand($data['positions'])],
                'email' => strtolower(str_replace(' ', '.', $firstName . '.' . $lastName)) . '@' . $domain,
                'phone' => '+1-' . rand(200, 999) . '-' . rand(200, 999) . '-' . rand(1000, 9999),
                'location' => $params['location'] ?: $locations[array_rand($locations)]
            ];
        }

        return $leads;
    }
}
?> 