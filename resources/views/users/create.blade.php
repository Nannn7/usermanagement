@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render(request()->route()->getName()) }}
@endsection

@section('content')
    <div class="w-full grid gap-5 lg:gap-7.5 mx-auto">
        @if (isset($user->id))
            <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="{{ $user->id }}">
                @method('PUT')
            @else
                <form method="POST" action="{{ route('users.store') }}">
        @endif
        @csrf
        <div class="card pb-2.5">
            <div class="card-header" id="basic_settings">
                <h3 class="card-title">
                    {{ isset($user->id) ? 'Edit' : 'Add' }} User
                </h3>
                <div class="flex items-center gap-2">
                    <label class="switch switch-sm">
                        <span class="switch-label">
                            Public Profile
                        </span>
                        <input checked="" name="check" type="checkbox" value="1">
                    </label>
                </div>
            </div>
            <div class="card-body grid gap-5">

                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        Name
                    </label>
                    <div class="flex flex-wrap items-baseline w-full">
                        <input class="input  @error('name') border-danger @enderror" type="text" name="name"
                            value="{{ $user->name ?? '' }}">
                        @error('name')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        Email
                    </label>
                    <div class="flex flex-wrap items-baseline w-full">
                        <input class="w-full input @error('email') border-danger @enderror" type="email" name="email"
                            value="{{ $user->email ?? '' }}">
                        @error('email')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        NIK
                    </label>
                    <div class="flex flex-wrap items-baseline w-full">
                        <input class="w-full input @error('nik') border-danger @enderror" type="number" name="nik"
                            value="{{ $user->nik ?? '' }}">
                        @error('nik')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        Branch
                    </label>
                    <div class="flex flex-wrap items-baseline w-full">
                        <select class="input tomselect w-full @error('branch_id') border-danger @enderror" name="branch_id"
                            id="branch_id">
                            <option value="">Pilih Branch</option>
                            @if (isset($branches))
                                @foreach ($branches as $row)
                                    @if (isset($user))
                                        <option value="{{ $row->id }}"
                                            {{ isset($user->branch_id) && $user->branch_id == $row->id ? 'selected' : '' }}>
                                            {{ $row->name }}
                                        </option>
                                    @else
                                        <option value="{{ $row->id }}">
                                            {{ $row->name }}
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('branch_id')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        Directorate
                    </label>
                    <div class="flex flex-wrap items-baseline w-full">
                        <select class="input tomselect w-full @error('directorate_id') border-danger @enderror"
                            name="directorate_id" id="directorate_id">
                            <option value="">Pilih Direktorat</option>
                            @if (isset($directorates))
                                @foreach ($directorates as $row)
                                    @if (isset($user))
                                        <option value="{{ $row->id }}"
                                            {{ isset($user->directorate_id) && $user->directorate_id == $row->id ? 'selected' : '' }}>
                                            {{ $row->name }}
                                        </option>
                                    @else
                                        <option value="{{ $row->id }}">
                                            {{ $row->name }}
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('directorate_id')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        Position
                    </label>
                    <div class="flex flex-wrap items-baseline w-full">
                        <select class="input tomselect w-full @error('position_id') border-danger @enderror"
                            name="position_id" id="position_id">
                            <option value="">Pilih Position</option>
                            @if (isset($positions))
                                @foreach ($positions as $row)
                                    @if (isset($user))
                                        <option value="{{ $row->id }}"
                                            {{ isset($user->position_id) && $user->position_id == $row->id ? 'selected' : '' }}>
                                            {{ $row->name }}
                                        </option>
                                    @else
                                        <option value="{{ $row->id }}">
                                            {{ $row->name }}
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('position_id')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                @if (isset($user->id))
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            E-Sign
                        </label>
                        <div class="flex flex-wrap items-baseline w-full">
                            <input class="file-input" type="file" name="sign" value="">
                        </div>
                    </div>
                @endif
                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        Password
                    </label>

<<<<<<< HEAD
                                    <div class="flex flex-wrap items-baseline w-full">
                                        <div class="input @error('password') border-danger @enderror" data-toggle-password="true" data-toggle-password-permanent="true">
                                            <input placeholder="Password (Min : 8 Digit, Must Contains 1 Capital (A-a), Contains Number (0-9), Contains Symbol (!,@,*))" type="password" name="password"/>
                                            <div class="btn btn-icon" data-toggle-password-trigger="true">
                                                <i class="ki-outline ki-eye toggle-password-active:hidden"></i>
                                                <i class="ki-outline ki-eye-slash hidden toggle-password-active:block"></i>
                                            </div>
                                        </div>
                                        @error('password')
                                        <em class="alert text-danger text-sm">{{ $message }}</em>
                                        @enderror
                                    </div>
                                </div>
                                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                    <label class="form-label max-w-56">
                                        Password Confirmation
                                    </label>
                                    <div class="flex flex-wrap items-baseline w-full">
                                        <div class="input @error('password_confirmation') border-danger @enderror" data-toggle-password="true" data-toggle-password-permanent="true">
                                            <input placeholder="Password Confirmation" type="password" name="password_confirmation"/>
                                            <div class="btn btn-icon" data-toggle-password-trigger="true">
                                                <i class="ki-outline ki-eye toggle-password-active:hidden"></i>
                                                <i class="ki-outline ki-eye-slash hidden toggle-password-active:block"></i>
                                            </div>
                                        </div>
                                        @error('password_confirmation')
                                        <em class="alert text-danger text-sm">{{ $message }}</em>
                                        @enderror
                                    </div>
                                </div>
                                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                    <label class="form-label max-w-56">
                                        Role
                                    </label>
                                    <div class="flex flex-wrap items-baseline w-full">
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 py-5 lg:py-7.5 w-full">
                                            @foreach($roles as $role)
                                                <div class="rounded-xl border p-4 flex items-center justify-between gap-2.5">
                                                    <div class="flex items-center gap-3.5">
                                                        <div class="relative size-[45px] shrink-0">
                                                            <svg class="w-full h-full stroke-gray-300 fill-gray-100" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
                                                                </path>
                                                                <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
                                                                </path>
                                                            </svg>
                                                            <div class="absolute leading-none left-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4">
                                                                <i class="ki-filled ki-category text-lg text-gray-500">
                                                                </i>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-col gap-1">
                                                            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-gray-900">
                                                                {{ $role->name }}
                                                            </span>
                                                            <span class="text-2sm text-gray-700">

                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="switch switch-sm">
                                                        @if(isset($user))
                                                        <input {{ in_array($role->name,$user->roles->pluck("name")->toArray()) ? 'checked' : '' }} name="roles" type="radio" value="{{ $role->name }}">
                                                        @else
                                                            <input name="roles" type="radio" value="{{ $role->name }}">
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="btn btn-primary">
                                        Save
                                    </button>
                                </div>
=======
                    <div class="flex flex-wrap items-baseline w-full">
                        <div class="input @error('password') border-danger @enderror" data-toggle-password="true"
                            data-toggle-password-permanent="true">
                            <input
                                placeholder="Password (Min : 8 Digit, Must Contains 1 Capital, Contains Number, Contains Symbol (!,@,*))"
                                type="password" name="password" />
                            <div class="btn btn-icon" data-toggle-password-trigger="true">
                                <i class="ki-outline ki-eye toggle-password-active:hidden"></i>
                                <i class="ki-outline ki-eye-slash hidden toggle-password-active:block"></i>
>>>>>>> b221050f45210fa2b4011fc6874d69ea79756aa8
                            </div>
                        </div>
                        @error('password')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        Password Confirmation
                    </label>
                    <div class="flex flex-wrap items-baseline w-full">
                        <div class="input @error('password_confirmation') border-danger @enderror"
                            data-toggle-password="true" data-toggle-password-permanent="true">
                            <input placeholder="Password Confirmation" type="password" name="password_confirmation" />
                            <div class="btn btn-icon" data-toggle-password-trigger="true">
                                <i class="ki-outline ki-eye toggle-password-active:hidden"></i>
                                <i class="ki-outline ki-eye-slash hidden toggle-password-active:block"></i>
                            </div>
                        </div>
                        @error('password_confirmation')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                    <label class="form-label max-w-56">
                        Role
                    </label>
                    <div class="flex flex-wrap items-baseline w-full">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 py-5 lg:py-7.5 w-full">
                            @foreach ($roles as $role)
                                <div class="rounded-xl border p-4 flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-3.5">
                                        <div class="relative size-[45px] shrink-0">
                                            <svg class="w-full h-full stroke-gray-300 fill-gray-100" fill="none"
                                                height="48" viewBox="0 0 44 48" width="44"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506
       18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937
       39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z"
                                                    fill="">
                                                </path>
                                                <path
                                                    d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506
       18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937
       39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z"
                                                    stroke="">
                                                </path>
                                            </svg>
                                            <div
                                                class="absolute leading-none left-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4">
                                                <i class="ki-filled ki-category text-lg text-gray-500">
                                                </i>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span
                                                class="flex items-center gap-1.5 leading-none font-medium text-sm text-gray-900">
                                                {{ $role->name }}
                                            </span>
                                            <span class="text-2sm text-gray-700">

                                            </span>
                                        </div>
                                    </div>
                                    <div class="switch switch-sm">
                                        @if (isset($user))
                                            <input
                                                {{ in_array($role->name, $user->roles->pluck('name')->toArray()) ? 'checked' : '' }}
                                                name="roles" type="radio" value="{{ $role->name }}">
                                        @else
                                            <input name="roles" type="radio" value="{{ $role->name }}">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

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
