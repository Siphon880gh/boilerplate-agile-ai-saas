// imported `finalHost` from `app-dashboard/index.php` which sourced app root's `assets/common.js`
// imported `data` from `app-dashboard/index.php` at code: var data = `<?php echo $data; ?>`;

window.widthClassNormal = 'w-32'; // 32, 64, 96
window.heightClassNormal = 'h-32';
window.widthClassLarge = 'w-40'; // 32, 64, 96
window.heightClassLarge = 'h-40';
window.widthRem = '8rem'; // 8rem, 16rem, 24rem, 48rem, 72rem

function handleEditVideo(event) {
    var imageContainer = event.target.closest(".image-container");
    var caseId = imageContainer.getAttribute("data-case-id");
    caseId = parseInt(caseId);
    var windowParent = window.parent;
    windowParent.appModel.caseId=caseId; 
    windowParent.navController.switchPanel(SCREENS.EditCase);
    windowParent.navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.EditCase);
    pauseAllVideos();
}

function pauseAllVideos() {
    document.querySelectorAll("video").forEach(vid=>vid.pause())
}

document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams({
        appId: window.parent.appModel.appId,
        userId: window.parent.appModel.userId,
    });

    console.group("Dashboard");
    console.log(data);
    console.groupEnd();
    // data = JSON.parse(data);

    const totalCredits = data.totalCredits;
    const incompleteVideos = data.resumableVideos;
    let completedVideos = data.prevVideos;

    let creditsThumbnailsContainer = document.getElementById('credits-thumbnails');
    let videosThumbnailsContainer = document.getElementById('completed-videos-gallery');
    let resumableThumbnailsContainer = document.getElementById('resumable-thumbnails');

    const startCase = () => {
        pauseAllVideos();
        if(window.parent?.resumingModelAIPrompt) delete window.parent?.resumingModelAIPrompt;

        window.parent.preinitCase(-1, ()=>{
            window.parent.appModel.finalVideo = ""; // Resets the observable that the subscriber sees in order to signal that the video finished generating
            window.parent.navController.switchPanel(SCREENS.ReadInstructions);
            window.parent.navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.ReadInstructions);
        })
    } // startCase

    // Generate credits thumbnails
    if(!totalCredits || totalCredits.length <= 0) {
        // document.getElementById("#available-credits").remove();
        var div = document.createElement('div')
        div.className = "text-xl";
        div.innerHTML = `
            No credits available. Please <a class="bg-gray-300" target="_blank" href="#">buy more credits / upgrade / subscribe</a> to create new videos.
        `
        document.getElementById("available-credits").append(div);
    } else {
        var cappedGui = 1; // Was 20 but we're capping it to 1 for visual simplicity
        for (let i = 0; i < Math.min(totalCredits, cappedGui); i++) {
            const thumbnail = document.createElement('div');
            thumbnail.className = 'relative bg-gray-300 flex justify-center items-center image-container';
            thumbnail.classList.add(widthClassLarge, heightClassLarge);
            thumbnail.style.minWidth = widthRem;
            const plusSign = document.createElement('span');
            plusSign.className = 'text-4xl text-gray-600';
            plusSign.innerText = '+';
            thumbnail.appendChild(plusSign);
            thumbnail.onclick = startCase;
            creditsThumbnailsContainer.appendChild(thumbnail);
        }
    }

    // Generate resumable videos thumbnails
    if(!incompleteVideos || incompleteVideos.length === 0)
        document.getElementById("resumable-videos").remove();
    else {
        incompleteVideos.forEach(video => {
            const thumbnail = document.createElement('div');
            thumbnail.className = 'relative image-container';
            const img = document.createElement('img');
            img.src = "assets/img/placeholder-150x150.png";
            img.classList.add(widthClassNormal, heightClassNormal);
            img.className = 'w-full object-cover';
            img.style.minWidth = widthRem;
            thumbnail.appendChild(img);

            const restartButton = document.createElement('button');
            restartButton.className = 'absolute inset-0 flex justify-center items-center text-white text-2xl fas fa-times-circle text-red-200 opacity-80';
            restartButton.setAttribute("data-case-id", video.caseId);
            restartButton.onclick = () => {
                var yes = confirm("A video was not done generating. Let's reclaim that credit to start a new video.");

                if(yes) {
                    pauseAllVideos();
                    window.parent.appModel.finalVideo = ""; // Resets the observable that the subscriber sees in order to signal that the video finished generating
                    window.parent.document.getElementById("iframe-preview-slideshow").src=""; // If second session, reset the previous iframe that's just hidden so old value resets
                    window.parent.document.getElementById("iframe-read-instructions").src=window.parent.document.getElementById("iframe-read-instructions").src; // If second session, reset the previous iframe that's just hidden so old value resets
                    window.parent.preinitCase(video.caseId, ()=>{
                        window.parent.navController.switchPanel(SCREENS.ReadInstructions);
                        window.parent.navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.ReadInstructions);
                    })
                }

                // TODO: Implement restart video at a current case number
            };
            thumbnail.appendChild(restartButton);
            resumableThumbnailsContainer.appendChild(thumbnail);
        });
    }

    // Generate previous videos thumbnails
    if(!completedVideos || completedVideos.length === 0)
        document.getElementById("completed-videos").remove();
    else {

        // Change view button
        const changeViewBtn = document.getElementById("change-completed-videos-view");
        changeViewBtn.onclick = () => {
            document.getElementById("completed-videos-gallery").classList.toggle("grid-toggler-on");
        }

        // Add search functionality
        const searchInput = document.getElementById('completed-videos-search');
        searchInput.addEventListener('input', function() {
            const queryLC = searchInput.value.toLowerCase();
            const videoThumbnails = document.querySelectorAll('#completed-videos-gallery .image-container');

            videoThumbnails.forEach(thumbnail => {
                const topLines = thumbnail.querySelector('.completed-date-status');
                if (topLines) {
                    const topLinesLC = topLines.textContent.toLowerCase();
                    if (topLinesLC.includes(queryLC)) {
                        thumbnail.style.display = ''; // Show the thumbnail
                    } else {
                        thumbnail.style.display = 'none'; // Hide the thumbnail
                    }
                }
            });
        });
        
         // For user to manage completed videos
        completedVideos.sort((a, b) => {
            return a.createdUnixTime - b.createdUnixTime;
        });

        completedVideos.forEach(video => {
            const thumbnail = document.createElement('div');
            thumbnail.className = 'image-container relative ';
            thumbnail.setAttribute("data-case-id", video.caseId);

            const img = document.createElement('img');

            // Try to use video thumbnail if available, fallback to jpg
            const videoUrl = "../" + video.finalVideo;
            const video_el = document.createElement('video');
            video_el.src = videoUrl;
            video_el.addEventListener('loadeddata', function() {
                video_el.currentTime = 1; // Seek to 1 second
            });
            video_el.addEventListener('seeked', function() {
                // Create canvas and draw video frame
                const canvas = document.createElement('canvas');
                canvas.width = video_el.videoWidth;
                canvas.height = video_el.videoHeight;
                canvas.getContext('2d').drawImage(video_el, 0, 0);
                img.src = canvas.toDataURL();
            });
            // Fallback to jpg if video fails
            video_el.addEventListener('error', function() {
                img.src = videoUrl.replace(".mp4", ".jpg");
            });
            

            const imgWrapper = document.createElement('div');
            imgWrapper.className = "relative";

            img.classList.add(widthClassNormal, heightClassNormal);
            img.className = 'w-full object-cover';
            img.style.minWidth = widthRem;
            img.onclick = (event) => {
                // Only one bordered previous video thumbnail
                document.querySelectorAll("#completed-videos .image-container relative active").forEach(el=>{el.classList.remove("active");});       
                event.currentTarget.closest(".image-container").classList.add("active");

                // Close all option menus
                document.querySelectorAll(".completed-options:not(.hidden)").forEach(el=>el.classList.add("hidden"))

                // Reset video preview
                document.getElementById("preview-video-wrapper").classList.remove("hidden");
                const videoElement = document.getElementById('preview-video'); // <video> tag
                videoElement.src = "../" + video.finalVideo;
                videoElement.controls = true;
                videoElement.autoplay = true;

                videoElement.addEventListener('click', () => {
                    if (videoElement.paused) {
                        videoElement.play();
                    } else {
                        videoElement.pause();
                    }
                }, {passive: true});
                
                // Scroll to video
                setTimeout(() => {
                    document.getElementById('preview-video-wrapper').scrollIntoView({behavior: 'smooth'});
                }, 100);
            };
            imgWrapper.appendChild(img);

            // Play Button
            const playButton = document.createElement('button');
            playButton.className = 'absolute w-fit h-fit top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 inset-0 text-white text-4xl text-stroke-black opacity-50 hover:opacity-80';
            playButton.innerHTML = '&#9658;';
            playButton.onclick = (event) => {
                event.currentTarget.parentElement.classList.add("active");
                event.currentTarget.parentElement.querySelector("img").dispatchEvent(new Event("click"));
            };
            imgWrapper.appendChild(playButton);
            
            thumbnail.appendChild(imgWrapper);


            // Options Button ("+")
            const optionsButton = document.createElement('div');
            optionsButton.className = "completed-options-btn-wrapper w-full";
            optionsButton.innerHTML = `
                <button class="completed-options-btn flex justify-center items-center gap-2 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-sm shadow hover:bg-gray-300 transition duration-200 w-full">
                    <i class="fa fa-bars completed-options-icon"></i>
                    <span class="completed-options-label">Menu</span>
                </button>
            `;


            // Options Menu
            const optionsMenu = document.createElement('div');
            optionsMenu.className = 'mt-2 bg-white shadow-lg rounded border completed-options hidden'; // hidden by default
            optionsMenu.style.minWidth = '120px';

            const options = [
                { 
                    text: `
                        <div class="relative">
                            Share »
                            <div class="absolute left-full top-0 mt-0 ml-2 bg-white shadow-lg rounded hidden share-submenu" style="width:150px; z-index:200;">
                                <button class="block w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 transition-colors duration-200 ease-in-out" onclick="window.parent.shareController.shareToText('https://YOUR_DOMAIN/' + video.finalVideo)">Share to Text</button>
                                <button class="block w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 transition-colors duration-200 ease-in-out" onclick="window.parent.shareController.shareToGmail('https://YOUR_DOMAIN/' + video.finalVideo)">Share to Gmail (web)</button>
                                <button class="block w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 transition-colors duration-200 ease-in-out" onclick="window.parent.shareController.shareToEmail('https://YOUR_DOMAIN/' + video.finalVideo)">Share to Email (app)</button>
                            </div>
                        </div>
                    `,
                    hover: (event) => {
                        const submenu = event.target.querySelector('.share-submenu');
                        submenu?.classList?.toggle('hidden');
                    }
                },
                { text: '#HR#' },
                { text: 'Copy Video Link', action: (event) => { window.parent.shareController.copyVideoLink("https://YOUR_DOMAIN/" + video.finalVideo); }},
                { text: 'Download', action: (event) => { 
                    var $downloadModal = $(window.parent.document.querySelector("#downloadModal"));

                    $downloadModal.find(".download-video").attr("data-url", video.finalVideo)
                    
                    setTimeout(()=>{
                        $downloadModal.modal("show");
                    }, 100);
                }},
                
                { text: '#HR#' },

                // Edit case
                { 
                    text: `
                        <div class="flex flex-row justify-start items-center gap-3">
                            <span>Edit</span>
                        </div>
                    `, 
                    addClasses: "",
                    action: (event) => { 
                        handleEditVideo(event);
                     }
                },

                { text: `<div class="text-red-400">Delete ⤬</div>`, action: (event) => { 
                    var confirmed = confirm("Are you sure you want to delete this video?");
                    if(confirmed) {
                        const payload = {
                            caseId: video.caseId,
                            userId: window.parent.getUserId()
                        }
                        fetch(finalHost + "/profile/cases", {
                            method: "DELETE",
                            headers: {
                              'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(payload)
                          }).then(response => response.json())
                            .then(resource => {
                                console.log("Deleting video case:")
                                console.log(resource);
                                document.querySelector(`#completed-videos .image-container[data-case-id="${video.caseId}"]`).remove();

                                // Hide any video previewing in case it's the one you just deleted
                                document.getElementById("close-preview-video").click();
                            });
                    } // if user confirmed

                }}
            ];

            options.forEach(option => {
                var optionItem = null;

                // Create element
                if(option.text.includes("#HR#")) {
                    optionItem = document.createElement('hr');
                } else {
                    optionItem = document.createElement('button');
                }

                // Add classes, text, and event listeners
                if(option?.addClasses && option.addClasses.length > 0) {
                    console.log("addClasses: " + option.addClasses);
                    optionItem.className += (" " + option.addClasses);
                }

                if(option.text.includes("#HR#")) {
                    optionItem.className += " border-t border-gray-200";
                } else {
                    optionItem.className += " block w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 transition-colors duration-200 ease-in-out";
                    optionItem.innerHTML = option.text;
                    if(option.action) {
                        optionItem.onclick = (event) => {
                            event.stopPropagation(); // Prevent other click events
                            option.action(event);
                            optionsMenu.classList.add("hidden"); // Hide menu after selecting
                        };
                    }
                    if(option.hover) {
                        optionItem.onclick = (event) => {
                            event.stopPropagation(); // Prevent other click events
                            option.hover(event);
                            // optionsMenu.classList.add('hidden'); // Hide menu after selecting
                        };
                    }
                }
                optionsMenu.appendChild(optionItem);
            });

            // Append options button and menu to thumbnail
            optionsButton.append(optionsMenu);
            thumbnail.appendChild(optionsButton);

            // Completed date status
            video.createdUnixTime = video.createdUnixTime ? new Date(video.createdUnixTime * 1000).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' }).replace(', ', '<br>') : "";

            const completedDateStatus = document.createElement('div');
            completedDateStatus.className = "completed-date-status w-full";
            completedDateStatus.innerHTML = `
                <div class="flex justify-center items-center text-center gap-2 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-sm shadow hover:bg-gray-300 transition duration-200 w-full">
                    ${video.createdUnixTime || "&nbsp;<br/>&nbsp;"}
                </div>
            `
            thumbnail.prepend(completedDateStatus);

            videosThumbnailsContainer.prepend(thumbnail);
        }); // forEach completedVideo

          document.body.addEventListener("click", event => {
            event.preventDefault();
            event.stopPropagation(); // Prevent triggering other events on the thumbnail

            // Look ahead who we will open if applicable
            var weWillOpenOptions = false;
            var whoWeAreOpening = null;
            if(event.target.matches(".completed-options-btn") || event.target.matches(".completed-options-icon")  || event.target.matches(".completed-options-label")) {
                // Toggle options menu visibility
                var imageContainer = event.target.closest(".image-container");
                var optionsMenu = imageContainer.querySelector(".completed-options");
                var isClosed = optionsMenu.className.includes("hidden");
                
                if(isClosed) {
                    whoWeAreOpening = optionsMenu;
                    weWillOpenOptions = true;
                }
                
                // Close previous options menus
                document.querySelectorAll(".completed-options:not(.hidden)").forEach(el=>el.classList.add("hidden"));
                
                // Open if applicable
                if(weWillOpenOptions) {
                    // Animate hamburger icon rotating
                    setTimeout(() => {
                        whoWeAreOpening.classList.remove("hidden")
                    }, 250);
                }
            } // if was toggle options button
            
            if(event.target.matches("#close-preview-video")) {
                document.getElementById("preview-video-wrapper").classList.add("hidden");
                document.getElementById("preview-video").pause();
            }

        }); // body click


    } // Completed videos rendered

    // A more welcoming experience when no past history of videos (incomplete or completed)
    if((!incompleteVideos || incompleteVideos.length === 0) && (!completedVideos || completedVideos.length === 0)) {
        document.getElementById("available-credits").remove();
        const onboardPanel = document.getElementById("onboarder")
        onboardPanel.classList.remove("d-none");
        onboardPanel.onclick = startCase;
    }

}); // DOMContentLoaded