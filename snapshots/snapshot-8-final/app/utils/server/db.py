import os
from dotenv import load_dotenv
import json

# Specify important paths
dotenv_path = os.path.join(os.path.dirname(__file__), '..', '..', '.env')
dotenv_path_local = os.path.join(os.path.dirname(__file__), '..', '..', '.env.local')
app_config_path = os.path.join(os.path.dirname(__file__), '..', '..', 'app.APP_ABBREV.config.json')

# Load the .env file from the specified path
load_dotenv(override=True, dotenv_path=dotenv_path)
load_dotenv(override=True, dotenv_path=dotenv_path_local)

# Load database name from app_config_path
with open(app_config_path) as config_file:
    config = json.load(config_file)
db_name = config['db_name']

# Load database credentials
mongo_user = os.getenv('MONGO_USER')
mongo_password = os.getenv('MONGO_PASSWORD')

# Load server type, eg. development
server_type = os.getenv('SERVER_TYPE')

# print("******* mongo_user********")
# print(mongo_user)

# print("******* mongo_password********")
# print(mongo_password)

from pymongo import MongoClient

# MongoDB connection with authentication
def get_db():
    if(server_type == "APP_SERVER_debian_12"): # remote server 1
        uri = f"mongodb://{mongo_user}:{mongo_password}@localhost:27017/?authSource=admin"
        client = MongoClient(uri)
        client[db_name]
    else: # local development server which doesn't require authentication
        client = MongoClient("mongodb://localhost:27017/")

    db = client[db_name]
    return db
