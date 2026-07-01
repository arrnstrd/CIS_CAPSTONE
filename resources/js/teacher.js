document.addEventListener('DOMContentLoaded', () => {

    const editTeacherModal = document.getElementById('editTeacherModal');
    const editForm = document.getElementById('editTeacherForm');

    if (!editTeacherModal || !editForm) {
        return;
    }

    editTeacherModal.addEventListener('show.bs.modal', (event) => {

        const button = event.relatedTarget;

        const id = button.dataset.id;
        const firstName = button.dataset.first_name;
        const lastName = button.dataset.last_name;
        const email = button.dataset.email;

        // Update form action
        editForm.action = editForm.dataset.updateUrl.replace(':id', id);

        // Populate inputs
        editForm.querySelector('[name="first_name"]').value = firstName;
        editForm.querySelector('[name="last_name"]').value = lastName;
        editForm.querySelector('[name="email"]').value = email;

    });

    //for edit form
    editForm.addEventListener('submit', function (event) {

        event.preventDefault();

        ajaxCrud.submitAjaxForm(this);

    });


    //for add form ajax
    const addForm = document.getElementById('addTeacherForm');

    if (addForm) {
        addForm.addEventListener('submit', function (event) {
            event.preventDefault();

            ajaxCrud.submitAjaxForm(this);
        });
    }

});