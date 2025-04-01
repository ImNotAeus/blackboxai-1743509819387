// Dark mode functionality
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
}

// Initialize dark mode based on preference
function initDarkMode() {
    // Check localStorage first, then system preference
    const storedPref = localStorage.getItem('darkMode');
    if (storedPref !== null) {
        if (storedPref === 'true') document.documentElement.classList.add('dark');
    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', true);
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initDarkMode);