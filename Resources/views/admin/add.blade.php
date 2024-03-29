@extends('Core.Admin.Http.Views.layout', [
    'title' => __('admin.title', ['name' => __('stats.admin.add')]),
])

@push('content')
    <div class="admin-header d-flex align-items-center">
        <a href="{{ url('admin/module_stats/list') }}" class="back_btn">
            <i class="ph ph-caret-left"></i>
        </a>
        <div>
            <h2>@t('stats.admin.add')</h2>
            <p>@t('stats.admin.add_description')</p>
        </div>
    </div>

    <form data-form="add" data-page="module_stats" enctype="multipart/form-data">
        @csrf
        <div class="position-relative row form-group">
            <div class="col-sm-3 col-form-label required">
                <label for="dbname">@t('stats.admin.dbname')</label>
                <small class="form-text text-muted">@t('stats.admin.dbname_desc')</small>
            </div>
            <div class="col-sm-9">
                <select name="dbname" id="dbname" class="form-control">
                    @foreach (config('database.databases') as $key => $val)
                        <option value="{{ $key }}">
                            {{ $key }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="position-relative row form-group">
            <div class="col-sm-3 col-form-label required">
                <label for="mod">@t('stats.admin.mod')</label>
                <small class="form-text text-muted">@t('stats.admin.mod_desc')</small>
            </div>
            <div class="col-sm-9">
                <select name="mod" id="mod" class="form-control">

                    @foreach ($drivers as $key => $val)
                        <option value="{{ $key }}">
                            {{ basename($val) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="position-relative row form-group">
            <div class="col-sm-3 col-form-label required">
                <label for="sid">@t('stats.admin.server_label')</label>
            </div>
            <div class="col-sm-9">
                <select name="sid" id="sid" class="form-control">

                    @foreach ($servers as $key => $server)
                        <option value="{{ $server->id }}">
                            {{ $server->id }} - {{ $server->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Кнопка отправки -->
        <div class="position-relative row form-check">
            <div class="col-sm-9 offset-sm-3">
                <button type="submit" data-save class="btn size-m btn--with-icon primary">
                    @t('def.save')
                    <span class="btn__icon arrow"><i class="ph ph-arrow-right"></i></span>
                </button>
            </div>
        </div>
    </form>
@endpush
