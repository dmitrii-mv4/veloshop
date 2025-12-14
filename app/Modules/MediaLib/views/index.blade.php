@extends('admin.layouts.default')

@section('content')

    <!-- Hero -->
    <div class="content">
        <div class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">Медиабиблиотека</h1>
                <p class="text-muted">Управление файлами и изображениями</p>
            </div>
            <div class="mt-4 mt-md-0">
                <button type="button" class="btn btn-outline-primary me-1 mb-3" data-bs-toggle="modal"
                    data-bs-target="#uploadModal">
                    <i class="fa fa-fw fa-upload me-1"></i> Загрузить
                </button>
                <button type="button" class="btn btn-outline-warning me-1 mb-3" data-bs-toggle="modal"
                    data-bs-target="#createFolderModal">
                    <i class="fa fa-fw fa-folder me-1"></i> Создать папку
                </button>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @foreach($breadcrumbs as $breadcrumb)
                    <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                        @if(!$loop->last)
                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                        @else
                            {{ $breadcrumb['name'] }}
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    <!-- Вывод сообщений -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Основной контент -->
    <div class="content">
        @if($folders->count() > 0 || $mediaFiles->count() > 0)
            <div class="row">
                <!-- Папки -->
                @foreach($folders as $folder)

                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="block block-rounded folder-item position-relative">
                            <div class="folder-actions position-absolute top-0 end-0 mt-2 me-2">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-alt-secondary dropdown-toggle" type="button" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item" data-bs-toggle="modal" 
                                                    data-bs-target="#editFolderModal" 
                                                    data-folder-id="{{ $folder->id }}" 
                                                    data-folder-name="{{ $folder->name }}">
                                                <i class="fa fa-edit me-1"></i> Редактировать
                                            </button>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.media.destroy-folder', $folder->id) }}" 
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item" 
                                                        onclick="return confirm('Удалить папку и все содержимое?')">
                                                    <i class="fa fa-trash me-1"></i> Удалить
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="block-content block-content-full text-center" 
                                 onclick="window.location.href='{{ route('admin.media', ['folder_id' => $folder->id]) }}'" 
                                 style="cursor: pointer;">
                                <div class="py-4">
                                    <i class="fa fa-folder fa-3x text-warning"></i>
                                </div>
                                <div class="fw-semibold text-truncate" title="{{ $folder->name }}">
                                    {{ $folder->name }}
                                </div>
                                <small class="text-muted">
                                    Папка
                                </small>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        {{ $folder->created_at->format('d.m.Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Модальное окно редактирования папки -->
                    <div class="modal fade" id="editFolderModal" tabindex="-1" role="dialog" aria-labelledby="editFolderModalLabel"
                    aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editFolderModalLabel">
                                        <i class="fa fa-edit me-2"></i>Редактировать папку
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.media.update-folder', $folder->id) }}" method="POST" id="editFolderForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="edit_folder_name" class="form-label">Название папки</label>
                                            <input type="text" class="form-control" id="edit_folder_name" name="folder_name" 
                                                placeholder="Введите название папки" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Файлы -->
                @foreach($mediaFiles as $media)
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="block block-rounded media-item position-relative" data-bs-toggle="modal" data-bs-target="#modal-detail-{{ $media->id }}">
                            <div class="block-content block-content-full text-center media-content" 
                                data-media-id="{{ $media->id }}" 
                                data-media-name="{{ $media->name }}"
                                data-media-size="{{ number_format($media->size / 1024, 2) }}"
                                data-media-date="{{ $media->created_at->format('d.m.Y H:i') }}"
                                data-media-type="{{ $media->mime_type }}"
                                data-media-url="{{ Storage::disk($media->disk)->url($media->path . '/' . $media->filename) }}"
                                style="cursor: pointer;">
                                @if (str_starts_with($media->mime_type, 'image/'))
                                    <img src="{{ asset('storage/' . $media->path . '/' . $media->filename) }}" alt="{{ $media->name }}" class="img-fluid mb-2 media-preview" style="max-height: 150px; object-fit: cover;">
                                @else
                                    <div class="py-4">
                                        <i class="fa fa-file fa-3x text-muted media-icon"></i>
                                    </div>
                                @endif

                                <div class="fw-semibold text-truncate media-name" title="{{ $media->name }}">
                                    {{ $media->name }}
                                </div>
                                <small class="text-muted media-size">
                                    {{ number_format($media->size / 1024, 2) }} KB
                                </small>
                                <div class="mt-2">
                                    <small class="text-muted media-date">
                                        {{ $media->created_at->format('d.m.Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <!-- Модальное окно детального просмотра файла -->
                    <div class="modal" id="modal-detail-{{ $media->id }}" tabindex="-1" aria-labelledby="modal-detail-{{ $media->id }}" aria-hidden="true" style="display: none;" >
                        <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h5 class="modal-title">Информация о файле</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pb-1">
                                <div class="row items-push">
                                    <div class="col-md-4" style="text-align: center">
                                        @if (str_starts_with($media->mime_type, 'image/'))
                                            <img src="{{ asset('storage/' . $media->path . '/' . $media->filename) }}" alt="{{ $media->name }}" class="img-fluid mb-2 media-preview" style="max-height: 150px; object-fit: cover;">
                                        @else
                                            <div class="py-4">
                                                <i class="fa fa-file fa-3x text-muted media-icon"></i>
                                            </div>
                                        @endif                                    
                                    </div>
                                    <div class="col-md-8" style="overflow-wrap: break-word;">
                                        <div><b>Дата создания:</b> {{ $media['created_at'] }}</div>
                                        <div><b>Создал:</b> {{ $media->user->name }}</div>
                                        <div><b>Имя файла:</b> {{ $media['filename'] }}</div>
                                        <div><b>Тип файла:</b> {{ $media['mime_type'] }}</div>
                                        <div><b>Размер файла:</b> {{ $media['size'] }} MB</div>
                                        <div><b>Публичная ссылка для файла:</b> {{ asset('storage/' . $media->path . '/' . $media->filename) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <form action="{{ route('admin.media.destroy-file', $media->id) }}" 
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('Удалить файл?')">
                                            <i class="fa fa-trash me-1"></i> Удалить
                                        </button>
                                </form>
                            </div>
                        </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="block block-rounded">
                <div class="block-content">
                    <p class="text-muted text-center py-5">
                        <i class="fa fa-images fa-3x mb-3"></i><br>
                        @if($currentFolder)
                            Папка "{{ $currentFolder->name }}" пуста
                        @else
                            Ваши файлы появятся здесь после загрузки
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>

    <!-- Модальное окно загрузки -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="fa fa-upload me-2"></i>Загрузка файлов
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
                        
                        <!-- Drag & Drop зона -->
                        <div class="drop-zone mb-4" id="dropZone">
                            <div class="drop-zone-content">
                                <input type="file" id="fileInput" name="files[]" multiple
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                            </div>
                        </div>

                        <!-- Информация о поддерживаемых форматах -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Поддерживаемые форматы:</h6>
                            <ul class="mb-0">
                                <li><strong>Изображения:</strong> JPEG, JPG, PNG, GIF, WebP, SVG (макс. 20MB)</li>
                                <li><strong>Документы:</strong> PDF, DOC, DOCX (макс. 20MB)</li>
                            </ul>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button class="btn btn-primary">
                        <i class="fa fa-upload me-1"></i>Загрузить
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно создания папки -->
    <div class="modal fade" id="createFolderModal" tabindex="-1" role="dialog" aria-labelledby="createFolderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createFolderModalLabel">
                        <i class="fa fa-folder me-2"></i>Создать папку
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.media.create-folder') }}" method="POST">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="folder_name" class="form-label">Название папки</label>
                            <input type="text" class="form-control" id="folder_name" name="folder_name" 
                                   placeholder="Введите название папки" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Создать папку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Все превью медиафайлов -->
    @foreach($mediaFiles as $media)
        <div class="media-full-preview" id="preview-{{ $media->id }}" style="display: none;">
            <!-- Боковая панель для просмотра медиа -->
            <div class="media-preview-sidebar">
                <div class="media-preview-header">
                    <h3>Просмотр медиа</h3>
                    <button class="close-btn" title="Закрыть">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="media-preview-content">
                    <div class="media-preview-container">
                        @if (str_starts_with($media->mime_type, 'image/'))
                            <img src="{{ Storage::disk($media->disk)->url($media->path . '/' . $media->filename) }}" 
                                alt="{{ $media->name }}" style="max-width: 100%; max-height: 400px; object-fit: contain;">
                        @else
                            <div class="file-preview">
                                <i class="fa fa-file fa-3x text-muted"></i>
                                <p>Файл: {{ $media->name }}</p>
                                <small class="text-muted">Этот тип файла можно только скачать</small>
                                <div class="mt-3">
                                    <a href="{{ Storage::disk($media->disk)->url($media->path . '/' . $media->filename) }}"
                                        download="{{ $media->name }}" class="btn btn-primary">
                                        <i class="fa fa-download"></i> Скачать файл
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="media-info">
                        <h4>{{ $media->name }}</h4>
                        <div class="media-details">
                            <p><strong>Тип:</strong> <span>{{ $media->mime_type }}</span></p>
                            <p><strong>Размер:</strong> <span>{{ number_format($media->size / 1024, 2) }} KB</span></p>
                            <p><strong>Дата:</strong> <span>{{ $media->created_at->format('d.m.Y H:i') }}</span></p>
                            <p><strong>Кто создал:</strong> <span>{{ $media->user->name ?? 'Неизвестно' }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Затемнение фона -->
            <div class="media-preview-overlay"></div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Media library script loaded');

    // ОЧЕНЬ ПРОСТОЙ И РАБОЧИЙ КОД
    const mediaBlocks = document.querySelectorAll('.block-content[data-media-id]');
    console.log('Found media blocks:', mediaBlocks.length);
    
    mediaBlocks.forEach(block => {
        block.addEventListener('click', function(e) {
            // Предотвращаем открытие если кликнули на dropdown
            if (e.target.closest('.file-actions')) {
                console.log('Click on dropdown - ignoring');
                return;
            }
            
            console.log('Opening modal for file');
            e.preventDefault();
            e.stopPropagation();
        });
    });
});
</script>
@endpush