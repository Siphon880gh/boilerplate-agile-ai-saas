
window.addEventListener("DOMContentLoaded", function (e) {

    var jwt = localStorage.getItem("YOUR_APP_jwt");
    if (jwt) {
      document.querySelector("html").style.display = "none"
    }
});