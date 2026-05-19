document.addEventListener('DOMContentLoaded', function () {

    // ─── Elements ───────────────────────────────────────────
    const searchInput   = document.getElementById('enrollmentStudentSearch');
    const resultsBox    = document.getElementById('enrollmentSearchResults');
    const hiddenInput   = document.getElementById('enrollmentStudentId');
    const selectedBox   = document.getElementById('enrollmentSelectedStudent');
    const selectedName  = document.getElementById('enrollmentSelectedName');
    const clearBtn      = document.getElementById('enrollmentClearStudent');
    const enrollForm    = document.getElementById('enrollmentForm');

    let debounceTimer;

    // ─── Live Search ─────────────────────────────────────────
    searchInput.addEventListener('input', function () {
        const q = this.value.trim();

        // reset selected student pag nagtype ulit
        hiddenInput.value = '';
        selectedBox.style.display = 'none';

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
                        item.className = 'enrollment-search-item px-3 py-2 small';
                        item.style.cursor = 'pointer';
                        item.textContent = `${student.student_number} — ${student.first_name} ${student.last_name}`;

                        item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
                        item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));

                        item.addEventListener('click', () => {
                            // set hidden input
                            hiddenInput.value = student.id;

                            // update search field text
                            searchInput.value = `${student.student_number} — ${student.first_name} ${student.last_name}`;

                            // show selected badge
                            selectedName.textContent = `${student.first_name} ${student.last_name}`;
                            selectedBox.style.display = 'block';

                            // close dropdown
                            resultsBox.style.display = 'none';
                            resultsBox.innerHTML = '';
                        });

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
        hiddenInput.value   = '';
        searchInput.value   = '';
        selectedBox.style.display = 'none';
        resultsBox.style.display  = 'none';
        resultsBox.innerHTML      = '';
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
        hiddenInput.value         = '';
        selectedBox.style.display = 'none';
        resultsBox.style.display  = 'none';
        resultsBox.innerHTML      = '';
        searchInput.value         = '';
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

        fetch('/enrollments', {
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