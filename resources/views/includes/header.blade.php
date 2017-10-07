<nav class="navbar navbar-default navbar-fixed-top" style="margin-bottom: 0px">
    <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mobile-header" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ URL::asset('img/logo.png') }}" alt="IndiaReads" class="img-responsive logo-header">
            </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="category">
            <ul class="nav navbar-nav">
                <li><a href="javascript:void(0)" id="browse-cat">Browse Rooms <span class="caret"></span></a></li>
            </ul>
            <form class="navbar-form navbar-left" method="post" action="{{ route('search') }}">
           <input type="hidden" name='nexttoken' value="0">
           <input type="hidden" name='paginatevalue' value="1">
           <input type='hidden' name='filter1' value='video1'>
           <input type='hidden' name='filter2' value='book1'>
           <input type='hidden' name='filter3' value='article1'>
           <input type="hidden" name="_token" value="{{ csrf_token() }}">
           <input type='hidden' name='data' value=''>
                    <input type="hidden" name="nextYoutubetoken" value="0">
                    <input type="hidden" name="course_type" value="all">
                <div class="form-group{{ $errors->has('keywords') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                    <div class="input-group">
                        <input id="keywords" type="search" class="form-control home-search" name="q" value="@if(isset($req['q'])){{ $req['q'] }}@endif" required autofocus placeholder="Search.." style="min-width: 300px">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-default home-search-btn" type="button"><i class="fa fa-search"></i></button>
                        </span>
                        @if($errors->has('keywords'))
                            <span class="help-block">
                                <strong>{{ $errors->first('keywords') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </form>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{ route('rooms') }}">Rooms</a></li>
                <li><a href="{{ route('login') }}">Login</a></li>
                <li><a href="{{ route('register') }}">Register</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
    <div class="navbar-header navbar-default category-navbar hidden-xs hidden-sm">
        <ul class="nav nav-tabs nav-justified">
            @foreach($category as $cat)
                <li class="dropdown">
                    <a class="cat-name dropdown-toggle" href="{{ route('category',['cat' => str_slug($cat->category_name)]) }}">{{ ucwords($cat->category_name) }}</span></a>
                    <ul class="dropdown-menu sub-cat-menu">
                        @foreach($sub_category as $sub_cat)
                            @if($sub_cat->categories_id == $cat->categories_id)
                            <li>
                                <a class="subcat-name" href="{{ route('category',['cat' => str_slug($cat->category_name), 'topic' => str_slug($sub_cat->sub_category_name)]) }}">
                                    <i class="fa fa-code-fork"></i>&nbsp;&nbsp;
                                    {{ ucwords($sub_cat->sub_category_name) }}
                                </a>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </div>
</nav>