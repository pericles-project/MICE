<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>MICE - Model Impact Change Explorer</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="{{ elixir("css/all.css") }}">
  </head>
  <body class="page-header-fixed bg-1" ng-app="app">
    <div class="modal-shiftfix">
      <!-- Navigation -->
      <div class="navbar navbar-fixed-top scroll-hide">
        <div class="container-fluid top-bar">
          <!-- <div id="appHeading"> -->
          <div class="navbar-brand">Model Impact Change Explorer</div>
            <!-- <ul class="nav navbar-nav pull-right">
              <li><a ui-sref="app" class="pull-right">Case 1</a></li>
              <li><a ui-sref="case2" class="pull-right">Case 2</a></li>
              <li><a ui-sref="case3" class="pull-right">Case 3</a></li>
           </ul> -->
          <!-- </div> -->
        </div>
      </div>
      <div id="content">
        @yield('content')
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
    <script src="{{ elixir("js/all.js") }}"></script>
</body>
</html>
