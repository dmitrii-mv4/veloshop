@extends('admin.layouts.default')

@section('content')

    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ admin_trans('app.info_block.name') }}</h1>
                <p class="text-muted">{{ admin_trans('app.info_block.description') }}</p>
            </div>
            <div class="mt-4 mt-md-0">
                <a href="{{ route('admin.info_block.create') }}">
                    <button type="button" class="btn btn-alt-success me-1 mb-3">
                        <i class="fa fa-fw fa-plus opacity-50 me-1"></i> {{ admin_trans('app.info_block.create_block') }}
                    </button>
                </a>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ admin_trans('app.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ admin_trans('app.info_block.name') }}</li>
            </ol>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="content">

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">{{ admin_trans('app.info_block.list') }}</h3>
            </div>
            <div class="block-content">
                <div class="table-responsive">

                    @if ($items->isNotEmpty())
                        <table class="table table-bordered table-striped table-vcenter">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 100px;">
                                        <i class="far fa-user"></i>
                                    </th>
                                    <th>{{ admin_trans('app.title') }}</th>
                                    <th class="text-center" style="width: 100px;">{{ admin_trans('app.options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>111</td>
                                        <td class="text-center">
                                            <div class="btn-group">

                                                <a href="{{ route('admin.info_block.edit', $item->id) }}" type="button"
                                                    class="btn btn-sm btn-alt-secondary js-bs-tooltip-enabled"
                                                    data-bs-toggle="tooltip" aria-label="{{ admin_trans('app.edit') }}"
                                                    data-bs-original-title="{{ admin_trans('app.edit') }}">
                                                    <i class="fa fa-pencil-alt"></i>
                                                </a>

                                                <form action="{{ route('admin.info_block.delete', $item->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')

                                                    <input type="hidden" name="item_id" value="{{ $item->id }}">

                                                    <button type="submit"
                                                        class="btn btn-sm btn-alt-secondary js-bs-tooltip-enabled"
                                                        data-bs-toggle="tooltip" aria-label="{{ admin_trans('app.delete') }}"
                                                        data-bs-original-title="{{ admin_trans('app.delete') }}">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div>Инфоблоки пока не созданы</div>
                    @endif
                </div>
            </div>
        </div>

    </div>
    <!-- END Page Content -->

@endsection
