// imported `finalHost` from `app-read-instructions/index.php` which sourced `assets/common.js`
// imported `highlights` from `dictionary-substitutions.js`


/* 
    Check character limit one more time when user submits questionare
    Needed because navigation could have the text pre-filled when user realized not enough photos 
*/

const CHAR_LIMIT = 1000;

function animateUnable(element) {
    var $el = $(element);

    $el.prop("disabled", true);
    var originalColor = $el.css("color");

    // 1. Expand (increase width, for example) (0px if dont want to expand)        
    $el.animate({ width: '+=0px' }, 300)
        // 2. Turn gray
        .animate({ color: '#ccc' }, 200)

        .effect('shake', { distance: 5, times: 2 }, 200)


        // 3. Go back to original color
        .animate({ color: originalColor }, 300, function () {

            // Optionally re-enable after animation completes
            $el.prop("disabled", false);

            // Restore the original width if you want
            $(this).css('width', '');
        });
} // animateUnable

const textEditor = {
    checkHasTypedPollerId: -1,
    textarea: null,
    rewriteBtn: null,
    toast: null,
    btnSubmit: null,

    history: [],
    currentIndex: -1,
    rewritesLeft: 5,
    rewrittenYet: false,

    init() {
        this.textarea = document.getElementById('textarea');
        this.btnSubmit = document.getElementById("submit");

        this.initCheckCharInputted();

        return {
            cleanupCheckCharInputted:this.cleanupCheckCharInputted,
            finalCheck:this.finalCheck,
            getTextareaValue:this.getTextareaValue
        };
    },

    /* If at least one character is inputted, enable the "Continue" button */
    initCheckCharInputted() {
        const textarea = this.textarea

        function _icht_checkHasTypedEl(textareaEl) {
            const charCount = textareaEl.value.length;
            const limit = CHAR_LIMIT;
            const report = document.querySelector("#char-count");

            if (charCount) {
                this.btnSubmit.classList.remove("disabled");
                report.textContent = `${charCount} / ${limit}`;
                if (charCount > limit) {
                    report.classList.replace("text-purple-600", "text-red-500");
                    this.btnSubmit.classList.add("disabled");
                } else {
                    report.classList.remove("text-red-500");
                    this.btnSubmit.classList.remove("disabled");
                }
            } else {

                report.textContent = `0 / ${limit}`;
                this.btnSubmit.classList.add("disabled");
            }
        } // initCheckHasTyped:..checkHasTypedEl

        this.checkHasTypedPollerId = setInterval(() => {
            _icht_checkHasTypedEl.call(this, textarea);
        }, 100);
    },
    // export cleanupCheckCharInputted
    cleanupCheckCharInputted() {
        if (this?.checkHasTypedPollerId) {
            clearInterval(this.checkHasTypedPollerId);
        }
    },
    // export finalCheck
    finalCheck() {
        var passed = true;
        if (this.textarea.value.length === 0) {
            animateUnable(this.textarea);
            alert('Please enter your text.');
            passed = false;
        }
        if (this.textarea.value.length >= CHAR_LIMIT) {
            animateUnable(this.textarea);
            alert('Too many characters. Please shorten your text.');
            passed = false;
        }
        return passed;
    }, // finalCheck

    // export getTextareaValue
    getTextareaValue() {
        return this.textarea.value;
    },
};

// Initialize the editor when the page loads
const {cleanupCheckCharInputted, finalCheck, getTextareaValue} = textEditor.init();
window.cleanupCheckCharInputted = cleanupCheckCharInputted;
window.finalCheck = finalCheck;
window.getTextareaValue = getTextareaValue;

console.log(`Just testing the app? Try this text entry:

Create a slideshow highlighting our company's success journey. Use the uploaded pictures to visually represent key milestones and achievements. Incorporate information from the URLs linked to articles to provide a narrative on our growth story. Ensure each slide tells a part of the story, conveying both visual and textual elements that reflect our progress and accomplishments.
`);