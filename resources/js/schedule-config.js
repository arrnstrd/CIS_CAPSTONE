document.addEventListener('hidden.bs.modal', function (event) {
    if (event.target?.id === 'addScheduleModal') {
        document.getElementById('addScheduleForm')?.reset();
    }
});

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (form?.id === 'addScheduleForm') {
        event.preventDefault();
        window.ajaxCrud.submitAjaxForm(form);
    }
});
