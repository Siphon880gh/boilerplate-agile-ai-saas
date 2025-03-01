var TIP_DURATION = 5; // Duration of each tip in seconds (can be float)

// Array of tips
var firstTip = 'The slideshow is made from your instructions and files.';
var tips = [
    'Labeling images with the "Label image" feature is optional, but recommended for better results.',
    'For faster processing time, submit smaller files (eg. compress your images).'
];
tips = tips.sort(() => Math.random() - 0.5);
tips.unshift(firstTip);

var tipIndex = 0; // Index to track the current tip
var tipInterval = null;

// Function to display the next tip with fade effects
function showNextTip() {
    var tipDiv = document.getElementById('tip-div');

    // Start fade-out
    tipDiv.style.opacity = '0';

    // After fade-out duration (1s), change the content and start fade-in
    setTimeout(function() {
    // Update the tip content
    tipDiv.innerHTML = tips[tipIndex];


    // Move to the next tip, loop back to the first after the last tip
    tipIndex = (tipIndex + 1) % tips.length;

    // Start fade-in
    tipDiv.style.opacity = '1';
    }, 1000); // Wait 1 second for fade-out before changing content and fading in
}

// Show the first tip when the page loads
window.onload = function() {
    // Initialize the first tip
    var tipDiv = document.getElementById('tip-div');
    tipDiv.innerHTML = tips[tipIndex];
    tipDiv.addEventListener('click', ()=>{
        clearInterval(tipInterval);
        showNextTip();
        setInterval(showNextTip, TIP_DURATION * 1000);
    });

    // Move to the next tip
    tipIndex = (tipIndex + 1) % tips.length;

    // Set interval to change tips every X seconds (Can be float)
    tipInterval = setInterval(showNextTip, TIP_DURATION * 1000);
};