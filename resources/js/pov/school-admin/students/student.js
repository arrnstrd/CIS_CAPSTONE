/**
 * Student & Section Management — Context-Aware Slide-Over Drawer Controller
 * Preserves the exact original selection flow:
 * Student Management -> Grade Level Selection -> Section Selection -> Student/Class List
 * Triggers ~30% right-side sliding panels (drawers) for actions without modal popups or inline form expansions.
 */

document.addEventListener("DOMContentLoaded", () => {
    // 1. Offcanvas Drawer Context Injection
    document.addEventListener("show.bs.offcanvas", (event) => {
        const drawer = event.target;
        const trigger = event.relatedTarget;

        if (!drawer) return;

        // Context values from trigger button or container
        const grade = trigger?.dataset.grade || drawer.dataset.grade || "";
        const sectionId = trigger?.dataset.sectionId || drawer.dataset.sectionId || "";
        const sectionName = trigger?.dataset.sectionName || drawer.dataset.sectionName || "";

        // A. Create Section Drawer
        if (drawer.id === "createSectionDrawer") {
            const badge = drawer.querySelector("#createSectionGradeBadge");
            const text = drawer.querySelector("#createSectionGradeText");
            const gradeLabel = drawer.querySelector("#create_section_grade_label");
            const hiddenGrade = drawer.querySelector("#create_section_grade_level");
            const hiddenLevel = drawer.querySelector("#create_section_level");

            if (grade) {
                if (badge) badge.textContent = `Grade ${grade}`;
                if (text) text.textContent = `${grade}`;
                if (gradeLabel) gradeLabel.textContent = `${grade}`;
                if (hiddenGrade) hiddenGrade.value = grade;
                if (hiddenLevel) {
                    const gNum = parseInt(grade, 10);
                    if (gNum >= 1 && gNum <= 6) hiddenLevel.value = "elementary";
                    else if (gNum >= 7 && gNum <= 10) hiddenLevel.value = "highschool";
                    else hiddenLevel.value = "senior_high_school";
                }
            }
        }

        // B. Add Student Drawer
        if (drawer.id === "addStudentDrawer") {
            const badge = drawer.querySelector("#addStudentContextBadge");
            const secText = drawer.querySelector("#addStudentSectionText");
            const grdText = drawer.querySelector("#addStudentGradeText");
            const hiddenGrade = drawer.querySelector("#add_student_grade_level");
            const hiddenSection = drawer.querySelector("#add_student_section_id");

            if (badge && grade && sectionName) badge.textContent = `Grade ${grade} · Section ${sectionName}`;
            if (secText && sectionName) secText.textContent = `Section ${sectionName}`;
            if (grdText && grade) grdText.textContent = `${grade}`;
            if (hiddenGrade && grade) hiddenGrade.value = grade;
            if (hiddenSection && sectionId) hiddenSection.value = sectionId;
        }

        // C. Assign Advisor Drawer
        if (drawer.id === "assignAdvisorDrawer") {
            const form = drawer.querySelector("#assignAdvisorForm");
            if (form && sectionId) {
                form.action = `/sections/${sectionId}/advisor`;
            }

            const badge = drawer.querySelector("#advisorContextBadge");
            const secText = drawer.querySelector("#advisorSectionText");
            if (badge && grade && sectionName) badge.textContent = `Grade ${grade} · Section ${sectionName}`;
            if (secText && sectionName) secText.textContent = `Section ${sectionName}`;
        }

        // D. Assign Teacher Drawer
        if (drawer.id === "assignTeacherDrawer") {
            const badge = drawer.querySelector("#teacherContextBadge");
            const secText = drawer.querySelector("#teacherSectionText");
            const hiddenSection = drawer.querySelector("#assign_teacher_section_id");

            if (badge && grade && sectionName) badge.textContent = `Grade ${grade} · Section ${sectionName}`;
            if (secText && sectionName) secText.textContent = `Section ${sectionName}`;
            if (hiddenSection && sectionId) hiddenSection.value = sectionId;
        }

        // E. Edit Student Drawer
        if (drawer.id === "editStudentDrawer" && trigger) {
            const form = drawer.querySelector("#editStudentForm");
            if (form && trigger.dataset.id) {
                form.action = `/students/${trigger.dataset.id}`;
            }

            setVal(drawer, "#edit_hub_lrn", trigger.dataset.lrn);
            setVal(drawer, "#edit_hub_first_name", trigger.dataset.first_name);
            setVal(drawer, "#edit_hub_middle_name", trigger.dataset.middle_name);
            setVal(drawer, "#edit_hub_last_name", trigger.dataset.last_name);
            setVal(drawer, "#edit_hub_suffix", trigger.dataset.suffix);
            setVal(drawer, "#edit_hub_sex", trigger.dataset.sex);
            setVal(drawer, "#edit_hub_address", trigger.dataset.address);
            setVal(drawer, "#edit_hub_age", trigger.dataset.age);
            setVal(drawer, "#edit_hub_status", trigger.dataset.status);
            setVal(drawer, "#edit_hub_guardian_name", trigger.dataset.guardian_name);
            setVal(drawer, "#edit_hub_guardian_relationship", trigger.dataset.guardian_relationship);
            setVal(drawer, "#edit_hub_guardian_contact", trigger.dataset.guardian_contact);
            setVal(drawer, "#edit_hub_guardian_email", trigger.dataset.guardian_email);
            setVal(drawer, "#edit_hub_school_year_id", trigger.dataset.school_year_id);
            setVal(drawer, "#edit_hub_grade_level", trigger.dataset.grade_level);
            setVal(drawer, "#edit_hub_section_id", trigger.dataset.section_id);
            setVal(drawer, "#edit_hub_enrollment_status", trigger.dataset.enrollment_status);
        }
    });

    // 2. Offcanvas Drawer Cleanup
    document.addEventListener("hidden.bs.offcanvas", (event) => {
        const form = event.target.querySelector("form");
        if (form && window.ajaxCrud) {
            window.ajaxCrud.clearFormErrors(form);
        }
    });

    function setVal(container, selector, value) {
        const el = container.querySelector(selector);
        if (el) el.value = value ?? "";
    }

    // 3. Form Submissions via AJAX with Drawer Auto-Hide
    document.addEventListener("submit", (event) => {
        const form = event.target;

        // Create Section
        if (form?.id === "createSectionForm") {
            event.preventDefault();
            event.stopImmediatePropagation();

            const drawerEl = document.getElementById("createSectionDrawer");
            const drawer = drawerEl && window.bootstrap ? bootstrap.Offcanvas.getOrCreateInstance(drawerEl) : null;
            if (window.ajaxCrud) {
                window.ajaxCrud.submitAjaxForm(form, {
                    skipRefresh: true,
                    onSuccess: () => {
                        drawer?.hide();
                        form.reset();
                        window.location.reload();
                    },
                });
            }
            return;
        }

        // Add Student (Direct submission, zero modals!)
        if (form?.id === "addStudentToSectionForm") {
            event.preventDefault();
            event.stopImmediatePropagation();

            const drawerEl = document.getElementById("addStudentDrawer");
            const drawer = drawerEl && window.bootstrap ? bootstrap.Offcanvas.getOrCreateInstance(drawerEl) : null;
            if (window.ajaxCrud) {
                window.ajaxCrud.submitAjaxForm(form, {
                    skipRefresh: true,
                    onSuccess: () => {
                        drawer?.hide();
                        form.reset();
                        window.location.reload();
                    },
                });
            }
            return;
        }

        // Assign / Unassign Advisor
        if (form?.id === "assignAdvisorForm" || form?.matches('[data-ajax-form="advisor"]')) {
            event.preventDefault();
            event.stopImmediatePropagation();

            const assignEl = document.getElementById("assignAdvisorDrawer");
            const viewEl = document.getElementById("viewAdvisorDrawer");
            const drawer = (assignEl && window.bootstrap ? bootstrap.Offcanvas.getInstance(assignEl) : null)
                || (viewEl && window.bootstrap ? bootstrap.Offcanvas.getInstance(viewEl) : null);

            if (window.ajaxCrud) {
                window.ajaxCrud.submitAjaxForm(form, {
                    skipRefresh: true,
                    onSuccess: () => {
                        drawer?.hide();
                        window.location.reload();
                    },
                });
            }
            return;
        }

        // Assign Teacher
        if (form?.id === "assignTeacherForm") {
            event.preventDefault();
            event.stopImmediatePropagation();

            const drawerEl = document.getElementById("assignTeacherDrawer");
            const drawer = drawerEl && window.bootstrap ? bootstrap.Offcanvas.getOrCreateInstance(drawerEl) : null;
            if (window.ajaxCrud) {
                window.ajaxCrud.submitAjaxForm(form, {
                    skipRefresh: true,
                    onSuccess: () => {
                        drawer?.hide();
                        form.reset();
                        window.location.reload();
                    },
                });
            }
            return;
        }

        // Edit Student
        if (form?.id === "editStudentForm") {
            event.preventDefault();
            event.stopImmediatePropagation();

            const drawerEl = document.getElementById("editStudentDrawer");
            const drawer = drawerEl && window.bootstrap ? bootstrap.Offcanvas.getOrCreateInstance(drawerEl) : null;
            if (window.ajaxCrud) {
                window.ajaxCrud.submitAjaxForm(form, {
                    skipRefresh: true,
                    onSuccess: () => {
                        drawer?.hide();
                        window.location.reload();
                    },
                });
            }
            return;
        }

        // AJAX Deletions
        if (form?.matches('[data-ajax-delete="student"]') || form?.matches('[data-ajax-delete="assignment"]')) {
            event.preventDefault();
            event.stopImmediatePropagation();
            if (window.ajaxCrud) {
                window.ajaxCrud.submitAjaxDelete(form).then(() => {
                    window.location.reload();
                });
            }
            return;
        }
    });
});
