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

    $('#modal-accept-change-confirm').click(function(e){
      $.post( API_UPDATE_URL, {
        change: PARAMS.change,
        repository_name: PARAMS.repository_name
      })
      .done(function() {
        if (PARAMS.callback_url) {
            window.location.href = PARAMS.callback_url;
        } else {
          $('#update-success').removeClass('hidden');
          $('#update-failure').addClass('hidden');
        }
      })
      .fail(function() {
        if (PARAMS.callback_url) {
            window.location.href = PARAMS.callback_url;
        } else {
          $('#update-success').addClass('hidden');
          $('#update-failure').removeClass('hidden');
        }
      });
    });

    $('#modal-reject-change-confirm').click(function(e){
      $('#modal-reject-change').modal('hide');
    });
});
