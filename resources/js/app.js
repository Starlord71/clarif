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

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-delete-trigger]');

    if (trigger) {
        openDeleteModal(trigger.dataset.action, trigger.dataset.name);

        return;
    }

    if (event.target.closest('[data-modal-cancel]') || event.target === deleteModal) {
        closeDeleteModal();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});
