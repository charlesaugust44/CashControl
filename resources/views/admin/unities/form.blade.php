@extends('layouts.app')

@section('content')
    <div class="admin-page">
        <div class="admin-scroll">
            <div class="admin-inner">
                <div class="admin-page-header">
                    <div class="admin-page-header__left">
                        <a href="{{ route('admin.unities.index') }}" class="admin-page-header__back">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div class="admin-page-header__info">
                            <h1 class="admin-page-header__title">
                                {{ isset($unity) ? __('admin.unities.edit_unity') : __('admin.unities.create_unity') }}
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__body">
                        @if($errors->any())
                            <div class="admin-form-errors">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ isset($unity) ? route('admin.unities.update', $unity->id) : route('admin.unities.store') }}" method="POST">
                            @csrf
                            @if(isset($unity))
                                @method('PUT')
                            @endif

                            <div class="admin-form-group">
                                <label for="name" class="admin-form-label">
                                    {{ __('admin.unities.name') }} <span>*</span>
                                </label>
                                <input type="text" class="admin-form-input" id="name" name="name" value="{{ old('name', $unity->name ?? '') }}" required>
                            </div>

                            <div class="admin-form-group">
                                <label for="description" class="admin-form-label">
                                    {{ __('admin.unities.description') }}
                                </label>
                                <textarea class="admin-form-input" id="description" name="description" rows="3">{{ old('description', $unity->description ?? '') }}</textarea>
                            </div>

                            <div class="admin-form-actions">
                                <button type="submit" class="admin-btn admin-btn--primary">
                                    {{ isset($unity) ? __('admin.unities.update') : __('admin.unities.create') }}
                                </button>
                                <a href="{{ route('admin.unities.index') }}" class="admin-btn admin-btn--secondary">
                                    {{ __('admin.unities.cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
