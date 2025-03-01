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