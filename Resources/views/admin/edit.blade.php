@extends('Core.Admin.Http.Views.layout', [
    'title' => __('admin.title', ['name' => __('stats.admin.edit_title')]),
])

@push('content')
    <div class="admin-header d-flex align-items-center">
        <a href="{{ url('admin/module_stats/list') }}" class="back_btn">
            <i class="ph ph-caret-left"></i>
        </a>
        <div>
            <h2>@t('stats.admin.edit_title')</h2>
            <p>@t('stats.admin.edit_description')</p>
        </div>
    </div>

    <form data-form="edit" data-page="module_stats" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $connection->id }}">
        <div class="position-relative row form-group">
            <div class="col-sm-3 col-form-label required">
                <label for="dbname">@t('stats.admin.dbname')</label>
                <small class="form-text text-muted">@t('stats.admin.dbname_desc')</small>
            </div>
            <div class="col-sm-9">
                <select name="dbname" id="dbname" class="form-control">
                    @foreach (config('database.databases') as $key => $val)
                        <option value="{{ $key }}" @if( $key === $connection->dbname ) selected @endif>
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
                        <option value="{{ $key }}" @if( $key === $connection->mod ) selected @endif>
                            {{ basename($val) }}</option>
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