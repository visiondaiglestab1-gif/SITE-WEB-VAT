// ========== PRELOADER ==========
window.addEventListener('load', () => {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        setTimeout(() => {
            preloader.classList.add('fade-out');
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        }, 1000);
    }
});

// ========== HEADER SCROLL ==========
const header = document.querySelector('.header');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// ========== MOBILE MENU ==========
const menuToggle = document.getElementById('menuToggle');
const navMenu = document.getElementById('navMenu');

if (menuToggle) {
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
}

// Fermer le menu en cliquant sur un lien
document.querySelectorAll('nav ul li a').forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
    });
});

// ========== HORLOGE ET DATE ==========
function updateDateTime() {
    const now = new Date();
    
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    let dateStr = now.toLocaleDateString('fr-FR', options);
    dateStr = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
    
    const timeStr = now.toLocaleTimeString('fr-FR', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
    
    const dateElement = document.getElementById('currentDate');
    const timeElement = document.getElementById('currentTime');
    
    if (dateElement) dateElement.textContent = dateStr;
    if (timeElement) timeElement.textContent = timeStr;
}

updateDateTime();
setInterval(updateDateTime, 1000);

// ========== SCROLL INDICATOR ==========
const scrollIndicator = document.querySelector('.scroll-indicator');
if (scrollIndicator) {
    scrollIndicator.addEventListener('click', () => {
        window.scrollTo({
            top: window.innerHeight,
            behavior: 'smooth'
        });
    });
}

// ========== HERO SLIDER ==========
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');

function nextSlide() {
    if (slides.length > 0) {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }
}

if (slides.length > 0) {
    setInterval(nextSlide, 5000);
}

// ========== STATISTIQUES EN TEMPS RÉEL ==========
async function loadStats() {
    try {
        const response = await fetch('php/get-stats.php');
        const data = await response.json();
        
        if (data.success) {
            // Mettre à jour les compteurs
            document.getElementById('visitorsCount').innerText = data.visitors || 0;
            document.getElementById('subscribersCount').innerText = data.subscribers || 0;
            document.getElementById('sermonsCount').innerText = data.total_sermons || 0;
            document.getElementById('megaCount').innerText = data.sermons_mega || 0;
            document.getElementById('degooCount').innerText = data.sermons_degoo || 0;
            
            // Gérer les badges de notification
            const totalNotifs = (data.sermon_notifications || 0) + (data.announcement_notifications || 0);
            const notificationCount = document.getElementById('notificationCount');
            const sermonBadge = document.getElementById('sermonNotificationBadge');
            const megaNotif = document.getElementById('megaNotification');
            const degooNotif = document.getElementById('degooNotification');
            
            if (totalNotifs > 0) {
                notificationCount.style.display = 'block';
                notificationCount.innerText = totalNotifs;
                if (sermonBadge) sermonBadge.style.display = 'inline-block';
                if (megaNotif) megaNotif.style.display = 'block';
                if (degooNotif) degooNotif.style.display = 'block';
            } else {
                notificationCount.style.display = 'none';
                if (sermonBadge) sermonBadge.style.display = 'none';
                if (megaNotif) megaNotif.style.display = 'none';
                if (degooNotif) degooNotif.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Erreur chargement stats:', error);
    }
}

// ========== CHARGEMENT DES ANNONCES ==========
async function loadAnnouncements() {
    try {
        const response = await fetch('php/check-notifications.php');
        const data = await response.json();
        
        const container = document.getElementById('announcementsList');
        if (container) {
            if (data.announcements && data.announcements.length > 0) {
                container.innerHTML = data.announcements.map(ann => `
                    <div class="announcement-item" data-aos="fade-up">
                        <div class="announcement-title">📢 ${escapeHtml(ann.title)}</div>
                        <div>${escapeHtml(ann.content)}</div>
                        <div class="announcement-date">
                            <i class="far fa-calendar-alt"></i> ${new Date(ann.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p style="text-align:center;">Aucune annonce pour le moment.</p>';
            }
        }
    } catch (error) {
        console.error('Erreur chargement annonces:', error);
    }
}

// ========== NOTIFICATIONS ==========
const notificationIcon = document.getElementById('notificationIcon');
const notificationDropdown = document.getElementById('notificationDropdown');

if (notificationIcon) {
    notificationIcon.addEventListener('click', async (e) => {
        e.stopPropagation();
        notificationDropdown.classList.toggle('show');
        await loadNotifications();
        await markNotificationsRead();
    });
}

async function loadNotifications() {
    try {
        const response = await fetch('php/check-notifications.php');
        const data = await response.json();
        
        const list = document.getElementById('notificationList');
        if (list) {
            if (data.notifications && data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(notif => `
                    <div class="notification-item unread" data-id="${notif.id}">
                        <div style="font-weight: bold;">${notif.type === 'sermon' ? '🎵 Nouveau sermon' : '📢 Nouvelle annonce'}</div>
                        <div style="font-size: 0.9rem;">${escapeHtml(notif.message)}</div>
                        <div style="font-size: 0.7rem; color: #999;">${new Date(notif.created_at).toLocaleString('fr-FR')}</div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<div style="padding: 15px; text-align: center;">Aucune notification</div>';
            }
        }
    } catch (error) {
        console.error('Erreur chargement notifications:', error);
    }
}

async function markNotificationsRead() {
    try {
        await fetch('php/mark-notification-read.php', { method: 'POST' });
        document.getElementById('notificationCount').style.display = 'none';
        const sermonBadge = document.getElementById('sermonNotificationBadge');
        const megaNotif = document.getElementById('megaNotification');
        const degooNotif = document.getElementById('degooNotification');
        if (sermonBadge) sermonBadge.style.display = 'none';
        if (megaNotif) megaNotif.style.display = 'none';
        if (degooNotif) degooNotif.style.display = 'none';
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Fermer le dropdown en cliquant ailleurs
document.addEventListener('click', (e) => {
    if (notificationIcon && !notificationIcon.contains(e.target)) {
        if (notificationDropdown) notificationDropdown.classList.remove('show');
    }
});

// ========== MODAL AUTHENTIFICATION ==========
const modal = document.getElementById('authModal');
let pendingLibrary = null;

window.showAuthModal = function(library) {
    pendingLibrary = library;
    if (modal) modal.classList.add('show');
};

function closeModal() {
    if (modal) modal.classList.remove('show');
    pendingLibrary = null;
}

// Fermeture modal
document.querySelector('.modal-close')?.addEventListener('click', closeModal);
window.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

// Tabs modal
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(`${tab}Tab`).classList.add('active');
    });
});

// Formulaire de connexion
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const messageDiv = document.getElementById('loginMessage');
        
        try {
            const response = await fetch('php/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password, action: 'login' })
            });
            const result = await response.json();
            
            messageDiv.className = 'form-message';
            if (result.success) {
                messageDiv.classList.add('success');
                messageDiv.textContent = 'Connexion réussie ! Redirection...';
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                messageDiv.classList.add('error');
                messageDiv.textContent = result.message;
            }
        } catch (error) {
            messageDiv.classList.add('error');
            messageDiv.textContent = 'Erreur de connexion';
        }
    });
}

// Formulaire d'inscription
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('first_name', document.getElementById('regFirstname').value);
        formData.append('last_name', document.getElementById('regLastname').value);
        formData.append('email', document.getElementById('regEmail').value);
        formData.append('phone', document.getElementById('regPhone').value);
        formData.append('password', document.getElementById('regPassword').value);
        formData.append('newsletter', document.getElementById('regNewsletter').checked ? 'on' : '');
        
        const messageDiv = document.getElementById('registerMessage');
        
        try {
            const response = await fetch('php/subscribe.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            messageDiv.className = 'form-message';
            if (result.success) {
                messageDiv.classList.add('success');
                messageDiv.textContent = result.message;
                registerForm.reset();
                setTimeout(() => {
                    document.querySelector('.tab-btn[data-tab="login"]').click();
                }, 2000);
            } else {
                messageDiv.classList.add('error');
                messageDiv.textContent = result.message;
            }
        } catch (error) {
            messageDiv.classList.add('error');
            messageDiv.textContent = 'Erreur de connexion';
        }
    });
}

// ========== BOUTON ANNONCE ==========
const announcementBtn = document.getElementById('announcementBtn');
if (announcementBtn) {
    announcementBtn.addEventListener('click', () => {
        const annoncesSection = document.getElementById('annonces');
        if (annoncesSection) {
            annoncesSection.scrollIntoView({ behavior: 'smooth' });
            loadAnnouncements();
        }
    });
}

// ========== ANIMATIONS AU SCROLL ==========
const animateOnScroll = () => {
    const elements = document.querySelectorAll('[data-aos]');
    const windowHeight = window.innerHeight;
    
    elements.forEach(el => {
        const rect = el.getBoundingClientRect();
        if (rect.top < windowHeight - 100) {
            el.classList.add('aos-animate');
        }
    });
};

window.addEventListener('scroll', animateOnScroll);
animateOnScroll();

// ========== INITIALISATION ==========
loadStats();
loadAnnouncements();

// Rafraîchir toutes les 30 secondes
setInterval(() => {
    loadStats();
    loadAnnouncements();
}, 30000);

// ========== UTILITAIRES ==========
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========== SMOOTH SCROLL POUR LES LIENS ANCRES ==========
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== "#" && href !== "#") {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const headerOffset = 130;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });
});