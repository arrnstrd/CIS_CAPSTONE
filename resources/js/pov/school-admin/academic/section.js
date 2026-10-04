function resolveDepartmentLevel(gradeVal) {
    const grade = parseInt(gradeVal, 10);
    if (isNaN(grade) || grade < 1) return "";
    if (grade >= 1 && grade <= 6) return "elementary";
    if (grade >= 7 && grade <= 10) return "highschool";
    if (grade >= 11 && grade <= 12) return "senior_high_school";
    return "";
}

function updateAddSectionState() {
    const gradeSelect = document.getElementById("add_section_grade_level");
    const levelSelect = document.getElementById("add_section_level");
    const hint = document.getElementById("add_section_grade_hint");
    const fields = document.querySelectorAll("#addSectionForm .js-section-field");
    const submitBtn = document.querySelector("#addSectionForm .js-section-submit");

    if (!gradeSelect || !levelSelect) return;

    const selectedGrade = gradeSelect.value;
    const resolvedLevel = resolveDepartmentLevel(selectedGrade);

    if (resolvedLevel) {
        levelSelect.value = resolvedLevel;
        fields.forEach((field) => {
            if (field !== levelSelect) field.disabled = false;
        });
        if (submitBtn) submitBtn.disabled = false;
        if (hint) hint.classList.add("d-none");
    } else {
        levelSelect.value = "";
        fields.forEach((field) => {
            if (field !== levelSelect) field.disabled = true;
        });
        if (submitBtn) submitBtn.disabled = true;
        if (hint) hint.classList.remove("d-none");
    }
}

function updateEditSectionState() {
    const gradeSelect = document.getElementById("edit_section_grade_level");
    const levelSelect = document.getElementById("edit_section_level");
    if (!gradeSelect || !levelSelect) return;

    const resolvedLevel = resolveDepartmentLevel(gradeSelect.value);
    if (resolvedLevel) {
        levelSelect.value = resolvedLevel;
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const addGradeSelect = document.getElementById("add_section_grade_level");
    if (addGradeSelect) {
        addGradeSelect.addEventListener("change", updateAddSectionState);
    }

    const editGradeSelect = document.getElementById("edit_section_grade_level");
    if (editGradeSelect) {
        editGradeSelect.addEventListener("change", updateEditSectionState);
    }
});

document.addEventListener("show.bs.modal", function (event) {
    if (event.target?.id === "addSectionModal") {
        updateAddSectionState();
        return;
    }

    if (event.target?.id === "editSectionModal") {
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
        const gradeVal = button.dataset.gradeLevel || "";
        document.getElementById("edit_section_grade_level").value = gradeVal;
        document.getElementById("edit_section_level").value =
            button.dataset.level || resolveDepartmentLevel(gradeVal);
        document.getElementById("edit_section_advisor_id").value =
            button.dataset.advisorId || "";
        document.getElementById("edit_section_capacity").value =
            button.dataset.capacity || "";
        document.getElementById("edit_section_status").value =
            button.dataset.status || "active";
        document.getElementById("edit_session_type").value =
            button.dataset.sessionType || "";
        return;
    }

    if (event.target?.id === "hardDeleteSectionModal") {
        const button = event.relatedTarget;
        if (!button) {
            return;
        }

        const form = document.getElementById("hardDeleteSectionForm");
        if (!form) {
            return;
        }

        form.action = form.dataset.deleteUrl.replace(":id", button.dataset.id);
        const nameEl = document.getElementById("hard_delete_section_name");
        if (nameEl) {
            nameEl.textContent = button.dataset.name || "this section";
        }
        const pwdInput = document.getElementById("hard_delete_password");
        if (pwdInput) {
            pwdInput.value = "";
        }
    }
});

document.addEventListener("hidden.bs.modal", function (event) {
    if (event.target?.id === "addSectionModal") {
        document.getElementById("addSectionForm")?.reset();
        updateAddSectionState();
    }

    if (event.target?.id === "editSectionModal") {
        document.getElementById("editSectionForm")?.reset();
    }

    if (event.target?.id === "hardDeleteSectionModal") {
        document.getElementById("hardDeleteSectionForm")?.reset();
    }
});

document.addEventListener("submit", function (event) {
    const form = event.target;

    if (
        form?.id === "addSectionForm" ||
        form?.id === "editSectionForm" ||
        form?.id === "hardDeleteSectionForm"
    ) {
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

