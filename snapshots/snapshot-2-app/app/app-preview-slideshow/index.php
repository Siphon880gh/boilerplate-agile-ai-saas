<!-- Module type: iframe module -->
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slideshow Preview</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">

<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad
    <link rel="stylesheet" href="assets/index.css$v">
    <link rel="stylesheet" href="../assets/common.css$v">
    <script src="../assets/screens.js$v"></script>
cbust_ipad;
?>

    <?php $up=1; include("../assets--whitelabeler/brand-loader.php"); unset($up);?>

    <script class="config-phase">
      const pageMode = {
        MODES: {
          DEMO: 0,
          LIVE: 1,
        },
        currentMode: null,
      };

      // -> CONFIG HERE:      
      pageMode.currentMode = pageMode.MODES.LIVE;

        // TODO: Refactor into more reactive code
        // Operator:
        function operator(finalVideoURL) {
            // Preview video
            document.querySelector(".is-loading").classList.add("hidden");
            document.querySelector("#preview-video source").src = finalVideoURL+`?nocache=${Date.now()}`;
            document.querySelector("#preview-video").load();
            window.parent.playableMedias = [ document.querySelector("#preview-video") ];
            // Preview poster
            document.getElementById("dynamic-video-preview").classList.remove("hidden");
            document.querySelectorAll(".after-video-available").forEach(el=>{
                el.classList.remove("disabled")
                el.classList.remove("hidden")
                el.classList.remove("invisible")
            });

            document.getElementById("preview-video").addEventListener('ended', function() {
                document.getElementById("preview-video").load();     
            });
        }

        if( pageMode.currentMode === pageMode.MODES.DEMO ) {
            window.parent = {
                appModel: {}
            }
            document.addEventListener("DOMContentLoaded", () => {
                setTimeout(() => {
                    document.getElementById("action-btns").remove();
                    operator("demo/demo.mp4");
                }, 5000);
            });
        } else {
            function observable(subscriber) {
                if (window?.parent?.appModel?.finalVideo) {
                    operator(window.parent.appModel.finalVideo);
                    clearInterval(subscription1);
                    // Finished
                }

                // Pretend a backend server finishes generating a video in 5 seconds:
                setTimeout(() => {
                    window.parent.appModel.finalVideo = "demo/demo.mp4";
                }, 5000);
            }
            var subscription1 = setInterval(observable, 100)
        }


    </script>

</head>

<body class="flex flex-col items-center justify-center min-h-screen">
    

    <?php
    $step=7;
    include("../assets/steps.php");
    ?>

    <main class="space-y-10 w-full max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
        <!-- Increased width for emphasis on the video -->
        <!-- Video Title -->
        <h2 class="h2-brand text-center text-4xl font-bold text-gray-900">Slideshow Preview</h2>

        <div class="text-center bg-gray-200 border border-gray-500 rounded-lg shadow-md w-full relative p-10 mb-11">

            <div class="is-loading">
                <div class="flex flex-row justify-center">
                    <i class="text-2xl fas fa-spinner fa-spin loading-spinner"></i>
                </div>
                <div class="font-bold textcolor-brand mt-3">
                    <span>Creating your slideshow now...</span>
                    <div class="mt-2"></div>
                </div>

                <div class="mt-4">
                    <div id="tip-div"></div>
                </div>
            </div>

            <!-- Video container with thumbnail and play button -->
            <div id="dynamic-video-preview" class="relative group hidden">
                <!-- Video -->
                <div id="video-container" class="absolute-off inset-0-off flex items-center justify-center">
                    <video id="preview-video" width="100%" height="auto" controls>
                        <source src="" type="video/mp4">
                        Your browser does not support the video tag
                    </video>
                </div>
            </div>

            <div class="block text-center flex flex-row justify-between text-xl after-video-available mt-4 disabled hidden">
                <div class="flex flex-row flex-start text-xl">
                    <div class="border border-black border-solid p-1 clickable text-gray-600 hoverable mr-4" onclick="window.parent.shareController.copyVideoLink('https://YOUR_DOMAIN/'+window.parent.appModel.finalVideo);">
                        <i class="fab fa-facebook"></i>
                        <span>|</span>
                        <i class="fab fa-instagram"></i>
                    </div>

                    <div class="border border-black border-solid p-1 clickable text-gray-600 hoverable mr-2" onclick="window.parent.shareController.shareToText('https://YOUR_DOMAIN/'+window.parent.appModel.finalVideo);">
                        <i class="fas fa-sms"></i>
                    </div>

                    <div class="border border-black border-solid p-1 clickable text-gray-600 hoverable mr-2" onclick="window.parent.shareController.shareToGmail('https://YOUR_DOMAIN/'+window.parent.appModel.finalVideo);">
                        <img src="../assets/icons/gmail-logo.png" style="margin-top:2.5px; width:20px; height:20px; opacity:0.65;">
                    </div>

                    <div class="border border-black border-solid p-1 clickable text-gray-600 hoverable mr-2" onclick="window.parent.shareController.shareToEmail('https://YOUR_DOMAIN/'+window.parent.appModel.finalVideo);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    
                    
                </div>
                <div class="flex flex-row flex-end">
                    <div class="clickable text-gray-600 hoverable" onclick="event.preventDefault(); if(!this.className.includes('disabled')) requestDownloadModal()" class-old="btn-brand-primary preview-video-btn">
                        <i class="fa fa-download"></i>
                    </div>
                </div>
            
            </div>
            </div>
            

            <div id="action-btns" class="block text-center text-xl p-4 relative after-video-available after-video-available disabled invisible">

                <!-- Action Wrapper -->
                <div class="shadow-sm border-t border-gray-200 p-10">
                    <!-- Action Header -->
                    <p class="text-gray-700 text-2xl font-black mb-2">Create More:</p>

                    <!-- Action Buttons -->
                    <div id="info-action-btns-body" class="flex flex-wrap justify-center items-start align-start gap-6">
                        <!-- - Create New Video -->
                        <button class="btn-brand-secondary py-2 px-4 rounded text-md" onclick="pauseAllVideos(); window.parent.location.reload();">
                            Start New Video
                        </button>
                    </div>
                </div>

                <button class="inline-flex items-center bg-gray-300 text-white hover:text-gray-300 hover:bg-gray-400 text-md px-4 py-2 mb-8 rounded-lg border border-gray-300 transition-all duration-200 no-underline" onclick="pauseAllVideos(); window.parent.location.reload();"  style="margin-top: 66px">
                    ← Visit Home
                </button>
            </div>

        </div>
    </main>

    <!-- Modal -->
    <!-- Modularized. Trigger with: $("#downloadModal").modal("show") -->
    <div class="modal fade transition duration-300 ease-in-out hover:text-black" id="downloadModal" tabindex="-1" role="dialog" aria-labelledby="downloadModalLabel">
        <div class="modal-dialog p-2.5 bg-gray-300 SPECIAL mt-20vh scale-1.5" role="document">
            <div class="modal-content pb-6">
                <div class="modal-header">
                    <h4 class="modal-title" id="downloadModalLabel">Download</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="position:absolute; top:10px; right:10px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body flex flex-column justify-center align-items-center">
                    
                    <div class="flex flex-row flex-nowrap">
                        <div class="mt-6 mb-6 text-gray-600 clickable hoverable cursor-pointer" onclick="downloadFromLink($('#sharePreview').val().trim());">
                            <i class="fa fa-download text-center"></i>
                            <span> Preview Thumbnail</span>
                            <input id="sharePreview" class="form-control" rows="3" readonly style="width:355px; display:none;"></input>
                        </div>
                    </div>
                    <div class="flex flex-row flex-nowrap">
                        <div class="mb-6 text-gray-600 clickable hoverable cursor-pointer" onclick="downloadFromLink($('#shareSnippet').val().trim());">
                            <i class="fa fa-download text-center"></i>
                            <span> Video</span>
                            <textarea id="shareSnippet" class="form-control" rows="3" readonly style="width:355px; display:none;"></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div> <!-- Modal -->

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>

<?php
echo <<<cbust_ipad
    <script src="assets/index.js$v"></script>
    <script src="assets/tips.js$v"></script>
cbust_ipad;
?>

</body>

</html>