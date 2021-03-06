<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>MICE - Model Impact Change Explorer</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="{{ url('') }}{{ elixir("css/all.css") }}">
  </head>
  <body class="page-header-fixed bg-1" ng-app="app">
    <div class="modal-shiftfix">
      <!-- Navigation -->
      <div class="navbar navbar-fixed-top scroll-hide">
        <div class="container-fluid top-bar">
          <!-- <div id="appHeading"> -->
          <div class="navbar-brand"><a href="{{ url('') }}" title="Model Impact Change Explorer"><img src="{{ url('') }}/images/mice_logo.png" alt="MICE - Model Impact Change Explorer" height="57" id="logo"/></a></div>
            <ul class="nav navbar-nav pull-right">
              <li><a href="https://github.com/pericles-project/MICE/wiki" target="_blank" class="pull-right">Quick Start Guide</a></li>
              <li class="dropdown hidden-xs">
                 <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    Test Cases<b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                  <li><a href="{{ url('') }}?case=1">
                    <i class="fa fa-arrow-circle-right"></i>Case 1</a>
                  </li>
                  <li><a href="{{ url('/') }}?case=2">
                    <i class="fa fa-arrow-circle-right"></i>Case 2</a>
                  </li>
                  <li><a href="{{ url('/') }}?case=3">
                    <i class="fa fa-arrow-circle-right"></i>Case 3</a>
                  </li>
                </ul>
              </li>
           </ul>
          <!-- </div> -->
        </div>
      </div>
      <div id="content">
        @yield('content')
      </div>

      <div id="footer" class="container-fluid main-content">
          <div class="row">
              <div class="col-md-12">
                  <p>
                      <em>This project has received funding from the European Union’s Seventh Framework Programme for research, technological development and demonstration under grant agreement no FP7- 601138 PERICLES.</em>
                  </p>
                  <p>
                      <a href="http://ec.europa.eu/research/fp7" target="_blank"><img src="{{ url('') }}/images/LogoEU.png" height="60" style="margin-right:20px"></a>
                      <a href="http://www.pericles-project.eu/" target="_blank"> <img src="{{ url('') }}/images/PERICLES_logo_black.jpg" height="60"> </a>
                  </p>
              </div>
          </div>
        </div>
    </div>
    <!-- Google Analytics: change UA-XXXXX-X to be your site's ID -->
     <script>
       !function(A,n,g,u,l,a,r){A.GoogleAnalyticsObject=l,A[l]=A[l]||function(){
       (A[l].q=A[l].q||[]).push(arguments)},A[l].l=+new Date,a=n.createElement(g),
       r=n.getElementsByTagName(g)[0],a.src=u,r.parentNode.insertBefore(a,r)
       }(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

       ga('create', 'UA-XXXXX-X');
       ga('send', 'pageview');
    </script>
    <script type="text/javascript">
        var APP_URL = "{!! url('/') !!}";
        var API_UPDATE_URL = "{!! env('API_UPDATE_URL') !!}";
        var PARAMS = {!! json_encode($params) !!};
    </script>
    <script src="{{ url('') }}{{ elixir("js/all.js") }}"></script>
</body>
</html>
