from google import genai

client = genai.Client(api_key="AIzaSyBzNxikoqCxukLCmab80RZRU5peqKOicmE")

response = client.models.generate_content(
    model="gemini-2.0-flash", 
    contents= "You are an intelligent plant health advisor. Analyze temperature, humidity, gas levels, and altitude. Determine if the current conditions are suitable for healthy plant growth and suggest improvements if needed."
)
print(response.text)