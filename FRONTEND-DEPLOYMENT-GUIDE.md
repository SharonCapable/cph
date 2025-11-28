# Circle Point Homes - Frontend Integration & Deployment Guide

## Overview

This guide covers the complete setup of the new Next.js frontend integrated with the existing PHP backend.

## What We've Accomplished

### 1. ✅ Environment Setup
- Created `.env.local` and `.env.production` files with API configuration
- Set up environment variables for API URLs and upload paths

### 2. ✅ API Integration Layer
Created TypeScript services in `redesign/lib/`:
- `types.ts` - Type definitions for all data models
- `api-client.ts` - HTTP client for API requests
- `services/` - Service layer for properties, bookings, auth, and applications

### 3. ✅ PHP JSON API Endpoints
Created new API endpoints in `api/`:
- `properties/list.php` - Get all properties
- `properties/get.php` - Get single property
- `properties/featured.php` - Get featured properties
- `properties/search.php` - Search properties
- `users/login.php` - User login
- `users/signup.php` - User signup
- `users/current.php` - Get current user
- `bookings/list.php` - Get user bookings
- `bookings/get.php` - Get single booking
- `applications/submit.php` - Submit property manager application

### 4. ✅ Updated Next.js Pages
- `app/page.tsx` - Homepage (fetches featured properties from API)
- `app/properties/page.tsx` - Properties listing (fetches all properties from API)
- `app/properties/[id]/page.tsx` - Property detail page (fetches single property from API)

### 5. ✅ Dependencies Installed
- Installed all Next.js dependencies using npm with `--legacy-peer-deps`

---

## Architecture

```
┌─────────────────┐         ┌──────────────────┐         ┌────────────────┐
│   Next.js       │  HTTP   │   PHP Backend    │  MySQL  │   Database     │
│   Frontend      │◄───────►│   (API Layer)    │◄───────►│   Hostinger    │
│   (Port 3000)   │         │   (Port 80/443)  │         │   MySQL        │
└─────────────────┘         └──────────────────┘         └────────────────┘
```

---

## Local Testing (IMPORTANT - DO THIS FIRST)

Before deploying to production, test everything locally:

### Step 1: Start Your Local PHP Server

If you have PHP installed locally with a web server (XAMPP, MAMP, or similar):

```bash
# Make sure your PHP backend is running on http://localhost
# The Next.js app expects the PHP API at http://localhost/api
```

### Step 2: Start Next.js Development Server

```bash
cd redesign
npm run dev
```

The app will start on `http://localhost:3000`

### Step 3: Test Key Features
1. Homepage - should load featured properties
2. Browse all properties - `/properties`
3. View property details - click on any property
4. Check browser console for any API errors

**NOTE:** If you don't have the PHP backend running locally, the Next.js app will show empty states (no properties). This is expected. The integration will work once deployed to your VPS where both systems will be running.

---

## Production Deployment to VPS

### Prerequisites
✅ Ubuntu VPS with root access
✅ Domain pointed to VPS IP: `circlepointhomes.apartments`
✅ Software installed: nginx, PHP 8.3-fpm, MySQL, Node.js 18+, Composer

### Directory Structure on VPS
```
/var/www/circlepointhomes/
├── backend/              # PHP application (existing code)
│   ├── public/
│   ├── admin/
│   ├── api/
│   ├── includes/
│   └── uploads/
└── frontend/             # Next.js application (redesign folder)
    ├── .next/
    ├── app/
    ├── components/
    └── lib/
```

### Deployment Steps

#### 1. Upload Code to VPS

```bash
# On your LOCAL machine, from the project root:
rsync -avz --exclude 'node_modules' --exclude '.next' \
  . root@YOUR_VPS_IP:/var/www/circlepointhomes/backend/

rsync -avz --exclude 'node_modules' --exclude '.next' \
  redesign/ root@YOUR_VPS_IP:/var/www/circlepointhomes/frontend/
```

#### 2. On VPS - Set Up Backend

```bash
# SSH into VPS
ssh root@YOUR_VPS_IP

# Install PHP dependencies
cd /var/www/circlepointhomes/backend
composer install

# Set permissions
chown -R www-data:www-data /var/www/circlepointhomes/backend
chmod -R 755 /var/www/circlepointhomes/backend
chmod -R 775 /var/www/circlepointhomes/backend/uploads

# Copy .env file
cp .env /var/www/circlepointhomes/backend/.env
# Edit .env and update APP_URL to: https://circlepointhomes.apartments
```

#### 3. On VPS - Set Up Frontend

```bash
cd /var/www/circlepointhomes/frontend

# Update .env.production with your domain
cat > .env.production <<EOF
NEXT_PUBLIC_API_URL=https://circlepointhomes.apartments
NEXT_PUBLIC_API_BASE=/api
NEXT_PUBLIC_SITE_NAME=Circle Point Homes
NEXT_PUBLIC_SITE_URL=https://circlepointhomes.apartments
NEXT_PUBLIC_UPLOAD_URL=https://circlepointhomes.apartments/uploads
EOF

# Install dependencies and build
npm install --legacy-peer-deps
npm run build

# Set up PM2 to keep Next.js running
npm install -g pm2
pm2 start npm --name "circlepointhomes-frontend" -- start
pm2 save
pm2 startup
```

#### 4. Configure Nginx

Create nginx configuration:

```bash
nano /etc/nginx/sites-available/circlepointhomes
```

Paste this configuration:

```nginx
server {
    listen 80;
    server_name circlepointhomes.apartments www.circlepointhomes.apartments;

    # PHP Backend - Admin and API
    location /admin {
        root /var/www/circlepointhomes/backend;
        index index.php index.html;
        try_files $uri $uri/ /admin/index.php?$query_string;

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }

    location /api {
        root /var/www/circlepointhomes/backend;
        try_files $uri $uri/ /api/$uri.php?$query_string;

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }

    # Uploads directory
    location /uploads {
        alias /var/www/circlepointhomes/backend/uploads;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Next.js Frontend - Proxy all other requests
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Enable the site:

```bash
ln -s /etc/nginx/sites-available/circlepointhomes /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

#### 5. Set Up SSL Certificate

```bash
# Install Certbot
apt install certbot python3-certbot-nginx -y

# Get SSL certificate
certbot --nginx -d circlepointhomes.apartments -d www.circlepointhomes.apartments

# Certbot will automatically update your nginx config for HTTPS
```

#### 6. Verify Deployment

Test these URLs:
- `https://circlepointhomes.apartments` - Should show new Next.js frontend
- `https://circlepointhomes.apartments/admin` - Should show PHP admin panel
- `https://circlepointhomes.apartments/api/properties/list.php` - Should return JSON
- `https://circlepointhomes.apartments/uploads/` - Should serve uploaded images

---

## Troubleshooting

### Issue: Next.js shows empty property lists
**Solution:** Check that:
1. PHP backend is running and accessible
2. Database contains properties
3. API endpoints return JSON (test in browser)
4. CORS headers are set correctly in PHP files

### Issue: Images not loading
**Solution:**
1. Check `/uploads` directory permissions: `chmod -R 775 /var/www/circlepointhomes/backend/uploads`
2. Verify nginx `/uploads` location is correctly configured
3. Check that `NEXT_PUBLIC_UPLOAD_URL` is set correctly

### Issue: 502 Bad Gateway
**Solution:**
1. Check if Next.js is running: `pm2 status`
2. Restart: `pm2 restart circlepointhomes-frontend`
3. Check logs: `pm2 logs circlepointhomes-frontend`

### Issue: PHP API not working
**Solution:**
1. Check PHP-FPM is running: `systemctl status php8.3-fpm`
2. Check nginx error logs: `tail -f /var/log/nginx/error.log`
3. Verify database connection in `.env`

---

## Maintenance

### Update Frontend Code
```bash
cd /var/www/circlepointhomes/frontend
git pull  # or upload new files
npm install --legacy-peer-deps
npm run build
pm2 restart circlepointhomes-frontend
```

### Update Backend Code
```bash
cd /var/www/circlepointhomes/backend
git pull  # or upload new files
composer install
```

### View Logs
```bash
# Next.js logs
pm2 logs circlepointhomes-frontend

# Nginx logs
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# PHP logs
tail -f /var/log/php8.3-fpm.log
```

---

## Next Steps After Deployment

1. **Test all functionality thoroughly**
2. **Set up backups** - Database and uploads folder
3. **Monitor performance** - Use pm2 monitoring: `pm2 monit`
4. **Enable gzip compression** in nginx for better performance
5. **Set up monitoring** - Consider using services like UptimeRobot

---

## Important Notes

- The existing PHP admin panel remains unchanged and accessible at `/admin`
- All existing bookings, properties, and user data remain intact
- The new frontend is purely a UI upgrade - all backend logic is unchanged
- You can iterate and improve the frontend independently of the backend

---

## Support

If you encounter issues:
1. Check the logs (see Maintenance section)
2. Verify all environment variables are set correctly
3. Test API endpoints directly in the browser
4. Ensure database connection is working

---

**Generated:** November 2024
**Version:** 1.0
