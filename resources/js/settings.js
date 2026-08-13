document.addEventListener("show.bs.modal", function (event) {
    if (event.target?.id !== "editSchoolYearModal") {
        return;
    }

    const button = event.relatedTarget;
    if (!button) {
        return;
    }

    const form = document.getElementById("editSchoolYearForm");
    if (!form) {
        return;
    }

    form.action = form.dataset.updateUrl.replace(":id", button.dataset.id);
    document.getElementById("edit_school_year").value =
        button.dataset.schoolYear || "";
    document.getElementById("edit_is_active").checked =
        button.dataset.isActive === "1";
});

document.addEventListener("hidden.bs.modal", function (event) {
    if (event.target?.id === "addSchoolYearModal") {
        document.getElementById("addSchoolYearForm")?.reset();
        document.getElementById("add_is_active").checked = true;
    }

    if (event.target?.id === "editSchoolYearModal") {
        document.getElementById("editSchoolYearForm")?.reset();
    }
});

document.addEventListener("submit", function (event) {
    const form = event.target;

    if (form?.id === "addSchoolYearForm" || form?.id === "editSchoolYearForm") {
        event.preventDefault();
        window.ajaxCrud.submitAjaxForm(form, {
            scope: form.dataset.ajaxScope || "#school-year-table-pane",
        });
        return;
    }

    if (
        form?.matches('[data-ajax-delete="school-year"]') ||
        form?.matches('[data-ajax-restore="school-year"]')
    ) {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form, {
            scope: form.dataset.ajaxScope || "#school-year-table-pane",
        });
    }
});
