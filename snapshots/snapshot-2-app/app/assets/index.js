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
} // shareController

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

    navController.switchPanel(SCREENS.UploadFiles, true)
    navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.UploadFiles);
  } // saveAIPrompt

} // mainController

const { resetCornerStatuses, pauseAllPossVideos } = mainController.init();
window.resetCornerStatuses = resetCornerStatuses;
window.pauseAllPossVideos = pauseAllPossVideos;


// NavController
var navController = {
  beforePopstate: -1,
  init: function () {
    window.addEventListener("popstate", function (e) {

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

        } // if navigated


        if ((navigated + "").includes("_")) {
          var [panelNum, state] = navigated.split("_");
          navigated = panelNum;
        }
        if (parseInt(navigated) === SCREENS.PreviewSlideshow && this.lastVisited.length > 2) {
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

      e.preventDefault();

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

  }
} // navController


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

/* Experimental features / Secret menu */
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
document.addEventListener("DOMContentLoaded", () => {
  secretMenu.init("secret", () => {
    $("#experimental-features").modal("show");
  });
});