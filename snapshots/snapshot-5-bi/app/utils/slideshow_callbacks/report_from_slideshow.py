import sys
import os

project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
sys.path.append(os.path.join(project_root))
from utils.server.db import get_db
db = get_db()

def update_mongo(inputs, input_vals): 
    app_id, case_id, user_id = inputs
    PT_FINAL_PATH = input_vals[0]

    print("PT_FINAL_PATH @ UTIL report to Mongo: ", PT_FINAL_PATH)

    contents = db["content"]
    report = contents.find_one_and_update({
        "userId": user_id, 
        "appId": app_id, 
        "caseId": case_id
    }, 
    {
        "$set":
        {
            "content_is.finalVideo": PT_FINAL_PATH,
        }
    }, upsert=True)

    print("*******  Slideshow Mongo Callback *******")
    print(report)