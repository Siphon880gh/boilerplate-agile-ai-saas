from pymongo import MongoClient
from bson import ObjectId
from utils.datetimes import get_current_time_hr, get_one_month_later_hr;

import sys
import os
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))


# Read app_config_path for "app_abbrev" value
import json
app_config_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "..", "app.APP_ABBREV.config.json")
app_id = ""
with open(app_config_path, 'r') as f:
    config = json.load(f)
    app_id = config['app_abbrev']

# Establish a connection to MongoDB and select the database
from utils.server.db import get_db
db = get_db()

today = get_current_time_hr()
demo_user_id = "01234abcd555555555ef6789"

# Select the 'users' collection and seed the data
users_collection = db['users']
data = {
    "_id": ObjectId(demo_user_id),
    "login": "demo@gmail.com",
    "password": "demo_1",
    "appId": app_id,
    "full_name": "Weng CTO",
    "newsletter": "n",
    "createdOn": today,
    "jwt": ""
}
# Insert super admin (Will get user ID later)
users_collection.delete_many({})
inserted = users_collection.insert_one(data)

# Get the user ID for all other collections from this point on
user_id = str(inserted.inserted_id)

# Select the 'user_membership' collection
# TODO: Might substitute or complement an algo script to determine the user's access to more cases / credits

today = get_current_time_hr()
one_month_later = get_one_month_later_hr() # approximate 1 month as 30 days

creditsAddons = []
creditsAddons.append("voice-training")

users_collection = db['user_membership']
data = {
    "userId": user_id,
    "appId": app_id,
    "tierLevel": "admin",
    "billId": 0,
    "billStarted": 0,
    "billLate": 0,
    "billNext": 0,
    "creditsAddons": creditsAddons,
    "creditsAvailable": 5000,
    "creditsTimesOne": 0,
    "creditsMonthlyResetNumber": 1,
    "createdOn": today,
    "trialStarted": today,
}

users_collection.delete_many({})
users_collection.insert_one(data)

# Select the 'content' collection
content_collection = db['content']
content_collection.delete_many({})
# data = {'userId': user_id, 'appId':app_id, 'caseId':1, 'content_is': {}}
# content_collection.insert_one(data)
data = {
  "userId": demo_user_id,
  "appId": app_id,
  "caseId": 1,
  "content_is": {
    "aiPrompt": "Let's create a slideshow about the history of the company. I will provide articles and some text stats about the company.",
    "files": [],
    "finalVideo": "",
    "created": today
  }
} # data
content_collection.insert_one(data)


print("Data seeded successfully.")