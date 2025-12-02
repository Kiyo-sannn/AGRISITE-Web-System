# AGRI-SITE

A real-time sensor data monitoring system with AI-powered interpretation using Google's Generative AI. The system displays sensor data and provides intelligent analysis through a web interface powered by PHP and Python.

---

## Directory Structure

```
AgriSight/
│
├── assets/
│   ├── images/
│   │   └── Logo_1.png
│   └── style.css
│
├── backup/
│
├── bot/
│   └── ai.py
│
├── data/
│   ├── ai_data.json
│   ├── history.json
│   └── raw_data.json
│
├── others/
│
├── pages/
│   ├── agrisite_calc.html
│   ├── analytics.php
│   └── index.php
│
├── scripts/
│   └── script.js
│
└── README.md
```

---

## Requirements

### Server Environment
- [XAMPP](https://www.apachefriends.org/) (Apache server)
- PHP 7.4 or higher

### Python Environment
- Python 3.8 or higher
- `pip` package manager

### Required Python Libraries
```bash
pip install requests
pip install google-generativeai
```

---

## Installation & Setup

### 1. Clone or Download the Project
Place the project folder in your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\AgriSight\
```

### 2. Install Python Dependencies

Open your terminal or command prompt and install required libraries:

```bash
# Install requests library for HTTP communication
pip install requests

# Install Google Generative AI library for AI integration
pip install google-generativeai
```

Verify installation:
```bash
pip show requests
pip show google-generativeai
```

### 3. Configure Google Generative AI

1. Obtain a Google AI API key from [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Set up your API key in `bot/ai.py` (add configuration as needed)

### 4. Start Apache Server

- Open **XAMPP Control Panel**
- Click **Start** next to **Apache**
- Ensure Apache is running (green indicator)

---

## Running the Application

### 1. Start the Web Interface

Open your browser and navigate to:
```
http://localhost/AgriSITE/pages/index.php
```

### 2. Run the AI Interpreter

Open a terminal in the project directory and run:

```bash
cd bot
python ai.py
```

The AI script will:
- Continuously monitor sensor data from `data/raw_data.json`
- Generate intelligent interpretations using Google's Generative AI
- Update `data/ai_data.json` with analysis results
- Log historical data to `data/history.json`

---

## Data Files Overview

| File Name        | Purpose                                           |
|------------------|---------------------------------------------------|
| `raw_data.json`  | Raw sensor readings from hardware devices         |
| `ai_data.json`   | AI-generated interpretations and recommendations  |
| `history.json`   | Timestamped log of sensor data and AI analysis    |

---

## Features

- **Real-time Monitoring**: Live sensor data visualization
- **AI-Powered Analysis**: Intelligent interpretation using Google Generative AI
- **Historical Tracking**: Automated data logging with timestamps
- **Analytics Dashboard**: Data trends and insights visualization
- **Responsive Design**: Mobile-friendly interface

---

## Troubleshooting

### AI Script Not Working
- Verify Google Generative AI library is installed: `pip show google-generativeai`
- Check if your API key is properly configured
- Ensure `raw_data.json` exists and contains valid data

### Web Page Not Loading
- Confirm Apache is running in XAMPP
- Check file paths match the directory structure
- Verify PHP files are in the correct `htdocs` subfolder

### Data Not Updating
- Ensure `ai.py` is running in the background
- Check file permissions for JSON files
- Verify network connectivity for API calls

---

## Technology Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **AI Engine**: Python with Google Generative AI (`google-generativeai`)
- **Server**: Apache (XAMPP)
- **Data Storage**: JSON files

---

## Version History

- **Version 2.1** - July 21, 2024: Initial release
- **Current Version** - December 2, 2024: Added Google AI integration

---

## Notes

- Keep `ai.py` running for continuous AI analysis
- API rate limits may apply depending on your Google AI plan
- Regular backups of JSON data files are recommended
- Monitor `history.json` file size for long-running deployments

---

## Contributors

- Gereuel Brillantes
- Marah Antoinette Esconde
- Ysabel Angela Embile