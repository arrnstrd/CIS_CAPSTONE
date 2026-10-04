/**
 * Academic Setup Excel-Style Table Filtering & Data-Grid Controller
 * 
 * Provides Microsoft Excel-grade column filtering, sorting, popover anchoring,
 * and multi-predicate state management across Academic Setup tabs.
 */

class AcademicExcelGrid {
    constructor(paneSelector) {
        this.paneSelector = paneSelector;
        this.pane = document.querySelector(paneSelector);
        if (!this.pane) return;

        this.table = this.pane.querySelector('table');
        if (!this.table) return;

        this.tbody = this.table.querySelector('tbody');
        if (!this.tbody) return;

        // Central tab-isolated state
        this.state = {
            sort: { column: null, direction: null }, // 'asc' | 'desc'
            filters: {}, // { [colKey]: { values: Array(string), search: '', capacityMode: 'all' } }
        };

        this.activePopover = null;
        this.sourceRows = []; // Memoized source row snapshots

        this.init();
    }

    init() {
        this.extractSourceRows();
        this.setupHeaderTriggers();
        this.bindDocumentEvents();
        this.recalculateFilters();
    }

    extractSourceRows() {
        if (!this.tbody) return;
        const trs = Array.from(this.tbody.querySelectorAll('tr:not(.excel-empty-row)'));
        this.sourceRows = trs.map((tr, index) => {
            const rowData = {
                id: tr.dataset.id || tr.dataset.assignmentId || `row_${index}`,
                element: tr,
                initialIndex: index,
                values: {},
                capacity: {
                    total: parseInt(tr.dataset.colCapacity, 10) || 0,
                    enrolled: parseInt(tr.dataset.colEnrolled, 10) || 0,
                    vacant: parseInt(tr.dataset.colVacant, 10) || 0,
                },
            };

            // Parse column values from dataset or cells
            const ths = Array.from(this.table.querySelectorAll('thead th[data-column]'));
            ths.forEach((th) => {
                const colKey = th.dataset.column;
                let val = tr.dataset[`col${this.capitalize(colKey)}`];
                if (val === undefined || val === null) {
                    const colIndex = Array.from(th.parentNode.children).indexOf(th);
                    const cell = tr.children[colIndex];
                    val = cell ? (cell.textContent || '').trim() : '';
                }
                rowData.values[colKey] = (val || '').toString().trim();
            });

            return rowData;
        });
    }

    capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    setupHeaderTriggers() {
        const ths = Array.from(this.table.querySelectorAll('thead th[data-column]'));
        ths.forEach((th) => {
            const colKey = th.dataset.column;
            const colTitle = th.dataset.columnTitle || th.textContent.trim();
            const colType = th.dataset.columnType || 'text';

            th.classList.add('excel-th');

            // If header content is not yet wrapped, wrap it
            let headerContent = th.querySelector('.excel-header-content');
            if (!headerContent) {
                const originalText = th.childNodes[0] ? th.childNodes[0].textContent.trim() : colTitle;
                th.innerHTML = '';

                headerContent = document.createElement('div');
                headerContent.className = 'excel-header-content';

                const titleSpan = document.createElement('span');
                titleSpan.className = 'excel-header-title';
                titleSpan.textContent = originalText || colTitle;
                headerContent.appendChild(titleSpan);

                const triggerBtn = document.createElement('button');
                triggerBtn.type = 'button';
                triggerBtn.className = 'excel-filter-trigger';
                triggerBtn.setAttribute('aria-label', `Filter & sort by ${colTitle}`);
                triggerBtn.setAttribute('title', `Filter & sort by ${colTitle}`);
                triggerBtn.dataset.column = colKey;
                triggerBtn.dataset.columnType = colType;
                triggerBtn.dataset.columnTitle = colTitle;
                triggerBtn.innerHTML = `
                    <i class="fa-solid fa-filter"></i>
                    <span class="excel-active-indicator d-none"></span>
                `;

                triggerBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    this.togglePopover(th, triggerBtn, colKey, colTitle, colType);
                });

                headerContent.appendChild(triggerBtn);
                th.appendChild(headerContent);
            }
        });
    }

    togglePopover(th, triggerBtn, colKey, colTitle, colType) {
        if (this.activePopover && this.activePopover.dataset.column === colKey) {
            this.closePopover();
            return;
        }

        this.closePopover();
        this.openPopover(th, triggerBtn, colKey, colTitle, colType);
    }

    openPopover(th, triggerBtn, colKey, colTitle, colType) {
        const popover = document.createElement('div');
        popover.className = 'excel-popover-panel shadow-lg';
        popover.dataset.column = colKey;

        // Distinct values with occurrence counts
        const distinctCounts = this.getDistinctValuesWithCounts(colKey);
        const distinctValues = Object.keys(distinctCounts).sort((a, b) => {
            // Natural numerical sort for Grades
            const numA = parseInt(a.replace(/\D/g, ''), 10);
            const numB = parseInt(b.replace(/\D/g, ''), 10);
            if (!isNaN(numA) && !isNaN(numB)) return numA - numB;
            return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
        });

        // Current filter state for this column
        const currentFilter = this.state.filters[colKey] || null;
        const isColumnFiltered = currentFilter !== null;
        const currentSelectedValues = currentFilter ? new Set(currentFilter.values) : new Set(distinctValues);
        const currentSearch = currentFilter?.search || '';
        const currentCapMode = currentFilter?.capacityMode || 'all';

        // Sort state
        const currentSort = this.state.sort.column === colKey ? this.state.sort.direction : null;

        // Specialized Capacity UI
        let specializedControlsHtml = '';
        if (colType === 'capacity') {
            specializedControlsHtml = `
                <div class="px-3 py-1 bg-light border-bottom">
                    <div class="fw-semibold text-dark mb-1" style="font-size: 0.73rem;">Vacancy Quick Filters:</div>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <button type="button" class="btn btn-xs ${currentCapMode === 'all' ? 'btn-dark' : 'btn-outline-secondary'} js-cap-quick" data-mode="all" style="font-size: 0.72rem; padding: 2px 6px;">All</button>
                        <button type="button" class="btn btn-xs ${currentCapMode === 'available' ? 'btn-dark' : 'btn-outline-secondary'} js-cap-quick" data-mode="available" style="font-size: 0.72rem; padding: 2px 6px;">Has Available Capacity</button>
                        <button type="button" class="btn btn-xs ${currentCapMode === 'full' ? 'btn-dark' : 'btn-outline-secondary'} js-cap-quick" data-mode="full" style="font-size: 0.72rem; padding: 2px 6px;">At Capacity / Full</button>
                    </div>
                </div>
            `;
        } else if (colType === 'status') {
            specializedControlsHtml = `
                <div class="px-3 py-1 bg-light border-bottom d-flex align-items-center justify-content-between">
                    <span class="text-muted" style="font-size: 0.73rem;">Status Direct Toggles:</span>
                    <div class="btn-group btn-group-xs">
                        <button type="button" class="btn btn-xs btn-outline-success js-status-toggle" data-val="Active" style="font-size: 0.7rem; padding: 1px 6px;">Active</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary js-status-toggle" data-val="Inactive" style="font-size: 0.7rem; padding: 1px 6px;">Inactive</button>
                    </div>
                </div>
            `;
        }

        popover.innerHTML = `
            <div class="px-3 pt-2 pb-1 d-flex justify-content-between align-items-center bg-light border-bottom">
                <span class="fw-semibold text-dark" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-filter me-1 text-primary"></i> ${this.escapeHtml(colTitle)}
                </span>
                <button type="button" class="btn-close btn-close-xs js-popover-close" aria-label="Close" style="font-size: 0.65rem;"></button>
            </div>

            <!-- Sort Section -->
            <div class="p-1">
                <button type="button" class="excel-popover-item js-sort-btn ${currentSort === 'asc' ? 'fw-bold text-primary bg-light' : ''}" data-dir="asc">
                    <i class="fa-solid ${colType === 'grade' || colType === 'capacity' ? 'fa-arrow-down-1-9' : 'fa-arrow-down-a-z'} me-2 text-muted"></i>
                    Sort Ascending (${colType === 'grade' || colType === 'capacity' ? '1 to 9' : 'A to Z'})
                </button>
                <button type="button" class="excel-popover-item js-sort-btn ${currentSort === 'desc' ? 'fw-bold text-primary bg-light' : ''}" data-dir="desc">
                    <i class="fa-solid ${colType === 'grade' || colType === 'capacity' ? 'fa-arrow-up-9-1' : 'fa-arrow-up-z-a'} me-2 text-muted"></i>
                    Sort Descending (${colType === 'grade' || colType === 'capacity' ? '9 to 1' : 'Z to A'})
                </button>
                <button type="button" class="excel-popover-item js-clear-sort ${!currentSort ? 'is-disabled' : ''}">
                    <i class="fa-solid fa-rotate-left me-2 text-muted"></i> Clear Sort
                </button>
            </div>

            <hr class="excel-popover-divider">

            <!-- Column Clear Action -->
            <div class="px-1">
                <button type="button" class="excel-popover-item js-clear-col-filter ${!isColumnFiltered ? 'is-disabled' : 'text-danger'}">
                    <i class="fa-solid fa-filter-circle-xmark me-2"></i> Clear Filter from "${this.escapeHtml(colTitle)}"
                </button>
            </div>

            <hr class="excel-popover-divider">

            ${specializedControlsHtml}

            <!-- Live Search -->
            <div class="px-2 pt-2 pb-1">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted py-1" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm border-start-0 ps-0 py-1 js-popover-search" 
                           placeholder="Search values..." value="${this.escapeHtml(currentSearch)}" style="font-size: 0.78rem;">
                </div>
            </div>

            <!-- Value Checklist -->
            <div class="px-2 pb-1">
                <div class="form-check py-1 border-bottom mb-1 d-flex align-items-center">
                    <input class="form-check-input me-2 js-select-all" type="checkbox" id="chk_select_all_${colKey}" style="width: 14px; height: 14px;">
                    <label class="form-check-label small fw-semibold user-select-none" for="chk_select_all_${colKey}" style="font-size: 0.75rem;">
                        (Select All)
                    </label>
                </div>
                <div class="excel-checklist-scroll js-checklist-container">
                    ${this.renderChecklistItems(distinctValues, distinctCounts, currentSelectedValues, currentSearch, colKey)}
                </div>
            </div>

            <hr class="excel-popover-divider">

            <!-- Popover Footer -->
            <div class="px-3 py-2 d-flex justify-content-end gap-2 bg-light border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 js-cancel-btn" style="font-size: 0.75rem;">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary px-3 js-apply-btn" style="font-size: 0.75rem;">Apply</button>
            </div>
        `;

        document.body.appendChild(popover);
        this.activePopover = popover;

        // Position popover beneath trigger button
        this.positionPopover(triggerBtn, popover);

        // Bind interactive events within popover
        this.bindPopoverEvents(popover, colKey, colTitle, colType, distinctValues, currentCapMode);
    }

    renderChecklistItems(distinctValues, distinctCounts, selectedSet, searchQuery, colKey) {
        const query = (searchQuery || '').toLowerCase().trim();
        let itemsHtml = '';
        let matchCount = 0;

        distinctValues.forEach((val, idx) => {
            const isMatch = !query || val.toLowerCase().includes(query);
            if (!isMatch) return;
            matchCount++;

            const isChecked = selectedSet.has(val);
            const count = distinctCounts[val] || 0;
            const inputId = `chk_${colKey}_${idx}`;

            itemsHtml += `
                <div class="excel-checklist-item" data-value="${this.escapeHtml(val)}">
                    <div class="form-check d-flex align-items-center gap-2 mb-0 text-truncate">
                        <input class="form-check-input js-item-chk" type="checkbox" value="${this.escapeHtml(val)}" 
                               id="${inputId}" ${isChecked ? 'checked' : ''} style="width: 13px; height: 13px; margin-top: 0;">
                        <label class="form-check-label small user-select-none text-truncate mb-0" for="${inputId}" 
                               title="${this.escapeHtml(val)}" style="font-size: 0.75rem; cursor: pointer;">
                            ${this.escapeHtml(val)}
                        </label>
                    </div>
                    <span class="excel-count-pill">${count}</span>
                </div>
            `;
        });

        if (matchCount === 0) {
            return `<div class="text-muted small text-center py-3" style="font-size: 0.72rem;">No matching values</div>`;
        }

        return itemsHtml;
    }

    positionPopover(triggerBtn, popover) {
        const rect = triggerBtn.getBoundingClientRect();
        const popoverWidth = 295;
        const popoverHeight = popover.offsetHeight || 380;
        const padding = 10;

        let top = rect.bottom + window.scrollY + 4;
        let left = rect.left + window.scrollX;

        // Prevent right overflow
        if (left + popoverWidth > window.innerWidth - padding) {
            left = rect.right + window.scrollX - popoverWidth;
        }
        if (left < padding) {
            left = padding;
        }

        // Prevent bottom overflow if near page bottom
        if (top + popoverHeight > window.innerHeight + window.scrollY - padding) {
            top = rect.top + window.scrollY - popoverHeight - 4;
            if (top < window.scrollY + padding) {
                top = rect.bottom + window.scrollY + 4;
            }
        }

        popover.style.top = `${top}px`;
        popover.style.left = `${left}px`;
    }

    bindPopoverEvents(popover, colKey, colTitle, colType, distinctValues, currentCapMode) {
        const selectAllCb = popover.querySelector('.js-select-all');
        const searchInput = popover.querySelector('.js-popover-search');
        const checklistContainer = popover.querySelector('.js-checklist-container');
        let activeCapMode = currentCapMode;

        const updateSelectAllState = () => {
            const chks = Array.from(checklistContainer.querySelectorAll('.js-item-chk'));
            const checkedChks = chks.filter((chk) => chk.checked);
            if (selectAllCb) {
                selectAllCb.checked = chks.length > 0 && checkedChks.length === chks.length;
                selectAllCb.indeterminate = checkedChks.length > 0 && checkedChks.length < chks.length;
            }
        };

        updateSelectAllState();

        // Select All listener
        selectAllCb?.addEventListener('change', () => {
            const chks = Array.from(checklistContainer.querySelectorAll('.js-item-chk'));
            chks.forEach((chk) => {
                chk.checked = selectAllCb.checked;
            });
        });

        checklistContainer?.addEventListener('change', (e) => {
            if (e.target.matches('.js-item-chk')) {
                updateSelectAllState();
            }
        });

        // Live search filtering checklist items
        searchInput?.addEventListener('input', () => {
            const q = searchInput.value.toLowerCase().trim();
            const items = checklistContainer.querySelectorAll('.excel-checklist-item');
            let visibleCount = 0;

            items.forEach((item) => {
                const val = (item.dataset.value || '').toLowerCase();
                const match = !q || val.includes(q);
                item.style.display = match ? 'flex' : 'none';
                if (match) visibleCount++;
            });

            updateSelectAllState();
        });

        // Sorting buttons
        popover.querySelectorAll('.js-sort-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const dir = btn.dataset.dir;
                this.state.sort = { column: colKey, direction: dir };
                this.recalculateFilters();
                this.closePopover();
            });
        });

        // Clear sort
        popover.querySelector('.js-clear-sort')?.addEventListener('click', () => {
            if (this.state.sort.column === colKey) {
                this.state.sort = { column: null, direction: null };
                this.recalculateFilters();
                this.closePopover();
            }
        });

        // Clear column filter
        popover.querySelector('.js-clear-col-filter')?.addEventListener('click', () => {
            delete this.state.filters[colKey];
            this.recalculateFilters();
            this.closePopover();
        });

        // Capacity Quick Filter Toggles
        popover.querySelectorAll('.js-cap-quick').forEach((btn) => {
            btn.addEventListener('click', () => {
                popover.querySelectorAll('.js-cap-quick').forEach((b) => {
                    b.className = 'btn btn-xs btn-outline-secondary js-cap-quick';
                });
                btn.className = 'btn btn-xs btn-dark js-cap-quick';
                activeCapMode = btn.dataset.mode;
            });
        });

        // Status Quick Toggles
        popover.querySelectorAll('.js-status-toggle').forEach((btn) => {
            btn.addEventListener('click', () => {
                const targetVal = btn.dataset.val;
                const chks = checklistContainer.querySelectorAll('.js-item-chk');
                chks.forEach((chk) => {
                    chk.checked = chk.value.toLowerCase() === targetVal.toLowerCase();
                });
                updateSelectAllState();
            });
        });

        // Close / Cancel
        popover.querySelector('.js-popover-close')?.addEventListener('click', () => this.closePopover());
        popover.querySelector('.js-cancel-btn')?.addEventListener('click', () => this.closePopover());

        // Apply Button
        popover.querySelector('.js-apply-btn')?.addEventListener('click', () => {
            const checkedChks = Array.from(checklistContainer.querySelectorAll('.js-item-chk:checked'));
            const selectedValues = checkedChks.map((chk) => chk.value);
            const query = searchInput ? searchInput.value.trim() : '';

            // If all distinct values are checked, query is empty, and capMode is 'all', clear filter for this column
            const isAllSelected = selectedValues.length === distinctValues.length && !query && activeCapMode === 'all';

            if (isAllSelected) {
                delete this.state.filters[colKey];
            } else {
                this.state.filters[colKey] = {
                    type: colType,
                    values: selectedValues,
                    search: query,
                    capacityMode: activeCapMode,
                };
            }

            this.recalculateFilters();
            this.closePopover();
        });
    }

    closePopover() {
        if (this.activePopover) {
            this.activePopover.remove();
            this.activePopover = null;
        }
    }

    bindDocumentEvents() {
        // Close popover when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.activePopover) return;
            if (this.activePopover.contains(e.target)) return;
            if (e.target.closest('.excel-filter-trigger')) return;
            this.closePopover();
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activePopover) {
                this.closePopover();
            }
        });
    }

    getDistinctValuesWithCounts(colKey) {
        const counts = {};

        // In Advisor column, ensure 'Not Assigned' is present if any class has no advisor
        this.sourceRows.forEach((row) => {
            let val = row.values[colKey];
            if (val === undefined || val === null || val === '') {
                val = colKey === 'advisor' ? 'Not Assigned' : '—';
            }
            counts[val] = (counts[val] || 0) + 1;
        });

        return counts;
    }

    recalculateFilters() {
        if (!this.tbody) return;

        const activeFilterKeys = Object.keys(this.state.filters);
        let visibleRows = [];

        // Apply compound AND filter logic
        this.sourceRows.forEach((row) => {
            let matchesAll = true;

            for (const colKey of activeFilterKeys) {
                const rule = this.state.filters[colKey];
                const cellVal = (row.values[colKey] || '').toString();

                // Capacity vacancy rule
                if (rule.capacityMode && rule.capacityMode !== 'all') {
                    const vacant = row.capacity.vacant;
                    if (rule.capacityMode === 'available' && vacant <= 0) {
                        matchesAll = false;
                        break;
                    }
                    if (rule.capacityMode === 'full' && vacant > 0) {
                        matchesAll = false;
                        break;
                    }
                }

                // Substring search rule (if text search is entered)
                if (rule.search) {
                    const q = rule.search.toLowerCase();
                    if (!cellVal.toLowerCase().includes(q)) {
                        matchesAll = false;
                        break;
                    }
                }

                // Multi-select value checklist rule
                if (rule.values && rule.values.length >= 0) {
                    let normalizedVal = cellVal;
                    if (colKey === 'advisor' && (!cellVal || cellVal === '—')) {
                        normalizedVal = 'Not Assigned';
                    }
                    if (!rule.values.includes(normalizedVal)) {
                        matchesAll = false;
                        break;
                    }
                }
            }

            if (matchesAll) {
                visibleRows.push(row);
            }
        });

        // Apply sorting
        const sortCol = this.state.sort.column;
        const sortDir = this.state.sort.direction;

        if (sortCol && sortDir) {
            visibleRows.sort((a, b) => {
                let valA = a.values[sortCol] || '';
                let valB = b.values[sortCol] || '';

                // Handle numbers for grades & capacity
                const numA = parseInt(valA.replace(/[^\d.-]/g, ''), 10);
                const numB = parseInt(valB.replace(/[^\d.-]/g, ''), 10);

                let comp = 0;
                if (!isNaN(numA) && !isNaN(numB) && (valA.match(/^\d+$/) || valA.startsWith('Grade') || valA.includes('/'))) {
                    comp = numA - numB;
                } else {
                    comp = valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
                }

                return sortDir === 'asc' ? comp : -comp;
            });
        }

        // Render visible rows and update DOM
        const visibleElements = new Set(visibleRows.map((r) => r.element));
        this.sourceRows.forEach((row) => {
            if (visibleElements.has(row.element)) {
                row.element.classList.remove('d-none');
            } else {
                row.element.classList.add('d-none');
                // Uncheck hidden rows if needed
                const cb = row.element.querySelector('.row-checkbox, .academic-check-input');
                if (cb && cb.checked) {
                    cb.checked = false;
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });

        // Reorder DOM elements if sorted
        if (sortCol && sortDir) {
            visibleRows.forEach((row) => {
                this.tbody.appendChild(row.element);
            });
        }

        // Handle Empty Filter State
        let emptyRow = this.tbody.querySelector('.excel-empty-row');
        if (visibleRows.length === 0) {
            const colSpan = this.table.querySelectorAll('thead th').length || 7;
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.className = 'excel-empty-row';
                this.tbody.appendChild(emptyRow);
            }
            emptyRow.innerHTML = `
                <td colspan="${colSpan}" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-filter-circle-xmark fa-2x mb-2 opacity-50 d-block"></i>
                    <div class="fw-semibold mb-1">No records matching current filters.</div>
                    <div class="small mb-3">Try clearing column filters to view all records.</div>
                    <button type="button" class="btn btn-sm btn-outline-primary px-3 js-clear-all-filters">
                        <i class="fa-solid fa-rotate-left me-1"></i> Clear All Filters
                    </button>
                </td>
            `;
            emptyRow.querySelector('.js-clear-all-filters')?.addEventListener('click', () => {
                this.resetAllFilters();
            });
            emptyRow.classList.remove('d-none');
        } else if (emptyRow) {
            emptyRow.classList.add('d-none');
        }

        // Update Header Trigger Indicators
        this.updateHeaderIndicators();

        // Dispatch notification event
        this.pane.dispatchEvent(new CustomEvent('excel-grid:filtered', {
            bubbles: true,
            detail: {
                totalCount: this.sourceRows.length,
                visibleCount: visibleRows.length,
                filters: this.state.filters,
                sort: this.state.sort,
            },
        }));
    }

    resetAllFilters() {
        this.state.filters = {};
        this.state.sort = { column: null, direction: null };
        this.recalculateFilters();
    }

    updateHeaderIndicators() {
        const ths = Array.from(this.table.querySelectorAll('thead th[data-column]'));
        ths.forEach((th) => {
            const colKey = th.dataset.column;
            const triggerBtn = th.querySelector('.excel-filter-trigger');
            if (!triggerBtn) return;

            const isFiltered = !!this.state.filters[colKey];
            const isSorted = this.state.sort.column === colKey;
            const sortDir = isSorted ? this.state.sort.direction : null;

            triggerBtn.classList.toggle('is-active', isFiltered);
            triggerBtn.classList.toggle('is-sorted', isSorted);

            const activeDot = triggerBtn.querySelector('.excel-active-indicator');
            if (activeDot) {
                activeDot.classList.toggle('d-none', !isFiltered);
            }

            // Update icon for sorting
            const icon = triggerBtn.querySelector('i');
            if (icon) {
                if (isSorted) {
                    icon.className = sortDir === 'asc' ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down';
                } else {
                    icon.className = 'fa-solid fa-filter';
                }
            }
        });
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Global Registry for Tab Isolation
window.AcademicGrids = window.AcademicGrids || {};

function initAcademicGrids() {
    const panes = [
        '#subject-table-pane',
        '#section-table-pane',
        '#assignment-table-pane',
    ];

    panes.forEach((selector) => {
        const paneEl = document.querySelector(selector);
        if (!paneEl) return;

        // Preserve previous filters if already initialized
        const prevGrid = window.AcademicGrids[selector];
        const prevFilters = prevGrid ? prevGrid.state.filters : null;
        const prevSort = prevGrid ? prevGrid.state.sort : null;

        const grid = new AcademicExcelGrid(selector);
        if (prevFilters) grid.state.filters = prevFilters;
        if (prevSort) grid.state.sort = prevSort;

        grid.recalculateFilters();
        window.AcademicGrids[selector] = grid;
    });
}

document.addEventListener('DOMContentLoaded', initAcademicGrids);
document.addEventListener('ajax:table-refreshed', initAcademicGrids);
document.addEventListener('ajax:content-refreshed', initAcademicGrids);

export { AcademicExcelGrid, initAcademicGrids };
