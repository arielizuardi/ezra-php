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
        <a class="item" href="{{ url('dashboard/report') }}">
            <i class="block layout icon"></i>
            Report
        </a>
    </div>
    <div class="pusher">
        <div class="ui basic segment">
            @yield('content')
        </div>
    </div>
</div>
