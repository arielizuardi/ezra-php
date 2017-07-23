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
                <form action="{{ url('logout') }}" method="POST">
                    {{ csrf_field() }}
                    <button class="ui button" type="submit">Logout</button>
                </form>
            </div>
        </div>
        @foreach (\Auth::user()->role->menus as $menu)
            <a class="item" href="{{ url($menu->redirect_to) }}">
                <i class="{{ $menu->icon }}"></i>
                {{ $menu->display_name }}
            </a>
        @endforeach
    </div>
    <div class="pusher">
        <div class="ui basic segment">
            @yield('content')
        </div>
    </div>
</div>
