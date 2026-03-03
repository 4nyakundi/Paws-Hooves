# EmailJS Setup Guide for Paws & Hooves

This guide will help you enable automatic email notifications for appointment bookings.

## What You'll Get
- ✅ Automatic confirmation emails sent to clients
- ✅ Booking notifications sent to doctor (careywangaretracy@gmail.com)
- ✅ WhatsApp integration link for client-doctor communication
- ✅ All appointment details in both client and doctor emails

## Step-by-Step Setup

### 1. Sign Up for EmailJS
1. Go to [EmailJS.com](https://www.emailjs.com/)
2. Click **Sign Up** (Free tier is sufficient)
3. Create your account with email and password
4. Verify your email address

### 2. Get Your Public ID
1. Log in to EmailJS dashboard
2. Go to **Account** → **General**
3. Copy your **Public ID** (looks like: `abc123def456`)
4. Open `js/booking.js` in your editor
5. Find this line at the top:
   ```javascript
   const EMAILJS_PUBLIC_ID = 'YOUR_EMAILJS_PUBLIC_ID';
   ```
6. Replace `YOUR_EMAILJS_PUBLIC_ID` with your actual Public ID:
   ```javascript
   const EMAILJS_PUBLIC_ID = 'abc123def456';
   ```
7. Save the file

### 3. Create Email Service
1. In EmailJS dashboard, go to **Email Services**
2. Click **Add Service**
3. Choose **Gmail** (or your email provider)
4. Configure with your email settings:
   - **Service Name**: `pawshooves_service` (or similar)
   - **Service ID**: Should be auto-generated (use the Service ID we configured: `service_pawshooves`)
   - **Email**: Enter careywangaretracy@gmail.com
5. Complete the connection process
6. **Important**: Get the Service ID from the settings

### 4. Create Email Templates
1. In EmailJS dashboard, go to **Email Templates**
2. Click **Create New Template**

#### Template 1: Client Confirmation Email
1. **Template Name**: `Appointment Client Confirmation`
2. **Subject**:
   ```
   Appointment Confirmed - Booking ID: {{booking_id}}
   ```
3. **Body**:
   ```
   Dear {{to_name}},

   Your appointment has been successfully booked!

   📋 BOOKING DETAILS
   Booking ID: {{booking_id}}
   Pet/Livestock: {{pet_name}} ({{pet_type}})
   Service: {{service}}
   Date: {{appointment_date}}
   Time: {{appointment_time}}

   📞 CLINIC CONTACT INFORMATION
   Phone: {{doctor_phone}}
   WhatsApp: {{doctor_phone}}
   Email: {{doctor_email}}
   Clinic: {{clinic_name}}

   We will confirm your appointment within 2 hours via phone or email.
   If you need to reschedule, please contact us using the details above.

   Thank you for choosing Paws & Hooves!
   ```

#### Template 2: Doctor Notification Email
1. **Template Name**: `Appointment Doctor Notification`
2. **Subject**:
   ```
   New Booking: {{pet_name}} - {{appointment_date}} at {{appointment_time}}
   ```
3. **Body**:
   ```
   New Appointment Booking

   📌 BOOKING ID: {{booking_id}}

   👤 CLIENT INFORMATION
   Name: {{client_name}}
   Phone: {{client_phone}}
   Email: {{client_email}}

   🐾 PET/LIVESTOCK INFORMATION
   Name: {{pet_name}}
   Type: {{pet_type}}
   Service: {{service}}

   📅 APPOINTMENT DETAILS
   Date: {{appointment_date}}
   Time: {{appointment_time}}

   📝 NOTES FROM CLIENT
   {{notes}}

   Please confirm this appointment within 2 hours via phone or WhatsApp.
   ```

5. Save each template and note the **Template ID**

### 5. Update Template IDs in Code
1. Open `js/booking.js`
2. Find these lines:
   ```javascript
   const EMAILJS_SERVICE_ID = 'service_pawshooves';
   const EMAILJS_TEMPLATE_ID = 'template_appointment';
   ```
3. Replace with your actual IDs from EmailJS:
   ```javascript
   const EMAILJS_SERVICE_ID = 'your_service_id_here';
   const EMAILJS_TEMPLATE_ID = 'your_template_id_here';
   ```

### 6. Test the System
1. Open your website locally (http://localhost:8000)
2. Go to the Appointment page
3. Fill out the form with test data
4. Submit the form
5. Check that:
   - Confirmation message appears
   - Client email receives the confirmation (check inbox + spam folder)
   - Doctor email receives the notification
   - WhatsApp and call buttons work

## Troubleshooting

### Emails Not Sending
- **Check Console**: Open browser DevTools (F12) → Console tab
- **Look for error messages** mentioning EmailJS
- **Common Issues**:
  - Public ID not set correctly → Re-check your account settings
  - Service/Template IDs wrong → Copy-paste from EmailJS dashboard
  - Gmail not authorized → Re-do the Gmail connection in EmailJS

### WhatsApp Not Opening
- Make sure the phone number format is correct: `0759396531` (or `+254759396531`)
- Links should open WhatsApp Web or app

### Client Email Not Provided
- If client doesn't enter email, only doctor gets notified
- Email to client is skipped (not an error)

## Free Tier Limits
- **EmailJS Free**: 200 emails/month
- **Upgrade** when you exceed this
- Perfect for small clinic bookings

## Security Note
Your Public ID is safe to share (it's meant to be public). Never share:
- Private Keys
- Service credentials
- Gmail passwords

## Support
- EmailJS Help: support@emailjs.com
- Visit: https://www.emailjs.com/docs/

---

**Status**: 
- ✅ Email system integrated in code
- ⏳ Awaiting EmailJS configuration
- 🔄 Once configured, system is fully functional

Document created: March 3, 2026
