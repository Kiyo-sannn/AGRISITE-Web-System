<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Analytics - AGRI-SITE</title>
    <link rel="icon" href="../assets/images/Logo_1.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #4682b4 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Left Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 0 20px 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo {
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            margin: 0 10px 20px 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-3px);
        }

        .back-icon {
            font-size: 16px;
        }

        .nav-menu {
            list-style: none;
            padding: 0 10px;
        }

        .nav-item {
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 14px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            border-left: 4px solid #ffd700;
            background: rgba(255, 215, 0, 0.1);
        }

        .nav-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .temp-icon { background: linear-gradient(45deg, #ff6b6b, #ffa726); }
        .humidity-icon { background: linear-gradient(45deg, #42a5f5, #2196f3); }
        .gas-icon { background: linear-gradient(45deg, #66bb6a, #4caf50); }

        .sensor-info h3 {
            font-size: 16px;
            margin-bottom: 3px;
        }

        .sensor-info p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Main Content Area */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }

        .content-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .content-title {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .content-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 24px;
            position: relative;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .chart-title {
            color: white;
            font-size: 20px;
            font-weight: 600;
        }

        .chart-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #66bb6a;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .status-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }

        .threshold-info {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            padding: 16px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            flex-wrap: wrap;
        }

        .threshold-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
        }

        .threshold-color {
            width: 16px;
            height: 3px;
            border-radius: 2px;
        }

        .loading-spinner {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #ffd700;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .no-data-message {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
            font-style: italic;
        }

        .refresh-info {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .refresh-timer {
            color: #ffd700;
            font-weight: 600;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .stat-value {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Mobile Responsive */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 200;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 60px 15px 20px 15px;
            }

            .threshold-info {
                flex-direction: column;
                gap: 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Smooth transitions */
        .content-section {
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>

    <div class="dashboard-container">
        <!-- Left Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">Sensor Analytics</div>
                <div class="subtitle">Real-time Data Analysis</div>
            </div>
            
            <!-- Back Button -->
            <a href="../pages/agrisite.php" class="back-button">
                <span class="back-icon">←</span>
                <span>Back to Dashboard</span>
            </a>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#temperature" class="nav-link active" data-sensor="temperature">
                        <div class="nav-icon temp-icon">🌡️</div>
                        <div class="sensor-info">
                            <h3>Temperature</h3>
                            <p>DHT22 Sensor</p>
                        </div>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="#humidity" class="nav-link" data-sensor="humidity">
                        <div class="nav-icon humidity-icon">💧</div>
                        <div class="sensor-info">
                            <h3>Humidity</h3>
                            <p>DHT22 Sensor</p>
                        </div>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="#gas" class="nav-link" data-sensor="gas">
                        <div class="nav-icon gas-icon">🔥</div>
                        <div class="sensor-info">
                            <h3>Gas Sensor</h3>
                            <p>MQ2 Sensor</p>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <div class="content-title" id="main-title">Temperature Analytics</div>
                <div class="content-subtitle" id="main-subtitle">Real-time temperature monitoring with historical data analysis</div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="currentValue">--</div>
                    <div class="stat-label">Current Value</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="averageValue">--</div>
                    <div class="stat-label">Average</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="maxValue">--</div>
                    <div class="stat-label">Maximum</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="minValue">--</div>
                    <div class="stat-label">Minimum</div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="chart-container content-section">
                <div class="chart-header">
                    <h3 class="chart-title" id="chart-title">Temperature Over Time</h3>
                    <div class="chart-status">
                        <div class="status-indicator"></div>
                        <span class="status-text">Live Data</span>
                    </div>
                </div>
                
                <div class="chart-wrapper">
                    <canvas id="sensorChart"></canvas>
                    <div class="loading-spinner" id="loadingSpinner"></div>
                </div>
                
                <div class="threshold-info" id="thresholdInfo">
                </div>
            </div>
        </div>
    </div>

    <div class="refresh-info">
        <span>Auto-refresh: </span>
        <span class="refresh-timer" id="refreshTimer">0:30</span>
    </div>

    <script>
        class SensorAnalytics {
            constructor() {
                this.currentSensor = 'temperature';
                this.chart = null;
                this.refreshInterval = null;
                this.countdownInterval = null;
                this.refreshCountdown = 30;
                
                this.thresholds = {
                    temperature: {
                        min: 20,
                        max: 35,
                        unit: '°C',
                        color: '#ff6b6b',
                        warningHigh: 32,
                        warningLow: 22
                    },
                    humidity: {
                        min: 40,
                        max: 80,
                        unit: '%',
                        color: '#42a5f5',
                        warningHigh: 75,
                        warningLow: 45
                    },
                    gas: {
                        min: 0,
                        max: 500,
                        unit: 'ppm',
                        color: '#66bb6a',
                        warningHigh: 300,
                        warningLow: 0
                    }
                };
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
                this.checkUrlParameter();
                this.loadSensorData();
                this.startRefreshTimer();
                this.startCountdown();
            }
            
            checkUrlParameter() {
                const urlParams = new URLSearchParams(window.location.search);
                const sensor = urlParams.get('sensor');
                if (sensor && this.thresholds[sensor]) {
                    this.switchSensor(sensor);
                }
            }
            
            setupEventListeners() {
                const navLinks = document.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const sensor = link.dataset.sensor;
                        this.switchSensor(sensor);
                    });
                });
            }
            
            switchSensor(sensor) {
                // Update active nav
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                document.querySelector(`[data-sensor="${sensor}"]`).classList.add('active');
                
                // Update content
                this.currentSensor = sensor;
                this.updateContent();
                this.loadSensorData();
            }
            
            updateContent() {
                const titles = {
                    temperature: {
                        main: 'Temperature Analytics',
                        subtitle: 'Real-time temperature monitoring with historical data analysis',
                        chart: 'Temperature Over Time'
                    },
                    humidity: {
                        main: 'Humidity Analytics',
                        subtitle: 'Real-time humidity monitoring with historical data analysis',
                        chart: 'Humidity Over Time'
                    },
                    gas: {
                        main: 'Gas Sensor Analytics',
                        subtitle: 'Real-time gas concentration monitoring with historical data analysis',
                        chart: 'Gas Concentration Over Time'
                    }
                };
                
                document.getElementById('main-title').textContent = titles[this.currentSensor].main;
                document.getElementById('main-subtitle').textContent = titles[this.currentSensor].subtitle;
                document.getElementById('chart-title').textContent = titles[this.currentSensor].chart;
                
                this.updateThresholdInfo();
            }
            
            updateThresholdInfo() {
                const threshold = this.thresholds[this.currentSensor];
                const thresholdContainer = document.getElementById('thresholdInfo');
                
                thresholdContainer.innerHTML = `
                    <div class="threshold-item">
                        <div class="threshold-color" style="background-color: ${threshold.color}"></div>
                        <span>Current Value</span>
                    </div>
                    <div class="threshold-item">
                        <div class="threshold-color" style="background-color: #ffa726"></div>
                        <span>Warning High: ${threshold.warningHigh}${threshold.unit}</span>
                    </div>
                    <div class="threshold-item">
                        <div class="threshold-color" style="background-color: #ef5350"></div>
                        <span>Critical High: ${threshold.max}${threshold.unit}</span>
                    </div>
                    <div class="threshold-item">
                        <div class="threshold-color" style="background-color: #42a5f5"></div>
                        <span>Warning Low: ${threshold.warningLow}${threshold.unit}</span>
                    </div>
                `;
            }
            
            async loadSensorData() {
                const spinner = document.getElementById('loadingSpinner');
                spinner.style.display = 'block';
                
                try {
                    // Load both current data and history
                    const [historyResponse, currentResponse] = await Promise.all([
                        fetch('../data/history.json'),
                        fetch('../data/raw_data.json')
                    ]);
                    
                    const historyData = await historyResponse.json();
                    const currentData = await currentResponse.json();
                    
                    if (historyData && Array.isArray(historyData) && historyData.length > 0) {
                        this.renderChart(historyData);
                        this.updateStats(historyData, currentData);
                    } else {
                        this.showNoDataMessage();
                    }
                } catch (error) {
                    console.error('Error loading sensor data:', error);
                    this.showNoDataMessage();
                } finally {
                    spinner.style.display = 'none';
                }}
                        
                updateStats(historyData, currentData) {
                    let values = [];
                    let current = null;
                    
                    switch(this.currentSensor) {
                        case 'temperature':
                            values = historyData.map(item => item.temperature || 0).filter(val => val > 0);
                            current = currentData?.temperature || values[values.length - 1];
                            break;
                        case 'humidity':
                            values = historyData.map(item => item.humidity || 0).filter(val => val > 0);
                            current = currentData?.humidity || values[values.length - 1];
                            break;
                        case 'gas':
                            values = historyData.map(item => item.gas_sensor || 0).filter(val => val >= 0);
                            current = currentData?.gas_sensor || values[values.length - 1];
                            break;
                    }
                    
                    if (values.length > 0) {
                        const average = values.reduce((a, b) => a + b, 0) / values.length;
                        const max = Math.max(...values);
                        const min = Math.min(...values);
                        const unit = this.thresholds[this.currentSensor].unit;
                        
                        // Use current data from raw_data.json for "Current Value"
                        document.getElementById('currentValue').textContent = current.toFixed(1) + unit;
                        document.getElementById('averageValue').textContent = average.toFixed(1) + unit;
                        document.getElementById('maxValue').textContent = max.toFixed(1) + unit;
                        document.getElementById('minValue').textContent = min.toFixed(1) + unit;
                    } else {
                        document.getElementById('currentValue').textContent = '--';
                        document.getElementById('averageValue').textContent = '--';
                        document.getElementById('maxValue').textContent = '--';
                        document.getElementById('minValue').textContent = '--';
                    }
                }
            
            renderChart(data) {
                const ctx = document.getElementById('sensorChart').getContext('2d');
                const threshold = this.thresholds[this.currentSensor];
                
                // Prepare data based on current sensor
                const labels = data.map(item => {
                    const date = new Date(item.timestamp);
                    return date.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                });
                
                let values = [];
                
                switch(this.currentSensor) {
                    case 'temperature':
                        values = data.map(item => item.temperature || 0);
                        break;
                    case 'humidity':
                        values = data.map(item => item.humidity || 0);
                        break;
                    case 'gas':
                        values = data.map(item => item.gas_sensor || 0);
                        break;
                }
                
                // Destroy existing chart
                if (this.chart) {
                    this.chart.destroy();
                }
                
                // Create new chart
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: `${this.currentSensor.charAt(0).toUpperCase() + this.currentSensor.slice(1)} (${threshold.unit})`,
                            data: values,
                            borderColor: threshold.color,
                            backgroundColor: threshold.color + '20',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: threshold.color,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: 'rgba(255, 255, 255, 0.9)',
                                    font: {
                                        size: 13
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Time',
                                    color: 'rgba(255, 255, 255, 0.9)',
                                    font: {
                                        size: 14
                                    }
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.7)',
                                    maxTicksLimit: 8
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: `${this.currentSensor.charAt(0).toUpperCase() + this.currentSensor.slice(1)} (${threshold.unit})`,
                                    color: 'rgba(255, 255, 255, 0.9)',
                                    font: {
                                        size: 14
                                    }
                                },
                                ticks: {
                                    color: 'rgba(255, 255, 255, 0.7)'
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                },
                                min: Math.max(0, Math.min(...values) - 5),
                                max: Math.max(...values) + 5
                            }
                        }
                    },
                    plugins: [{
                        afterDraw: (chart) => {
                            this.drawThresholdLines(chart, threshold);
                        }
                    }]
                });
            }
            
            drawThresholdLines(chart, threshold) {
                const ctx = chart.ctx;
                const chartArea = chart.chartArea;
                
                // Draw warning high line
                const warningHighY = chart.scales.y.getPixelForValue(threshold.warningHigh);
                if (warningHighY >= chartArea.top && warningHighY <= chartArea.bottom) {
                    ctx.save();
                    ctx.strokeStyle = '#ffa726';
                    ctx.setLineDash([5, 5]);
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.moveTo(chartArea.left, warningHighY);
                    ctx.lineTo(chartArea.right, warningHighY);
                    ctx.stroke();
                    ctx.restore();
                }
                
                // Draw critical high line
                const criticalHighY = chart.scales.y.getPixelForValue(threshold.max);
                if (criticalHighY >= chartArea.top && criticalHighY <= chartArea.bottom) {
                    ctx.save();
                    ctx.strokeStyle = '#ef5350';
                    ctx.setLineDash([10, 5]);
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.moveTo(chartArea.left, criticalHighY);
                    ctx.lineTo(chartArea.right, criticalHighY);
                    ctx.stroke();
                    ctx.restore();
                }
            }
            
            showNoDataMessage() {
                const chartWrapper = document.querySelector('.chart-wrapper');
                chartWrapper.innerHTML = '<div class="no-data-message">No historical data available yet. Data will appear here once collection begins.</div>';
                
                // Reset stats
                document.getElementById('currentValue').textContent = '--';
                document.getElementById('averageValue').textContent = '--';
                document.getElementById('maxValue').textContent = '--';
                document.getElementById('minValue').textContent = '--';
            }
            
            startRefreshTimer() {
                // refresh every 30 seconds
                this.refreshInterval = setInterval(() => {
                    this.loadSensorData();
                    this.refreshCountdown = 30; // reset countdown
                }, 30000);

                // initialize countdown
                this.refreshCountdown = 30;
                this.startCountdown();
            }

            startCountdown() {
                this.countdownInterval = setInterval(() => {
                    if (this.refreshCountdown <= 0) {
                        this.refreshCountdown = 30; // reset to 30 sec
                    }

                    const seconds = this.refreshCountdown;
                    document.getElementById('refreshTimer').textContent =
                        `0:${seconds.toString().padStart(2, '0')}`;

                    this.refreshCountdown--;
                }, 1000);
            }}
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }
        
        // Initialize the application
        document.addEventListener('DOMContentLoaded', () => {
            new SensorAnalytics();
        });
    </script>
</body>
</html>