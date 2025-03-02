<?php
// imported `$logo` which is relative path to the logo image at app root's `assets--whitelabeler/branding-<company>/logo-<company>.png`
?>
<div class="text-center w-full mx-2">
    <!-- Removed the w-full if you don't want it to stretch across the full width -->

    <p class="text-xl mt-1 textcolor-brand"><b>YOUR_HEADLINE</b></p>
    <div style="width:1px; height:20px"></div>
    <p class="text-lg mt-1 textcolor-brand"><i>Here at</i></p>
    <img src="<?php echo $logo; ?>" alt="Logo" style="display:block; margin:0 auto; margin-bottom:40px;" />

    <hr style="width:50%; margin: 0 auto;">
    <p class="text-lg mt-6 mb-7 font-semibold">Here we can DESCRIBE_YOUR_VALUE_PROPOSITION</p>

    <p class="textcolor-brand text-lg font-bold mt-8">Create Yours Now:</p>

    <button class="btn-brand-primary mr-8" href="javascript:void(0);"
      onclick="window.parent.document.querySelector('#link-signup').dispatchEvent(new Event('click'))">
      Sign Up
    </button>

    <button class="btn-brand-secondary" onclick="window.parent.document.querySelector('#link-login').dispatchEvent(new Event('click'))">
      Login
    </button>

    <p class="mt-8">
      Please use Chrome on Desktop for best experience.</p>
    
    <div class="text-center mt-4 text-sm mx-auto" style="max-width:350px">
      <a href="#" target="_blank" class="textcolor-brand">
        Learn More
      </a>
    </div>
  </div>
