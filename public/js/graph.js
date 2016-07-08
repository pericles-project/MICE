(function () {
  $(document).ready(function(){
      function getGraph(url) {
          $("#graph").html("");
          url = url ? url : "/graph";
          $.get(url, function( data ) {
            createGraph(data);
          });
      }

      $(".updateGraph").click(function(e){
        e.preventDefault();
        getGraph($(this).attr('href'));
      });

      getGraph();
  });

  function createGraph(data) {
    var width = 900,
        height = 800,
        root,
        nodes,
        links,
        total_resources = [],
        impacted_resources = [],
        not_impacted_resources = [];

    var force = d3.layout.force()
        .linkDistance(120)
        .charge([-500])
        .theta(0.1)
        .gravity(0.05)
        .size([width, height])
        .on("tick", tick);

    var svg = d3.select("#graph").append("svg")
       .attr("width", '100%')
       .attr("height", '100%');
   var realWidth = $("svg").parent().width();
   svg.attr('viewBox','0 0 '+Math.min(realWidth,realWidth)+' '+Math.min(realWidth,realWidth))
       .attr('preserveAspectRatio','xMinYMin')
       .attr("transform", "translate(" + Math.min(realWidth,realWidth) / 2 + "," + Math.min(realWidth,realWidth) / 2 + ")");


    var tip = d3.tip()
      .attr('class', 'd3-tip')
      .offset([-10, 0])
      .html(function(d) {
        var allowedProperties = ["name", "type", "dependencyType", "intention", "value", "change", "impacted", "reason"];
        var mapProperties = ["Name", "Type", "Dependency type", "Intention", "Value", "Change", "Status", "Reason"];
        var body = '', value;
        for(var propt in d){
          var index = allowedProperties.indexOf(propt);
          if (index >= 0) {
            if (propt == 'dependencyType') {
              value = d[propt] + " dependency (" + (d[propt] == 'Conjunctive' ? "ALL" : "ANY") + " of the 'from' requirements must be consistent" + ")";
            } else if (propt == 'impacted') {
              value = d[propt] ? "Impacted" : "Not impacted";
            } else {
              value = d[propt];
            }

            body += '<p>' + mapProperties[index] + ': ' + value + '</p>'
          }
        }
        return body;
      });
    svg.call(tip);

    var link = svg.selectAll(".link"),
        node = svg.selectAll(".node"),
        linkline = svg.selectAll(".linkline"),
        linkpath = svg.selectAll(".linkpath"),
        linklabel = svg.selectAll(".linklabel");

    // build the arrow.
    var defs = svg.append('defs');
    defs.append('marker')
        .attr({'id':'arrowhead',
               'viewBox':'-0 -5 10 10',
               'refX':20,
               'refY':0,
               //'markerUnits':'strokeWidth',
               'orient':'auto',
               'markerWidth':10,
               'markerHeight':10,
               'xoverflow':'visible'})
        .append('svg:path')
            .attr('d', 'M 0,-5 L 10 ,0 L 0,5')
            .attr('fill', '#ccc')
            .attr('stroke','#ccc');

    defs.append('marker')
        .attr({'id':'arrowhead-dark',
               'viewBox':'-0 -5 10 10',
               'refX':20,
               'refY':0,
               //'markerUnits':'strokeWidth',
               'orient':'auto',
               'markerWidth':10,
               'markerHeight':10,
               'xoverflow':'visible'})
        .append('svg:path')
            .attr('d', 'M 0,-5 L 10 ,0 L 0,5')
            .attr('fill', '#000')
            .attr('stroke','#000');

    function update() {
      nodes = flatten(root);
      links = d3.layout.tree().links(nodes);

      // Restart the force layout.
      force
          .nodes(nodes)
          .links(links)
          .start();

      link = link.data(links, function(d) {return d.source.id + "_" + d.target.id; });
      link.exit().remove();
      var linkEnter = link.enter().insert("g")
        .attr("class", "link");
      var linkline = linkEnter.append("line")
        .attr('marker-end', function(d) {return d.target.children || d.target._children || d.target.isTerminal ? "url(#arrowhead-dark)" : 'url(#arrowhead)';})
        .attr("class", "linkline")
        .style("stroke", function(d) {console.info(d); return d.target.children || d.target._children || d.target.isTerminal ? "#000" : "#ccc";})
        // .style("stroke-dasharray", function(d) {return d.target.children || d.target.isTerminal ? ("0, 0") : ("3", "3");})
        .style("pointer-events", "none");

      var linkpath = linkEnter.append('path')
          .attr({'d': function(d) {return 'M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y},
                 'class':'linkpath',
                 'id':function(d,i) {return 'linkpath'+ d.source.id + "_" + d.target.id}})
          .style("pointer-events", "none");

      var linklabel = linkEnter.append('text')
          .style("pointer-events", "none")
          .attr({'class':'linklabel',
                 'dx':60,
                 'dy':0,
                 'font-size':10,
                 'fill':function(d){return d.target.children || d.target._children || d.target.isTerminal ? "#000" : "#ccc";}})
          .append('textPath')
            .attr('xlink:href',function(d,i) {return '#linkpath'+ d.source.id + "_" + d.target.id})
            .style("pointer-events", "none")
            .text(function(d,i){return d.target.link.label;});

      // Update nodes.
      node = node.data(nodes, function(d) {return d.id; });
      node.exit().remove();

      var nodeEnter = node.enter().append("g")
          .attr("class", function(d){return "node" + (d.children || d._children ? "" : " terminal") + (d.type == "Resource" ? " resource" : " dependency")})
          .on("click", function(d){
              click(d);
              tip.hide(d);
          })
          .on('mouseover', tip.show)
          .on('mouseout', tip.hide)
          .call(force.drag);
      nodeEnter.append("path")
          .attr("d", d3.svg.symbol()
            .size(function(d) { return (d.type == 'Dependency' ? 30 : 8) * 40; })
            .type(function(d) { return d.type == 'Dependency' ? "diamond" : "circle";}))
            .style("stroke", function(d) {return d.children || d._children ? "none" : "#444";})
            .style("stroke-width", function(d) {return d.children || d._children ? "none" : "2px";})
          // .attr("r", function(d) { return d.type == 'Dependency' ? 4.5 : 10; })
          .style("fill", color);
      nodeEnter.append("text")
          .attr("class", function(d) { return d.type == 'Dependency' ? "diamond" : "circle"; })
          .attr("dx", function(d) { return d.type == 'Dependency' ? "-5em" : "1em"; })
          .attr("dy", ".35em")
          .text(function(d) { return d.name; });
    }

    function tick() {
      // Custom function that swaps the link's source and target. This is needed in order to change the direction of the arrows for nodes of type dependency.
      function getPositionFrom(d, type) {
        var posFrom;
        if ((d.source.link && d.source.link.direction == 'self') ||
          d.target.link && d.target.link.direction == 'parent') {
          type = (type == 'source') ? 'target' : 'source';
        }
        return type;
      }

      link.select('.linkline').attr({
        "x1": function(d){var type=getPositionFrom(d,'source'); return d[type].x;},
        "y1": function(d){var type=getPositionFrom(d,'source'); return d[type].y;},
        "x2": function(d){var type=getPositionFrom(d,'target'); return d[type].x;},
        "y2": function(d){var type=getPositionFrom(d,'target'); return d[type].y;}
      });

      link.select('.linkpath').attr('d', function(d) {
        var path='M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y;
        return path;
      });

      link.select('.linklabel').attr('transform',function(d,i){
          if (d.target.x<d.source.x){
              var bbox = this.getBBox();
              var rx = bbox.x+bbox.width/2;
              var ry = bbox.y+bbox.height/2;
              return 'rotate(180 '+rx+' '+ry+')';
              }
          else {
              return 'rotate(0)';
              }
      });

      //if (d.isRoot) {d.x=480; d.y=50;};
      node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
    }

    function color(d) {
      // return d._children ? "#3182bd" // collapsed package
      //     : d.children ? "#c6dbef" // expanded package
      //     : "#fd8d3c"; // leaf node
      var color = '#c6dbef';
      if (d.type == 'Dependency') {
        color = d.dependencyType == 'Disjunctive' ? '#6baed6' : '#9e9ac8';
      } else {
        if (d.impacted === true) {
          color = '#e6550d';
        } else if (d.impacted === false) {
          color = '#74c476';
        } else {
          if (d._children) {
            color = '#3182bd';
          }
        }
      }
      return color;
    }

    // Toggle children on click.
    function click(d) {
      if (d3.event && d3.event.defaultPrevented) return; // ignore drag
      if (d.type != 'Dependency') {
        toggleChildren(d);
      }
      if (d.children) {
        d.children.forEach(function(c){
          if (c.type == 'Dependency' && !c.children) {
            toggleChildren(c);
          }
        });
      }
      update();
    }

    function toggleChildren(d) {
      if (d.children) {
        d._children = d.children;
        d.children = null;
      } else {
        d.children = d._children;
        d._children = null;
      }
    }

    // Returns a list of all nodes under the root.
    function flatten(root) {
      var nodes = [], i = 0;

      function recurse(node) {
        if (node.children) node.children.forEach(recurse);
        if (!node.id) node.id = ++i;
        nodes.push(node);
      }

      recurse(root);
      return nodes;
    }

    // Initialize
    root = data;
    root.isRoot = true;
    root.fixed = true;
    root.x = width / 2;
    root.y = 20;

    update();

    // Find counts of total, impacted, not impacted nodes.
    nodes.forEach(function(d){
        if (d.type == 'Resource') {
            total_resources.push(d);
            if (d.impacted == true) {
                impacted_resources.push(d);
            } else if (d.impacted == false) {
                not_impacted_resources.push(d);
            }
        }
    });
    $('#totalResourcesCount').html(total_resources.length);
    $('#impactedResourcesCount').html(impacted_resources.length);
    $('#notImpactedResourcesCount').html(not_impacted_resources.length);

    // Make all nodes collapsed by default. This is needed due to possible bug in d3 force layout.
    nodes.forEach(function(d){
      if (d.children) {
        d._children = d.children;
        d.children = null;
      }
    });
    click(root);
    // update();
  }
})();
