<?php
header("Vary: *");
include "assets--whitelabeler/brand-loader-by-url.php";
include "assets/common.php";

require 'vendor/autoload.php';
use Dotenv\Dotenv;

// Load the .env file
$dotenv = Dotenv::createImmutable(__DIR__, ".env.local");
$dotenv->load();

$env_guest_mode = (int) ($_ENV['GUEST_MODE'] ?? 0);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>App</title>

  <meta name="description" content="YOUR_APP_DESCRIPTION" />

  <!-- Open Graph Meta Tags for Social Previews -->
  <meta property="og:title" content="COMPANY_NAME" />
  <meta property="og:description" content="." />
  <meta property="og:url" content="https://www.domain.com" />
  <meta property="og:type" content="website" />

  <!-- Favicon/Icon -->
  <link rel="shortcut icon" href="favicon.ico" />
  <meta name="twitter:card" content="summary_large_image" />

  <meta http-equiv="Cache-Control" content="no-store">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  <!-- Syntactic Sugar: Style classes inline with html -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="//cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">

  <!-- Icon Sets -->
  <link data-docs="https://fontawesome.com/v5/search?o=r&m=free"
    href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css" rel="stylesheet">

<?php include("./assets/version-cache-bust.php");
echo <<<cbust_ipad

  <script src="assets/jwt-paint-before.js$v"></script>
  <link href="assets/index.css$v" rel="stylesheet">
  <link href="assets/common.css$v" rel="stylesheet">

cbust_ipad;
?>

  <?php include("./assets--whitelabeler/brand-loader.php"); ?>
</head>

<body class="main-background">
  <!-- Loading Overlay -->
  <div id="loading-overlay" class="fixed inset-0 bg-white flex items-center justify-center z-50 d-none">
      <i class="fas fa-spinner fa-spin text-white text-4xl"></i>
  </div>

  <div id="bottom-left" class="fixed bottom-4 left-0 rounded z-10"></div>
  <div id="bottom-right" class="fixed bottom-4 right-3 bpy-2 px-4 rounded z-10"></div>


  <div class="fixed top-0 right-0 pt-2 pb-2 px-4 rounded-lg bg-white w-full">
    <div class="menu-primary flex flex-nowrap justify-between md:justify-end gap-4 z-50 w-full">
      <?php include("./menus/app.php"); ?>
    </div>
    <div class="menu-secondary flex justify-end mt-2 gap-4"></div>
  </div> <!-- top right -->

  <div class="min-h-screen min-w-screen-off container" style="margin-top:60px;">
    <div id="panel-containers" x-data="{ activePanel: SCREENS.AuthLanding }" x-init="window.activePanel = activePanel"
      x-effect="window.activePanel = activePanel" class=" min-h-screen min-w-screen">

      <!-- Panel 1 -->
      <div id="panel-1" x-show="activePanel === SCREENS.ReadInstructions" class="dynamic-panel min-h-screen min-w-screen z-40"
        data-off-class="dynamic-panel-1 bg-white p-6 border rounded-lg ">
        <iframe id="iframe-read-instructions" src="app-read-instructions/"  data-will-src="app-read-instructions/" class="dynamic-panel min-h-screen min-w-screen" frameborder="0" width="100%"
          height="100%"></iframe>
      </div>

      <!-- Panel 2 -->
      <div id="panel-2" x-show="activePanel === SCREENS.WritePrompt" class="dynamic-panel min-h-screen min-w-screen" style="display: none;"
      >
        <iframe id="iframe-write-prompt" src="app-write-prompt/" class="min-h-screen min-w-screen z-40" frameborder="0" width="100%"
          height="100%"></iframe>
      </div>

      <!-- Panel 3 -->
      <div id="panel-3" x-show="activePanel === SCREENS.UploadFiles" class="dynamic-panel min-h-screen min-w-screen z-40" style="display: none;">
        <iframe id="iframe-upload-files" src="about:blank" data-will-src="app-upload-files/"
          class="min-h-screen min-w-screen" frameborder="0" width="100%" height="100%"></iframe>
      </div>

      <!-- Panel 4 -->
      <div id="panel-4" x-show="activePanel === SCREENS.PreviewSlideshow" class="dynamic-panel min-h-screen min-w-screen z-40" style="display: none;">
        <iframe id="iframe-preview-slideshow" src="about:blank" data-will-src="app-preview-slideshow/" class="min-h-screen min-w-screen" frameborder="0" width="100%" height="100%"></iframe>
      </div>

      <!-- Panel Edit Case -->
      <div id="panel-11" x-show="activePanel === SCREENS.EditCase" class="dynamic-panel min-h-screen min-w-screen z-40" style="display: none;">
        <iframe id="iframe-edit-case" src="about:blank" data-will-src="app-edit-case/" class="min-h-screen min-w-screen" frameborder="0" width="100%" height="100%"></iframe>
      </div>


      <!-- Panel Edit Profile -->
      <div id="panel-12" x-show="activePanel === SCREENS.EditProfile;" class="dynamic-panel min-h-screen min-w-screen"
        data-off-class="dynamic-panel-1 bg-white p-6 border rounded-lg ">
        <iframe src="about:blank" data-will-src="app-profile/" class="dynamic-panel min-h-screen min-w-screen" frameborder="0" width="100%"
          height="100%"></iframe>
      </div>

      <!-- Panel Auth Form -->
      <div id="panel-13" x-show="activePanel === SCREENS.AuthLanding;" class="dynamic-panel min-h-screen min-w-screen"
        data-off-class="dynamic-panel-1 bg-white p-6 border rounded-lg ">
        <iframe src="app-auth-landing/" class="dynamic-panel min-h-screen min-w-screen" frameborder="0" width="100%"
          height="100%"></iframe>
      </div>

      <!-- Panel Dashboard-->
      <div id="panel-14" x-show="activePanel === SCREENS.Dashboard;" class="dynamic-panel min-h-screen min-w-screen"
        data-off-class="dynamic-panel-1 bg-white p-6 border rounded-lg ">
        <iframe src="about:blank" data-will-src="app-dashboard/" class="dynamic-panel min-h-screen min-w-screen" frameborder="0" width="100%"
          height="100%"></iframe>
      </div>

    </div> <!-- panel-containers -->
  </div> <!-- center page -->

  <?php include("./modals.php"); ?>\
  <!-- Syntactic Sugar: JS Logic -->
  <script data-docs="https://underscorejs.org/"
    src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.6/underscore-min.js"
    integrity="sha512-2V49R8ndaagCOnwmj8QnbT1Gz/rie17UouD9Re5WxbzRVUGoftCu5IuqqtAM9+UC3fwfHCSJR1hkzNQh/2wdtg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script data-docs="https://api.jquery.com/" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"
    integrity="sha512-DUC8yqWf7ez3JD1jszxCWSVB0DMP78eOyBpMa5aJki1bIRARykviOuImIczkxlj1KhVSyS16w2FSQetkD4UU2w=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script data-docs="https://github.com/tkambler/whenLive/"
    src="https://rawcdn.githack.com/tkambler/whenLive/4574d3b022012677f1f6d61309a91673c4878f51/src/jquery.whenlive.js"></script>

  <!-- Syntactic Sugar: Logic inline with html -->
  <script data-docs="https://alpinejs.dev"
    src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.3.0/alpine-ie11.min.js"
    integrity="sha512-Atu8sttM7mNNMon28+GHxLdz4Xo2APm1WVHwiLW9gW4bmHpHc/E2IbXrj98SmefTmbqbUTOztKl5PDPiu0LD/A=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  <!-- Bootstrap Interactive Elements -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<?php
echo <<<cbust_ipad
  <script src="assets/common.js$v"></script>
  <script src="assets/screens.js$v"></script>
  <script src="assets/utils.js$v"></script>
  <script src="assets/index.js$v"></script>

  <script src="assets/jwt-paint-after.js$v"></script>
cbust_ipad;
?>

</body>

</html>