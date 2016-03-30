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
          var width = 700,
              height = 800,
              root;

          var force = d3.layout.force()
              .linkDistance(120)
              .charge([-500])
              .theta(0.1)
              .gravity(0.05)
              .size([width, height])
              .on("tick", tick);

          var svg = d3.select("#content").append("svg")
              .attr("width", width)
              .attr("height", height);

          var tip = d3.tip()
            .attr('class', 'd3-tip')
            .offset([-10, 0])
            .html(function(d) {
              var allowedProperties = ["name", "type", "dependencyType", "intention", "value"];
              var body = '';
              for(var propt in d){
                if (allowedProperties.indexOf(propt) >= 0) {
                  body += '<p>' + propt + ': ' + d[propt] + '</p>'
                }
              }
              return body;
            });
          svg.call(tip);

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


            // Restart the force layout.
            force
                .nodes(nodes)
                .links(links)
                .start();

            link = link.data(links, function(d) {return d.target.id; });
            link.exit().remove();
            link.enter().append("line")
              .attr("id",function(d,i) {return 'link'+i})
              .attr('marker-end','url(#arrowhead)')
              .attr("class", "link")
              .style("stroke", "#ccc")
              .style("pointer-events", "none");

            // Update nodes.
            node = node.data(nodes, function(d) {return d.id; });
            node.exit().remove();

            var nodeEnter = node.enter().append("g")
                .attr("class", "node")
                .on("click", function(d){
                    click(d);
                    tip.hide(d);
                })
                .on('mouseover', tip.show)
                .on('mouseout', tip.hide)
                .call(force.drag);
            nodeEnter.append("circle")
                .attr("r", function(d) { return d.type == 'dependency' ? 4.5 : 10; });
            nodeEnter.append("text")
                .attr("dx", "1em")
                .attr("dy", ".35em")
                .text(function(d) { return d.name; });

            node.select("circle")
                // .style("stroke", function(d){return d.isRoot ? 'black' : 'steelblue';})
                .style("fill", color);

            linkpath = linkpath.data(links);
            linkpath.exit().remove();
            linkpath.enter()
                .append('path')
                .attr({'d': function(d) {return 'M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y},
                       'class':'linkpath',
                       'fill-opacity':0,
                       'stroke-opacity':0,
                       'fill':'blue',
                       'stroke':'red',
                       'id':function(d,i) {return 'linkpath'+i}})
                .style("pointer-events", "none");

            linklabel = linklabel.data(links);
            linklabel.exit().remove();
            linklabel.enter()
                .append('text')
                .style("pointer-events", "none")
                .attr({'class':'linklabel',
                       'id':function(d,i){return 'linklabel'+i},
                       'dx':60,
                       'dy':0,
                       'font-size':10,
                       'fill':'#aaa'})
                .append('textPath')
                .attr('xlink:href',function(d,i) {return '#linkpath'+i})
                .style("pointer-events", "none")
                .text(function(d,i){return d.target.link.label;});
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
            link.attr({"x1": function(d){var type=getPositionFrom(d,'source'); return d[type].x;},
                    "y1": function(d){var type=getPositionFrom(d,'source'); return d[type].y;},
                    "x2": function(d){var type=getPositionFrom(d,'target'); return d[type].x;},
                    "y2": function(d){var type=getPositionFrom(d,'target'); return d[type].y;}
            });

            //if (d.isRoot) {d.x=480; d.y=50;};
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
            // return d._children ? "#3182bd" // collapsed package
            //     : d.children ? "#c6dbef" // expanded package
            //     : "#fd8d3c"; // leaf node
            return d.impacted ? "red" : d.impacted === false ? "green" : (d._children ? "#3182bd" // collapsed package
                : "#c6dbef"); // expanded package
          }

          // Toggle children on click.
          function click(d) {
            if (d3.event.defaultPrevented) return; // ignore drag
            toggleChildren(d);
            if (d.children) {
              d.children.forEach(function(c){
                if (c.type == 'dependency') {
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

          scope.$watch('val', function(graphData, graphDataOld) {
            if (graphData === graphDataOld) {
              return;
            }

            root = graphData;
            root.isRoot = true;
            root.fixed = true;
            root.x = width / 2;
            root.y = 50;

            function collapse(d) {
              if (d.children) {
                d._children = d.children;
                d._children.forEach(collapse);
                d.children = null;
              }
            }

            // root.children.forEach(function(c){
            //   collapse(c);
            //   if (c.type == 'dependency') {
            //     toggleChildren(c);
            //   }
            // });
            update();
          });
        }
      }
    });
}());
