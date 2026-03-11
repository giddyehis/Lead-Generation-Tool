<?php
class Validators {
    private $config;

    public function __construct() {
        $this->config = new Config();
    }

    /**
     * Validate email address
     */
    public function isValidEmail($email) {
        if (empty($email)) {
            return false;
        }

        // Basic email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check for blacklisted domains
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        if (in_array($domain, $this->config->validation['email_domains_blacklist'])) {
            return false;
        }

        // Check for disposable email domains
        if ($this->isDisposableEmail($domain)) {
            return false;
        }

        return true;
    }

    /**
     * Validate phone number
     */
    public function isValidPhone($phone) {
        if (empty($phone)) {
            return false;
        }

        // Remove all non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Check if it's a valid length (7-15 digits)
        if (strlen($cleanPhone) < 7 || strlen($cleanPhone) > 15) {
            return false;
        }

        // Check for common invalid patterns
        $invalidPatterns = [
            '/^0+$/',           // All zeros
            '/^1+$/',           // All ones
            '/^123/',           // 123 pattern
            '/^000/',           // 000 pattern
            '/^111/',           // 111 pattern
        ];

        foreach ($invalidPatterns as $pattern) {
            if (preg_match($pattern, $cleanPhone)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate name
     */
    public function isValidName($name) {
        if (empty($name)) {
            return false;
        }

        $length = strlen($name);
        if ($length < $this->config->validation['min_name_length'] || 
            $length > $this->config->validation['max_name_length']) {
            return false;
        }

        // Check for valid characters (letters, spaces, hyphens, apostrophes)
        if (!preg_match('/^[a-zA-Z\s\-\']+$/', $name)) {
            return false;
        }

        // Check for common invalid names
        $invalidNames = [
            'test', 'example', 'demo', 'sample', 'unknown', 'n/a', 'none',
            'anonymous', 'admin', 'user', 'guest', 'temp', 'fake'
        ];

        $nameLower = strtolower($name);
        foreach ($invalidNames as $invalid) {
            if ($nameLower === $invalid) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate company name
     */
    public function isValidCompany($company) {
        if (empty($company)) {
            return false;
        }

        $length = strlen($company);
        if ($length < $this->config->validation['min_company_length'] || 
            $length > $this->config->validation['max_company_length']) {
            return false;
        }

        // Check for common invalid company names
        $invalidCompanies = [
            'test company', 'example corp', 'demo inc', 'sample llc',
            'unknown business', 'n/a', 'none', 'temp company'
        ];

        $companyLower = strtolower($company);
        foreach ($invalidCompanies as $invalid) {
            if ($companyLower === $invalid) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate website URL
     */
    public function isValidWebsite($url) {
        if (empty($url)) {
            return false;
        }

        // Basic URL format validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check for common invalid domains
        $invalidDomains = [
            'example.com', 'test.com', 'demo.com', 'sample.com',
            'localhost', '127.0.0.1', '0.0.0.0'
        ];

        $domain = parse_url($url, PHP_URL_HOST);
        if (in_array(strtolower($domain), $invalidDomains)) {
            return false;
        }

        return true;
    }

    /**
     * Validate location
     */
    public function isValidLocation($location) {
        if (empty($location)) {
            return false;
        }

        // Check for valid characters
        if (!preg_match('/^[a-zA-Z\s\-\,\.]+$/', $location)) {
            return false;
        }

        // Check for common invalid locations
        $invalidLocations = [
            'n/a', 'none', 'unknown', 'test', 'example', 'demo'
        ];

        $locationLower = strtolower($location);
        foreach ($invalidLocations as $invalid) {
            if ($locationLower === $invalid) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if email domain is disposable
     */
    private function isDisposableEmail($domain) {
        $disposableDomains = [
            '10minutemail.com', 'guerrillamail.com', 'mailinator.com',
            'tempmail.org', 'throwaway.email', 'yopmail.com',
            'mailnesia.com', 'sharklasers.com', 'getairmail.com',
            'maildrop.cc', 'mailnesia.com', 'mailnull.com'
        ];

        return in_array(strtolower($domain), $disposableDomains);
    }

    /**
     * Validate complete lead data
     */
    public function validateLead($lead) {
        $errors = [];

        // Validate name
        if (!empty($lead['name']) && !$this->isValidName($lead['name'])) {
            $errors[] = 'Invalid name format';
        }

        // Validate company
        if (!empty($lead['company']) && !$this->isValidCompany($lead['company'])) {
            $errors[] = 'Invalid company name';
        }

        // Validate email
        if (!empty($lead['email']) && !$this->isValidEmail($lead['email'])) {
            $errors[] = 'Invalid email address';
        }

        // Validate phone
        if (!empty($lead['phone']) && !$this->isValidPhone($lead['phone'])) {
            $errors[] = 'Invalid phone number';
        }

        // Validate location
        if (!empty($lead['location']) && !$this->isValidLocation($lead['location'])) {
            $errors[] = 'Invalid location';
        }

        // Validate website
        if (!empty($lead['website']) && !$this->isValidWebsite($lead['website'])) {
            $errors[] = 'Invalid website URL';
        }

        // Check if lead has at least one contact method
        if (empty($lead['email']) && empty($lead['phone'])) {
            $errors[] = 'Lead must have at least one contact method (email or phone)';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Calculate lead quality score (0-100)
     */
    public function calculateQualityScore($lead) {
        $score = 0;
        $maxScore = 100;

        // Name validation (20 points)
        if (!empty($lead['name']) && $this->isValidName($lead['name'])) {
            $score += 20;
        }

        // Company validation (15 points)
        if (!empty($lead['company']) && $this->isValidCompany($lead['company'])) {
            $score += 15;
        }

        // Email validation (25 points)
        if (!empty($lead['email']) && $this->isValidEmail($lead['email'])) {
            $score += 25;
        }

        // Phone validation (20 points)
        if (!empty($lead['phone']) && $this->isValidPhone($lead['phone'])) {
            $score += 20;
        }

        // Location validation (10 points)
        if (!empty($lead['location']) && $this->isValidLocation($lead['location'])) {
            $score += 10;
        }

        // Position validation (5 points)
        if (!empty($lead['position'])) {
            $score += 5;
        }

        // Website validation (5 points)
        if (!empty($lead['website']) && $this->isValidWebsite($lead['website'])) {
            $score += 5;
        }

        return $score;
    }

    /**
     * Clean and format phone number
     */
    public function formatPhoneNumber($phone) {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Format based on length
        if (strlen($cleanPhone) === 10) {
            return '(' . substr($cleanPhone, 0, 3) . ') ' . substr($cleanPhone, 3, 3) . '-' . substr($cleanPhone, 6);
        } elseif (strlen($cleanPhone) === 11 && substr($cleanPhone, 0, 1) === '1') {
            return '+1 (' . substr($cleanPhone, 1, 3) . ') ' . substr($cleanPhone, 4, 3) . '-' . substr($cleanPhone, 7);
        } elseif (strlen($cleanPhone) === 7) {
            return substr($cleanPhone, 0, 3) . '-' . substr($cleanPhone, 3);
        }

        return $phone; // Return original if can't format
    }

    /**
     * Clean and format email address
     */
    public function formatEmail($email) {
        if (empty($email)) {
            return '';
        }

        return strtolower(trim($email));
    }

    /**
     * Clean and format name
     */
    public function formatName($name) {
        if (empty($name)) {
            return '';
        }

        // Remove extra spaces and capitalize properly
        $name = preg_replace('/\s+/', ' ', trim($name));
        return ucwords(strtolower($name));
    }

    /**
     * Check if lead is high quality (score >= 70)
     */
    public function isHighQualityLead($lead) {
        $score = $this->calculateQualityScore($lead);
        return $score >= 70;
    }

    /**
     * Get lead quality category
     */
    public function getQualityCategory($lead) {
        $score = $this->calculateQualityScore($lead);

        if ($score >= 90) {
            return 'Premium';
        } elseif ($score >= 70) {
            return 'High';
        } elseif ($score >= 50) {
            return 'Medium';
        } elseif ($score >= 30) {
            return 'Low';
        } else {
            return 'Poor';
        }
    }

    /**
     * Validate multiple leads and return statistics
     */
    public function validateLeadsBatch($leads) {
        $stats = [
            'total' => count($leads),
            'valid' => 0,
            'high_quality' => 0,
            'with_email' => 0,
            'with_phone' => 0,
            'with_both' => 0,
            'quality_distribution' => [
                'Premium' => 0,
                'High' => 0,
                'Medium' => 0,
                'Low' => 0,
                'Poor' => 0
            ]
        ];

        foreach ($leads as $lead) {
            $validation = $this->validateLead($lead);
            if ($validation['is_valid']) {
                $stats['valid']++;
            }

            if ($this->isHighQualityLead($lead)) {
                $stats['high_quality']++;
            }

            if (!empty($lead['email'])) {
                $stats['with_email']++;
            }

            if (!empty($lead['phone'])) {
                $stats['with_phone']++;
            }

            if (!empty($lead['email']) && !empty($lead['phone'])) {
                $stats['with_both']++;
            }

            $quality = $this->getQualityCategory($lead);
            $stats['quality_distribution'][$quality]++;
        }

        return $stats;
    }
}
?> 