@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render(request()->route()->getName()) }}
@endsection

@section('content')
    <div class="w-full grid gap-5 lg:gap-7.5 mx-auto">
        <form action="{{ isset($position->id) ? route('users.positions.update', $position->id) : route('users.positions.store') }}" method="POST" id="position_form">
            @csrf
            @if(isset($position->id))
                <input type="hidden" name="id" value="{{ $position->id }}">
                @method('PUT')
            @endif
            <div class="card pb-2.5">
                <div class="card-header" id="basic_settings">
                    <h3 class="card-title">
                        {{ isset($position->id) ? 'Edit' : 'Add' }} Position
                    </h3>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('users.positions.index') }}" class="btn btn-xs btn-info">Back</a>
                    </div>
                </div>
                <div class="card-body grid gap-5">
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Code
                        </label>
                        <div class="flex flex-wrap items-baseline w-full">
                            <input class="input @error('code') border-danger @enderror" type="text" name="code" value="{{ $position->code ?? '' }}">
                            @error('code')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                            @enderror
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Name
                        </label>
                        <div class="flex flex-wrap items-baseline w-full">
                            <input class="input @error('name') border-danger @enderror" type="text" name="name" value="{{ $position->name ?? '' }}">
                            @error('name')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                            @enderror
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Level
                        </label>
                        <div class="flex flex-wrap items-baseline w-full">
                            <input class="input @error('level') border-danger @enderror" type="number" name="level" value="{{ $position->level ?? '' }}">
                            @error('level')
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
