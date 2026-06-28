function initializeEnrollmentForm() {

    // ─── Elements ───────────────────────────────────────────
    const addModal = document.getElementById('addEnrollmentModal');
    const searchInput = document.getElementById('enrollmentStudentSearch');
    const resultsBox = document.getElementById('enrollmentSearchResults');
    const hiddenInput = document.getElementById('enrollmentStudentId');
    const selectedBox = document.getElementById('enrollmentSelectedStudent');
    const selectedName = document.getElementById('enrollmentSelectedName');
    const clearBtn = document.getElementById('enrollmentClearStudent');
    const enrollForm = document.getElementById('enrollmentForm');

    let debounceTimer;

    function setSelectedStudent(studentId, studentNumber, studentName) {
        hiddenInput.value = studentId;
        searchInput.value = `${studentNumber} — ${studentName}`;
        selectedName.textContent = studentName;
        selectedBox.style.display = 'block';
    }

    function resetSelectedStudent() {
        hiddenInput.value = '';
        selectedBox.style.display = 'none';
    }

    if (addModal) {
        addModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;

            if (trigger?.dataset.studentId) {
                setSelectedStudent(
                    trigger.dataset.studentId,
                    trigger.dataset.studentNumber || '',
                    trigger.dataset.studentName || ''
                );
                searchInput.classList.remove('is-invalid');
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
            }
        });
    }

    // ─── Live Search ─────────────────────────────────────────
    if (!searchInput || !resultsBox || !hiddenInput || !selectedBox || !selectedName || !clearBtn || !enrollForm) {
        return;
    }

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();

        // reset selected student pag nagtype ulit
        resetSelectedStudent();

        if (q.length < 2) {
            resultsBox.style.display = 'none';
            resultsBox.innerHTML = '';
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {

            fetch(`/students/search?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(students => {
                    resultsBox.innerHTML = '';

                    if (students.length === 0) {
                        resultsBox.innerHTML = `
                            <div class="px-3 py-2 text-muted small">No students found.</div>
                        `;
                        resultsBox.style.display = 'block';
                        return;
                    }

                    students.forEach(student => {
                        const item = document.createElement('div');
                        item.className = 'enrollment-search-item px-3 py-2 small d-flex align-items-center justify-content-between gap-2';
                        item.style.cursor = student.is_enrolled ? 'not-allowed' : 'pointer';
                        item.textContent = `${student.student_number} — ${student.first_name} ${student.last_name}`;

                        if (student.is_enrolled) {
                            const badge = document.createElement('span');
                            badge.className = 'badge rounded-pill bg-secondary';
                            badge.textContent = 'Enrolled';
                            item.appendChild(badge);
                            item.classList.add('text-muted', 'pe-none');
                        }

                        item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
                        item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));

                        if (!student.is_enrolled) {
                            item.addEventListener('click', () => {
                                setSelectedStudent(student.id, student.student_number, `${student.first_name} ${student.last_name}`);
                                resultsBox.style.display = 'none';
                                resultsBox.innerHTML = '';
                            });
                        }

                        resultsBox.appendChild(item);
                    });

                    resultsBox.style.display = 'block';
                })
                .catch(() => {
                    resultsBox.innerHTML = `
                        <div class="px-3 py-2 text-danger small">Something went wrong. Try again.</div>
                    `;
                    resultsBox.style.display = 'block';
                });

        }, 300);
    });

    // ─── Clear Selected Student ──────────────────────────────
    clearBtn.addEventListener('click', () => {
        resetSelectedStudent();
        searchInput.value = '';
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
    });

    // ─── Close dropdown pag nag-click sa labas ───────────────
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });

    // ─── Reset modal pag nasara ──────────────────────────────
    const modal = document.getElementById('addEnrollmentModal');
    modal.addEventListener('hidden.bs.modal', () => {
        enrollForm.reset();
        resetSelectedStudent();
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
        searchInput.value = '';
    });

}

document.addEventListener('DOMContentLoaded', initializeEnrollmentForm);
document.addEventListener('ajax:content-refreshed', initializeEnrollmentForm);



document.addEventListener('show.bs.modal', function (event) {
    if (event.target?.id !== 'editEnrollmentModal') {
        return;
    }

    const btn = event.relatedTarget;

    document.getElementById('edit_level').value = btn.dataset.level;
    document.getElementById('edit_grade_level').value = btn.dataset.gradeLevel;
    document.getElementById('edit_section').value = btn.dataset.section;
    document.getElementById('edit_session_type').value = btn.dataset.sessionType;
    document.getElementById('edit_status').value = btn.dataset.status;

    document.getElementById('editEnrollmentForm').action =
        btn.dataset.updateUrl || '/enrollment/' + btn.dataset.id;

    document.getElementById('editStudentName').textContent =
        btn.dataset.studentName;

    document.getElementById('editStudentLrn').textContent =
        btn.dataset.studentLrn;
});

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (form?.id === 'enrollmentForm') {
        event.preventDefault();
        window.ajaxCrud.clearFormErrors(form);

        const studentInput = form.querySelector('#enrollmentStudentId');
        const searchField = form.querySelector('#enrollmentStudentSearch');

        if (!studentInput?.value) {
            searchField?.classList.add('is-invalid');
            searchField?.focus();
            return;
        }

        searchField?.classList.remove('is-invalid');
        window.ajaxCrud.submitAjaxForm(form);
    }

    if (form?.id === 'editEnrollmentForm') {
        event.preventDefault();
        window.ajaxCrud.submitAjaxForm(form);
    }

    if (form?.matches('[data-ajax-delete="enrollment"]')) {
        event.preventDefault();
        window.ajaxCrud.submitAjaxDelete(form);
    }
});

document.addEventListener('show.bs.modal', function (event) {
    if (event.target?.id !== 'viewEnrollmentModal') {
        return;
    }

        const btn = event.relatedTarget;

        document.getElementById('viewStudentName').textContent = btn.dataset.studentName || '-';
        document.getElementById('viewStudentLrn').textContent = btn.dataset.studentLrn || 'LRN';
        document.getElementById('viewEnrollmentStatus').textContent = btn.dataset.status || '-';
        document.getElementById('viewSchoolYear').textContent = btn.dataset.schoolYear || '-';
        document.getElementById('viewGradeLevel').textContent = btn.dataset.gradeLevel || '-';
        document.getElementById('viewSection').textContent = btn.dataset.section || '-';
        document.getElementById('viewLevel').textContent = btn.dataset.level || '-';
        document.getElementById('viewCreatedAt').textContent = btn.dataset.createdAt || '-';
        document.getElementById('viewDetailStatus').textContent = btn.dataset.status || '-';
    });
