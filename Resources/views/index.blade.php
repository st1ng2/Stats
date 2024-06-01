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
        <div class="row gx-3 gy-3">
            @if (sizeof($servers) <= 3 && sizeof($servers) > 1)
                <div class="col-md-12">
                    <div class="servers">
                        @foreach ($servers as $key => $server)
                            <a href="{{ url('stats/')->addParams(['sid' => $key]) }}"
                                class="btn size-s @if (isset($server['current'])) primary @else outline @endif">
                                {{ $server['server']->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="col-md-{{ sizeof($servers) > 3 ? 9 : 12 }}">
                <div class="card">
                    <div class="card-header mb-3">
                        <h2>@t('stats.title')</h2>
                        <p>@t('stats.description')</p>
                    </div>
                    {!! $stats !!}
                </div>
            </div>

            @if (sizeof($servers) > 3)
                <div class="col-md-3">
                    <div class="servers servers-block">
                        <h3>@t('stats.choose_server')</h3>
                        <div class="servers-block-container">
                            @foreach ($servers as $key => $server)
                                <a href="{{ url('stats/')->addParams(['sid' => $key]) }}"
                                    class="servers-block-btn @if (isset($server['current'])) selected @endif">
                                    {{ $server['server']->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endpush

@footer
