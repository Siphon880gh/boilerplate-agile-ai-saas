window.addEventListener("DOMContentLoaded", function (e) {

    var jwt = localStorage.getItem("YOUR_APP_jwt");
    if (jwt) {
      authController.loginWithJWT(
        { jwt }, 
        ()=>{
          navController.switchPanel(SCREENS.Dashboard);
          navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.Dashboard);
        }, 
        ()=>{
          // failed
          localStorage.removeItem("YOUR_APP_jwt")
        });
    }
    document.querySelector("html").style.display = "block"
});
