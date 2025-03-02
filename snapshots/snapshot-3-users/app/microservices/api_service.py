import os
import sys
import re
import time
import pytz
import glob
from datetime import datetime
from bson.json_util import dumps
from bson.objectid import ObjectId

# Add parent folder to system path
app_config_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "app.APP_ABBREV.config.json")
script_dir = os.path.dirname(os.path.abspath(__file__))
app_dir = os.path.join(script_dir, "..")
sys.path.append(app_dir)

from utils.datetimes import get_current_time_hr, get_one_month_later_hr;
from utils.server.jsonwebtoken import base64_url_encode, base64_url_decode, generate_jwt, decode_jwt

# print("\n******** vid: app_dir ********\n")
# print(app_dir)

import threading
import queue
sys.path.append('/Users/wengffung/.local/share/virtualenvs/app-A39rOKjm/lib/python3.8/site-packages')

# Increase the traceback limit
sys.tracebacklimit = 100  # You can set this to any integer value

from flask import Flask, request, jsonify, Response
from flask_cors import cross_origin, CORS
import json
import asyncio
from runtime.run_server import runServer
from bson import ObjectId
import requests

from pymongo import MongoClient, ReturnDocument

from service.openai_rewrite import openai_rewrite
from utils.server.credits import do_check_have_credit, do_hypothesize_newest_case_info_to_start, do_decrement_credit_and_insert_bare_new_case_to_start
from utils.server.credits import do_hypothesize_newest_case_info_that_will_be_copied_over
from utils.server.credits import do_decrement_credit_and_insert_bare_new_case_that_will_be_copied_over, insert_bare_new_case_that_will_be_copied_over, do_clone_over_case
from utils.server.credits import check_ok_to_write_mux

from dotenv import load_dotenv
import os
load_dotenv(override=True)
env_guest_mode = int(os.getenv('GUEST_MODE', 0))
SECRET_KEY_JWT = os.getenv('JWT_SECRET', "")

# print("******* python env_guest_mode", env_guest_mode)
# print("******* python env_guest_mode type", type(env_guest_mode))

from utils.server.db import get_db


video_generator_dir = os.path.join(app_dir, "./slideshow-engine")
sys.path.append(video_generator_dir)
from process import process # if says could not resolve in vs code, ignore that. it doesn't realize your sys path appended

def convert_timezone_to_unix(mux_created, location_time_zone):
    if mux_created:
        # Define the PST timezone
        pst = pytz.timezone(location_time_zone)
        
        # Parse the created timestamp
        # Assuming created is in the format 'YYYY-MM-DD HH:MM:SS'
        dt_pst = datetime.strptime(mux_created, '%Y-%m-%d %H:%M:%S')
        
        # Localize the datetime to PST
        dt_pst = pst.localize(dt_pst)
        
        # Convert to UTC
        dt_utc = dt_pst.astimezone(pytz.utc)
        
        # Convert to Unix time
        unix_time = int(dt_utc.timestamp())
        
        return unix_time
    return None

def delete_files_with_userid(user_id):
    # Construct the pattern to match files
    pattern = os.path.join(app_dir, "users", f"*{user_id}*")
    
    # Find all files matching the pattern
    files_to_delete = glob.glob(pattern)

    logger = ""
    
    # Iterate and delete each file
    for file_path in files_to_delete:
        try:
            os.remove(file_path)
            logger+=(f"Deleted: {file_path}")
        except Exception as e:
            logger+=(f"Error deleting {file_path}: {e}")

    return logger


class StripApiMiddleware:
    def __init__(self, app):
        self.app = app

    def __call__(self, environ, start_response):
        # Strip "/api" from the request path if it exists
        if environ['PATH_INFO'].startswith('/api'):
            environ['PATH_INFO'] = environ['PATH_INFO'][4:]  # Remove "/api"
        return self.app(environ, start_response)

def generate_jwt_by_api(user_id, expiration_days=30):
    expiration = int(time.time()) + expiration_days * 24 * 60 * 60  # X days from now
    # expiration = int(time.time()) + 3 # 3 seconds from now
    payload = {"user_id": user_id, "exp": expiration}

    # Generating
    token = generate_jwt(payload, SECRET_KEY_JWT)
    return token
    
def validate_jwt_by_api(jwt=""):
    decoded = decode_jwt(jwt, SECRET_KEY_JWT)
    return decoded    

def create_app():
    app = Flask(__name__)
    app.wsgi_app = StripApiMiddleware(app.wsgi_app)
    CORS(app, resources={r"/*": {"origins": "*"}})

    # Read app_config_path for "app_abbrev" value
    appId = ""
    with open(app_config_path, 'r') as f:
        config = json.load(f)
        appId = config['app_abbrev']

    print("appId", appId)
    db = get_db()

    # region utils

    # Utils that require db
    def updateContentWith_JobId(user_id, case_id, jobId):

        if not user_id or not case_id:
            return jsonify({
                "error": 1,
                "error_desc": "Missing required parameters: uid (user id) and c (case id)"
            }), 400
        
        contents = db["content"]
        content = contents.find_one_and_update({
            "userId": user_id,
            "appId": appId,
            "caseId": case_id
        }, {
            "$set": {
                "jobId": jobId
            }
        })

    # endregion utils


    # region auth and user

    # Quick test at / when migrating
    # http://127.0.0.1 :5001/test-generate-token
    @app.route("/test-generate-token", methods=["GET"])
    def testGenerateJWTokenByApi():
        token = generate_jwt_by_api(user_id=123, expiration_days=30)
        return jsonify({"message": "Generated token: " + token}), 201
    
    @app.route("/test-validate-token", methods=["GET"])
    def testValidateJWTokenByApi():
        jwt = "eyJhbGciOiAiSFMyNTYiLCAidHlwIjogIkpXVCJ9.eyJ1c2VyX2lkIjogMTIzLCAiZXhwIjogMTczNTI3MzgxNH0.-tszvJ8pKQTT04jCl3VuZkjWHhIuX2ZYfsXpMqGghA8"
        decoded = validate_jwt_by_api(jwt)
        if "error" in decoded:
            return jsonify({"error": 1, "message": decoded["error"]}), 401
        else:
            return jsonify({"error": 0, "payload": decoded}), 201


    # Quick test at / when migrating
    # http://127.0.0.1:5001/
    @app.route("/", methods=["GET"])
    def test():
        return jsonify({"error": "Server loaded but your endpoint is incorrect"}), 400


    # GET http://127.0.0.1:5001/validate/credits?userId=__&appId=APP_ABBREV
    # Show credits available
    @app.route("/validate/credits", methods=["GET"])
    def checkHaveCredits():
        user_id = request.args.get('userId')
        app_id = request.args.get('appId')
        result = do_check_have_credit(db, user_id, app_id)

        return jsonify(result)


    # http://127.0.0.1:5001/cases
    @app.route("/cases", methods=["GET"])
    def getCaseInfoToContinueEditing():
        user_id = request.args.get('uid')
        app_id = appId
        case_id = request.args.get('c')
        case_id = int(case_id)

        # print("********** user_id", user_id)
        # print("********** case_id", case_id)

        if not user_id or not case_id:
            return jsonify({"error": 1, "error_desc": "Missing required parameters: uid (user id) and c (case id)"}), 400
        
        contents = db["content"]
        content = contents.find_one({"userId": user_id, "caseId": case_id})
        if not content:
            return jsonify({"error": 1, "error_desc": "Case not found"}), 400
        
        # Convert using bson's json_util
        content_json = json.loads(dumps(content))
        return jsonify({"error": 0, "caseObject": content_json}), 201

    # http://127.0.0.1:5001/cases
    @app.route("/cases/", methods=["POST"])
    def initCase():
        if request.is_json:
            data = request.get_json()
            USER_ID = data.get('userId')
            case_info = do_hypothesize_newest_case_info_to_start(db, user_id=USER_ID, app_id=appId)

            # Increment at video generation instead
            new_case_info = insert_bare_new_case_that_will_be_copied_over(db, case_info=case_info, user_id=USER_ID, app_id=appId)
            # new_case_info = do_decrement_credit_and_insert_bare_new_case_to_start(db, case_info=case_info, user_id=USER_ID, app_id=appId)

            print(new_case_info)
            return jsonify(new_case_info), 201
        else:
            return jsonify({"error": "JSON not accepted at creating new case to start"}), 400


    # User deletes video
    # GET http://127.0.0.1:5001/profile/cases
    @app.route('/profile/cases', methods=['DELETE'])
    def deleteCase(): 
        if request.is_json:
            data = request.get_json()
            caseId = data.get('caseId', -1)
            userId = data.get('userId', -1)

        if caseId>=0:
            contents = db["content"]
            
            try:
                content = contents.find_one(
                    { "appId": appId, "userId": userId, "caseId": caseId}
                )
                # print("*********caseId", int(caseId))
                # print("*********appId", appId)
                # print("*********userId", userId)
                # print("*********contents", contents)
                # print("*********content", content)
                if("content_is" not in content):
                    return jsonify({"error": 1, "message": "ERROR - Nothing to delete. Case is not completely finished. 'content_is' not found."}), 500
                if("files" not in content["content_is"]):
                    return jsonify({"error": 1, "message": "ERROR - Nothing to delete. Case is not completely finished. 'files' not found in 'content_is"}), 500

                files = content["content_is"]["files"]
                # Map files to get just the path values
                files = list(map(lambda f: f["path"] if isinstance(f, dict) else f, files))
                # Remove "../" prefix from file paths if present
                files = [f[3:] if f.startswith("../") else f for f in files]
                
                finalVideo = content["content_is"].get("finalVideo", "")

                # API microservice must be started from app root and not inside microservices/ folder because users/... is relative to the app root
                # Delete the case from the database
                contents.delete_one({ "appId": appId, "userId": userId, "caseId": caseId})

                job_id = content.get("jobId", "")

                # Delete the job from the databas
                if job_id is not "":
                    jobs = db["jobs"]
                    jobs.delete_one({ "_id": ObjectId(job_id)})

                # Delete the files from the server
                files_to_delete = files + [finalVideo]
                print("files_to_delete", files_to_delete)

                errors = ""
                for file_path in files_to_delete:
                    if file_path:
                        full_file_path = os.path.join(app_dir, file_path)
                        try:
                            os.remove(full_file_path)
                        except FileNotFoundError:
                            errors += f"File not found: {file_path}\n"
                        except Exception as e:
                            errors += f"Error deleting file {file_path}: {e}\n"


                return jsonify({"message": "Case and associated files deleted", "error":errors}), 201

            except Exception as e:
                return jsonify({"error": 1, "message": "ERROR - Problem deleting case: " + e}), 500
            return jsonify({"message": "Voice deleted"}), 201
        return jsonify({"error":1, "message": "ERROR - API Call Invalid. Missing caseId"}), 40

    # App gets their account
    # GET http://127.0.0.1:5001/profile
    @app.route("/profile/<userId>", methods=['GET'])
    def getProfile(userId):

        if userId:
            contents = db["content"]
            users = db["users"]
            user_voices = db["user_voices"]
            user_memberships = db["user_membership"]
            
            try:
                user = users.find_one(
                    {"_id": ObjectId(userId)}
                )

                if user:
                    return jsonify({
                        "error":0,
                        "email": user.get('login', 0),
                        "full_name": user.get('full_name', 0),
                        "newsletter": user.get('newsletter', "N"),                
                        "advanced_mode": user.get('advanced_mode', 0)
                    }), 201
                else:
                    return jsonify({"error": 1, "message": "ERROR - User not found"}), 400

            except Exception as e:
                return jsonify({"error": 1, "message": "ERROR - Problem getting user profile data: " + e}), 500
        return jsonify({"error":1, "message": "ERROR - API Call Invalid. Missing userId"}), 400


    # User updates their account
    # PUT http://127.0.0.1:5001/profile
    @app.route("/profile/<userId>", methods=['PUT'])
    def updateProfile(userId):
        if userId:
            if request.is_json:
                data = request.get_json()
                email = data.get('email',"")
                full_name = data.get('full_name',"")
                advanced_mode = data.get('advanced_mode', 0)

                try:

                    users = db["users"]
                    user = users.find_one_and_update(
                        {"_id": ObjectId(userId)},
                        {
                            "$set": {
                                "email": email,
                                "full_name": full_name,
                                "advanced_mode": advanced_mode
                            }
                        },
                    )
                    return jsonify({"error": 0, "message": "Success updating user's information"}), 200
                
                except Exception as e:
                    return jsonify({"error": 1, "message": "ERROR - Problem updating user's data: " + e}), 500

            else:
                return jsonify({"message": "Missing email and full_name payload to update user information", "error":1}), 400
        else:
            return jsonify({"message": "Missing userId", "error":1}), 400


    # User updates their account's profile pic
    # PUT http://127.0.0.1:5001/profile/<userId>/profile-pic
    @app.route("/profile/<userId>/profile-pic", methods=['PUT'])
    def updateProfilePic(userId):
        if userId:
            if request.is_json:
                data = request.get_json()
                profile_pic = data.get('profile_pic',"")


                try:

                    users = db["users"]
                    user = users.find_one_and_update(
                        {"_id": ObjectId(userId)},
                        {
                            "$set": {
                                "profile_pic": profile_pic
                            }
                        },
                    )
                    return jsonify({"error": 0, "message": "Success updating user's profile picture"}), 200
                
                except Exception as e:
                    return jsonify({"error": 1, "message": "ERROR - Problem updating user's data: " + e}), 500

            else:
                return jsonify({"message": "Missing profile_pic payload to update user profile picture", "error":1}), 400
        else:
            return jsonify({"message": "Missing userId", "error":1}), 400

    # User deletes their profile picture
    # DELETE http://127.0.0.1:5001/profile/profile-picture
    @app.route('/profile/profile-picture', methods=['DELETE'])
    def deleteProfilePicture():
        if request.is_json:
            data = request.get_json()
            userId = data.get('userId', -1)

            if userId:
                users = db["users"]
                try:
                    users.find_one_and_update(
                        {"_id": ObjectId(userId)},
                        {"$unset": {"profile_pic": ""}}
                    )
                    return jsonify({"error": 0, "message": "Successfully deleted user's profile picture"}), 200
                except Exception as e:
                    return jsonify({"error": 1, "message": "ERROR - Problem deleting user's profile picture: " + e}), 500
            else:
                return jsonify({"message": "Missing userId", "error": 1}), 400
        else:
            return jsonify({"message": "Request must be JSON", "error": 1}), 400


    # User deletes their account
    # DELETE http://127.0.0.1:5001/profile
    @app.route('/profile', methods=['DELETE'])
    def deleteProfile(): 
        if request.is_json:
            data = request.get_json()
            userId = data.get('userId', -1)

        if userId:
            contents = db["content"]
            users = db["users"]
            jobs = db["jobs"]
            user_voices = db["user_voices"]
            user_memberships = db["user_membership"]
            
            
            try:

                jobs_result = jobs.delete_many(
                    {"userId": userId}
                )

                user_voice_result = user_voices.delete_many(
                    {"userId": userId}
                )

                content_result = contents.delete_many(
                    {"userId": userId}
                )

                user_membership_result = user_memberships.delete_many(
                    {"userId": userId}
                )

                user_result = users.delete_many(
                    {"_id": ObjectId(userId)}
                )

                errors = delete_files_with_userid(userId)
                if len(errors)==0:
                    errors = 0
                
                return jsonify({"message": "User account, membership, user_voices, and cases deleted", "error":errors}), 201

            except Exception as e:
                return jsonify({"error": 1, "message": "ERROR - Problem deleting user and user data: " + e}), 500
        return jsonify({"error":1, "message": "ERROR - API Call Invalid. Missing userId"}), 400


        userId = request.args.get('userId')
        app_id = request.args.get('appId')
        umems = db['user_membership']
        umem = umems.find_one({"userId": userId, "appId": app_id})
        if umem:
            if("creditsAddons" in umem and "voice-training" in umem["creditsAddons"]):
                print({"error": 0, "authorized_for_training_voice": 1})
                return jsonify({"error": 0, "authorized_for_training_voice": 1}), 201
            else:
                print({"error": 0, "authorized_for_training_voice": 0})
                return jsonify({"error": 0, "authorized_for_training_voice": 0}), 201
        print({"error": 1, "error_desc": "User membership not found from userId and appId", "authorized_for_training_voice": 0})
        return jsonify({"error": 1, "error_desc": "User membership not found from userId and appId", "authorized_for_training_voice": 0}), 400

    # Hidden API call in PHP
    # GET http://127.0.0.1:5001/profile/credits/access-page
    @app.route('/profile/credits/access-page', methods=['GET'])
    def accessPage():
        userId = request.args.get('userId')

        # Placeholder
        data = {
            "totalCredits": 0,
            "prevVideos": [],
            "resumableVideos": []
        }

        # Fetch user membership data
        umem_collection = db['user_membership']
        umem = umem_collection.find_one({"userId": userId, "appId": appId})
        if umem:
            data['totalCredits'] = umem.get('creditsAvailable', 0)
            data['totalCredits'] += umem.get('creditsTimesOne', 0)

        # Fetch content data
        content_collection = db['content']
        contents = content_collection.find({"userId": userId, "appId": appId})

        for content in contents:
            if content.get("content_is") and content["content_is"].get("finalVideo"):
                mux_created = content["content_is"].get("created", "")
                unix_time = ""
                if(mux_created):
                    unix_time = convert_timezone_to_unix(mux_created, 'America/Los_Angeles')


                data['prevVideos'].append({
                    "caseId": content.get("caseId", ""),
                    "finalVideo": content["content_is"].get("finalVideo", ""),
                    "createdUnixTime": unix_time
                })

            if not content.get("content_is") or (content.get("content_is") and not content["content_is"].get("finalVideo")):
                data['resumableVideos'].append({
                    "caseId": content.get("caseId", ""),
                    "finalVideo": ""
                })

        print(data)
        return jsonify(data)

    # App gets user's credit information
    # GET http://127.0.0.1:5001/profile/credits
    @app.route('/profile/credits', methods=['GET'])
    def getCredits():
        user_id = request.args.get('userId')
        app_id = request.args.get('appId')

        if not user_id or not app_id:
            return jsonify({"error": "Missing userId or appId"}), 400

        user_membership_collection = db["user_membership"]
        user_membership = user_membership_collection.find_one({"userId": user_id, "appId": app_id})
        
        if not user_membership:
            return jsonify({"error": "User membership not found"}), 404
        
        a = user_membership.get('creditsAvailable', 0)
        b = user_membership.get('creditsTimesOne', 0)
        c = a + b
        
        return jsonify({"credits": c}), 200


    # http://127.0.0.1:5001/auth/login
    @app.route("/auth/login", methods=["POST"])
    def authLogin():
        if request.is_json:
            requestBody = request.get_json()

            # Db collection users
            users = db["users"]
            
            inputtedLogin = requestBody.get("login")
            inputtedPassword = requestBody.get("password")
            user = users.find_one({
                'login': re.compile('^' + re.escape(inputtedLogin) + '$', re.IGNORECASE),
                'password': inputtedPassword
            })

            if user:
                userId = str(user["_id"])
                jwt = generate_jwt_by_api(user_id=userId, expiration_days=30)
                result = users.update_one(
                    {"_id": ObjectId(userId)},  # Match by ID
                    {"$set": {"jwt": jwt}}      # Add or update the "jwt" key
                )

                return (
                    jsonify(
                        {
                            "message": "authorized",
                            "email": user["login"],
                            "full_name": user.get("full_name", ""),
                            "userId": userId,
                            "appId": user.get("appId", ""),
                            "caseId": -1, # -1 because no case selected or started yet
                            "profile_pic": user.get("profile_pic", ""),
                            "advanced_mode": user.get("advanced_mode", 0),
                            "jwt": jwt
                        }
                    ),
                    201,
                )
            else:
                return jsonify({"message": "unauthorized"}), 401
        else:
            return jsonify({"error": "JSON not accepted"}), 400


    # http://127.0.0.1:5001/auth/login
    @app.route("/auth/login/jwt", methods=["POST"])
    def authLoginWithJWT():
        if request.is_json:
            requestBody = request.get_json()

            # Db collection users
            users = db["users"]
            
            # inputtedLogin = requestBody.get("login")
            # inputtedPassword = requestBody.get("password")
            # user = users.find_one({
            #     'login': re.compile('^' + re.escape(inputtedLogin) + '$', re.IGNORECASE),
            #     'password': inputtedPassword
            # })

            jwt = requestBody.get("jwt")
            user = users.find_one({
                'jwt': jwt,
            })

            if user:
                userId = str(user["_id"])

                return (
                    jsonify(
                        {
                            # "email": data.get("login"),
                            # "voiceId": user.get("voiceId", ""), 
                            "message": "authorized",
                            "email": user["login"],
                            "full_name": user.get("full_name", ""),
                            "userId": userId,
                            "appId": user.get("appId", ""),
                            "caseId": -1, # -1 because no case selected or started yet
                            "profile_pic": user.get("profile_pic", ""),
                            "advanced_mode": user.get("advanced_mode", 0),
                            "jwt": jwt
                        }
                    ),
                    201,
                )
            else:
                return jsonify({"message": "unauthorized"}), 401
        else:
            return jsonify({"error": "JSON not accepted"}), 400

    # http://127.0.0.1:5001/auth/signup
    @app.route("/auth/signup", methods=["POST"])
    def authSignup():
        if request.is_json:
            users = db["users"]
            data = request.get_json()
            if "full_name" not in data:
                data["full_name"] = ""
            if "newsletter" not in data:
                data["newsletter"] = ""

            if ("guest_mode" in data and data["guest_mode"]==1) and (env_guest_mode and env_guest_mode==0):
                return jsonify({"error": "Please sign up regularly with a password. Try Now / Guest is removed."}), 400

            # Check if user already exists case-insensitively
            inputtedLogin = data.get("login")
            
            user_existence = users.find_one({
                'login': re.compile('^' + re.escape(inputtedLogin) + '$', re.IGNORECASE)
            })
            if user_existence:
                return jsonify({"error": "Email already exists"}), 400

            # Insert user
            newUser = users.insert_one(
                {
                    "login": inputtedLogin,
                    "password": data.get("password"),
                    "appId": appId,
                    "full_name": data.get("full_name"),
                    "newsletter": data.get("newsletter"),
                    "createdOn": get_current_time_hr()
                }
            )
            userId = str(newUser.inserted_id)

            jwt = generate_jwt_by_api(user_id=userId, expiration_days=30)
            result = users.update_one(
                {"_id": ObjectId(userId)},  # Match by ID
                {"$set": {"jwt": jwt}}      # Add or update the "jwt" key
            )
            
            # Select the 'user_membership' collection
            creditsAddons = []
            creditsAvailable = 3

            if ("guest_mode" in data and data["guest_mode"]==1) and (env_guest_mode and env_guest_mode==1):
                creditsAddons.append("voice-training")
                creditsAvailable = 3
            # guest email address and guest password has been passed into authSignup

            today = get_current_time_hr()

            umems = db["user_membership"]
            umems.insert_one(
                {
                    "userId": userId,
                    "appId": appId,
                    "tierLevel": 0,
                    "billId": 0,
                    "billStarted": 0,
                    "billLate": 0,
                    "billNext": 0,
                    "creditsAddons": creditsAddons,
                    "creditsAvailable": creditsAvailable,
                    "creditsTimesOne": 0,
                    "creditsMonthlyResetNumber": 1,
                    "createdOn": today,
                    "trialStarted": today
                }
            )

            return (
                jsonify(
                    {
                        "message": "authorized",
                        "email": data.get("login"),
                        "full_name": data.get("full_name"),
                        "userId": userId,
                        "appId": appId,
                        "caseId": -1, # -1 because no case created yet
                        "jwt": jwt
                    }
                ),
                201,
            )
        else:
            return jsonify({"error": "JSON not accepted"}), 400

    # endregion auth and user


    # region media

    # http://127.0.0.1:5001/media/interim/prompt/rewrite
    @app.route("/media/interim/prompt/rewrite", methods=["POST"])
    def rewritePrompt():
        if request.is_json:
            data = request.get_json()
            text = data.get("text", "")
            user_id = data.get("userId", "")
            app_id = data.get("appId", "")
            case_id = data.get("caseId", "")

            try:
                rewrittenText = ""
                rewrittenText = openai_rewrite(text)
                return jsonify({"rewrittenText": rewrittenText}), 201
            except Exception as e:
                print("Error: ", e)
                return jsonify({"error": 1, "error_desc": "Error rewriting text with AI because " + str(e)}), 400     


            
        else:
            return jsonify({"error": "JSON not accepted"}), 400

    # http://127.0.0.1:5001/media/interim/prompt
    @app.route("/media/interim/prompt", methods=["POST"])
    def finishPrompt():
        if request.is_json:
            data = request.get_json()

            def getUserId():
                return data.get("userId", "err")

            def getCaseId():
                return data.get("caseId", "err")

            contents = db["content"]
            content = contents.find_one_and_update(
                {"userId": getUserId(), "caseId": getCaseId()},
                {
                    "$set": {
                        "content_is": {
                            "aiPrompt": data.get("aiPrompt", "")
                        }
                    }
                },
            )

            if content:
                return jsonify({"message": "success"}), 201
            else:
                return (
                    jsonify(
                        {
                            "error": "User ID not found so unable to update to user's document"
                        }
                    ),
                    400,
                )

        else:
            return jsonify({"error": "Text not sent to Flask server"}), 400


    # http://127.0.0.1:5001/media/interim/files
    @app.route("/media/interim/files", methods=["POST"])
    def updateDbModelWithFilesLabels():
        if request.is_json:
            data = request.get_json()
            userId = data.get("userId", "")
            appId = data.get("appId", "")
            caseId = int(data.get("caseId", -1))
            files = data.get("files", "") # [{url, caption}]
            labels = data.get("labels", {}) # [{url, caption}]

            print(data)

            contents = db["content"]
            content = contents.find_one_and_update(
                {"userId": userId, "appId": appId, "caseId": caseId}, 
                {
                    "$set": {
                        "content_is.files": files,
                        "content_is.labels": labels
                    }
                 },
                return_document=ReturnDocument.AFTER
            )
            if content:
                return jsonify({"message": "success"}), 201
            else:
                return (
                    jsonify(
                        {
                            "error": "Case not found so unable to update to user's document"
                        }
                    ),
                    400,
                )

        else:
            return jsonify({"error": "Text not sent to Flask server"}), 400


    # endregion media


    # region jobs and videos


    def createVideo(ids, data={}):

        userId = ids['userId']
        appId = ids['appId']
        caseId = ids['caseId']

        contents = db["content"]

        content = contents.find_one({"userId": userId, "appId": appId, "caseId": caseId})
        if content is None:
            return jsonify({"error": 1, "error_desc": "Case not found"}), 400
        
        content_is = content["content_is"]
        umems = db["user_membership"]

        # Moved to check_ok_to_write_mux to keep more atomic
        created = None
        if "created" in content["content_is"]:
            created = content["content_is"]["created"]

        precheck = check_ok_to_write_mux(db, user_id=userId, app_id=appId, case_id=caseId)
        if(precheck==False):
            return jsonify({"error": 1, "error_desc": precheck["error_desc"]}), 400
                            
        users = db["users"]
        user = users.find_one({"_id": ObjectId(userId), "appId": appId})
        if user is None:
            return jsonify({"error": 1, "error_desc": "User not found"}), 400


        process_args = {
            "filenameAttachIds":f"a{appId}-c{caseId}-{userId}",
            "files": [os.path.join(app_dir, item.get('url', '')) for item in content_is.get("files", [])],
            "callbacks": [
                {
                    "dynamic_module_path": "../",
                    "dynamic_module_name": "utils.slideshow_callbacks.report_from_slideshow",
                    "method_name": "update_mongo",
                    "inputs": [
                        appId,
                        caseId,
                        userId
                    ],
                    "inputVars": [
                        "FINAL_VIDEO"
                    ]
                }
            ],
            "app_dir": app_dir
        } # a with values
        

        try:
            vidCreatedInfo = process(**process_args) # process
            if("error" in vidCreatedInfo and vidCreatedInfo["error"] == 1):
                print("Error: ", vidCreatedInfo)
                return jsonify(vidCreatedInfo), 400
            print("FETCH: Direct process() Success: ", vidCreatedInfo)


            if created is None:
                contents.find_one_and_update(
                    {"userId": userId, "appId": appId, "caseId":caseId}, 
                    {"$set": {"content_is.created": get_current_time_hr()}},
                    return_document=ReturnDocument.AFTER
                )
        
            # Decrement credit on creating video
            umems = db["user_membership"]
            umem_able = umems.find_one({"userId": userId, "appId": appId})
            if(umem_able):
                if("creditsTimesOne" in umem_able and umem_able["creditsTimesOne"]>0):
                    umem = umems.find_one_and_update({"userId": userId, "appId": appId}, {'$inc': {'creditsTimesOne': -1}})
                else:
                    umem = umems.find_one_and_update({"userId": userId, "appId": appId}, {'$inc': {'creditsAvailable': -1}})
            else:
                return jsonify({"error": 1, "error_desc": "User membership not found"}), 400



            return jsonify(vidCreatedInfo), 201
            # rt.stop()
        except Exception as e:
            print("FETCH: Direct process() Error: ", e)
            return jsonify({"error": 1, "error_desc": "Error in creating video because " + str(e)}), 400


    # POST http://127.0.0.1:5001/jobs
    @app.route("/jobs", methods=["GET"])
    def getJob():
        jobId = request.args.get('jobId')
        job = db["jobs"].find_one({"_id": ObjectId(jobId)})
        if job is None:
            return jsonify({"error": 1, "error_desc": "Job not found"}), 400
        if("data" not in job):
            return jsonify({"error": 1, "error_desc": "Job data not found"}), 400
        

        return jsonify(job["data"]), 200

    # POST http://127.0.0.1:5001/media/video/prepare
    @app.route("/media/video/prepare", methods=["POST"])
    def prepareVideoJob():
        # print("******************* prepareVideoJob()")
        if request.is_json:
            data = request.get_json()
            userId = data.get("userId")
            appId = data.get("appId")
            caseId = int(data.get("caseId"))

            data.pop("userId")
            data.pop("appId")
            data.pop("caseId")

            try:
                jobs = db["jobs"]
                result = jobs.find_one_and_update(
                    {"userId": userId, "appId": appId, "caseId": caseId},
                    {
                        "$set": {
                            "data": data
                        }
                    },
                    upsert=True,
                    return_document=ReturnDocument.AFTER
                )

                if result is None:
                    return jsonify({"error": "Failed to update job"}), 400

                return jsonify({"error": 0, "jobId": str(result["_id"])}), 201
            except Exception as e:
                print("Error updating job:", e)
                return jsonify({"error": 1, "error_desc": str(e)}), 400
    

    # POST http://127.0.0.1:5001/media/video
    @app.route("/media/video", methods=["POST"])
    def createVideo_POST():
        jobId = request.args.get('jobId')
        job = db["jobs"].find_one({"_id": ObjectId(jobId)})
        if job is None:
            return jsonify({"error": 1, "error_desc": "Job not found"}), 400
        
        data = job["data"]
        userId = job.get("userId")
        appId = job.get("appId")
        caseId = job.get("caseId")

        # print(jobId)
        # print(userId)
        # print(appId)
        updateContentWith_JobId(userId, caseId, jobId)

        return createVideo(ids={"userId": userId, "appId": appId, "caseId": caseId}, data=data)

    # endregion jobs and videos

    # region analytics

    @app.route("/analytics/webpages/visited", methods=["POST"])
    def updateWebpageVisitReport():
        if request.is_json:
            data = request.get_json()
            user_id = data.get("userId", "err")
            new_visit = data.get("newVisit")

            # Validate user_id
            if user_id == "err":
                return jsonify({"error": 1, "message": "No user_id provided to save analytics to."}), 400

            # Validate new_visit
            if not new_visit:
                return jsonify({"error": 1, "message": "No new visit data provided."}), 400
            
            try:
                # Extract timestamp and visit details from new_visit
                parts = new_visit.split(",", 1)  # Split into 2 parts: timestamp and the rest
                timestamp_str = parts[0]
                visit_data = parts[1] if len(parts) > 1 else ""  # Handle case where there's no detail
                
                # Validate and parse timestamp
                datetime.strptime(timestamp_str, "%Y-%m-%d %H:%M:%S")
            except (ValueError, IndexError) as e:
                return jsonify({"error": 1, "message": "Invalid newVisit format. Expected CSV: timestamp,detail1,detail2"}), 400

            users = db["users"]

            try:
                result = {}
                # If analytics exceed this number, shift head entry and that is the oldest session record being dropped
                limit_recorded_sessions = 20
                
                user = users.find_one({"_id": ObjectId(user_id)})

                if user and "analytics_page_visits" in user:
                    # Get existing keys for this specific update_key
                    prev_recorded_sessions = user.get("analytics_page_visits", {})
                    
                    # If number of keys exceeds `limit_recorded_sessions`, remove the oldest key
                    if len(prev_recorded_sessions) >= limit_recorded_sessions:
                        # Sort keys and get the oldest (first) key to remove
                        sorted_keys = sorted(prev_recorded_sessions.keys())
                        oldest_key = sorted_keys[0]
                        del prev_recorded_sessions[oldest_key]
                    
                    # Add the new visit_data
                    prev_recorded_sessions[timestamp_str] = visit_data

                    # Update the document
                    result = users.update_one(
                        {"_id": ObjectId(user_id)},
                        {"$set": {"analytics_page_visits": prev_recorded_sessions}},
                        upsert=False
                    )
                else: # Still have space for more analytics so no need to shift head of timestamp-hashed map object.
                    update_key = f"analytics_page_visits.{timestamp_str}"

                    result = users.update_one(
                        {"_id": ObjectId(user_id)},
                        {
                            "$set": {update_key: visit_data}  # Add or update the key-value pair
                        },
                        upsert=False
                    )
                
                if result.matched_count > 0:
                    return jsonify({"error": 0, "message": f"Analytics updated successfully for user_id {user_id}"}), 200
                else:
                    return jsonify({"error": 1, "message": "Analytics failed. User ID does not exist. Possibly user has more than one tab opened while deleting profile on another tab."}), 200

            except Exception as e:
                return jsonify({"error": 1, "message": f"Unable to save analytics of visited page because: ${e}"}), 400

    return app

# endregion analytics


if __name__ == "__main__":
    # app.run(debug=True, port=5001)
    # app.run(host='0.0.0.0', port=5001)
    try:
        app = create_app()
        runServer(app) # from runtime.env.py which runs local `app.run(debug=True, port=5001)` or remote https `app.run(ssl_context=ssl_context, host='0.0.0.0', port=5001, debug=True)`
    except NameError:
        print("Remote/local setup not setup right")
