@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render(request()->route()->getName()) }}
@endsection

@section('content')
    <div class="w-full grid gap-5 lg:gap-7.5 mx-auto">
        <form action="{{ isset($role->id) ? route('users.roles.update', $role->id) : route('users.roles.store') }}"
            method="POST" id="role_form">
            @csrf
            @if (isset($role->id))
                <input type="hidden" name="id" value="{{ $role->id }}">
                @method('PUT')
            @endif
            <div class="card pb-2.5">
                <div class="card-header" id="basic_settings">
                    <h3 class="card-title">
                        {{ isset($role->id) ? 'Edit' : 'Add' }} Role
                    </h3>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('users.roles.index') }}" class="btn btn-xs btn-info">Back</a>
                    </div>
                </div>
                <div class="card-body grid gap-5">
                    @php
                        $selectedPermissionNames = isset($role)
                            ? $role->permissions->pluck('name')->flip()
                            : collect();
                    @endphp
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Name
                        </label>
                        <div class="flex flex-wrap items-baseline w-full">
                            <input class="input  @error('name') border-danger @enderror" type="text" name="name"
                                value="{{ $role->name ?? '' }}">
                            @error('name')
                                <em class="alert text-danger text-sm">{{ $message }}</em>
                            @enderror
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Position
                        </label>
                        <div class="flex flex-wrap items-baseline w-full">
                            <select class="select tomselect @error('position_id') border-danger @enderror"
                                name="position_id">
                                <option value="">Select Position</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}"
                                        {{ isset($role) && $role->position_id == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }} | Tingkat Jabatan: {{ $position->level }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <em class="alert text-danger text-sm">{{ $message }}</em>
                            @enderror
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Administrator/Superuser Access
                        </label>
                        <div class="flex flex-wrap items-baseline w-full">
                            <label class="switch">
                                <input name="check" id="select_all" type="checkbox" value="1" />
                                <span class="switch-label">
                                    Select All
                                </span>
                            </label>
                        </div>
                    </div>
                    @foreach ($permissiongroups as $group)
                        @php
                            // The old "corsec" group used to cover Letter, Meeting,
                            // Workplan, Report and Library all at once. Those 5 menus
                            // now have their own dedicated groups below, so every
                            // action here EXCEPT "authorize" is redundant with them.
                            // "corsec.authorize" is kept and relabeled: it's the one
// that actually drives the "Approval Requests" menu
// (see ApproverController::@can('corsec.authorize')).
$isLegacyCorsec = $group->name === 'corsec';
$groupPermissions = $group->getpermissionsByGroupId($group->id);
if ($isLegacyCorsec) {
    $groupPermissions = $groupPermissions->filter(
        fn($permission) => str_ends_with($permission->name, '.authorize'),
    );
}
$groupLabel = $isLegacyCorsec ? 'Approval Requests' : ucwords($group->name);
                        @endphp
                        @if ($groupPermissions->isNotEmpty())
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5 permission-group">
                                <label class="form-label max-w-56">
                                    {{ $groupLabel }}
                                </label>
                                <div class="flex flex-wrap items-baseline w-full gap-2.5">
                                    <label class="switch switch-sm">
                                        <input type="checkbox" class="permission-group-select-all" />
                                        <span class="switch-label text-primary">
                                            Select All
                                        </span>
                                    </label>
                                    @foreach ($groupPermissions as $permission)
                                        <label class="switch">
                                            @if (isset($role))
                                                <input type="checkbox" value="{{ $permission->id }}" name="permissions[]"
                                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : null }} />
                                            @else
                                                <input type="checkbox" value="{{ $permission->id }}"
                                                    name="permissions[]" />
                                            @endif
                                            @php
                                                $permission_name = explode('.', $permission->name);
                                            @endphp

                                            <span class="switch-label">
                                                {{ ucwords($permission_name[1]) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection