/**
 * Subject Modal & Delete Handlers (Academic Management)
 * POV: School Admin
 */

document.addEventListener('show.bs.modal', (event) => {
    if (event.target?.id !== 'editSubjectModal') {
        return;
    }

    const button = event.relatedTarget;
    const form = document.getElementById('editSubjectForm');

    if (!button || !form) {
        return;
    }

    form.action = button.dataset.updateUrl || `/subjects/${button.dataset.id}`;
    document.getElementById('edit_subject_code').value = button.dataset.code || '';
    document.getElementById('edit_subject_name').value = button.dataset.name || '';
    document.getElementById('edit_subject_level').value = button.dataset.level || '';
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!form?.matches('[data-ajax-delete="subject"]')) {
        return;
    }

    event.preventDefault();
    if (window.ajaxCrud) {
        window.ajaxCrud.submitAjaxDelete(form, {
            scope: form.dataset.ajaxScope || '#subject-table-pane',
        });
    }
});
