@extends('user.layout', ['user' => isset($user) ? $user : null])

@section('profile-title')
    {{ $user->name }}'s Profile
@endsection

@section('meta-img')
    {{ $user->avatarUrl }}
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url]) !!}

    @if (Auth::check() && Auth::user()->id != $user->id && config('lorekeeper.mod_mail.allow_user_mail'))
        <a class="btn btn-primary btn-sm float-right" href="{{ url('mail/new?recipient_id=' . $user->id) }}"><i class="fas fa-envelope"></i> Message User</a>
    @endif

    @if (mb_strtolower($user->name) != mb_strtolower($name))
        <div class="alert alert-info">This user has changed their name to <strong>{{ $user->name }}</strong>.</div>
    @endif

    @if ($user->is_banned)
        <div class="alert alert-danger">This user has been banned.</div>
    @endif

    @if (Auth::user() ?? false && Auth::user()->isStaff && $user->settings->strike_count > 0)
        <div class="alert alert-warning">
            This user has received {{ $user->settings->strike_count }} strike{{ $user->settings->strike_count > 1 ? 's' : '' }}.
        </div>
    @endif

    @if ($user->is_deactivated)
        <div class="alert alert-info text-center">
            <h1>{!! $user->displayName !!}</h1>
            <p>This account is currently deactivated, be it by staff or the user's own action. All information herein is hidden until the account is reactivated.</p>
            @if (Auth::check() && Auth::user()->isStaff)
                <p class="mb-0">As you are staff, you can see the profile contents below and the sidebar contents.</p>
            @endif
        </div>
    @endif

    @if (!$user->is_deactivated || (Auth::check() && Auth::user()->isStaff))
        @include('user._profile_content', ['user' => $user, 'deactivated' => $user->is_deactivated])
    @endif

@endsection
