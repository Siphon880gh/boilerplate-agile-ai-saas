<?php
// imported `$logo` which is relative path to the logo image at app root's `assets--whitelabeler/branding-<company>/logo-<company>.png`
?>
<div class="text-center mx-2">

    <div class="mx-auto" style="margin-bottom:30px;">
      <h1 class="text-3xl mt-1 textcolor-brand"><b>App Portal:</b></h1>
      <div style="width:1px; height:20px"></div>
      <div class="flex flex-row justify-center gap-8 items-center">
        <img src="<?php echo $logo; ?>" alt="Logo"
          style="display:block; margin:0 auto; margin-bottom:40px; width:200px; height:145px;" />
      </div>
    </div>


    <hr style="width:25%; margin: 0 auto;">
    <p class="subtitle-brand text-lg mt-6 mb-7 font-semibold">Here we can &lt;DESCRIBE&gt;...
    </p>

    <p class="my-2">
      Please use Chrome, Safari, or Firefox on Desktop for best experience.<br/>Any questions, please reach out to <EMAIL></p>

    <h2 class="textcolor-brand text-2xl font-bold mb-4"  style="margin-top:60px;"><b>&nbsp;Create Yours Now:</b></h2>

    <div class="flex flex-row justify-center items-center gap-4">
      <button class="btn-brand-primary mr-8" href="javascript:void(0);"
        onclick="window.parent.document.querySelector('#link-signup').dispatchEvent(new Event('click'))">
        Sign Up
      </button>

      <button class="btn-brand-secondary" onclick="window.parent.document.querySelector('#link-login').dispatchEvent(new Event('click'))">
        Login
      </button>
    </div>

    <div class="flex flex-row justify-center items-center align-center mx-auto mb-6 mt-14">
      <span class="text-sm">Powered by <a target="_blank" href="#">DEFAULT_BRAND</a></span> 
    </div>

  </div>
