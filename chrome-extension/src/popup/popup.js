// Main Popup Dashboard
import AuthManager from '../auth/auth-manager.js';

document.addEventListener('DOMContentLoaded', async () => {
    // Check if user is authenticated
    const isAuth = await AuthManager.init();

    if (!isAuth) {
        // Redirect to login
        window.location.href = 'login.html';
        return;
    }

    // Load user data
    const user = await AuthManager.getCurrentUser();

    if (user) {
        document.getElementById('userName').textContent = user.name;
        document.getElementById('userEmail').textContent = user.email;
        document.getElementById('userTier').textContent = user.subscription_tier || 'Free';
    }

    // Analyze button
    document.getElementById('analyzeBtn').addEventListener('click', async () => {
        // TODO: Send message to content script to analyze current page
        console.log('Analyze clicked');
        alert('Analysis feature coming soon!');
    });

    // Settings button
    document.getElementById('settingsBtn').addEventListener('click', () => {
        // TODO: Open settings page
        console.log('Settings clicked');
        alert('Settings page coming soon!');
    });

    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', async () => {
        if (confirm('Are you sure you want to logout?')) {
            await AuthManager.logout();
            window.location.href = 'login.html';
        }
    });
});
