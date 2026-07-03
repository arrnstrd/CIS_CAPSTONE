document.addEventListener("hidden.bs.modal", function (event) {
    if (event.target?.id === "addScheduleModal") {
        document.getElementById("addScheduleForm")?.reset();
    }

    if (event.target?.id === "editScheduleModal") {
        document.getElementById("editScheduleForm")?.reset();
    }

    if (event.target?.id === "deleteScheduleModal") {
        document.getElementById("deleteScheduleForm")?.reset();
    }
});

document.addEventListener("click", function (event) {
    const editButton = event.target.closest(".js-edit-schedule");
    const deleteButton = event.target.closest(".js-delete-schedule");

    if (editButton) {
        const form = document.getElementById("editScheduleForm");
        const updateUrlTemplate = form?.dataset.updateUrl || "";
        const recordId = editButton.dataset.id;

        if (!form || !recordId) {
            return;
        }

        form.action = updateUrlTemplate.replace("__ID__", recordId);

        document.getElementById("edit_schedule_level").value =
            editButton.dataset.level || "";
        document.getElementById("edit_schedule_session_type").value =
            editButton.dataset.sessionType || "";
        document.getElementById("edit_schedule_in_start").value =
            editButton.dataset.inStart || "";
        document.getElementById("edit_schedule_in_end").value =
            editButton.dataset.inEnd || "";
        document.getElementById("edit_schedule_late_threshold").value =
            editButton.dataset.lateThreshold || "";
        document.getElementById("edit_schedule_out_start").value =
            editButton.dataset.outStart || "";
        document.getElementById("edit_schedule_out_end").value =
            editButton.dataset.outEnd || "";
    }

    if (deleteButton) {
        const form = document.getElementById("deleteScheduleForm");
        const deleteUrlTemplate = form?.dataset.deleteUrl || "";
        const recordId = deleteButton.dataset.id;

        if (!form || !recordId) {
            return;
        }

        form.action = deleteUrlTemplate.replace("__ID__", recordId);
        document.getElementById("delete_schedule_level").textContent =
            deleteButton.dataset.level || "-";
        document.getElementById("delete_schedule_session_type").textContent =
            deleteButton.dataset.sessionType || "-";
    }
});

document.addEventListener("submit", function (event) {
    const form = event.target;

    if (form?.id === "addScheduleForm") {
        event.preventDefault();
        window.ajaxCrud.submitAjaxForm(form);
        return;
    }

    if (form?.id === "editScheduleForm") {
        event.preventDefault();
        window.ajaxCrud.submitAjaxForm(form);
        return;
    }

    if (form?.id === "deleteScheduleForm") {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form);
    }
});
