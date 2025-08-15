@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('users') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="grid">
            <div class="min-w-full card card-grid" data-datatable="false" data-datatable-page-size="5"
                data-datatable-state-save="true" id="users-table" data-api-url="{{ route('users.datatables') }}">
                <div class="flex-wrap py-5 card-header">
                    <h3 class="card-title">
                        List of Users
                    </h3>
                    <div class="flex flex-wrap gap-2 lg:gap-5">
                        <div class="flex">
                            <label class="input input-sm"> <i class="ki-filled ki-magnifier"> </i>
                                <input placeholder="Search users" id="search" type="text" value="">

                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2.5 lg:gap-5">
                            <div class="h-[24px] border border-r-gray-200"> </div>
                            <a class="btn btn-sm btn-light" id="export-btn" href="{{ route('users.export') }}"> Export to
                                Excel </a>
                            <a class="btn btn-sm btn-primary" href="{{ route('users.create') }}"> Add User </a>
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
                                        <span class="sort"> <span class="sort-label"> Name </span>
                                            <span class="sort-icon"> </span> </span>
                                    </th>
                                    <th class="min-w-[185px]" data-datatable-column="email">
                                        <span class="sort"> <span class="sort-label"> Email </span>
                                            <span class="sort-icon"> </span> </span>
                                    </th>
                                    <th class="min-w-[185px]" data-datatable-column="nik">
                                        <span class="sort"> <span class="sort-label"> NIK </span>
                                            <span class="sort-icon"> </span> </span>
                                    </th>
                                    <th class="min-w-[185px]" data-datatable-column="branch">
                                        <span class="sort"> <span class="sort-label"> Branch </span>
                                            <span class="sort-icon"> </span> </span>
                                    </th>
                                    <th class="min-w-[185px]" data-datatable-column="role">
                                        <span class="sort"> <span class="sort-label"> Role </span>
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

                    $.ajax(`users/${data}`, {
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
        const element = document.querySelector('#users-table');
        const searchInput = document.getElementById('search');
        const exportBtn = document.getElementById('export-btn');

        // Update export URL with filters
        function updateExportUrl() {
            let url = new URL(exportBtn.href);

            if (searchInput.value) {
                url.searchParams.set('search', searchInput.value);
            } else {
                url.searchParams.delete('search');
            }

            exportBtn.href = url.toString();
        };

        const apiUrl = element.getAttribute('data-api-url');
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
                    title: 'Name',
                },
                email: {
                    title: 'Email',
                },
                nik: {
                    title: 'NIK',
                },
                branch: {
                    title: 'Branch',
                    render: (item, data) => {
                        return data.branch?.name || '-';
                    },
                },
                role: {
                    title: 'Role',
                    render: (item, data) => {
                        console.log(data);
                        return data.roles.map(role => role.name).join(', ');
                    },
                },
                actions: {
                    title: 'Status',
                    render: (item, data) => {
                        return `<div class="flex flex-nowrap justify-center">
                            <a class="btn btn-sm btn-icon btn-clear btn-info" href="users/${data.id}/edit">
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
            updateExportUrl();
            dataTable.goPage(1);
        });
    </script>
@endpush
