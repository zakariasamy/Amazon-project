# 🚀 Quick Start Guide

## Prerequisites
- PHP 8.0+ with Composer installed ✅
- MySQL or PostgreSQL database
- Chrome browser
- Code editor (VS Code recommended)

---

## 📦 Backend Setup (5 minutes)

### 1. Configure Database

Edit `backend-laravel8/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amazon_analyzer
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. Create Database

```bash
# In MySQL:
CREATE DATABASE amazon_analyzer;
```

### 3. Run Migrations

```bash
cd backend-laravel8
php artisan migrate
```

You should see:
```
✓ Migration: 2014_10_12_000000_create_users_table
✓ Migration: 2014_10_12_100000_create_password_resets_table
✓ Migration: 2019_08_19_000000_create_failed_jobs_table
✓ Migration: 2019_12_14_000001_create_personal_access_tokens_table
```

### 4. Configure CORS

Edit `backend-laravel8/config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_origins' => ['*'], // For development only!

'allowed_methods' => ['*'],

'allowed_headers' => ['*'],

'supports_credentials' => false,
```

### 5. Start Server

```bash
php artisan serve
```

Server runs at: **http://localhost:8000**

### 6. Test API

```bash
# Test registration
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@test.com","password":"password123","password_confirmation":"password123"}'
```

If successful, you'll get a JSON response with a **token**! 🎉

---

## 🧩 Chrome Extension Setup (3 minutes)

### 1. Update API URL

Edit `chrome-extension/src/auth/auth-manager.js`:

```javascript
constructor() {
  this.baseUrl = 'http://localhost:8000'; // ✅ Already correct for local dev
  // ...
}
```

### 2. Add Extension Icons (Optional)

Create 3 PNG icons and place in `chrome-extension/public/icons/`:
- `icon16.png` (16x16)
- `icon48.png` (48x48)
- `icon128.png` (128x128)

**Or skip** and use placeholder icons for now.

### 3. Load Extension in Chrome

1. Open Chrome
2. Go to `chrome://extensions/`
3. Enable **Developer mode** (toggle in top right)
4. Click **Load unpacked**
5. Select the `chrome-extension` folder
6. Extension installed! 🎉

### 4. Test Extension

1. Click the extension icon in Chrome toolbar
2. You should see the **Login/Register** form
3. Register a new account
4. After login, you'll see the **Dashboard**

### 5. Test on Amazon

1. Go to any Amazon product page:
   - https://www.amazon.com/dp/B08N5WRWNW (example)
2. You should see a **floating "Analyze" button** (bottom right)
3. Click it to test (currently shows a placeholder alert)

---

## 🧪 Testing the Full Flow

### 1. Register New User

Open extension → Click "Create account" → Fill form → Submit

### 2. Login

Use the credentials you just created

### 3. Check Dashboard

You should see:
- Your name and email
- Stats (Products Analyzed: 0)
- Subscription tier: Free
- Action buttons

### 4. Visit Amazon

Go to Amazon.com product page → See floating analyze button

---

## 🔧 Troubleshooting

### Extension not loading?
- Check `chrome://extensions/` for errors
- Make sure manifest.json is valid
- Try reloading the extension

### API not responding?
- Check Laravel server is running (`php artisan serve`)
- Check database connection in `.env`
- Check CORS configuration

### Login not working?
- Open browser console (F12) → Check for errors
- Verify API URL in `auth-manager.js`
- Check network tab for failed requests

### Database errors?
- Make sure migrations ran successfully
- Check database credentials in `.env`
- Try: `php artisan migrate:fresh`

---

## 📊 Current Features

### ✅ Working Now:
- User registration & login
- JWT authentication
- Secure token storage
- Auto token refresh
- Product page detection
- Floating analyze button

### 🚧 Coming Soon (In Implementation Plan):
- Sales estimation algorithm
- Revenue calculator
- FBA fees calculator
- Keyword analysis
- Reverse ASIN lookup
- Competition metrics
- Historical data tracking

---

## 🎯 Next Development Steps

1. **Database migrations** for app-specific tables:
   - `keyword_cache`
   - `asin_keyword_rankings`
   - `user_activity_logs`

2. **API Controllers**:
   - ConstantsController
   - AnalyticsController
   - FeedbackController
   - KeywordsController

3. **Extension Features**:
   - Data scraper module
   - Intelligence engine
   - Shadow UI dashboard
   - Analytics display

---

## 📚 Useful Commands

```bash
# Backend
cd backend-laravel8
php artisan serve              # Start server
php artisan migrate            # Run migrations
php artisan make:controller    # Create controller
php artisan make:migration     # Create migration
php artisan route:list         # List all routes

# Database
php artisan migrate:fresh      # Reset & re-run migrations
php artisan migrate:rollback   # Undo last migration

# Extension
# Just reload at chrome://extensions/ after changes
```

---

## 🔗 Important Files

```
Backend:
├── routes/api.php                          → API routes
├── app/Http/Controllers/Api/AuthController.php → Auth logic
├── config/cors.php                         → CORS settings
└── .env                                    → Database config

Extension:
├── manifest.json                           → Extension config
├── src/auth/auth-manager.js               → Auth logic
├── src/popup/login.html                   → Login UI
└── src/content/content-script.js          → Amazon page integration
```

---

**Need Help?** Check `README.md` for full documentation!

**Ready?** Let's build something amazing! 🚀
