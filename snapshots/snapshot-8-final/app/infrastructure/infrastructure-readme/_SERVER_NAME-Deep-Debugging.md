Worked on the computer but once uploaded it doesnt work. Whats worse, you dont see any errors because it’s SSE. Console just waiting for the ping that it’s a success.

Level 1 Attempt:
npm run restart-APP_SERVER
npm run restart-APP_SERVER

Level 2 Attempt:
cd build-scripts && npm run build-APP_SERVER
npm run restart-APP_SERVER
npm run restart-APP_SERVER

Leve 3  Attempt - Switch from SSE to fetch in order
cd build-scripts && npm run change-video-server:fetch
npm run shutdown-APP_SERVER
npm run shutdown-APP_SERVER
cd infrastructure/supervisor-apps
pyenv activate APP_NAME
./supervisor_APP_SERVER_api.conf
ON OTHER SSH TERMINAL: ./supervisor_APP_SERVER_video.conf

^ About level 3 attempt: No need to run video microservice because api in non-SSE/multithreading/microserver mode, so api will call process directly

```
(2025-01-02 11:45:28 +0000] [4155523] [INFO] Starting gunicorn 22.0.0
+0000] [4155523] [DEBUG] Arbiter booted
4 [INFO] Listening at: https://0.0.0.0:5001 (4155523)
155523] [INFO] Using worker: gevent
+0000] [4155567] [INFO] Booting worker with pid: 4155567
+0000] [4155568] [INFO] Booting worker with pid: 4155568
+0000] [4155569] [INFO] Booting worker with pid: 4155569
+0000] [4155570] [INFO] Booting worker with pid: 4155570
+0000] [4155571] [INFO] Booting worker with pid: 4155571
2025-01-02 +0000] [4155572] [INFO] Booting worker with pid: 4155572
2025-01-02 11:45:28 +0000] [4155573] [INFO] Booting worker with pid: 4155573
ansstst2 11:45:28 +0000] [4155574] [INFO] Booting worker with pid: 4155574
-02 11:45:28 +0000] [4155576] [INFO] Booting worker with pid: 4155576
2025-01-02 11:45:28 +0000] [4155577] [INFO] Booting worker with pid: 4155577
2025-01-02 11:45:28 +0000] [4155578] [INFO] Booting worker with pid: 4155578
2025-01-02 11:45:28 +0000] [4155579] [INFO] Booting worker with pid: 4155579
2025-01-02 11:45:28 +0000] [4155580] [INFO] Booting worker with pid: 4155580
2025-01-02 11:45:28 +0000] [4155581] [INFO] Booting worker with pid: 4155581
2025-01-02 11:45:28 +0000] [4155582] [INFO] Booting worker with pid: 4155582
2025-01-02 11:45:28 +0000] [4155583] [INFO] Booting worker with pid: 4155583
2025-01-02 11:45:28 +0000] [4155584] [INFO] Booting worker with pid: 4155584
2025-01-02 11:45:28 +0000] [4155585] [INFO] Booting worker with pid: 4155585
2025-01-02 11:45:29 +0000] [4155586] [INFO] Booting with pid: 415
2025-01-02 11:45:29 +0000] [4155587] with pid: 4155587
2025-01-02 11:45:29 +0000] [4155588]
2025-01-02 11:45:29 +0000] [4155589]
2025-01-02 11:45:29 +0000] [4155591]
2025-01-02 11:45:29 +0000] [4155592]
2025-01-02 11:45:29 +0000] [4155593]
2025-01-02 11:45:29 +0000] [4155597]
2025-01-02 11:45:29 +0000] [4155598]
2025-01-02 11:45:29 +0000] [4155601]
2025-01-02 11:45:29 +0000] [4155602]
```

And as you the test user is using the app, console logs appear:
```
{
'caseId': 21, 'finalVideo': 'users/final-video-aAPP_ABBREV-c21-673eb6bf48c77420d743190e.mp4'
},
{ 
'caseId': 22, 'finalVideo': 'users/final-video-aAPP_ABBREV-c22-673eb6bf48c77420d743190e.mp4'
}
... etc from a big console.log dump

[2025-01-02 11:45:49 +0000] [4155583] [DEBUG] Closing connection.
[2025-01-02 11:47:04 +0000] [4155592] [DEBUG] POST /api/analytics/webpages/visited
[2025-01-02 11:47:12 +0000] [4155581] [DEBUG] POST /api/profile/credits/train-voice
[2025-01-02 11:47:12 +0000] [4155601] [DEBUG] Closing connection.
[2025-01-02 11:47:14 +0000] [4155587] Generating speech at ElevenLabs with: eleven_multilingual_v2
[2025-01-02 11:47:16 +0000] [4155587] [DEBUG] Closing connection.
```


Test app. Any errors will show. This is meant for only when one user is on. So ideally if you have a lot of customers, this is done on a STAGING SERVER (You would have two servers)
```
FETCH: Direct process() Error:  [Errno 2] No such file or directory: '.....png'
[2025-01-02 11:48:45 +0000] [4155589] [DEBUG] Closing connection. 
```

---


^ Cleanup after level 3 attempt: Switch back to SSE and restart the server `npm run change-video-server:sse+multithreading`