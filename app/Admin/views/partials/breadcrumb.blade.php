<!-- Обертка для breadcrumb с белым фоном -->
<div class="breadcrumb-wrapper fade-in">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb-custom">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <span class="breadcrumb-home-icon">
                        <i class="bi bi-house-door"></i>
                    </span>
                    {{ admin_trans('app.dashboard') }}
                </a>
            </li>
            
            {{-- Динамические элементы --}}
            @isset($items)
                @foreach($items as $item)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $item['title'] }}
                        </li>
                    @elseif(isset($item['url']))
                        <li class="breadcrumb-item">
                            <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                        </li>
                    @else
                        <li class="breadcrumb-item">{{ $item['title'] }}</li>
                    @endif
                @endforeach
            @else
                {{-- Если нет элементов, показываем текущий раздел --}}
                <li class="breadcrumb-item active" aria-current="page">
                    @yield('breadcrumb_current', 'Страница')
                </li>
            @endisset
        </ol>
    </nav>
</div>