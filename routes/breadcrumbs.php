<?php

    use Diglactic\Breadcrumbs\Breadcrumbs;
    use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

    // Home
    Breadcrumbs::for('users', function (BreadcrumbTrail $trail) {
        $trail->push('Users', route('users.index'));
    });

    Breadcrumbs::for('users.profile', function (BreadcrumbTrail $trail) {
        $trail->parent('users');
        $trail->push('Profile');
    });

    Breadcrumbs::for('users.create', function (BreadcrumbTrail $trail) {
        $trail->parent('users');
        $trail->push('Add User', route('users.create'));
    });

    Breadcrumbs::for('users.edit', function (BreadcrumbTrail $trail) {
        $trail->parent('users');
        $trail->push('Edit User');
    });

    Breadcrumbs::for('users.roles', function (BreadcrumbTrail $trail) {
        $trail->parent('users');
        $trail->push('Roles', route('users.roles.index'));
    });

    Breadcrumbs::for('users.roles.create', function (BreadcrumbTrail $trail) {
        $trail->parent('users.roles');
        $trail->push('Add Role', route('users.roles.create'));
    });

    Breadcrumbs::for('users.roles.edit', function (BreadcrumbTrail $trail) {
        $trail->parent('users.roles');
        $trail->push('Edit Role');
    });


    Breadcrumbs::for('users.permissions', function (BreadcrumbTrail $trail) {
        $trail->parent('users');
        $trail->push('Permissions', route('users.permissions.index'));
    });

    Breadcrumbs::for('users.permissions.create', function (BreadcrumbTrail $trail) {
        $trail->parent('users.permissions');
        $trail->push('Add Permission', route('users.permissions.create'));
    });

    Breadcrumbs::for('users.permissions.edit', function (BreadcrumbTrail $trail) {
        $trail->parent('users.permissions');
        $trail->push('Edit Permission');
    });

    Breadcrumbs::for('users.positions', function (BreadcrumbTrail $trail) {
        $trail->parent('users');
        $trail->push('Positions', route('users.positions.index'));
    });

    Breadcrumbs::for('users.positions.create', function (BreadcrumbTrail $trail) {
        $trail->parent('users.positions');
        $trail->push('Add Position', route('users.positions.create'));
    });

    Breadcrumbs::for('users.positions.edit', function (BreadcrumbTrail $trail) {
        $trail->parent('users.positions');
        $trail->push('Edit Position');
    });
