/**
 * Teaching Assignments — CRUD modal controller.
 *
 * Relies on ajax-crud.js for form submission and deletion.
 */

const form = document.getElementById("assignmentForm");
const modalEl = document.getElementById("assignmentModal");
const methodInput = document.getElementById("formMethod");
const submitText = document.getElementById("formSubmitText");

const sectionSelect = document.getElementById("field_section_id");
const teacherSelect = document.getElementById("field_teacher_id");
const schoolYearSelect = document.getElementById("field_school_year_id");
const subjectSelect = document.getElementById("field_subject_id");
const singleSubjectContainer = document.getElementById("single_subject_container");
const massSubjectContainer = document.getElementById("mass_subject_container");
const massSubjectsList = document.getElementById("mass_subjects_list");
const btnSelectAll = document.getElementById("btnSelectAllSubjects");
const btnDeselectAll = document.getElementById("btnDeselectAllSubjects");

function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function setSingleSubjectMode() {
    if (singleSubjectContainer) singleSubjectContainer.classList.remove("d-none");
    if (subjectSelect) {
        subjectSelect.disabled = false;
        subjectSelect.required = true;
    }
    if (massSubjectContainer) massSubjectContainer.classList.add("d-none");
    if (massSubjectsList) {
        massSubjectsList.innerHTML = '<div class="text-muted small text-center py-2">Select a section and school year to load grade-specific subjects.</div>';
    }
}

function setMassSubjectMode() {
    if (singleSubjectContainer) singleSubjectContainer.classList.add("d-none");
    if (subjectSelect) {
        subjectSelect.disabled = true;
        subjectSelect.required = false;
    }
    if (massSubjectContainer) massSubjectContainer.classList.remove("d-none");
}

function renderMassSubjects(subjects) {
    if (!massSubjectsList) return;

    if (!subjects || subjects.length === 0) {
        massSubjectsList.innerHTML =
            '<div class="text-muted small text-center py-2">No subjects found for Grade 1–3 (elementary level).</div>';
        return;
    }

    massSubjectsList.innerHTML = "";
    const grid = document.createElement("div");
    grid.className = "row g-2";

    subjects.forEach((subject) => {
        const col = document.createElement("div");
        col.className = "col-md-6";

        let badgeHtml = "";
        let isChecked = false;
        let isDisabled = false;

        if (subject.is_assigned) {
            isDisabled = true;
            if (subject.is_current_teacher) {
                isChecked = true;
                badgeHtml =
                    '<span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Current Teacher</span>';
            } else {
                const teacherName =
                    subject.assigned_teacher_name || "Another Teacher";
                badgeHtml = `<span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem;" title="Assigned to ${escapeHtml(
                    teacherName
                )}">Assigned: ${escapeHtml(teacherName)}</span>`;
            }
        } else {
            isChecked = true;
        }

        col.innerHTML = `
            <div class="form-check p-2 rounded border bg-white h-100 d-flex align-items-center">
                <input class="form-check-input mass-subject-checkbox ms-1 me-2" type="checkbox" 
                    name="subject_ids[]" 
                    value="${subject.id}" 
                    id="chk_subj_${subject.id}" 
                    ${isChecked ? "checked" : ""} 
                    ${isDisabled ? "disabled" : ""}>
                <label class="form-check-label small flex-grow-1 user-select-none mb-0 text-truncate" for="chk_subj_${subject.id}">
                    <span class="fw-semibold">${escapeHtml(subject.name)}</span>
                    ${subject.code ? `<span class="text-muted" style="font-size: 0.75rem;">(${escapeHtml(subject.code)})</span>` : ""}
                    ${badgeHtml}
                </label>
            </div>
        `;
        grid.appendChild(col);
    });

    massSubjectsList.appendChild(grid);
}

async function loadSectionContext() {
    // Mass assignment only applies in create mode
    if (methodInput.value !== "POST") {
        setSingleSubjectMode();
        return;
    }

    const selectedOption = sectionSelect?.selectedOptions[0];
    if (!selectedOption || !selectedOption.value) {
        setSingleSubjectMode();
        return;
    }

    const gradeLevel = parseInt(selectedOption.dataset.gradeLevel, 10);
    const isPrimary = [1, 2, 3].includes(gradeLevel);

    if (!isPrimary) {
        setSingleSubjectMode();
        return;
    }

    setMassSubjectMode();

    const sectionId = selectedOption.value;
    const schoolYearId = schoolYearSelect?.value || "";
    const teacherId = teacherSelect?.value || "";

    massSubjectsList.innerHTML =
        '<div class="text-center py-3 text-muted small"><span class="spinner-border spinner-border-sm me-2"></span>Loading subjects...</div>';

    try {
        const queryParams = new URLSearchParams({
            section_id: sectionId,
        });
        if (schoolYearId) queryParams.set("school_year_id", schoolYearId);
        if (teacherId) queryParams.set("teacher_id", teacherId);

        const response = await fetch(
            `/teaching-assignments/section-context?${queryParams.toString()}`,
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            }
        );

        if (!response.ok) {
            throw new Error("Failed to fetch section context");
        }

        const data = await response.json();
        renderMassSubjects(data.subjects || []);
    } catch (err) {
        massSubjectsList.innerHTML =
            '<div class="text-danger small text-center py-2">Failed to load subjects. Please try again.</div>';
    }
}

function resetForm() {
    form.reset();
    if (window.ajaxCrud) {
        window.ajaxCrud.clearFormErrors(form);
    }
    document.getElementById("assignmentId").value = "";
    setSingleSubjectMode();
}

window.openCreateModal = function () {
    resetForm();
    methodInput.value = "POST";
    form.action = "/teaching-assignments";
    submitText.textContent = "Create";
    modalEl.querySelector(".modal-title").textContent =
        "New Teaching Assignment";

    if (schoolYearSelect && !schoolYearSelect.value) {
        const activeOpt = schoolYearSelect.querySelector('option[data-active="1"]');
        if (activeOpt) schoolYearSelect.value = activeOpt.value;
    }
};

window.openEditModal = function (id) {
    resetForm();

    const row = document.querySelector(`tr[data-assignment-id="${id}"]`);
    if (!row) return;

    methodInput.value = "PUT";
    form.action = `/teaching-assignments/${id}`;
    submitText.textContent = "Update";
    modalEl.querySelector(".modal-title").textContent =
        "Edit Teaching Assignment";

    document.getElementById("assignmentId").value = id;
    document.getElementById("field_teacher_id").value =
        row.dataset.teacherId || "";
    document.getElementById("field_section_id").value =
        row.dataset.sectionId || "";
    document.getElementById("field_school_year_id").value =
        row.dataset.schoolYearId || "";
    document.getElementById("field_status").value =
        row.dataset.status || "active";

    setSingleSubjectMode();
    document.getElementById("field_subject_id").value =
        row.dataset.subjectId || "";
};

// Event Listeners for dynamic mode switching
sectionSelect?.addEventListener("change", loadSectionContext);

schoolYearSelect?.addEventListener("change", () => {
    const selectedOption = sectionSelect?.selectedOptions[0];
    const gradeLevel = parseInt(selectedOption?.dataset?.gradeLevel, 10);
    if ([1, 2, 3].includes(gradeLevel)) {
        loadSectionContext();
    }
});

teacherSelect?.addEventListener("change", () => {
    const selectedOption = sectionSelect?.selectedOptions[0];
    const gradeLevel = parseInt(selectedOption?.dataset?.gradeLevel, 10);
    if ([1, 2, 3].includes(gradeLevel)) {
        loadSectionContext();
    }
});

btnSelectAll?.addEventListener("click", () => {
    massSubjectsList
        ?.querySelectorAll('input[type="checkbox"]:not(:disabled)')
        .forEach((chk) => {
            chk.checked = true;
        });
});

btnDeselectAll?.addEventListener("click", () => {
    massSubjectsList
        ?.querySelectorAll('input[type="checkbox"]:not(:disabled)')
        .forEach((chk) => {
            chk.checked = false;
        });
});

// --- AJAX delete handler ---
document.addEventListener("submit", (event) => {
    const formEl = event.target;
    if (!formEl?.matches('[data-ajax-delete="assignment"]')) return;
    event.preventDefault();
    window.ajaxCrud.submitAjaxDelete(formEl, { scope: null });
});
