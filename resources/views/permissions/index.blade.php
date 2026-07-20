@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('users.permissions') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="grid">
            <div class="min-w-full card card-grid" data-datatable="false" data-datatable-page-size="5"
                data-datatable-state-save="true" id="permissions-table"
                data-api-url="{{ route('users.permissions.datatables') }}">
                <div class="flex-wrap py-5 card-header">
                    <h3 class="card-title">
                        List of Permissions
                    </h3>
                    <div class="flex flex-wrap gap-2 lg:gap-5">
                        <div class="flex">
                            <label class="input input-sm"> <i class="ki-filled ki-magnifier"> </i>
                                <input placeholder="Search permissions" id="search" type="text" value="">

                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2.5 lg:gap-5">
                            <div class="h-[24px] border border-r-gray-200"> </div>
                            <a class="btn btn-sm btn-light" href="{{ route('users.permissions.export') }}"> Export to Excel
                            </a>
                            <a class="btn btn-sm btn-primary" href="{{ route('users.permissions.create') }}"> Add Permission
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="scrollable-x-auto">
                        <table class="table text-sm font-medium text-gray-700 align-middle table-auto table-border"
                            data-datatable-table="true">
                            <thead>
                                <tr>
                                    <th class="w-14">
                                        <input class="checkbox checkbox-sm" data-datatable-check="true" type="checkbox" />
                                    </th>
                                    <th class="min-w-[250px]" data-datatable-column="name">
                                        <span class="sort"> <span class="sort-label"> Permission </span>
                                            <span class="sort-icon"> </span> </span>
                                    </th>
                                    <th class="min-w-[250px]" data-datatable-column="roles">
                                        <span class="sort"> <span class="sort-label"> Roles </span>
                                            <span class="sort-icon"> </span> </span>
                                    </th>
                                    <th class="min-w-[50px] text-center" data-datatable-column="actions">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div
                        class="flex-col gap-3 justify-center font-medium text-gray-600 card-footer md:justify-between md:flex-row text-2sm">
                        <div class="flex gap-2 items-center">
                            Show
                            <select class="w-16 select select-sm" data-datatable-size="true" name="perpage"> </select> per
                            page
                        </div>
                        <div class="flex gap-4 items-center">
                            <span data-datatable-info="true"> </span>
                            <div class="pagination" data-datatable-pagination="true">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        function deleteData(data) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    $.ajax(`permissions/${data}`, {
                        type: 'DELETE'
                    }).then((response) => {
                        swal.fire('Deleted!', 'User has been deleted.', 'success').then(() => {
                            window.location.reload();
                        });
                    }).catch((error) => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An error occurred while deleting the file.', 'error');
                    });
                }
            })
        }
    </script>
    <script type="module">
        const element = document.querySelector('#permissions-table');
        const searchInput = document.getElementById('search');
        const apiUrl = element.getAttribute('data-api-url');
        const colors = ['', 'badge-primary', 'badge-success', 'badge-info', 'badge-danger', 'badge-warning', 'badge-dark'];


        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 5,
            columns: {
                select: {
                    render: (item, data, context) => {
                        const checkbox = document.createElement('input');
                        checkbox.className = 'checkbox checkbox-sm';
                        checkbox.type = 'checkbox';
                        checkbox.value = data.id.toString();
                        checkbox.setAttribute('data-datatable-row-check', 'true');
                        return checkbox.outerHTML.trim();
                    },
                },
                name: {
                    title: 'Permission',
                },
                roles: {
                    title: 'Roles',
                    render: (item, data) => {
                        const _render = data.roles.map((role) => {
                            const randomColor = colors[Math.floor(Math.random() * colors.length)];
                            return `<span class="badge ${randomColor} badge-sm">${role.name}</span>`;
                        });

                        return _render.join(' ');
                    }
                },
                actions: {
                    title: 'Status',
                    render: (item, data) => {
                        return `<div class="flex flex-nowrap justify-center">
                            <a class="btn btn-sm btn-icon btn-clear btn-info" href="permissions/${data.id}/edit">
                                <i class="ki-outline ki-notepad-edit"></i>
                            </a>
                            <a onclick="deleteData(${data.id})" class="delete btn btn-sm btn-icon btn-clear btn-danger">
                                <i class="ki-outline ki-trash"></i>
                            </a>
                        </div>`;
                    },
                }
            },
        };

        let dataTable = new KTDataTable(element, dataTableOptions);
        // Custom search functionality
        searchInput.addEventListener('input', function() {
            const searchValue = this.value.trim();
            dataTable.search(searchValue, true);
            dataTable.goPage(1);
        });
    </script>
@endpush
