<x-layouts.teacher>
    <x-slot name="pageName">Import Data</x-slot>
    <x-slot name="subtitle">Upload CSV files to import student data and grades</x-slot>

@section('title', 'Import Data - Grading System')

@section('content')
<div class="gs-container">
    <div class="gs-panel">
        <div class="gs-panel-header">
            <h2 class="gs-panel-title">
                <i class="fa-solid fa-file-import"></i> Import Data
            </h2>
            <p class="gs-panel-subtitle">Upload CSV files to import student data and grades</p>
        </div>

        <div class="gs-panel-body">
            <!-- Upload Section -->
            <div class="gs-section">
                <h3 class="gs-section-title">Upload CSV File</h3>
                <div class="gs-upload-area" id="uploadArea">
                    <div class="gs-upload-content">
                        <i class="fa-solid fa-cloud-upload-alt fa-3x gs-icon-primary"></i>
                        <h4>Drag & Drop CSV File Here</h4>
                        <p>or click to browse files</p>
                        <input type="file" id="csvFile" accept=".csv" style="display: none;">
                        <button type="button" class="gs-button gs-button-primary" onclick="document.getElementById('csvFile').click()">
                            <i class="fa-solid fa-folder-open"></i> Choose File
                        </button>
                    </div>
                </div>
                <div class="gs-upload-info">
                    <p><i class="fa-solid fa-info-circle"></i> Only CSV files are accepted. Excel files are not supported.</p>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="gs-section" id="previewSection" style="display: none;">
                <h3 class="gs-section-title">Data Preview</h3>
                <div class="gs-table-container">
                    <table class="gs-table" id="previewTable">
                        <thead>
                            <tr id="previewHeader"></tr>
                        </thead>
                        <tbody id="previewBody"></tbody>
                    </table>
                </div>
                
                <!-- Column Mapping -->
                <div class="gs-section">
                    <h3 class="gs-section-title">Column Mapping</h3>
                    <div class="gs-grid gs-grid-2" id="columnMapping">
                        <!-- Mapping options will be populated here -->
                    </div>
                </div>

                <!-- Import Options -->
                <div class="gs-section">
                    <h3 class="gs-section-title">Import Options</h3>
                    <div class="gs-form-group">
                        <label class="gs-form-label">
                            <input type="checkbox" id="overwriteExisting" class="gs-form-checkbox">
                            Overwrite existing records
                        </label>
                        <p class="gs-form-help">If checked, existing student records will be updated with new data</p>
                    </div>
                    <div class="gs-form-group">
                        <label class="gs-form-label">
                            <input type="checkbox" id="createNewStudents" class="gs-form-checkbox" checked>
                            Create new student records
                        </label>
                        <p class="gs-form-help">If checked, new students will be created for records not found in the system</p>
                    </div>
                </div>

                <!-- Import Button -->
                <div class="gs-section-actions">
                    <button type="button" class="gs-button gs-button-success" id="importButton" onclick="performImport()">
                        <i class="fa-solid fa-check"></i> Import Data
                    </button>
                    <button type="button" class="gs-button gs-button-secondary" onclick="resetUpload()">
                        <i class="fa-solid fa-times"></i> Cancel
                    </button>
                </div>
            </div>

            <!-- Status Messages -->
            <div class="gs-section" id="statusSection" style="display: none;">
                <div class="gs-alert" id="statusAlert">
                    <!-- Status messages will be shown here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Progress Modal -->
<div class="modal fade" id="importProgressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="gs-progress">
                    <div class="gs-progress-bar" id="progressBar">
                        <div class="gs-progress-bar-fill" id="progressFill"></div>
                    </div>
                    <div class="gs-progress-text" id="progressText">0%</div>
                </div>
                <div class="gs-import-stats" id="importStats">
                    <p>Processing: <span id="processedCount">0</span> records</p>
                    <p>Success: <span id="successCount">0</span> records</p>
                    <p>Errors: <span id="errorCount">0</span> records</p>
                </div>
                <div class="gs-error-log" id="errorLog" style="display: none;">
                    <h6>Error Details:</h6>
                    <ul id="errorList"></ul>
                </div>
            </div>
        </div>
    </div>
</x-layouts.teacher>

@push('styles')
<style>
.gs-upload-area {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
    background-color: #f9f9f9;
}

.gs-upload-area.dragover {
    border-color: #007bff;
    background-color: #e3f2fd;
}

.gs-upload-area.has-file {
    border-color: #28a745;
    background-color: #d4edda;
}

.gs-upload-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.gs-upload-info {
    margin-top: 15px;
    padding: 10px;
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    color: #856404;
}

.gs-section {
    margin-bottom: 30px;
}

.gs-section-title {
    margin-bottom: 15px;
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
}

.gs-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.gs-form-group {
    margin-bottom: 15px;
}

.gs-form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    cursor: pointer;
}

.gs-form-checkbox {
    width: 18px;
    height: 18px;
}

.gs-form-help {
    margin-top: 5px;
    font-size: 0.875rem;
    color: #666;
}

.gs-section-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.gs-alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.gs-alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.gs-alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.gs-alert-warning {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

.gs-progress {
    margin-bottom: 15px;
}

.gs-progress-bar {
    width: 100%;
    height: 20px;
    background-color: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.gs-progress-bar-fill {
    height: 100%;
    background-color: #007bff;
    transition: width 0.3s ease;
    width: 0%;
}

.gs-progress-text {
    text-align: center;
    margin-top: 5px;
    font-weight: 500;
}

.gs-import-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}

.gs-import-stats p {
    margin: 0;
    text-align: center;
    padding: 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.gs-error-log {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    padding: 15px;
}

.gs-error-log h6 {
    margin-top: 0;
    color: #721c24;
}

.gs-error-log ul {
    margin-bottom: 0;
}

.gs-error-log li {
    color: #721c24;
    margin-bottom: 5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .gs-grid-2 {
        grid-template-columns: 1fr;
    }
    
    .gs-section-actions {
        flex-direction: column;
    }
    
    .gs-import-stats {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
let csvData = [];
let headers = [];
let mappedColumns = {};

// File upload handling
const uploadArea = document.getElementById('uploadArea');
const csvFile = document.getElementById('csvFile');

// Drag and drop events
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
});

// File input change
csvFile.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFile(e.target.files[0]);
    }
});

function handleFile(file) {
    // Check if file is CSV
    if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
        showStatus('error', 'Only CSV files are accepted. Please upload a valid CSV file.');
        return;
    }

    // Check file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        showStatus('error', 'File size exceeds 10MB limit. Please choose a smaller file.');
        return;
    }

    // Show file selected state
    uploadArea.classList.add('has-file');
    uploadArea.querySelector('h4').textContent = `File Selected: ${file.name}`;
    uploadArea.querySelector('p').textContent = `Size: ${(file.size / 1024 / 1024).toFixed(2)} MB`;

    // Read and parse CSV
    const reader = new FileReader();
    reader.onload = (e) => {
        parseCSV(e.target.result);
    };
    reader.readAsText(file);
}

function parseCSV(csvText) {
    try {
        const lines = csvText.trim().split('\n');
        headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''));
        
        csvData = [];
        for (let i = 1; i < lines.length; i++) {
            const values = lines[i].split(',').map(v => v.trim().replace(/"/g, ''));
            if (values.length === headers.length) {
                csvData.push(values);
            }
        }

        if (csvData.length === 0) {
            showStatus('error', 'No data rows found in the CSV file.');
            return;
        }

        showPreview();
        generateColumnMapping();
        
    } catch (error) {
        showStatus('error', 'Error parsing CSV file: ' + error.message);
    }
}

function showPreview() {
    const previewSection = document.getElementById('previewSection');
    const previewHeader = document.getElementById('previewHeader');
    const previewBody = document.getElementById('previewBody');

    // Clear existing content
    previewHeader.innerHTML = '';
    previewBody.innerHTML = '';

    // Create header row
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        previewHeader.appendChild(th);
    });

    // Create data rows (show first 5 rows)
    const previewRows = csvData.slice(0, 5);
    previewRows.forEach(row => {
        const tr = document.createElement('tr');
        row.forEach(cell => {
            const td = document.createElement('td');
            td.textContent = cell;
            tr.appendChild(td);
        });
        previewBody.appendChild(tr);
    });

    // Show total rows
    const rowCount = document.createElement('div');
    rowCount.className = 'gs-table-info';
    rowCount.innerHTML = `Showing 5 of ${csvData.length} rows`;
    previewSection.appendChild(rowCount);

    previewSection.style.display = 'block';
}

function generateColumnMapping() {
    const mappingContainer = document.getElementById('columnMapping');
    mappingContainer.innerHTML = '';

    const requiredColumns = [
        { key: 'lrn', label: 'LRN ( Learner Reference Number)' },
        { key: 'lastName', label: 'Last Name' },
        { key: 'firstName', label: 'First Name' },
        { key: 'middleName', label: 'Middle Name' },
        { key: 'gradeLevel', label: 'Grade Level' },
        { key: 'section', label: 'Section' },
        { key: 'subject', label: 'Subject' },
        { key: 'firstQuarter', label: 'First Quarter Grade' },
        { key: 'secondQuarter', label: 'Second Quarter Grade' },
        { key: 'thirdQuarter', label: 'Third Quarter Grade' },
        { key: 'fourthQuarter', label: 'Fourth Quarter Grade' },
        { key: 'finalGrade', label: 'Final Grade' }
    ];

    requiredColumns.forEach(required => {
        const mappingItem = document.createElement('div');
        mappingItem.className = 'gs-form-group';
        
        const label = document.createElement('label');
        label.className = 'gs-form-label';
        label.textContent = required.label;
        
        const select = document.createElement('select');
        select.className = 'gs-form-select';
        select.id = `map_${required.key}`;
        
        // Add empty option
        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = '-- Select Column --';
        select.appendChild(emptyOption);
        
        // Add CSV columns
        headers.forEach((header, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = header;
            select.appendChild(option);
        });
        
        select.addEventListener('change', (e) => {
            mappedColumns[required.key] = e.target.value ? parseInt(e.target.value) : null;
        });
        
        label.appendChild(select);
        mappingItem.appendChild(label);
        mappingContainer.appendChild(mappingItem);
    });
}

function performImport() {
    // Validate mapping
    const requiredColumns = ['lrn', 'lastName', 'firstName', 'gradeLevel', 'section', 'subject'];
    const missingMappings = [];
    
    requiredColumns.forEach(col => {
        if (!mappedColumns[col] && mappedColumns[col] !== 0) {
            missingMappings.push(col);
        }
    });

    if (missingMappings.length > 0) {
        showStatus('error', `Please map all required columns: ${missingMappings.join(', ')}`);
        return;
    }

    // Show progress modal
    const modal = new bootstrap.Modal(document.getElementById('importProgressModal'));
    modal.show();

    // Simulate import process
    let processed = 0;
    let success = 0;
    let errors = 0;
    const errorList = [];

    const processRow = () => {
        if (processed >= csvData.length) {
            // Import complete
            setTimeout(() => {
                modal.hide();
                showStatus('success', `Import completed successfully! ${success} records processed, ${errors} errors.`);
                resetUpload();
            }, 1000);
            return;
        }

        const row = csvData[processed];
        
        // Simulate processing delay
        setTimeout(() => {
            // Simulate random success/failure for demo
            const willFail = Math.random() < 0.1; // 10% chance of error
            
            if (willFail) {
                errors++;
                const errorMsg = `Row ${processed + 1}: Invalid data format`;
                errorList.push(errorMsg);
                updateErrorLog();
            } else {
                success++;
            }

            processed++;
            updateProgress(processed, csvData.length, success, errors);
            
            processRow();
        }, 100);
    };

    processRow();
}

function updateProgress(processed, total, success, errorCount) {
    const percentage = Math.round((processed / total) * 100);
    document.getElementById('progressFill').style.width = percentage + '%';
    document.getElementById('progressText').textContent = percentage + '%';
    document.getElementById('processedCount').textContent = processed;
    document.getElementById('successCount').textContent = success;
    document.getElementById('errorCount').textContent = errorCount;
}

function updateErrorLog() {
    const errorLog = document.getElementById('errorLog');
    const errorList = document.getElementById('errorList');
    
    if (errorList.children.length > 0) {
        errorLog.style.display = 'block';
    }
}

function showStatus(type, message) {
    const statusSection = document.getElementById('statusSection');
    const statusAlert = document.getElementById('statusAlert');
    
    statusAlert.className = `gs-alert gs-alert-${type}`;
    statusAlert.innerHTML = `<i class="fa-solid fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i> ${message}`;
    
    statusSection.style.display = 'block';
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            statusSection.style.display = 'none';
        }, 5000);
    }
}

function resetUpload() {
    // Reset all state
    csvData = [];
    headers = [];
    mappedColumns = {};
    
    // Reset UI
    uploadArea.classList.remove('has-file');
    uploadArea.querySelector('h4').textContent = 'Drag & Drop CSV File Here';
    uploadArea.querySelector('p').textContent = 'or click to browse files';
    document.getElementById('csvFile').value = '';
    document.getElementById('previewSection').style.display = 'none';
    document.getElementById('statusSection').style.display = 'none';
    document.getElementById('columnMapping').innerHTML = '';
    
    // Reset checkboxes
    document.getElementById('overwriteExisting').checked = false;
    document.getElementById('createNewStudents').checked = true;
}
</script>
@endpush>