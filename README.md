# CirclePoint Homes - Property Listing Platform

Modern PHP property listing platform built for Hostinger shared hosting.

## 🚀 Quick Start

### 1. Test Database Connection

Open in browser:
```
http://localhost:8000/test-connection.php
```

This will verify:
- ✅ Database connection works
- ✅ Tables exist
- ✅ Admin user is set up

### 2. Run Locally (Development)

```bash
cd circlepoint-homes-fresh
php -S localhost:8000
```

Then open: `http://localhost:8000/public/index.php`

### 3. Test Login

**Default Admin Credentials:**
- Email: `admin@circlepointhomes.com`
- Password: `admin123`

⚠️ **Change this immediately after first login!**

---

## 📁 Project Structure

```
circlepoint-homes-fresh/
├── .env                        # Environment configuration
├── test-connection.php         # Database test script
├── public/                     # Public pages
│   ├── index.php              # Homepage (property listings)
│   ├── login.php              # Login page
│   ├── signup.php             # Signup page
│   ├── account.php            # User account (coming next)
│   └── admin.php              # Admin dashboard (coming next)
├── api/                        # API endpoints
│   └── logout.php             # Logout endpoint
├── includes/                   # Core files
│   ├── config.php             # Database & config
│   ├── auth.php               # Authentication system
│   ├── functions.php          # Utility functions
│   ├── header.php             # Page header
│   └── footer.php             # Page footer
├── assets/                     # Static assets
│   ├── css/style.css          # Custom styles
│   └── js/app.js              # JavaScript
└── uploads/                    # File uploads directory
```

---

## ✅ What's Built (Phase 1 - Day 1)

### Core Infrastructure
- ✅ Database connection (Hostinger MySQL)
- ✅ Environment configuration (.env)
- ✅ Session management
- ✅ Authentication system (login/signup)

### Authentication
- ✅ User registration (email + password)
- ✅ User login with session
- ✅ Password hashing (bcrypt)
- ✅ Role-based access (user, admin, super_admin)
- ✅ Logout functionality

### UI/UX
- ✅ Responsive header with navigation
- ✅ Mobile menu
- ✅ Footer with social links
- ✅ Flash messages
- ✅ Modern gradient design
- ✅ Tailwind CSS + Font Awesome icons

---

## 🔧 Configuration

### Environment Variables (.env)

All configuration is in `.env` file:

```env
# Database (Hostinger MySQL) - Already configured
DB_HOST=auth-db517.hstgr.io
DB_USER=u514979897_app
DB_PASSWORD=@7u4gotT
DB_NAME=u514979897_cph
DB_PORT=3306

# Contact Info - UPDATE THESE
WHATSAPP_NUMBER=1234567890
INSTAGRAM_HANDLE=circlepointhomes
LINKEDIN_COMPANY=circlepointhomes
```

### Update Contact Information

Edit `.env` and change:
- `WHATSAPP_NUMBER` - Your WhatsApp number (with country code, no +)
- `INSTAGRAM_HANDLE` - Your Instagram username
- `LINKEDIN_COMPANY` - Your LinkedIn company name

---

## 📅 Development Timeline

### ✅ Day 1 (Completed)
- Project structure
- Database connection
- Authentication system
- Login/Signup pages
- Header/Footer

### 📋 Day 2 (Next - Tomorrow)
- Homepage with property listings
- Property detail page
- Property search/filter
- "List Your Property" button

### 📋 Day 3
- Property manager application system
- Application form
- Image upload for sample properties

### 📋 Day 4
- Super admin dashboard
- Approve/reject property managers
- Approve/reject properties

### 📋 Day 5
- Property manager portal
- Add/edit properties
- Multiple image upload

### 📋 Day 6
- Booking system
- User account dashboard
- Booking history
- Visa requirements

### 📋 Day 7
- Final testing
- Deploy to Hostinger
- **GO LIVE!** 🚀

---

## 🚀 Deployment to Hostinger

### Step 1: Prepare Files

1. Download FileZilla (FTP client): https://filezilla-project.org/
2. Get FTP credentials from Hostinger dashboard

### Step 2: Upload Files

1. Connect to Hostinger via FTP
2. Navigate to `public_html` folder
3. Upload all files from `circlepoint-homes-fresh/` to `public_html/`

### Step 3: Configure

1. Make sure `.env` file is uploaded
2. Set `APP_ENV=production` in `.env`
3. Update `APP_URL` to your domain

### Step 4: Test

1. Visit your website
2. Test login/signup
3. Create test property

---

## 🧪 Testing Checklist

### Local Testing
- [ ] Database connection works
- [ ] Can create new account
- [ ] Can login with credentials
- [ ] Can logout
- [ ] Mobile menu works
- [ ] Flash messages appear and disappear

### Hostinger Testing (After Deploy)
- [ ] Website loads
- [ ] Database connects
- [ ] Login works
- [ ] Signup works
- [ ] Images upload correctly

---

## 🔐 Security Notes

### Before Going Live:

1. **Change Admin Password**
   - Login with default credentials
   - Go to Account page
   - Change password immediately

2. **Update Super Admin Email**
   - Edit `.env`
   - Change `SUPER_ADMIN_EMAIL` to your email

3. **Production Mode**
   - Set `APP_ENV=production` in `.env`
   - Error messages will be hidden from users

4. **File Permissions**
   - `uploads/` folder: 755
   - `.env` file: 644

---

## 🆘 Troubleshooting

### Database Connection Failed
1. Check `.env` credentials
2. Verify IP is whitelisted in Hostinger
3. Run `test-connection.php`

### Can't Login
1. Verify user exists in database
2. Check session is enabled
3. Clear browser cookies

### Images Won't Upload
1. Check `uploads/` folder exists
2. Verify folder permissions (755)
3. Check `MAX_FILE_SIZE` in `.env`

### 404 Errors
1. Make sure you're accessing `/public/index.php`
2. Check file permissions
3. Verify all files uploaded to Hostinger

---

## 📞 Support

**Contact Sharon:**
- Project Directory: `/c/Users/Sharon/Videos/Wizzle/webapps/circlepoint-homes-fresh`

**Need Help?**
Just tell me what's not working and I'll fix it! 🚀

---

## 🎯 Next Steps

**Tomorrow (Day 2), we'll build:**
1. Homepage with property gallery
2. Property cards with images
3. Property detail pages
4. Search and filter functionality
5. "List Your Property" call-to-action

**Get some rest! We're on track for 1-week launch! 💪**
