function initializeEnrollmentForm() {
    // ─── Elements ───────────────────────────────────────────
    const addModal = document.getElementById("addEnrollmentModal");
    const addGradeLevel = document.getElementById("add_grade_level");
    const addSectionSelect = document.getElementById("add_section_id");
    const searchInput = document.getElementById("enrollmentStudentSearch");
    const resultsBox = document.getElementById("enrollmentSearchResults");
    const hiddenInput = document.getElementById("enrollmentStudentId");
    const selectedBox = document.getElementById("enrollmentSelectedStudent");
    const selectedName = document.getElementById("enrollmentSelectedName");
    const clearBtn = document.getElementById("enrollmentClearStudent");
    const enrollForm = document.getElementById("enrollmentForm");
    const editGradeLevel = document.getElementById("edit_grade_level");
    const editSectionSelect = document.getElementById("edit_section_id");

    let debounceTimer;

    const sectionOptionCache = new Map();

    function cacheSectionOptions(select) {
        if (!select || sectionOptionCache.size) {
            return;
        }

        Array.from(select.options).forEach((option) => {
            if (!option.value) {
                return;
            }

            sectionOptionCache.set(option.value, {
                element: option,
                gradeLevel: option.dataset.gradeLevel || "",
                text: option.textContent,
            });
        });
    }

    function resetSectionSelect(select, placeholder = "Select section") {
        if (!select) {
            return;
        }

        select.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        select.value = "";
        select.disabled = true;
    }

    function populateSections(select, gradeLevel, selectedSectionId = "") {
        if (!select) {
            return;
        }

        cacheSectionOptions(select);

        const normalizedGradeLevel = String(gradeLevel || "");
        const matches = Array.from(sectionOptionCache.values()).filter(
            (section) =>
                String(section.gradeLevel || "") === normalizedGradeLevel,
        );

        select.innerHTML = '<option value="" disabled>Select section</option>';

        matches.forEach((section) => {
            const option = document.createElement("option");
            option.value = section.element.value;
            option.dataset.gradeLevel = section.gradeLevel;
            option.textContent = section.text;
            if (section.element.value === selectedSectionId) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        select.disabled = matches.length === 0;
        if (selectedSectionId) {
            select.value = selectedSectionId;
        }
    }

    function getSectionGradeLevel(sectionId) {
        const cached = sectionOptionCache.get(sectionId);
        return cached?.gradeLevel ? String(cached.gradeLevel) : "";
    }

    function getGradeLevelFromSelect(select, sectionId) {
        if (!select || !sectionId) {
            return "";
        }

        const option = Array.from(select.options).find(
            (item) => item.value === sectionId,
        );
        return option?.dataset.gradeLevel
            ? String(option.dataset.gradeLevel)
            : "";
    }

    function setSelectedStudent(studentId, studentNumber, studentName) {
        hiddenInput.value = studentId;
        searchInput.value = `${studentNumber} — ${studentName}`;
        selectedName.textContent = studentName;
        selectedBox.style.display = "block";
    }

    function resetSelectedStudent() {
        hiddenInput.value = "";
        selectedBox.style.display = "none";
    }

    if (addModal) {
        addModal.addEventListener("show.bs.modal", function (event) {
            const trigger = event.relatedTarget;

            if (addSectionSelect) {
                cacheSectionOptions(addSectionSelect);
                resetSectionSelect(addSectionSelect);
            }

            if (addGradeLevel) {
                addGradeLevel.value = "";
            }

            if (trigger?.dataset.studentId) {
                setSelectedStudent(
                    trigger.dataset.studentId,
                    trigger.dataset.studentNumber || "",
                    trigger.dataset.studentName || "",
                );
                searchInput.classList.remove("is-invalid");
                resultsBox.style.display = "none";
                resultsBox.innerHTML = "";
            }
        });
    }

    // ─── Live Search ─────────────────────────────────────────
    if (
        !searchInput ||
        !resultsBox ||
        !hiddenInput ||
        !selectedBox ||
        !selectedName ||
        !clearBtn ||
        !enrollForm
    ) {
        return;
    }

    searchInput.addEventListener("input", function () {
        const q = this.value.trim();

        // reset selected student pag nagtype ulit
        resetSelectedStudent();

        if (q.length < 2) {
            resultsBox.style.display = "none";
            resultsBox.innerHTML = "";
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetch(`/students/search?q=${encodeURIComponent(q)}`)
                .then((res) => res.json())
                .then((students) => {
                    resultsBox.innerHTML = "";

                    if (students.length === 0) {
                        resultsBox.innerHTML = `
                            <div class="px-3 py-2 text-muted small">No students found.</div>
                        `;
                        resultsBox.style.display = "block";
                        return;
                    }

                    students.forEach((student) => {
                        const item = document.createElement("div");
                        item.className =
                            "enrollment-search-item px-3 py-2 small d-flex align-items-center justify-content-between gap-2";
                        item.style.cursor = student.is_enrolled
                            ? "not-allowed"
                            : "pointer";
                        item.textContent = `${student.student_number} — ${student.first_name} ${student.last_name}`;

                        if (student.is_enrolled) {
                            const badge = document.createElement("span");
                            badge.className = "badge rounded-pill bg-secondary";
                            badge.textContent = "Enrolled";
                            item.appendChild(badge);
                            item.classList.add("text-muted", "pe-none");
                        }

                        item.addEventListener("mouseenter", () =>
                            item.classList.add("bg-light"),
                        );
                        item.addEventListener("mouseleave", () =>
                            item.classList.remove("bg-light"),
                        );

                        if (!student.is_enrolled) {
                            item.addEventListener("click", () => {
                                setSelectedStudent(
                                    student.id,
                                    student.student_number,
                                    `${student.first_name} ${student.last_name}`,
                                );
                                resultsBox.style.display = "none";
                                resultsBox.innerHTML = "";
                            });
                        }

                        resultsBox.appendChild(item);
                    });

                    resultsBox.style.display = "block";
                })
                .catch(() => {
                    resultsBox.innerHTML = `
                        <div class="px-3 py-2 text-danger small">Something went wrong. Try again.</div>
                    `;
                    resultsBox.style.display = "block";
                });
        }, 300);
    });

    // ─── Clear Selected Student ──────────────────────────────
    clearBtn.addEventListener("click", () => {
        resetSelectedStudent();
        searchInput.value = "";
        resultsBox.style.display = "none";
        resultsBox.innerHTML = "";
    });

    // ─── Close dropdown pag nag-click sa labas ───────────────
    document.addEventListener("click", function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = "none";
        }
    });

    // ─── Reset modal pag nasara ──────────────────────────────
    const modal = document.getElementById("addEnrollmentModal");
    modal.addEventListener("hidden.bs.modal", () => {
        enrollForm.reset();
        resetSelectedStudent();
        resetSectionSelect(addSectionSelect);
        if (addGradeLevel) {
            addGradeLevel.value = "";
        }
        resultsBox.style.display = "none";
        resultsBox.innerHTML = "";
        searchInput.value = "";
    });

    if (addGradeLevel && addSectionSelect) {
        addGradeLevel.addEventListener("change", () => {
            populateSections(addSectionSelect, addGradeLevel.value);
        });
    }

    if (editGradeLevel && editSectionSelect) {
        editGradeLevel.addEventListener("change", () => {
            populateSections(editSectionSelect, editGradeLevel.value);
            editSectionSelect.value = "";
        });
    }

    const editModal = document.getElementById("editEnrollmentModal");
    if (editModal && editGradeLevel && editSectionSelect) {
        editModal.addEventListener("show.bs.modal", function (event) {
            const btn = event.relatedTarget;
            const sectionId = btn?.dataset.sectionId || "";
            const sectionGradeLevel =
                getSectionGradeLevel(sectionId) ||
                getGradeLevelFromSelect(editSectionSelect, sectionId);

            editGradeLevel.value = sectionGradeLevel || "";
            populateSections(editSectionSelect, sectionGradeLevel, sectionId);

            document.getElementById("edit_session_type").value =
                btn.dataset.sessionType;
            document.getElementById("edit_status").value = btn.dataset.status;

            document.getElementById("editEnrollmentForm").action =
                btn.dataset.updateUrl || "/enrollment/" + btn.dataset.id;

            document.getElementById("editStudentName").textContent =
                btn.dataset.studentName;

            document.getElementById("editStudentLrn").textContent =
                btn.dataset.studentLrn;
        });
    }
}

document.addEventListener("DOMContentLoaded", initializeEnrollmentForm);
document.addEventListener("ajax:content-refreshed", initializeEnrollmentForm);

document.addEventListener("submit", function (event) {
    const form = event.target;

    if (form?.id === "enrollmentForm") {
        event.preventDefault();
        window.ajaxCrud.clearFormErrors(form);

        const studentInput = form.querySelector("#enrollmentStudentId");
        const searchField = form.querySelector("#enrollmentStudentSearch");

        if (!studentInput?.value) {
            searchField?.classList.add("is-invalid");
            searchField?.focus();
            return;
        }

        searchField?.classList.remove("is-invalid");
        window.ajaxCrud.submitAjaxForm(form);
    }

    if (form?.id === "editEnrollmentForm") {
        event.preventDefault();
        window.ajaxCrud.submitAjaxForm(form);
    }

    if (form?.matches('[data-ajax-delete="enrollment"]')) {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form);
    }
});

document.addEventListener("show.bs.modal", function (event) {
    if (event.target?.id !== "viewEnrollmentModal") {
        return;
    }

    const btn = event.relatedTarget;

    document.getElementById("viewStudentName").textContent =
        btn.dataset.studentName || "-";
    document.getElementById("viewStudentLrn").textContent =
        btn.dataset.studentLrn || "LRN";
    document.getElementById("viewEnrollmentStatus").textContent =
        btn.dataset.status || "-";
    document.getElementById("viewSchoolYear").textContent =
        btn.dataset.schoolYear || "-";
    document.getElementById("viewSectionName").textContent =
        btn.dataset.sectionName || "-";
    document.getElementById("viewGradeLevel").textContent =
        btn.dataset.sectionGradeLevel || "-";
    document.getElementById("viewLevel").textContent =
        btn.dataset.sectionLevel || "-";
    document.getElementById("viewAdviser").textContent =
        btn.dataset.sectionAdviser || "-";
    document.getElementById("viewCapacity").textContent =
        btn.dataset.sectionCapacity || "-";
    document.getElementById("viewCreatedAt").textContent =
        btn.dataset.createdAt || "-";
    document.getElementById("viewDetailStatus").textContent =
        btn.dataset.status || "-";
});
