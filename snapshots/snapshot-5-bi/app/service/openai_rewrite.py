
import requests
from dotenv import load_dotenv
import os
import json
# Specify the path to the .env file two directories up
dotenv_path = os.path.join(os.path.dirname(__file__), '..', '.env')

# Load the .env file from the specified path
load_dotenv(override=True, dotenv_path=dotenv_path)

api_key = os.getenv('OPENAI_API_KEY')
if not api_key:
    raise ValueError("OpenAI API key not found or empty. Please check your .env file.")


def openai_rewrite(text):

    url = "https://api.openai.com/v1/chat/completions"

    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }

    request_body = {
        "model": "gpt-4o",
        # "model": "gpt-4o-mini",
        "messages": [
            {
                "role": "assistant",
                "content": f"You write prompts for an AI service that creates slideshows. That AI service will be given a pictures, audios, and text documents as context for the slideshow, however those files will not be provided to you. Your job is to receive an AI prompt to create the slideshow, maybe what the slideshow is about, and maybe how to use the files to create the slideshow, and you rewrite the prompt to be more specific and detailed. There is a possibility that the text is NOT a prompt or instructions to create a slideshow, so if you detect the text is not, you will respond with all caps 'ERROR' so that my code can detect the error and respond with an error message. If you are rewriting the user's prompt, avoid using the word 'ERROR' in your response."
            },
            {
                "role": "user",
                "content": f"Rewrite the following prompt or instructions for an AI slideshow creator.\n\nPrompt:{text}!"
            }
        ]
    }

    try:
        response = requests.post(url, headers=headers, json=request_body)
        # print(response.json())
        rprom = response.json()
        if "error" in rprom:
            error_desc = rprom["error"]
            if(type(error_desc) is dict):
                error_desc = json.dumps(error_desc)
            print("ERROR/openai_translate: " + error_desc)
            return {"error":1, "error_desc":"openai_translate: "+error_desc}
        if "choices" not in rprom:
            print('ERROR/openai_translate: OpenAI not responding back with expected data for translation. Should have rprom["choices"][0]["message"]["content"].')
            return {"error":1, "error_desc":'openai_translate: OpenAI not responding back with expected data for translation. Should have rprom["choices"][0]["message"]["content"].', "rprom_is":json.dumps(rprom)}
        output = rprom["choices"][0]["message"]["content"]
        print(output)
        return output
    except Exception as e:
        print(f"Error: {str(e)}")
        return {"error":1, "description": str(e)}
