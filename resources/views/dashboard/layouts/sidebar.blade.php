<div class="ui bottom attached segment pushable">
    <div class="ui visible left vertical sidebar menu">
        <div class="item">
            <div class="ui center aligned container">
                <img class="ui tiny avatar image" src="{{ \Auth::user()->avatar }}">
                <br/>
                <br/>
                <div class="header">
                    {{ \Auth::user()->name }}
                </div>
                <br/>
                <a href="#" onclick="signOut();">Sign out</a>
            </div>
        </div>
        @foreach (\Auth::user()->role->menus as $menu)
            @if(str_contains($menu->redirect_to, 'id') and str_contains($menu->redirect_to, 'facilitator'))
                @if (!empty(\Auth::user()->facilitator_id))
                <a class="item" href="{{ url(str_replace('id', base64_encode('facilitator:'.\Auth::user()->facilitator_id), $menu->redirect_to)) }}">
                    <i class="{{ $menu->icon }}"></i>
                    {{ $menu->display_name }}
                </a>
                @endif
            @elseif(str_contains($menu->redirect_to, 'id') and str_contains($menu->redirect_to, 'presenter'))
                @if (!empty(\Auth::user()->presenter_id))
                <a class="item" href="{{ url(str_replace('id', base64_encode('presenter:'.\Auth::user()->presenter_id), $menu->redirect_to)) }}">
                    <i class="{{ $menu->icon }}"></i>
                    {{ $menu->display_name }}
                </a>
                @endif
            @else
                <a class="item" href="{{ url($menu->redirect_to) }}">
                    <i class="{{ $menu->icon }}"></i>
                    {{ $menu->display_name }}
                </a>
            @endif
        @endforeach
    </div>
    <div class="pusher">
        {{--<div class="ui basic segment">--}}
            {{--@yield('content')--}}
        {{--</div>--}}
    <div class="ui basic segment">
        <div class="ui grid">
            <div class="row">
                <div class="twelve wide column">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
