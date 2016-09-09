$(document).ready(function(){
    var graph = Graph ();

    function getGraph(url, btnLoading) {
      $("#graph").html("");
      url = url ? url : "/graph";
      $('#graph-loading').show();
      if (btnLoading) {
           btnLoading.start();
      }
      
      $.get(APP_URL + url, function( data ) {
        graph.createGraph(data);
        $('#graph-loading').hide();
        if (btnLoading) {
            btnLoading.stop();
        }
      });
    }

    $(".updateGraph").click(function(e){
      e.preventDefault();
      $('.selected').removeClass('selected');
      $(this).closest('tr').addClass('selected');
      var l = Ladda.create(this);
       getGraph($(this).attr('href'), l);
    });

    $('#expandAllBtn').click(function(e){
      e.preventDefault();
      graph.expandAll();
    });

    $('#collapseAllBtn').click(function(e){
      e.preventDefault();
      graph.collapseAll();
    });

    getGraph();
});
