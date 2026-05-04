// ============ APPOINTMENT BOOKING SYSTEM ============

// Signal to main.js that the booking system has successfully loaded
window.bookingSystemLoaded = true;

// EmailJS Configuration
const DOCTOR_INFO = {
    email: 'careywangaretracy@gmail.com',
    phone: '0759396531',
    name: 'Paws & Hooves Clinic'
};

// Initialize EmailJS - Replace with your actual Public ID from emailjs.com
// Sign up at: https://www.emailjs.com/
// Add your Public ID here
const EMAILJS_PUBLIC_ID = 'Zz3nnMTyMKGIGiui0'; // Get this from emailjs.com dashboard
const EMAILJS_SERVICE_ID = 'service_pawshooves'; // Will create this in emailjs
const CLIENT_TEMPLATE_ID = 'template_client_confirmation'; // Template for client confirmation
const DOCTOR_TEMPLATE_ID = 'template_doctor_notification'; // Template for doctor notification

// Initialize EmailJS
if (typeof emailjs !== 'undefined') {
    emailjs.init(EMAILJS_PUBLIC_ID);
}

document.addEventListener('DOMContentLoaded', () => {
    const appointmentForm = document.getElementById('appointmentForm');
    
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', handleAppointmentSubmit);
        
        // Prevent accidental submit on Enter (except in textarea)
        appointmentForm.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });

        // Set minimum date to today
        const dateInput = document.getElementById('appointmentDate');
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        // Optional: Disable past dates and weekends
        dateInput.addEventListener('change', validateDate);
    }
});

// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-analytics.js";
import { getFirestore, collection, addDoc } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-firestore.js";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyBAIkjUgQpSoIMDdKDgPhlqKlNs6LlAivo",
  authDomain: "paws-and-hooves.firebaseapp.com",
  projectId: "paws-and-hooves",
  storageBucket: "paws-and-hooves.firebasestorage.app",
  messagingSenderId: "101044561812",
  appId: "1:101044561812:web:31d114c1e7c8da50399181",
  measurementId: "G-XQ9Y7Y12EX"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const db = getFirestore(app);

/**
 * Validates appointment date
 * - Prevents Sundays (0) and public holidays
 * - Prevents dates before today
 */
function validateDate(event) {
    const selectedDate = new Date(event.target.value);
    const dayOfWeek = selectedDate.getDay();
    
    // Disable Sundays (0)
    if (dayOfWeek === 0) {
        showFormError('Sundays are not available. Please select another day.', 'appointmentDate');
        event.target.value = '';
        return false;
    }
    
    // Remove error styling if date is valid
    const field = document.getElementById('appointmentDate');
    if (field.parentElement.classList.contains('error')) {
        field.parentElement.classList.remove('error');
    }
    
    return true;
}

/**
 * Handles appointment form submission
 * Validates form, saves to localStorage, shows confirmation
 */
function handleAppointmentSubmit(event) {
    console.log('handleAppointmentSubmit fired');
    event.preventDefault();
    
    // Clear previous errors
    clearFormErrors();
    
    // Get form data
    const formData = {
        ownerName: document.getElementById('ownerName').value.trim(),
        ownerPhone: document.getElementById('ownerPhone').value.trim(),
        ownerEmail: document.getElementById('ownerEmail').value.trim(),
        appointmentType: document.getElementById('appointmentType').value,
        petName: document.getElementById('petName').value.trim(),
        petType: document.getElementById('petType').value,
        appointmentDate: document.getElementById('appointmentDate').value,
        appointmentTime: document.getElementById('appointmentTime').value,
        appointmentNotes: document.getElementById('appointmentNotes').value.trim(),
        bookingId: generateBookingId(),
        bookingDate: new Date().toLocaleString()
    };
    
    // Validate form data
    if (!validateAppointmentForm(formData)) {
        return;
    }
    
    // Save to localStorage (in production, this would be sent to a server)
    saveAppointment(formData);
    
    // Show success message
    showSuccessMessage(formData);
    
    // Reset form
    document.getElementById('appointmentForm').reset();
    document.getElementById('appointmentDate').min = new Date().toISOString().split('T')[0];
}

/**
 * Validates appointment form data
 */
function validateAppointmentForm(data) {
    const errors = [];
    
    // Validate name
    if (!data.ownerName || data.ownerName.length < 2) {
        errors.push({ message: 'Please enter a valid name (at least 2 characters)', field: 'ownerName' });
    }
    
    // Validate phone
    if (!data.ownerPhone || !/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(data.ownerPhone.replace(/\s/g, ''))) {
        errors.push({ message: 'Please enter a valid phone number', field: 'ownerPhone' });
    }
    
    // Validate email (if provided)
    if (data.ownerEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.ownerEmail)) {
        errors.push({ message: 'Please enter a valid email address', field: 'ownerEmail' });
    }
    
    // Validate appointment type
    if (!data.appointmentType) {
        errors.push({ message: 'Please select a service type', field: 'appointmentType' });
    }
    
    // Validate pet name
    if (!data.petName || data.petName.length < 1) {
        errors.push({ message: 'Please enter your pet/livestock name', field: 'petName' });
    }
    
    // Validate pet type
    if (!data.petType) {
        errors.push({ message: 'Please select a pet/livestock type', field: 'petType' });
    }
    
    // Validate date
    if (!data.appointmentDate) {
        errors.push({ message: 'Please select an appointment date', field: 'appointmentDate' });
    } else {
        const selectedDate = new Date(data.appointmentDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            errors.push({ message: 'Please select a future date', field: 'appointmentDate' });
        }
        
        if (selectedDate.getDay() === 0) {
            errors.push({ message: 'We are closed on Sundays. Please select another day', field: 'appointmentDate' });
        }
    }
    
    // Validate time
    if (!data.appointmentTime) {
        errors.push({ message: 'Please select an appointment time', field: 'appointmentTime' });
    }
    
    // Validate terms checkbox
    const termsCheckbox = document.getElementById('termsAgree');
    if (!termsCheckbox.checked) {
        errors.push({ message: 'Please agree to the terms and conditions', field: 'termsAgree' });
    }
    
    // Display errors if any
    if (errors.length > 0) {
        errors.forEach(error => showFormError(error.message, error.field));
        displayErrorAnimation();
        return false;
    }
    
    return true;
}

/**
 * Saves appointment to localStorage, Firestore, and sends emails
 */
async function saveAppointment(appointment) {
    // 1. Save to localStorage as a backup/local cache
    let appointments = JSON.parse(localStorage.getItem('appointments')) || [];
    appointments.push(appointment);
    localStorage.setItem('appointments', JSON.stringify(appointments));
    
    // 2. Save to Firebase Firestore
    try {
        const docRef = await addDoc(collection(db, "appointments"), appointment);
        console.log("Document written to Firebase with ID: ", docRef.id);
    } catch (e) {
        console.error("Error adding document to Firebase: ", e);
        // We still continue even if Firebase fails so the user gets their confirmation
    }
    
    // 3. Send emails to both client and doctor
    sendAppointmentEmails(appointment);
    
    // 4. Send WhatsApp notification via backend server (Uncomment when server is deployed)
    /*
    fetch('https://your-deployed-server.onrender.com/book', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(appointment)
    })
    .then(r => r.json())
    .then(data => console.log('WhatsApp ping sent', data))
    .catch(err => console.error('WhatsApp error', err));
    */
    
    // Log to console for testing
    console.log('Appointment saved:', appointment);
}

/**
 * Sends appointment confirmation emails to client and doctor
 */
function sendAppointmentEmails(appointment) {
    // Only send if EmailJS is properly configured
    if (EMAILJS_PUBLIC_ID === 'YOUR_EMAILJS_PUBLIC_ID') {
        console.log('⚠️ EmailJS not configured. To enable email notifications:');
        console.log('1. Sign up at https://www.emailjs.com/');
        console.log('2. Get your Public ID from the dashboard');
        console.log('3. Replace YOUR_EMAILJS_PUBLIC_ID in booking.js');
        console.log('Appointment data:', appointment);
        return;
    }

    // 1. Email to Client
    if (appointment.ownerEmail) {
        const clientEmailParams = {
            to_email: appointment.ownerEmail,
            name: appointment.ownerName,
            to_name: appointment.ownerName,
            time: new Date().toLocaleTimeString(),
            booking_id: appointment.bookingId,
            pet_name: appointment.petName,
            pet_type: appointment.petType,
            service: appointment.appointmentType,
            appointment_date: formatDate(appointment.appointmentDate),
            appointment_time: appointment.appointmentTime,
            doctor_phone: DOCTOR_INFO.phone,
            doctor_email: DOCTOR_INFO.email,
            clinic_name: DOCTOR_INFO.name
        };

        emailjs.send(EMAILJS_SERVICE_ID, CLIENT_TEMPLATE_ID, clientEmailParams)
            .then((response) => {
                console.log('✓ Confirmation email sent to client:', appointment.ownerEmail);
            })
            .catch((error) => {
                console.error('Error sending client email:', error);
            });
    }

    // 2. Email to Doctor/Clinic
    const doctorEmailParams = {
        to_email: DOCTOR_INFO.email, // The clinic's email
        name: appointment.ownerName,
        booking_id: appointment.bookingId,
        client_name: appointment.ownerName,
        client_phone: appointment.ownerPhone,
        client_email: appointment.ownerEmail || 'Not provided',
        pet_name: appointment.petName,
        pet_type: appointment.petType,
        service: appointment.appointmentType,
        appointment_date: formatDate(appointment.appointmentDate),
        appointment_time: appointment.appointmentTime,
        notes: appointment.appointmentNotes || 'None'
    };

    emailjs.send(EMAILJS_SERVICE_ID, DOCTOR_TEMPLATE_ID, doctorEmailParams)
        .then((response) => {
            console.log('✓ Booking notification sent to doctor');
        })
        .catch((error) => {
            console.error('Error sending doctor email:', error);
        });
}

/**
 * Generates a unique booking ID
 */
function generateBookingId() {
    return 'PAH-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
}

/**
 * Shows success message with booking details as a professional ticket/modal
 */
function showSuccessMessage(bookingData) {
    const whatsappLink = `https://wa.me/${DOCTOR_INFO.phone.replace(/\D/g, '')}?text=Hi%20Paws%20%26%20Hooves%2C%20I%20have%20a%20booking%20confirmation%20with%20ID%20${bookingData.bookingId}`;
    
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'ticket-modal-overlay';
    overlay.id = 'ticketModalOverlay';
    
    // Create modal container
    const modal = document.createElement('div');
    modal.className = 'ticket-modal';
    modal.id = 'appointmentTicket';
    
    modal.innerHTML = `
        <div class="ticket-container">
            <!-- Close button -->
            <button class="ticket-close-btn" onclick="closeTicketModal()">✕</button>
            
            <!-- Ticket Header with Logo -->
            <div class="ticket-header">
                <img src="assets/images/mainlogo.png" alt="Paws & Hooves Logo" class="ticket-logo">
                <h2>Appointment Confirmed</h2>
                <p class="clinic-name">Paws & Hooves Veterinary Clinic</p>
            </div>
            
            <!-- Booking ID Badge -->
            <div class="booking-id-badge">
                <span class="badge-label">Booking ID:</span>
                <span class="badge-value">${bookingData.bookingId}</span>
            </div>
            
            <!-- Main Content -->
            <div class="ticket-content">
                <!-- Welcome Message -->
                <div class="welcome-message">
                    <p>Dear <strong>${bookingData.ownerName}</strong>,</p>
                    <p>Your appointment has been successfully booked! Please save this confirmation for your records.</p>
                </div>
                
                <!-- Patient Information -->
                <div class="ticket-section">
                    <h3 class="section-title">🐾 Patient Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Pet/Livestock Name:</span>
                            <span class="info-value">${bookingData.petName}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Type:</span>
                            <span class="info-value">${bookingData.petType}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Appointment Details -->
                <div class="ticket-section">
                    <h3 class="section-title">📅 Appointment Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Service:</span>
                            <span class="info-value">${bookingData.appointmentType}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date:</span>
                            <span class="info-value">${formatDate(bookingData.appointmentDate)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Time:</span>
                            <span class="info-value">${bookingData.appointmentTime}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Duration:</span>
                            <span class="info-value">30-45 minutes</span>
                        </div>
                    </div>
                </div>
                
                <!-- Client Information -->
                <div class="ticket-section">
                    <h3 class="section-title">👤 Your Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value">${bookingData.ownerName}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value">${bookingData.ownerPhone}</span>
                        </div>
                        ${bookingData.ownerEmail ? `
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value">${bookingData.ownerEmail}</span>
                        </div>
                        ` : ''}
                        ${bookingData.appointmentNotes ? `
                        <div class="info-item full-width">
                            <span class="info-label">Notes:</span>
                            <span class="info-value">${bookingData.appointmentNotes}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <!-- Clinic Contact -->
                <div class="ticket-section clinic-contact-section">
                    <h3 class="section-title">📞 Clinic Contact Information</h3>
                    <div class="contact-details">
                        <p><strong class="contact-label">Phone:</strong> ${DOCTOR_INFO.phone}</p>
                        <p><strong class="contact-label">Email:</strong> ${DOCTOR_INFO.email}</p>
                        <p><strong class="contact-label">Location:</strong> Nairobi & Kiambu (Mobile Vet)</p>
                    </div>
                </div>
                
                <!-- Important Notes -->
                <div class="ticket-section important-notes">
                    <h3 class="section-title">⚠️ Important</h3>
                    <ul>
                        <li>The clinic will confirm your appointment within 2 hours via phone/email</li>
                        <li>Please arrive 10 minutes early for your appointment</li>
                        <li>Bring any medical records if available</li>
                        <li>For emergencies, please call/WhatsApp the clinic immediately</li>
                    </ul>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="ticket-actions">
                <button class="ticket-btn save-pdf-btn" onclick="downloadTicketPDF('${bookingData.bookingId}')">
                    📥 Save as PDF
                </button>
                <button class="ticket-btn save-image-btn" onclick="downloadTicketImage('${bookingData.bookingId}')">
                    🖼️ Save as Image
                </button>
                <a href="${whatsappLink}" target="_blank" class="ticket-btn whatsapp-contact-btn">
                    💬 Message Clinic on WhatsApp
                </a>
            </div>
            
            <!-- Footer -->
            <div class="ticket-footer">
                <p>Confirmation has been sent to ${bookingData.ownerEmail || 'your email'}</p>
                <p class="ticket-date">Booked on: ${bookingData.bookingDate}</p>
            </div>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Show modal with animation
    setTimeout(() => {
        overlay.classList.add('show');
    }, 10);
    
    // Close on overlay click (outside modal)
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeTicketModal();
        }
    });
}

/**
 * Close the ticket modal
 */
window.closeTicketModal = function() {
    const overlay = document.getElementById('ticketModalOverlay');
    if (overlay) {
        overlay.classList.remove('show');
        setTimeout(() => overlay.remove(), 300);
    }
}

/**
 * Download ticket as PDF
 */
window.downloadTicketPDF = function(bookingId) {
    const element = document.getElementById('appointmentTicket');
    if (!element) return;
    
    const opt = {
        margin: 10,
        filename: `PAH-Booking-${bookingId}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
    };
    
    html2pdf().set(opt).from(element).save();
}

/**
 * Download ticket as image
 */
window.downloadTicketImage = function(bookingId) {
    const element = document.getElementById('appointmentTicket');
    if (!element) return;
    
    html2canvas(element, {
        scale: 2,
        backgroundColor: '#ffffff',
        logging: false
    }).then(canvas => {
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = `PAH-Booking-${bookingId}.png`;
        link.click();
    });
}

/**
 * Shows individual error messages with field highlighting
 */
function showFormError(message, fieldName) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error error-animation';
    errorDiv.innerHTML = `<p>⚠ ${message}</p>`;
    
    const form = document.getElementById('appointmentForm');
    form.insertBefore(errorDiv, form.firstChild);
    
    // Add error styling to the field if fieldName is provided
    if (fieldName) {
        const field = document.getElementById(fieldName);
        if (field && field.parentElement.classList.contains('form-group')) {
            field.parentElement.classList.add('error');
        }
    }
}

/**
 * Clears all error messages and removes error styling
 */
function clearFormErrors() {
    const errors = document.querySelectorAll('.form-error');
    errors.forEach(error => error.remove());
    
    // Remove error styling from form groups
    const formGroups = document.querySelectorAll('.form-group.error');
    formGroups.forEach(group => group.classList.remove('error'));
}

/**
 * Displays error animation on form
 */
function displayErrorAnimation() {
    const form = document.getElementById('appointmentForm');
    form.classList.add('error-animation');
    setTimeout(() => {
        form.classList.remove('error-animation');
    }, 500);
}

/**
 * Formats date for display
 */
function formatDate(dateString) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString + 'T00:00:00').toLocaleDateString('en-US', options);
}

const style = document.createElement('style');
style.textContent = `
    /* ====== TICKET MODAL STYLES ====== */
    
    .ticket-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
        overflow-y: auto;
        padding: 2rem 0;
    }

    .ticket-modal-overlay.show {
        display: flex;
        opacity: 1;
        justify-content: center;
        align-items: flex-start;
    }

    .ticket-modal {
        animation: slideUp 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        margin-top: 2rem;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ticket-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 700px;
        width: 90vw;
        position: relative;
        overflow: hidden;
    }

    .ticket-close-btn {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        background: #ff6b6b;
        border: none;
        color: white;
        font-size: 1.5rem;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .ticket-close-btn:hover {
        background: #ee5a52;
        transform: scale(1.1);
    }

    /* Header with Logo */
    .ticket-header {
        background: linear-gradient(135deg, #55a39a 0%, #3d8077 100%);
        color: white;
        padding: 3rem 2rem 2rem;
        text-align: center;
    }

    .ticket-logo {
        width: 80px;
        height: 80px;
        margin-bottom: 1rem;
        filter: brightness(0) invert(1);
        object-fit: contain;
    }

    .ticket-header h2 {
        font-size: 2rem;
        margin: 0 0 0.5rem 0;
        font-weight: 700;
    }

    .clinic-name {
        font-size: 0.95rem;
        opacity: 0.95;
        margin: 0;
    }

    /* Booking ID Badge */
    .booking-id-badge {
        background: linear-gradient(135deg, #fff9e6 0%, #ffe6e6 100%);
        padding: 1rem;
        text-align: center;
        border-top: 2px solid #55a39a;
        border-bottom: 2px solid #55a39a;
    }

    .badge-label {
        display: block;
        color: #666;
        font-size: 0.85rem;
        margin-bottom: 0.3rem;
    }

    .badge-value {
        display: block;
        font-size: 1.4rem;
        font-weight: 700;
        color: #55a39a;
        font-family: 'Courier New', monospace;
        letter-spacing: 1px;
    }

    /* Main Content */
    .ticket-content {
        padding: 2rem;
    }

    .welcome-message {
        background: linear-gradient(135deg, #e8f8f5 0%, #f0fdf4 100%);
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        border-left: 4px solid #55a39a;
    }

    .welcome-message p {
        margin: 0.5rem 0;
        color: #2d5b55;
        line-height: 1.6;
    }

    /* Section Styles */
    .ticket-section {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.1rem;
        color: #55a39a;
        margin: 0 0 1rem 0;
        font-weight: 700;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 0.5rem;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-item.full-width {
        grid-column: 1 / -1;
    }

    .info-label {
        font-size: 0.85rem;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.3rem;
    }

    .info-value {
        font-size: 1rem;
        color: #333;
        font-weight: 600;
    }

    /* Contact Section */
    .clinic-contact-section .contact-details {
        background: #f9f9f9;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #55a39a;
    }

    .clinic-contact-section p {
        margin: 0.7rem 0;
        color: #333;
    }

    .contact-label {
        color: #55a39a;
        min-width: 100px;
    }

    /* Important Notes */
    .important-notes ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .important-notes li {
        padding: 0.7rem 0 0.7rem 2rem;
        color: #666;
        position: relative;
        line-height: 1.6;
    }

    .important-notes li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #55a39a;
        font-weight: bold;
    }

    /* Action Buttons */
    .ticket-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        padding: 0 2rem 1rem;
    }

    .ticket-actions a {
        grid-column: 1 / -1;
    }

    .ticket-btn {
        padding: 1rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .save-pdf-btn {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .save-pdf-btn:hover {
        background: linear-gradient(135deg, #5568d3, #653a8e);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .save-image-btn {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }

    .save-image-btn:hover {
        background: linear-gradient(135deg, #e077e8, #e4435a);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
    }

    .whatsapp-contact-btn {
        background: linear-gradient(135deg, #25d366, #1da851);
        color: white;
    }

    .whatsapp-contact-btn:hover {
        background: linear-gradient(135deg, #20ba5d, #188b43);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
    }

    /* Footer */
    .ticket-footer {
        background: #f5f5f5;
        padding: 1.5rem 2rem;
        border-top: 1px solid #e0e0e0;
        text-align: center;
    }

    .ticket-footer p {
        margin: 0.5rem 0;
        color: #666;
        font-size: 0.9rem;
    }

    .ticket-date {
        color: #999;
        font-size: 0.85rem !important;
    }

    /* Print Styles */
    @media print {
        .ticket-modal-overlay {
            background: white;
            position: static;
        }

        .ticket-modal {
            box-shadow: none;
            max-width: 100%;
            margin: 0;
        }

        .ticket-container {
            box-shadow: none;
            border-radius: 0;
        }

        .ticket-close-btn,
        .ticket-actions {
            display: none;
        }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .ticket-container {
            width: 95vw;
            margin: 0 auto;
        }

        .ticket-header {
            padding: 2rem 1.5rem 1.5rem;
        }

        .ticket-header h2 {
            font-size: 1.6rem;
        }

        .ticket-logo {
            width: 60px;
            height: 60px;
        }

        .ticket-content {
            padding: 1.5rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .ticket-actions {
            grid-template-columns: 1fr;
            padding: 0 1.5rem 1rem;
        }

        .ticket-actions a {
            grid-column: 1;
        }

        .ticket-footer {
            padding: 1rem 1.5rem;
        }

        .welcome-message {
            padding: 1rem;
        }

        .ticket-btn {
            padding: 0.8rem;
            font-size: 0.85rem;
        }
    }

    /* ====== FORM ERROR STYLES ====== */
    
    .form-error {
        background: #fee;
        border-left: 4px solid #f66;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 8px;
        color: #d00;
        font-weight: 500;
        animation: slideIn 0.3s ease-out;
    }

    .form-group.error input,
    .form-group.error select,
    .form-group.error textarea {
        border-color: #f66 !important;
        background: rgba(255, 230, 230, 0.5) !important;
    }

    .form-group.error input:focus,
    .form-group.error select:focus,
    .form-group.error textarea:focus {
        box-shadow: 0 0 0 3px rgba(255, 102, 102, 0.15) !important;
    }

    .error-animation {
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
