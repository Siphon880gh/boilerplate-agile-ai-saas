from pymongo import ReturnDocument
from bson import ObjectId
import copy
from datetime import datetime
import math
from utils.datetimes import get_current_time_hr, get_one_month_later_hr;
import os

app_config_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "..", "app.APP_ABBREV.config.json")

# Read app_config_path for "app_abbrev" value
import json

app_id = ""
with open(app_config_path, 'r') as f:
    config = json.load(f)
    app_id = config['app_abbrev']

# Custom serialization function
def serialize_document(doc):
    for key, value in doc.items():
        if isinstance(value, ObjectId):
            doc[key] = str(value)
        elif isinstance(value, datetime.datetime):
            doc[key] = value.isoformat()
        # Add more elif blocks here for other non-serializable types as needed
    return doc

# ----- Utilities for credits.py above this line -----


# ----- Utilities for rest of app below this line -----

# Get whether there is still credits available for more cases from db["user_membership"]
def do_check_have_credit(db, user_id, app_id):
    credits_available = 0
    umems = db["user_membership"]
    umem = umems.find_one({"userId": user_id, "appId": app_id})
    if umem:
        credits_available = umem.get("creditsAvailable", 0)
        credits_available += umem.get("creditsTimesOne", 0)
        print("credits_available", credits_available) # 50

        if credits_available > 0:
            return {"error":0, "credits_available": credits_available}
        else:
            isLateBill = umem.get("billLate")
            nextBillDate = umem.get("billNext")
            creditsMonthlyResetNumber = umem.get("creditsMonthlyResetNumber")
            # Obsoleted. Just have them view the dashboard. This is because there may be a-la-cart or lifetime deals
            # error_desc = f"No more credits available. Next reset to {creditsMonthlyResetNumber} credit(s) is on {nextBillDate}" if isLateBill==0 else f"No more credits available. Bill is overdue. Please visit billing page."
            error_desc = "No more credits available. Visit homepage for more information."
            return {"error":1, "error_desc": error_desc}
    else:
        return {"error":1, "error_desc": "User not found"}
    
# Get whether there is still credits available for more cases from db["user_membership"]
def do_decrement_credit(db, user_id, app_id):
    credits_available = 0
    umems = db["user_membership"]
    umem = umems.find_one({"userId": user_id, "appId": app_id})
    
    if umem:
        if("creditsTimesOne" in umem and umem["creditsTimesOne"]>0):
            stats = umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$inc": {"creditsTimesOne": -1}}, return_document=ReturnDocument.AFTER )
            new_credits_available =  stats["creditsTimesOne"]
            print("Consumed credit of times one credits -1 to: ", new_credits_available)
            return {"error":0, "new_credits_available": new_credits_available}
        elif("creditsAvailable" in umem and umem["creditsAvailable"]>0):
            stats = umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$inc": {"creditsAvailable": -1}}, return_document=ReturnDocument.AFTER )
            new_credits_available =  stats["creditsAvailable"]
            print("Consumed credit of restartable credits -1 to: ", new_credits_available)
            return {"error":0, "new_credits_available": new_credits_available}
        else:
            isLateBill = umem.get("billLate")
            nextBillDate = umem.get("billNext")
            creditsMonthlyResetNumber = umem.get("creditsMonthlyResetNumber")
            # Obsoleted. Just have them view the dashboard. This is because there may be a-la-cart or lifetime deals
            # error_desc = f"No more credits available. Next reset to {creditsMonthlyResetNumber} credit(s) is on {nextBillDate}" if isLateBill==0 else f"No more credits available. Bill is overdue. Please visit billing page."
            error_desc = "No more credits available. Visit homepage for more information."
            return {"error":1, "error_desc": error_desc}
    else:
        return {"error":1, "error_desc": "User not found"}

def do_hypothesize_newest_case_info_to_start(db, user_id, app_id=app_id):
    # print("***********")
    # print("user_id", user_id)
    content = db["content"]
    my_content = content.find({"userId": user_id, "appId": app_id})
    caseIds = []

    if my_content:
        for document in my_content:
            # print("doc", document)
            caseIds.append(document.get("caseId"))
        caseIds.sort()
    # print("my_content", my_content)

    case_count = 0
    new_case_id = -1
    if(len(caseIds)==0):
        print("Case count", 0)
        new_case_id = 1
        print("New case number", new_case_id) # Int
        """
            Formula: No cases allowed
            Case count 0
            New case number 0
        """

        return {"error":0, "user_id":user_id, "app_id":app_id, "caseIds":caseIds, "case_count":case_count, "new_case_id":new_case_id}

    else:
        case_count = len(caseIds)
        new_case_id = caseIds[-1] + 1

        print("Case count", case_count)
        # print("Highest case number", caseIds[-1]) # Int
        print("New case number", new_case_id) # Int

        """
            Eg. 3 cases found
            caseIds [0, 1, 2]
            Case count 3
            New case number 3
        """

        return {"error":0, "user_id":user_id, "app_id":app_id, "caseIds":caseIds, "case_count":case_count, "new_case_id":new_case_id}


def do_decrement_credit_and_insert_bare_new_case_to_start(db, case_info, user_id, app_id):
    credits_available = 0
    umems = db["user_membership"]
    umem = umems.find_one({"userId": user_id, "appId": app_id})
    new_case_id = case_info["new_case_id"]

    if umem:
        if("creditsTimesOne" in umem and umem["creditsTimesOne"]>0):
            stats = umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$inc": {"creditsTimesOne": -1}}, return_document=ReturnDocument.AFTER )
            new_credits_available =  stats["creditsTimesOne"]
            print("Consumed credit of times one credits -1 to: ", new_credits_available)
            content = db["content"]
            data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
            content.insert_one(data)
            print("Case created successfully: ", data)
            return {"error":0, "new_case_id": new_case_id, "new_credits_available": new_credits_available}
        elif("creditsAvailable" in umem and umem["creditsAvailable"]>0):
            stats = umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$inc": {"creditsAvailable": -1}}, return_document=ReturnDocument.AFTER )
            new_credits_available =  stats["creditsAvailable"]
            print("Consumed credit of restartable credits -1 to: ", new_credits_available)
            content = db["content"]
            data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
            content.insert_one(data)
            print("Case created successfully: ", data)
            return {"error":0, "new_case_id": new_case_id, "new_credits_available": new_credits_available}
        else:
            isLateBill = umem.get("billLate")
            nextBillDate = umem.get("billNext")
            creditsMonthlyResetNumber = umem.get("creditsMonthlyResetNumber")
            # Obsoleted. Just have them view the dashboard. This is because there may be a-la-cart or lifetime deals
            # error_desc = f"No more credits available. Next reset to {creditsMonthlyResetNumber} credit(s) is on {nextBillDate}" if isLateBill==0 else f"No more credits available. Bill is overdue. Please visit billing page."
            error_desc = "No more credits available. Visit homepage for more information."
            return {"error":1, "error_desc": error_desc}
    else:
        return {"error":1, "error_desc": "User not found"}
        

    # if umem:
    #     credits_available = umem.get("creditsAvailable")
    #     print("credits_available", credits_available) # 50

    #     if credits_available > 0:
    #         # Create a new case
    #         new_case_id = case_info["new_case_id"]
    #         content = db["content"]
    #         data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
    #         content.insert_one(data)
    #         print("Case created successfully")

    #         # Decrement the credits by 1
    #         new_credits_available = credits_available - 1
    #         umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$set": {"creditsAvailable": new_credits_available}})
    #         print("new_credits_available", new_credits_available) # 49
    #         return {"error":0, "new_case_id": new_case_id, "new_credits_available": new_credits_available}
    #     else:
    # else:
    #     return {"error":1, "error_desc": "User not found"}
    

def check_ok_to_write_mux(db, user_id, app_id, case_id, limit_hour=2):
    content = db["content"]

    # Check if muxPath exists. If does and has length, check last created was outside 1 hour. If it is, then deny
    case = content.find_one({"userId": user_id, "appId": app_id, "caseId": case_id})


    if case is None:
        return {"error":1, "error_desc":"No cases found. Was your account or video deleted before translating?"}

    # If finalVideo exists, then check if created is within the last hour (ie. user re-doing their video within 1 hour is ok)
    if "content_is" in case and "finalVideo" in case["content_is"]:

        if "created" in case["content_is"] and bool(case["content_is"]["created"]):

            print("created", case["content_is"]["created"])
            print("get_current_time_hr()", get_current_time_hr())

            # Get the Date field from your MongoDB document
            mongo_date = case["content_is"]["created"]

            # Get the current time
            current_time = get_current_time_hr()
            
            # Calculate the time difference
            date_format = "%Y-%m-%d %H:%M:%S"
            date1 = datetime.strptime(mongo_date, date_format)
            date2 = datetime.strptime(current_time, date_format)

            # Calculate the difference in seconds
            seconds_difference = (date2 - date1).total_seconds()

            seconds_in_limit = limit_hour*60*60
            if(seconds_difference>seconds_in_limit):
                return {"error":1, "error_desc":"You have already created a video within the last hour. Please use a new credit."}

            return {"error":0}


    return {"error":0}


# ----- Translating creating new video below this line -----

# Get the case info for the user which includes what cases they have already created and the next case number
def do_hypothesize_newest_case_info_that_will_be_copied_over(db, user_id, app_id=app_id):
    content = db["content"]
    my_content = content.find({"userId": user_id, "appId": app_id})
    caseIds = []

    if my_content:
        for document in my_content:
            # print("doc", document)
            caseIds.append(document.get("caseId"))
        caseIds.sort()
    # print("my_content", my_content)

    case_count = 0
    new_case_id = -1
    if(len(caseIds)==0):
        print("No cases found")

        print("Case count", case_count)
        print("New case number", new_case_id) # Int
        """
            Formula: No cases allowed
            No cases found
            Case count 0
            New case number 
        """

        return {"error":1, "error_desc":"No cases found. Was your account or video deleted before translating?"}, 400
    else:
        case_count = len(caseIds)
        new_case_id = caseIds[-1] + 1

        print("Case count", case_count)
        # print("Highest case number", caseIds[-1]) # Int
        print("New case number", new_case_id) # Int

        """
            Eg. 3 cases found
            caseIds [0, 1, 2]
            Case count 3
            New case number 3
        """

        return {"error":0, "user_id":user_id, "app_id":app_id, "caseIds":caseIds, "case_count":case_count, "new_case_id":new_case_id}

# Get whether there is still credits available for more cases from db["user_membership"]
# If there is, then create a new case Mongo document with the new case num.
# If there isn't, then return an error message that there are no more credits available.
def insert_bare_new_case_that_will_be_copied_over(db, case_info, user_id, app_id):
    credits_available = 0
    umems = db["user_membership"]
    umem = umems.find_one({"userId": user_id, "appId": app_id})
    new_case_id = case_info["new_case_id"]

    print("db", db)
    print("userId", user_id)
    print("appId", app_id)
    print("umem", umem)

    if umem is not None:
        if("creditsTimesOne" in umem and umem["creditsTimesOne"]>0):
            # stats = umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$inc": {"creditsTimesOne": -1}}, return_document=ReturnDocument.AFTER )
            stats = umems.find_one({"userId": user_id, "appId": app_id})
            old_credits_available =  stats["creditsTimesOne"]
            print("Checked credit of times one available: ", old_credits_available)
            content = db["content"]
            data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
            content.insert_one(data)
            print("Case created successfully: ", data)
            return {"error":0, "new_case_id": new_case_id, "old_credits_available": old_credits_available}
        elif("creditsAvailable" in umem and umem["creditsAvailable"]>0):
            # stats = umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$inc": {"creditsAvailable": -1}}, return_document=ReturnDocument.AFTER )
            stats = umems.find_one({"userId": user_id, "appId": app_id})
            old_credits_available =  stats["creditsAvailable"]
            print("Checked credit of restartable credits available: ", old_credits_available)
            content = db["content"]
            data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
            content.insert_one(data)
            print("Case created successfully: ", data)
            return {"error":0, "new_case_id": new_case_id, "old_credits_available": old_credits_available}
        else:
            isLateBill = umem.get("billLate")
            nextBillDate = umem.get("billNext")
            creditsMonthlyResetNumber = umem.get("creditsMonthlyResetNumber")
            # Obsoleted. Just have them view the dashboard. This is because there may be a-la-cart or lifetime deals
            # error_desc = f"No more credits available. Next reset to {creditsMonthlyResetNumber} credit(s) is on {nextBillDate}" if isLateBill==0 else f"No more credits available. Bill is overdue. Please visit billing page."
            error_desc = "Bill is late. Account suspended. Visit homepage for more information."
            content = db["content"]
            data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
            content.insert_one(data)
            print("Case created successfully: ", data)
            return {"error":1, "error_desc": error_desc}
    else:
        return {"error":1, "error_desc": "User not found"}   


# Get whether there is still credits available for more cases from db["user_membership"]
# If there is, then create a new case Mongo document with the new case num. Then decrement the credits by 1
# If there isn't, then return an error message that there are no more credits available.
def do_decrement_credit_and_insert_bare_new_case_that_will_be_copied_over(db, case_info, user_id, app_id):
    credits_available = 0
    umems = db["user_membership"]
    umem = umems.find_one({"userId": user_id, "appId": app_id})
    new_case_id = case_info["new_case_id"]

    if umem is not None:
        if("creditsTimesOne" in umem and umem["creditsTimesOne"]>0):
            stats = umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$inc": {"creditsTimesOne": -1}}, return_document=ReturnDocument.AFTER )
            new_credits_available =  stats["creditsTimesOne"]
            print("Consumed credit of times one credits -1 to: ", new_credits_available)
            content = db["content"]
            data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
            content.insert_one(data)
            print("Case created successfully: ", data)
            return {"error":0, "new_case_id": new_case_id, "new_credits_available": new_credits_available}
        elif("creditsAvailable" in umem and umem["creditsAvailable"]>0):
            stats = umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$inc": {"creditsAvailable": -1}}, return_document=ReturnDocument.AFTER )
            new_credits_available =  stats["creditsAvailable"]
            print("Consumed credit of restartable credits -1 to: ", new_credits_available)
            content = db["content"]
            data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
            content.insert_one(data)
            print("Case created successfully: ", data)
            return {"error":0, "new_case_id": new_case_id, "new_credits_available": new_credits_available}
        else:
            isLateBill = umem.get("billLate")
            nextBillDate = umem.get("billNext")
            creditsMonthlyResetNumber = umem.get("creditsMonthlyResetNumber")
            # Obsoleted. Just have them view the dashboard. This is because there may be a-la-cart or lifetime deals
            # error_desc = f"No more credits available. Next reset to {creditsMonthlyResetNumber} credit(s) is on {nextBillDate}" if isLateBill==0 else f"No more credits available. Bill is overdue. Please visit billing page."
            error_desc = "No more credits available. Visit homepage for more information."
            content = db["content"]
            data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
            content.insert_one(data)
            print("Case created successfully: ", data)
            return {"error":1, "error_desc": error_desc}
    else:
        return {"error":1, "error_desc": "User not found"}   

    # if umem:
    #     credits_available = umem.get("creditsAvailable")
    #     print("credits_available", credits_available) # 50

    #     if credits_available > 0:
    #         # Create a new case
    #         new_case_id = case_info["new_case_id"]
    #         content = db["content"]
    #         data = {'userId': user_id, 'appId':app_id, 'caseId':new_case_id, 'content_is': {}}
    #         content.insert_one(data)
    #         print("Case created successfully")

    #         # Decrement the credits by 1
    #         new_credits_available = credits_available - 1
    #         umems.find_one_and_update({"userId": user_id, "appId": app_id}, {"$set": {"creditsAvailable": new_credits_available}})
    #         print("new_credits_available", new_credits_available) # 49
    #         return {"error":0, "new_case_id": new_case_id, "new_credits_available": new_credits_available}
    #     else:
    #         isLateBill = umem.get("billLate")
    #         nextBillDate = umem.get("billNext")
    #         creditsMonthlyResetNumber = umem.get("creditsMonthlyResetNumber")
    #         # Obsoleted. Just have them view the dashboard. This is because there may be a-la-cart or lifetime deals
    #         # error_desc = f"No more credits available. Next reset to {creditsMonthlyResetNumber} credit(s) is on {nextBillDate}" if isLateBill==0 else f"No more credits available. Bill is overdue. Please visit billing page."
    #         error_desc = "No more credits available. Visit homepage for more information."
    #         return {"error":1, "error_desc": error_desc}
    # else:
    #     return {"error":1, "error_desc": "User not found"}
    
# Copy over a previous content into the new case (especially the photos uploaded).
def do_clone_over_case(db, user_id, app_id, template_case_id, new_case_id, otherRootOverrides=None):
    content = db["content"]
    ui_case_as_template = content.find_one({"userId": user_id, "appId": app_id, "caseId": template_case_id})

    print("*********** (do_clone_over_case)")
    print(ui_case_as_template)
    print("new_case_id", new_case_id)

    keys_to_delete = ['_id', 'caseId']
    new_case_override_with_template = {key: copy.deepcopy(value) for key, value in ui_case_as_template.items() if key not in keys_to_delete}

    # print("new_case_override_with_template", new_case_override_with_template)
    if("content_is" in new_case_override_with_template):
        keys_to_delete = ["finalVideo", "created"]
        for key in keys_to_delete:
            if key in new_case_override_with_template["content_is"]:
                del new_case_override_with_template["content_is"][key]
        # print("**************** !new_case_override_with_template", new_case_override_with_template)

    if otherRootOverrides is not None:
        for key, value in otherRootOverrides.items():
            new_case_override_with_template[key] = value

    result = content.find_one_and_update(
        {"userId": user_id, "appId": app_id, "caseId": new_case_id}, 
        {"$set": new_case_override_with_template},
        return_document=ReturnDocument.AFTER 
    )
    # ^ ReturnDocument.AFTER will return the updated document including deep nested fields. Otherwise, it will only return the top level fields.

    # print(result)
    return result