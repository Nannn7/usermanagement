@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render(request()->route()->getName()) }}
@endsection

@section('content')
    <div class="w-full grid gap-5 lg:gap-7.5 mx-auto">
        @if(isset($permission->id))
            <form action="{{ route('users.permissions.update', $permission->id) }}" method="POST">
                <input type="hidden" name="id" value="{{ $permission->id }}">
            @method('PUT')
        @else
            <form method="POST" action="{{ route('users.permissions.store') }}">
        @endif
        @csrf
            <div class="card pb-2.5">
                <div class="card-header" id="basic_settings">
                    <h3 class="card-title">
                        {{ isset($permission->id) ? 'Edit' : 'Add' }} Permission
                    </h3>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('users.permissions.index') }}" class="btn btn-xs btn-info">Back</a>
                    </div>
                </div>
                <div class="card-body grid gap-5">

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Name
                        </label>
                        <div class="flex flex-wrap items-baseline w-full">
                            <input class="input  @error('name') border-danger @enderror" type="text" name="name" value="{{ $permission->name ?? '' }}">
                            @error('name')
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
