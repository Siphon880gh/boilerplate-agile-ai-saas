// Copy and pasting
function selectAndCopyTextarea($el, $done) {
    this.selectTextarea = function ($el, callback) {
        var isIOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);

        if (isIOS)
            $el.get(0).setSelectionRange(0, 9999);
        else
            $el.select();

        callback();
    } // selectTextarea

    this.saveToClipboard = function () {
        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
            // console.log('Copying text command was ' + msg);
            if ($done)
                $done.fadeIn(800).delay(1200).fadeOut(500);
        } catch (err) {
            // console.log('Oops, unable to copy');
        }

    } // saveToClipboard

    this.selectTextarea($el, saveToClipboard);

} // selectAndCopyTextarea

function pauseAllVideos() {
    document.querySelectorAll("video").forEach(vid => vid.pause())
} // pauseAllVideos

function stripNoCacheFromUrl(url) {
    url = url.replace(/\?nocache=.*/g, "");
    return url;
} // stripNoCacheFromUrl


// download from link
function downloadFromLink(url) {

    // Check if the URL is not empty
    if (url) {
        // Create a temporary anchor element
        var downloadLink = document.createElement("a");
        downloadLink.href = url;

        // Set the download attribute (use the URL to create a meaningful filename if possible)
        downloadLink.download = url.split('/').pop();

        // Append the anchor to the body, click it, and remove it
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    } else {
        alert("No URL found to download.");
    }
} // downloadFromLink

function requestDownloadModal() {
    var $downloadModal = $(window.parent.document.querySelector("#downloadModal"));

    $downloadModal.find(".download-video").attr("data-url", stripNoCacheFromUrl(document.querySelector("#preview-video source").src))

    setTimeout(() => {
        $downloadModal.modal("show");
    }, 100);
}

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip({ placement: "right" }); // This initializes all tooltips
});