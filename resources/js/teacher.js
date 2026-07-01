document.addEventListener("DOMContentLoaded", () => {
    initializeEditModal();
    initializeAddForm();
    initializeTableActions();
});

/*
|--------------------------------------------------------------------------
| Edit Teacher
|--------------------------------------------------------------------------
*/

function initializeEditModal() {
    const editTeacherModal = document.getElementById("editTeacherModal");
    const editForm = document.getElementById("editTeacherForm");

    if (!editTeacherModal || !editForm) {
        return;
    }

    editTeacherModal.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;

        const id = button.dataset.id;
        const firstName = button.dataset.first_name;
        const lastName = button.dataset.last_name;
        const email = button.dataset.email;

        editForm.action = editForm.dataset.updateUrl.replace(":id", id);

        editForm.querySelector('[name="first_name"]').value = firstName;
        editForm.querySelector('[name="last_name"]').value = lastName;
        editForm.querySelector('[name="email"]').value = email;
    });

    editForm.addEventListener("submit", function (event) {
        event.preventDefault();

        ajaxCrud.submitAjaxForm(this);
    });
}

/*
|--------------------------------------------------------------------------
| Add Teacher
|--------------------------------------------------------------------------
*/

function initializeAddForm() {
    const addForm = document.getElementById("addTeacherForm");

    if (!addForm) {
        return;
    }

    addForm.addEventListener("submit", function (event) {
        event.preventDefault();

        ajaxCrud.submitAjaxForm(this);
    });
}

/*
|--------------------------------------------------------------------------
| Archive & Restore
|--------------------------------------------------------------------------
*/

function initializeTableActions() {
    document.addEventListener("submit", function (event) {
        const form = event.target;

        if (form.matches('[data-ajax-delete="teacher"]')) {
            event.preventDefault();

            ajaxCrud.submitAjaxDelete(form);

            return;
        }

        if (form.matches('[data-ajax-restore="teacher"]')) {
            event.preventDefault();

            ajaxCrud.submitAjaxDelete(form);
        }
    });
}
