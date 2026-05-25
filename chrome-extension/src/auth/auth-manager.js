// Authentication Manager for Chrome Extension
class AuthManager {
  constructor() {
    this.baseUrl = 'http://localhost:8000'; // Change to your API URL in production
    this.token = null;
    this.user = null;
  }

  /**
   * Initialize auth from storage
   */
  async init() {
    const data = await chrome.storage.local.get(['authToken', 'user']);
    this.token = data.authToken || null;
    this.user = data.user || null;
    
    return this.isAuthenticated();
  }

  /**
   * Register new user
   */
  async register(name, email, password, passwordConfirmation) {
    try {
      const response = await fetch(`${this.baseUrl}/api/auth/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          name,
          email,
          password,
          password_confirmation: passwordConfirmation
        })
      });

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Registration failed');
      }

      const data = await response.json();
      await this.saveAuth(data.token, data.user);
      
      return { success: true, user: data.user };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Login user
   */
  async login(email, password) {
    try {
      const response = await fetch(`${this.baseUrl}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password })
      });

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Login failed');
      }

      const data = await response.json();
      await this.saveAuth(data.token, data.user);
      
      return { success: true, user: data.user };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Logout user
   */
  async logout() {
    if (this.token) {
      try {
        await fetch(`${this.baseUrl}/api/auth/logout`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Accept': 'application/json',
          }
        });
      } catch (error) {
        console.error('Logout error:', error);
      }
    }

    await this.clearAuth();
  }

  /**
   * Get current user
   */
  async getCurrentUser() {
    if (!this.token) return null;

    try {
      const response = await fetch(`${this.baseUrl}/api/auth/me`, {
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Accept': 'application/json',
        }
      });

      if (!response.ok) {
        await this.clearAuth();
        return null;
      }

      const data = await response.json();
      this.user = data.user;
      await chrome.storage.local.set({ user: this.user });
      
      return this.user;
    } catch (error) {
      console.error('Get user error:', error);
      return null;
    }
  }

  /**
   * Refresh token
   */
  async refreshToken() {
    if (!this.token) return false;

    try {
      const response = await fetch(`${this.baseUrl}/api/auth/refresh`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Accept': 'application/json',
        }
      });

      if (!response.ok) {
        await this.clearAuth();
        return false;
      }

      const data = await response.json();
      await this.saveAuth(data.token, this.user);
      
      return true;
    } catch (error) {
      console.error('Token refresh error:', error);
      return false;
    }
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return this.token !== null && this.user !== null;
  }

  /**
   * Get auth token for API requests
   */
  getToken() {
    return this.token;
  }

  /**
   * Save authentication data
   */
  async saveAuth(token, user) {
    this.token = token;
    this.user = user;
    
    await chrome.storage.local.set({
      authToken: token,
      user: user
    });
  }

  /**
   * Clear authentication data
   */
  async clearAuth() {
    this.token = null;
    this.user = null;
    
    await chrome.storage.local.remove(['authToken', 'user']);
  }

  /**
   * Make authenticated API request
   */
  async request(method, endpoint, body = null) {
    if (!this.token) {
      throw new Error('Not authenticated');
    }

    const options = {
      method,
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      }
    };

    if (body) {
      options.body = JSON.stringify(body);
    }

    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, options);

      // Handle 401 - try to refresh token
      if (response.status === 401) {
        const refreshed = await this.refreshToken();
        if (refreshed) {
          // Retry request with new token
          options.headers['Authorization'] = `Bearer ${this.token}`;
          return await fetch(`${this.baseUrl}${endpoint}`, options);
        } else {
          throw new Error('Session expired. Please login again.');
        }
      }

      return response;
    } catch (error) {
      console.error('API request error:', error);
      throw error;
    }
  }
}

// Export singleton instance
const authManager = new AuthManager();
export default authManager;
