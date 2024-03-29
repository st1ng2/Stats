
@extends('Core.Admin.Http.Views.layout', [
    'title' => __('admin.title', ['name' => __('stats.admin.title')]),
])

@push('header')
@endpush

@push('content')
    <div class="admin-header d-flex justify-content-between align-items-center">
        <div>
            <h2>@t('stats.admin.title')</h2>
            <p>@t('stats.admin.setting_description')</p>
        </div>
        <div>
            <a href="{{url('admin/module_stats/add')}}" class="btn size-s outline">
                @t('stats.admin.add')
            </a>
        </div>
    </div>

    {!! $table !!}
@endpush

@push('footer')
@endpush
