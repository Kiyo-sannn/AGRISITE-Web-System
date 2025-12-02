<?php
$esp32_ip = "192.168.68.188";
$stream_port = "81";
$stream_url = "http://{$esp32_ip}:{$stream_port}/stream";

function readJsonData($filename) {
    if (file_exists($filename)) {
        $json_content = file_get_contents($filename);
        return json_decode($json_content, true);
    }
    return null;
}

$sensor_data = readJsonData('../data/raw_data.json');
$ai_data = readJsonData('../data/ai_data.json');
$history_data = readJsonData('../data/history.json');

$default_sensor_data = [
    'temperature' => 28.5,
    'humidity' => 57,
    'gas_sensor' => 286,
    'altitude' => 163.7
];

$default_ai_data = [
    'recommendation' => 'Based on the comprehensive data analysis, I recommend implementing a multi-tiered approach focusing on user engagement optimization and performance enhancement. The patterns indicate significant opportunities for improvement in conversion rates through targeted personalization strategies.'
];

$data = $sensor_data ?: $default_sensor_data;
$ai_recommendation = $ai_data ?: $default_ai_data;

if (isset($_GET['proxy_stream'])) {
    header('Content-Type: multipart/x-mixed-replace; boundary=frame');
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'method' => 'GET'
        ]
    ]);
    
    $stream = fopen($stream_url, 'r', false, $context);
    
    if ($stream) {
        while (!feof($stream)) {
            echo fread($stream, 8192);
            flush();
        }
        fclose($stream);
    } else {
        echo "Error: Could not connect to ESP32 camera stream";
    }
    exit;
}

if (isset($_GET['snapshot'])) {
    $snapshot_url = "http://{$esp32_ip}/capture";
    
    $image_data = file_get_contents($snapshot_url);
    
    if ($image_data !== false) {
        header('Content-Type: image/jpeg');
        echo $image_data;
    } else {
        header('HTTP/1.1 404 Not Found');
        echo "Snapshot not available";
    }
    exit;
}

function getSensorStatus($temperature, $humidity, $gas_level) {
    if ($gas_level > 300) return ['class' => 'status-warning', 'text' => 'Warning'];
    if ($temperature > 35 || $humidity > 80) return ['class' => 'status-caution', 'text' => 'Caution'];
    return ['class' => 'status-safe', 'text' => 'Normal'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGRI-SITE</title>
    <link rel="icon" href="../assets/images/Logo_1.png">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <div class="dashboard-container">
        <!-- Left Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">AGRI-SITE</div>
                <div class="subtitle">Smart Agriculture Dashboard</div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a class="nav-link active" data-section="sensors">
                        Sensors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-section="camera">
                        Camera System
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-section="ai-insights">
                        AI Insights
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-section="history">
                        History
                    </a>
                </li>
                <li class="nav-item">
                    <a  class="nav-link" onclick="window.location.href='../pages/agrisite_calc.html'">
                        Cost-Benefit Calculator
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-section="about">
                        About
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <div class="content-title" id="contentTitle">Live Sensor Monitoring</div>
                <div class="content-subtitle" id="contentSubtitle">Real-time data from your agricultural monitoring system</div>
            </div>

            <!-- Sensors Section -->
            <div class="content-section active" id="sensors">
                <div class="sensors-top-row">
                    <div class="metric-card temperature" onclick="redirectToAnalytics('temperature')">
                        <div class="metric-header">
                            <div class="metric-title">Temperature</div>
                            <div class="metric-title">DHT22</div>
                        </div>
                        <div class="metric-value"><?php echo $data['temperature']; ?>°C</div>
                        <div class="metric-subtitle">Current temperature</div>
                        <span class="status-badge status-safe">Normal</span>
                        <div class="click-indicator">Click for analytics</div>
                    </div>

                    <div class="metric-card humidity" onclick="redirectToAnalytics('humidity')">
                        <div class="metric-header">
                            <div class="metric-title">Humidity</div>
                            <div class="metric-title">DHT22</div>
                        </div>
                        <div class="metric-value"><?php echo $data['humidity']; ?>%</div>
                        <div class="metric-subtitle">Relative humidity</div>
                        <span class="status-badge status-safe">Normal</span>
                        <div class="click-indicator">Click for analytics</div>
                    </div>
                </div>

                <!-- Bottom Row: Gas and Altitude -->
                <div class="sensors-bottom-row">
                    <div class="metric-card gas" onclick="redirectToAnalytics('gas')">
                        <div class="metric-header">
                            <div class="metric-title">Gas Sensor</div>
                            <div class="metric-title">MQ2</div>
                        </div>
                        <div class="metric-value"><?php echo $data['gas_sensor']; ?></div>
                        <div class="metric-subtitle">Gas concentration level</div>
                        <span class="status-badge status-safe">Safe</span>
                        <div class="click-indicator">Click for analytics</div>
                    </div>

                    <div class="metric-card altitude" onclick="redirectToAnalytics('altitude')">
                        <div class="metric-header">
                            <div class="metric-title">Altitude</div>
                            <div class="metric-title">Barometric</div>
                        </div>
                        <div class="metric-value"><?php echo $data['altitude']; ?>m</div>
                        <div class="metric-subtitle">Above sea level</div>
                        <span class="status-badge status-safe">Active</span>
                        <div class="click-indicator">Click for analytics</div>
                    </div>
                </div>
            </div>

            <!-- Camera Section -->
            <div class="content-section" id="camera">
                <div class="camera-container">
                    <div class="camera-status-bar">
                        <h3 style="color: white; margin: 0;">ESP32 Camera Feed</h3>
                        <span id="cameraStatus" class="camera-status connecting">Connecting</span>
                    </div>
                    <div class="camera-preview" onclick="openFullscreen()">
                        <img id="cameraStream" style="display: none;" alt="Camera Stream">
                        <div class="camera-overlay" id="cameraOverlay">
                            <div class="camera-text">Click to view live feed</div>
                            <div class="camera-text" style="font-size: 14px; opacity: 0.8;">ESP32 Camera Stream</div>
                        </div>
                    </div>
                    <div style="margin-top: 16px; color: rgba(255, 255, 255, 0.7); font-size: 14px;" id="cameraInfo">
                        Ready to connect to <?php echo $esp32_ip; ?>
                    </div>
                </div>
            </div>

            <!-- AI Insights Section -->
            <div class="content-section" id="ai-insights">
                <div class="ai-container">
                    <div class="ai-header">
                        <div class="ai-icon">🤖</div>
                        <div class="ai-title">AI Insights & Recommendations</div>
                    </div>
                    <div class="ai-content" id="ai-analysis">
                        <?php
                        if (is_array($ai_data) && isset($ai_data[0]['analysis'])) {
                            $analysis = $ai_data[0]['analysis'];

                            // Convert headings (**Heading**) to bold HTML
                            $formatted = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $analysis);

                            // Convert bullet points (*) to HTML list items
                            $lines = explode("\n", $formatted);
                            $in_list = false;
                            $cleaned = '';

                            foreach ($lines as $line) {
                                $line = trim($line);
                                if (preg_match('/^\*\s+(.*)/', $line, $matches)) {
                                    if (!$in_list) {
                                        $cleaned .= '<ul>';
                                        $in_list = true;
                                    }
                                    $cleaned .= '<li>' . $matches[1] . '</li>';
                                } else {
                                    if ($in_list) {
                                        $cleaned .= '</ul>';
                                        $in_list = false;
                                    }
                                    if ($line !== '') {
                                        $cleaned .= '<p>' . $line . '</p>';
                                    }
                                }
                            }

                            if ($in_list) {
                                $cleaned .= '</ul>';
                            }

                            echo $cleaned;
                        } else {
                            echo "NO DATA AVAILABLE";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="content-section" id="history">
                <div class="history-table">
                    <h3 class="table-title">Sensor Data History</h3>
                    
                    <?php if ($history_data && is_array($history_data) && count($history_data) > 0): ?>
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
                            <?php foreach ($history_data as $record): ?>
                            <?php 
                                $status = getSensorStatus(
                                    $record['temperature'] ?? 0, 
                                    $record['humidity'] ?? 0, 
                                    $record['gas_sensor'] ?? 0
                                );
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['timestamp'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($record['temperature'] ?? 'N/A'); ?><?php echo isset($record['temperature']) ? '°C' : ''; ?></td>
                                <td><?php echo htmlspecialchars($record['humidity'] ?? 'N/A'); ?><?php echo isset($record['humidity']) ? '%' : ''; ?></td>
                                <td><?php echo htmlspecialchars($record['gas_sensor'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($record['altitude'] ?? 'N/A'); ?><?php echo isset($record['altitude']) ? 'm' : ''; ?></td>
                                <td><span class="status-badge <?php echo $status['class']; ?>"><?php echo $status['text']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data-message">
                        No historical data available yet. Data will appear here once collection begins.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- About Section -->
            <div class="content-section" id="about">
                <div class="ai-container">
                    <div class="ai-header">
                        <div class="ai-icon">ℹ️</div>
                        <div class="ai-title">About AgriSITE</div>
                    </div>
                    <div class="ai-content">
                        <p><strong>Satellite-Guided Rover Technology for Data-Driven Irrigation and Crop Health Monitoring</strong></p>
                        <br>
                        <p>AgriSITE represents a cutting-edge agricultural monitoring system that combines IoT sensors, satellite technology, and AI-driven analytics to optimize crop health and irrigation management.</p>
                        <br>
                        <p><strong>Key Features:</strong></p>
                        <p>• Real-time environmental monitoring (temperature, humidity, gas levels, altitude)</p>
                        <p>• High-definition ESP32 camera surveillance system with live streaming</p>
                        <p>• AI-powered data analysis and intelligent recommendations</p>
                        <p>• Historical data tracking and trend analysis</p>
                        <p>• Interactive analytics dashboard with sensor-specific insights</p>
                        <p>• Cost-Benefit Calculator with multi-year ROI projections</p>
                        <br>
                        <p><strong>Technology Stack:</strong></p>
                        <p>• ESP32 microcontroller with integrated camera module</p>
                        <p>• DHT22 temperature and humidity sensor</p>
                        <p>• MQ2 gas sensor for air quality monitoring</p>
                        <p>• Barometric pressure sensor for altitude measurement</p>
                        <p>• Web-based dashboard with real-time data visualization</p>
                        <p>• PHP backend with JSON data storage</p>
                        <p>• Chart.js for advanced data analytics</p>
                        <br>
                        <p><strong>Financial Tools:</strong></p>
                        <p>• Smart Cost-Benefit Calculator</p>
                        <p>• ROI projections (1, 3, 5, 10-year scenarios)</p>
                        <p>• Payback period analysis</p>
                        <p>• Customizable deployment configurations</p>
                        <br>
                        <p><strong>Version:</strong> 3.2</p>
                        <p><strong>Last Updated:</strong> December 2025</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Camera Modal -->
    <div id="cameraModal" class="camera-modal" onclick="closeFullscreen()">
        <span class="camera-modal-close" onclick="closeFullscreen()">&times;</span>
        <div class="camera-modal-content">
            <img id="modalCameraStream" alt="Fullscreen Camera">
        </div>
    </div>
    <script src="../scripts/script.js"></script>
</body>
</html>
