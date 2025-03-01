import sys
import os
import importlib

# from utils.slideshow_updates_mongo.test import test

# Example usage
# dynamic_module_name = "utils.slideshow_callbacks.report_from_slideshow"  # The name of the module to import
# dynamic_module_path = "../"  # The path where the module is located
# --
# Store the function name as a string
# method_name = "update_mongo"
def callbacker(dynamic_module_path, dynamic_module_name, method_name, inputs, input_vals):
# def callbacker(**args):
    # Function to dynamically import a module from a specified path
    def import_dynamic_module(module_name, module_path):
        # Add the module path to sys.path if it's not already there
        if module_path not in sys.path:
            sys.path.insert(0, module_path)
        
        # Import the module dynamically
        module = importlib.import_module(module_name)
        
        # Optionally, remove the module path from sys.path to avoid potential conflicts
        sys.path.pop(0)
        
        return module

    # Import the module
    callback = import_dynamic_module(dynamic_module_name, dynamic_module_path)
    # callback.test()  # Call the test function from the dynamically imported module

    # Use getattr to get the method from the object and call it
    getattr(callback, method_name)(inputs, input_vals)