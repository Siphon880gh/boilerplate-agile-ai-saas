
  <!-- Modal Login -->
  <div class="modal fade modal-lg" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel">
    <div class="modal-dialog responsive-fix-login">
      <div class="modal-content" style="max-height:90vh; overflow-y:scroll;">
        <div class="modal-body">

          <h2 class="h2-brand">LOGIN</h2>

          <div class="row">
            <div class="col-12">
              <section class="input-group">
                <label class="block text-gray-700 font-bold" for="email-login">
                  Email
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight"
                  id="email-login" type="email">
              </section>
            </div>
          </div>

          <section class="col-12 mb-4">
            <div class="w-full">
              <label class="block text-gray-700 font-bold mx-auto" for="password-login">
                Password
              </label>
            </div>
            <div class="input-group">
            <div class="flex flex-row w-full">
              <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight w-full" id="password-login" type="password">
              <i class="fas fa-eye icon text-xl px-2 content-center" onclick="toggleLoginPassword();"></i>
            </div>
          </div>
        </section> <!-- col-12 -->

          <div class="float-end">
            <?php
              if($env_guest_mode == 1) {
                echo '<button id="try-demo-btn" class="btn-brand-secondary bg-gray-500 hover:bg-gray-700" data-bs-dismiss="modal" type="button" onclick="authController.signupAsGuest()" style="transform: scale(.9);">
                        Try Demo
                      </button>';
              }
            ?>
            <button class="btn-brand-primary" type="button" onclick="event.preventDefault(); event.stopPropagation(); authController.login()">
              LOGIN
            </button>
          </div>

        </div>
        <div class="modal-footer justify-between">
          <a href="javascript:void(0)" class="font-medium no-underline" data-bs-toggle="modal"
            data-bs-target="#signupModal">Sign up</a>
          <a href="javascript:void(0)" class="text-gray-600 no-underline"
            onclick="alert('Please contact EMAIL_HERE')">Forgot
            password</a>
        </div>
      </div>
    </div>
  </div>
  </div> <!-- Login Modal -->

  <!-- Signup Modal -->
  <div class="modal modal-lg fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel">
    <div class="modal-dialog responsive-fix-register">
      <div class="modal-content" style="max-height:90vh; overflow-y:scroll;">
        <div class="modal-body py-6 px-8 md:py-10 md:px-14">

          <h2 class="h2-brand">REGISTRATION</h2>

          <div class="tag-title-brand">Welcome to Registration</div>

          <p class="text-center font-semibold">Please complete the form below to start your FREE TRIAL.</p>

          <div class="row">
          <div id="signup-page-1">

            <section class="col-12 mt-4">
              <div class="input-group">
                <label class="block text-gray-700 font-bold mx-auto" for="email-signup">
                  Email
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight h-12 "
                  id="email-signup" type="email" onblur="assessReadySignupForPage2()" onkeyup="assessReadySignupForPage2()">
              </div>
            </section> <!-- col-12 -->

            <section class="col-12 mt-4">
                <div class="w-full text-center">
                  <label class="block text-gray-700 font-bold mx-auto" for="password-signup">
                    Password
                  </label>
                </div>
                <div class="input-group">
                <div class="flex flex-row w-full">
                  <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight w-full" id="password-signup" type="password" onblur="assessReadySignupForPage2()" onkeyup="assessReadySignupForPage2()">
                  <!-- <i class="fas fa-eye icon text-xl px-2 content-center"></i> -->
                </div>
              </div>
            </section> <!-- col-12 -->

            <section class="col-12 mt-4">
                <div class="w-full text-center">
                  <label class="block text-gray-700 font-bold mx-auto" for="password-signup-confirm">
                    Confirm Password
                  </label>
                </div>
                <div class="input-group">
                <div class="flex flex-row w-full">
                  <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight w-full" id="password-signup-confirm" type="password" onkeyup="assessReadySignupForPage2()">
                  <i class="fas fa-eye icon text-xl px-2 content-center" onclick="toggleSignupPassword();"></i>
                </div>
              </div>
            </section> <!-- col-12 -->

            </div> <!-- signup-page-1 -->


            <div id="signup-page-2" class="hidden">

              <div class="row">
                <section class="col-12 mt-4">
                  <div class="input-group">
                    <label class="block text-gray-700 font-bold mx-auto" for="agent-full-name">
                      Full Name
                    </label>
                    <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tighth-12" maxlength="28"
                      id="agent-full-name" type="text">
                  </div>
                </section>

              </div>

              <div class="row">
                <section class="col-12 mt-4">
                  <div class="flex items-center justify-center gap-2">
                    <label class="flex flex-col items-center cursor-pointer relative">
                      <label for="newsletter" class="block text-gray-700 font-bold mx-auto">Subscribe to Newsletter</label>
                      <div class="flex items-center content-center mt-1 "><input id="newsletter" type="checkbox" class="toggle-checkbox peer transform scale-125 mr-2"><span>Yes</span></div>
                    </label>
                  </div>
                </section>
              </div> <!-- row -->
              
            </div> <!-- signup-page-2 -->

            <div class="flex justify-end gap-2 mt-8" style="align-items: center;">
              <button id="signup-btn-1" class="btn-brand-primary-3 text-sm" type="button" onclick="signupPrev(event.target)"
                disabled>
                Prev
              </button>
              <button id="signup-btn-2" class="btn-brand-primary-3 text-sm" type="button" data-not-ready
                onclick="signupNext(event.target)">
                Next
              </button>

              <div class="text-gray opacity-40">|</div>

              <!-- <div class="flex items-center justify-center gap-4"> -->
              <button id="signup-btn-3" class="btn-brand-primary" type="button"
                onclick="if(signupFinal(event.target)) authController.signup({ /* no payload */ }, ()=>{$('#walkthroughVideoModal').modal('show');})" disabled>
                Register
              </button>
            </div>

          </div>
          <div class="modal-footer flex-end-off justify-between mt-8">

            <p class="text-center mt-8-off mb-2-off">
              By registering, you agree to the <a href="#" target="_blank" style="text-decoration:none"> 
                Terms and Services</a>.
            </p>
            <a href="#" class="font-medium no-underline" data-bs-toggle="modal"
              data-bs-target="#loginModal">Login</a>
          </div>
        </div>
      </div>
    </div>
  </div> <!-- Signup Modal -->


  <!-- Experimental Modal -->
  <div class="modal fade" id="experimental-features" tabindex="-1" aria-labelledby="modalLabel">
    <div class="modal-dialog">
      <div class="modal-content" style="max-height:90vh; overflow-y:scroll;">
        <div class="modal-header border-0">
          <h3 class="modal-title font-bold text-xl" id="modalLabel">Experimental Features</h3>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
              <div id="modalMessage" style="text-align:center;">
                <p class="mb-8">
                  These features are experimental and will be incorporated as full features in the future. Thank you for testing. Any bugs, please report at Tester Resources -> Bug Report.
                </p>


                <div class="overflow-x-auto">
                  <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                      <tr>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700 border-b">Feature</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700 border-b">Details</th>
                      </tr>
                    </thead>
                    <tbody>

                      <!-- Experimental Feature 1 -->
                      <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-bold text-gray-900 border-b">Experimental Feature 1</td>
                        <td class="px-6 py-4 text-sm text-gray-900 border-b">
                            <p class="text-gray-700">
                              Description coming soon.
                            </p>
                            <div class="flex flex-col space-y-2">
                              <button class="btn-brand-secondary-2" onclick='alert("DONE. Lets pretend we loaded this feature into the page");'
                              data-bs-dismiss="modal">
                                Load into page
                              </button>
                        </td>
                      </tr>


                      <!-- Experimental Feature 2 -->
                      <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-bold text-gray-900 border-b">Experimental Feature 2</td>
                        <td class="px-6 py-4 text-sm text-gray-900 border-b">
                            <p class="text-gray-700">
                              Description coming soon.
                            </p>
                            <div class="flex flex-col space-y-2">
                              <button class="btn-brand-secondary-2" onclick='alert("DONE. Lets pretend we loaded this feature into the page");'
                              data-bs-dismiss="modal">
                                Load into page
                              </button>
                        </td>
                      </tr>

                    </tbody>
                  </table>
                </div>


              </div> <!-- #modalMessage -->
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn-brand-secondary text-sm" data-bs-dismiss="modal">Done</button>
        </div>
      </div>
    </div>
  </div> <!-- Experimental Modal -->


  <!-- Modal Walkthrough Video -->
  <div class="modal modal-lg fade" id="walkthroughVideoModal" tabindex="-1" aria-labelledby="walkthroughVideoModalLabel">
    <div class="modal-dialog">
      <div class="modal-content" style="max-height:90vh; overflow-y:scroll;">
        <div class="modal-body py-6 px-8 md:py-10 md:px-14">
          <!-- Walkthrough Video Title -->
          <h2 class="h2-brand text-center">Video Tutorial</h2>

          <!-- Walkthrough Video Description -->
          <p class="text-center mb-4">
            Watch this walkthrough on how to create your video:
          </p>

          <!-- Video Embed or Placeholder -->
          <div class="video-container mb-6">
            <video id="walkthrough-video" width="100%" height="315" controls>
              <source src="./assets/videos/demo-walkthrough.mp4" type="video/mp4">
              Your browser does not support the video tag.
            </video>
          </div>

          <!-- Navigation to other content or resources -->
          <p class="text-center">You can come back to this video at anytime from the menu.<br/><br/>Need more help? <a href="mailto:weng@mixotype.com" class="no-underline">Contact Support</a>.</p>

          <!-- Next Steps Buttons -->
          <div class="flex justify-end gap-2 mt-8">
            <!-- <button id="walkthrough-back-btn" class="btn-brand-primary-3 text-sm" type="button" data-bs-dismiss="modal">Close</button> -->
            <button id="walkthrough-next-btn" class="btn-brand-primary text-sm" type="button" onclick="document.querySelector('#walkthrough-video').pause();" data-bs-dismiss="modal">Get Started</button>
          </div>
        </div>
      </div>
    </div>
  </div> <!-- Walkthrough -->


  <!-- Modal Page Video -->
  <div class="modal modal-lg fade" id="walkthroughPageModal" tabindex="-1" aria-labelledby="walkthroughPageModal">
    <div class="modal-dialog">
      <div class="modal-content" style="max-height:90vh; overflow-y:scroll;">
        <div class="modal-body py-6 px-8 md:py-10 md:px-14">
          <!-- Walkthrough Video Title -->
          <h2 class="title h2-brand text-center">Video Tutorial</h2>

          <!-- Walkthrough Video Description -->
          <p class="instructions text-center mb-4">
            <!-- Watch this walkthrough on how to create your video: -->
          </p>

          <!-- Video Embed or Placeholder -->
          <div class="video-container mb-6">
            <video class="video" width="100%" height="315" controls>
              <source src="./assets/videos/demo-walkthrough.mp4" type="video/mp4">
              Your browser does not support the video tag.
            </video>
          </div>

          <!-- Navigation to other content or resources -->
          <p class="text-center">You can come back to this video at anytime.<br/><br/>Need more help? <a href="mailto:weng@mixotype.com" class="no-underline">Contact Support</a>.</p>

          <!-- Next Steps Buttons -->
          <div class="flex justify-end gap-2 mt-8">
            <!-- <button id="walkthrough-back-btn" class="btn-brand-primary-3 text-sm" type="button" data-bs-dismiss="modal">Close</button> -->
            <button class="btn-brand-primary text-sm" type="button" onclick="document.querySelector('#walkthroughPageModal .video').pause();" data-bs-dismiss="modal">Okay</button>
          </div>
        </div>
      </div>
    </div>
  </div> <!-- Page Video -->


  <!-- Modal -->
  <!-- Modularized. Trigger with: $("#downloadModal").modal("show") OR document.getElementById("downloadModal").classList.toggle("d-none"); -->
  <script>
    // download from link
    function downloadFromLink(url) {

    // Check if the URL is not empty
    if (url) {
        // Create a temporary anchor element
        var downloadLink = document.createElement("a");
        downloadLink.href = url;

        // Set the download attribute (use the URL to create a meaningful filename if possible)
        downloadLink.download = url.split('/').pop();

        // Append the anchor to the body, click it, and remove it
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    } else {
        alert("No URL found to download.");
    }
    }
    </script>
  <div class="modal d-none transition duration-300 ease-in-out hover:text-black" id="downloadModal" tabindex="-1" role="dialog" aria-labelledby="downloadModalLabel">

      <div class="modal-dialog p-2.5 bg-gray-300" role="document" style="min-width: 50vw;">
          <div class="modal-content pb-6">
              <div class="modal-header">
                  <h4 class="text-2xl font-bold modal-title" id="downloadModalLabel">Download</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                      style="position:absolute; top:10px; right:10px;">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body flex flex-column justify-center align-items-center">
                  
                  <div class="flex flex-row flex-nowrap my-8">
                      <div class="download-video mb-6 text-gray-600 clickable hoverable cursor-pointer" data-url="" onclick="downloadFromLink(event.currentTarget.getAttribute('data-url'))">
                          <i class="fa fa-download text-center"></i>
                          <span> Video</span>
                          <!-- <textarea id="shareSnippet" class="form-control" rows="3" readonly style="width:355px; display:none;"></textarea> -->
                      </div>
                  </div>
              </div>

          </div>
      </div>
  </div> <!-- Modal Download -->


    <?php
echo <<<cbust_ipad
  <script src="app-auth-landing/assets/index.js$v"></script>
  <link href="app-auth-landing/assets/index.css$v" rel="stylesheet"></link>
cbust_ipad;
?>