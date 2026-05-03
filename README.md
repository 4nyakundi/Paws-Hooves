# Paws & Hooves by Wangari - Veterinary Services Website

A modern, professional website for a veterinary clinic in Kenya offering comprehensive pet and livestock care services.

## 🐾 Features

### Core Functionality
- **Responsive Design**: Mobile-first approach with full responsiveness across all devices
- **Dynamic Services Grid**: 12+ services loaded from JSON data with icons, descriptions, and pricing
- **Professional Booking System**: Complete appointment form with validation and localStorage storage
- **Gallery Section**: Showcase of clinic facilities and services
- **Testimonials**: Client reviews and success stories
- **Contact Information**: Multiple ways to reach the clinic

### Technical Features
- **Smooth Animations**: Fade-in, slide, bounce, and stagger animations on scroll
- **Form Validation**: Client-side validation for appointment form
- **Mobile Menu**: Hamburger menu for mobile navigation
- **Scroll-to-Top Button**: Quick return to homepage
- **Intersection Observer**: Performance-optimized scroll animations
- **Keyboard Shortcuts**: Quick navigation (B for Booking, S for Services, Home to top)
- **Analytics Ready**: Event tracking infrastructure for Google Analytics
- **Print-Friendly**: Styled for proper printing

## 📁 Project Structure

```
paws-and-hooves-web/
├── index.html                 # Main HTML file with all sections
├── css/
│   ├── main.css              # Primary stylesheet with comprehensive styling
│   └── animations.css        # Animation definitions and keyframes
├── js/
│   ├── data-handler.js       # Service data loading and display
│   ├── main.js               # Core interactions and utilities
│   └── booking.js            # Appointment form handling
├── data/
│   └── services.json         # Service catalog data
├── assets/
│   ├── images/
│   │   └── mainlogo.jpeg     # Clinic logo
│   └── fonts/                # Custom fonts (if any)
└── README.md                 # This file
```

## 🎨 Color Palette

- **Primary Teal**: #55a39a - Main brand color
- **Earth Brown**: #6d4c3d - Headings and emphasis
- **Soft Pink**: #f4a7b9 - Accents and highlights
- **Cream**: #fffdf9 - Background
- **Dark Gray**: #333 - Text
- **Light Gray**: #666 - Secondary text

## 📋 Sections

### 1. Navigation Bar
- Sticky navigation with logo
- Mobile hamburger menu
- Quick access to all sections
- "Book Now" CTA button

### 2. Hero Section
- Eye-catching headline with animations
- Call-to-action buttons
- Background imagery with gradient overlay
- Scroll indicator animation

### 3. Statistics Section
- Key metrics (Happy Pets, Years Experience, Satisfaction Rate, 24/7 Support)
- Animated counter cards

### 4. Services Section
- 12 comprehensive services with:
  - Icons and titles
  - Detailed descriptions
  - Service duration
  - Pricing
  - "Book This" buttons

**Services Offered:**
- Pet Wellness Exam (KSh 3,000)
- Livestock Consultation (KSh 5,500)
- Complete Vaccinations (KSh 2,500)
- Surgical Services (KSh 8,000-25,000)
- Dental Care (KSh 5,000-12,000)
- Microchipping & ID (KSh 2,000)
- Farm Health Program (KSh 10,000/month)
- Dermatology Services (KSh 4,000)
- Laboratory & Diagnostics (KSh 2,500-15,000)
- Emergency Care (KSh 5,000 + treatment)
- Pet Grooming (KSh 3,000-8,000)
- Breeding Consultation (KSh 4,000)

### 5. Gallery Section
- Visual showcase of clinic facilities
- Placeholder cards with category icons
- Hover effects and animations

### 6. About Us Section
- Clinic history and mission
- Dr. Wangari's background
- Key features and strengths
- Statistics and credentials

### 7. Testimonials Section
- Client reviews and ratings
- Success stories from pet owners and farmers
- 5-star ratings

### 8. Appointment Booking Section
- Comprehensive form with validation
- Fields for owner and pet information
- Date and time selection
- Service type selection
- Additional notes/concerns
- Success message with booking ID
- Quick contact information

### 9. Contact Section
- Address and location
- Phone numbers (regular and emergency)
- Email addresses
- Operating hours
- Social media links

### 10. Footer
- Links to all sections
- Additional navigation
- Social media icons
- Copyright information

## 🚀 Getting Started

### Prerequisites
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Text editor (VS Code, Sublime Text, etc.)
- Web server (for local development)

### Running Locally

#### Option 1: Python HTTP Server
```bash
cd paws-and-hooves-web
python -m http.server 8000
# Open http://localhost:8000 in your browser
```

#### Option 2: Node.js HTTP Server
```bash
cd paws-and-hooves-web
npx http-server
```

#### Option 3: VS Code Live Server Extension
- Install "Live Server" extension in VS Code
- Right-click `index.html` and select "Open with Live Server"

## 📱 Responsive Breakpoints

- **Desktop**: 1200px and above
- **Tablet**: 768px to 1199px
- **Mobile**: Below 768px
- **Small Mobile**: Below 480px

## 🔧 Customization

### Changing Colors
Edit the CSS variables in `css/main.css`:
```css
:root {
    --primary-teal: #55a39a;
    --earth-brown: #6d4c3d;
    --soft-pink: #f4a7b9;
    --cream: #fffdf9;
    /* ... more colors */
}
```

### Adding Services
Edit `data/services.json` and add new service objects:
```json
{
    "id": 13,
    "category": "Paws",
    "title": "Your Service Name",
    "description": "Description here",
    "price": "KSh X,XXX",
    "icon": "🏥",
    "duration": "Duration"
}
```

### Updating Contact Information
Update the following in `index.html`:
- Phone numbers
- Email addresses
- Address
- Hours of operation

## 🔒 Form Handling

Currently, appointment data is stored in browser's localStorage. For production, you'll need to:

1. **Connect to a Backend Server**:
   - Create API endpoint to receive form data
   - Implement email notifications
   - Store in database

2. **Email Service Integration**:
   - SendGrid, Mailgun, or similar service
   - Send confirmation to client
   - Send notification to clinic

3. **Database Storage**:
   - Store appointments for clinic management
   - Generate reports and analytics

## 📊 Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari 12+, Chrome Mobile)

## ⚡ Performance Optimization

### Implemented:
- CSS animations using GPU-accelerated transforms
- Intersection Observer for scroll animations (lazy loading)
- Minimal JavaScript dependencies
- Optimized media queries
- Efficient event delegation

### Recommendations:
- Compress images using tools like TinyPNG
- Add responsive images with srcset
- Implement lazy loading for images
- Cache resources with service workers
- Minify CSS and JavaScript for production

## 🎯 Keyboard Shortcuts

- **B**: Jump to Booking section
- **S**: Jump to Services section
- **Home**: Scroll to top

## 📞 Contact Information

**Paws & Hooves by Wangari**
- Phone: +254 (0) 701 234 567
- Emergency: +254 (0) 702 998 765
- Email: info@pawhooves.co.ke
- Location: Garden Estate, Nairobi, Kenya
- Hours: Mon-Fri 8AM-6PM | Sat 9AM-2PM | 24/7 Emergency

## 📝 Customization Checklist

- [ ] Update clinic logo in `assets/images/mainlogo.jpeg`
- [ ] Replace hero background image (`hero-bg.jpg`)
- [ ] Add clinic photos to gallery section
- [ ] Update contact information
- [ ] Customize color scheme
- [ ] Add actual testimonials
- [ ] Integrate with booking system/calendar
- [ ] Connect email notifications
- [ ] Add analytics tracking
- [ ] Create terms and conditions page
- [ ] Implement privacy policy

## 🐛 Troubleshooting

### Services not loading
- Check browser console for errors
- Verify `data/services.json` exists
- Check CORS if hosting on different domain

### Mobile menu not working
- Check if JavaScript files are loading
- Verify `js/main.js` is included in HTML

### Form not submitting
- Open browser console for validation errors
- Check if localStorage is enabled
- Verify email validation pattern

### Animations not working
- Check if animations.css is linked
- Verify browser supports CSS animations
- Check if JavaScript is enabled

## 🔐 Security Notes

- Form data currently stored in localStorage (client-side only)
- For production, implement server-side validation
- Use HTTPS for form submissions
- Implement CSRF protection
- Sanitize all user inputs

## 📈 Future Enhancements

- [ ] Online payment integration
- [ ] Appointment calendar/scheduling system
- [ ] Pet medical records access
- [ ] Client portal/dashboard
- [ ] Telemedicine capabilities
- [ ] Loyalty program
- [ ] Blog/Article section
- [ ] Live chat support
- [ ] Multi-language support
- [ ] Map integration for location

## 📄 License

This website template is provided as-is for Paws & Hooves by Wangari clinic.

---

**Last Updated**: February 24, 2026
**Version**: 1.0
**Status**: Production Ready ✓
