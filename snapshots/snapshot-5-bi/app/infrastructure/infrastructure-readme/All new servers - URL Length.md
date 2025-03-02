When fetch POST or SSE/Multithreading, arguments are used for things like:

model (frontend arg in url) => model (backend receiving),
[
    {"i": 0, "info": "...", "params": {}}, 
    {"i": 1, "info": "...", "params": {}},
    ... 
    {"i": 50, "info": "...", "params": {}},
]

So make sure your apache or nginx server is setup so that it can handle long URLs!