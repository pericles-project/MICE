$(document).ready(function(){
    function getGraph(url, btnLoading) {
      $("#graph").html("");
      url = url ? url : "/graph";
      $('#graph-loading').show();
      if (btnLoading) {
           btnLoading.start();
      }
      $.get(url, function( data ) {
        var graph = Graph ();
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

    getGraph();
});
