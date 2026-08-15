document.addEventListener("show.bs.modal", function (event) {
    if (event.target?.id !== "editSectionModal") {
        return;
    }

    const button = event.relatedTarget;
    if (!button) {
        return;
    }

    const form = document.getElementById("editSectionForm");
    if (!form) {
        return;
    }

    form.action = form.dataset.updateUrl.replace(":id", button.dataset.id);
    document.getElementById("edit_section_name").value =
        button.dataset.name || "";
    document.getElementById("edit_section_level").value =
        button.dataset.level || "";
    document.getElementById("edit_section_grade_level").value =
        button.dataset.gradeLevel || "";
    document.getElementById("edit_section_advisor_id").value =
        button.dataset.advisorId || "";
    document.getElementById("edit_section_capacity").value =
        button.dataset.capacity || "";
    document.getElementById("edit_section_status").value =
        button.dataset.status || "active";
    document.getElementById("edit_session_type").value =
        button.dataset.sessionType || "";
});

document.addEventListener("hidden.bs.modal", function (event) {
    if (event.target?.id === "addSectionModal") {
        document.getElementById("addSectionForm")?.reset();
    }

    if (event.target?.id === "editSectionModal") {
        document.getElementById("editSectionForm")?.reset();
    }
});

document.addEventListener("submit", function (event) {
    const form = event.target;

    if (form?.id === "addSectionForm" || form?.id === "editSectionForm") {
        event.preventDefault();
        window.ajaxCrud.submitAjaxForm(form, {
            scope: form.dataset.ajaxScope || "#section-table-pane",
        });
        return;
    }

    if (
        form?.matches('[data-ajax-delete="section"]') ||
        form?.matches('[data-ajax-restore="section"]')
    ) {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form, {
            scope: form.dataset.ajaxScope || "#section-table-pane",
        });
    }
});
