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
          scope.$watch('val', function(graphData, graphDataOld) {
            if (graphData === graphDataOld) {
              return;
            }

            var width = 960,
              height = 500,
              root;

            var nodes = graphData.nodes,
                links = graphData.links;

            var force = d3.layout.force()
                .linkDistance(80)
                .charge(-120)
                .gravity(.05)
                .size([width, height])
                .on("tick", tick);

            var svg = d3.select("body").append("svg")
                .attr("width", width)
                .attr("height", height);

            var link = svg.selectAll(".link"),
              node = svg.selectAll(".node");

            // root = graphData;
            update();

            function update() {
              // Restart the force layout.
              force
                  .nodes(nodes)
                  .links(links)
                  .start();

              // Update links.
              // link = link.data(links, function(d) { return d.target.id; });
              link = link.data(links);

              link.exit().remove();

              link.enter().insert("line", ".node")
                  .attr("class", "link");

              // Update nodes.
              node = node.data(nodes, function(d) { return d.id; });

              node.exit().remove();

              var nodeEnter = node.enter().append("g")
                  .attr("class", "node")
                  .on("click", click)
                  .call(force.drag);

              nodeEnter.append("circle")
                  .attr("r", function(d) { return Math.sqrt(d.size) / 10 || 4.5; });

              nodeEnter.append("text")
                  .attr("dy", ".35em")
                  .text(function(d) { return d.name; });

              node.select("circle")
                  .style("fill", color);
            }

            function tick() {
              link.attr("x1", function(d) { return d.source.x; })
                  .attr("y1", function(d) { return d.source.y; })
                  .attr("x2", function(d) { return d.target.x; })
                  .attr("y2", function(d) { return d.target.y; });

              node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
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
          });
        }
      }
    });
}());
