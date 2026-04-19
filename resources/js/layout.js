 // Live date display
    (function () {
        const dateEl = document.getElementById('liveDate');
        if (dateEl) {
            const now = new Date();
            dateEl.textContent = now.toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: '2-digit'
            });
        }
    })();


    