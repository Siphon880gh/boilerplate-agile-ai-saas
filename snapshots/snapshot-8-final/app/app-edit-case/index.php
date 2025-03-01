<!-- Module type: iframe rerouter module that redirects to another iframe module after saving resuming models to window.parent -->
<!-- import `window.parent.effectsDict` as `effectsDict` from app root `index.php` which sources `assets/dictionary-effects.js` -->
<!DOCTYPE html>
<html>
<head>
    <title>Edit Video</title>

    <link href="//cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">

<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad

    <script src="../assets/common.js$v"></script>
    <link href="../assets/common.css$v" rel="stylesheet">
    <script src="../assets/screens.js$v"></script>

cbust_ipad;
?>

<?php include("../assets/common.php"); ?>
<?php $up=1; include("../assets--whitelabeler/brand-loader.php"); unset($up);?>

</head>
<body class="p-9 d-none">

<h1 class="h2-brand text-center text-3xl font-bold">Edit Video</h1>
<div id="error-text" class="text-center my-12 text-red-500"></div>
<div class="flex flex-row justify-center">
    <button class="btn-brand-secondary" onclick="
        window.parent.navController.switchPanel(SCREENS.Dashboard);
        window.parent.navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.Dashboard);    
    ">Back to dashboard</button>
</div>

<!-- jQuery to speed up development -->
<script src="//code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const effectsDict = window.parent.effectsDict;
</script>

<?php
echo <<<cbust_ipad
    <script src="assets/index.js$v"></script>
cbust_ipad;
?>

</body>
</html>