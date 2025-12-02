const ESP32_IP = '<?php echo $esp32_ip; ?>';
const STREAM_PORT = '<?php echo $stream_port; ?>';
const STREAM_URL = `http://${ESP32_IP}:${STREAM_PORT}/stream`;
const PROXY_URL = '?proxy_stream=1';

let streamActive = false;
let reconnectAttempts = 0;
let maxReconnectAttempts = 5;
let reconnectTimeout;

// DOM elements
const cameraStream = document.getElementById('cameraStream');
const cameraOverlay = document.getElementById('cameraOverlay');
const cameraStatus = document.getElementById('cameraStatus');
const cameraInfo = document.getElementById('cameraInfo');
const modalStream = document.getElementById('modalCameraStream');
const cameraModal = document.getElementById('cameraModal');

// Function to determine sensor status
function getSensorStatus(temperature, humidity, gasLevel) {
    if (gasLevel > 300) {
        return { class: 'status-warning', text: 'Warning' };
    }
    if (temperature > 35 || humidity > 80) {
        return { class: 'status-caution', text: 'Caution' };
    }
    return { class: 'status-safe', text: 'Normal' };
}

// Function to update individual sensor status
function updateSensorStatus(sensorType, value, temperature = null, humidity = null, gasLevel = null) {
    const card = document.querySelector(`.metric-card.${sensorType}`);
    if (!card) return;
    
    const badge = card.querySelector('.status-badge');
    if (!badge) return;
    
    let status;
    
    switch(sensorType) {
        case 'temperature':
            status = value > 35 ? 
                { class: 'status-caution', text: 'High' } : 
                value < 15 ? 
                { class: 'status-caution', text: 'Low' } : 
                { class: 'status-safe', text: 'Normal' };
            break;
            
        case 'humidity':
            status = value > 80 ? 
                { class: 'status-caution', text: 'High' } : 
                value < 30 ? 
                { class: 'status-caution', text: 'Low' } : 
                { class: 'status-safe', text: 'Normal' };
            break;
            
        case 'gas':
            status = value > 300 ? 
                { class: 'status-warning', text: 'Warning' } : 
                value > 200 ? 
                { class: 'status-caution', text: 'Elevated' } : 
                { class: 'status-safe', text: 'Safe' };
            break;
            
        case 'altitude':
            status = { class: 'status-safe', text: 'Active' };
            break;
            
        default:
            status = { class: 'status-safe', text: 'Normal' };
    }
    
    // Update badge
    badge.className = `status-badge ${status.class}`;
    badge.textContent = status.text;
}

// Navigation functionality
function switchSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from all nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(sectionId).classList.add('active');
    
    // Add active class to clicked nav link
    document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
    
    // Update content header
    updateContentHeader(sectionId);
}

function updateContentHeader(sectionId) {
    const titles = {
        'sensors': {
            title: 'Live Sensor Monitoring',
            subtitle: 'Real-time data from your agricultural monitoring system'
        },
        'camera': {
            title: 'Camera System',
            subtitle: 'Live video feed from ESP32 camera module'
        },
        'ai-insights': {
            title: 'AI Insights & Recommendations',
            subtitle: 'Intelligent analysis of your agricultural data'
        },
        'history': {
            title: 'Historical Data',
            subtitle: 'Past sensor readings and trend analysis'
        },
        'about': {
            title: 'About AgriSITE',
            subtitle: 'System information and technology overview'
        }
    };
    
    document.getElementById('contentTitle').textContent = titles[sectionId].title;
    document.getElementById('contentSubtitle').textContent = titles[sectionId].subtitle;
}

// Add click event listeners to navigation links
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const sectionId = this.getAttribute('data-section');
        switchSection(sectionId);
        
        // Close mobile menu if open
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.remove('mobile-open');
        }
    });
});

// Mobile menu toggle
function toggleMobileMenu() {
    document.getElementById('sidebar').classList.toggle('mobile-open');
}

// Redirect to analytics page when sensor card is clicked
function redirectToAnalytics(sensorType) {
    window.location.href = `analytics.php?sensor=${sensorType}`;
}

// Camera functions
function updateStatus(status, message) {
    cameraStatus.className = `camera-status ${status}`;
    let statusText = status === 'online' ? 'Connected' : 
                    status === 'offline' ? 'Offline' : 
                    'Connecting';
    cameraStatus.textContent = statusText;
    if (message) {
        cameraInfo.textContent = message;
    }
}

function initializeStream() {
    updateStatus('connecting', 'Connecting to camera...');
    
    // Try direct connection first, then proxy
    const streamSrc = STREAM_URL + '?t=' + Date.now();
    cameraStream.src = streamSrc;
    modalStream.src = streamSrc;
    
    cameraStream.onload = function() {
        streamActive = true;
        reconnectAttempts = 0;
        cameraOverlay.classList.add('hidden');
        cameraStream.style.display = 'block';
        updateStatus('online', `Connected to ${ESP32_IP}`);
    };
    
    cameraStream.onerror = function() {
        // Try proxy method
        const proxySrc = PROXY_URL + '&t=' + Date.now();
        cameraStream.src = proxySrc;
        modalStream.src = proxySrc;
        
        cameraStream.onload = function() {
            streamActive = true;
            reconnectAttempts = 0;
            cameraOverlay.classList.add('hidden');
            cameraStream.style.display = 'block';
            updateStatus('online', `Connected via proxy to ${ESP32_IP}`);
        };
        
        cameraStream.onerror = function() {
            handleConnectionError();
        };
    };
}

function handleConnectionError() {
    streamActive = false;
    cameraOverlay.classList.remove('hidden');
    cameraStream.style.display = 'none';
    
    if (reconnectAttempts < maxReconnectAttempts) {
        reconnectAttempts++;
        updateStatus('connecting', `Reconnecting... (${reconnectAttempts}/${maxReconnectAttempts})`);
        
        reconnectTimeout = setTimeout(() => {
            initializeStream();
        }, 3000 * reconnectAttempts);
    } else {
        updateStatus('offline', 'Connection failed. Check ESP32 camera.');
        cameraOverlay.querySelector('.camera-text').textContent = 'Camera Offline';
    }
}

function openFullscreen() {
    if (streamActive) {
        cameraModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    } else {
        initializeStream();
    }
}

function closeFullscreen() {
    cameraModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Auto-refresh functions
function refreshSensorData() {
    fetch('../data/raw_data.json')
        .then(response => response.json())
        .then(data => {
            // Update metric cards
            const tempCard = document.querySelector('.temperature .metric-value');
            const humidityCard = document.querySelector('.humidity .metric-value');
            const gasCard = document.querySelector('.gas .metric-value');
            const altitudeCard = document.querySelector('.altitude .metric-value');
            
            if (tempCard) tempCard.textContent = data.temperature + '°C';
            if (humidityCard) humidityCard.textContent = data.humidity + '%';
            if (gasCard) gasCard.textContent = data.gas_sensor;
            if (altitudeCard) altitudeCard.textContent = data.altitude + 'm';
            
            // Update status badges for each sensor
            updateSensorStatus('temperature', data.temperature);
            updateSensorStatus('humidity', data.humidity);
            updateSensorStatus('gas', data.gas_sensor);
            updateSensorStatus('altitude', data.altitude);
        })
        .catch(error => console.log('Error fetching sensor data:', error));
}

function refreshAIInsights() {
    fetch('../data/ai_data.json')
        .then(response => response.json())
        .then(data => {
            const aiContent = document.getElementById('ai-analysis');
            if (aiContent && data[0] && data[0].analysis) {
                const analysis = data[0].analysis;
                const formatted = analysis.replace(/\b(\d+)\.\s+(Temperature|Humidity|Gas Sensor(?: \(.*?\))?|Altitude):/gi, "<br><strong>$1. $2:</strong>");
                aiContent.innerHTML = formatted.replace(/\n/g, '<br>');
            }
        })
        .catch(error => console.log('Error fetching AI data:', error));
}

function refreshHistoryData() {
    fetch('../data/history.json')
        .then(response => response.json())
        .then(data => {
            const historyContainer = document.querySelector('#history .history-table');
            if (!historyContainer) return;
            
            if (data && Array.isArray(data) && data.length > 0) {
                let tableHTML = `
                    <h3 class="table-title">Sensor Data History</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Temperature</th>
                                <th>Humidity</th>
                                <th>Gas Level</th>
                                <th>Altitude</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                data.forEach(record => {
                    const temp = record.temperature || 'N/A';
                    const humidity = record.humidity || 'N/A';
                    const gas = record.gas_sensor || 'N/A';
                    const altitude = record.altitude || 'N/A';
                    const timestamp = record.timestamp || 'N/A';
                    
                    // Determine status
                    const status = getSensorStatus(
                        temp !== 'N/A' ? temp : 0,
                        humidity !== 'N/A' ? humidity : 0,
                        gas !== 'N/A' ? gas : 0
                    );
                    
                    tableHTML += `
                        <tr>
                            <td>${timestamp}</td>
                            <td>${temp}${temp !== 'N/A' ? '°C' : ''}</td>
                            <td>${humidity}${humidity !== 'N/A' ? '%' : ''}</td>
                            <td>${gas}</td>
                            <td>${altitude}${altitude !== 'N/A' ? 'm' : ''}</td>
                            <td><span class="status-badge ${status.class}">${status.text}</span></td>
                        </tr>
                    `;
                });
                
                tableHTML += '</tbody></table>';
                historyContainer.innerHTML = tableHTML;
            } else {
                historyContainer.innerHTML = `
                    <h3 class="table-title">Sensor Data History</h3>
                    <div class="no-data-message">
                        No historical data available yet. Data will appear here once collection begins.
                    </div>
                `;
            }
        })
        .catch(error => console.log('Error fetching history data:', error));
}

document.querySelectorAll('.metric-card').forEach(card => {
    card.addEventListener('click', function(e) {
        const rect = card.getBoundingClientRect();
        const ripple = document.createElement('div');
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(255, 255, 255, 0.3)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s linear';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.width = '20px';
        ripple.style.height = '20px';
        ripple.style.marginLeft = '-10px';
        ripple.style.marginTop = '-10px';
        ripple.style.pointerEvents = 'none';
        
        card.style.position = 'relative';
        card.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    });
});

// Initialize dashboard
window.addEventListener('load', function() {
    // Initial status update on page load
    refreshSensorData();
    
    setTimeout(() => {
        initializeStream();
    }, 1000);
    
    // Set up auto-refresh intervals
    setInterval(refreshSensorData, 5000);    // Every 5 seconds
    setInterval(refreshAIInsights, 30000);    // Every 30 seconds
    setInterval(refreshHistoryData, 30000);   // Every 30 seconds
});

// Handle page visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden, optionally pause stream
    } else {
        // Page is visible again, resume stream if it was active
        if (streamActive && !cameraStream.src) {
            initializeStream();
        }
    }
});

// Prevent modal from closing when clicking on the image
if (document.querySelector('.camera-modal-content')) {
    document.querySelector('.camera-modal-content').addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(e.target) && 
        !toggle.contains(e.target) && 
        sidebar.classList.contains('mobile-open')) {
        sidebar.classList.remove('mobile-open');
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        document.getElementById('sidebar').classList.remove('mobile-open');
    }
});
