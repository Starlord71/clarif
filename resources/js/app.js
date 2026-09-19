// Shared helpers ---------------------------------------------------------

const debounce = (callback, wait = 300) => {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => callback(...args), wait);
    };
};

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Replace a fragment root with the freshly rendered HTML, matching by name.
const replaceFragment = (name, html) => {
    const current = document.querySelector(`[data-fragment="${name}"]`);

    if (!current) {
        return;
    }

    const template = document.createElement('template');
    template.innerHTML = html.trim();

    const fresh = template.content.firstElementChild;

    if (fresh) {
        current.replaceWith(fresh);
    }
};

// Fetch a single fragment from an endpoint and swap it into the page.
const fetchFragment = async (name, url, signal) => {
    const response = await fetch(url, {
        headers: {
            'X-Clarif-Fragment': name,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'text/html',
        },
        credentials: 'same-origin',
        signal,
    });

    if (!response.ok) {
        throw new Error(`Fragment request failed with status ${response.status}`);
    }

    replaceFragment(name, await response.text());
};

// Lightweight toast, reusing the flash-message palette.
const toast = document.querySelector('#app-toast');

const showToast = (message, variant = 'success') => {
    if (!toast) {
        return;
    }

    const body = toast.querySelector('[data-toast-body]');

    body.textContent = message;
    body.className =
        variant === 'error'
            ? 'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg'
            : 'rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-lg';

    toast.classList.remove('hidden');
    toast.classList.add('block');

    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => {
        toast.classList.add('hidden');
        toast.classList.remove('block');
    }, 4000);
};

// Build a URL from a GET form, ignoring empty values.
const formToUrl = (form) => {
    const url = new URL(form.action, window.location.origin);

    for (const [key, value] of new FormData(form).entries()) {
        if (String(value).trim() !== '') {
            url.searchParams.set(key, value);
        }
    }

    return url;
};

// Run comparison: pick exactly two reports and diff them. The older id is the
// base run and the newer one is the head, so the diff always reads old -> new.
document.addEventListener('change', (event) => {
    const checkbox = event.target.closest('[data-compare-form] input[name="selected[]"]');

    if (!checkbox) {
        return;
    }

    const form = checkbox.closest('[data-compare-form]');
    const selected = form.querySelectorAll('input[name="selected[]"]:checked').length;
    const hint = form.querySelector('[data-compare-hint]');

    if (hint) {
        hint.textContent = selected === 2 ? form.dataset.selectionReady : form.dataset.selectionHint;
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-compare-form]');

    if (!form) {
        return;
    }

    event.preventDefault();

    const ids = Array.from(form.querySelectorAll('input[name="selected[]"]:checked'))
        .map((input) => Number(input.value))
        .sort((a, b) => a - b);

    const hint = form.querySelector('[data-compare-hint]');

    if (ids.length !== 2) {
        if (hint) {
            hint.textContent = form.dataset.selectionError;
            hint.classList.add('text-red-600');
            hint.classList.remove('text-slate-500');
        }

        return;
    }

    const url = new URL(form.dataset.compareUrl || form.action, window.location.origin);
    url.searchParams.set('base', String(ids[0]));
    url.searchParams.set('head', String(ids[1]));

    window.location.href = url.toString();
});

// File inputs: show the chosen filename next to the custom trigger.
document.querySelectorAll('[data-file-input]').forEach((input) => {
    const wrapper = input.closest('[data-file-wrapper]');
    const name = wrapper?.querySelector('[data-file-name]');

    if (!name) {
        return;
    }

    input.addEventListener('change', () => {
        name.textContent = input.files?.[0]?.name || name.dataset.emptyLabel;
    });
});

// Delete confirmation modal, so no browser-localized native dialog is used.
const deleteModal = document.querySelector('[data-modal="delete-report"]');

const openDeleteModal = (action, name) => {
    if (!deleteModal) {
        return;
    }

    deleteModal.querySelector('[data-modal-form]').action = action;

    const nameTarget = deleteModal.querySelector('[data-modal-name]');

    if (nameTarget) {
        nameTarget.textContent = name;
    }

    deleteModal.classList.remove('hidden');
    deleteModal.classList.add('flex');
};

const closeDeleteModal = () => {
    deleteModal?.classList.add('hidden');
    deleteModal?.classList.remove('flex');
};

// Finding detail modal, so the message stays truncated inside the table row.
const findingModal = document.querySelector('[data-modal="finding-detail"]');
let findingReturnFocus = null;

const openFindingModal = (template, trigger) => {
    if (!findingModal || !template) {
        return;
    }

    const body = findingModal.querySelector('[data-finding-detail-body]');

    if (body) {
        body.replaceChildren(template.content.cloneNode(true));
    }

    findingReturnFocus = trigger;

    findingModal.classList.remove('hidden');
    findingModal.classList.add('flex');

    findingModal.querySelector('[data-finding-modal-cancel]')?.focus();
};

const closeFindingModal = () => {
    if (!findingModal) {
        return;
    }

    findingModal.classList.add('hidden');
    findingModal.classList.remove('flex');

    findingModal.querySelector('[data-finding-detail-body]')?.replaceChildren();

    findingReturnFocus?.focus();
    findingReturnFocus = null;
};

// Findings filters: live, debounced, no page reload and no Enter required.
const filterForm = document.querySelector('[data-findings-filters]');
let findingsAbort;

const buildFindingsUrl = () => {
    const url = formToUrl(filterForm);
    const perPage = document
        .querySelector('[data-fragment="findings-results"] [name="per_page"]')
        ?.value;

    if (perPage) {
        url.searchParams.set('per_page', perPage);
    }

    return url;
};

const loadFindings = (url) => {
    findingsAbort?.abort();
    findingsAbort = new AbortController();

    history.replaceState({}, '', url.toString());

    fetchFragment('findings-results', url.toString(), findingsAbort.signal).catch((error) => {
        if (error.name !== 'AbortError') {
            console.error(error);
        }
    });
};

if (filterForm) {
    filterForm.querySelectorAll('input, select').forEach((field) => {
        const eventName = field.tagName === 'SELECT' ? 'change' : 'input';

        field.addEventListener(eventName, debounce(() => loadFindings(buildFindingsUrl()), 300));
    });

    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        loadFindings(buildFindingsUrl());
    });

    filterForm.querySelector('[data-filters-reset]')?.addEventListener('click', (event) => {
        event.preventDefault();

        filterForm.querySelectorAll('input').forEach((input) => {
            input.value = '';
        });

        filterForm.querySelectorAll('select').forEach((select) => {
            select.selectedIndex = 0;
        });

        loadFindings(new URL(filterForm.action, window.location.origin));
    });
}

// Click handling: delete triggers and inline pagination links.
document.addEventListener('click', (event) => {
    const findingTrigger = event.target.closest('[data-finding-trigger]');

    if (findingTrigger) {
        openFindingModal(findingTrigger.querySelector('[data-finding-detail]'), findingTrigger);

        return;
    }

    if (event.target.closest('[data-finding-modal-cancel]') || event.target === findingModal) {
        closeFindingModal();

        return;
    }

    const trigger = event.target.closest('[data-delete-trigger]');

    if (trigger) {
        openDeleteModal(trigger.dataset.action, trigger.dataset.name);

        return;
    }

    if (event.target.closest('[data-modal-cancel]') || event.target === deleteModal) {
        closeDeleteModal();

        return;
    }

    const reportRow = event.target.closest('[data-report-trigger]');

    if (reportRow) {
        if (event.target.closest('a, button, input, label, [data-row-ignore]')) {
            return;
        }

        if (window.getSelection()?.toString()) {
            return;
        }

        window.location.href = reportRow.dataset.href;

        return;
    }

    const pageLink = event.target.closest('nav[data-pagination] a[href]');

    if (pageLink) {
        event.preventDefault();

        const fragment = pageLink.closest('[data-fragment]')?.dataset.fragment;
        const url = new URL(pageLink.href, window.location.origin);

        if (!fragment) {
            window.location.href = url.toString();

            return;
        }

        history.replaceState({}, '', url.toString());

        fetchFragment(fragment, url.toString()).catch((error) => console.error(error));
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeDeleteModal();
        closeFindingModal();

        return;
    }

    const row = event.target.closest?.('[data-finding-trigger]');

    if (row && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();

        openFindingModal(row.querySelector('[data-finding-detail]'), row);

        return;
    }

    const reportRow = event.target.closest?.('[data-report-trigger]');

    if (reportRow && (event.key === 'Enter' || event.key === ' ')) {
        if (event.target.closest('a, button, input, label, [data-row-ignore]')) {
            return;
        }

        event.preventDefault();

        window.location.href = reportRow.dataset.href;
    }
});

// Form submits: AJAX fragments and the delete modal.
document.addEventListener('submit', async (event) => {
    const ajaxForm = event.target.closest('[data-ajax-form]');

    if (ajaxForm) {
        event.preventDefault();

        const fragment = ajaxForm.dataset.ajaxForm;
        const url = formToUrl(ajaxForm);

        history.replaceState({}, '', url.toString());

        fetchFragment(fragment, url.toString()).catch((error) => console.error(error));

        return;
    }

    const deleteForm = event.target.closest('[data-modal-form]');

    if (!deleteForm) {
        return;
    }

    event.preventDefault();

    try {
        const response = await fetch(deleteForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body: new FormData(deleteForm),
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Delete request failed with status ${response.status}`);
        }

        const data = await response.json();

        closeDeleteModal();
        showToast(data.message);

        await fetchFragment('reports-list', window.location.href);
    } catch (error) {
        console.error(error);
        showToast(deleteForm.dataset.errorMessage || 'The request failed.', 'error');
    }
});

// Polling: refresh the report detail once parsing reaches a terminal state.
const initStatusPolling = () => {
    const initial = document.querySelector('[data-fragment="report-status"]');

    if (!initial || initial.dataset.terminal === '1' || !initial.dataset.statusUrl) {
        return;
    }

    const statusUrl = initial.dataset.statusUrl;
    let refreshing = false;

    const timer = setInterval(async () => {
        const current = document.querySelector('[data-fragment="report-status"]');

        if (!current || current.dataset.terminal === '1') {
            clearInterval(timer);

            return;
        }

        if (refreshing) {
            return;
        }

        try {
            const response = await fetch(statusUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (!data.terminal) {
                return;
            }

            refreshing = true;

            await Promise.all([
                fetchFragment('report-status', window.location.href),
                fetchFragment('findings-results', window.location.href),
            ]);
        } catch (error) {
            console.error(error);
        } finally {
            refreshing = false;
        }
    }, 3000);
};

// Polling: refresh the listing while any report is still being parsed.
const initListPolling = () => {
    const initial = document.querySelector('[data-fragment="reports-list"]');

    if (!initial || initial.dataset.hasPending !== '1') {
        return;
    }

    const timer = setInterval(async () => {
        const current = document.querySelector('[data-fragment="reports-list"]');

        if (!current) {
            clearInterval(timer);

            return;
        }

        try {
            await fetchFragment('reports-list', window.location.href);
        } catch (error) {
            console.error(error);
        }

        const next = document.querySelector('[data-fragment="reports-list"]');

        if (!next || next.dataset.hasPending !== '1') {
            clearInterval(timer);
        }
    }, 4000);
};

initStatusPolling();
initListPolling();
