if (window.parent.location.href.includes("?login")) {
    window.parent.document.querySelector('#link-login').dispatchEvent(new Event('click'));

    const url = new URL(window.parent.location.href);
    const params = url.searchParams;

    if (params.has('login')) {
        params.delete('login');
        const newUrl = url.toString();
        window.parent.history.replaceState({}, document.title, newUrl);
    }


} else if (window.parent.location.href.includes("?signup")) {
    window.parent.document.querySelector('#link-signup').dispatchEvent(new Event('click'));

    const url = new URL(window.parent.location.href);
    const params = url.searchParams;

    if (params.has('signup')) {
        params.delete('signup');

        // Construct the new URL without the 'signup' query parameter
        const newUrl = url.toString();

        // Replace the current history entry with the new URL
        window.parent.history.replaceState({}, document.title, newUrl);
    }
}

function signupNext(btn) {
    if (btn.hasAttribute("disabled") || btn.hasAttribute("data-not-ready"))
        return;
    document.querySelector("#signup-page-1").classList.add("hidden");
    document.querySelector("#signup-page-2").classList.remove("hidden");

    document.querySelector("#signup-btn-1").removeAttribute("disabled");
    document.querySelector("#signup-btn-2").setAttribute("disabled", true);
    document.querySelector("#signup-btn-3").removeAttribute("disabled");
}

function signupPrev(btn) {
    if (btn.hasAttribute("disabled") || btn.hasAttribute("data-not-ready"))
        return;
    document.querySelector("#signup-page-1").classList.remove("hidden");
    document.querySelector("#signup-page-2").classList.add("hidden");

    document.querySelector("#signup-btn-1").setAttribute("disabled", true);
    document.querySelector("#signup-btn-2").removeAttribute("disabled");
}

function signupFinal(btn) {
    if (btn.hasAttribute("disabled") || btn.hasAttribute("data-not-ready"))
        return false;
    else
        return true;
}

function assessReadySignupForPage2() {
    var a = document.querySelector("#email-signup").value.length;
    var aa = (/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/).test(document.querySelector("#email-signup").value) ? 1 : 0;
    var b = document.querySelector("#password-signup").value.length;
    var c = document.querySelector("#password-signup-confirm").value.length;
    var d = document.querySelector("#password-signup").value === document.querySelector("#password-signup-confirm").value ? 1 : 0;

    console.log({ val1: document.querySelector("#password-signup").value, val2: document.querySelector("#password-signup-confirm").value, multi: (a * b * c * d) })
    if ((a * aa * b * c * d) > 0) {
        document.querySelector("#signup-btn-2").removeAttribute("data-not-ready");
    } else {
        document.querySelector("#signup-btn-2").setAttribute("data-not-ready", "true");
    }
} // assessReadySignupForPage2