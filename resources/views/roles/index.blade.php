@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('users.roles') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="grid">
            <div class="card card-grid min-w-full" data-datatable="false" data-datatable-page-size="5" data-datatable-state-save="true" id="roles-table" data-api-url="{{ route('users.roles.datatables') }}">
                <div class="card-header py-5 flex-wrap">
                    <h3 class="card-title">
                        List of Roles
                    </h3>
                    <div class="flex flex-wrap gap-2 lg:gap-5">
                        <div class="flex">
                            <label class="input input-sm"> <i class="ki-filled ki-magnifier"> </i>
                                <input placeholder="Search roles" id="search" type="text" value="">

                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2.5">
                            <select class="select select-sm w-28">
                                <option value="1">
                                    Active
                                </option>
                                <option value="2">
                                    Disabled
                                </option>
                                <option value="2">
                                    Pending
                                </option>
                            </select>
                            <select class="select select-sm w-28">
                                <option value="desc">
                                    Latest
                                </option>
                                <option value="asc">
                                    Oldest
                                </option>
                            </select>
                            <button class="btn btn-sm btn-outline btn-primary">
                                <i class="ki-filled ki-setting-4"> </i> <Filters></Filters>
                            </button>
                            <div class="h-[24px] border border-r-gray-200"> </div>
                            <a class="btn btn-sm btn-light" href="{{ route('users.roles.export') }}"> Export to Excel </a>
                            <a class="btn btn-sm btn-primary" href="{{ route('users.roles.create') }}"> Add Role </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="scrollable-x-auto">
                        <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm" data-datatable-table="true">
                            <thead>
                            <tr>
                                <th class="w-14">
                                    <input class="checkbox checkbox-sm" data-datatable-check="true" type="checkbox"/>
                                </th>
                                <th class="min-w-[250px]" data-datatable-column="name">
                                    <span class="sort"> <span class="sort-label"> Role </span>
                                        <span class="sort-icon"> </span> </span>
                                </th>
                                <th class="min-w-[50px] text-center" data-datatable-column="actions">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-footer justify-center md:justify-between flex-col md:flex-row gap-3 text-gray-600 text-2sm font-medium">
                        <div class="flex items-center gap-2">
                            Show
                            <select class="select select-sm w-16" data-datatable-size="true" name="perpage"> </select> per page
                        </div>
                        <div class="flex items-center gap-4">
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
                text: "You won't be able to revert this!" ,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token()  }}'
                        }
                    });

                    $.ajax(`roles/${data}`, {
                        type: 'DELETE'
                    }).then((response) => {
                        swal.fire('Deleted!', 'User has been deleted.','success').then(() => {
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
        const element = document.querySelector('#roles-table');
        const searchInput = document.getElementById('search');

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
                    title: 'Role',
                },
                actions: {
                    title: 'Status',
                    render: (item, data) => {
                        return `<div class="flex flex-nowrap justify-center">
                            <a class="btn btn-sm btn-icon btn-clear btn-info" href="roles/${data.id}/edit">
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
        searchInput.addEventListener('input', function () {
            const searchValue = this.value.trim();
            dataTable.search(searchValue, true);
        });
    </script>
@endpush

