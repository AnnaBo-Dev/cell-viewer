window.addEventListener('load', function() {
    const importBtn = document.querySelector('#import-btn');

    // document.getElementById('check').addAttribute('hidden');
    // document.getElementById('cross').addAttribute('hidden');
    /**
     * if import is clicked and import is loading show laoder icon
     * if import is done show check icon and not show error icon
     */
    importBtn.addEventListener('click', function() {
        // show the spinner icon
        document.getElementById('loader').removeAttribute('hidden');
    });
});

/** hide or unhide elements */
function hideElement(elementField, bool) {
    if (bool === true) {
        document.querySelector(elementField).style.display = 'none';
    }
    if (bool === false) {
        document.querySelector(elementField).style.display = '';
    }
}


