(function() {
  'use strict';

  angular.module('app')
    .directive('graphVisualization', function() {
      return {
        restrict: 'E',
        scope: {
          val: '='
        },
        link: function(scope, element, attrs) {
          var width = 960,
              height = 500,
              root;

          var force = d3.layout.force()
              .linkDistance(150)
              .charge([-500])
              .theta(0.1)
              .gravity(0.05)
              .size([width, height])
              .on("tick", tick);

          var svg = d3.select("body").append("svg")
              .attr("width", width)
              .attr("height", height);

          var link = svg.selectAll(".link"),
              node = svg.selectAll(".node"),
              linkpath = svg.selectAll(".linkpath"),
              linklabel = svg.selectAll(".linklabel");

          // build the arrow.
          svg.append('defs').append('marker')
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

          function update() {

            var nodes = flatten(root),
                links = d3.layout.tree().links(nodes);

            // for (var l in links) {
            //   if (link.direction == 'self') {
            //     d._target = d.target;
            //     d.target = d.source;
            //     d.source = d._target;
            //   }
            // }

            // Restart the force layout.
            force
                .nodes(nodes)
                .links(links)
                .start();

            link = link.data(links, function(d) { console.info('linkdata'+d.target.id);return d.target.id; });

            link.exit().remove();

            link.enter().append("line")
              .attr("id",function(d,i) {console.info('link'+i);return 'link'+i})
              .attr('marker-end','url(#arrowhead)')
              .attr("class", "link")
              .style("stroke","#ccc")
              .style("pointer-events", "none");
console.info('nodes');
            // Update nodes.
            node = node.data(nodes, function(d) { console.info('node'+d);return d.id; });

            node.exit().remove();

            var nodeEnter = node.enter().append("g")
                .attr("class", "node")
                .on("click", click)
                .call(force.drag);

            nodeEnter.append("circle")
                .attr("r", function(d) { return d.type == 'dependency' ? 4.5 : 8; });

            nodeEnter.append("text")
                .attr("dy", ".35em")
                .text(function(d) { return d.name; });

            node.select("circle")
                .style("fill", color);

            linkpath = linkpath.data(links, function(d) { console.info('path'+d.target.id);return d.target.id; });

            linkpath.exit().remove();

            // TODO
            linkpath.enter()
                .append('path')
                .attr({'d': function(d) {return 'M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y},
                       'class':'linkpath',
                       'fill-opacity':0,
                       'stroke-opacity':0,
                       'fill':'blue',
                       'stroke':'red',
                       'id':function(d,i) {console.info('linkpath'+i);return 'linkpath'+i}})
                .style("pointer-events", "none");

            linklabel = linklabel.data(links);

            linklabel.exit().remove();

            linklabel.enter()
                .append('text')
                .style("pointer-events", "none")
                .attr({'class':'linklabel',
                       'id':function(d,i){return 'linklabel'+i},
                       'dx':80,
                       'dy':0,
                       'font-size':10,
                       'fill':'#aaa'});

            linklabel.append('textPath')
                .attr('xlink:href',function(d,i) {return '#linkpath'+i})
                .style("pointer-events", "none")
                .text(function(d,i){return "label" + i});
          }

          function tick() {
            link.attr({"x1": function(d){return d.source.x;},
                    "y1": function(d){return d.source.y;},
                    "x2": function(d){return d.target.x;},
                    "y2": function(d){return d.target.y;}
            });

            node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

            linkpath.attr('d', function(d) {
              var path='M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y;
              return path;
            });

            linklabel.attr('transform',function(d,i){
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
          }

          function color(d) {
            return d._children ? "#3182bd" // collapsed package
                : d.children ? "#c6dbef" // expanded package
                : "#fd8d3c"; // leaf node
          }

          // Toggle children on click.
          function click(d) {
            if (d3.event.defaultPrevented) return; // ignore drag
            if (d.children) {
              d._children = d.children;
              d.children = null;
            } else {
              d.children = d._children;
              d._children = null;
            }
            update();
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

          scope.$watch('val', function(graphData, graphDataOld) {
            if (graphData === graphDataOld) {
              return;
            }

            root = graphData;
            update();
          });
        }
      }
    });
}());
