import json, time
from datetime import datetime
from google import genai

API_KEY = "AIzaSyBzNxikoqCxukLCmab80RZRU5peqKOicmE"
MODEL = "gemini-2.0-flash"

HISTORY_PATH = r"C:\xampp\htdocs\AgriSight\data\history.json"
AI_OUTPUT_PATH = r"C:\xampp\htdocs\AgriSight\data\ai_data.json"

client = genai.Client(api_key=API_KEY)

def load_sensor_data(filepath=HISTORY_PATH):
    try:
        with open(filepath, "r") as f:
            return json.load(f)
    except Exception as e:
        print("❌ Failed to load sensor data:", e)
        return []

def summarize_data(data):
    summary = "Recent environmental sensor readings:\n"
    for entry in data:
        summary += (
            f"• {entry['timestamp']} — Temp: {entry['temperature']}°C, "
            f"Humidity: {entry['humidity']}%, Gas: {entry['gas_sensor']}, "
            f"Altitude: {entry.get('altitude', 'N/A')}m\n"
        )
    return summary

def ask_ai(summary):
    prompt = (
        "You are an expert in crop health and environmental monitoring. "
        "Analyze the following sensor data — temperature, humidity, gas levels, and altitude. "
        "Provide insights on the overall environmental conditions and suggest improvements to maintain optimal conditions for healthy crops. "
        "Do not mention specific plant types.\n\n"
        f"{summary}"
    )
    try:
        response = client.models.generate_content(
            model=MODEL,
            contents=prompt
        )
        return response.text.strip()
    except Exception as e:
        return f"❌ Error: {e}"

def save_ai_output(content, filepath=AI_OUTPUT_PATH):
    entry = {
        "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "analysis": content
    }
    try:
        with open(filepath, "w") as f:
            json.dump([entry], f, indent=2)
        print("💾 Analysis saved to ai_data.json\n")
    except Exception as e:
        print("❌ Failed to save AI output:", e)

print("🌱 Smart Crop Health Bot running...\n(Press Ctrl+C to stop)\n")

try:
    while True:
        sensor_data = load_sensor_data()
        if not sensor_data:
            print("⚠️ No sensor data found. Waiting for next refresh...")
        else:
            summary = summarize_data(sensor_data)
            print("📊 Analyzing sensor data...\n")
            analysis = ask_ai(summary)
            print("🤖 AI Analysis:\n" + analysis + "\n")
            save_ai_output(analysis)
        time.sleep(60)
except KeyboardInterrupt:
    print("🛑 Monitoring stopped by user.")