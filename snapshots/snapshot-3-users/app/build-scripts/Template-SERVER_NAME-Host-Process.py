import ssl;

def runServer(app):
    try:
        # Flask gives up ssl_context for gunicorn to set ssl, otherwise would conflict
        # ssl_context = ssl.create_default_context(ssl.Purpose.CLIENT_AUTH)
        # ssl_context.load_cert_chain('/home/bse7iy70lkjz/ssl/certs/wengindustry_com_a444f_a4d3f_1716207937_8b85eb3fb0975662e15e5edb0a0312ea.crt', '/home/bse7iy70lkjz/ssl/keys/a444f_a4d3f_0a15eb4d8e049276bf3024f5ba73562b.key')
        # app.run(ssl_context=ssl_context, host='0.0.0.0', port=5002, debug=True)
        app.run(host='0.0.0.0', port=5001, debug=True)
    except:
        print("ERROR: Please check your SSL certificate and key paths. Are you running remote environment locally?")

def runServer2(app):
    try:
        # Flask gives up ssl_context for gunicorn to set ssl, otherwise would conflict
        # ssl_context = ssl.create_default_context(ssl.Purpose.CLIENT_AUTH)
        # ssl_context.load_cert_chain('/home/bse7iy70lkjz/ssl/certs/wengindustry_com_a444f_a4d3f_1716207937_8b85eb3fb0975662e15e5edb0a0312ea.crt', '/home/bse7iy70lkjz/ssl/keys/a444f_a4d3f_0a15eb4d8e049276bf3024f5ba73562b.key')
        # app.run(ssl_context=ssl_context, host='0.0.0.0', port=5002, debug=True)
        app.run(host='0.0.0.0', port=5002, debug=True)
    except:
        print("ERROR: Please check your SSL certificate and key paths. Are you running remote environment locally?")