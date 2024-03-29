@extends(tt('layout.blade.php'))

@section('title')
    {{ !empty(page()->title) ? page()->title : __('stats.title') }}
@endsection

@push('header')
    @at('Modules/Stats/Resources/assets/scss/index_stats.scss')
@endpush

@push('content')
    @navbar
    <div class="container">
        @navigation
        @breadcrumb
        @flash
        @editor

        <div class="servers mb-3">
            @foreach ($servers as $key => $server)
                <a href="{{ url('stats/')->addParams(['sid' => $key]) }}" class="btn size-s @if(isset($server['current'])) primary @else outline @endif">
                    {{ $server['server']->name }}
                </a>
            @endforeach
        </div>

        <div class="card">
            <div class="card-header mb-3">
                <h2>@t('stats.title')</h2>
                <p>@t('stats.description')</p>
            </div>
            {!! $stats !!}
        </div>
    </div>
@endpush

@footer
