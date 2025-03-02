<link href="assets/dropdown.css" rel="stylesheet">

<div class="dropdown-wrapper logged-in hidden-important">
  <a id="login-name" href="javascript:void(0)" class="dropdown-toggle"></a>
  <div class="dropdown-content">
      <a href='javascript:void(0)' onclick="pauseAllPossVideos(); navController.switchPanel(SCREENS.Dashboard); resetCornerStatuses();">Dashboard</a>
      <a href='javascript:void(0)' onclick="pauseAllPossVideos(); navController.switchPanel(SCREENS.EditProfile); resetCornerStatuses();">Edit Profile</a>
  </div>
</div>

<a href="#" class="logged-in credit-status hidden" target="_blank"></a>

<a href="javascript:void(0)" id="see-walkthrough" class="logged-in hidden bg-white rounded no-underline" onclick="$('#walkthroughVideoModal').modal('show');">Walkthrough</a>

<div class="dropdown-wrapper logged-in hidden-important">
  <a href="javascript:void(0)" class="dropdown-toggle">Tester Resources</a>
  <div class="dropdown-content">
      <a href="#" target="_blank">Survey</a>
      <a href="#" target="_blank">Report Bug</a>
      <a href="#" target="_blank">Request Feature</a>
  </div>
</div>

<a href="javascript:void(0);" id="link-logout" class="logged-in rounded no-underline hidden" onclick="authController.logout();">Logout</a>
<a href="#" id="link-login" class="logged-out bg-white rounded no-underline" data-bs-toggle="modal"
  data-bs-target="#loginModal">Login</a>
<a href="#" id="link-signup" class="logged-out bg-white rounded no-underline" data-bs-toggle="modal"
  data-bs-target="#signupModal" type="button">Signup</a>

<script src="assets/dropdown.js"></script>