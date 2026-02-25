// ============ MAIN INTERACTIONS & UTILITIES ============

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initSmoothScroll();
    initScrollAnimations();
    initScrollToTop();
});

// ============ MOBILE MENU TOGGLE ============

function initMobileMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            menuToggle.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
        });
        
        // Close menu when a link is clicked
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                menuToggle.textContent = '☰';
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar')) {
                navLinks.classList.remove('active');
                menuToggle.textContent = '☰';
            }
        });
    }
}

// ============ SMOOTH SCROLL ENHANCEMENT ============

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offsetTop = target.offsetTop - 80; // Account for sticky navbar
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

// ============ SCROLL ANIMATIONS ============

function initScrollAnimations() {
    // Use Intersection Observer for performance
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    });
    
    // Observe service cards, testimonial cards, etc
    document.querySelectorAll(
        '.service-card, .testimonial-card, .gallery-item, .contact-card, .stat-card, .about-stat'
    ).forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(element);
    });
}

// ============ SCROLL TO TOP BUTTON ============

function initScrollToTop() {
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    
    // Create the scroll to top button if it doesn't exist
    if (!scrollTopBtn) {
        const btn = document.createElement('button');
        btn.id = 'scrollTopBtn';
        btn.innerHTML = '↑';
        btn.className = 'scroll-top-btn';
        document.body.appendChild(btn);
        
        const styles = document.createElement('style');
        styles.textContent = `
            .scroll-top-btn {
                position: fixed;
                bottom: 30px;
                right: 30px;
                background: linear-gradient(135deg, #55a39a, #7ab5ad);
                color: white;
                border: none;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                cursor: pointer;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                z-index: 999;
                box-shadow: 0 4px 12px rgba(85, 163, 154, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .scroll-top-btn:hover {
                background: linear-gradient(135deg, #3d8077, #55a39a);
                transform: translateY(-5px);
                box-shadow: 0 6px 20px rgba(85, 163, 154, 0.6);
            }

            .scroll-top-btn.visible {
                opacity: 1;
                visibility: visible;
            }

            @media (max-width: 480px) {
                .scroll-top-btn {
                    bottom: 20px;
                    right: 20px;
                    width: 45px;
                    height: 45px;
                    font-size: 1.2rem;
                }
            }
        `;
        document.head.appendChild(styles);
    }
    
    const scrollBtn = document.getElementById('scrollTopBtn');
    
    // Show/hide scroll to top button
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });
    
    // Scroll to top on button click
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ============ NAVBAR BACKGROUND ON SCROLL ============

window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.pageYOffset > 50) {
        navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
    } else {
        navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.05)';
    }
});

// ============ ACTIVE NAVIGATION INDICATOR ============

function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id], header[id]');
    const navLinks = document.querySelectorAll('.nav-links a');
    
    window.addEventListener('scroll', () => {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active-nav');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active-nav');
            }
        });
    });
}

// Add styles for active nav link
const navStyles = document.createElement('style');
navStyles.textContent = `
    .nav-links a.active-nav {
        color: var(--primary-teal);
        font-weight: 700;
        border-bottom: 2px solid var(--primary-teal);
        padding-bottom: 0.5rem;
    }
`;
document.head.appendChild(navStyles);

updateActiveNavLink();

// ============ KEYBOARD SHORTCUTS ============

document.addEventListener('keydown', (e) => {
    // Press 'B' to jump to booking section
    if (e.key === 'b' || e.key === 'B') {
        const bookingSection = document.getElementById('appointment');
        if (bookingSection) {
            bookingSection.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    // Press 'S' to jump to services
    if (e.key === 's' || e.key === 'S') {
        const servicesSection = document.getElementById('services');
        if (servicesSection) {
            servicesSection.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    // Press 'Home' to scroll to top
    if (e.key === 'Home') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

// ============ FORM INPUT VALIDATION ============

function setupFormValidation() {
    const form = document.getElementById('appointmentForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', () => {
            validateInput(input);
        });
        
        input.addEventListener('input', () => {
            if (input.classList.contains('error')) {
                validateInput(input);
            }
        });
    });
}

function validateInput(input) {
    let isValid = true;
    
    if (input.type === 'email') {
        isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
    } else if (input.type === 'tel') {
        isValid = input.value.length >= 9;
    } else if (input.type === 'text' || input.tagName === 'TEXTAREA') {
        isValid = input.value.trim().length > 0;
    } else if (input.tagName === 'SELECT') {
        isValid = input.value !== '';
    }
    
    if (!isValid) {
        input.classList.add('error');
    } else {
        input.classList.remove('error');
    }
    
    return isValid;
}

setupFormValidation();

// Add error styling
const errorStyles = document.createElement('style');
errorStyles.textContent = `
    input.error,
    select.error,
    textarea.error {
        border-color: #f66 !important;
        background: #fee;
    }

    input.error::placeholder {
        color: #f66;
    }
`;
document.head.appendChild(errorStyles);

// ============ LOADING STATE FOR SERVICES ============

function showLoadingState() {
    const servicesGrid = document.getElementById('services-grid');
    if (servicesGrid) {
        servicesGrid.innerHTML = '<p class="loading">Loading services...</p>';
    }
}

// ============ PRINT FRIENDLY STYLES ============

const printStyles = document.createElement('style');
printStyles.textContent = `
    @media print {
        .navbar,
        .scroll-top-btn,
        .menu-toggle,
        .appointment-section,
        .contact-section {
            display: none;
        }

        body {
            font-size: 12pt;
            line-height: 1.5;
        }

        .service-card {
            page-break-inside: avoid;
        }
    }
`;
document.head.appendChild(printStyles);

// ============ PERFORMANCE MONITORING ============

if (window.performance) {
    window.addEventListener('load', () => {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log('Page load time: ' + pageLoadTime + 'ms');
    });
}

// ============ ANALYTICS TRACKING ============

function trackEvent(eventName, eventDetails) {
    if (window.gtag) {
        gtag('event', eventName, eventDetails);
    }
    // Also log to console for debugging
    console.log('Event tracked:', eventName, eventDetails);
}

// Track section views
const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            trackEvent('section_view', {
                section_name: entry.target.id
            });
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('section[id]').forEach(section => {
    sectionObserver.observe(section);
});

// Track button clicks
document.querySelectorAll('button, a.btn-primary, a.btn-secondary').forEach(element => {
    element.addEventListener('click', () => {
        trackEvent('button_click', {
            button_text: element.textContent,
            button_id: element.id,
            button_class: element.className
        });
    });
});

console.log('✓ Main.js initialized successfully');
// ============ KEYBOARD SHORTCUTS ============

document.addEventListener('keydown', (e) => {
    // B key - Go to booking
    if (e.key === 'b' || e.key === 'B') {
        window.location.href = 'appointment.html';
    }
    // S key - Go to services
    if (e.key === 's' || e.key === 'S') {
        window.location.href = 'services.html';
    }
    // C key - Go to clinic
    if (e.key === 'c' || e.key === 'C') {
        window.location.href = 'clinic.html';
    }
    // H key - Go to home
    if (e.key === 'h' || e.key === 'H') {
        window.location.href = 'index.html';
    }
});

// ============ PERFORMANCE OPTIMIZATION ============

// Preload critical resources
function preloadResources() {
    const link = document.createElement('link');
    link.rel = 'dns-prefetch';
    link.href = '//fonts.googleapis.com';
    document.head.appendChild(link);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', preloadResources);
} else {
    preloadResources();
}

// ============ PRINT STYLES ============

window.addEventListener('beforeprint', () => {
    document.body.style.background = 'white';
    document.querySelectorAll('nav, .scroll-top-btn').forEach(el => {
        el.style.display = 'none';
    });
});

window.addEventListener('afterprint', () => {
    document.body.style.background = '';
    document.querySelectorAll('nav, .scroll-top-btn').forEach(el => {
        el.style.display = '';
    });
});