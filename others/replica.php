<?php
// index.php - Combined single-file version (CSS + JS + HTML)
// Replace ESP32 values if needed:
$esp32_ip = "192.168.68.204";
$stream_port = "81";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>AgriSight Dashboard</title>

  <!-- ---------- Styles (from style.css, inlined) ---------- -->
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #4682b4 100%);
      min-height: 100vh;
      overflow-x: hidden;
      color: white;
    }
    .dashboard-container { display: flex; min-height: 100vh; }

    /* Sidebar */
    .sidebar {
      width: 280px;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(12px);
      border-right: 1px solid rgba(255,255,255,0.06);
      padding: 20px 0;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
      z-index: 100;
    }
    .sidebar-header { padding: 0 20px 22px; border-bottom: 1px solid rgba(255,255,255,0.04); margin-bottom: 18px; }
    .logo { color: white; font-size: 20px; font-weight: 700; }
    .subtitle { color: rgba(255,255,255,0.7); font-size: 12px; }

    .main-content { margin-left: 280px; padding: 20px; width: calc(100% - 280px); min-height: 100vh; }

    .nav-menu { list-style: none; padding: 0 10px; display:flex; flex-direction:column; gap:8px; }
    .nav-link {
      display:flex; align-items:center; padding:10px 14px; color: rgba(255,255,255,0.85);
      text-decoration:none; border-radius:10px; cursor:pointer; font-size:14px;
      transition:0.18s;
    }
    .nav-link:hover { transform: translateX(6px); background: rgba(255,255,255,0.04); }
    .nav-link.active { background: rgba(255,215,0,0.08); color: white; border-left: 4px solid #ffd700; transform:none; }

    .content-header {
      background: rgba(255,255,255,0.06); backdrop-filter: blur(10px);
      border-radius: 12px; padding: 18px; margin-bottom: 18px; border:1px solid rgba(255,255,255,0.04);
    }
    .content-title { color: white; font-size: 22px; font-weight: 600; }
    .content-subtitle { color: rgba(255,255,255,0.75); font-size: 13px; margin-top:6px; }

    .content-section { display: none; }
    .content-section.active { display: block; animation: fadeInUp 0.36s ease; }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(12px); } to { opacity:1; transform: translateY(0); }
    }

    /* Metric cards (small styles) */
    .metric-card { background: rgba(255,255,255,0.06); padding: 18px; border-radius:12px; border:1px solid rgba(255,255,255,0.03); margin-bottom:12px; }
    .metric-value { font-size:28px; font-weight:700; color:white; }

    /* Camera */
    .camera-preview { width:100%; height:300px; border-radius:12px; overflow:hidden; background:linear-gradient(135deg,#2c3e50,#34495e); display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.7); }

    /* AI modal (small) */
    .ai-chat-modal { display:none; position:fixed; inset:0; background: rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:1200; }
    .ai-chat-modal.active { display:flex; }

    /* History table */
    .history-table { background: rgba(255,255,255,0.04); padding: 18px; border-radius:10px; border:1px solid rgba(255,255,255,0.03); overflow:auto; }

    /* Calculator Section */
    .calc-form {
      display:flex; flex-direction:column; gap:12px; background: rgba(255,255,255,0.03); padding:16px; border-radius:10px; color:white;
      border:1px solid rgba(255,255,255,0.03);
    }
    .calc-label { font-size:13px; font-weight:600; opacity:0.95; }
    .calc-input { width:100%; padding:10px 12px; border-radius:8px; border:none; background: rgba(255,255,255,0.06); color:white; outline:none; }
    .calc-input::placeholder { color: rgba(255,255,255,0.5); }
    .calc-checkbox label { display:flex; gap:8px; align-items:center; font-size:13px; opacity:0.95; }
    .calc-btn { background:#4da3ff; border:none; padding:12px; border-radius:10px; color:white; font-weight:700; cursor:pointer; }
    .calc-results { background: rgba(0,0,0,0.2); padding:12px; border-radius:8px; margin-top:8px; color:white; }
    .small-muted { color: rgba(255,255,255,0.65); font-size:13px; }

    /* responsive */
    @media (max-width: 900px) {
      .sidebar { position:relative; width:100%; height:auto; }
      .main-content { margin-left: 0; width:100%; }
    }
  </style>
  <!-- ---------- End Styles ---------- -->
</head>
<body data-esp32-ip="<?php echo htmlspecialchars($esp32_ip); ?>" data-stream-port="<?php echo htmlspecialchars($stream_port); ?>">

  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
      <div class="sidebar-header">
        <div class="logo">AgriSight</div>
        <div class="subtitle">GrowVer Integration</div>
      </div>

      <nav>
        <ul class="nav-menu" id="navMenu">
          <li><a href="#sensors" class="nav-link" data-section="sensors">Sensors</a></li>
          <li><a href="#camera" class="nav-link" data-section="camera">Camera</a></li>
          <li><a href="#ai-insights" class="nav-link" data-section="ai-insights">AI Insights</a></li>
          <li><a href="#history" class="nav-link" data-section="history">History</a></li>
          <li><a href="#agrisight-calc" class="nav-link" data-section="agrisight-calc">Calculator</a></li>
          <li><a href="#about" class="nav-link" data-section="about">About</a></li>
        </ul>
      </nav>

      <div style="padding:16px;">
        <button class="ai-chat-button" onclick="openAIChat()" title="Chat with AI">Chat AI</button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
      <header class="content-header">
        <div id="contentTitle" class="content-title">Live Sensor Monitoring</div>
        <div id="contentSubtitle" class="content-subtitle">Real-time agricultural sensor data</div>
      </header>

      <!-- Sections -->
      <section id="sensors" class="content-section active">
        <div class="sensors-top-row">
          <div class="metric-card">
            <div class="metric-title small-muted">Temperature</div>
            <div class="metric-value temperature metric-value" id="tempMetric">-- °C</div>
          </div>

          <div class="metric-card">
            <div class="metric-title small-muted">Humidity</div>
            <div class="metric-value humidity metric-value" id="humMetric">-- %</div>
          </div>
        </div>

        <div class="sensors-bottom-row">
          <div class="metric-card">
            <div class="metric-title small-muted">Gas</div>
            <div class="metric-value gas metric-value" id="gasMetric">--</div>
          </div>

          <div class="metric-card">
            <div class="metric-title small-muted">Altitude</div>
            <div class="metric-value altitude metric-value" id="altMetric">-- m</div>
          </div>
        </div>
      </section>

      <section id="camera" class="content-section">
        <div class="camera-container">
          <div class="camera-preview">
            <img id="cameraStream" alt="camera feed" style="width:100%; height:100%; object-fit:cover; display:none;">
            <div id="cameraOverlay" class="camera-overlay" style="display:flex;">
              <div class="camera-text">Connecting to camera...</div>
            </div>
          </div>
          <div style="margin-top:10px;">
            <span id="cameraStatus" class="camera-status connecting">Connecting</span>
            <div id="cameraInfo" class="small-muted" style="margin-top:6px;">No stream yet</div>
          </div>
        </div>
      </section>

      <section id="ai-insights" class="content-section">
        <div class="ai-container">
          <div class="ai-section-title">AI Insights</div>
          <div id="ai-analysis" class="ai-content small-muted">AI analysis will appear here when available.</div>
        </div>
      </section>

      <section id="history" class="content-section">
        <div id="history" class="history-table">
          <div class="table-title">Sensor Data History</div>
          <div class="no-data-message">No historical data available yet.</div>
        </div>
      </section>

      <section id="agrisight-calc" class="content-section">
        <h3 style="margin-bottom:10px;">AgriSight Calculator</h3>

        <form id="calcForm" class="calc-form" onsubmit="return false;">
          <div>
            <label class="calc-label">Deployment cost (₱)</label>
            <input id="deploymentType" class="calc-input" type="number" placeholder="Total deployment cost in ₱" value="10000" />
          </div>

          <div>
            <label class="calc-label">Cube Count (number of units) — each count uses 2200 ₱</label>
            <input id="cubeCount" class="calc-input" type="number" placeholder="Number of cubes (units)" value="0" />
          </div>

          <div>
            <div class="calc-label">Sensors (additive cost values)</div>
            <div class="calc-checkbox">
              <label><input type="checkbox" class="sensor" value="1500" /> Soil (₱1,500)</label><br/>
              <label><input type="checkbox" class="sensor" value="1200" /> Humidity (₱1,200)</label><br/>
              <label><input type="checkbox" class="sensor" value="900" /> Gas (₱900)</label>
            </div>
          </div>

          <div>
            <label class="calc-label">Expected yield (kg)</label>
            <input id="yield" class="calc-input" type="number" step="any" placeholder="e.g., 500" value="0" />
          </div>

          <div>
            <label class="calc-label">Price per kg (₱)</label>
            <input id="price" class="calc-input" type="number" step="any" placeholder="e.g., 50" value="0" />
          </div>

          <div>
            <label class="calc-label">Projected improvement (%)</label>
            <input id="improvement" class="calc-input" type="number" step="any" placeholder="e.g., 10" value="0" />
          </div>

          <div style="display:flex; gap:8px; align-items:center;">
            <button id="calcBtn" class="calc-btn" type="button">Calculate</button>
            <button id="resetCalcBtn" class="calc-btn" type="button" style="background:#9e9e9e;">Reset</button>
          </div>

          <div class="calc-results" id="calcResults" aria-live="polite">
            <div>Cost: <strong id="costResult">₱0</strong></div>
            <div>Yield Gain: <strong id="yieldResult">0 kg</strong></div>
            <div>Income Gain: <strong id="incomeResult">₱0.00</strong></div>
          </div>
        </form>
      </section>

      <section id="about" class="content-section">
        <div class="metric-card">
          <h4>About AgriSight</h4>
          <p class="small-muted">System information and integration overview.</p>
        </div>
      </section>
    </main>
  </div>

  <!-- AI Chat modal minimal -->
  <div id="aiChatModal" class="ai-chat-modal" onclick="closeAIChat()">
    <div style="width:90%; max-width:640px; background:white; padding:18px; border-radius:10px;" onclick="event.stopPropagation();">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <strong>AI Chat</strong>
        <button onclick="closeAIChat()" style="background:#eee; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;">Close</button>
      </div>
      <div id="aiChatMessages" style="height:300px; overflow:auto; margin-top:12px; background:#f8f9fa; padding:12px; border-radius:8px;"></div>
      <div style="margin-top:12px; display:flex; gap:8px;">
        <textarea id="aiChatInput" style="flex:1; padding:8px;" placeholder="Type message..."></textarea>
        <button id="sendButton" onclick="sendMessage()" style="padding:10px 14px; background:#667eea; color:white; border:none; border-radius:6px;">Send</button>
      </div>
    </div>
  </div>

  <!-- ---------- Scripts (from script.js, inlined and fixed) ---------- -->
  <script>
  (function () {
    // Config from body dataset
    const ESP32_IP = document.body.dataset.esp32Ip || '192.168.68.204';
    const STREAM_PORT = document.body.dataset.streamPort || '81';
    const STREAM_URL = `http://${ESP32_IP}:${STREAM_PORT}/stream`;
    const PROXY_URL = '?proxy_stream=1';

    let streamActive = false;
    let reconnectAttempts = 0;
    const maxReconnectAttempts = 5;
    let reconnectTimeout;

    // DOM shortcuts; many elements may not be present on some pages so guard access
    const cameraStream = document.getElementById('cameraStream');
    const cameraOverlay = document.getElementById('cameraOverlay');
    const cameraStatus = document.getElementById('cameraStatus');
    const cameraInfo = document.getElementById('cameraInfo');

    // Robust section-switcher
    function switchSection(sectionId, pushState = true) {
      if (!sectionId) return;
      // Hide all sections
      document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
      // Remove active from nav links
      document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));

      // Activate section by id (if exists)
      const target = document.getElementById(sectionId);
      if (target) {
        target.classList.add('active');
      } else {
        // If no matched id, try partial match (fallback)
        const fallback = document.querySelector('.content-section');
        if (fallback) fallback.classList.add('active');
      }

      // Activate nav link with matching data-section
      let activeNav = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
      if (!activeNav) {
        // try by hash href match
        activeNav = document.querySelector(`.nav-link[href="#${sectionId}"]`);
      }
      if (activeNav) activeNav.classList.add('active');

      // Update header text
      updateContentHeader(sectionId);

      // update URL hash (but only if pushState true)
      if (pushState) {
        try {
          history.replaceState(null, '', '#' + sectionId);
        } catch (e) {
          // ignore (some browsers may block)
          location.hash = '#' + sectionId;
        }
      }

      // If user navigates to camera section — initialize stream
      if (sectionId === 'camera') {
        initializeStream();
      }
    }

    function updateContentHeader(sectionId) {
      const titles = {
        sensors: { title: "Live Sensor Monitoring", subtitle: "Real-time agricultural sensor data" },
        camera: { title: "Camera System", subtitle: "Live feed from the ESP32-CAM module" },
        "ai-insights": { title: "AI Insights & Recommendations", subtitle: "Automated analysis for better decision making" },
        history: { title: "Historical Data", subtitle: "Track past sensor readings and trends" },
        "agrisight-calc": { title: "AgriSight Calculator", subtitle: "Cost-benefit and field efficiency calculator" },
        about: { title: "About AgriSight & GrowVer", subtitle: "System information and technology overview" }
      };
      const header = titles[sectionId];
      const titleEl = document.getElementById("contentTitle");
      const subEl = document.getElementById("contentSubtitle");
      if (header && titleEl && subEl) {
        titleEl.textContent = header.title;
        subEl.textContent = header.subtitle;
      } else {
        // fallback: try to derive title from nav link
        const nav = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
        if (nav && titleEl) titleEl.textContent = nav.textContent.trim();
        if (subEl && !header) subEl.textContent = "";
      }
    }

    // wire up nav links (event delegation)
    document.getElementById('navMenu').addEventListener('click', function (ev) {
      const link = ev.target.closest('.nav-link');
      if (!link) return;
      ev.preventDefault();
      const sectionId = link.dataset.section || (link.getAttribute('href')||'').replace('#','');
      if (sectionId) switchSection(sectionId, true);
    });

    // On load: pick section by hash if available, otherwise keep 'sensors'
    document.addEventListener('DOMContentLoaded', function () {
      const hash = (location.hash || '').replace('#','');
      const start = hash || 'sensors';
      switchSection(start, false);

      // Set up auto-refreshers (non-fatal if fetch fails)
      setInterval(refreshSensorData, 30000);
      setInterval(refreshAIInsights, 60000);
      setInterval(refreshHistoryData, 45000);

      // bind calculator button
      const calcBtn = document.getElementById('calcBtn');
      if (calcBtn) calcBtn.addEventListener('click', computeAgriCalc);
      const resetBtn = document.getElementById('resetCalcBtn');
      if (resetBtn) resetBtn.addEventListener('click', resetCalculator);

      // Small safety: if camera section is active on load initialize stream
      if (start === 'camera') initializeStream();
    });

    /* ---------------- Camera stream (defensive) ---------------- */
    function updateStatus(status, message) {
      if (!cameraStatus) return;
      cameraStatus.className = 'camera-status ' + status;
      let statusText = status === 'online' ? 'Connected' : status === 'offline' ? 'Offline' : 'Connecting';
      cameraStatus.textContent = statusText;
      if (cameraInfo && message) cameraInfo.textContent = message;
    }

    function initializeStream() {
      // Do nothing if no camera elements present
      if (!cameraStream || !cameraOverlay) return;
      updateStatus('connecting', 'Connecting to camera...');
      streamActive = false;

      const tryDirect = () => {
        cameraStream.src = STREAM_URL + '?t=' + Date.now();
        cameraStream.style.display = 'none'; // hidden until load
        cameraStream.onload = function () {
          streamActive = true;
          reconnectAttempts = 0;
          cameraOverlay.style.display = 'none';
          cameraStream.style.display = 'block';
          updateStatus('online', `Connected to ${ESP32_IP}`);
        };
        cameraStream.onerror = function () {
          tryProxy();
        };
      };

      const tryProxy = () => {
        cameraStream.src = PROXY_URL + '&t=' + Date.now();
        cameraStream.onload = function () {
          streamActive = true;
          reconnectAttempts = 0;
          cameraOverlay.style.display = 'none';
          cameraStream.style.display = 'block';
          updateStatus('online', `Connected via proxy to ${ESP32_IP}`);
        };
        cameraStream.onerror = function () {
          handleConnectionError();
        };
      };

      const handleConnectionError = () => {
        streamActive = false;
        cameraOverlay.style.display = 'flex';
        cameraStream.style.display = 'none';
        if (reconnectAttempts < maxReconnectAttempts) {
          reconnectAttempts++;
          updateStatus('connecting', `Reconnecting... (${reconnectAttempts}/${maxReconnectAttempts})`);
          clearTimeout(reconnectTimeout);
          reconnectTimeout = setTimeout(() => initializeStream(), 3000 * reconnectAttempts);
        } else {
          updateStatus('offline', 'Connection failed. Check ESP32 camera.');
          if (cameraOverlay) cameraOverlay.querySelector('.camera-text').textContent = 'Camera Offline';
        }
      };

      // Start direct attempt
      tryDirect();
    }

    /* ---------------- Auto-refresh functions ---------------- */
    function safeSetText(id, text) {
      const el = document.getElementById(id);
      if (el) el.textContent = text;
    }

    function refreshSensorData() {
      fetch('data/raw_data.json').then(r => r.json()).then(data => {
        if (!data) return;
        safeSetText('tempMetric', (data.temperature !== undefined ? data.temperature + '°C' : '-- °C'));
        safeSetText('humMetric', (data.humidity !== undefined ? data.humidity + '%' : '-- %'));
        safeSetText('gasMetric', (data.gas_sensor !== undefined ? data.gas_sensor : '--'));
        safeSetText('altMetric', (data.altitude !== undefined ? data.altitude + 'm' : '-- m'));
      }).catch(err => {
        // silently ignore; keep previous UI values
        // console.log('Error fetching sensor data', err);
      });
    }

    function refreshAIInsights() {
      fetch('data/ai_data.json').then(r => r.json()).then(data => {
        const aiContent = document.getElementById('ai-analysis');
        if (!aiContent) return;
        if (Array.isArray(data) && data[0] && data[0].analysis) {
          const analysis = data[0].analysis;
          const formatted = analysis.replace(/\n/g, '<br>');
          aiContent.innerHTML = formatted;
        }
      }).catch(() => {});
    }

    function refreshHistoryData() {
      fetch('data/history.json').then(r => r.json()).then(data => {
        const historyContainer = document.querySelector('#history .history-table');
        if (!historyContainer) return;
        // build table if data exists
        if (Array.isArray(data) && data.length) {
          let html = '<div class="table-title">Sensor Data History</div><table class="data-table" style="width:100%; border-collapse:collapse;"><thead><tr><th>Timestamp</th><th>Temperature</th><th>Humidity</th><th>Gas</th><th>Altitude</th></tr></thead><tbody>';
          data.forEach(record => {
            html += `<tr>
              <td>${record.timestamp || 'N/A'}</td>
              <td>${record.temperature !== undefined ? record.temperature + '°C' : 'N/A'}</td>
              <td>${record.humidity !== undefined ? record.humidity + '%' : 'N/A'}</td>
              <td>${record.gas_sensor !== undefined ? record.gas_sensor : 'N/A'}</td>
              <td>${record.altitude !== undefined ? record.altitude + 'm' : 'N/A'}</td>
            </tr>`;
          });
          html += '</tbody></table>';
          historyContainer.innerHTML = html;
        } else {
          historyContainer.innerHTML = '<div class="table-title">Sensor Data History</div><div class="no-data-message">No historical data available yet.</div>';
        }
      }).catch(() => {});
    }

    /* ---------------- AI Chat (minimal) ---------------- */
    window.openAIChat = function () {
      const modal = document.getElementById('aiChatModal');
      if (!modal) return;
      modal.classList.add('active');
      const input = document.getElementById('aiChatInput');
      if (input) input.focus();
      document.body.style.overflow = 'hidden';
    };
    window.closeAIChat = function () {
      const modal = document.getElementById('aiChatModal');
      if (!modal) return;
      modal.classList.remove('active');
      document.body.style.overflow = '';
    };

    // Minimal sendMessage that echoes back (you can replace with backend call)
    window.sendMessage = async function () {
      const input = document.getElementById('aiChatInput');
      const container = document.getElementById('aiChatMessages');
      if (!input || !container) return;
      const txt = input.value.trim();
      if (!txt) return;
      // show user message
      const userDiv = document.createElement('div');
      userDiv.textContent = 'You: ' + txt;
      userDiv.style.textAlign = 'right';
      container.appendChild(userDiv);
      input.value = '';
      // fake AI reply (placeholder)
      const aiDiv = document.createElement('div');
      aiDiv.textContent = 'AI: Thinking...';
      container.appendChild(aiDiv);
      container.scrollTop = container.scrollHeight;
      // simulate delay then replace
      setTimeout(() => {
        aiDiv.textContent = 'AI: (This is a demo reply. Connect to your ai_chat_backend.php to enable real responses.)';
      }, 800);
    };

    /* ---------------- Calculator code (fixed) ---------------- */
    function resetCalculator() {
      try {
        const form = document.getElementById('calcForm');
        if (!form) return;
        form.reset();
        // reset derived results
        document.getElementById('costResult').textContent = '₱0';
        document.getElementById('yieldResult').textContent = '0 kg';
        document.getElementById('incomeResult').textContent = '₱0.00';
      } catch (e) {}
    }

    window.computeAgriCalc = function () {
      // get numeric inputs carefully and treat NaN as 0
      const deploymentRaw = document.getElementById('deploymentType') ? document.getElementById('deploymentType').value : '';
      const cubeCountRaw = document.getElementById('cubeCount') ? document.getElementById('cubeCount').value : '0';
      const yieldRaw = document.getElementById('yield') ? document.getElementById('yield').value : '0';
      const priceRaw = document.getElementById('price') ? document.getElementById('price').value : '0';
      const improveRaw = document.getElementById('improvement') ? document.getElementById('improvement').value : '0';

      // convert to numbers robustly
      const deployment = Number(deploymentRaw) || 0;
      const cubeCount = Number(cubeCountRaw) || 0;
      const cubes = cubeCount * 2200; // fixed cost per cube
      // sensors: sum numeric values of checked boxes
      let sensorsTotal = 0;
      document.querySelectorAll('.sensor').forEach(function (el) {
        if (el.checked) {
          const v = Number(el.value) || 0;
          sensorsTotal += v;
        }
      });

      const yieldKg = Number(yieldRaw) || 0;
      const price = Number(priceRaw) || 0;
      const improve = Number(improveRaw) || 0;

      // compute results
      const totalCost = deployment + cubes + sensorsTotal;
      const yieldGain = yieldKg * (improve / 100);
      const incomeGain = yieldGain * price;

      // display safely
      const costEl = document.getElementById('costResult');
      const yieldEl = document.getElementById('yieldResult');
      const incomeEl = document.getElementById('incomeResult');

      if (costEl) costEl.textContent = '₱' + totalCost.toLocaleString();
      if (yieldEl) yieldEl.textContent = yieldGain.toFixed(2) + ' kg';
      if (incomeEl) incomeEl.textContent = '₱' + incomeGain.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      // return an object for potential testing
      return { totalCost, yieldGain, incomeGain };
    };

    // Expose compute for manual calling (useful for debugging)
    window.computeAgriCalc = window.computeAgriCalc;

    // Prevent accidental form submit reload
    document.addEventListener('submit', function (e) {
      if (e.target && e.target.id === 'calcForm') e.preventDefault();
    });

    // Listen for hash change (browser back/forward)
    window.addEventListener('hashchange', function () {
      const newHash = (location.hash || '').replace('#','') || 'sensors';
      switchSection(newHash, false);
    });

  })();
  </script>
  <!-- ---------- End Scripts ---------- -->
</body>
</html>