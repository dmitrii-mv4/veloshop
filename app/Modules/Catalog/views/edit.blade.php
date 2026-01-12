@extends('admin::layouts.default')

@section('title', 'Редактирование товара')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Редактирование товара</h3>
                    <div class="card-tools">
                        <a href="{{ route('catalog.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад к списку
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('catalog.update', $product) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Код товара</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="code" 
                                           value="{{ $product->code }}" 
                                           readonly>
                                    <small class="form-text text-muted">Код из 1С (не редактируется)</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ncode">Код номенклатуры</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="ncode" 
                                           value="{{ $product->ncode }}" 
                                           readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price">Цена</label>
                                    <input type="number" 
                                           step="0.01"
                                           min="0"
                                           class="form-control @error('price') is-invalid @enderror" 
                                           id="price" 
                                           name="price" 
                                           value="{{ old('price', $product->price) }}">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price2">Цена 2</label>
                                    <input type="number" 
                                           step="0.01"
                                           min="0"
                                           class="form-control @error('price2') is-invalid @enderror" 
                                           id="price2" 
                                           name="price2" 
                                           value="{{ old('price2', $product->price2) }}">
                                    @error('price2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stock">Остаток</label>
                                    <input type="number" 
                                           min="0"
                                           class="form-control @error('stock') is-invalid @enderror" 
                                           id="stock" 
                                           name="stock" 
                                           value="{{ old('stock', $product->stock) }}">
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group_code">Код группы</label>
                                    <input type="text" 
                                           class="form-control @error('group_code') is-invalid @enderror" 
                                           id="group_code" 
                                           name="group_code" 
                                           value="{{ old('group_code', $product->group_code) }}">
                                    @error('group_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group_name">Название группы</label>
                                    <input type="text" 
                                           class="form-control @error('group_name') is-invalid @enderror" 
                                           id="group_name" 
                                           name="group_name" 
                                           value="{{ old('group_name', $product->group_name) }}">
                                    @error('group_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mt-3">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Товар активен</label>
                        </div>
                        
                        <div class="form-check mt-2">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="has_image" 
                                   name="has_image" 
                                   value="1" 
                                   {{ old('has_image', $product->has_image) ? 'checked' : '' }}
                                   disabled>
                            <label class="form-check-label" for="has_image">Есть изображение в 1С</label>
                        </div>
                        
                        <hr class="mt-4">
                        
                        <!-- Переводы -->
                        <h5>Переводы</h5>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>Русский (ru)</h6>
                                @php
                                    $ruTranslation = $product->getTranslation('ru', '00001');
                                @endphp
                                
                                <div class="form-group">
                                    <label for="translations_ru_name">Название *</label>
                                    <input type="text" 
                                           class="form-control @error('translations.ru.name') is-invalid @enderror" 
                                           id="translations_ru_name" 
                                           name="translations[ru][name]" 
                                           value="{{ old('translations.ru.name', $ruTranslation->name ?? '') }}"
                                           required>
                                    @error('translations.ru.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="translations_ru_description">Описание</label>
                                    <textarea class="form-control @error('translations.ru.description') is-invalid @enderror" 
                                              id="translations_ru_description" 
                                              name="translations[ru][description]" 
                                              rows="3">{{ old('translations.ru.description', $ruTranslation->description ?? '') }}</textarea>
                                    @error('translations.ru.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <input type="hidden" name="translations[ru][shop_code]" value="00001">
                                <input type="hidden" name="translations[ru][locale]" value="ru">
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Английский (en)</h6>
                                @php
                                    $enTranslation = $product->getTranslation('en', '00001');
                                @endphp
                                
                                <div class="form-group">
                                    <label for="translations_en_name">Название</label>
                                    <input type="text" 
                                           class="form-control @error('translations.en.name') is-invalid @enderror" 
                                           id="translations_en_name" 
                                           name="translations[en][name]" 
                                           value="{{ old('translations.en.name', $enTranslation->name ?? '') }}">
                                    @error('translations.en.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="translations_en_description">Описание</label>
                                    <textarea class="form-control @error('translations.en.description') is-invalid @enderror" 
                                              id="translations_en_description" 
                                              name="translations[en][description]" 
                                              rows="3">{{ old('translations.en.description', $enTranslation->description ?? '') }}</textarea>
                                    @error('translations.en.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <input type="hidden" name="translations[en][shop_code]" value="00001">
                                <input type="hidden" name="translations[en][locale]" value="en">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        
                        <button type="button" class="btn btn-warning" id="syncProductBtn">
                            <i class="fas fa-sync-alt"></i> Синхронизировать с 1С
                        </button>
                        
                        <a href="{{ route('catalog.index') }}" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Боковая панель -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Информация</h3>
                </div>
                <div class="card-body">
                    <h6>Основная информация</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Создан:</td>
                            <td>{{ $product->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Обновлен:</td>
                            <td>{{ $product->updated_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Синхронизация:</td>
                            <td>
                                @if($product->synced_at)
                                    {{ $product->synced_at->format('d.m.Y H:i') }}
                                @else
                                    <span class="text-muted">Никогда</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    
                    <h6 class="mt-4">Свойства из 1С</h6>
                    @if($product->properties && count($product->properties) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Значение</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->properties as $property)
                                        <tr>
                                            <td>{{ $property['name'] ?? '—' }}</td>
                                            <td>{{ $property['value'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Нет свойств</p>
                    @endif
                    
                    <h6 class="mt-4">Остатки по складам</h6>
                    @if($product->warehouses && count($product->warehouses) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Склад</th>
                                        <th>Количество</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->warehouses as $warehouse)
                                        <tr>
                                            <td>{{ $warehouse['code'] ?? '—' }}</td>
                                            <td>{{ $warehouse['quantity'] ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Нет данных по складам</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Синхронизация товара с 1С
    const syncProductBtn = document.getElementById('syncProductBtn');
    
    syncProductBtn.addEventListener('click', function() {
        if (!confirm('Синхронизировать этот товар с 1С? Текущие данные будут обновлены.')) {
            return;
        }
        
        syncProductBtn.disabled = true;
        syncProductBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Синхронизация...';
        
        fetch(`/admin/catalog/product/{{ $product->code }}/sync`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Товар успешно синхронизирован с 1С');
                    location.reload();
                } else {
                    alert('Ошибка синхронизации: ' + data.message);
                }
            })
            .catch(error => {
                alert('Ошибка запроса: ' + error.message);
            })
            .finally(() => {
                syncProductBtn.disabled = false;
                syncProductBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Синхронизировать с 1С';
            });
    });
});
</script>
@endpush