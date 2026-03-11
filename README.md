# Lead Generation System

A professional PHP-based lead generation system designed to collect high-quality business leads from multiple sources with real-time processing capabilities.

## 🚀 Features

### Core Functionality
- **Multi-Source Lead Collection**: Scrapes data from business directories, social media, Google Maps, Yellow Pages, LinkedIn, and company websites
- **Real-Time Processing**: Generates leads instantly with fallback to sample data when external sources are unavailable
- **Smart Validation**: Validates emails, phone numbers, and other contact information
- **Quality Scoring**: Rates leads based on data completeness and validity
- **Duplicate Removal**: Automatically removes duplicate entries
- **Export Options**: Export leads in CSV, Excel, and JSON formats

### Advanced Features
- **Industry-Specific Data**: Tailored lead generation for different industries (Technology, Healthcare, Finance, Real Estate, Retail)
- **Location-Based Filtering**: Generate leads from specific geographic locations
- **Keyword Targeting**: Find leads based on job titles, company names, or other keywords
- **Rate Limiting**: Built-in protection against overwhelming external sources
- **Error Handling**: Graceful fallback when external sources are unavailable
- **Logging System**: Comprehensive logging for debugging and monitoring

## 🛠️ Installation

### Prerequisites
- PHP 7.4 or higher
- Composer
- Required PHP extensions: curl, xml, gd, mbstring, json

### Setup Instructions

1. **Clone or download the project**
   ```bash
   cd /path/to/your/project
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Create required directories**
   ```bash
   mkdir -p exports logs temp
   ```

4. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the application**
   Open your browser and navigate to `http://localhost:8000`

## 📖 Usage

### Web Interface
1. Open the application in your browser
2. Fill in the search parameters:
   - **Industry**: Select from predefined industries
   - **Location**: Enter city, state, or country
   - **Keywords**: Enter job titles or company keywords
   - **Company Size**: Filter by company size
   - **Maximum Results**: Choose how many leads to generate
   - **Data Sources**: Select which sources to scrape
3. Click "Generate Leads"
4. View results and export as needed

### API Usage
You can also use the system programmatically:

```bash
curl -X POST http://localhost:8000/generate_leads.php \
  -d "industry=technology&location=New York&keywords=CEO&max_results=50&sources[]=business_directories&sources[]=linkedin" \
  -H "Content-Type: application/x-www-form-urlencoded"
```

### Export Functionality
```bash
curl -X POST http://localhost:8000/export_leads.php \
  -d "industry=technology&location=New York&keywords=CEO&export_format=csv" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --output leads.csv
```

## 🔧 Configuration

### API Keys (Optional)
For enhanced functionality, add your API keys to `config.php`:

```php
'google_maps' => [
    'api_key' => 'your_google_maps_api_key',
],
'linkedin' => [
    'access_token' => 'your_linkedin_api_token',
],
```

### Rate Limiting
Adjust rate limiting settings in `config.php`:

```php
'rateLimit' => [
    'requests_per_minute' => 10,
    'delay_between_requests' => 3,
    'max_concurrent_requests' => 2
],
```

## 📊 Data Sources

### Supported Sources
1. **Business Directories**: Yellow Pages, White Pages, Super Pages
2. **Social Media**: LinkedIn, Twitter, Facebook
3. **Google Maps**: Business listings and contact information
4. **Yellow Pages**: Traditional business directory
5. **LinkedIn**: Professional networking platform
6. **Company Websites**: Direct website scraping

### Fallback System
When external sources are unavailable or blocked, the system automatically generates realistic sample data based on:
- Industry-specific company names
- Relevant job positions
- Appropriate email domains
- Valid phone number formats
- Geographic location data

## 🎯 Industry Support

### Technology
- Companies: TechCorp, InnovateTech, Digital Solutions, Cloud Systems, DataFlow Inc
- Positions: CTO, Software Engineer, Product Manager, DevOps Engineer, Data Scientist
- Domains: techcorp.com, innovatetech.com, digitalsolutions.com, etc.

### Healthcare
- Companies: HealthCare Plus, Medical Solutions, Wellness Center, CareFirst, HealthTech
- Positions: Medical Director, Nurse Manager, Healthcare Administrator, Physician, Clinical Manager
- Domains: healthcareplus.com, medicalsolutions.com, wellnesscenter.com, etc.

### Finance
- Companies: FinancePro, Wealth Management, Investment Corp, Banking Solutions, Financial Partners
- Positions: CFO, Financial Advisor, Investment Manager, Account Manager, Risk Analyst
- Domains: financepro.com, wealthmanagement.com, investmentcorp.com, etc.

### Real Estate
- Companies: Real Estate Pro, Property Solutions, Housing Corp, Estate Management, Property Partners
- Positions: Real Estate Agent, Property Manager, Broker, Sales Director, Development Manager
- Domains: realestatepro.com, propertysolutions.com, housingcorp.com, etc.

### Retail
- Companies: Retail Solutions, Store Management, Shopping Corp, Retail Partners, Store Systems
- Positions: Store Manager, Sales Director, Retail Operations, Customer Service Manager, Merchandising Manager
- Domains: retailsolutions.com, storemanagement.com, shoppingcorp.com, etc.

## 🔍 Quality Features

### Validation
- **Email Validation**: Checks format, domain validity, and disposable email detection
- **Phone Validation**: Validates phone number format and removes invalid patterns
- **Name Validation**: Ensures realistic names and removes test data
- **Company Validation**: Filters out test company names

### Quality Scoring
Leads are scored based on:
- Data completeness (0-100 points)
- Email validity (20 points)
- Phone validity (20 points)
- Company information (20 points)
- Location data (20 points)
- Source reliability (20 points)

### Quality Categories
- **Premium** (90-100): Complete data with valid contact information
- **High** (70-89): Good data with minor issues
- **Medium** (50-69): Acceptable data with some missing information
- **Low** (0-49): Poor quality or incomplete data

## 📈 Performance Optimization

### Real-Time Processing
- Asynchronous processing for multiple sources
- Intelligent caching of results
- Optimized database queries (when using database)
- Memory-efficient data handling

### Scalability
- Modular architecture for easy extension
- Configurable rate limiting
- Support for multiple concurrent users
- Efficient data storage and retrieval

## 🛡️ Security & Compliance

### Data Protection
- No sensitive data storage
- Secure API communication
- Input validation and sanitization
- Rate limiting to prevent abuse

### Compliance
- Respects robots.txt files
- Implements proper user agents
- Follows ethical scraping practices
- Provides fallback data when external sources are unavailable

## 🐛 Troubleshooting

### Common Issues

1. **cURL not available**
   - Install PHP cURL extension: `sudo apt install php8.4-curl`
   - The system will fallback to file_get_contents

2. **DOM extension missing**
   - Install PHP XML extension: `sudo apt install php8.4-xml`
   - The system will fallback to regex parsing

3. **Permission errors**
   - Ensure directories are writable: `chmod 755 exports logs temp`

4. **No leads generated**
   - Check log files in the `logs/` directory
   - Verify search parameters are provided
   - Ensure at least one data source is selected

### Logging
Logs are stored in `logs/leads_generator.log` and include:
- Error messages and warnings
- API request/response data
- Performance metrics
- Validation results

## 🔄 Recent Improvements

### Version 2.0 Updates
- **Real-time processing** with immediate results
- **Enhanced fallback system** for reliable data generation
- **Industry-specific data** for more relevant leads
- **Improved validation** with better quality scoring
- **Multiple export formats** (CSV, Excel, JSON)
- **Better error handling** and logging
- **Responsive web interface** with modern design

### Technical Improvements
- Modular architecture for better maintainability
- Comprehensive error handling and logging
- Fallback mechanisms for all external dependencies
- Optimized performance with reduced processing time
- Enhanced data validation and quality scoring
- Better user experience with real-time feedback

## 📞 Support

For issues, questions, or feature requests:
1. Check the troubleshooting section above
2. Review the log files for detailed error information
3. Ensure all dependencies are properly installed
4. Verify configuration settings in `config.php`

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

**Note**: This system is designed for educational and legitimate business purposes. Always respect the terms of service of external websites and comply with applicable laws and regulations when scraping data. 
