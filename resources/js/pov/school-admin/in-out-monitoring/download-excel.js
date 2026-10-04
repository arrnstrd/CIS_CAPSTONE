/**
 * Date range validation for the Download Excel modal
 * (Time In / Time Out history).
 *
 * Guards against an end date earlier than the start date, and limits the
 * range to one month (31 days) to match the backend validation.
 */
(function () {
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');
    const err = document.getElementById('dlDateError');

    const MAX_DAYS = 31;

    if (!start || !end || !err) {
        return;
    }

    function setEndMax() {
        if (!start.value) {
            end.max = '';
            return;
        }

        const parsed = new Date(start.value);
        parsed.setDate(parsed.getDate() + MAX_DAYS);
        end.max = parsed.toISOString().split('T')[0];
    }

    function validate() {
        if (!start.value || !end.value) {
            err.style.display = 'none';
            return;
        }

        if (end.value < start.value) {
            err.textContent = 'End date cannot be earlier than the start date.';
            err.style.display = 'block';
            return;
        }

        const diff = (new Date(end.value) - new Date(start.value)) / 86400000;

        if (diff > MAX_DAYS) {
            err.textContent = `The date range cannot exceed one month (${MAX_DAYS} days).`;
            err.style.display = 'block';
            return;
        }

        err.style.display = 'none';
    }

    start.addEventListener('change', () => {
        setEndMax();
        validate();
    });

    end.addEventListener('change', validate);

    setEndMax();
})();
