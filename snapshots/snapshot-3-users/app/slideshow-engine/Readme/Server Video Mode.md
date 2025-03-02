Fetch mode is slow in production because it ties up resources, however it's good at raising errors

When using multithreading for slideshow generation on the backend and Server Sent Events (SSE on backend, EventStream on frontend), it's faster for users and traffic, but errors dont get raised even on try-except blocks. Even using Python's built-in logger (`import logging`) won't detect errors.

So when developing new features in the slideshow creator, it'd be hard to find errors. So we built in switches to go from Fetch to SSE+Multithreading

For testing errors can raise, you can place this code block into slideshow-engine/process.py:
```
    # # Test that raising errors work which works on "Fetching" Slideshow Server Mode, but errors don't get raised in "SSE+Multithreading" mode
    # x = 14
    # y = 0
    # z = x / y  # This will raise ZeroDivisionError
```

===

How:

In `build-scripts/` you can run:
- npm run change-video-server:sse+multithreading
- npm run change-video-server:fetch

What this does is override `runtime/serverSlideshowMode.php` with a `script` block that sets the `window.serverSlideshowMode` which `assets/index.js:buildSlideshowFromAssets()` looks into to determine whether to request the server for SSE which includes multithreading, or to request server with a normal fetch.