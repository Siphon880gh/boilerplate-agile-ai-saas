<!-- Module type: iframe module -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Intro Instructions</title>
  <link href="//cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">

<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad

  <link rel="stylesheet" href="../assets/common.css$v">
  <script src="../assets/common.js$v"></script>

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

      function messageParent() {
        window.parent.mainController.proceedFromInstructions();
      }

      function messageSelf() {
        alert("DONE. Let's pretend we go to the next page - User will write AI prompt to design the slideshow.")
      }

      let submit = () => {}
      if (pageMode.currentMode === pageMode.MODES.LIVE) {
        submit = messageParent; // No args to pass in
      } else {
        submit = messageSelf;
      }
  </script>
</head>

<body>

  <?php
    $step=1;
    include("../assets/steps.php");
  ?>


  <div class="flex items-center justify-center">


    <div class="textcolor-brand p-8 pt-0 box-border font-sans">
      <div class="max-w-3xl mx-auto bgcolor-brand-contrasted-off p-8 rounded-2xl">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="h2-brand font-bold text-white">Read Instructions</h1>

          <div class="my-24 text-center">
            <p> First, you'll tell the AI what kind of slideshow you'd like. Then, you can upload any files you want to include—pictures, text, audio, and more.</p>
            <p> Once that's done, the AI will generate the complete slideshow for you.</p>
          </div>

        </div>

        <!-- Navigation -->
        <div class="flex justify-between mt-8 border-t-2-offborder-gray-300">
          <button id="submit" class="btn-brand-primary" style="margin:20px auto" onclick="submit()">Start</button>
        </div>
      </div>
    </div>

  </div>

</body>
</html>