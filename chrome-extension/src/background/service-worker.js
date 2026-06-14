// Background Service Worker
console.log('Amazon Product Analyzer - Background Service Worker Active');

// Listen for extension installation
chrome.runtime.onInstalled.addListener((details) => {
    if (details.reason === 'install') {
        console.log('Extension installed successfully');
        // Open welcome page or login
        chrome.tabs.create({
            url: chrome.runtime.getURL('src/popup/login.html')
        });
    } else if (details.reason === 'update') {
        console.log('Extension updated to version:', chrome.runtime.getManifest().version);
    }
});

// Listen for messages from content scripts or popup
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    console.log('Message received in background:', request.action);

    if (request.action === 'analyze') {
        console.log('Handling analyze request...');
        // Handle product analysis request
        handleAnalysis(request.data)
            .then(result => {
                console.log('Analysis complete, sending response');
                sendResponse({ success: true, data: result });
            })
            .catch(error => {
                console.error('Analysis error:', error);
                sendResponse({ success: false, error: error.message });
            });
        return true; // Keep channel open for async response
    }

    if (request.action === 'getAuth') {
        console.log('Handling getAuth request...');
        
        const getCookie = (name, callback) => {
            chrome.cookies.get({ url: 'http://127.0.0.1/', name: name }, (c1) => {
                if (c1 && c1.value) {
                    callback(c1.value);
                } else {
                    chrome.cookies.get({ url: 'http://localhost/', name: name }, (c2) => {
                        if (c2 && c2.value) {
                            callback(c2.value);
                        } else {
                            callback(null);
                        }
                    });
                }
            });
        };

        getCookie('extension_auth_token', (token) => {
            if (token) {
                getCookie('extension_user_info', (userJson) => {
                    if (userJson) {
                        try {
                            const decodedToken = decodeURIComponent(token);
                            const user = JSON.parse(decodeURIComponent(userJson));
                            chrome.storage.local.set({ authToken: decodedToken, user: user }, () => {
                                console.log('Automatically authenticated from dashboard cookies:', user.email);
                                sendResponse({ authenticated: true, token: decodedToken, user: user });
                            });
                            return;
                        } catch (e) {
                            console.error('Failed to parse user cookie:', e);
                        }
                    }
                    chrome.storage.local.remove(['authToken', 'user'], () => {
                        sendResponse({ authenticated: false, token: null, user: null });
                    });
                });
            } else {
                chrome.storage.local.remove(['authToken', 'user'], () => {
                    sendResponse({ authenticated: false, token: null, user: null });
                });
            }
        });
        return true; // Keep channel open for async response
    }

    // Proxy fetch requests from content scripts (bypasses CORS)
    if (request.action === 'fetchUrl') {
        console.log('Fetching URL:', request.url?.substring(0, 80));

        // Add browser-like headers to avoid bot detection
        const headers = {
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.5',
            'Accept-Encoding': 'gzip, deflate, br',
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Upgrade-Insecure-Requests': '1',
            'sec-ch-ua': '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
            'sec-ch-ua-mobile': '?0',
            'sec-ch-ua-platform': '"Windows"'
        };

        fetch(request.url, { headers, ...request.options })
            .then(async response => {
                const text = await response.text();
                console.log(`Fetch response: ${response.status}, length: ${text.length}`);
                sendResponse({
                    success: true,
                    status: response.status,
                    text: text
                });
            })
            .catch(error => {
                console.error('Fetch error:', error);
                sendResponse({ success: false, error: error.message });
            });
        return true; // Keep channel open for async response
    }

    // Fetch Amazon suggestions API
    if (request.action === 'getAmazonSuggestions') {
        const url = `https://completion.amazon.com/api/2017/suggestions?mid=ATVPDKIKX0DER&alias=aps&prefix=${encodeURIComponent(request.prefix)}`;
        fetch(url)
            .then(async response => {
                const data = await response.json();
                const suggestions = data.suggestions?.map(s => s.value) || [];
                sendResponse({ success: true, suggestions });
            })
            .catch(error => {
                console.error('Suggestions error:', error);
                sendResponse({ success: false, suggestions: [] });
            });
        return true;
    }

    if (request.action === 'logout') {
        console.log('Handling logout request...');
        const domains = ['127.0.0.1', 'localhost'];
        
        domains.forEach(domain => {
            chrome.cookies.getAll({ domain: domain }, (cookies) => {
                if (cookies) {
                    cookies.forEach(cookie => {
                        const protocol = cookie.secure ? 'https://' : 'http://';
                        const cleanDomain = cookie.domain.startsWith('.') ? cookie.domain.substring(1) : cookie.domain;
                        const url = `${protocol}${cleanDomain}${cookie.path}`;
                        
                        chrome.cookies.remove({ url: url, name: cookie.name }, (details) => {
                            if (chrome.runtime.lastError) {
                                console.warn(`Failed to remove cookie ${cookie.name} on ${url}:`, chrome.runtime.lastError);
                            }
                        });
                    });
                }
            });
        });

        chrome.storage.local.remove(['authToken', 'user'], () => {
            sendResponse({ success: true });
        });
        return true;
    }

    console.log('Unknown action:', request.action);
});

// Handle product analysis
async function handleAnalysis(data) {
    // TODO: Implement product analysis logic
    console.log('Analyzing product:', data);
    return {
        success: true,
        message: 'Analysis complete',
        data: {}
    };
}

// Keep service worker alive
chrome.alarms.create('keepAlive', { periodInMinutes: 1 });
chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === 'keepAlive') {
        console.log('Service worker kept alive');
    }
});
