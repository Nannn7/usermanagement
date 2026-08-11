@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('users.positions') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="grid">
            <div class="min-w-full card card-grid" data-datatable="false" data-datatable-page-size="5"
                data-datatable-state-save="true" id="positions-table" data-api-url="{{ route('users.positions.datatables') }}">
                <div class="flex-wrap py-5 card-header">
                    <h3 class="card-title">
                        List of Positions
                    </h3>
                    <div class="flex flex-wrap gap-2 lg:gap-5">
                        <div class="flex">
                            <label class="input input-sm"> <i class="ki-filled ki-magnifier"> </i>
                                <input placeholder="Search positions" id="search" type="text" value="">
                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2.5 lg:gap-5">
                            <div class="h-[100%] border border-r-gray-200"> </div>
                            <a class="btn btn-sm btn-light" id="export-btn" href="{{ route('users.positions.export') }}">
                                Export to Excel </a>
                            <a class="btn btn-sm btn-primary" href="{{ route('users.positions.create') }}"> Add Position
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
                                    <th class="min-w-[150px]" data-datatable-column="code">
                                        <span class="sort"> <span class="sort-label"> Code </span>
                                            <span class="sort-icon"> </span> </span>
                                    </th>
                                    <th class="min-w-[250px]" data-datatable-column="name">
                                        <span class="sort"> <span class="sort-label"> Name </span>
                                            <span class="sort-icon"> </span> </span>
                                    </th>
                                    <th class="min-w-[100px]" data-datatable-column="level">
                                        <span class="sort"> <span class="sort-label"> Tingkat Jabatan </span>
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
        const positionDestroyUrlTemplate = @json(route('users.positions.destroy', ['position' => '__POSITION_ID__']));

        function deleteData(data) {
            const deleteUrl = positionDestroyUrlTemplate.replace('__POSITION_ID__', data);

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
<<<<<<< HEAD
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

=======
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

>>>>>>> b221050f45210fa2b4011fc6874d69ea79756aa8
                    $.ajax(deleteUrl, {
                        type: 'DELETE',
                        dataType: 'json'
                    }).then((response) => {
                        swal.fire('Deleted!', 'Position has been deleted.', 'success').then(() => {
                            window.location.reload();
                        });
                    }).catch((error) => {
                        console.error('Error:', error);
                        Swal.fire('Error!', error.responseJSON?.message || 'An error occurred while deleting the position.', 'error');
                    });
                }
            })
        }
    </script>
    <script type="module">
        const element = document.querySelector('#positions-table');
        const searchInput = document.getElementById('search');
        const positionEditUrlTemplate = @json(route('users.positions.edit', ['position' => '__POSITION_ID__']));
<<<<<<< HEAD

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
                code: {
                    title: 'Code',
                },
                name: {
                    title: 'Name',
                },
                level: {
                    title: 'Level',
                },
=======

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
                code: {
                    title: 'Code',
                },
                name: {
                    title: 'Name',
                },
                level: {
                    title: 'Level',
                },
>>>>>>> b221050f45210fa2b4011fc6874d69ea79756aa8
                actions: {
                    title: 'Status',
                    render: (item, data) => {
                        const editUrl = positionEditUrlTemplate.replace('__POSITION_ID__', data.id);

                        return `<div class="flex flex-nowrap justify-center">
                            <a class="btn btn-sm btn-icon btn-clear btn-info" href="${editUrl}">
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
        const exportBtn = document.getElementById('export-btn');
        const baseExportUrl = exportBtn.getAttribute('href');

        // Custom search functionality
        searchInput.addEventListener('input', function() {
            const searchValue = this.value.trim();
            dataTable.search(searchValue, true);
            dataTable.goPage(1);

            // Update export URL with search parameter
            if (searchValue) {
                exportBtn.setAttribute('href', `${baseExportUrl}?search=${encodeURIComponent(searchValue)}`);
            } else {
                exportBtn.setAttribute('href', baseExportUrl);
            }
        });
    </script>
@endpush
