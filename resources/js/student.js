function setStudentStep(modal, step) {
    modal.querySelectorAll("[data-step-panel]").forEach((panel) => {
        panel.classList.toggle(
            "d-none",
            Number(panel.dataset.stepPanel) !== step,
        );
    });
    modal.querySelectorAll("[data-step-label]").forEach((label) => {
        label.classList.toggle(
            "active",
            Number(label.dataset.stepLabel) === step,
        );
    });
}

function filterSectionsByGrade(modal) {
    const gradeSelect = modal.querySelector("select[name='grade_level']");
    const sectionSelect = modal.querySelector("select[name='section_id']");

    if (!gradeSelect || !sectionSelect) {
        return;
    }

    const gradeValue = String(gradeSelect.value);
    let visible = 0;

    Array.from(sectionSelect.options).forEach((option) => {
        if (!option.value) {
            return;
        }
        const show = String(option.dataset.gradeLevel) === gradeValue;
        option.hidden = !show;
        if (show) {
            visible++;
        }
    });

    if (sectionSelect.value && !sectionSelect.selectedOptions[0]?.hidden) {
        // keep current selection
    } else {
        sectionSelect.value = "";
    }

    sectionSelect.disabled = visible === 0;
}

function setValue(modal, selector, value) {
    const field = modal.querySelector(selector);
    if (field) {
        field.value = value ?? "";
    }
}

document.addEventListener("click", (event) => {
    const nextBtn = event.target.closest("[data-next-step]");
    if (nextBtn) {
        setStudentStep(nextBtn.closest(".modal"), 2);
        return;
    }

    const prevBtn = event.target.closest("[data-prev-step]");
    if (prevBtn) {
        setStudentStep(prevBtn.closest(".modal"), 1);
    }
});

document.addEventListener("change", (event) => {
    if (
        event.target.matches("select[name='grade_level']") &&
        event.target.closest("form[data-ajax-form='student']")
    ) {
        filterSectionsByGrade(event.target.closest(".modal"));
    }
});

document.addEventListener("show.bs.modal", function (event) {
    const modal = event.target;
    const btn = event.relatedTarget;

    if (modal.id === "addStudentModal") {
        const grade = btn?.dataset.grade || "";
        if (grade) {
            const gradeSelect = modal.querySelector("#add_grade_level");
            if (gradeSelect) {
                gradeSelect.value = grade;
            }
        }
        filterSectionsByGrade(modal);
        setStudentStep(modal, 1);
    }

    if (modal.id === "editStudentModal") {
        if (!btn) {
            return;
        }

        setValue(modal, "#edit_lrn", btn.dataset.lrn);
        setValue(modal, "#edit_first_name", btn.dataset.first_name);
        setValue(modal, "#edit_middle_name", btn.dataset.middle_name);
        setValue(modal, "#edit_last_name", btn.dataset.last_name);
        setValue(modal, "#edit_suffix", btn.dataset.suffix);
        setValue(modal, "#edit_sex", btn.dataset.sex);
        setValue(modal, "#edit_address", btn.dataset.address);
        setValue(modal, "#edit_birthdate", btn.dataset.birthdate);
        setValue(modal, "#edit_status", btn.dataset.status);
        setValue(modal, "#edit_guardian_name", btn.dataset.guardian_name);
        setValue(
            modal,
            "#edit_guardian_relationship",
            btn.dataset.guardian_relationship,
        );
        setValue(modal, "#edit_guardian_contact", btn.dataset.guardian_contact);
        setValue(modal, "#edit_guardian_email", btn.dataset.guardian_email);
        setValue(modal, "#edit_school_year_id", btn.dataset.school_year_id);
        setValue(modal, "#edit_grade_level", btn.dataset.grade_level);
        setValue(modal, "#edit_section_id", btn.dataset.section_id);
        setValue(modal, "#edit_session_type", btn.dataset.session_type);
        setValue(
            modal,
            "#edit_enrollment_status",
            btn.dataset.enrollment_status,
        );

        const form = modal.querySelector("#editStudentForm");
        if (form) {
            form.action = "/students/" + btn.dataset.id;
        }

        filterSectionsByGrade(modal);
        setStudentStep(modal, 1);
    }
});

document.addEventListener("hidden.bs.modal", function (event) {
    const modal = event.target;

    if (modal.id === "addStudentModal" || modal.id === "editStudentModal") {
        const form = modal.querySelector("form[data-ajax-form='student']");
        form?.reset();
        setStudentStep(modal, 1);
    }
});

// Form submission is handled by ajax-crud.js general event listener
// Only handle delete operations here
document.addEventListener("submit", function (event) {
    const form = event.target;

    if (form?.matches('[data-ajax-delete="student"]')) {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form);
    }
});
