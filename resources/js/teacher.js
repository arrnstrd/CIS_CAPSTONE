document.addEventListener("DOMContentLoaded", () => {
    initializeEditModal();
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

    // Form submission is handled by ajax-crud.js general event listener
}

/*
|--------------------------------------------------------------------------
| Archive & Restore
|--------------------------------------------------------------------------
*/

// ajax-crud.js only auto-handles `data-ajax-delete="subject"`, so teacher
// archive/restore forms are wired up here (add/edit forms are handled by
// ajax-crud.js via the `data-ajax-form` attribute on the forms).
document.addEventListener("submit", function (event) {
    const form = event.target;

    if (
        form?.matches('[data-ajax-delete="teacher"]') ||
        form?.matches('[data-ajax-restore="teacher"]')
    ) {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form);
    }
});
