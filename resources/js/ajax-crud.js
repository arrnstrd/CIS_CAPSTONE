const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

function getSubmitMethod(form) {
    return (form.method || 'POST').toUpperCase();
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

function showFormErrors(form, data) {
    const errorBox = getErrorBox(form);
    const messages = [];

    if (data?.errors) {
        Object.entries(data.errors).forEach(([name, fieldMessages]) => {
            const message = fieldMessages[0] || 'Invalid value.';
            const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
            messages.push(message);

            if (field) {
                addFieldError(field, message);
            }
        });
    }

    if (!messages.length && data?.message) {
        messages.push(data.message);
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
    const response = await fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    const html = await response.text();
    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
    const scope = options.scope || null;

    replaceMatchedRegions('.table-panel', nextDocument, scope);

    document.dispatchEvent(new CustomEvent('ajax:table-refreshed'));
    document.dispatchEvent(new CustomEvent('ajax:content-refreshed'));
}

const refreshPageContent = refreshTables;

async function submitAjaxForm(form, options = {}) {
    clearFormErrors(form);

    const submitButton = form.querySelector('[type="submit"]');
    const originalButtonText = submitButton?.innerHTML;

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = submitButton.dataset.loadingText || 'Saving...';
    }

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
            options.onError?.(data, response);
            return false;
        }

        const modalElement = form.closest('.modal');
        const modal = modalElement ? bootstrap.Modal.getInstance(modalElement) : null;

        modal?.hide();

        form.reset();

        await refreshTables({
            scope: options.scope || form.dataset.ajaxScope || modalElement?.dataset.ajaxScope || null,
        });

        options.onSuccess?.(data, response);

        return true;
    } catch (error) {
        
        showFormErrors(form, { message: 'Something went wrong' });
        options.onError?.(error);
        return false;
    } finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    }
}

async function submitAjaxDelete(form, options = {}) {
    if (!confirm('Are you sure?')) {
        return;
    }

    clearFormErrors(form);

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

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!form?.matches('[data-ajax-form]')) {
        return;
    }

    event.preventDefault();
    submitAjaxForm(form, {
        scope: form.dataset.ajaxScope || null,
    });
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!form?.matches('[data-ajax-delete="subject"]')) {
        return;
    }

    event.preventDefault();
    submitAjaxDelete(form, {
        scope: form.dataset.ajaxScope || '#subject-table-pane',
    });
});

document.addEventListener('show.bs.modal', (event) => {
    if (event.target?.id !== 'editSubjectModal') {
        return;
    }

    const button = event.relatedTarget;
    const form = document.getElementById('editSubjectForm');

    if (!button || !form) {
        return;
    }

    form.action = button.dataset.updateUrl || `/subjects/${button.dataset.id}`;
    document.getElementById('edit_subject_code').value = button.dataset.code || '';
    document.getElementById('edit_subject_name').value = button.dataset.name || '';
    document.getElementById('edit_subject_level').value = button.dataset.level || '';
});

window.ajaxCrud = {
    clearFormErrors,
    refreshTables,
    refreshPageContent,
    showFormErrors,
    submitAjaxDelete,
    submitAjaxForm,
};
