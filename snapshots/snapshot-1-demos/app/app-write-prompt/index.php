<!-- Module type: iframe module -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Write Prompt</title>
  <link href="//cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">

<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad

  <link rel="stylesheet" href="assets/index.css$v">
  <link rel="stylesheet" href="../assets/common.css$v">
  <script src="../assets/common.js$v"></script>

cbust_ipad;
?>

  <script class="config-phase">
  const pageMode = {
    MODES: {
      DEMO: 0,
      LIVE: 1,
    },
    currentMode: null,
  };

  // -> CONFIG HERE:      
  pageMode.currentMode = pageMode.MODES.DEMO;

  let submit = () => {}
  if (pageMode.currentMode === pageMode.MODES.LIVE) {
    submit = messageParent; // No args to pass in
  } else {
    document.addEventListener("DOMContentLoaded", () => {
      document.querySelector(".rewrite-controls").remove();
    });
    submit = messageSelf;
  }

  function messageSelf() {
    alert("DONE. Let's pretend we go to the next page - User will upload files (images, documents, audios, etc) to create the slideshow.")
  }

  /* MessageParent will save text */
  function messageParent() {

      const payload = {
        aiPrompt: window.getTextareaValue()
      }

      // console.log(payload.propertyDesc)
      window.parent.mainController.saveAIPrompt(payload); // payload includes payload.propertyDesc
  } // messageParent
  </script>

</head>

<body>

  <div class="min-h-screen-off flex items-center justify-center">

    <div class="textcolor-brand p-8 pt-0 box-border font-sans">
      <div class="max-w-3xl mx-auto bgcolor-brand-contrasted-off p-8 rounded-2xl">
        <div class="mb-8">
          <h1 class="h2-brand font-bold text-white">Describe Your Slideshow</h1>

          <div class="mb-4">
            <p>
            Describe what slideshow you are creating and how to use the materials you will upload (images, documents, text, audio, videos). Our AI will take your instructions and create the slideshow.
            </p>

            <p>
            You can press the AI suggest button to rewrite your instructions.
            </p>
          </div>
        </div>

        <!-- Textarea -->
        <div class="input-group mb-2" style="position:relative;">
          <label for="textarea" class="mx-auto font-bold">Type here</label>
          <div id="char-count" class="absolute bottom-2 right-0 text-xs"></div>
          <textarea id="textarea"
            class="w-full h-48 p-4 border-2 border-gray-300 rounded-lg text-base resize-y focus:outline-none focus:border-primary-color-tinted mb-4"
            placeholder="Enter your text here..."></textarea>
        </div>

        <!-- Controls -->
        <div class="rewrite-controls flex items-center gap-4 mb-6">

          <button id="rewriteBtn" class="btn btn-brand-primary-2 flex items-center gap-2" style="transition: all 100ms !important;">
            <i class="fas fa-magic"></i>
            <i class="fas fa-spinner fa-spin loading-spinner hidden"></i>
            Rewrite with AI
          </button>

          <div class="flex gap-2 show-after-rewrite d-none">
            <button id="undoBtn" class="btn btn-brand-secondary" disabled>
              <i class="fas fa-undo"></i> Undo
            </button>
          </div>

          <div class="ml-auto text-sm text-gray-600">
            <span id="rewritesLeft">5</span> rewrites remaining
          </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between mt-8 border-t-2-offborder-gray-300">
          <button id="submit" class="btn-brand-primary disabled" style="margin:20px auto"
            onclick="if(finalCheck()) { cleanupCheckCharInputted(); submit(event) }"
          >Continue</button>
        </div>
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