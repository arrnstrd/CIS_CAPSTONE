document.addEventListener('show.bs.modal', function (event) {
    if (event.target?.id !== 'editStudentModal') {
        return;
    }

    const btn = event.relatedTarget;
    document.getElementById('edit_lrn').value = btn.dataset.lrn;
    document.getElementById('edit_first_name').value = btn.dataset.first_name;
    document.getElementById('edit_last_name').value = btn.dataset.last_name;
    document.getElementById('edit_middle_name').value = btn.dataset.middle_name;
    document.getElementById('edit_sex').value = btn.dataset.sex;
    document.getElementById('edit_address').value = btn.dataset.address;
    document.getElementById('edit_birthdate').value = btn.dataset.birthdate;
    document.getElementById('edit_status').value = btn.dataset.status;
    document.getElementById('edit_name').value = btn.dataset.name;
    document.getElementById('edit_relationship').value = btn.dataset.relationship;
    document.getElementById('edit_email').value = btn.dataset.email;
    document.getElementById('editStudentForm').action = '/students/' + btn.dataset.id;
});

document.addEventListener('hidden.bs.modal', function (event) {
    if (event.target?.id === 'addStudentModal') {
        document.getElementById('addStudentForm')?.reset();
    }
});

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (form?.id === 'addStudentForm' || form?.id === 'editStudentForm') {
        event.preventDefault();
        window.ajaxCrud.submitAjaxForm(form);
    }

    if (form?.matches('[data-ajax-delete="student"]')) {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form);
    }
});
