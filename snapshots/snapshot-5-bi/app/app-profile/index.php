<!-- Module type: iframe module -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">

<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad
    <script src="../assets/common.js$v"></script>
    <link href="../assets/common.css$v" rel="stylesheet">
    <link rel="stylesheet" href="../assets/toggle-switch.css$v">
    <script src="../assets/screens.js$v"></script>
    <link rel="stylesheet" href="assets/index.css$v">
cbust_ipad;
?>

    <?php $up=1; include("../assets--whitelabeler/brand-loader.php"); unset($up);?>

    <!-- Add Cropper.js CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

    <style>
        :root {
            /* 1:1 aspect ratio */
            --aspect-ratio-1-1-width: 242px;
            --aspect-ratio-1-1-height: var(--aspect-ratio-1-1-width);

            /* 1.91:1 aspect ratio */
            --aspect-ratio-191-1-width: 242px;
            --aspect-ratio-191-1-height: var(--aspect-ratio-191-1-width);

            /* 4:3 aspect ratio */
            --aspect-ratio-4-3-width: 242px;
            --aspect-ratio-4-3-height: calc(var(--aspect-ratio-4-3-width) * 3 / 4);
            
            /* 4:5 aspect ratio */
            --aspect-ratio-4-5-width: 242px;
            --aspect-ratio-4-5-height: calc(var(--aspect-ratio-4-5-width) * 5 / 4);

            /* 16:9 aspect ratio */
            --aspect-ratio-16-9-width: 242px;
            --aspect-ratio-16-9-height: calc(var(--aspect-ratio-16-9-width) * 9 / 16);

            /* 9:16 aspect ratio */
            --aspect-ratio-9-16-width: 242px;
            --aspect-ratio-9-16-height: calc(var(--aspect-ratio-9-16-width) * 16 / 9);

            /* 3:2 aspect ratio */
            --aspect-ratio-3-2-width: 242px;
            --aspect-ratio-3-2-height: calc(var(--aspect-ratio-3-2-width) * 2 / 3);

            /* 2:3 aspect ratio */
            --aspect-ratio-2-3-width: 242px;
            --aspect-ratio-2-3-height: calc(var(--aspect-ratio-2-3-width) * 3 / 2);

            /* CONFIG HERE */
            --aspect-ratio-width: var(--aspect-ratio-4-5-width);
            --aspect-ratio-height: var(--aspect-ratio-4-5-width);
        }

        #profile-pic-upload {
            width: var(--aspect-ratio-width);
            height: var(--aspect-ratio-height);
        }

        #cropper-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
        }

        #cropper-modal .cropper-content {
            position: relative;
            background: black;
            padding: 2rem;
            border-radius: 0.5rem;
            max-width: 36rem;
            width: 90%;
            margin: 2rem auto;
            max-height: 90vh;
            overflow: visible;
            display: flex;
            flex-direction: column;
        }

        .cropper-container {
            max-height: 60vh !important;
        }

        .cropper-wrap-box {
            background-color: black !important;
        }

        .cropper-view-box,
        .cropper-face {
            border-radius: 0;
        }

        .cropper-modal {
            background-color: black !important;
            opacity: 0.8;
        }

        #cropper-area {
            background-color: black;
            overflow: hidden;
            max-height: 60vh;
        }

        .cropper-bg {
            background-color: black !important;
        }

        .cropper-drag-box {
            background-color: black !important;
        }
        
        #profile-pic-upload[empty="false"] #deletePhotoPic {
            display: block !important;
        }

        #profile-pic-upload[empty="true"] #deletePhotoPic {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen">

    <h2 class="h2-brand">Edit Profile</h2>

    <div class="text-center my-8 mx-auto" style="max-width:400px;">
        <p>Review your user information or delete account.</p>
    </div>

    <div class="flex items-center justify-center">

        <div class="bg-white shadow-md rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">Edit Profile</h2>
            <form id="editProfileForm">
                <!-- Add Profile Picture Section -->
                <div class="mb-6 text-center">
                    <label for="profile-pic" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                    <div id="profile-pic-upload" class="relative bg-gray-200 rounded-lg overflow-hidden mb-4 mx-auto" empty="true">
                        <img
                            id="preview-img"
                            empty="true"
                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 21v-2a4 4 0 0 0-4-4H8 a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='12' cy='7' r='4'/%3E%3C/svg%3E"
                            alt="Profile preview"
                            class="w-full h-full object-cover"
                        />
                        <div class="upload-overlay absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center text-white opacity-0 hover:opacity-100 transition-opacity">
                            <input type="file" id="profile-pic" accept="image/*" class="hidden">
                            <button 
                                type="button" 
                                class="bg-white text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-100 mb-2"
                                onclick="document.getElementById('profile-pic').click()"
                            >
                                Upload Photo
                            </button>
                            <button 
                                type="button" 
                                class="text-red-400 hover:text-red-500"
                                id="deletePhotoPic"
                            >
                                Delete Photo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Email (Login) -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2 text-center text-center">Email (Login)</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center" 
                        placeholder="Enter your email"
                        readonly>
                </div>

                <!-- Email - Confirm -->
                <!-- <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700">Confirm your email</label>
                    <input 
                        type="email" 
                        id="email-confirm" 
                        name="email-confirm" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-500 bg-gray-100" 
                        placeholder="Confirm your email">
                </div> -->

                <div class="mt-6"></div>

                <!-- Full Name -->
                <div class="mb-6">
                    <label for="fullName" class="block text-sm font-medium text-gray-700 mb-2 text-center">Full Name</label>
                    <input 
                        type="text" 
                        id="fullName" 
                        name="fullName" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center" 
                        placeholder="Enter your full name" 
                        required>
                </div>

                  <!-- Newsletter Subscription -->
                  <div class="mb-6">
                    <div class="flex items-center justify-center gap-2">
                      <label for="newsletter" class="text-sm font-medium text-gray-700">Subscribe to Newsletter</label>
                      <label class="inline-flex items-center cursor-pointer relative">
                        <input id="newsletter" type="checkbox" class="toggle-checkbox sr-only peer">
                        <div class="toggle-slider" style="filter: drop-shadow(1px 2px 2px rgba(125, 125, 125, 0.5));"></div>
                      </label>
                    </div>
                  </div>

                  <div class="text-center mt-10">
                    <div id="advanced-mode-container" class="max-w-md mx-auto border rounded-lg p-4">
                        <p class="mr-2">Turn on Advanced Mode:</p>  
                        <label class="inline-flex items-center cursor-pointer relative p-2 mb-2 rounded-lg w-full justify-center">  
                            <input id="advanced-mode-checkbox" type="checkbox" class="toggle-checkbox sr-only peer" onclick="toggleAdvancedMode(event.target.checked)">
                        <div class="toggle-slider" style="filter: drop-shadow(1px 2px 2px rgba(125, 125, 125, 0.5));"></div>
                        <span class="ml-3 text-sm font-medium text-gray-900"></span>
                        </label>
                        <div class="mt-2 text-sm text-gray-700 text-center ">
                        <p class="font-bold mb-4">Advanced Mode enables:</p>
                        <ul class="list-disc list-inside space-y-1 text-center">
                            <li>Upload URL as content for slideshow</li>
                        </ul>
                        </div>
                    </div>
                    </div>

                  <div class="flex justify-center mt-7">
                    <button type="button" id="deleteProfile" class="text-red-400">
                        Delete Account
                    </button>
                  </div>
              </section>

                <!-- Buttons -->
                <div class="mt-6"></div>
                <div class="flex justify-center items-center gap-8">
                    <button onclick="window.history.go(-1)" class="btn-brand-secondary text-white px-4 rounded border-0" style="transform:scale(0.9); width:110px;">Back</button>
                    <button type="submit" id="saveProfile" class="btn-brand-primary" style="width:110px;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Cropper Modal -->
    <div id="cropper-modal" class="fixed inset-0 bg-black bg-opacity-80 z-100 flex items-center justify-center" style="display: none;">
        <div class="cropper-content relative bg-white p-4 rounded-lg mx-auto overflow-scroll">
            <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" onclick="closeCropperModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div id="cropper-area" class="w-full" style=""></div>
            <!-- Add zoom controls -->
            <div class="flex justify-center gap-4 mt-4">
                <button type="button" id="zoom-in" class="bg-gray-200 hover:bg-gray-300 rounded-full p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </button>
                <button type="button" id="zoom-out" class="bg-gray-200 hover:bg-gray-300 rounded-full p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>
            </div>
            <div class="btn-wrapper my-4">
                <button type="button" id="crop-btn" class="btn-brand-primary w-full p-3 text-white font-medium">
                    Crop & Save
                </button>
            </div>
        </div>
    </div>

<?php
echo <<<cbust_ipad
    <script src="assets/index.js$v"></script>
cbust_ipad;
?>

</body>
</html>
