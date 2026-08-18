<x-layouts.teacher>
    <x-slot name="pageName">Grading System</x-slot>
    <x-slot name="subtitle">Overview of your sections' grading performance.</x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'grading'])

    <div class="gd-layout">
        @include('teacher-modules.partials.grading-dashboard-sidebar', ['gdActive' => 'import-data'])

        <div class="gd-content">
            <p class="gs-panel-title mb-1">Import Data</p>
            <p class="text-muted small mb-3">Upload a class record (CSV) and preview how its columns map to system fields.</p>

            <div id="dropZone" class="gs-panel text-center py-5" style="border: 2px dashed #c5c5c5; cursor: pointer;">
                <i class="fa-solid fa-file-csv" style="font-size: 2rem; color: #2438b9;"></i>
                <p class="mt-2 mb-1 fw-semibold">Drag & drop your CSV file here</p>
                <p class="text-muted small mb-0">or click to browse — CSV only for now</p>
                <input type="file" id="fileInput" accept=".csv" class="d-none">
            </div>

            <div id="fileError" class="text-danger small mt-2"></div>

            <div id="previewSection" class="d-none mt-4">
                <p class="gs-panel-title mb-2">Column Mapping Preview</p>
                <div class="table-panel">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Detected Column</th>
                                <th>Sample Value</th>
                                <th>Map to System Field</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="previewBody"></tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="btn gd-btn-primary" id="confirmImportBtn">Confirm Import</button>
                    <button type="button" class="btn btn-outline-secondary" id="cancelImportBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const SYSTEM_FIELDS = [
            { value: 'learner_name', label: 'Learner Name' },
            { value: 'lrn', label: 'LRN' },
            { value: 'written_work', label: 'Written Work' },
            { value: 'performance_task', label: 'Performance Task' },
            { value: 'quarterly_assessment', label: 'Quarterly Assessment' },
            { value: 'section', label: 'Section' },
            { value: '', label: '— Not mapped —' },
        ];

        function guessField(header) {
            const h = header.toLowerCase().trim();
            if (h.includes('lrn')) return 'lrn';
            if (h.includes('name') || h.includes('learner')) return 'learner_name';
            if (h.match(/^ww\d*/) || h.includes('written')) return 'written_work';
            if (h.match(/^pt\d*/) || h.includes('performance')) return 'performance_task';
            if (h.includes('qa') || h.includes('quarterly') || h.includes('exam')) return 'quarterly_assessment';
            if (h.includes('section')) return 'section';
            return '';
        }

        function parseCsv(text) {
            const lines = text.split(/\r\n|\n/).filter(l => l.trim() !== '');
            if (lines.length === 0) return { headers: [], firstRow: [] };
            const headers = lines[0].split(',').map(h => h.trim());
            const firstRow = lines.length > 1 ? lines[1].split(',').map(v => v.trim()) : [];
            return { headers, firstRow };
        }

        function renderPreview(headers, firstRow) {
            const tbody = document.getElementById('previewBody');
            tbody.innerHTML = '';

            headers.forEach((header, i) => {
                const guessed = guessField(header);
                const sample = firstRow[i] ?? '';

                const optionsHtml = SYSTEM_FIELDS.map(f =>
                    `<option value="${f.value}" ${f.value === guessed ? 'selected' : ''}>${f.label}</option>`
                ).join('');

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${header}</td>
                    <td class="text-muted">${sample}</td>
                    <td><select class="form-select form-select-sm">${optionsHtml}</select></td>
                    <td>${guessed ? '<span class="text-success"><i class="fa-solid fa-check"></i></span>' : '<span class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i> Not mapped</span>'}</td>
                `;
                tbody.appendChild(row);
            });

            document.getElementById('previewSection').classList.remove('d-none');
        }

        function handleFile(file) {
            const errorEl = document.getElementById('fileError');
            errorEl.textContent = '';

            if (!file.name.toLowerCase().endsWith('.csv')) {
                errorEl.textContent = 'Only CSV files are supported right now. Please export your Excel file as CSV first (File > Save As > CSV).';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const { headers, firstRow } = parseCsv(e.target.result);
                if (headers.length === 0) {
                    errorEl.textContent = 'This file appears to be empty.';
                    return;
                }
                renderPreview(headers, firstRow);
            };
            reader.onerror = () => {
                errorEl.textContent = 'Could not read this file. Please try again.';
            };
            reader.readAsText(file);
        }

        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        dropZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (e) => {
            if (e.target.files[0]) handleFile(e.target.files[0]);
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.background = '#f7f7f5';
        });
        dropZone.addEventListener('dragleave', () => {
            dropZone.style.background = '';
        });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.background = '';
            if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
        });

        document.getElementById('confirmImportBtn').addEventListener('click', () => {
            alert('Import is not yet available. This will be enabled once student-matching logic is finalized.');
        });

        document.getElementById('cancelImportBtn').addEventListener('click', () => {
            document.getElementById('previewSection').classList.add('d-none');
            document.getElementById('previewBody').innerHTML = '';
            fileInput.value = '';
        });
    </script>
</x-layouts.teacher>