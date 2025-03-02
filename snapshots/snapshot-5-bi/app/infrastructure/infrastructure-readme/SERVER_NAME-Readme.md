# Troubleshooting - APP_SERVER VPS0 (Readme infrastructure)

Start in no daemon so can print everything
npm run test-APP_SERVER
npm run log-APP_SERVER

Manage supervisor and see tail logs:
http://YOUR_DOMAIN:9001/
Supervisor credentials in ACC document, if asked.

If errors are not showing through, it's because of too many worker processes causing prints to not show through, so you should edit by commenting in and out so that it's one worker, one thread, for both API and Video Generator sh files:

infrastructure/supervisor-app-runs/supervisor_server_api.sh
infrastructure/supervisor-app-runs/supervisor_server_video.sh

# Starting/Restarting - APP_SERVER VPS0 (Readme infrastructure)

1. Restart
```
npm run restart-APP_SERVER
```
^ FYI, it runs from root package.json which restarts supervisor which runs the infrastructure/supervisor-app-runs/supervisor_server_api.sh and supervisor_server_video.sh which have `pyenv activate APP_NAME`

2. Too soon? See if there is error about address being used. If there is, attempt above again. The final line should be "Restarted supervisor" rather than:
```
Error: Another program is already listening on a port that one of our HTTP servers is configured to use.  Shut this program down first before starting supervisord.
For help, use /usr/bin/supervisord -h
```