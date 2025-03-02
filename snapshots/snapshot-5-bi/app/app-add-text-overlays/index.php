<!-- Module type: PHP partial at an iframe module "app-upload-files" -->
<!-- /* Already have tailwind 2.2.19 and font awesome 5.13.1 loaded via app-upload-files */ -->
<!-- /* Trigger on with (although inappropriate because not tied to thumbnail scene id): document.getElementById('overlayPanels').classList.toggle('inactive'); */-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partial</title>

    <link href="//cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/common.css">

    <?php
    /* config-phase */
    $pageMode = [
        'MODES' => [
            'DEMO' => 0,
            'LIVE' => 1
        ],
        'currentMode' => null
    ];

    // -> CONFIG HERE:
    $pageMode['currentMode'] = $pageMode['MODES']['LIVE'];

    // Determine $html based on the current mode
    if ($pageMode['currentMode'] === $pageMode['MODES']['LIVE']) {
        ob_start();
        ?>

            <script>
                window.labelGraphicMode = false;
            </script>
            
        <?php
        $html = ob_get_clean();
    } else {
        ob_start();
        ?>

            <script>
                // If demo version, show the panel
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('overlayPanels').classList.remove('inactive');
                });
            </script>

        <?php
        $html = ob_get_clean();
    } // else

    echo $html;
    ?>

    <style id="overlayPanels__effects">
    /* Dynamic effects will be inserted here */
    </style>
    <style>
        #overlayPanels-tabs .active {
            background-color: var(--primary-color) !important;
            color: var(--primary-color-contrasted) !important;
        }
        #overlayPanels .panels .active {
            background-color: var(--primary-color) !important;
            color: var(--primary-color-contrasted) !important;
        }
        .w-fit {
            width: fit-content;
        }

        .inactive, .translate-y-full {
            --tw-translate-y: 110% !important;
        }
    </style>

</head>
<body>

<!-- Bottom Panel -->
<div id="overlayPanels" 
    class="modal-panel fixed left-0 bottom-8 w-full bg-white border rounded-sm border-gray-300 shadow-lg transform inactive transition-transform duration-300 z-20">

  <!-- Tabs -->
  <div id="overlayPanels-tabs" class="flex border-b relative">

    <button class="text-gray-500 hover:text-gray-700 absolute right-2 top-2" onclick="closeOverlaysPanel()">
        <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="panels">

    <section id="overlayPanels__edit" class="p-4 d-none--less-important">

        <div class="text-center">
            <h5 class="text-lg font-semibold">Label graphic to add more context</h5>
        </div>

        <div class="flex justify-center items-center w-full mb-4">
            <div class="text-center mx-auto w-75">By labeling the graphic, you can explain the graphic to the AI that creates the slideshow.</div>
        </div>
        
        <div id="please-select-preview" class="mt-4 flex flex-col w-fit mx-auto items-center">
            <p class="font-semibold mb-1" for="overlayText">Select a preview thumbnail to label it</p>
        </div>

        <div id="overlayText-wrapper" class="mt-4 flex flex-col w-fit mx-auto items-center hidden">
            <label class="mb-1 text-center" for="overlayText"><span class="font-semibold">Text Content for</span><br/><span id="overlayText-filename"></span></label>
            <input type="text" id="overlayText" class="w-50 p-2 border rounded" rows="2" placeholder="Enter your text here...">
        </div>

    </section>

  </div>
</div>

<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://raw.githack.com/tkambler/whenLive/master/src/jquery.whenlive.js"></script>

<script>
    window.labelsModel = {};
    var overlayPanelManager = {
        currentItemId: null, // Add property to track the currently selected item

        saveSceneOverlay() {
            const text = document.getElementById('overlayText').value;
            const itemId = this.currentItemId;
            window.labelsModel[itemId] = text;
            
            if (itemId) {
                // Save the overlay text for this item (could store in localStorage or send to server)
                console.log(`Labeling ${itemId} as ${text}`);
                // Example: localStorage.setItem(`overlay_${itemId}`, text);
            }
        },

        // Add function to load existing overlay text
        loadSceneOverlay(itemId) {
            this.currentItemId = itemId;
            let savedText = window?.labelsModel?.[itemId] || "";
            document.getElementById('overlayText').value = savedText || '';
        },

        init: function () {
        
            $(".preview-item").whenLive((el)=> {
                // 'this' refers to the DOM element in this context
                $(el).on('click', function(event) { 
                    var el = event.currentTarget;
            
                    // Store the current item id or data for reference
                    const itemId = el.querySelector('.filename').textContent;
                    if (itemId) {
                        overlayPanelManager.currentItemId = itemId;
                        //var that = overlayPanelManager;
                        console.log("itemId", itemId);

                        document.querySelector("#please-select-preview").classList.add("hidden");
                        document.querySelector("#overlayText-wrapper").classList.remove("hidden");
                        document.querySelector("#overlayText-filename").textContent = itemId;
                        document.querySelector("#overlayText").value = "";

                        // Load existing overlay text if available
                        if(window?.labelsModel?.[itemId]) {
                            overlayPanelManager.loadSceneOverlay(itemId);
                        } 
                        // else {
                        //     document.querySelector("#please-select-preview").classList.add("hidden");
                        //     document.querySelector("#overlayText-wrapper").classList.remove("hidden");
                        //     document.querySelector("#overlayText-filename").textContent = itemId;
                        //     overlayPanelManager.saveSceneOverlay(); // will save empty entry
                        // }
                    }
                });
            }); // whenLive

            document.getElementById("overlayText").addEventListener("keyup", function(event) {
                overlayPanelManager.saveSceneOverlay();
            });

            return {
                openOverlayPanel: this.openOverlayPanel.bind(this),
                closeOverlaysPanel: this.closeOverlaysPanel.bind(this)
            };
        }, // init

    // export openOverlayPanel
    openOverlayPanel(index) {
        // Show panel
        const panel = document.getElementById('overlayPanels');
        panel.classList.remove('inactive');
    },

    // export closeOverlaysPanel
    closeOverlaysPanel() {
        // Unload the panel
        this.currentItemId = null;
        document.querySelector("#please-select-preview").classList.remove("hidden");
        document.querySelector("#overlayText-wrapper").classList.add("hidden");

        const panel = document.getElementById('overlayPanels');
        panel.classList.add('inactive');
    },
}; // overlayPanelManager


// Initialize ScenePanelManager
const {openOverlayPanel, closeOverlaysPanel} = overlayPanelManager.init();
window.openOverlayPanel = openOverlayPanel;
window.closeOverlaysPanel = closeOverlaysPanel;

</script>

</body>
</html>