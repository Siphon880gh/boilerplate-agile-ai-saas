def runServer(app):
    print("Ready CMD+Click: http://localhost:8888/saas/app/")
    app.run(debug=True, port=5001)

def runServer2(app):
    print("Ready CMD+Click: http://localhost:8888/saas/app/")
    app.run(debug=True, port=5002)