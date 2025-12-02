from google import genai

client = genai.Client(api_key="AIzaSyBzNxikoqCxukLCmab80RZRU5peqKOicmE")

ai_startup = print("Hi! This is your Agriculcural AI Companion.")

user_input = input("")

while True:
    response = client.models.generate_content(
        model="gemini-2.0-flash", contents=user_input
    )
    print(response.text)