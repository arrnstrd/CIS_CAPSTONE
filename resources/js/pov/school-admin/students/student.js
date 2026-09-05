function setStudentStep(modal, step) {
    if (!modal) return;
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

function filterSectionsByGrade(container) {
    if (!container) return;
    const gradeSelect = container.querySelector("select[name='grade_level']");
    const sectionSelect = container.querySelector("select[name='section_id']");

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
        return;
    }

    const confirmBtn = event.target.closest("#confirmAddStudentBtn");
    if (confirmBtn) {
        const form = document.getElementById("addStudentForm");
        const confirmModalEl = document.getElementById("confirmAddStudentModal");
        const confirmModal = confirmModalEl ? bootstrap.Modal.getInstance(confirmModalEl) : null;
        const sidePanelEl = document.getElementById("addStudentSidePanel");
        const sidePanel = sidePanelEl ? bootstrap.Offcanvas.getInstance(sidePanelEl) : null;

        if (!form || !window.ajaxCrud) return;

        confirmBtn.disabled = true;
        const originalContent = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';

        window.ajaxCrud.submitAjaxForm(form, {
            onSuccess: () => {
                confirmModal?.hide();
                sidePanel?.hide();
                form.reset();
            },
            onError: () => {
                confirmModal?.hide();
            }
        }).finally(() => {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalContent;
        });
    }
});

document.addEventListener("change", (event) => {
    if (
        event.target.matches("select[name='grade_level']") &&
        event.target.closest("form[data-ajax-form='student']")
    ) {
        const container = event.target.closest(".modal") || event.target.closest(".offcanvas");
        if (container) {
            filterSectionsByGrade(container);
        }
    }
});

document.addEventListener("show.bs.offcanvas", function (event) {
    const sidePanel = event.target;
    const btn = event.relatedTarget;

    if (sidePanel.id === "addStudentSidePanel") {
        const grade = btn?.dataset.grade || "";
        const defaultSection = btn?.dataset.defaultSection || "";

        if (grade) {
            const gradeSelect = sidePanel.querySelector("#add_grade_level");
            if (gradeSelect) {
                gradeSelect.value = grade;
            }
        }
        filterSectionsByGrade(sidePanel);

        if (defaultSection) {
            const sectionSelect = sidePanel.querySelector("#add_section_id");
            if (sectionSelect && !sectionSelect.options[sectionSelect.selectedIndex]?.hidden) {
                sectionSelect.value = defaultSection;
            }
        }
    }
});

document.addEventListener("hidden.bs.offcanvas", function (event) {
    const sidePanel = event.target;

    if (sidePanel.id === "addStudentSidePanel") {
        const form = sidePanel.querySelector("form[data-ajax-form='student']");
        if (form) {
            form.reset();
            if (window.ajaxCrud) {
                window.ajaxCrud.clearFormErrors(form);
            }
        }
    }
});

document.addEventListener("show.bs.modal", function (event) {
    const modal = event.target;
    const btn = event.relatedTarget;

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
        setValue(modal, "#edit_age", btn.dataset.age);
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

    if (modal.id === "editStudentModal") {
        const form = modal.querySelector("form[data-ajax-form='student']");
        form?.reset();
        setStudentStep(modal, 1);
    }
});

// Intercept Add Student form submission in capture phase to prompt confirmation modal first
document.addEventListener("submit", function (event) {
    const form = event.target;

    if (form?.id === "addStudentForm") {
        event.preventDefault();
        event.stopImmediatePropagation();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const firstName = form.querySelector("#add_first_name")?.value.trim() || "";
        const lastName = form.querySelector("#add_last_name")?.value.trim() || "";
        const fullName = `${firstName} ${lastName}`.trim();
        const confirmDetails = document.getElementById("confirmAddStudentDetails");
        if (confirmDetails) {
            confirmDetails.textContent = fullName
                ? `Are you sure you want to add ${fullName} as a new student?`
                : "Are you sure you want to add this student?";
        }

        const confirmModalEl = document.getElementById("confirmAddStudentModal");
        if (confirmModalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
            modal.show();
        }
        return;
    }

    if (form?.matches('[data-ajax-delete="student"]')) {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form);
    }
}, true);

