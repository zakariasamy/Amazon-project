// Login/Register Form Handler
import AuthManager from '../auth/auth-manager.js';

document.addEventListener('DOMContentLoaded', async () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const message = document.getElementById('message');
    const loading = document.getElementById('loading');

    // Check if already logged in
    const isAuth = await AuthManager.init();
    if (isAuth) {
        window.location.href = 'popup.html';
        return;
    }

    // Login Handler
    document.getElementById('loginBtn').addEventListener('click', async () => {
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        if (!email || !password) {
            showMessage('Please fill in all fields', 'error');
            return;
        }

        showLoading(true);
        const result = await AuthManager.login(email, password);
        showLoading(false);

        if (result.success) {
            showMessage('Login successful! Redirecting...', 'success');
            setTimeout(() => window.location.href = 'popup.html', 1000);
        } else {
            showMessage(result.error, 'error');
        }
    });

    // Register Handler
    document.getElementById('registerBtn').addEventListener('click', async () => {
        const name = document.getElementById('registerName').value.trim();
        const email = document.getElementById('registerEmail').value.trim();
        const password = document.getElementById('registerPassword').value;
        const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

        if (!name || !email || !password || !passwordConfirm) {
            showMessage('Please fill in all fields', 'error');
            return;
        }

        if (password.length < 8) {
            showMessage('Password must be at least 8 characters', 'error');
            return;
        }

        if (password !== passwordConfirm) {
            showMessage('Passwords do not match', 'error');
            return;
        }

        showLoading(true);
        const result = await AuthManager.register(name, email, password, passwordConfirm);
        showLoading(false);

        if (result.success) {
            showMessage('Registration successful! Redirecting...', 'success');
            setTimeout(() => window.location.href = 'popup.html', 1000);
        } else {
            showMessage(result.error, 'error');
        }
    });

    // Toggle Forms
    document.getElementById('showRegister').addEventListener('click', (e) => {
        e.preventDefault();
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
        clearMessage();
    });

    document.getElementById('showLogin').addEventListener('click', (e) => {
        e.preventDefault();
        registerForm.classList.add('hidden');
        loginForm.classList.remove('hidden');
        clearMessage();
    });

    // Forgot Password
    document.getElementById('forgotPassword').addEventListener('click', (e) => {
        e.preventDefault();
        showMessage('Password reset feature coming soon!', 'info');
    });

    // Enter key support
    document.getElementById('loginPassword').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            document.getElementById('loginBtn').click();
        }
    });

    document.getElementById('registerPasswordConfirm').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            document.getElementById('registerBtn').click();
        }
    });

    // Helper Functions
    function showMessage(text, type) {
        message.textContent = text;
        message.className = `message ${type}`;
        message.style.display = 'block';
    }

    function clearMessage() {
        message.textContent = '';
        message.style.display = 'none';
    }

    function showLoading(show) {
        loading.classList.toggle('hidden', !show);
    }
});
