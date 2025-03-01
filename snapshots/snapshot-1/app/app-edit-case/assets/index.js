// Update the MVC model
function updateMvcModel(model, response) {
    // Update the root-level fields
    for (const key in response) {
        if (key in model) {
            // If the field exists and is an object, merge deeply
            if (typeof model[key] === 'object' && typeof response[key] === 'object') {
                model[key] = { ...model[key], ...response[key] };
            } else {
                // Otherwise, overwrite
                model[key] = response[key];
            }
        } else {
            // Add new fields from the API response
            model[key] = response[key];
        }
    }

    // model.aiPrompt = response.content_is.aiPrompt;
    // model.finalVideo = response.content_is.finalVideo;

    return model;
} // updateMvcModel


function onDomContentLoaded() {

    // Check if had navigated back from UploadFiles to EditCase router
    const hadJustVisitedUploadFiles = window.parent.navController.lastVisited.length>=3 && window.parent.navController.lastVisited[ window.parent.navController.lastVisited.length-2 ]===SCREENS.UploadFiles;

    if(hadJustVisitedUploadFiles) {
        window.parent.navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.Dashboard);
        window.parent.navController.switchPanel(SCREENS.Dashboard)
        return;
    }
    
    var userId = window.parent.appModel.userId;
    var appId = window.parent.appModel.appId;
    var c = window.parent.appModel.caseId;
    var casesUrl = `${finalHost}/cases?uid=${userId}&c=${c}`;

    Promise.all([
        fetch(casesUrl).then(response => response.json())
    ])
    .then(async ([caseData]) => {
        if (caseData?.error && parseInt(caseData.error) === 1) {
            $("#error-text").html("<b>ERROR:</b> " + caseData.error_desc);
            $("body").removeClass("d-none");
        } else {
            window.parent.appModel.finalVideo = "";

            console.log(caseData);
            var caseObject = caseData.caseObject;

            if(caseObject?.content_is?.aiPrompt) {
                window.parent.resumingModelAIPrompt = caseObject.content_is.aiPrompt;
            }

            // Update your resuming model that uses job here?
            // If yes: Uncomment this and make sure your other module detects the resumable model,
            // then handle rendering to reflect the resumable model
            //
            // if(caseObject?.jobId) {
            //     window.parent.appModel.jobId = caseObject.jobId;
            //     var jobEtcUrl = `${finalHost}/jobs?jobId=${caseObject.jobId}`;
            //     var jobEtcResponse = await fetch(jobEtcUrl)
            //     var jobEtcStateData = await jobEtcResponse.json();
            //     // ...
            // }

            window.parent.navController.setQueryWithoutTriggeringPopstate('navigate', SCREENS.WritePrompt);
            window.parent.navController.switchPanel(SCREENS.WritePrompt, true);
        }
    })
    .catch(error => {
        $("#error-text").html("<b>ERROR:</b> " + error);
        $("body").removeClass("d-none");
    });
}

document.addEventListener("DOMContentLoaded", onDomContentLoaded);