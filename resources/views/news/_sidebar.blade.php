<ul>
    <li class="sidebar-header"><a href="{{ url('news') }}" class="card-link">Recent Posts</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">News</div>
        @foreach($newses->take(5) as $news)
            <div class="sidebar-item"><a href="{{ $news->url }}" class="{{ set_active('news/'.$news->id.'*') }}">{{ $news->title }}</a></div>
        @endforeach
            <div class="sidebar-item"><a href="{{ url('news') }}">All News >></a></div>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Dev Logs</div>
        @foreach($devLogses->take(5) as $devLogs)
            <div class="sidebar-item"><a href="{{ $devLogs->url }}" class="{{ set_active('logs/'.$devLogs->id.'*') }}">{{ $devLogs->title }}</a></div>
        @endforeach
            <div class="sidebar-item"><a href="{{ url('logs') }}">All Logs >></a></div>
</ul>