// KM Services - JavaScript Principal

// Menu mobile
document.addEventListener('DOMContentLoaded', function() {
    var navToggle = document.getElementById('navToggle');
    var navMenu = document.querySelector('.navbar-menu');
    
    console.log('DOM Loaded. navToggle:', navToggle, 'navMenu:', navMenu);

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            
            var isActive = navMenu.classList.contains('active');
            console.log('Menu mobile toggled. Active:', isActive);
            
            if (isActive) {
                navMenu.style.display = 'flex';
            } else {
                navMenu.style.display = 'none';
            }
        });

        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
                navMenu.style.display = 'none';
            }
        });
    } else {
        console.error('Nav elements not found!');
    }
});

// Validation de formulaires
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateForm(formElement) {
    let isValid = true;
    
    formElement.querySelectorAll('[required]').forEach(field => {
        if (field.type === 'email') {
            if (!validateEmail(field.value)) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        } else if (field.value.trim() === '') {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Gestion du panier
function addToCart(productId) {
    console.log('Ajout du produit ' + productId + ' au panier');
}

function removeFromCart(itemId) {
    console.log('Suppression de l\'article ' + itemId + ' du panier');
}

// Lightbox pour galeries
function openLightbox(imageSrc) {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = '<img src="' + imageSrc + '" alt="Image"><span onclick="closeLightbox()" class="close">&times;</span>';
    document.body.appendChild(lightbox);
    lightbox.style.display = 'block';
}

function closeLightbox() {
    const lightbox = document.querySelector('.lightbox');
    if (lightbox) {
        lightbox.remove();
    }
}

// Scroll smooth
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// Animations au scroll (lazy loading)
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.service-card, .project-card, .product-card').forEach(el => {
    observer.observe(el);
});

console.log('KM Services - Script loaded successfully');
