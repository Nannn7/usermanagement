const selectAll = document.querySelector('#select_all');

if (selectAll) {
    const allCheckboxes = document.querySelectorAll('[type="checkbox"]');

    // Handle check state
    selectAll.addEventListener('change', e => {
        // Apply check state to all checkboxes
        allCheckboxes.forEach(c => {
            c.checked = e.target.checked;
        });
    });
}
