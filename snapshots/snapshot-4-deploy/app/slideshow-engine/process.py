# Relative output
# saved files are relative to app: app/server.js calls this function using engine_sequence.py, therefore saved files are relative to app

# Docker Path
# Let's say you're at slideshow-engine. To reach app, you need to go up one level, then cd into app, that is, if you're in your fs
# If these python scripts reside in Docker, then you go to absolute path /app/ which should be mounted appropriately so that app is a child of /app/
# from check_if_docker import getPossDockerPath
# possDockerPath = getPossDockerPath("./", "/app/")

import sys

from pymongo import MongoClient
from bson import ObjectId

import time
import os

import callbacker as callbacker
from flask import jsonify

from defaults import defaults

# Prep: Strip away the /home path so can be displayed in the frontend
def stripHome(input):
    if input is None:
        return ""
    index = input.rfind("/users/")

    # Extract the substring starting from "/users/" to the end
    if index != -1:
        result = input[index+1:]  # +1 to remove the leading "/"
    else:
        result = input
    return result

##################################################
# region process receives frontend args 
# the args are usually passed from api microservice (if fetch) or video microservice (if SSE+Multithreading)
def process(filenameAttachIds,
            files,
            callbacks = [],
            app_dir = "../"
            ):
    script_dir = "./"

    builtReturn = {}
    #endregion process receives frontend args

    ##################################################
    # region Process

    # Copy demo.mp4 as the final video
    import shutil
    
    demo_path = os.path.join(app_dir, "slideshow-engine/demo/demo.mp4")
    FINAL_VIDEO = os.path.join(app_dir, f"users/final-video-{filenameAttachIds}.mp4")
    
    # Ensure the users directory exists
    os.makedirs(os.path.dirname(FINAL_VIDEO), exist_ok=True)
    
    # Copy the demo video
    shutil.copy2(demo_path, FINAL_VIDEO)

    # Update the modification time of the final video to current time
    current_time = time.time()
    os.utime(FINAL_VIDEO, (current_time, current_time))

    # FINAL_VIDEO = os.path.join(app_dir, f"users/final-video-{filenameAttachIds}.mp4")

    # endregion Process

    ##################################################
    # region Strip away the /home path so can be displayed in the frontend

    FINAL_VIDEO = stripHome(FINAL_VIDEO)


    # Add 3 second delay
    time.sleep(5)

    # print("FINAL_VIDEO: ", FINAL_VIDEO)
    builtReturn["finalVideo"] = FINAL_VIDEO

    # endregion Strip away the /home path so can be displayed in the frontend

    ##################################################
    # region Callbacks (Eg. Mongo updates)

    # How this works:
    # At process.py as part of the slideshow creator, the script iterates the list of variable names at inputVars or input_vars first 
    # passed from api microservice (which did not have the variables defined) if fetch developmental mode, or passed from the video microservice 
    # if SSE+multithreading production mode. While iterating, the script looks up each variable name's value at the local scope at process 
    # which has been accruing values including the final video output, as the video is being created. Those variable names' values get appended 
    # to a fresh list input_vals, which process.py can open the module file (also passed as a variable, usually the value 
    # "utils.slideshow_callbacks.report_from_slideshow") using callbacker.callbacker, in order to have that module file run the mongo update.
    
    for callback in callbacks:
        dynamic_module_path = callback["dynamic_module_path"]
        dynamic_module_name = callback["dynamic_module_name"]
        method_name = callback["method_name"]
        
        inputs = callback["inputs"]
        input_vars = callback["inputVars"]

        input_vals = []
        for name in input_vars:
            input_vals.append(locals()[name])

        # print("**** process: callback in callbacks ****")
        # print(inputs)
        # print(input_vars)
        # print(input_vals)
    
       
        callbacker.callbacker(dynamic_module_path, dynamic_module_name, method_name, inputs, input_vals)
    # endregion Callbacks (Eg. Mongo updates)

    return builtReturn