// ========== THEME MANAGEMENT ==========
const themeToggle = document.getElementById('theme-toggle');
const body = document.body;

// Vérifier le thème sauvegardé dans localStorage
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark') {
    body.classList.add('dark-mode');
}

// Fonction pour basculer le thème
function toggleTheme() {
    body.classList.toggle('dark-mode');
    
    // Sauvegarder la préférence
    if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
    } else {
        localStorage.setItem('theme', 'light');
    }
}

// Écouter le clic sur le bouton
if (themeToggle) {
    themeToggle.addEventListener('click', toggleTheme);
}

// Détecter la préférence système (optionnel)
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
if (!localStorage.getItem('theme') && prefersDark.matches) {
    body.classList.add('dark-mode');
    localStorage.setItem('theme', 'dark');
}