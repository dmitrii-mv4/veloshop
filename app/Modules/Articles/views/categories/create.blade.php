@extends('admin::layouts.default')

@section('title', 'Создание категории | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Статьи', 'url' => route('admin.articles.index')],
                ['title' => 'Категории', 'url' => route('admin.articles.categories.index')],
                ['title' => 'Создание'],
            ],
        ])
    </div>

    <div class="page-actions fade-in">
        <h1 class="h5 mb-0">Создание категории</h1>
    </div>

    <div class="card fade-in">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.articles.categories.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Название <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Символьный код</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Оставьте пустым для автоматической генерации">
                    <small class="form-text text-muted">Используется в URL. Должен быть уникальным.</small>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Описание</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.articles.categories.index') }}" class="btn btn-outline-secondary">Отмена</a>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
@endsection