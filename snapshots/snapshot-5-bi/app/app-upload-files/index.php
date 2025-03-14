<!-- Module type: iframe module -->
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Upload Files</title>
  <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="//cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
  
<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad
    <link href="../assets/common.css$v" rel="stylesheet">
    <link href="assets/index.css$v" rel="stylesheet">
cbust_ipad;
?>

  <?php $up=1; include("../assets--whitelabeler/brand-loader.php"); unset($up);?>
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
  <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
  
  <script src="https://unpkg.com/heic2any@0.0.4/dist/heic2any.js"></script>
  
  <script class="config-phase">
  const maxFileSize = 100 * 1024 * 1024; // "A" mb in bytes

  const pageMode = {
    MODES: {
      DEMO: 0,
      LIVE: 1,
    },
    currentMode: null,
  };

  // -> CONFIG HERE:      
  pageMode.currentMode = pageMode.MODES.LIVE;

  let submit = () => {}
  if (pageMode.currentMode === pageMode.MODES.LIVE) {
    submit = messageParent; // No args to pass in
  } else {
    submit = messageSelf;
  }

  function messageSelf() {
    alert("DONE. Let's pretend we go to the next page - User will see a video being generated from the assets.")
  }

  /* MessageParent will save text */
  function messageParent() {

    if (document.querySelector("#submit").className.includes("disabled"))
        return;
        
    // Show loading indicator
    $('#is-uploading-php').removeClass('d-none');
    
    // Get user id, app id, and case id from parent window
    const userId = window.parent.getUserId();
    const appId = window.parent.getAppId();
    const caseId = window.parent.getCaseId();

    // Create FormData object
    const formData = new FormData();
    
    // Add metadata
    formData.append('userId', userId);
    formData.append('appId', appId);
    formData.append('caseId', caseId);
    
    // Add each file to the FormData
    window.files.forEach((file, index) => {
        if (file.size > maxFileSize) {
          alert(`File ${file.name} exceeds the 10MB size limit.`);
          $('#is-uploading-php').addClass('d-none');
          return; // Cancel the upload
        }
        if (file.isUrl) {
            formData.append(`url-${index}`, file.url);
        } else {
            formData.append(`file-${index}`, file, file.name);
        }
    });
    
    // Send data to the server using fetch
    fetch('upload-files.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not ok: " + response.status);
        }
        return response.json();
    })
    .then(payload => {
        console.log("Server response:", payload);
        $('#is-uploading-php').addClass('d-none');
        if (payload.error) {
            alert("Error: " + payload.error);
        } else {
            // Handle success

            // Adapt the labeled pics as data transfer object to send to API endpoint at getFilesForBuildingVideo
            let labelsDTO = {}
            for(key in window?.labelsModel) {
                $(".filename").each((i, filenameEl)=>{
                    if(key.toLowerCase() === filenameEl.textContent.trim().toLowerCase()) {
                        // console.log(key.toLowerCase());
                        labelsDTO[i] = window?.labelsModel[key]
                    }
                });
            }

            // Update core assets at content collection, and the requestpayload will not be long
            window.parent.mainController.updateDbModel({
              appId: appId,
              userId: userId,
              caseId: caseId,
              files: payload.files,
              labels: labelsDTO
            })
            

            // Update aux assets at content collection, and the request payload could potentially be long especially when add more features
            window.parent.mainController.addToJobQueue({
              labels: labelsDTO
            }, (resource)=>{
              window.parent.mainController.performJob(resource.serverVideoMode, resource.jobId);
            })
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        $('#is-uploading-php').addClass('d-none');
        alert("Error uploading files: " + error.message);
    });
  } // messageParent
  </script>


  </head>
  <body>

  <?php
    $step=3;
    include("../assets/steps.php");
  ?>

    <div class="container">
      <div class="header">
        <h1 class="h2-brand">Slideshow Creator</h1>
        <p>Provide pictures, audios, text/pdf, or url that could be support material to create the slideshow.</p>
      </div>
    
      <div class="upload-interface" id="uploadInterface">
        <div class="upload-panels">
          <div class="upload-panel dropzone w-full" id="dropzone">
            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h3>Drag & Drop Files Here</h3>
            <br/>
            <p>Supported files:<br/>Images, Audio, Video, Text,<br/>Data (JSON, XML, CSV),<br/>Spreadsheet, Word Documents and PDF</p>
            <button class="btn-brand-primary" id="selectFiles">Select Files</button>
            <input type="file" id="fileInput" multiple 
              accept="image/*,
                      .heic,.heif,
                      video/mp4,.mp4,
                      audio/mpeg,.mp3,                 
                      audio/wav,.wav,
                      audio/ogg,.ogg,
                      audio/x-m4a,.m4a,
                      video/webm,.webm,
                      video/quicktime,.mov,
                      text/plain,.txt,                 
                      text/csv,.csv,                   
                      application/json,.json,
                      application/xml,.xml,
                      text/xml,.xml,
                      application/pdf,.pdf,            
                      application/vnd.ms-excel,.xls,
                      application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.xlsx,
                      application/vnd.ms-excel.sheet.macroEnabled.12,.xlsm,
                      application/vnd.ms-excel.sheet.binary.macroEnabled.12,.xlsb,
                      application/msword,.doc,
                      application/vnd.openxmlformats-officedocument.wordprocessingml.document,.docx"
              style="display: none;">
          </div>
          
          <div class="upload-panel url-panel flex flex-col justify-center items-center content-center w-full advanced-only">
            <div class="inner">
              <i class="fas fa-link" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
              <h3>Add URL</h3>
              <p>Enter a URL to add it to your slideshow</p>
              <div class="url-input-container flex flex-row items-start content-start">
                <input type="text" id="urlInput" placeholder="https://..." class="url-input">
                <button class="btn-brand-primary" id="addUrl" style="margin:0;">Add URL</button>
              </div>
            </div>
          </div>
        </div>
    
        <div class="preview-grid-wrapper">
          <button id="label-graphic" href="javascript:void(0);" class="relative btn btn-brand-secondary"
            onclick="openOverlayPanel(); window.labelGraphicMode = true;">
            <i class="fas fa-vector-square" arial-label="Add Overlays"></i>
            <span class="label">Label graphic</span>
          </button>
          <div class="preview-grid" id="previewGrid"></div>
        </div>


        <div class="flex justify-end">
          <div class="flex flex-col justify-start">
            <button class="btn-brand-primary disabled" id="submit" onclick="submit()">Create Slideshow</button>

            <div id="is-uploading-php" class="d-none mt-4 text-gray-400">
              <span class="text-muted">Files are uploading...&nbsp;</span><i class="fas fa-spinner fa-spin fa-sm"></i><br>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  
<?php
echo <<<cbust_ipad
    <script src="assets/index.js$v"></script>
cbust_ipad;
?>

<?php include("../app-add-text-overlays/index.php"); ?>

</body>
</html>