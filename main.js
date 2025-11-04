/* === Counter Animation === */
function animateCounter(element, target, duration) {
    let start = 0;
    const increment = target / (duration / 16);
    const showPlus = target >= 1000;

    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target.toLocaleString() + (showPlus ? '+' : '');
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start).toLocaleString() + (showPlus ? '+' : '');
        }
    }, 16);
}

function startCounters() {
    document.querySelectorAll('.stat-number').forEach(counter => {
        const target = parseInt(counter.dataset.target);
        animateCounter(counter, target, 2000);
    });
}

const counterObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            startCounters();
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

const statsContainer = document.querySelector('.stats-container');
if (statsContainer) counterObserver.observe(statsContainer);

/* === Basic Image Slider === */
let slideIndex = 0;
const slidesContainer = document.querySelector('.slides');
const slideImages = document.querySelectorAll('.slides img');
const totalImages = slideImages.length;

function moveImageSlide(step) {
    slideIndex = (slideIndex + step + totalImages) % totalImages;
    slidesContainer.style.transform = `translateX(-${slideIndex * 100}%)`;
}

// Auto-slide every 3s
setInterval(() => moveImageSlide(1), 3000);

/* === Smooth Scroll === */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

/* === Header Shadow on Scroll === */
window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    if (!header) return;
    header.style.boxShadow = window.scrollY > 50
        ? '0 4px 20px rgba(0,0,0,0.1)'
        : '0 2px 10px rgba(0,0,0,0.05)';
});

/* === Scroll Reveal Animation === */
const scrollObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('.feature-card, .service-card, .testimonial-card, .product-card, .animate-on-scroll')
    .forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        scrollObserver.observe(el);
    });

/* === Accordion === */
function toggleAccordion(button) {
    const content = button.nextElementSibling;
    const serviceBox = button.closest('.service-box');
    if (!serviceBox) return;

    serviceBox.querySelectorAll('.accordion-btn').forEach((btn, index) => {
        const contentEl = serviceBox.querySelectorAll('.accordion-content')[index];
        if (btn !== button) {
            btn.classList.remove('active');
            contentEl.classList.remove('active');
        }
    });

    button.classList.toggle('active');
    content.classList.toggle('active');
}

/* === Contact Form Submit === */
document.getElementById('contactForm')?.addEventListener('submit', e => {
    const btn = document.querySelector('.submit-btn');
    if (btn) {
        btn.classList.add('loading');
        btn.disabled = true;
    }
});

/* === Carousel Slider (3 per view) === */
let currentIndex = 0;
const slidesToShow = 3;
const slideItems = document.querySelectorAll('.slide-item');
const totalSlideItems = slideItems.length;
const maxIndex = Math.ceil(totalSlideItems / slidesToShow) - 1;

function moveSlide(direction) {
    currentIndex = (currentIndex + direction + maxIndex + 1) % (maxIndex + 1);
    updateSlider();
}

function currentSlide(index) {
    currentIndex = index;
    updateSlider();
}

function updateSlider() {
    const track = document.querySelector('.slides-track');
    if (!track || !slideItems.length) return;
    const slideWidth = slideItems[0].offsetWidth + 30;
    track.style.transform = `translateX(-${currentIndex * slideWidth * slidesToShow}px)`;

    document.querySelectorAll('.dot').forEach((dot, i) => {
        dot.classList.toggle('active', i === currentIndex);
    });
}

setInterval(() => moveSlide(1), 5000);
window.addEventListener('resize', updateSlider);

/* === TESTIMONIAL SLIDER (FIXED) === */
document.addEventListener("DOMContentLoaded", () => {
    let testimonialIndex = 0;
    const testimonialSlides = document.querySelectorAll(".testimonial-slide");
    const track = document.querySelector(".testimonial-slides-track");
    const dots = document.querySelectorAll(".testimonial-dot");
    const totalTestimonials = testimonialSlides.length;

    // Set track width dynamically
    if (track) {
        track.style.width = `${totalTestimonials * 100}%`;
    }

    function updateTestimonialSlider() {
        if (!track || !testimonialSlides.length) return;
        const slideWidth = testimonialSlides[0].offsetWidth;
        track.style.transform = `translateX(-${testimonialIndex * slideWidth}px)`;

        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.toggle("active", index === testimonialIndex);
        });
    }

    // Move slides
    window.moveTestimonial = (direction) => {
        testimonialIndex += direction;
        if (testimonialIndex >= totalTestimonials) testimonialIndex = 0;
        if (testimonialIndex < 0) testimonialIndex = totalTestimonials - 1;
        updateTestimonialSlider();
    };

    window.currentTestimonial = (index) => {
        testimonialIndex = index;
        updateTestimonialSlider();
    };

    // Resize listener for responsiveness
    window.addEventListener("resize", updateTestimonialSlider);

    // Initialize
    updateTestimonialSlider();

    // Optional: Auto-slide every 6 seconds
    setInterval(() => {
        testimonialIndex = (testimonialIndex + 1) % totalTestimonials;
        updateTestimonialSlider();
    }, 6000);
});
