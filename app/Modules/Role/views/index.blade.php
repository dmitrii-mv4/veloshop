@extends('admin.layouts.default')

@section('content')
    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">Роли пользователей</h1>
            </div>
            <div class="mt-4 mt-md-0">
                @can('create', \App\Modules\Role\Models\Role::class)
                    <a href="{{ route('admin.roles.create') }}">
                        <button type="button" class="btn btn-alt-success me-1 mb-3">
                            <i class="fa fa-fw fa-plus opacity-50 me-1"></i> Создать роль
                        </button>
                    </a>
                @endcan
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Роли пользователей</li>
            </ol>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="content">

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Список ролей</h3>
            </div>
            <div class="block-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th class="text-center" style="width: 100px;">Опции</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $role->name }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">

                                            @can('update', $role)
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" type="button"
                                                class="btn btn-sm btn-alt-secondary js-bs-tooltip-enabled"
                                                data-bs-toggle="tooltip" aria-label="Edit" data-bs-original-title="Edit">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>
                                            @endcan

                                            @can('delete', $role)
                                            <form action="{{ route('admin.roles.delete', $role->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <input type="hidden" name="role_id" value="{{ $role->id }}">

                                                <button type="submit"
                                                    class="btn btn-sm btn-alt-secondary js-bs-tooltip-enabled"
                                                    data-bs-toggle="tooltip" aria-label="Delete"
                                                    data-bs-original-title="Delete">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- END Page Content -->
@endsection
