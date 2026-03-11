<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Generation System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .main-content {
            padding: 40px;
        }

        .search-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .results-section {
            margin-top: 30px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #6c757d;
            margin-top: 5px;
        }

        .leads-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .leads-table th, .leads-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .leads-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .leads-table tr:hover {
            background: #f8f9fa;
        }

        .export-buttons {
            margin-top: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .export-btn {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .export-btn:hover {
            background: #218838;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Lead Generation System</h1>
            <p>Professional lead generation with quality focus</p>
        </div>

        <div class="main-content">
            <div class="search-section">
                <h2><i class="fas fa-search"></i> Search Parameters</h2>
                <form id="leadForm" method="POST" action="generate_leads.php">
                    <div class="search-options">
                        <div class="form-group">
                            <label for="industry">Industry/Niche</label>
                            <select name="industry" id="industry">
                                <option value="">Select Industry</option>
                                <option value="technology">Technology</option>
                                <option value="healthcare">Healthcare</option>
                                <option value="finance">Finance</option>
                                <option value="real_estate">Real Estate</option>
                                <option value="retail">Retail</option>
                                <option value="manufacturing">Manufacturing</option>
                                <option value="consulting">Consulting</option>
                                <option value="education">Education</option>
                                <option value="legal">Legal</option>
                                <option value="marketing">Marketing</option>
                                <option value="automotive">Automotive</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="construction">Construction</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" name="location" id="location" placeholder="City, State or Country">
                        </div>

                        <div class="form-group">
                            <label for="company_size">Company Size</label>
                            <select name="company_size" id="company_size">
                                <option value="">Any Size</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="51-200">51-200 employees</option>
                                <option value="201-1000">201-1000 employees</option>
                                <option value="1000+">1000+ employees</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="keywords">Keywords</label>
                            <input type="text" name="keywords" id="keywords" placeholder="e.g., CEO, manager, director">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="max_results">Maximum Results</label>
                        <select name="max_results" id="max_results">
                            <option value="50">50 leads</option>
                            <option value="100">100 leads</option>
                            <option value="250">250 leads</option>
                            <option value="500">500 leads</option>
                            <option value="1000">1000 leads</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="data_sources">Data Sources (Select multiple)</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px;">
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="sources[]" value="business_directories" checked>
                                Business Directories
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="sources[]" value="social_media" checked>
                                Social Media
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="sources[]" value="google_maps" checked>
                                Google Maps
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="sources[]" value="yellow_pages" checked>
                                Yellow Pages
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="sources[]" value="linkedin" checked>
                                LinkedIn
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="sources[]" value="websites" checked>
                                Company Websites
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn" id="generateBtn">
                        <i class="fas fa-magic"></i> Generate Leads
                    </button>
                </form>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Generating leads... This may take a few minutes.</p>
            </div>

            <div id="results" class="results-section" style="display: none;">
                <div class="stats" id="stats">
                    <!-- Stats will be populated here -->
                </div>

                <div class="export-buttons">
                    <button class="export-btn" onclick="exportLeads('csv')">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                    <button class="export-btn" onclick="exportLeads('excel')">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button class="export-btn" onclick="exportLeads('json')">
                        <i class="fas fa-code"></i> Export JSON
                    </button>
                </div>

                <div id="leadsTable">
                    <!-- Leads table will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('leadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            const generateBtn = document.getElementById('generateBtn');
            
            loading.style.display = 'block';
            results.style.display = 'none';
            generateBtn.disabled = true;
            
            fetch('generate_leads.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                generateBtn.disabled = false;
                
                if (data.success) {
                    displayResults(data);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                generateBtn.disabled = false;
                alert('Error: ' + error.message);
            });
        });

        function displayResults(data) {
            const results = document.getElementById('results');
            const stats = document.getElementById('stats');
            const leadsTable = document.getElementById('leadsTable');
            
            // Update stats
            stats.innerHTML = `
                <div class="stat-card">
                    <div class="stat-number">${data.total_leads}</div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${data.valid_emails}</div>
                    <div class="stat-label">Valid Emails</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${data.valid_phones}</div>
                    <div class="stat-label">Valid Phones</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${data.sources_used}</div>
                    <div class="stat-label">Sources Used</div>
                </div>
            `;
            
            // Add quality distribution if available
            if (data.quality_distribution) {
                stats.innerHTML += `
                    <div class="stat-card">
                        <div class="stat-number">${data.quality_distribution.premium || 0}</div>
                        <div class="stat-label">Premium Quality</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${data.quality_distribution.high || 0}</div>
                        <div class="stat-label">High Quality</div>
                    </div>
                `;
            }
            
            // Create leads table
            let tableHTML = `
                <table class="leads-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.leads.forEach(lead => {
                tableHTML += `
                    <tr>
                        <td>${lead.name || 'N/A'}</td>
                        <td>${lead.company || 'N/A'}</td>
                        <td>${lead.position || 'N/A'}</td>
                        <td>${lead.email || 'N/A'}</td>
                        <td>${lead.phone || 'N/A'}</td>
                        <td>${lead.location || 'N/A'}</td>
                        <td>${lead.source || 'N/A'}</td>
                    </tr>
                `;
            });
            
            tableHTML += '</tbody></table>';
            leadsTable.innerHTML = tableHTML;
            
            results.style.display = 'block';
        }

        function exportLeads(format) {
            const formData = new FormData(document.getElementById('leadForm'));
            formData.append('export_format', format);
            
            fetch('export_leads.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `leads_${new Date().toISOString().split('T')[0]}.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => {
                alert('Export failed: ' + error.message);
            });
        }
    </script>
</body>
</html> 