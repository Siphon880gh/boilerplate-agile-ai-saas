// imported `finalHost` from app root `index.php` which sourced `assets/common.js`
// import timeUtils.getCurrentPSTDateTime() from app root `index.php` which sourced `assets/utils.js`

/* Main */

window.appModel = {
  userId: "err",
  appId: "APP_ABBREV",
  caseId: -1,
  aiPrompt: "",
  files: [],
  finalVideo: "",
}

function getUserId() {
  return appModel.userId;
}
function getAppId() {
  return appModel.appId;
}
function getCaseId() {
  return appModel.caseId;
}


CONST_NEW_CASE = -1; // new case vs resuming incomplete case
function preinitCase(caseId = CONST_NEW_CASE, callback) {
  console.log("getUserId()", getUserId())
  console.log("getUserId()", getAppId())

  if (caseId === CONST_NEW_CASE) {
    fetch(finalHost + "/cases/", {
      method: "POST",
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        userId: getUserId(),
        appId: getAppId()
      })
    }).then(response => response.json())
      .then(resource => {
        appModel.caseId = resource.new_case_id;
        initCase(resource.new_case_id);
        if (callback) callback()
      })
  } else {
    appModel.caseId = caseId
    initCase(caseId);
    if (callback) callback()
  }
}

function initCase(caseId) {
  window.appModel.caseId = caseId
}

var shareController = {
  copyVideoLink: (url) => {
    const textToCopy = url;
    navigator.clipboard.writeText(textToCopy).then(() => {
      alert('Video link copied to clipboard!\nShare to your social or property platform!');
    }).catch(err => {
      console.error('Failed to copy link: ', err);
    })
  }, // copyVideoLink

  shareToText: (url) => {
    if (navigator.share) {
      navigator.share({
        title: `Hi ___`,
        text: `Check out my slideshow at ${url}`
      }).then(() => {
        console.log('Content shared successfully!');
      }).catch((error) => {
        console.error('Error sharing content: ', error);
      });
    } else {
      lert('Sharing not supported in this browser');
    }
  },// shareToText

  shareToGmail: (url) => {
    const subject = encodeURIComponent(`Your invitation to create a slideshow`);
    const body = encodeURIComponent(`Hi ___,

Having to create slideshows for schools or meetings, use our AI tool to create a slideshow in minutes.

Check out an example slideshow at ${url}

Thank you.`);
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&su=${subject}&body=${body}`;
    window.open(gmailUrl, '_blank');
  },

  shareToEmail: (url) => {
    const subject = encodeURIComponent(`Your invitation to create a slideshow`);
    const body = encodeURIComponent(`Hi ___,

Having to create slideshows for schools or meetings, use our AI tool to create a slideshow in minutes.

Check out an example slideshow at ${url}

Thank you.`);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
  },


}

var creditsController = {
  loadCredits: () => {
    var params = new URLSearchParams({
      userId: getUserId(),
      appId: getAppId()
    })
    fetch(finalHost + `/profile/credits?${params.toString()}`, {
      method: "GET"
    })
      .then(response => response.json())
      .then(resource => {
        var credits = parseInt(resource.credits);
        if (credits < 0) credits = 0;
        document.querySelector(".credit-status").textContent = credits;
        document.querySelector(".credit-status").classList.remove("hidden");
      })
      .catch(error => {
        console.error("Error fetching credits:", error);
      });
  }, // loadCredits
  decrementGUI: () => {
    var credits = parseInt(document.querySelector(".credit-status").textContent)
    if (credits > 0)
      document.querySelector(".credit-status").textContent = credits - 1;
  }
};


var mainController = {
  init: function() {
    return {
      resetCornerStatuses: this.resetCornerStatuses.bind(this),
      pauseAllPossVideos: this.pauseAllPossVideos.bind(this)
    }
  },

  // exported resetCornerStatuses
  resetCornerStatuses() {
    document.getElementById('bottom-left').innerHTML = "";
    document.getElementById('bottom-right').innerHTML = "";
  },

  // exported pauseAllPossVideos
  pauseAllPossVideos() {
    document.getElementById("iframe-preview-slideshow").src = "about:blank";
  },

  proceedFromInstructions() {
    navController.switchPanel(SCREENS.WritePrompt);
    navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.WritePrompt);
  },

  saveAIPrompt: (payload) => {
    appModel.aiPrompt = payload.aiPrompt;
    fetch(finalHost + "/media/interim/prompt", {
      // fetch("server.php", {
      method: "POST",
      cache: "no-cache",
      body: JSON.stringify({
        "aiPrompt": appModel.aiPrompt,
        "userId": getUserId(),
        "appId": getAppId(),
        "caseId": getCaseId()
      }),
      headers: {
        "Content-type": "application/json; charset=UTF-8"
      }
    }).then(response => response.json())
      .then(resource => {
        if (resource.error === "1") {
          console.log(resource.error);
          console.log(resource.error_desc);
          alert(resource.error_desc);
        }
      }).catch(err => {
        var errStt = "Failed to connect"
        console.log(errStt + " - " + err);
        alert(errStt + " - " + err);
      }) // fetch

    navController.switchPanel(SCREENS.UploadFiles, true)
    navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.UploadFiles);
  }, // saveAIPrompt


  updateDbModel: (payload) => {

    const { appId, userId, caseId, files, labels } = payload;

    // Updates Mongo user's content.content_is.files
    fetch(finalHost + "/media/interim/files", {
      method: "POST",
      //mode: 'cors', // Set the mode to 'no-cors' to disable CORS
      cache: "no-cache",
      body: JSON.stringify({
        "userId": userId,
        "appId": appId,
        "caseId": caseId,
        "files": files,
        "labels": labels
      }),
      headers: {
        "Content-type": "application/json; charset=UTF-8"
      }
    }).then(response => response.text())
      .then(response => {
        // let iframe = document.getElementById("iframe-preview-slideshow");
        // iframe.src = iframe.dataset.willSrc;
        navController.switchPanel(SCREENS.PreviewSlideshow, true);
        navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.PreviewSlideshow);
      }).catch(err => {
        var errStt = "Failed to connect"
        console.log(errStt + " - " + err);
        alert(errStt + " - " + err);
      }) // fetch
  },
  addToJobQueue: async (jobDetails, callback = () => { }) => {
    // debugger;

    var userId = getUserId();
    var appId = getAppId();
    var caseId = getCaseId();

    var errored = false
    var finalVideo = ""

    var postBody = {
      userId,
      appId,
      caseId
    }

    const response = await fetch(`${finalHost}/media/video/prepare`, {
      method: "POST",
      body: JSON.stringify(postBody),
      headers: {
        'Content-Type': 'application/json'
      }
    });
    const resource = await response.json();

    if (resource.error) {
      alert(resource.error_desc);
    } else {
      callback(resource);
    }


  }, // buildVideoFromAssets


  performJob: (serverVideoMode = "SSE+MULTITHREADING", jobId = -1) => {
    if (jobId === -1) {
      alert("ERROR: Failed to prepare job. Please contact administrator.");
      return;
    }

    function _ifSSEAndMultithreading() {
      console.log("Server Video Mode: SSE with Multithreading");
      var errored = false;
      window.eventSource = new EventSource(`${finalHost}/media/video?jobId=${jobId}`);

      eventSource.onerror = function (event) {
        console.error("Error occurred:", event);

        // You can handle the error or retry logic here
        if (event.readyState === EventSource.CLOSED) {
          console.log("Connection was closed.");
        }
      };

      eventSource.onmessage = function (event) {

        if (event.data.indexOf('error') === 0) {
          alert(event.data);
          document.querySelector("#iframe-preview-slideshow").contentWindow.document.querySelector(".is-loading").innerHTML = event.data;
          errored = true;
          eventSource.close();
        }
        // console.log(event.data);
        if (event.data.indexOf("var") === 0) {
          var [, key, value] = event.data.split(" ");
          // debugger;
          switch (key) {
            case "finalVideo":
              appModel.finalVideo = value;
              break;
          } // switch
        } // if var

        console.log(event.data);

        if (event.data.indexOf('finished_video') === 0) {
          // Extract and use the video URL
          eventSource.close();

          if (!errored) {
            // Delegated to a pub sub polling:
            // - Hide loading cues
            // - Prepare video source
            // - Show video

            // Decrement credit
            creditsController.decrementGUI()

            // Setup translate button to alert if not enough credit or let user go to translate page
            var params = new URLSearchParams({
              userId: getUserId(),
              appId: getAppId()
            })
            fetch(finalHost + `/validate/credits?${params.toString()}`, {
              method: "GET"
            })
              .then(response => response.json())
              .then(resource => {
                if (resource.error === 1) {
                  document.querySelector("#iframe-preview-slideshow").contentWindow.document.querySelector("#want-translate").setAttribute("onclick", `alert("No credits available. Please buy more credits / upgrade / subscribe to create new videos.");`)
                }
              })
          } // !errored
        }
        // };
      } // on message
    } // ifSSE

    function _ifFetch() {
      console.log("Server Video Mode: Fetch");
      var errored = false;

      fetch(`${finalHost}/media/video?jobId=${jobId}`, { method: "POST" })
        .then(response => response.json())
        .then(resource => {
          if (resource.error === 1) {
            alert(resource.error_desc);
            document.querySelector("#iframe-preview-slideshow").contentWindow.document.querySelector(".is-loading").innerHTML = resource.error_desc;
            errored = true;
          } else {
            appModel.finalVideo = resource.finalVideo;


            // Decrement credit
            creditsController.decrementGUI()

            // Setup translate button to alert if not enough credit or let user go to translate page
            var params = new URLSearchParams({
              userId: getUserId(),
              appId: getAppId()
            })
            fetch(finalHost + `/validate/credits?${params.toString()}`, {
              method: "GET"
            })
              .then(response => response.json())
              .then(resource => {
                if (resource.error === 1) {
                  document.querySelector("#iframe-preview-slideshow").contentWindow.document.querySelector("#want-translate").setAttribute("onclick", `alert("No credits available. Please buy more credits / upgrade / subscribe to create new videos.");`)
                }
              })
          }
        })
        .catch(error => {
          console.error("Error occurred:", error);
        })
    }

    switch (serverVideoMode) {
      case "SSE+MULTITHREADING":
        _ifSSEAndMultithreading();
        break;
      case "FETCH":
        _ifFetch();
        break
    } // switch
  } // performJob

} // mainController

const { resetCornerStatuses, pauseAllPossVideos } = mainController.init();
window.resetCornerStatuses = resetCornerStatuses;
window.pauseAllPossVideos = pauseAllPossVideos;

var authController = {
  loggedIn() {
    return true;
  }, // loggedIn
  login(payload = null) {
    if (!payload) {
      payload = {
        login: document.querySelector("#loginModal #email-login").value,
        password: document.querySelector("#loginModal #password-login").value
      }
    }

    // TODO: Implement to check the case ID that's done / in progress / reached their tier limit?
    fetch(finalHost + "/auth/login", {
      method: "POST",
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    }).then(response => response.json())
      .then(resource => {
        if (resource.message === "authorized") {
          $('#loginModal').modal('hide');
          document.querySelector("#login-name").innerHTML = "Hi " + resource.full_name;
          document.querySelectorAll(".logged-in").forEach(el => { el.classList.remove("hidden"); el.classList.remove("hidden-important"); });
          document.querySelectorAll(".logged-out").forEach(el => el.classList.add("hidden"));

          localStorage.setItem("YOUR_APP_jwt", resource.jwt);

          var optionalProfilePic = resource?.profile_pic || "";
          this.initSessionJsModel({ userId: resource.userId, email: resource.email, fullName: resource.full_name, appId: resource.appId, profilePic: optionalProfilePic, advancedMode: resource.advanced_mode });

          // Load credit presentation
          creditsController.loadCredits();

          // Init PHP session
          this.initSessionPHP(resource, () => {
            navController.switchPanel(SCREENS.Dashboard);
            navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.Dashboard);
          });

        } else {
          alert("Wrong credentials!")
        }
      }) // fetch
  }, // login
  loginWithJWT(payload, callback = null, failCallback = null) {
    // Expect payload.jwt

    // TODO: How to check the case ID that's done / in progress / reached their tier limit?
    fetch(finalHost + "/auth/login/jwt", {
      method: "POST",
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    }).then(response => response.json())
      .then(resource => {
        if (resource.message === "authorized") {
          // $('#loginModal').modal('hide');
          document.querySelector("#login-name").innerHTML = "Hi " + resource.full_name;
          document.querySelectorAll(".logged-in").forEach(el => { el.classList.remove("hidden"); el.classList.remove("hidden-important"); });
          document.querySelectorAll(".logged-out").forEach(el => el.classList.add("hidden"));

          // localStorage.setItem("YOUR_APP_jwt", resource.jwt);

          var optionalProfilePic = resource?.profile_pic || "";
          this.initSessionJsModel({ userId: resource.userId, email: resource.email, fullName: resource.full_name, appId: resource.appId, profilePic: optionalProfilePic, advancedMode: resource.advanced_mode });

          // Load credit presentation
          creditsController.loadCredits();

          // Init PHP session
          this.initSessionPHP(resource, () => {
            if (callback) callback();
            navController.switchPanel(SCREENS.Dashboard);
            navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.Dashboard);
          });


          console.log({ error: 0, message: "Successfully logged in using JWT for persistent session." })

        } else {
          //alert("Wrong credentials!")
          console.log("ERROR: JWT not valid. Your session may have expired.");
          // debugger;
          if (failCallback) failCallback();
        }
      }).catch(() => {

        if (failCallback) failCallback();
      })
  }, // loginWithJWT
  signupAsGuest(callback = null) {
    // Guests must have different guest accounts because concurrent guests modifying their own samples with the same guest account may be problematic
    var payload = {
      login: `guest-a0-t${Date.now()}@gmail.com`,
      password: "guest",
      // caseId: getCaseId(),
      full_name: "Guest",
      newsletter: "n",
      guest_mode: 1
    }
    // guest_mode will be checked on the server side as well so can't be abused by public
    authController.signup(payload, callback)
  }, // signupAsGuest
  signup(payload = null, callback = null) {
    if (document.querySelector("#email-signup").value.length === 0 && document.querySelector("#password-signup").value.length === 0) {
      if (!payload?.login || !payload?.password) {
        alert("Please enter your email and password");
        return;
      }
    }

    if (!Boolean(payload) || JSON.stringify(payload) === '{}')
      payload = {
        login: payload?.login || document.querySelector("#signupModal #email-signup").value,
        password: payload?.password || document.querySelector("#signupModal #password-signup").value,
        full_name: payload?.full_name || document.querySelector("#signupModal #agent-full-name").value,
        newsletter: payload?.newsletter || document.querySelector("#signupModal #newsletter").checked ? "y" : "n"
      }

    fetch(finalHost + "/auth/signup", {
      method: "POST",
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    }).then(async (response) => {
      // Work with response in text or parsed JSON depending on what's available. 
      // If status not 2XX, raise http status with text or JSON for debugging purposes
      responseText = await response.text();
      responseBackJSON = {}
      try {
        responseBackJSON = JSON.parse(responseText);
      } catch (e) {
        if (!response.ok) throw new Error(`ERROR: ${JSON.stringify(responseText)}\n\nHTTP Status: ${response.status}`);
        return responseText;
      };
      // Custom message: 
      if (responseBackJSON?.error?.includes("Email already exists")) { throw new Error("Email already exists") }

      if (!response.ok) throw new Error(`ERROR: ${JSON.stringify(responseBackJSON)}\n\nHTTP Status: ${response.status}`);
      return responseBackJSON;
    }).then(resource => {
      $('#signupModal').modal('hide');
      document.querySelector("#login-name").innerHTML = "Hi " + resource.full_name;
      document.querySelectorAll(".logged-in").forEach(el => el.classList.remove("hidden"));
      document.querySelectorAll(".logged-out").forEach(el => el.classList.add("hidden"));
      // debugger;

      localStorage.setItem("YOUR_APP_jwt", resource.jwt);

      var optionalProfilePic = resource?.profile_pic || "";
      this.initSessionJsModel({ userId: resource.userId, email: resource.email, fullName: resource.full_name, appId: resource.appId, profilePic: optionalProfilePic });

      // Load credit presentation
      creditsController.loadCredits();

      // Init PHP session
      this.initSessionPHP(resource, () => {
        navController.switchPanel(SCREENS.Dashboard);
        navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.Dashboard);
      });

    }).catch(async (err) => {
      alert(err)
    })
  }, // signupAsGuest
  logout() {
    $("#loading-overlay").removeClass("d-none");
    localStorage.removeItem("YOUR_APP_jwt");
    localStorage.removeItem("logo_redirect_showed_once_in_session");

    navController.switchPanel(SCREENS.AuthLanding)
    navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.AuthLanding);

    document.querySelectorAll(".logged-in").forEach(el => { el.classList.add("hidden"); el.classList.add("hidden-important"); });
    document.querySelectorAll(".logged-out").forEach(el => el.classList.remove("hidden"));

    this.uninitSessionPHP();
    this.uninitSessionJsModel();
  }, // logout
  uninitSessionPHP() {
    fetch("app-auth-landing/uninit-session.php", {
      method: "GET",
      credentials: 'include',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).then(response => response.text()).then(data => {
      // Refresh the page and bypass the cache
      window.location.reload(true);
    });
  },
  uninitSessionJsModel() {
    window.appModel = {};
  }, // uninitSessionJsModel
  initSessionJsModel({ userId, email, fullName, appId, profilePic, advancedMode }) {
    window.appModel.userId = userId;
    window.appModel.email = email;
    window.appModel.fullName = fullName;
    window.appModel.appId = appId;
    window.appModel.caseId = null;
    window.appModel.profilePic = profilePic;
    window.appModel.advancedMode = advancedMode;
  }, // initSessionJsModel
  initSessionPHP(resource = {}, callback = () => { }) {
    // Init PHP session
    fetch("app-auth-landing/init-session.php", {
      method: "POST",
      credentials: 'include',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ user_id: resource.userId, app_id: resource.appId })
    }).then(response => response.text()).then(data => {
      try {
        data = JSON.parse(data);
      } catch (error) {
        console.error("Failed to parse data:", data);
      }

      // Enable if we want to redirect to whitelabeling for user with company name
      if (data?.redirectForWhitelabelingAgency && data.redirectForWhitelabelingAgency.length) {
        var trailUrl = data.redirectForWhitelabelingAgency;
        window.location.href = trailUrl;
        return;
      }

      if (callback) callback();

      // iPads have it scrolled down, so reset it to the top
      document.body.querySelector('iframe[data-will-src="app-dashboard/"]').addEventListener("load", () => {
        const iframeDoc = document.body.querySelector('iframe[data-will-src="app-dashboard/"]').contentWindow.document
        iframeDoc.documentElement.scrollTop = 0;
        iframeDoc.body.scrollTop = 0;
        window.scrollTo({
          top: 0,
          behavior: 'auto' // Optional for smooth scrolling, can be 'auto' for instant
        });
      })

      console.log(data);
    }); // fetch
  } // initSessionPHP

} //authController


// popstate for jwt login
var navController = {
  beforePopstate: -1,
  init: function () {
    window.addEventListener("popstate", function (e) {
      // debugger;
      var jwt = localStorage.getItem("YOUR_APP_jwt");
      if (jwt) {
        // authController.loginWithJWT({jwt});

        // navigated is after navigation done
        const currentUrl = new URL(window.location.href);
        const queryParams = currentUrl.searchParams;
        var navigated = queryParams.get("navigate")

        if (navigated) {

          // Clear all status messages
          resetCornerStatuses();

          // Pause all playing medias (requires you resetted / appended to window.parent.playableMedias)
          if (window.parent.playableMedias) {
            window.parent.playableMedias.forEach((media) => {
              media.pause();
            });
          }

          // Reset final video so does not get in the way of new case's generating
          appModel.finalVideo = "";

          // Close all SSE connections
          if (typeof window.eventSource !== "undefined") {
            if (window.eventSource.readyState !== EventSource.CLOSED) {
              window.eventSource.close();
            }
          }
          if (typeof window.eventSourceTrans !== "undefined") {
            if (window.eventSourceTrans.readyState !== EventSource.CLOSED) {
              window.eventSourceTrans.close();
            }
          }
        } // if navigated


        if ((navigated + "").includes("_")) {
          var [panelNum, state] = navigated.split("_");
          navigated = panelNum;
        }

        // If user had navigated back to AuthLanding, then login with JWT
        if (parseInt(navigated) === SCREENS.AuthLanding) {
          authController.loginWithJWT({ jwt });
        } else if (parseInt(navigated) === SCREENS.Dashboard) {
          authController.loginWithJWT({ jwt });
        }
        // If user had navigated back to Credits dashboard, from editing a case, reset all resumable models
        else if (parseInt(navigated) === SCREENS.EditCase) {
          delete window.parent.resumingModelAIPrompt;
        }
        else if (parseInt(navigated) === SCREENS.PreviewSlideshow && this.lastVisited.length > 2) {
          debugger;
          iframe.contentWindow.location.reload(); //this.lastVisited.at(-1)
        }

        // If user clicks Back to a page with _will-src, then that iframe will reload (rather than show previous state)
        // - Eg. Need to re-request server then re-render DOM.
        // Otherwise restore the previous state of the iframe
        // - Eg. Wan to restore what the user typed in that form previously. User can re-submit the form at a button's click.
        var panelNum = navigated;
        if ((navigated + "").includes("_")) {
          var [myPanelNum, state] = navigated.split("_"); // eg. 2_will-src for [data-will-src]
          panelNum = myPanelNum;
          if (state) {
            navController.switchPanel(parseInt(panelNum), true);
          }
        } else {
          navController.switchPanel(parseInt(panelNum));
        }

        navController.checkAdvancedMode();

      } // if jwt

      // Cancel the event
      e.preventDefault(); // If you prevent default behavior in Mozilla Firefox prompt will always be shown
      // Chrome requires returnValue to be set


      // e.returnValue = ''; // Setting to empty string will show the default leave site? dialog in Chrome
      // return ''; // For legacy browser support

    }); // popstate


    return {
      setQueryWithoutTriggeringPopstate: this.setQueryWithoutTriggeringPopstate.bind(this),
      getPanel: this.getPanel.bind(this),
      switchPanel: this.switchPanel.bind(this),
      // resetUploadIframe: this.resetUploadIframe.bind(this)
    }

  }, // init
  lastVisited: [],
  // Not just for analytics, but for:
  // If the page (aka routing page) you navigated back to has logic to redirect the user, and you dont want a redirect wall when navigating further back
  // Then detect if length-2 is SCREENS.X and if so, stop redirecting. Note it's not length-1 because at that point it recorded the routing page.

  // exported setQueryWithoutTriggeringPopstate
  setQueryWithoutTriggeringPopstate: function (key, value) {
    const url = new URL(window.location);
    url.searchParams.set(key, value);

    // Replace the current URL in the history stack
    history.pushState({}, '', url);
  }, // setQueryWithoutTriggeringPopstate

  getPanel: () => {
    return document.getElementById("panel-containers").__x.$data.activePanel

  }, // getPanel
  switchPanel: function (panelNum, refreshTo) {

    // Reset corner statuses
    resetCornerStatuses();

    // Record last visited
    if (typeof this.lastVisited?.length === "number" && this.lastVisited.length === 0) {
      var sessionTimestamp = timeUtils.getCurrentPSTDateTime(); // 2024-12-10 15:40:07
      this.lastVisited.push(sessionTimestamp);
    }
    this.lastVisited.push(panelNum);
    this.reportLastVisitedDb();

    // if(panelNum===4)
    //   debugger;      

    const iframe = document.querySelector(`#panel-${panelNum} iframe`);

    // If the iframe is about:blank, then it means the iframe will defer to load until 
    // switchPanel is called for this panel for the first time. Note this relies on
    // data-will-src being set at the iframe attributes.
    if (iframe.src.includes("about:blank")) {
      iframe.addEventListener("load", onShowModule);
      iframe.src = iframe.dataset.willSrc;
    }

    // If switchPanel is called with second argument true (for refreshTo), then the iframe 
    // will refresh according to either the data-will-src attribute or the src attribute 
    // of the iframe, whichever is present first. Otherwise, the iframe will just display 
    // the same state as it's been first loaded when the page containing the iframe loaded.
    else if (refreshTo) {
      iframe.addEventListener("load", onShowModule);
      const willSrc = iframe.dataset.willSrc
      if (willSrc) {
        iframe.src = willSrc;
      } else {
        iframe.contentWindow.location.reload();
      }
    } else {
      onShowModule();
    }

    function onShowModule() {
      if(parseInt(panelNum) === SCREENS.UploadFiles) {
        navController.checkAdvancedMode();
      }
    }

    // if (refreshTo?.length && !refreshTo.includes("data-")) refreshTo = "data-" + refreshTo;

    // Alpine JS will show the iframe needed
    document.getElementById("panel-containers").__x.$data.activePanel = panelNum;




  },

  checkAdvancedMode() {
    var isAdvancedMode = Boolean(window.parent.appModel?.advancedMode);
    
    const panelNum = navController.getPanel();
    const iframeElement = document.querySelector(`#panel-${panelNum} iframe`);

    if (!isAdvancedMode) {
      if (iframeElement) {
        const advancedOnlyEls = iframeElement.contentWindow.document.querySelectorAll('.advanced-only');
        advancedOnlyEls.forEach(element => {
          element.remove()
        });
      }
    } else {
      if (iframeElement) {
        const advancedOnlyEls = iframeElement.contentWindow.document.querySelectorAll('.advanced-only');
        advancedOnlyEls.forEach(element => {
          element.classList.remove("advanced-only");
          element.classList.add("advanced-okay");
        });
      }
    }
  },
  // resetUploadIframe() {
  //   // Assure if going to upload page by progressing forward, the upload page will refresh
  //   // Otherwise navigating back to the upload page would keep the state of your matched pictures
  //   uploadImagesLink = "app-upload-files/";
  //   uploadImagesIframe = document.getElementById("iframe-upload-files");
  //   uploadImagesIframe.setAttribute("data-will-src", uploadImagesLink);
  // }
} // navController

// Upgrade navController to have analytics
Object.assign(navController, {
  reportLastVisitedDb: function () { // window.parent.mainController._reportLastVisited();
    // var returningReport = "";

    function getEnumName(value) {
      for (const [key, val] of Object.entries(SCREENS)) {
        if (val === value) {
          return key; // `SCREENS.${key}`;
        }
      }
      return value; // if not matched, return the raw value
    } // getEnumName

    let reportingNames = [];
    this.lastVisited.forEach(index => {
      const name = getEnumName(index);
      reportingNames.push(name);
    });

    var payload = {
      userId: window.parent.getUserId(),
      newVisit: reportingNames.join(",")
    }

    fetch(finalHost + "/analytics/webpages/visited", {
      method: "POST",
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    }).then(response => response.json())
      .then(resource => {
        if (resource.error === 1) {
          console.error(resource)

        }
        console.log(resource)
      }) // fetch

  }, // reportLastVisitedDb
  reportLastVisitedStrings: function () { // window.parent.mainController._reportLastVisited();
    var returningReport = "";

    function getEnumName(value) {
      for (const [key, val] of Object.entries(SCREENS)) {
        if (val === value) {
          return key; // `SCREENS.${key}`;
        }
      }
      return value; // if not matched, return the raw value
    } // getEnumName

    returningReport += "Last visited screen enum ints: " + JSON.stringify(this.lastVisited) + "\n";

    let reportingNames = [];
    this.lastVisited.forEach(index => {
      const name = getEnumName(index);
      reportingNames.push(name);
    });
    returningReport += "Last visited screen enum names: " + JSON.stringify(reportingNames) + "\n";

    returningReport.split("\n").forEach(reportLine => { console.log(reportLine) });
    return returningReport;
  },
  getLastRevisitedRaw: function () {
    return this.lastVisited; // Eg. [-1,1,2] <-- 2 is the most recently visited via switchPanel function
  }
});


const { setQueryWithoutTriggeringPopstate, getPanel, switchPanel, resetUploadIframe } = navController.init();
window.setQueryWithoutTriggeringPopstate = setQueryWithoutTriggeringPopstate;
window.getPanel = getPanel;
window.switchPanel = switchPanel;
window.resetUploadIframe = resetUploadIframe;

/**
 * Walkthrough Modal
 * 
 */
// Workaround: Some web browsers automatically restart videos when they end, which is not the desired behavior. The video seemingly looped.
document.getElementById('walkthrough-video').addEventListener('ended', function () {
  this.pause();
  // Convenient for restart if the user wants to watch again
  video.currentTime = 0;
});

/**
 * White labeling agency
 * 
 * ?co=AGENCY_NAME php has already handled by the time this js file runs
 * So we reset the url so it doesn't have the ?co so it can look clean
 * However we also show the redirecting modal if it's the fist time logging in instead of opening the webpage to an active session
 */
var whitelabelAgencySystem = {
  init: function () {
    const hasBrandInUrl = location.search.includes("co=");
    if (!hasBrandInUrl) return;

    this._removeQueryParam('co');

    if (!localStorage.getItem("logo_redirect_showed_once_in_session")) {
      localStorage.setItem("logo_redirect_showed_once_in_session", 1);
      var el = document.createElement("div");
      el.className = "flex h-screen items-center justify-center bg-background fixed top-0 left-0 w-full bg-gray-400 bg-opacity-75";
      el.innerHTML = `
        <div class="bg-card bg-white dark:bg-card-foreground text-card-foreground dark:text-card p-16 shadow-lg mx-auto text-center" style="min-width:450px; border-radius:25px;">
          <!-- Placeholder logo -->
          <img src="${BRAND.LOGO}" alt="Placeholder Logo" class="mx-auto mb-4 mb-16 w-48 h-auto">
          <p class="text-lg">Redirecting to ${BRAND.NAME}'s portal...</p>
          <br/>
          <p>Please wait...</p>
          <div class="text-center">
          <i class="fas fa-spinner fa-spin fa-3x mt-8"></i>
          </div>
        </div>
      `;
      document.body.append(el);
      setTimeout(() => {
        el.remove();
      }, 4000);
    } // ! logo_redirect_showed_once_in_session
  },
  _removeQueryParam(key) {
    const url = new URL(window.location);

    if (url.searchParams.has(key)) {
      url.searchParams.delete(key);

      // Construct the new URL without the specified query parameter
      const newUrl = url.toString();

      // Replace the current history entry with the new URL
      window.history.replaceState({}, document.title, newUrl);

      // console.log(`Detected white labeling. Removed URL query parameter: ${key}`);
    } else {
      // console.log(`URL query parameter "${key}" not found.`);
    }
  } // _removeQueryParam
}
whitelabelAgencySystem.init()

var internetAccessDetector = {
  init: () => {
    // Detect online / offline
    window.addEventListener('online', () => {
      alert("Notice: Your internet is back online.")
    });

    window.addEventListener('offline', () => {
      alert("ERROR: Your internet has disconnected. If in the middle of uploading or processing a video (spinning icon), please re-attempt or click Back on your web browser.")
    });
  }
}
internetAccessDetector.init();

var secretMenu = {
  typedKeys: "",
  init: (secretPhrase, callback = null) => {
    // Track keys typed outside input and textarea
    // This isn't needed to be that secret, so it's okay to be on frontend
    const targetSequence = secretPhrase;

    document.body.addEventListener("keydown", (event) => {
      const activeElement = document.activeElement;

      // Ignore if the active element is an input or textarea
      if (activeElement.tagName === "INPUT" || activeElement.tagName === "TEXTAREA") {
        return;
      }
      // console.log(event.key);

      // Add the pressed key to the sequence
      this.typedKeys += event.key;

      // Trim the sequence to match the length of the target sequence
      if (typedKeys.length > targetSequence.length) {
        this.typedKeys = this.typedKeys.slice(-targetSequence.length);
      }

      // console.log(this.typedKeys);
      // console.log(targetSequence);

      // Check if the typed sequence matches the target
      if (this.typedKeys === targetSequence) {
        // console.log("Secret sequence detected!");
        // Add any action you want here
        // alert("Secret sequence detected!");
        if (callback) callback();
      }
    });
  }
}

// Experimental features / Secret menu
document.addEventListener("DOMContentLoaded", () => {
  secretMenu.init("secret", () => {
    $("#experimental-features").modal("show");
  });
});