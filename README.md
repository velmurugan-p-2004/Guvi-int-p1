# User Management System - Installation Guide

## Requirements

### Web Server
- Apache/Nginx with PHP 7.4 or higher
- PHP Extensions:
  - PDO
  - PDO_MySQL
  - MongoDB PHP Driver
  - Redis PHP Extension

### Databases
- MySQL 5.7 or higher
- MongoDB 4.0 or higher
- Redis 6.0 or higher

## Installation Steps

### 1. Database Setup

#### MySQL Setup
1. Create the database and table:
```sql
mysql -u root -p < database_setup.sql
```

#### MongoDB Setup
1. Make sure MongoDB is running
2. The collections will be created automatically when first used

#### Redis Setup
1. Make sure Redis server is running
2. Default configuration should work for development

### 2. PHP Extensions Installation

#### Install MongoDB PHP Driver
```bash
# Ubuntu/Debian
sudo apt-get install php-mongodb

# CentOS/RHEL
sudo yum install php-mongodb

# Or via PECL
sudo pecl install mongodb
```

#### Install Redis PHP Extension
```bash
# Ubuntu/Debian
sudo apt-get install php-redis

# CentOS/RHEL
sudo yum install php-redis

# Or via PECL
sudo pecl install redis
```

### 3. Configuration

1. Update database credentials in `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'user_system');
```

2. Update MongoDB configuration if needed:
```php
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', 27017);
define('MONGO_DB', 'user_profiles');
```

3. Update Redis configuration if needed:
```php
define('REDIS_HOST', 'localhost');
define('REDIS_PORT', 6379);
```

### 4. Web Server Configuration

#### Apache (.htaccess)
Create `.htaccess` file in the root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ php/$1 [QSA,L]

# Enable CORS
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
```

#### Nginx
Add to your server configuration:
```nginx
location /api/ {
    rewrite ^/api/(.*)$ /php/$1 last;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Enable CORS
add_header Access-Control-Allow-Origin "*" always;
add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
add_header Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With" always;
```

### 5. File Permissions

Set appropriate permissions for the PHP files:
```bash
chmod 644 php/*.php
chmod 755 php/
```

## Testing the Installation

1. Open your web browser and navigate to the application
2. Try registering a new user
3. Login with the registered credentials
4. Update your profile information

## Troubleshooting

### Common Issues

1. **MongoDB Connection Error**
   - Make sure MongoDB service is running
   - Check if PHP MongoDB extension is installed
   - Verify MongoDB connection string

2. **Redis Connection Error**
   - Make sure Redis service is running
   - Check if PHP Redis extension is installed
   - Verify Redis configuration

3. **MySQL Connection Error**
   - Check database credentials
   - Ensure MySQL service is running
   - Verify database exists

4. **CORS Issues**
   - Make sure CORS headers are properly configured
   - Check web server configuration

### Development Mode

For development, you can enable error reporting by keeping these lines in `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Production Deployment

For production:
1. Disable error reporting in `config.php`
2. Use HTTPS for secure communication
3. Set up proper database user permissions
4. Configure Redis authentication
5. Set up regular database backups

## Security Considerations

1. **Password Security**: Passwords are hashed using PHP's `password_hash()` function
2. **SQL Injection Prevention**: All MySQL queries use prepared statements
3. **Session Management**: Sessions are stored in Redis with expiration
4. **Input Validation**: All inputs are validated on both client and server side
5. **CORS Configuration**: Configure CORS properly for production use

## File Structure

```
/
├── index.html          # Home page
├── register.html       # Registration page
├── login.html         # Login page
├── profile.html       # Profile page
├── css/
│   └── style.css      # Custom styles
├── js/
│   ├── register.js    # Registration functionality
│   ├── login.js       # Login functionality
│   └── profile.js     # Profile functionality
├── php/
│   ├── config.php     # Configuration
│   ├── database.php   # Database connection manager
│   ├── auth.php       # Authentication class
│   ├── profile.php    # Profile management class
│   ├── register.php   # Registration API endpoint
│   ├── login.php      # Login API endpoint
│   ├── logout.php     # Logout API endpoint
│   └── profile_api.php # Profile API endpoints
└── database_setup.sql  # Database setup script
```