// ============ APPOINTMENT BOOKING SYSTEM ============

document.addEventListener('DOMContentLoaded', () => {
    const appointmentForm = document.getElementById('appointmentForm');
    
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', handleAppointmentSubmit);
        
        // Set minimum date to today
        const dateInput = document.getElementById('appointmentDate');
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        // Optional: Disable past dates and weekends
        dateInput.addEventListener('change', validateDate);
    }
});

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
 * Saves appointment to localStorage
 */
function saveAppointment(appointment) {
    let appointments = JSON.parse(localStorage.getItem('appointments')) || [];
    appointments.push(appointment);
    localStorage.setItem('appointments', JSON.stringify(appointments));
    
    // Log to console for testing
    console.log('Appointment saved:', appointment);
}

/**
 * Generates a unique booking ID
 */
function generateBookingId() {
    return 'PAH-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
}

/**
 * Shows success message with booking details
 */
function showSuccessMessage(bookingData) {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message success-animation';
    successDiv.innerHTML = `
        <h3>✓ Appointment Confirmed!</h3>
        <p><strong>Booking ID:</strong> ${bookingData.bookingId}</p>
        <p>Dear <strong>${bookingData.ownerName}</strong>,</p>
        <p>Your appointment has been successfully booked!</p>
        <div class="booking-details">
            <p><strong>Pet:</strong> ${bookingData.petName} (${bookingData.petType})</p>
            <p><strong>Service:</strong> ${bookingData.appointmentType}</p>
            <p><strong>Date:</strong> ${formatDate(bookingData.appointmentDate)}</p>
            <p><strong>Time:</strong> ${bookingData.appointmentTime}</p>
        </div>
        <p>We will confirm your appointment via phone/email within 2 hours.</p>
        <p><strong>Contact:</strong> +254 (0) 701 234 567</p>
        <button onclick="this.parentElement.remove()" class="close-success">Close</button>
    `;
    
    const formContainer = document.querySelector('.appointment-form');
    formContainer.parentElement.insertBefore(successDiv, formContainer);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (successDiv.parentElement) {
            successDiv.remove();
        }
    }, 10000);
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

    .success-message {
        background: linear-gradient(135deg, #e8f8f5, #f0fdf4);
        border: 2px solid #55a39a;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        color: #2d5b55;
        animation: slideIn 0.4s ease-out;
    }

    .success-message h3 {
        color: #55a39a;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .success-message p {
        margin: 0.5rem 0;
        color: #4a4a4a;
    }

    .booking-details {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        text-align: left;
    }

    .booking-details p {
        margin: 0.5rem 0;
        color: #333;
    }

    .close-success {
        background: #55a39a;
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .close-success:hover {
        background: #3d8077;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .success-message {
            padding: 1.5rem;
        }

        .booking-details {
            padding: 1rem;
        }
    }
`;
document.head.appendChild(style);
