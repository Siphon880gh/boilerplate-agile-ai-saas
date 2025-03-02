For python packages to exist so they can be imported, you can activate the pyenv that already installed the packages:

```
pyenv activate APP_NAME
```

---

Have the right fetch host:
Make sure to build with `npm run dev` at app/ before running server.py either directly or with gunicorn. This will set the javascript right, in particularly assets/common.js, so the host address is localhost:5001

Localhost stats:
Python version is: 3.8.18
Mac 2021 M1 chip

There are two microservices. Running concurrently (the usual way) because of command "concurrently", could hide errors.
During development, run `npm run dev1` and `npm run dev2` each at its own terminal

---

When testing python code, make sure it's npm running the python code (via package.json scripts) in system terminal (not VS Code terminal), and make sure you're running it from app/, so the packages are the right environment. If you had ran directly with python interpreter like `python ../slideshow-engine/test.py`, then it'd use the paths of that python interpreter which may not be the one you want (could be anaconda's which is bad for our cv2/pillow purposes).

---

VS Code Bash:
chrome http://localhost:8888/saas/app; cd /Users/wengffung/dev/web/saas/app; sudo python server.py;