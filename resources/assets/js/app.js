document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#role_form');

    if (!form) {
        return;
    }

    const selectAll = form.querySelector('#select_all');
    const groups = form.querySelectorAll('.permission-group');

    const actionCheckboxes = (group) =>
        group.querySelectorAll('input[type="checkbox"]:not(.permission-group-select-all)');

    const groupSelectAllToggle = (group) =>
        group.querySelector('.permission-group-select-all');

    const groupSelectAlls = () =>
        form.querySelectorAll('.permission-group-select-all');

    const allPermissionCheckboxes = () =>
        form.querySelectorAll(
            '.permission-group input[type="checkbox"]:not(.permission-group-select-all)'
        );

    // Reflect whether every action checkbox in one group is checked onto
    // that group's own "Select All" switch.
    const syncGroupSelectAll = (group) => {
        const toggle = groupSelectAllToggle(group);

        if (!toggle) {
            return;
        }

        const boxes = [...actionCheckboxes(group)];
        toggle.checked = boxes.length > 0 && boxes.every((c) => c.checked);
    };

    // Reflect whether every group's "Select All" is on onto the master
    // "Administrator/Superuser Access" switch.
    const syncMasterSelectAll = () => {
        if (!selectAll) {
            return;
        }

        const toggles = [...groupSelectAlls()];
        selectAll.checked = toggles.length > 0 && toggles.every((c) => c.checked);
    };

    groups.forEach((group) => {
        const toggle = groupSelectAllToggle(group);

        // Row-level "Select All": check/uncheck every action checkbox in
        // this group only, then bubble the effect up to the master switch.
        if (toggle) {
            toggle.addEventListener('change', (e) => {
                actionCheckboxes(group).forEach((c) => {
                    c.checked = e.target.checked;
                });
                syncMasterSelectAll();
            });
        }

        // Ticking (or unticking) any single action checkbox by hand keeps
        // this row's "Select All" — and the master switch — honest.
        actionCheckboxes(group).forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                syncGroupSelectAll(group);
                syncMasterSelectAll();
            });
        });

        // On load (e.g. editing a role that already has permissions saved),
        // reflect whatever came from the server instead of starting blank.
        syncGroupSelectAll(group);
    });

    syncMasterSelectAll();

    // Master switch drives everything top-down.
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            const checked = e.target.checked;

            allPermissionCheckboxes().forEach((c) => {
                c.checked = checked;
            });

            groupSelectAlls().forEach((c) => {
                c.checked = checked;
            });
        });
    }
});
