# Drahmi Backend API

## Quick Setup Guide

### 1. Import Database

**Option A: phpMyAdmin**
1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Create new database: `drahmi_db`
3. Click "Import" and select `database.sql`
4. Click "Go"

**Option B: Command Line**
```bash
mysql -u root -p drahmi_db < database.sql
```

### 2. Update Config

Edit `config/config.php` with your MySQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'drahmi_db');
define('DB_USER', 'root');      // your MySQL username
define('DB_PASS', '');          // your MySQL password

define('JWT_SECRET', 'your_secret_key_change_this');
```

### 3. Start Server

**Built-in PHP:**
```bash
cd C:\Users\Younes\Desktop\test\drahmi-api
php -S localhost:8000
```

**Or XAMPP:**
1. Copy folder to `C:\xampp\htdocs\drahmi-api`
2. Go to `http://localhost/drahmi-api`

### 4. Test API

**Test Login:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@drahmi.com","password":"password123"}'
```

**Test Register:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"nom":"John","prenom":"Doe","email":"john@drahmi.com","password":"123456"}'
```

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/auth/register | No | Register new user |
| POST | /api/auth/login | No | Login, get JWT |
| GET | /api/auth/profile | Yes | Get user profile |
| GET | /api/transactions | Yes | List transactions |
| POST | /api/transactions | Yes | Create transaction |
| GET | /api/dashboard | Yes | Full dashboard data |
| GET | /api/dashboard/summary | Yes | Financial summary |
| POST | /api/simulation/calculate | Yes | Run simulation |

## Flutter Connection

In your Flutter app, use your computer's IP (not localhost):
```dart
static const String baseUrl = 'http://192.168.1.X:8000/api';
```

Find your IP on Windows: `ipconfig` (look for IPv4 Address)

## Default Categories

8 expense categories + 4 revenue categories are pre-loaded.

## Test User

- Email: `test@drahmi.com`
- Password: `password123`

## Troubleshooting

| Error | Fix |
|-------|-----|
| 500 Database error | Check credentials in config.php |
| Connection refused | Start PHP server first |
| CORS error | Check .htaccess enabled |
| Token expired | Re-login to get new token |