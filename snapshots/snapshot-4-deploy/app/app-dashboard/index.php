<!-- Module type: iframe module -->
<?php
    session_start();
    ob_start();
    include("get-access-data.php");
    // Capture the output
    $output = ob_get_clean();

    // Check if the output contains the word "ERROR"
    if (strpos($output, "ERROR") !== false) {
        die("<script class='js-php'>window.parent.authController.logout()</script>");
    } else {
        echo $output;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Credits</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">

<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad
    <script src="../assets/common.js$v"></script>
    <link href="../assets/common.css$v" rel="stylesheet">
    <script src="../assets/screens.js$v"></script>
    <link href="./assets/index.css$v" rel="stylesheet">
cbust_ipad;
?>

    <?php $up=1; include("../assets--whitelabeler/brand-loader.php"); unset($up);?>

    <script class='js-php'>
        var data = `<?php echo $data; ?>`;
        data = JSON.parse(data);
    </script>

</head>

<body class="text-gray-900 flex justify-center items-center min-h-screen-off">
    <br/><br/>
    <div class="container mx-auto">
        <div class="relative min-h-screen">
            <div id="available-credits" class="mb-8">
                <h2 class="text-2xl font-extrabold mb-4">Create New<span>&nbsp;</span><span class="text-lg font-normal text-gray">Available Credits</span></h2>
                    <div class="flex flex-nowrap space-x-4 overflow-x-scroll" id="credits-thumbnails">
                        <!-- Credits Thumbnails will be injected here -->
                    </div>
                </div>
                <div class="absolute-off bottom-40 w-full unless-small-height">
                    <!-- Resumable Videos -->
                    <div id="resumable-videos" class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4">Unfinished/Incomplete Cases<span>&nbsp;</span><span class="text-lg font-normal text-gray">Extra available credits</span></h2></h2>
                        <!-- <h2 class="text-2xl font-semibold mb-4">Reclaim Credits<span>&nbsp;</span><span class="text-lg font-normal text-gray">Incomplete Videos</span></h2> -->
                        <div class="flex flex-nowrap space-x-4 overflow-x-scroll" id="resumable-thumbnails">
                            <!-- Resumable Videos Thumbnails will be injected here -->
                        </div>
                    </div>
                    <!-- Previous Videos -->
                    <div id="completed-videos" style="overflow-x:scroll;">
                        <h2 class="text-2xl font-semibold mb-4">Finished Cases</h2>
                        
                                                
                        <div id="completed-videos-global-options" class="mb-2 p-2 flex justify-end bg-gray-100 border w-full">
                            
                            <div class="flex flex-nowrap gap-1">
                                <button id="change-completed-videos-view" 
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded inline-flex items-start content-start">
                                    <i class="fas fa-th mr-2 pt-1"></i>
                                    <span class="font-black">Change View</span></span>
                                </button>
                                <div class="relative">
                                    <div class="flex items-start content-start hover:bg-gray-200 rounded">
                                        <i class="fas fa-search text-gray-500 mt-2 ml-3 mr-3 pt-1"></i>
                                        <input type="text" 
                                            id="completed-videos-search" 
                                            class="bg-transparent text-gray-800 py-2 px-4 rounded-l-none"
                                            placeholder="Search video"
                                            style="min-width: 200px;">
                                    </div>
                                </div>
                            </div>
                        </div> <!-- completed-videos-global-options -->

                        <div id="completed-videos-gallery" class="grid-toggler" style="overflow-x:scroll;">
                            <!-- Completed Videos Gallery will be injected here -->
                        </div>

                        <a class="hidden" id="info-programmatic-downloader-vid" href="#"></a>
                        <a class="hidden" id="info-programmatic-downloader-pic" href="#" target="_blank"></a>
                    </div>
                </div>
                
                <div id="preview-video-wrapper" class="flex flex-col hidden mt-8 mb-8 relative" style="justify-content:center; align-items:center; align-content:center;">
                    <button id="close-preview-video" class="absolute top-0 right-0 text-2xl transition-colors hover:text-red-400 rounded-full cursor-pointer">⤬</button>
                    <h2 class="text-xl font-bold mb-4">Replay Previous</h2>
                    <video id="preview-video" width="100%" height="auto" controls>
                        <source src="" type="video/mp4">
                        Your browser does not support the video tag
                    </video>
                </div>

                <br/><br/>

                
                <div id="onboarder" class="flex flex-col items-center justify-center h-screen bg-background text-foreground cursor-pointer d-none"
                     style="margin-top:-10%">
                    <h2 class="text-xl font-bold h2-brand" style="margin-bottom:5%;">Welcome!</h2>
                    <h2 class="textcolor-brand text-xl font-normal" style="line-height: 1.5rem;">Click here to</h2>
                    <div class="border-brand-2-o80 rounded-lg flex flex-col justify-center items-center p-8 mt-6">
                        <h1 class="textcolor-brand font-bold text-4xl mt-1">Create now</h1>
                        <button class="textcolor-brand rounded-md mt-2">
                            <i class="fas fa-plus text-4xl"></i>
                        </button>
                    </div>
                </div>

        </div> <!-- min-h-screen -->

    </div> <!-- container -->

<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad
    <script src="./assets/index.js$v"></script>
cbust_ipad;
?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
    integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
    crossorigin="anonymous"></script>

</body>

</html>
