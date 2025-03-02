/* Slide down menu */
document.body.addEventListener('click', function(event) {
  var dropdownToggleClicked = null;
  var dropdownToggleClickedButBeenOpened = false;

  if(event.target.matches(".dropdown-toggle")) {
    dropdownToggleClicked = event.target;
  }
  
  if(dropdownToggleClicked) {
    var dropdownWrapper = dropdownToggleClicked.closest('.dropdown-wrapper');
    if(dropdownWrapper.className.includes('open')) {
        dropdownToggleClickedButBeenOpened = true;
    }
  }

  const dropdownWrappers = document.querySelectorAll('.dropdown-wrapper');
  dropdownWrappers.forEach(ddWrapper=>{
    ddWrapper.classList.remove('open');
  });

  if(dropdownToggleClicked && !dropdownToggleClickedButBeenOpened) {
    var dropdownWrapper = dropdownToggleClicked.closest('.dropdown-wrapper');
    dropdownWrapper.classList.add('open');
  }
});