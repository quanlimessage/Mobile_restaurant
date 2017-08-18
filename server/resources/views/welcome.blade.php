<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ url('/login') }}">Login</a>
                        <a href="{{ url('/register') }}">Register</a>
                    @endif
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    Laravel
                </div>

                <div class="links">
                    <a href="https://laravel.com/docs">Documentation</a>
                    <a href="https://laracasts.com">Laracasts</a>
                    <a href="https://laravel-news.com">News</a>
                    <a href="https://forge.laravel.com">Forge</a>
                    <a href="https://github.com/laravel/laravel">GitHub</a>
                </div>

                <div class="trung">
                    <h1>TEST ADD TABLE</h1>

                    <form action="<?php echo URL::to('api/manager/tables'); ?>" method="post">
                        {{ method_field('GET') }}
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="submit" name="Submit" value="Show all">
                    </form>

                    <form action="<?php echo URL::to('api/manager/tables'); ?>" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <p>table</p>
                        <input type="text" name="table_status" placeholder="table_status" /><br>
                        <input type="text" name="num_of_seat" placeholder="num_of_seat" /><br>
                        <p>table_description 1</p>
                        <input type="text" name="tb_des[VN][table_name]" placeholder="table_name" /><br>
                        <input type="text" name="tb_des[VN][table_description]" placeholder="table_description" /><br>
                        <p>table_description 2</p>
                        <input type="text" name="tb_des[JP][table_name]" placeholder="table_name" /><br>
                        <input type="text" name="tb_des[JP][table_description]" placeholder="table_description" /><br>
                        <input type="submit" name="Submit" value="Add">
                    </form>

                    <h1>TEST UPDATE TABLE</h1>
                    <form action="<?php echo URL::to('api/manager/tables/27'); ?>" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        {{ method_field('PUT') }}
                        <p>table</p>
                        <input type="text" name="table_status" placeholder="table_status" /><br>
                        <input type="text" name="num_of_seat" placeholder="num_of_seat" /><br>
                        <p>table_description 1</p>
                        <input type="text" name="tb_des[VN][table_name]" placeholder="table_name" /><br>
                        <input type="text" name="tb_des[VN][table_description]" placeholder="table_description" /><br>
                        <p>table_description 2</p>
                        <input type="text" name="tb_des[JP][table_name]" placeholder="table_name" /><br>
                        <input type="text" name="tb_des[JP][table_description]" placeholder="table_description" /><br>
                        <input type="submit" name="Submit" value="update">
                    </form>
                    <form action="<?php echo URL::to('api/manager/tables/27'); ?>" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        {{ method_field('DELETE') }}
                        <input type="submit" name="Submit" value="delete">
                    </form>
					
					<form action="http://localhost/mobile_restaurant/server/public/oauth/token" method="POST">
                        <input type="hidden" name="username" value="l_thi@stagegroup.jp">
						<input type="hidden" name="password" value="123456">
						<input type="hidden" name="grant_type" value="password">
						<input type="hidden" name="client_id" value="2">
						<input type="hidden" name="client_secret" value="riLc61gVyK0bmmUaATyVeTCWMlHa99F9GHCOa3uk">
						<input type="hidden" name="scope" value="">
                        <input type="submit" name="Submit" value="Submit">
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
