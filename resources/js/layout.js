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

// School Admin Top Navigation Profile Dropdown
(function () {
    function initTopNavDropdown() {
        const trigger = document.getElementById('saTopNavProfileTrigger');
        const dropdown = document.getElementById('saTopNavDropdown');
        if (!trigger || !dropdown) return;

        function closeDropdown() {
            dropdown.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            dropdown.setAttribute('aria-hidden', 'true');
        }

        function openDropdown() {
            dropdown.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            dropdown.setAttribute('aria-hidden', 'false');
        }

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = dropdown.classList.contains('is-open');
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && dropdown.classList.contains('is-open')) {
                closeDropdown();
                trigger.focus();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTopNavDropdown);
    } else {
        initTopNavDropdown();
    }
})();