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

function replaceMatchedRegions(selector, nextDocument) {
    const currentRegions = document.querySelectorAll(selector);
    const nextRegions = nextDocument.querySelectorAll(selector);

    currentRegions.forEach((currentRegion, index) => {
        const nextRegion = nextRegions[index];

        if (nextRegion) {
            currentRegion.replaceWith(nextRegion.cloneNode(true));
        }
    });
}

async function refreshTables() {
    const response = await fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    const html = await response.text();
    const nextDocument = new DOMParser().parseFromString(html, 'text/html');

    replaceMatchedRegions('.table-panel', nextDocument);
    replaceMatchedRegions('.pagination', nextDocument);

    document.dispatchEvent(new CustomEvent('ajax:table-refreshed'));
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
        await refreshTables();
        options.onSuccess?.(data, response);

        return true;
    } catch (error) {
        showFormErrors(form, { message: 'Network error. Please try again.' });
        options.onError?.(error);
        return false;
    } finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    }
}

async function submitAjaxDelete(form) {
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

    await refreshTables();
}

window.ajaxCrud = {
    clearFormErrors,
    refreshTables,
    refreshPageContent,
    showFormErrors,
    submitAjaxDelete,
    submitAjaxForm,
};
