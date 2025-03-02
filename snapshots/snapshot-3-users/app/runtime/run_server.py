def runServer(app):
    print("API Service Ready at port 5001.")
    app.run(debug=True, port=5001)