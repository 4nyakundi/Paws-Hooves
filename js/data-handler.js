document.addEventListener('DOMContentLoaded', () => {
    const servicesGrid = document.getElementById('services-grid');

    // Fetch the data from our JSON file
    fetch('data/services.json')
        .then(response => response.json())
        .then(data => {
            displayServices(data.services);
        })
        .catch(error => {
            console.error('Error loading services:', error);
            servicesGrid.innerHTML = '<p>Check back soon for our service list!</p>';
        });

    function displayServices(services) {
        servicesGrid.innerHTML = services.map((service, index) => `
            <div class="service-card" style="animation-delay: ${index * 0.1}s;">
                <div class="service-icon">${service.icon}</div>
                <h3>${service.title}</h3>
                <p class="category">${service.category}</p>
                <p class="service-description">${service.description}</p>
                <div class="service-meta">
                    <span class="duration">⏱️ ${service.duration}</span>
                </div>
                <p class="price"><strong>${service.price}</strong></p>
                <a href="#appointment" class="btn-outline" onclick="setServiceType('${service.title}')">Book This</a>
            </div>
        `).join('');
    }
});

/**
 * Sets the selected service type when booking from service card
 */
window.setServiceType = function(serviceName) {
    const typeMap = {
        'Pet Wellness Exam': 'wellness',
        'Livestock Consultation': 'consultation',
        'Complete Vaccinations': 'vaccination',
        'Surgical Services': 'surgery',
        'Dental Care': 'wellness',
        'Microchipping & ID': 'wellness',
        'Farm Health Program': 'consultation',
        'Dermatology Services': 'wellness',
        'Laboratory & Diagnostics': 'consultation',
        'Emergency Care': 'emergency',
        'Pet Grooming': 'wellness',
        'Breeding Consultation': 'consultation'
    };
    
    const appointmentType = document.getElementById('appointmentType');
    if (appointmentType && typeMap[serviceName]) {
        appointmentType.value = typeMap[serviceName];
        appointmentType.dispatchEvent(new Event('change'));
    }
};

console.log('✓ Data handler loaded successfully');
