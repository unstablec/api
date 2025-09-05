# API Proxy Logger

This project is a PHP proxy server for intercepting and logging API requests for the purpose of analyzing behavior of malicious applications.

## Purpose

### Main Goal
The proxy is designed for **analyzing and documenting API requests** from applications that may:
- Violate user agreements
- Use APIs in ways not intended by developers
- Bypass limitations or security mechanisms
- Abuse service resources

### Practical Applications

1. **Application Behavior Research**
   - Analyzing request patterns from suspicious applications
   - Identifying attempts to bypass API limitations
   - Documenting unauthorized usage

2. **Creating Defense Mechanisms**
   - Based on collected logs, API modifiers can be created
   - Blocking or limiting suspicious requests
   - Implementing additional validation

3. **Monitoring and Analytics**
   - Tracking frequency and types of requests
   - Identifying anomalous behavior
   - Collecting API usage statistics

## How It Works

1. **Interception**: Application makes request to proxy instead of original API
2. **Logging**: Proxy saves complete information about request and response
3. **Proxying**: Request is forwarded to original API without changes
4. **Return**: API response is returned to application unchanged

## Target API

Configured by default to work with: `https://vpvpay.store/api/`

## Project Structure

```
/
├── index.php          # Main page with proxy status
├── api/
│   ├── proxy.php      # Main proxy logic
│   ├── index.php      # Request handler in api folder
│   └── .htaccess      # Routing settings for api
├── logs/
│   ├── api_log.json   # Log file (created automatically)
│   ├── .htaccess      # Block web access to logs
│   └── README.md      # Log structure description
├── config.php         # Configuration settings
└── .htaccess          # Main routing settings
```

## Using Logs

Collected logs can be used for:
- **Pattern Analysis**: Identifying recurring suspicious requests
- **Creating Filters**: Developing rules to block unwanted traffic
- **API Modification**: Implementing additional checks and limitations
- **Security Research**: Studying methods of bypassing protection

## Ethical Considerations

⚠️ **Important**: This tool is intended for:
- Protecting your own APIs from abuse
- Information security research
- Analyzing application behavior with owner consent

Using it for illegal interception of third-party traffic or privacy violations is prohibited.

## Installation

1. Upload all files to your web server
2. Ensure Apache mod_rewrite is enabled
3. Set write permissions for the `logs/` directory
4. Access the main page to verify proxy status

## Usage

- Send requests to `/api/[endpoint]`
- Example: `/api/users` will be proxied to `https://vpvpay.store/api/users`
- All requests and responses are logged to `logs/api_log.json`
- View proxy status at the root URL

## Features

- ✅ Full request/response logging
- ✅ Duplicate prevention
- ✅ Support for all HTTP methods
- ✅ Header and body forwarding
- ✅ Automatic log rotation (keeps last 1000 entries)
- ✅ Security restrictions on log access
- ✅ Configurable timeout settings

## Log Format

Each log entry contains:
```json
{
  "timestamp": "2024-01-15 10:30:45",
  "request_hash": "md5_hash_of_request",
  "request": {
    "method": "POST",
    "url": "https://vpvpay.store/api/endpoint",
    "headers": {...},
    "body": "request_data"
  },
  "response": {
    "code": 200,
    "headers": {...},
    "body": "response_data"
  }
}
```

This format makes it easy to analyze API behavior and create appropriate countermeasures for malicious applications.