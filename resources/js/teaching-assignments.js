/**
 * Teaching Assignments — CRUD modal controller.
 *
 * Relies on ajax-crud.js for form submission and deletion.
 */

const form = document.getElementById("assignmentForm");
const modalEl = document.getElementById("assignmentModal");
const methodInput = document.getElementById("formMethod");
const submitText = document.getElementById("formSubmitText");

function resetForm() {
    form.reset();
    if (window.ajaxCrud) {
        window.ajaxCrud.clearFormErrors(form);
    }
    document.getElementById("assignmentId").value = "";
}

window.openCreateModal = function () {
    resetForm();
    methodInput.value = "POST";
    form.action = "/teacher/teaching-assignments";
    submitText.textContent = "Create";
    modalEl.querySelector(".modal-title").textContent =
        "New Teaching Assignment";
};

window.openEditModal = function (id) {
    resetForm();

    const row = document.querySelector(`tr[data-assignment-id="${id}"]`);
    if (!row) return;

    methodInput.value = "PUT";
    form.action = `/teacher/teaching-assignments/${id}`;
    submitText.textContent = "Update";
    modalEl.querySelector(".modal-title").textContent =
        "Edit Teaching Assignment";

    document.getElementById("assignmentId").value = id;
    document.getElementById("field_teacher_id").value =
        row.dataset.teacherId || "";
    document.getElementById("field_subject_id").value =
        row.dataset.subjectId || "";
    document.getElementById("field_section_id").value =
        row.dataset.sectionId || "";
    document.getElementById("field_school_year_id").value =
        row.dataset.schoolYearId || "";
    document.getElementById("field_session_type").value =
        row.dataset.sessionType || "";
    document.getElementById("field_status").value =
        row.dataset.status || "active";
    document.getElementById("field_in_start").value = row.dataset.inStart || "";
    document.getElementById("field_late_threshold").value =
        row.dataset.lateThreshold || "";
    document.getElementById("field_out_end").value = row.dataset.outEnd || "";
};

// --- AJAX delete handler ---
document.addEventListener("submit", (event) => {
    const formEl = event.target;
    if (!formEl?.matches('[data-ajax-delete="assignment"]')) return;
    event.preventDefault();
    window.ajaxCrud.submitAjaxDelete(formEl, { scope: null });
});
