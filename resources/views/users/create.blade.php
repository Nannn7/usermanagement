@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render(request()->route()->getName()) }}
@endsection

@section('content')
    <div class="w-full grid gap-5 lg:gap-7.5 mx-auto">
        @if(isset($user->id))
            <form action="{{ route('users.update', $user->id) }}" method="POST">
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
                            <input class="input  @error('name') border-danger @enderror" type="text" name="name" value="{{ $user->name ?? '' }}">
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
                            <input class="w-full input @error('email') border-danger @enderror" type="email" name="email" value="{{ $user->email ?? '' }}">
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
                            <input class="w-full input @error('nik') border-danger @enderror" type="number" name="nik" value="{{ $user->nik ?? '' }}">
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
                            <select class="input tomselect w-full @error('branch_id') border-danger @enderror" name="branch_id" id="branch_id">
                                <option value="">Pilih Branch</option>
                                @if(isset($branches))
                                    @foreach($branches as $row)
                                        @if(isset($user))
                                            <option value="{{ $row->id }}" {{ isset($user->branch_id) && $user->branch_id == $row->id?'selected' : '' }}>
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
                            Password
                        </label>

                        <div class="flex flex-wrap items-baseline w-full">
                            <div class="input @error('password') border-danger @enderror" data-toggle-password="true" data-toggle-password-permanent="true">
                                <input placeholder="Password" type="password" name="password"/>
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
