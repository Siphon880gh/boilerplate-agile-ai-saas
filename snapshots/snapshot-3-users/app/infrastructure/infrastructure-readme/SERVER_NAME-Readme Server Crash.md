
When server crashes out of our control (Eg. power outage at data center). The major steps:
1. Make sure VM restarted properly
2. Make sure Supervisor restarted properly (likely won't restart properly because crashing the server does not recreate the empheral /var/run/supervisor that's required to have a socket file).

Go into the dedicated server `APP_SERVER` to check the VPS has restarted:
```
xl list
```

If vps0 is shown at a “p” state, aka paused state, it’s still restarting the VM. Just wait longer
If vps0 is not even listed, you will need to restart the VM (the create command won't wipe your data): `sudo xl create /etc/xen/vps0.cfg`.

3. Then ssh into the VPS server (you may want to add an alias at bash_profile or zshrc that lets you ssh into the server with a short command), which is actually a VM hosted on the dedicated server and bridged to the internet with a different IP address (which DNS resolves, for example, app.domain.tld to)

If you can ssh into both servers, you’re fine. Websites should load fine. However, we need to check that signup/login and database capabilities are intact.

---

For website that has backend and database capability, check that our backend server is running. Since we use Supervisor to persist the backend server against app crashes, making sure the supervisor process has successfully restarted and therefore restarting the backend server that accesses the database. Usually, Supervisor autorestarts when the VPS restarts, however when it comes to server crashes, it will cause problems with the Socket file because of its storage in an epheral folder path `/var/run/supervisor/supervisor.sock`. 

Check supervisor process has correct syntax (usually does):
```
sudo systemctl status supervisor
```

If you get this error...:
```
root..# sudo systemctl status supervisor
● supervisor.service - Supervisor process control system for UNIX
     Loaded: loaded (/lib/systemd/system/supervisor.service; enabled; preset: enabled)
     Active: activating (auto-restart) (Result: exit-code) since Thu 2024-10-24 02:42:11 UTC; 11s ago
       Docs: http://supervisord.org
    Process: 2781 ExecStart=/usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf (code=exited, status=2)
   Main PID: 2781 (code=exited, status=2)
        CPU: 175ms
```

Then it’s likely a socket problem even though the error does not point to a socket problem. Errors at this level can be very vague. You may assume a socket problem or perform a full confirmation by following these debugging steps:

Run Supervisor against the global config which will load in all app configs to check if supervisor ran incorrectly:
```
sudo tail -f /var/log/supervisor/supervisord.log
```

If it says it ran successfully and have included files - it is good even though it still ignored showing socket errors (it included files because the global config file for supervisor includes a globed app configs path, therefore running your other backend server apps for supervisor). 

If there are no apparent errors in this output (mostly dealing with wrong syntax in either the global config file or the app config files), run Supervisor directly to see if there are problems while running Supervisor itself:
```
sudo supervisord -c /etc/supervisor/supervisord.conf
```

It’s a socket problem if you run... 1:
```
root...# sudo supervisord -c /etc/supervisor/supervisord.conf
```

And you get this error... 2:
```
Error: Cannot open an HTTP server: socket.error reported errno.ENOENT (2)
```

<b>Explanation</b>: This is because `/var/run/supervisor/` doesn't exist after your server crashed because directories in `/var/run/` (or `/run/`, which is often a symlink to `/var/run/` on modern systems) are typically **temporary directories** that are created at boot time. If your server crashed, when you restart the VPS with Xen, the ephemeral directory may not get an opportunity to be recreated. Therefore, to fix the socket problem preventing supervisor from restarting our backend server to access the database:

Create the directory manually and ensure Supervisor has the correct permissions to use it:
```bash
sudo mkdir -p /var/run/supervisor
sudo chown root:root /var/run/supervisor
sudo chmod 755 /var/run/supervisor
```

Then restart/shutdown the supervisor per the npm scripts. Check that you can login at the app page.