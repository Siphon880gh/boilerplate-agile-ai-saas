# All new servers

Copy set of hostinger in build-scripts. Then update the URLs esepecially host-curl and hots-fetch
Update package.json copying hostinger sciprts as another set of the newer server

At local env template, update SERVER_TYPE, MONGO_USER, and MONGO_PASSWORD. At utils/server/db.py,  add to:
if(server_type == "godaddy_centos" or server_type == "hostinger_ubuntu_22" or server_type == "APP_SERVER_debian_12"):


supervisor app conf:
update paths to where sh file loads
command=/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure/supervisor_hostinger.sh
directory=/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure/

supervisor sh file:
update paths to where gunicorn, env, and pyenv/pipenv virtual envs are at:
DIR_APP_ROOT=/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app
DIR_APP_SH=/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure

Also make sure the sh file is activating the correct pyenv app name

---

CHECK 00:

See if your server has ImageMagick which MoviePy relies on and there are no errors with pip until runtime of the app

```
convert --version
```

If not found, then look up instructions how to install. Eg. Google: Debian 12 install ImageMagick. Instructions could be similar to:
```
sudo apt install libpng-dev libjpeg-dev libtiff-dev
sudo apt install imagemagick
```

---

CHECK 0:

Run seed.py. Then run python.py directly (no running gunicorn or sh file yet)

If error 500 visiting web app, add to top of index.php:
```

// Set error logging
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set error logging to page
ini_set('display_errors', 1); // displays errors on page
```
Then revisit and see what the bug is

---

CHECK 1:
When running flask:

```
/root/.pyenv/versions/app/lib/python3.12/site-packages/pydub/utils.py:170: RuntimeWarning: Couldn't find ffmpeg or avconv - defaulting to ffmpeg, but may not work
  warn("Couldn't find ffmpeg or avconv - defaulting to ffmpeg, but may not work", RuntimeWarning)
```

Here's the actual problem. When installing ffmpeg with python, do I need to have ffmpeg on my computer? YES

Ubuntu 22
```
sudo apt install ffmpeg
```

Python:
```
pipev install ffmpeg-python
```

You DO NOT install with `pipenv install fmpeg` which might work but it's not the proper package that is a python wrapper to use the OS' ffmpeg

---

CHECK 2:

```
 * Debugger is active!
 * Debugger PIN: 822-465-440
209.65.62.26 - - [20/Aug/2024 08:11:53] code 400, message Bad request version ('\x00\x02\x01\x00\x00\x1b\x00\x03\x02\x00\x02\x00')
209.65.62.26 - - [20/Aug/2024 08:11:53] "\x16\x03\x01\x06¹\x01\x00\x06µ\x03\x03\x91ifsÂGÉuBR\x8b¯\x81\x14\x04\x844¡õ÷\x90ûl{µ\x9c\x99lè®;\x1b \x0c¡\x87D¥oGy\x89»ikÔÆN\x03°\x85¡¬\x97\x1dñ°u¼­Í²\x04§­\x00 jj\x13\x01\x13\x02\x13\x03À+À/À,À0Ì©Ì¨À\x13À\x14\x00\x9c\x00\x9d\x00/\x005\x01\x00\x06Lªª\x00\x00\x00\x0d\x00\x12\x00\x10\x04\x03\x08\x04\x04\x01\x05\x03\x08\x05\x05\x01\x08\x06\x06\x01\x00-\x00\x02\x01\x01\x00\x10\x00\x0e\x00\x0c\x02h2\x08http/1.1\x00\x12\x00\x00\x00+\x00\x07\x06ZZ\x03\x04\x03\x03þ\x0d\x00º\x00\x00\x01\x00\x01h\x00 jS\x18\\øÏð&nÛ)´\x9a÷'\x11\x19Ë¸\x8d\x0f;vÊ\x02»Ø'a(\x9di\x00\x90ÄÉN1\x96±4k4aø\x99\x1c:*oÎ¿M¢hgr±°m©ªØ¾\x95\x09ø\x90èq\x14zÛè@RÇe\x15»Dôã´mBá%<"ò4)X\x92[\x1f¾s0\x13\x03Pªh\x8d i\x87HÚ\x93¹¬\x06ó\x97ø\x91À>\x86ðÖ!pä\x1dc>\x15\x95\x17^gäÁÝ\x1aë+1:\x80¬¨à<Ù\x91ÿã\x8fQo\x8a\x16ö\x11#Ú\x97~\x04\x9f\x05\x19½ÈÏ¨qú\x93n\x8b\x86ùDi\x00\x05\x00\x03\x02h2\x00\x00\x00\x15\x00\x13\x00\x00\x10YOUR_DOMAIN\x00#\x00\x00\x00\x17\x00\x00\x00\x0b\x00\x02\x01\x00\x00\x1b\x00\x03\x02\x00\x02\x00" 400 -
```


Visit both versions of the page directly on the web browser (with and without https):
http://YOUR_DOMAIN:5001/
Versus
https://YOUR_DOMAIN:5001/


You should be able to hit the endpoint at http and may have that server console error show at https. Explanation: The issue you're encountering is due to the fact that your Flask application is receiving HTTPS requests, but it is only configured to handle HTTP requests. When a request is made over HTTPS, the data is encrypted. Your Flask application, if not set up to handle HTTPS, does not expect encrypted data, so it tries to interpret the encrypted data as a regular HTTP request. This results in the "Bad request version" errors because the encrypted data cannot be parsed as a valid HTTP request. One way to solve this is to load in the SSL cert and key at server.py, but this is not the most maintainable way. The following is a more maintainable way.

You can use a reverse proxy like Nginx to terminate SSL and forward the request to your Python server over HTTP. This way, your Python server can remain HTTP while Nginx handles the HTTPS traffic:
You should prevent frontend sending requests to port 5001. If you listen to port 5001 on Nginx then it becomes a web server and blocks Flask from running (will say port in use). 

Instead your frontend should request to YOUR_DOMAIN/api either over http or https, then your server block can match for /api, then terminate the SSL and forward the request to port 5001 over http

You’ll edit the request URL at Template-APP_SERVERVPS0-Host-Fetch.js and Template-APP_SERVERVPS0-Host-Curl.php.
https://YOUR_DOMAIN:5001 → https://YOUR_DOMAIN/api
Then run npm run build-..  while in the build-scripts folder

On web browser, you can check console: finalHost  to make sure the new fetch url is updated.

If you find API endpoints are hitting 404, you’d have to adjust the server.py endpoints to have /api. Or you can strip /api before matching endpoints:
```
from flask import Flask, request

class StripApiMiddleware:
    def __init__(self, app):
        self.app = app

    def __call__(self, environ, start_response):
        # Strip "/api" from the request path if it exists
        if environ['PATH_INFO'].startswith('/api'):
            environ['PATH_INFO'] = environ['PATH_INFO'][4:]  # Remove "/api"
        return self.app(environ, start_response)

app = Flask(__name__)
app.wsgi_app = StripApiMiddleware(app.wsgi_app)

@app.route('/example')
def example_route():
    return 'This is an example route.'

if __name__ == '__main__':
    app.run(port=5001)
```

---

With running server.py successful and able to hit endpoints and respond with data to the frontend, let's try running sh directly

While in the app folder, which contains infrastructure/, server.py, and wsgi.py, run:
```
$(pwd)/infrastructure/supervisor_hostinger.sh
```

The reason why is where you run the sh file matters

---

Once sh file works (app's not broken), it's time to use Supervisor to monitor and persist the sh file

Run:
```
sudo systemctl start supervisor
```

And check at http://YOUR_DOMAIN:9001/

If the app is not listed, did you rename the file extension to disable the Supervisor app earlier? Look at /etc/supervisor/conf.d

If the app is listed as failed, you can open the error log from the Supervisor web panel. It'll clue you in, eg. supervisor_hostinger.sh: line 15: cd: /home/wengindustries/htdocs/wengindustries.com/saas/app: No such file or directory

The "Restart All" button at the Supervisor web panel may not be reliable and just hangs. In server console run: `sudo systemctl restart supervisor`

If the web panel shows "command at '/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure/supervisor_APP_SERVER.sh' is not executable"..
Remember you've set the user at the Supervisor primary config to root. Now check the file permissions of the sh file to make sure execution is allowed, and if not then chmod it.

If the webpanel shows it errored and you clicked the error log showing that python packages are not found:
```
/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure/supervisor_APP_SERVER.sh: line 37: gunicorn: command not found
/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure/supervisor_APP_SERVER.sh: line 44: wait: `': not a pid or valid job spec
/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure/supervisor_APP_SERVER.sh: line 16: pyenv: command not found
/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure/supervisor_APP_SERVER.sh: line 37: gunicorn: command not found
/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/infrastructure/
```

Culprit: 
The errors you're encountering suggest that the environment for the root user isn't set up correctly for pyenv, meaning that pyenv and the gunicorn command are not available when the script is run as root.

Check for a Non-Interactive Shell: If the script is run in a non-interactive shell (e.g., via cron or supervisord), the environment may not load ~/.bash_profile or ~/.bashrc. You might need to source these files at the beginning of your script:
```
#!/bin/bash
source /root/.bash_profile
```

Do not use `~` because it might not expand.

If contineus to not find pyenv and other packages, echo $HOME in your terminal, then define $HOME in the sh script. It probably couldn't expand $HOME.

If it now says running but your app doesn't work, still check the error log from the web panel, and if you get:
```
ges/gevent/server.py", line 209, in wrap_socket_and_handle
    with _closing_socket(self.wrap_socket(client_socket, **self.ssl_args)) as ssl_socket:
                         ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
  File "/root/.pyenv/versions/3.12.4/envs/app/lib/python3.12/site-packages/gevent/ssl.py", line 120, in wrap_socket
    return self.sslsocket_class(
           ^^^^^^^^^^^^^^^^^^^^^
  File "/root/.pyenv/versions/3.12.4/envs/app/lib/python3.12/site-packages/gevent/ssl.py", line 349, in __init__
    self.do_handshake()
  File "/root/.pyenv/versions/3.12.4/envs/app/lib/python3.12/site-packages/gevent/ssl.py", line 724, in do_handshake
    self._sslobj.do_handshake()
ssl.SSLError: [SSL: HTTP_REQUEST] http request (_ssl.c:1000)
2024-08-20T10:29:17Z <Greenlet at 0x7f1c6179b2e0: _handle_and_close_when_done(<bound method StreamServer.wrap_socket_and_handle , <bound method StreamServer.do_close of <StreamServ, (<gevent._socket3.socket [closed] at 0x7f1c6164e35)> failed with SSLError
```

Now change the vhost proxy_pass to https://127.0.0.1:5001 instead of the previous http://127.0.0.1:5001

---

How long are people generating the video? Their fetch will wait for that long then expect a response with the video url. You need to raise up the allowed wait time before a timeout error.


Inside a server block:
```
    location /api/ {
        proxy_read_timeout 300s;   # Adjust as needed
        proxy_connect_timeout 300s; # Adjust as needed
        proxy_send_timeout 300s;   # Adjust as needed
    }
```

If you're proxy passing to a backend to hide non-web ports and increase security, it could ultimately be:
```
location /api {
	proxy_pass https://127.0.0.1:5001;
	proxy_read_timeout 300s;   # Adjust as needed
	proxy_connect_timeout 300s; # Adjust as needed
	proxy_send_timeout 300s;   # Adjust as needed
	proxy_set_header Host $host;
	proxy_set_header X-Real-IP $remote_addr;
	proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
	proxy_set_header X-Forwarded-Proto $scheme;
}
```

We added SSE so that instead of a fetch waiting for a response for minutes on the video, we have SSE that sends text streams on the status and completions of the video:
```
Make sure if you’re using Nginx to disable its default behavior of buffering responses. Otherwise the frontend .onmessage just wont trigger (you can console log to check it triggers). See (more than example):
    location /api {
        proxy_pass https://127.0.0.1:5001;
        proxy_read_timeout 600s;   # Adjust as needed
        proxy_connect_timeout 600s; # Adjust as needed
        proxy_send_timeout 600s;   # Adjust as needed
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Allows SSE by disabling Nginx's default behavior of buffering responses, 
        # which can cause issues with SSE as it expects to receive events as they 
        # are sent.
        proxy_buffering off;
        proxy_cache off;
        proxy_set_header Connection '';
        chunked_transfer_encoding off;
        proxy_http_version 1.1;
    }
```

Adjust your Gunicorn (Flask and Python):
```
gunicorn --timeout 300 myapp:app...
```
