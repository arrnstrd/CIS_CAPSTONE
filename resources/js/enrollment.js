document.addEventListener('DOMContentLoaded', function () {

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

    // ─── Form Submit ─────────────────────────────────────────
    enrollForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // guard: student must be selected
        if (!hiddenInput.value) {
            searchInput.classList.add('is-invalid');
            searchInput.focus();
            return;
        }
        searchInput.classList.remove('is-invalid');

        const formData = new FormData(enrollForm);

        fetch('/enrollment', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (ok) {
                    // close modal
                    bootstrap.Modal.getInstance(modal).hide();
                    // reload table to show new enrollment
                    window.location.reload();
                } else {
                    alert(data.message ?? 'Something went wrong.');
                }
            })
            .catch(() => alert('Network error. Please try again.'));
    });

});



const editEnrollmentModal = document.getElementById('editEnrollmentModal');
if (editEnrollmentModal) {
editEnrollmentModal.addEventListener('show.bs.modal', function (event) {
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
}

const viewEnrollmentModal = document.getElementById('viewEnrollmentModal');
if (viewEnrollmentModal) {
    viewEnrollmentModal.addEventListener('show.bs.modal', function (event) {
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
}




// // Handle edit form submission via AJAX
// const editForm = document.getElementById('editEnrollmentForm');
// if (editForm) {
//     editForm.addEventListener('submit', async function(e) {
//         e.preventDefault();

//         // Collect form data
//         const formData = {
//             school_year: document.getElementById('edit_school_year').value,
//             level: document.getElementById('edit_level').value,
//             grade_level: document.getElementById('edit_grade_level').value,
//             section: document.getElementById('edit_section').value,
//             session_type: document.getElementById('edit_session_type').value,
//             status: document.getElementById('edit_status').value
//         };

//         // Get enrollment ID from the form action (set in modal event)
//         const actionUrl = editForm.action;  // e.g. /enrollment/6
//         const enrollmentId = actionUrl.split('/').pop();

//         try {
//             const response = await fetch(`/enrollment/${enrollmentId}`, {
//                 method: 'PUT',
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
//                     'Accept': 'application/json'
//                 },
//                 body: JSON.stringify(formData)
//             });

//             const result = await response.json();

//             if (response.ok) {
//                 // Close modal
//                 const modal = bootstrap.Modal.getInstance(document.getElementById('editEnrollmentModal'));
//                 modal.hide();
//                 // Reload page to reflect changes (or update table row dynamically)
//                 window.location.reload();
//             } else {
//                 alert(result.message || 'Update failed. Check console for details.');
//                 console.error(result);
//             }
//         } catch (error) {
//             // alert('Network error. Please try again.');
//             console.error('Actual error:',error);
//         }
//     });
// }