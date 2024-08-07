<?php

    use Diglactic\Breadcrumbs\Breadcrumbs;
    use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

    // Home
    Breadcrumbs::for('users', function (BreadcrumbTrail $trail) {
        $trail->push('Users', route('users.index'));
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
