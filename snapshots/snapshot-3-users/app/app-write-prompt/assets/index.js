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
    undoBtn: null,
    rewritesLeftEl: null,
    toast: null,
    btnSubmit: null,

    history: [],
    currentIndex: -1,
    rewritesLeft: 5,
    rewrittenYet: false,

    onFirstRewrite() {
        document.querySelector(".show-after-rewrite").classList.remove("d-none");
    },

    init() {
        this.textarea = document.getElementById('textarea');
        this.rewriteBtn = document.getElementById('rewriteBtn');
        this.undoBtn = document.getElementById('undoBtn');
        this.rewritesLeftEl = document.getElementById('rewritesLeft');
        this.toast = document.getElementById('toast');
        this.btnSubmit = document.getElementById("submit");

        this.history = [];
        this.currentIndex = -1;
        this.rewritesLeft = 5;

        this.setupEventListeners();

        this.initCheckCharInputted();

        if(window.parent.resumingModelAIPrompt) {
            this.textarea.value = window.parent.resumingModelAIPrompt;
        }

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
            animateUnable(propertyDesc);
            alert('Please enter your property description.');
            passed = false;
        }
        if (this.textarea.value.length >= CHAR_LIMIT) {
            animateUnable(propertyDesc);
            alert('Too many characters. Please shorten your description.');
            passed = false;
        }
        return passed;
    }, // finalCheck

    // export getTextareaValue
    getTextareaValue() {
        return this.textarea.value;
    },

    setupEventListeners() {
        this.rewriteBtn.addEventListener('click', () => this.handleRewrite());
        this.undoBtn.addEventListener('click', () => this.undo());
    },

    async handleRewrite() {
        if (this.rewritesLeft <= 0) return;

        const currentText = this.textarea.value;
        if (!currentText.trim()) return;

        this.rewriteBtn.disabled = true;
        this.rewriteBtn.classList.add("disabled");
        this.showLoadingSpinner();

        try {
            const rewrittenText = await this.getAIRewrite(currentText);
            if (rewrittenText.includes("ERROR")) {
                alert("ERROR: ...");
                return;
            }

            if (!this.rewrittenYet) {
                this.rewrittenYet = true;
                this.onFirstRewrite();
            }
            this.textarea.value = rewrittenText;
            this.rewritesLeft--;
            this.updateUIRewriteCount();
            this.showToast();
        } catch (error) {
            console.error('Error during rewrite:', error);
        } finally {
            this.hideLoadingSpinner();
            this.rewriteBtn.disabled = false;
            this.rewriteBtn.classList.remove("disabled");
        }
    },

    async getAIRewrite(text) {
        try {
            const userId = window.parent.getUserId();
            const appId = window.parent.getAppId();
            const caseId = window.parent.getCaseId();
            const response = await fetch(finalHost + "/media/interim/prompt/rewrite", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text, userId, appId, caseId })
            });

            const data = await response.json();

            this.addToHistory(text);

            return data.rewrittenText;

        } catch (error) {
            const errorMessage = "Error rewriting with AI: " + error;
            console.error(errorMessage);
            alert(errorMessage);
            return text;
        }
    },

    addToHistory(text) {
        this.history = this.history.slice(0, this.currentIndex + 1);
        this.history.push(text);
        this.currentIndex = this.history.length;
        this.updateUIHistoryButtons();
    },

    undo() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.textarea.value = this.history[this.currentIndex];
            this.updateUIHistoryButtons();
        }
    },

    updateUIHistoryButtons() {
        this.undoBtn.disabled = this.currentIndex <= 0;
    },

    updateUIRewriteCount() {
        this.rewritesLeftEl.textContent = this.rewritesLeft;
        this.rewriteBtn.disabled = this.rewritesLeft <= 0;
    },

    showLoadingSpinner() {
        const spinner = this.rewriteBtn.querySelector('.loading-spinner');
        const wand = this.rewriteBtn.querySelector('.fa-magic');
        spinner.classList.remove('hidden');
        wand.classList.add('hidden');
    },

    hideLoadingSpinner() {
        const spinner = this.rewriteBtn.querySelector('.loading-spinner');
        const wand = this.rewriteBtn.querySelector('.fa-magic');
        spinner.classList.add('hidden');
        wand.classList.remove('hidden');
    },

    showToast() {
        window.parent.document.querySelector('#bottom-left').innerHTML = `
            <div class="p-4 rounded-lg transition-opacity duration-300 bgcolor-brand-contrasted textcolor-brand">
                Description updated successfully!
            </div>
        `;
        setTimeout(() => {
            window.parent.document.querySelector('#bottom-left').innerHTML = '';
        }, 2000);
    }
};

// Initialize the editor when the page loads
const {cleanupCheckCharInputted, finalCheck, getTextareaValue} = textEditor.init();
window.cleanupCheckCharInputted = cleanupCheckCharInputted;
window.finalCheck = finalCheck;
window.getTextareaValue = getTextareaValue;

console.log(`Just testing the app? Try this text entry:

Create a slideshow highlighting our company's success journey. Use the uploaded pictures to visually represent key milestones and achievements. Incorporate information from the URLs linked to articles to provide a narrative on our growth story. Ensure each slide tells a part of the story, conveying both visual and textual elements that reflect our progress and accomplishments.
`);