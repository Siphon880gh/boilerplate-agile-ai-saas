import os
import sys

# Add parent folder to system path
script_dir = os.path.dirname(os.path.abspath(__file__))
app_dir = os.path.join(script_dir, "..")
sys.path.append(app_dir)

# print("\n******** vid: app_dir ********\n")
# print(app_dir)

# print("\n******** vid: slideshow-engine ********\n")
# print(os.path.join(app_dir, "./slideshow-engine"))

sys.path.append('/Users/wengffung/.local/share/virtualenvs/app-A39rOKjm/lib/python3.8/site-packages')

# Increase the traceback limit
sys.tracebacklimit = 100  # You can set this to any integer value

from flask import Flask, Response, jsonify, request # import request in order to parse url query string
from flask_cors import cross_origin, CORS
from runtime.run_server import runServer2

import time
import requests

video_generator_dir = os.path.join(app_dir, "./slideshow-engine")
sys.path.append(video_generator_dir)
from process import process # if says could not resolve in vs code, ignore that. it doesn't realize your sys path appended

class StripApiMiddleware:
    def __init__(self, app):
        self.app = app

    def __call__(self, environ, start_response):
        # Strip "/api" from the request path if it exists
        if environ['PATH_INFO'].startswith('/testapi'):
            environ['PATH_INFO'] = environ['PATH_INFO'][4:]  # Remove "/api"
        return self.app(environ, start_response)

def create_app():
    app = Flask(__name__)
    app.wsgi_app = StripApiMiddleware(app.wsgi_app)
    CORS(app, resources={r"/*": {"origins": "*"}})

    @app.route('/video-service', methods=['POST'])
    def videoService():
        if not request.is_json:
            return jsonify({"error": 1, "error_desc": "Request is not JSON"}), 400

        a = request.get_json()
        print(a)

        # vidCreatedInfo = process(a) # process

        try:
            vidCreatedInfo = process(**a) # process
            if("error" in vidCreatedInfo and vidCreatedInfo["error"] == 1):
                print("Error: ", vidCreatedInfo)
                return jsonify(vidCreatedInfo), 400
            print("Success: ", vidCreatedInfo)
            return jsonify(vidCreatedInfo), 201
            # rt.stop()
        except Exception as e:
            print("Error: ", e)
            return jsonify({"error": 1, "error_desc": "Error in creating video because " + str(e)}), 400
        
    return app

if __name__ == "__main__":
    #app.run(debug=True, port=5001)
    # app.run(host='0.0.0.0', port=5001)
    try:
        app = create_app()
        runServer2(app) # from runtime.env.py which runs local `app.run(debug=True, port=5002)` or remote https `app.run(ssl_context=ssl_context, host='0.0.0.0', port=5001, debug=True)`
    except NameError:
        print("Remote/local setup not setup right")