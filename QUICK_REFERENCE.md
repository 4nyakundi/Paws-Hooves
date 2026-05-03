# Quick Reference Guide - Paws & Hooves Website

## File Locations & Quick Links

### HTML Pages
- **Home Page**: `index.html` - Main landing page
- **Services**: `services.html` - Service directory
- **Clinic**: `clinic.html` - Gallery and facilities
- **Booking**: `appointment.html` - Appointment form

### Style Files
- **Main Styles**: `css/main.css` - Layout, colors, responsive design
- **Animations**: `css/animations.css` - All animations and keyframes

### JavaScript Files
- **Core Features**: `js/main.js` - Menu, scroll, keyboard shortcuts
- **Form Handling**: `js/booking.js` - Appointment form validation
- **Data Loading**: `js/data-handler.js` - Service data from JSON

### Configuration
- **Services Data**: `data/services.json` - Service details and pricing
- **Search Engines**: `sitemap.xml` - Page listing for search engines
- **Crawlers**: `robots.txt` - Instructions for web crawlers

### Documents
- **Overview**: `README.md` - Complete documentation
- **Accessibility**: `ACCESSIBILITY.md` - A11y compliance details
- **Deployment**: `DEPLOYMENT.md` - How to deploy and maintain
- **Project Status**: `PROJECT_COMPLETION.md` - Completion summary

---

## Common Tasks

### Update Services
**What to Edit**: `data/services.json`

```json
{
  "services": [
    {
      "id": "new-service",
      "name": "Service Name",
      "icon": "🏥",
      "description": "Description here",
      "duration": "30 minutes",
      "price": "KES 1,500-2,500",
      "category": "Category"
    }
  ]
}
```

**Result**: Automatically appears on services.html and in booking form

### Update Contact Information
**Where it appears**: All pages

Update in these files:
- `index.html` - Contact section (near footer)
- `services.html` - Contact section
- `clinic.html` - Contact section
- `appointment.html` - Contact info box

**Contact Details to Update**:
- Phone: `+254 (0) 701 234 567`
- Emergency: `+254 (0) 702 998 765`
- Email: `info@pawhooves.co.ke`
- Emergency Email: `emergency@pawhooves.co.ke`
- Address: `Garden Estate, Nairobi`
- Hours: `Mon-Fri: 8:00 AM - 6:00 PM`

### Change Theme Colors
**What to Edit**: `css/main.css`

Find the `:root` section at the top:
```css
:root {
    --primary-teal: #55a39a;      /* Main brand color */
    --earth-brown: #6d4c3d;       /* Headings */
    --soft-pink: #f4a7b9;         /* Accents */
    --cream: #fffdf9;             /* Background */
}
```

Change these hex colors to your preferred colors. The entire site will update automatically.

### Add New Testimonial
**What to Edit**: `index.html`

Find the "Testimonials Section" and add:
```html
<div class="testimonial-card">
    <div class="stars">★★★★★</div>
    <p class="testimonial-text">"Quote here..."</p>
    <p class="testimonial-author">— Name, Title</p>
</div>
```

### Modify Appointment Form
**What to Edit**: `appointment.html`

Add new fields in the form:
```html
<div class="form-group">
    <label for="fieldId">Field Label *</label>
    <input type="text" id="fieldId" name="fieldId" required>
</div>
```

Then update validation in `js/booking.js`:
```javascript
const fieldValue = document.getElementById('fieldId').value.trim();
if (!fieldValue) {
    showError('fieldId', 'This field is required');
}
```

### Update Opening Hours
**Where it appears**: 
- `appointment.html` - Appointment info box
- `clinic.html` - Contact section
- `index.html` - May appear in contact section

Search for "Mon-Fri" in each file and update the hours.

### Update Logo/Images
**Current Logo**: `assets/images/mainlogo.jpeg`

To change:
1. Save new logo as `mainlogo.jpeg` in `assets/images/`
2. Or replace all `mainlogo.jpeg` references with new filename

Used in:
- Navigation bar: `<img src="assets/images/mainlogo.jpeg">`
- Hero section: `<img src="assets/images/mainlogo.jpeg">`
- All pages use same logo

### Change Domain in Meta Tags
**What to Edit**: All HTML files

Find and replace `https://pawhooves.co.ke` with your actual domain in:
- Canonical URLs: `<link rel="canonical" href="..."`
- Open Graph URLs: `<meta property="og:url" content="..."`
- Twitter cards: `<meta name="twitter:..."`

### Update Sitemap for New Domain
**What to Edit**: `sitemap.xml`

Replace all instances of:
```xml
<loc>https://pawhooves.co.ke</loc>
```

With your domain:
```xml
<loc>https://your-domain.co.ke</loc>
```

---

## Keyboard Shortcuts (For Users)

Users can press these keys on any page:
- **B** - Quick book appointment
- **S** - Go to services page
- **C** - Go to clinic page
- **H** - Go to home page

---

## Form Validation Rules

### Appointment Form Fields
| Field | Rules | Error Message |
|-------|-------|----------------|
| Owner Name | Min 2 characters | "Please enter your full name" |
| Phone | Match pattern | "Please enter valid phone" |
| Email | Valid email (optional) | "Invalid email format" |
| Service Type | Required select | "Please select a service" |
| Pet Name | Required | "Pet name cannot be empty" |
| Pet Type | Required select | "Please select pet type" |
| Date | No past dates, no Sundays | "Please select valid date" |
| Time | Business hours only | "Please select valid time" |
| Terms | Must be checked | "Please agree to terms" |

### Testing the Form
1. Try submitting empty form - should show errors
2. Try invalid phone number - should highlight field
3. Try past date - should reject
4. Try Sunday date - should reject
5. Fill correctly and submit - should show success

---

## Accessibility Features

### For Screen Reader Users
- Press **Tab** to navigate through all links and buttons
- Press **Enter** to activate buttons
- Press **Spacebar** to check boxes
- Skip-link at top of page (press Tab once on page load)

### For Keyboard-Only Users
- All features accessible without mouse
- Tab order follows logical page flow
- Focus indicators visible on all elements
- Form validation provides clear error messages

### For Users with Low Vision
- High contrast colors (WCAG AA compliant)
- Large touch targets (44px minimum)
- Readable font sizes
- Resizable text (zoom up to 200%)

---

## Mobile Testing Checklist

- [ ] Test on iPhone (Safari)
- [ ] Test on Android (Chrome)
- [ ] Test on tablet (iPad)
- [ ] Hamburger menu works
- [ ] Touch targets large enough (44px+)
- [ ] Form fills and submits
- [ ] Text readable without zoom
- [ ] Images display correctly
- [ ] No horizontal scroll needed

---

## Browser Compatibility

### Supported Browsers
✅ Chrome 90+  
✅ Firefox 88+  
✅ Safari 12+  
✅ Edge 90+  
✅ Mobile Chrome  
✅ Mobile Safari  

### Not Supported
❌ Internet Explorer (use modern browser)  
❌ Older browser versions

---

## Performance Tips

### For Deployment
1. Enable GZIP compression on server
2. Set up browser caching headers
3. Minify CSS and JavaScript
4. Optimize images (especially logo)
5. Use CDN if available
6. Enable HTTPS (SSL/TLS)

### Monitoring Performance
- Use Chrome Lighthouse (DevTools > Lighthouse tab)
- Target scores: 90+ for all categories
- Check Core Web Vitals in Google Search Console
- Monitor with GTmetrix or WebPageTest

---

## Troubleshooting

### Problem: Form not submitting
**Solution**: 
- Check console for JavaScript errors (F12 > Console)
- Verify all required fields are filled
- Check booking.js is loading correctly
- For production: ensure backend email service configured

### Problem: Images not showing
**Solution**:
- Verify image files exist in `assets/images/`
- Check file paths are correct
- Clear browser cache (Ctrl+Shift+Delete)
- Check browser console for 404 errors

### Problem: Styles not applying
**Solution**:
- Check CSS files are in correct folder (`css/`)
- Clear browser cache
- Verify stylesheet is linked in HTML head
- Check for conflicting CSS rules

### Problem: Services not displaying
**Solution**:
- Verify `data/services.json` exists
- Check JSON formatting is valid
- Open browser console for fetch errors
- Ensure correct file paths

### Problem: Mobile menu not working
**Solution**:
- Check JavaScript console for errors
- Verify `main.js` is loading
- Check hamburger button exists in HTML
- Try clearing cache and refreshing

### Problem: Keyboard navigation not working
**Solution**:
- Check skip-link is present (press Tab immediately on page load)
- Verify focus indicators are visible
- Check all buttons have proper `role` attributes
- Test with different screen readers

---

## File Size Reference

| File | Size | Lines |
|------|------|-------|
| main.css | ~45KB | 1403 |
| animations.css | ~12KB | 468 |
| main.js | ~12KB | 387+ |
| booking.js | ~11KB | 320+ |
| data-handler.js | ~3KB | 100+ |
| services.json | ~2KB | 12 services |
| Total Website | ~150KB | - |

---

## Useful Resources

### Official Documentation
- MDN Web Docs: https://developer.mozilla.org/
- W3C Web Accessibility: https://www.w3.org/WAI/
- Web.dev: https://web.dev/

### Testing Tools
- Lighthouse: Built into Chrome DevTools (F12)
- WAVE: https://wave.webaim.org/
- axe DevTools: Browser extension
- Color Contrast Checker: https://webaim.org/resources/contrastchecker/

### Learning Resources
- HTML: https://developer.mozilla.org/en-US/docs/Web/HTML
- CSS: https://developer.mozilla.org/en-US/docs/Web/CSS
- JavaScript: https://developer.mozilla.org/en-US/docs/Web/JavaScript
- Accessibility: https://www.a11yproject.com/

---

## Support

### For Questions About:
- **Code**: Review README.md and code comments
- **Deployment**: See DEPLOYMENT.md
- **Accessibility**: See ACCESSIBILITY.md
- **Project Status**: See PROJECT_COMPLETION.md

### Clinic Contact
- Phone: +254 (0) 701 234 567
- Email: info@pawhooves.co.ke
- Address: Garden Estate, Nairobi, Kenya

---

## Last Updated
2024

## Status
✅ Complete & Production-Ready
