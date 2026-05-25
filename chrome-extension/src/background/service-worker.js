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
        // Return authentication status
        chrome.storage.local.get(['authToken', 'user'], (data) => {
            const response = {
                authenticated: !!(data.authToken && data.user),
                user: data.user
            };
            sendResponse(response);
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
