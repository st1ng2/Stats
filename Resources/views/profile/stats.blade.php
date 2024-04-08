@push('header')
    @at(mm('Stats', 'Resources/assets/scss/profile_stats.scss'))
@endpush

@push('profile_body')
    @if (sizeof($servers) > 1)
        <div class="servers mb-3">
            @foreach ($servers as $key => $server)
                <a href="{{ url('profile/' . $user->id)->addParams(['sid' => $key, 'tab' => 'stats']) }}"
                    class="btn size-s @if (isset($server['current'])) primary @else outline @endif">
                    {{ $server['server']->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if (!empty($stats))
        <div class="row gx-3 gy-3">
            @foreach ($blocks as $key => $block)
                <div class="col-md-4">
                    <div class="profile-stat-block">
                        <i class="ph {{ $block['icon'] }}"></i>

                        <div>
                            <p>{{ __($block['text']) }}</p>
                            <div>{{ $stats['stats'][$key] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <h3 class="text-center">@t('stats.profile.no_info')</h3>
    @endif
@endpush
