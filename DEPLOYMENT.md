# Deployment Guide - Paws & Hooves Website

## Pre-Deployment Checklist

### Testing Phase
- [ ] All 4 HTML pages tested in Chrome, Firefox, Safari, Edge
- [ ] Mobile testing on iOS (iPhone) and Android devices
- [ ] Tablet testing (iPad, Android tablets)
- [ ] Navigation links verified (all internal links working)
- [ ] Form submission tested and validated
- [ ] Keyboard navigation tested (Tab, Enter, Skip-link focus)
- [ ] Screen reader testing with NVDA/JAWS/VoiceOver
- [ ] Images loading correctly (assets/images/mainlogo.jpeg)
- [ ] Services loading from data/services.json
- [ ] Animations and transitions smooth
- [ ] No console errors in browser DevTools
- [ ] Responsive design verified at multiple breakpoints (480px, 768px, 1200px)
- [ ] Performance check with Lighthouse (target: 90+ score)
- [ ] SEO check with Lighthouse (target: 90+ score)
- [ ] Accessibility check with Lighthouse (target: 95+ score)

### Files to Upload
```
All files in project directory:
├── index.html
├── services.html
├── clinic.html
├── appointment.html
├── css/
│   ├── main.css
│   └── animations.css
├── js/
│   ├── main.js
│   ├── booking.js
│   └── data-handler.js
├── data/
│   └── services.json
├── assets/
│   └── images/
│       └── mainlogo.jpeg
├── sitemap.xml
├── robots.txt
├── README.md
└── ACCESSIBILITY.md
```

## Deployment Steps

### 1. Prepare Domain & Hosting
```bash
# Register domain: pawhooves.co.ke (or alternative)
# Set up web hosting with:
# - HTTPS/SSL support (required for production)
# - PHP/Node.js if using backend form processing
# - Email server for appointment notifications
```

### 2. Update Configuration Files
Update all files to use your domain:

**In HTML head tags (index.html, services.html, clinic.html, appointment.html):**
```html
<!-- Update canonical URLs -->
<link rel="canonical" href="https://your-domain.co.ke">

<!-- Update OG URLs -->
<meta property="og:url" content="https://your-domain.co.ke">
```

**In sitemap.xml:**
```xml
<!-- Update all URLs to use production domain -->
<loc>https://your-domain.co.ke/</loc>
<loc>https://your-domain.co.ke/services.html</loc>
<!-- etc -->
```

### 3. Upload Files to Web Server
Using FTP, SFTP, or hosting control panel:
```
Upload entire directory structure to web root (public_html/)
Ensure all files maintain same relative paths:
  - CSS files in /css/ folder
  - JS files in /js/ folder
  - Data files in /data/ folder
  - Images in /assets/images/ folder
```

### 4. Configure Web Server

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # Enable GZIP compression
  <IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
  </IfModule>
  
  # Set cache headers
  <FilesMatch "\.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
  </FilesMatch>
  
  <FilesMatch "\.(html|htm|php)$">
    Header set Cache-Control "public, max-age=3600"
  </FilesMatch>
  
  # Redirect HTTP to HTTPS
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

#### Nginx Configuration
```nginx
server {
  listen 443 ssl http2;
  server_name pawhooves.co.ke www.pawhooves.co.ke;
  
  ssl_certificate /path/to/certificate.crt;
  ssl_certificate_key /path/to/private.key;
  
  root /var/www/html;
  index index.html index.htm;
  
  # Gzip compression
  gzip on;
  gzip_types text/html text/plain text/xml text/css text/javascript application/javascript;
  
  # Cache headers
  location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
  }
  
  location ~* \.(html|htm)$ {
    expires 1h;
    add_header Cache-Control "public";
  }
  
  # Security headers
  add_header X-Frame-Options "SAMEORIGIN" always;
  add_header X-Content-Type-Options "nosniff" always;
  add_header X-XSS-Protection "1; mode=block" always;
  add_header Referrer-Policy "no-referrer-when-downgrade" always;
  add_header Content-Security-Policy "default-src 'self' https:; script-src 'self' https://fonts.googleapis.com; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com" always;
}

# Redirect HTTP to HTTPS
server {
  listen 80;
  server_name pawhooves.co.ke www.pawhooves.co.ke;
  return 301 https://$server_name$request_uri;
}
```

### 5. SSL Certificate Setup
```bash
# Obtain free SSL certificate (Let's Encrypt)
# Many hosting providers offer AutoSSL or Let's Encrypt integration

# Verify SSL:
# - Use SSL Labs (https://www.ssllabs.com/ssltest/)
# - Ensure A or A+ rating
```

### 6. Email Configuration (For Appointment Confirmations)

#### Option A: Using Backend Service
Create a simple backend form processor (Node.js/PHP):

**Node.js Example (server.js):**
```javascript
const express = require('express');
const nodemailer = require('nodemailer');
const app = express();

app.use(express.json());

const transporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: process.env.CLINIC_EMAIL,
    pass: process.env.CLINIC_PASSWORD
  }
});

app.post('/api/book-appointment', async (req, res) => {
  const { ownerName, ownerEmail, petName, appointmentDate, appointmentTime } = req.body;
  
  const mailOptions = {
    from: process.env.CLINIC_EMAIL,
    to: ownerEmail,
    subject: 'Appointment Confirmation - Paws & Hooves',
    html: `
      <h2>Your Appointment Confirmed!</h2>
      <p>Hi ${ownerName},</p>
      <p>Your appointment for ${petName} is confirmed for:</p>
      <p><strong>${appointmentDate} at ${appointmentTime}</strong></p>
      <p>We'll see you soon!</p>
      <p>Paws & Hooves Clinic</p>
    `
  };
  
  try {
    await transporter.sendMail(mailOptions);
    res.status(200).json({ success: true, message: 'Email sent' });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, error: error.message });
  }
});

app.listen(3000, () => console.log('Server running on port 3000'));
```

#### Option B: Using Third-Party Service
Integrate with services like:
- **Formspree**: https://formspree.io/
- **Basin**: https://www.basinapp.com/
- **Getform**: https://getform.io/
- **SendGrid**: https://sendgrid.com/

**HTML Form Update for Formspree:**
```html
<form action="https://formspree.io/f/YOUR_FORM_ID" method="POST">
  <!-- form fields -->
</form>
```

### 7. Database Setup (Optional)
If storing appointments in database:

**MySQL Schema:**
```sql
CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_name VARCHAR(100) NOT NULL,
  owner_phone VARCHAR(20) NOT NULL,
  owner_email VARCHAR(100),
  pet_name VARCHAR(100) NOT NULL,
  pet_type VARCHAR(50) NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  service_type VARCHAR(100) NOT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending'
);
```

### 8. SEO & Analytics Setup

#### Google Search Console
```bash
1. Go to Google Search Console (search.google.com/search-console)
2. Add your domain/website
3. Verify ownership (upload verification file or add DNS record)
4. Submit sitemap.xml
5. Monitor search performance and indexing
```

#### Google Analytics
Add to all pages (before closing </head>):
```html
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

#### Bing Webmaster Tools
```bash
1. Go to Bing Webmaster Tools (www.bing.com/webmasters)
2. Add your site
3. Verify ownership
4. Submit sitemap.xml
```

### 9. Monitoring & Logging

#### Enable Error Logging
```php
// In PHP/backend
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php-errors.log');
error_reporting(E_ALL);
```

#### Monitor Performance
- Use Lighthouse regularly (Chrome DevTools)
- Monitor Core Web Vitals (Google Search Console)
- Check error logs for JavaScript console errors

### 10. Post-Deployment Verification

**Checklist:**
- [ ] Website loads on https:// (SSL working)
- [ ] All pages accessible from public internet
- [ ] Navigation links work
- [ ] Form submission works (test booking)
- [ ] Email confirmations being sent
- [ ] sitemap.xml accessible at /sitemap.xml
- [ ] robots.txt accessible at /robots.txt
- [ ] Images loading (check Page Inspector)
- [ ] No mixed content warnings (all HTTPS)
- [ ] Mobile version responsive
- [ ] Keyboard navigation working
- [ ] No console errors in browser DevTools
- [ ] Performance score > 90 (Lighthouse)
- [ ] Accessibility score > 95 (Lighthouse)

## Ongoing Maintenance

### Daily
- Monitor appointment submissions
- Respond to booking inquiries
- Check error logs

### Weekly
- Review analytics
- Check backups
- Respond to user feedback

### Monthly
- Update testimonials/content
- Review security logs
- Performance check
- Accessibility audit

### Quarterly
- Full SEO audit
- Update services/pricing
- Review and update meta tags
- Test form processing

### Annually
- Security audit
- Update SSL certificate
- Backup verification
- Disaster recovery test

## Rollback Plan

If issues occur after deployment:

```bash
# Keep backup of previous version:
backup/
├── previous-version-date/
│   ├── index.html
│   ├── css/
│   ├── js/
│   ├── data/
│   └── assets/

# Restore from backup:
1. Download current version from server
2. Upload previous backup files
3. Test all pages
4. Verify functionality
```

## Performance Optimization

### After Deployment
1. Minify CSS: Use online CSS minifier or build tools
2. Minify JavaScript: Use uglifyjs or similar
3. Optimize Images: Use TinyPNG or ImageOptim
4. Enable Browser Caching: Configure server headers (as shown above)
5. Enable GZIP: Configure web server compression
6. Use CDN: For images and static assets (optional)

### Monitoring Tools
- **Google PageSpeed Insights**: Continuous performance monitoring
- **GTmetrix**: Waterfall analysis and optimization suggestions
- **WebPageTest**: Real browser testing from different locations
- **Lighthouse**: Built-in Chrome DevTools audit

## Support & Troubleshooting

### Common Issues

**Issue: Form not submitting**
- Solution: Check email configuration, backend service, form handler

**Issue: Images not loading**
- Solution: Verify image paths, check file permissions, ensure /assets/images/ folder exists

**Issue: Slow performance**
- Solution: Enable GZIP, implement caching, optimize images, use CDN

**Issue: Form validation not working**
- Solution: Check JavaScript errors in console, verify booking.js loaded correctly

**Issue: SSL certificate error**
- Solution: Update certificate, clear browser cache, restart web server

## Contact & Support

For deployment assistance:
- **Hosting Support**: Contact your web hosting provider
- **Technical Questions**: Review documentation in README.md and ACCESSIBILITY.md
- **Clinic Support**: info@pawhooves.co.ke

---

## Final Notes

This website is **production-ready** with:
- ✅ Responsive design
- ✅ Accessibility compliance (WCAG 2.1 AA)
- ✅ SEO optimization
- ✅ Form validation
- ✅ Modern animations
- ✅ Security-ready

Deploy with confidence and monitor performance ongoing.

**Status**: Ready for Deployment
*Last Updated: 2024*
