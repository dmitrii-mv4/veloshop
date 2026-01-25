<tr class="menu-item">
    <td>{{ $item->order }}</td>
    <td>
        <div class="d-flex align-items-center" style="padding-left: {{ $level * 30 }}px;">
            @if($level > 0)
                <i class="bi bi-arrow-return-right text-muted me-2"></i>
            @endif
            @if($item->hasChildren())
                <i class="bi bi-folder-fill text-warning me-2"></i>
            @else
                <i class="bi bi-file-earmark text-secondary me-2"></i>
            @endif
            @if($item->icon)
                <i class="bi bi-{{ $item->icon }} text-primary me-2"></i>
            @endif
            <div>
                <div class="fw-semibold">{{ $item->title }}</div>
                @if($item->seo_title)
                    <div class="text-muted small">{{ $item->seo_title }}</div>
                @endif
            </div>
        </div>
    </td>
    <td>
        <code class="small">{{ Str::limit($item->url, 30) }}</code>
    </td>
    <td>
        @if($item->is_active)
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                <i class="bi bi-check-circle me-1"></i> Активен
            </span>
        @else
            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                <i class="bi bi-x-circle me-1"></i> Не активен
            </span>
        @endif
    </td>
    <td>{{ $item->order }}</td>
    <td>
        <div class="table-actions justify-content-end">
            <a href="{{ route('admin.menu.items.create', $menu) }}?parent_id={{ $item->id }}" 
               class="btn btn-outline-success btn-sm me-1" 
               title="Добавить подпункт">
                <i class="bi bi-plus-lg"></i>
            </a>
            <a href="{{ route('admin.menu.items.edit', [$menu, $item]) }}" 
               class="btn btn-outline-primary btn-sm me-1" 
               title="Редактировать">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.menu.items.destroy', [$menu, $item]) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить пункт меню «{{ $item->title }}»?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>

@foreach($item->children as $child)
    @include('menu::menuitem.partials.item-row', ['item' => $child, 'level' => $level + 1])
@endforeach