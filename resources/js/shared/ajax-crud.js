const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

function getSubmitMethod(form) {
    return (form.method || 'POST').toUpperCase();
}

function getSubmitButton(form) {
    let button = form.querySelector('[type="submit"]');
    if (!button && form.id) {
        button = document.querySelector(`button[type="submit"][form="${CSS.escape(form.id)}"]`);
    }
    return button;
}

function getErrorBox(form) {
    let errorBox = form.querySelector('[data-ajax-errors]');

    if (!errorBox) {
        errorBox = document.createElement('div');
        errorBox.dataset.ajaxErrors = '';
        errorBox.className = 'alert alert-danger d-none';
        form.prepend(errorBox);
    }

    return errorBox;
}

function clearFormErrors(form) {
    const errorBox = getErrorBox(form);
    errorBox.classList.add('d-none');
    errorBox.innerHTML = '';

    form.querySelectorAll('.is-invalid').forEach((field) => {
        field.classList.remove('is-invalid');
    });

    form.querySelectorAll('[data-field-error]').forEach((fieldError) => {
        fieldError.remove();
    });
}

function addFieldError(field, message) {
    field.classList.add('is-invalid');

    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback d-block';
    feedback.dataset.fieldError = '';
    feedback.textContent = message;

    field.insertAdjacentElement('afterend', feedback);
}

function sanitizeUserErrorMessage(msg) {
    if (!msg || typeof msg !== 'string') {
        return 'An unexpected error occurred. Please try again.';
    }
    if (
        /SQLSTATE/i.test(msg) ||
        /QueryException/i.test(msg) ||
        /SQL:/i.test(msg) ||
        /Illuminate\\Database/i.test(msg) ||
        /foreign key constraint/i.test(msg) ||
        /unique constraint/i.test(msg) ||
        /Connection refused/i.test(msg) ||
        /\.php/i.test(msg) ||
        /vendor\//i.test(msg)
    ) {
        return 'An error occurred while processing your request. Please check your entries and try again.';
    }
    return msg;
}

function showFormErrors(form, data) {
    const errorBox = getErrorBox(form);
    const messages = [];

    if (data?.errors) {
        Object.entries(data.errors).forEach(([name, fieldMessages]) => {
            const rawMsg = fieldMessages[0] || 'Invalid value.';
            const message = sanitizeUserErrorMessage(rawMsg);
            const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
            messages.push(message);

            if (field) {
                addFieldError(field, message);
            }
        });
    }

    if (!messages.length && data?.message) {
        messages.push(sanitizeUserErrorMessage(data.message));
    }

    errorBox.innerHTML = messages.map((message) => `<div>${message}</div>`).join('');
    errorBox.classList.toggle('d-none', messages.length === 0);
}

function replaceMatchedRegions(selector, nextDocument, scope = null) {
    const currentRoot = scope ? document.querySelector(scope) : document;
    const nextRoot = scope ? nextDocument.querySelector(scope) : nextDocument;

    if (!currentRoot || !nextRoot) {
        return;
    }

    const currentRegions = currentRoot.querySelectorAll(selector);
    const nextRegions = nextRoot.querySelectorAll(selector);

    currentRegions.forEach((currentRegion, index) => {
        const nextRegion = nextRegions[index];

        if (nextRegion) {
            currentRegion.replaceWith(nextRegion.cloneNode(true));
        }
    });
}

async function refreshTables(options = {}) {
    try {
        const response = await fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        });

        if (!response.ok) {
            console.warn('[ajaxCrud] refreshTables response not ok:', response.status);
            return;
        }

        const html = await response.text();
        const nextDocument = new DOMParser().parseFromString(html, 'text/html');
        const scope = options.scope || null;

        if (scope) {
            const currentScope = document.querySelector(scope);
            const nextScope = nextDocument.querySelector(scope);

            if (currentScope && nextScope) {
                const currentPanels = currentScope.querySelectorAll('.table-panel');
                if (currentPanels.length > 0) {
                    replaceMatchedRegions('.table-panel', nextDocument, scope);
                } else {
                    currentScope.replaceWith(nextScope.cloneNode(true));
                }
            }
        } else {
            replaceMatchedRegions('.table-panel', nextDocument, scope);
        }

        document.dispatchEvent(new CustomEvent('ajax:table-refreshed'));
        document.dispatchEvent(new CustomEvent('ajax:content-refreshed'));
    } catch (err) {
        console.warn('[ajaxCrud] refreshTables error:', err);
    }
}

const refreshPageContent = refreshTables;

async function submitAjaxForm(form, options = {}) {
    if (form.dataset.submitting === 'true') {
        return false;
    }
    form.dataset.submitting = 'true';

    clearFormErrors(form);

    const submitButton = getSubmitButton(form);
    const originalButtonText = submitButton?.innerHTML;

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = submitButton.dataset.loadingText || 'Saving...';
    }

    let response;
    let data;

    // --- PHASE 1: HTTP / Network Transaction ---
    try {
        response = await fetch(form.action, {
            method: getSubmitMethod(form),
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });
        data = await response.json().catch(() => ({}));
    } catch (networkError) {
        console.error('[ajaxCrud] Network/Transport Error:', networkError);
        showFormErrors(form, { message: 'Connection error. Please check your network and try again.' });
        options.onError?.(networkError);
        resetSubmitButton(form, submitButton, originalButtonText);
        return false;
    }

    // --- PHASE 2: Server-Side Validation / Logic Failure ---
    if (!response.ok) {
        showFormErrors(form, data);
        options.onError?.(data, response);
        resetSubmitButton(form, submitButton, originalButtonText);
        return false;
    }

    // --- PHASE 3: Post-Success UI Updates (Isolated & Protected) ---
    try {
        // 1. Safely hide modal using getOrCreateInstance
        const modalElement = form.closest('.modal');
        if (modalElement && window.bootstrap?.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement) 
                || bootstrap.Modal.getOrCreateInstance(modalElement);
            modalInstance?.hide();
        }

        // 2. Safely hide offcanvas drawer
        const offcanvasElement = form.closest('.offcanvas');
        if (offcanvasElement && window.bootstrap?.Offcanvas) {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasElement) 
                || bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
            offcanvasInstance?.hide();
        }

        // 3. Reset form inputs
        form.reset();

        // 4. Fire caller's onSuccess callback
        options.onSuccess?.(data, response);

        // 5. Asynchronously refresh table panels if not explicitly skipped
        if (!options.skipRefresh) {
            await refreshTables({
                scope: options.scope || form.dataset.ajaxScope || modalElement?.dataset.ajaxScope || offcanvasElement?.dataset.ajaxScope || null,
            });
        }
    } catch (uiError) {
        // Log DOM/callback issues to console without displaying false server errors on the form
        console.warn('[ajaxCrud] Non-fatal error during post-submission UI update:', uiError);
    } finally {
        resetSubmitButton(form, submitButton, originalButtonText);
    }

    return true;
}

function resetSubmitButton(form, submitButton, originalButtonText) {
    delete form.dataset.submitting;
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
}

async function submitAjaxDelete(form, options = {}) {
    if (form.dataset.deleting === 'true') {
        return;
    }

    if (!confirm('Are you sure?')) {
        return;
    }

    form.dataset.deleting = 'true';
    clearFormErrors(form);

    try {
        const response = await fetch(form.action, {
            method: getSubmitMethod(form),
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            showFormErrors(form, data);
            return;
        }

        await refreshTables({
            scope: options.scope || form.dataset.ajaxScope || null,
        });
    } catch (err) {
        console.warn('[ajaxCrud] submitAjaxDelete error:', err);
    } finally {
        delete form.dataset.deleting;
    }
}

const tabFilterDebounceTimers = new Map();

function getTabScope(element) {
    return element.dataset.tabScope ||
        element.dataset.ajaxScope ||
        element.closest('[data-tab-scope]')?.dataset.tabScope ||
        element.closest('.tab-pane')?.id && `#${element.closest('.tab-pane').id}`;
}

function getControlsForScope(scope) {
    if (!scope) {
        return [];
    }

    return Array.from(document.querySelectorAll(`${scope} [data-tab-filter]`));
}

function updateUrlForScope(scope, options = {}) {
    const nextUrl = new URL(window.location.href);
    const pageParam = options.pageParam;

    getControlsForScope(scope).forEach((control) => {
        if (!control.name) {
            return;
        }

        const value = control.value.trim ? control.value.trim() : control.value;

        if (value) {
            nextUrl.searchParams.set(control.name, value);
        } else {
            nextUrl.searchParams.delete(control.name);
        }
    });

    if (pageParam) {
        if (options.pageValue) {
            nextUrl.searchParams.set(pageParam, options.pageValue);
        } else {
            nextUrl.searchParams.delete(pageParam);
        }
    }

    history.pushState({}, '', nextUrl);
}

async function updateScopedFilters(control) {
    const scope = getTabScope(control);

    if (!scope) {
        return;
    }

    updateUrlForScope(scope, {
        pageParam: control.dataset.pageParam,
    });

    await refreshTables({ scope });
}

document.addEventListener('input', (event) => {
    const control = event.target;

    if (!control?.matches('[data-tab-filter][type="search"]')) {
        return;
    }

    const scope = getTabScope(control);
    const timerKey = `${scope}:${control.name}`;

    clearTimeout(tabFilterDebounceTimers.get(timerKey));
    tabFilterDebounceTimers.set(timerKey, setTimeout(() => {
        updateScopedFilters(control);
    }, 350));
});

document.addEventListener('change', (event) => {
    const control = event.target;

    if (!control?.matches('select[data-tab-filter]')) {
        return;
    }

    updateScopedFilters(control);
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('.tab-pane .pagination a');

    if (!link) {
        return;
    }

    const scope = getTabScope(link);

    if (!scope) {
        return;
    }

    const scopeRoot = document.querySelector(scope);
    const pageParam = scopeRoot?.querySelector('[data-tab-filter][data-page-param]')?.dataset.pageParam;

    if (!pageParam) {
        return;
    }

    event.preventDefault();

    const targetUrl = new URL(link.href);
    updateUrlForScope(scope, {
        pageParam,
        pageValue: targetUrl.searchParams.get(pageParam) || '1',
    });

    refreshTables({ scope });
});

const dedicatedHandlerFormIds = new Set([
    'createSectionForm',
    'addStudentToSectionForm',
    'assignAdvisorForm',
    'assignTeacherForm',
    'editStudentForm',
    'addSectionForm',
    'editSectionForm',
    'hardDeleteSectionForm',
]);

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!form?.matches('[data-ajax-form]')) {
        return;
    }

    // Allow specialized POV handlers to manage their own submission workflows
    if (dedicatedHandlerFormIds.has(form.id) || form.dataset.customAjax === 'true') {
        return;
    }

    if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }

    event.preventDefault();
    submitAjaxForm(form, {
        scope: form.dataset.ajaxScope || null,
    });
});

window.ajaxCrud = {
    clearFormErrors,
    refreshTables,
    refreshPageContent,
    showFormErrors,
    submitAjaxDelete,
    submitAjaxForm,
};
