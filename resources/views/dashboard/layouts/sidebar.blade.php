<div class="ui bottom attached segment pushable">
    <div class="ui visible left vertical sidebar menu">
        <div class="item">
            <div class="ui center aligned container">
                <img class="ui tiny avatar image" src="https://lh3.googleusercontent.com/-nie7hvurjew/AAAAAAAAAAI/AAAAAAAAAhE/rl031rVEtR0/photo.jpg">
                <br/>
                <br/>
                <div class="header">
                    Arie Ardaya Lizuardi
                </div>
                <br/>
                <a href="#">Logout</a>
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
