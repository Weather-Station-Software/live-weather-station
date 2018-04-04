function Windrose() {

    // options which should be accessible via ACCESSORS
    var data = [];
    var options = {
        margins: {top: 40, right: 80, bottom: 40, left: 40},
        width: window.innerWidth,
        widthMax: window.innerWidth,
        height: window.innerHeight,
        heightMax: window.innerHeight,
        innerRadius: 20,
        resize: false,
        scale: 'linear',
        classed: "lws",
        color: null,
        valFormat: '%'
    }

    // nodes layered such that radarInvisibleCircles always on top of radarAreas
    // and tooltip layer is at topmost layer
    var chart_node;           // parent node for this instance of radarChart
    var hover_node;           // parent node for invisibleRadarCircles
    var tooltip_node;         // parent node for tooltip, to keep on top
    var legend_node;          // parent node for tooltip, to keep on top

    // DEFINABLE EVENTS
    // Define with ACCESSOR function chart.events()
    /*var events = {
        'update': { 'begin': null, 'end': null },
        'gridCircle': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'axisLabel': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'line': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'legend': { 'mouseover': legendMouseover, 'mouseout': areaMouseout, 'mouseclick': legendClick },
        'axisLegend': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'radarArea': { 'mouseover': areaMouseover, 'mouseout': areaMouseout, 'mouseclick': null },
        'radarInvisibleCircle': { 'mouseover': tooltip_show, 'mouseout': tooltip_hide, 'mouseclick': null }
    };*/

    // functions which should be accessible via ACCESSORS
    var update;

    // helper functions
    var tooltip;


    // programmatic
    var _data = [];
    var chartWidth = options.width - options.margins.left - options.margins.right;
    var chartHeight = options.height - options.margins.top - options.margins.bottom;
    var outerRadius = (Math.min(chartWidth, chartHeight)/2);
    var angle = d4.scaleLinear().range([0, 2 * Math.PI]);
    var radius = d4.scaleLinear().range([options.innerRadius, outerRadius]);
    var x = d4.scaleBand().range([0, 2 * Math.PI]).align(0);
    var y = d4.scaleLinear().range([options.innerRadius, outerRadius]);
    var z = d3.scaleOrdinal().range(["#4242f4", "#42c5f4", "#42f4ce", "#42f456", "#adf442", "#f4e242", "#f4a142", "#f44242"]);
    var dom_parent;
    var Format = d4.format(options.valFormat);
    var transition_time = 0;


    var legend_toggles = [];
    var radial_calcs = {};
    var delay = 0;
    var keys;
    var keyScale;
    var colorScale;


    if (options.scale != 'linear') {
        y = d4.scaleRadial().range([options.innerRadius, outerRadius]);
    }

    function chart(selection) {
        selection.each(function () {
            dom_parent = d4.select(this);
            scaleChart();

            //////////// Create the container SVG and children g /////////////
            var svg = dom_parent.append('svg')
                .attr('overflow', 'visible')
                .attr('width', options.width)
                .attr('height', options.height);


            //g = svg.append("g").attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");


            // append parent g for chart
            chart_node = svg.append('g').attr('class', options.classed + 'WindroseNode');
            hover_node = svg.append('g').attr('class', options.classed + 'HoverNode');
            tooltip_node = svg.append('g').attr('class', options.classed + 'TooltipNode');
            legend_node = svg.append("g").attr("class", options.classed + "Legend");




            // Wrapper for the grid & axes
            //var axisGrid = chart_node.append("g").attr("class", options.classed + "AxisWrapper");



            tooltip = tooltip_node.append('foreignObject')
                .attr('class', options.classed + 'Tooltip')
                .style("opacity", 0)
                .style("padding-top", '10px')
                .style("padding-left", '20px')
                .style("width", "200")
                .style("height", "200");

            // update
            update = function() {

                var duration = transition_time;

                Format = d4.format(options.valFormat);





/*
                keys = _data.map(function(m) { return m.key; });
                keyScale = d4.scale.ordinal()
                    .domain(_data.map(function(m) { return m._i; }))
                    .range(_data.map(function(m) { return m.key; }));
                colorScale = d4.scale.ordinal()
                    .domain(_data.map(function(m) {
                        return options.areas.colors[keyScale(m._i)] ?
                            keyScale(m._i)
                            : m._i.toString();
                    }))
                    .range(_data.map(function(m) { return setColor(m); }));

                svg.transition().delay(delay).duration(duration)
                    .attr('width', options.width)
                    .attr('height', options.height)

                chart_node.transition().delay(delay).duration(duration)
                    .attr('width', options.width)
                    .attr('height', options.height)
                    .attr("transform",
                        "translate(" + ((options.width - (options.margins.left + options.margins.right)) / 2 + options.margins.left) + ","
                        + ((options.height - (options.margins.top + options.margins.bottom)) / 2 + options.margins.top) + ")")
                hover_node.transition().delay(delay).duration(duration)
                    .attr('width', options.width)
                    .attr('height', options.height)
                    .attr("transform",
                        "translate(" + ((options.width - (options.margins.left + options.margins.right)) / 2 + options.margins.left) + ","
                        + ((options.height - (options.margins.top + options.margins.bottom)) / 2 + options.margins.top) + ")")
                tooltip_node.transition().delay(delay).duration(duration)
                    .attr('width', options.width)
                    .attr('height', options.height)
                    .attr("transform",
                        "translate(" + ((options.width - (options.margins.left + options.margins.right)) / 2 + options.margins.left) + ","
                        + ((options.height - (options.margins.top + options.margins.bottom)) / 2 + options.margins.top) + ")")

                legend_node
                    .attr("transform", "translate(" + options.legend.position.x + "," + options.legend.position.y + ")");

                var update_gridCircles = axisGrid.selectAll("." + options.classed + "GridCircle")
                    .data(d4.range(1, (options.circles.levels + 1)).reverse())

                update_gridCircles
                    .transition().duration(duration)
                    .attr("r", function(d, i) { return radial_calcs.radius / options.circles.levels * d; })
                    .style("fill", options.circles.fill)
                    .style("fill-opacity", options.circles.opacity)
                    .style("stroke", options.circles.color)
                    .style("filter" , function() { if (options.filter) return "url(#" + options.filter + ")" });

                update_gridCircles.enter()
                    .append("circle")
                    .attr("class", options.classed + "GridCircle")
                    .attr("r", function(d, i) { return radial_calcs.radius / options.circles.levels * d; })
                    .on('mouseover', function(d, i) { if (events.gridCircle.mouseover) events.gridCircle.mouseover(d, i); })
                    .on('mouseout', function(d, i) { if (events.gridCircle.mouseout) events.gridCircle.mouseout(d, i); })
                    .style("fill", options.circles.fill)
                    .style("fill-opacity", options.circles.opacity)
                    .style("stroke", options.circles.color)
                    .style("filter" , function() { if (options.filter) return "url(#" + options.filter + ")" });

                update_gridCircles.exit()
                    .transition().duration(duration * .5)
                    .delay(function(d, i) { return 0; })
                    .remove();

                var update_axisLabels = axisGrid.selectAll("." + options.classed + "AxisLabel")
                    .data(d4.range(1, (options.circles.levels + 1)).reverse())

                update_axisLabels
                    .transition().duration(duration / 2)
                    .style('opacity', 1) // don't change to 0 if there has been no change in dimensions! possible??
                    .transition().duration(duration / 2)
                    .text(function(d, i) { if (radial_calcs.maxValue) return Format(options.correctAdd + (options.correctMul * radial_calcs.maxValue * d / options.circles.levels)) + options.valUnit; })
                    .attr("y", function(d) { return -d * radial_calcs.radius / options.circles.levels; })
                    .style('opacity', 1)

                update_axisLabels.enter()
                    .append("text")
                    .attr("class", options.classed + "AxisLabel")
                    .attr("x", 4)
                    .attr("y", function(d) { return -d * radial_calcs.radius / options.circles.levels; })
                    .attr("dy", "0.4em")
                    .style("font-size", "10px")
                    .attr("fill", "#737373")
                    .on('mouseover', function(d, i) { if (events.axisLabel.mouseover) events.axisLabel.mouseover(d, i); })
                    .on('mouseout', function(d, i) { if (events.axisLabel.mouseout) events.axisLabel.mouseout(d, i); })
                    .text(function(d, i) { if (radial_calcs.maxValue) return Format(options.correctAdd + (options.correctMul * radial_calcs.maxValue * d / options.circles.levels)) + options.valUnit; });

                update_axisLabels.exit()
                    .transition().duration(duration * .5)
                    .remove();

                var update_axes = axisGrid.selectAll("." + options.classed + "Axis")
                    .data(radial_calcs.axes, get_axis)

                update_axes
                    .enter().append("g")
                    .attr("class", options.classed + "Axis")
                    .attr("key", function(d) { return d.axis; });

                update_axes.exit()
                    .transition().duration(duration)
                    .style('opacity', 0)
                    .remove()

                var update_lines = update_axes.selectAll("." + options.classed + "Line")
                    .data(function(d) { return [d]; }, get_axis)

                update_lines.enter()
                    .append("line")
                    .attr("class", options.classed + "Line")
                    .attr("x1", 0)
                    .attr("y1", 0)
                    .attr("x2", function(d, i, j) { return calcX(null, 1.1, j); })
                    .attr("y2", function(d, i, j) { return calcY(null, 1.1, j); })
                    .on('mouseover', function(d, i, j) { if (events.line.mouseover) events.line.mouseover(d, j); })
                    .on('mouseout', function(d, i, j) { if (events.line.mouseout) events.line.mouseout(d, j); })
                    .style("stroke", options.axes.lineColor)
                    .style("stroke-width", "2px")

                update_lines.exit()
                    .transition().duration(duration * .5)
                    .delay(function(d, i) { return 0; })
                    .remove();

                update_lines
                    .transition().duration(duration)
                    .style("stroke", options.axes.lineColor)
                    .style("stroke-width", options.axes.lineWidth)
                    .attr("x2", function(d, i, j) { return calcX(null, 1.1, j); })
                    .attr("y2", function(d, i, j) { return calcY(null, 1.1, j); })

                var update_axis_legends = update_axes.selectAll("." + options.classed + "AxisLegend")
                    .data(function(d) { return [d]; }, get_axis)

                update_axis_legends.enter()
                    .append("text")
                    .attr("class", options.classed + "AxisLegend")
                    .style("font-size", options.axes.fontWidth)
                    .attr("text-anchor", "middle")
                    .attr("dy", "0.35em")
                    .attr("x", function(d, i, j) { return calcX(null, options.circles.labelFactor, j); })
                    .attr("y", function(d, i, j) { return calcY(null, options.circles.labelFactor, j); })
                    .style('opacity', function(d, i) { return options.axes.display ? 1 : 0})
                    .on('mouseover', function(d, i, j) { if (events.axisLegend.mouseover) events.axisLegend.mouseover(d, i, j); })
                    .on('mouseout', function(d, i, j) { if (events.axisLegend.mouseout) events.axisLegend.mouseout(d, i, j); })
                    .call(wrap, options.axes.wrapWidth)

                update_axis_legends.exit()
                    .transition().duration(duration * .5)
                    .delay(function(d, i) { return 0; })
                    .remove();

                update_axis_legends
                    .transition().duration(duration)
                    .style('opacity', function(d, i) {
                        return options.axes.display && radial_calcs.radius > options.axes.threshold ? 1 : 0
                    })
                    .attr("x", function(d, i, j) { return calcX(null, options.circles.labelFactor, j); })
                    .attr("y", function(d, i, j) { return calcY(null, options.circles.labelFactor, j); })
                    .selectAll('tspan')
                    .attr("x", function(d, i, j) { return calcX(null, options.circles.labelFactor, j); })
                    .attr("y", function(d, i, j) { return calcY(null, options.circles.labelFactor, j); })

                var radarLine = d4.svg.line.radial()
                    .interpolate( options.areas.rounded ?
                        "cardinal-closed" :
                        "linear-closed" )
                    .radius(function(d) { return radial_calcs.rScale(d.value); })
                    .angle(function(d,i) { return i * radial_calcs.angleSlice; });

                var update_blobWrapper = chart_node.selectAll("." + options.classed + "RadarWrapper")
                    .data(_data, get_key)

                update_blobWrapper.enter()
                    .append("g")
                    .attr("class", options.classed + "RadarWrapper")
                    .attr("key", function(d) { return d.key; });

                update_blobWrapper.exit()
                    .transition().duration(duration)
                    .style('opacity', 0)
                    .remove()

                update_blobWrapper
                    .style("fill-opacity", function(d, i) {
                        return options.areas.filter.indexOf(d.key) >= 0 ? 0 : options.areas.opacity;
                    })

                var update_radarArea = update_blobWrapper.selectAll('.' + options.classed + 'RadarArea')
                    .data(function(d) { return [d]; }, get_key);

                update_radarArea.enter()
                    .append("path")
                    .attr("class", function(d) { return options.classed + "RadarArea " + d.key.replace(/\s+/g, '') })
                    .attr("d", function(d, i) { return radarLine(d.values); })
                    .style("fill", function(d, i, j) { return setColor(d); })
                    .style("fill-opacity", 0)
                    .on('mouseover', function(d, i) { if (events.radarArea.mouseover) events.radarArea.mouseover(d, i, this); })
                    .on('mouseout', function(d, i) { if (events.radarArea.mouseout) events.radarArea.mouseout(d, i, this); })

                update_radarArea.exit().remove()

                update_radarArea
                    .transition().duration(duration)
                    .style("fill", function(d, i, j) { return setColor(d); })
                    .attr("d", function(d, i) { return radarLine(d.values); })
                    .style("fill-opacity", function(d, i) {
                        return options.areas.filter.indexOf(d.key) >= 0 ? 0 : options.areas.opacity;
                    })

                var update_radarStroke = update_blobWrapper.selectAll('.' + options.classed + 'RadarStroke')
                    .data(function(d) { return [d]; }, get_key);

                update_radarStroke.enter()
                    .append("path")
                    .attr("class", options.classed + "RadarStroke")
                    .attr("d", function(d, i) { return radarLine(d.values); })
                    .style("opacity", 0)
                    .style("stroke-width", options.areas.borderWidth + "px")
                    .style("stroke", function(d, i, j) { return setColor(d); })
                    .style("fill", "none")
                    .style("filter" , function() { if (options.filter) return "url(#" + options.filter + ")" });

                update_radarStroke.exit().remove();

                update_radarStroke
                    .transition().duration(duration)
                    .style("stroke", function(d, i, j) { return setColor(d); })
                    .attr("d", function(d, i) { return radarLine(d.values); })
                    .style("filter" , function() { if (options.filter) return "url(#" + options.filter + ")" })
                    .style("opacity", function(d, i) {
                        return options.areas.filter.indexOf(d.key) >= 0 ? 0 : 1;
                    });

                update_radarCircle = update_blobWrapper.selectAll('.' + options.classed + 'RadarCircle')
                    .data(function(d, i) { return add_index(d._i, d.key, d.values) });

                update_radarCircle.enter()
                    .append("circle")
                    .attr("class", options.classed + "RadarCircle")
                    .attr("r", options.areas.dotRadius)
                    .attr("cx", function(d, i, j){ return calcX(0, 0, i); })
                    .attr("cy", function(d, i, j){ return calcY(0, 0, i); })
                    .style("fill", function(d, i, j) { return setColor(d, d._i, _data[j].key); })
                    .style("fill-opacity", function(d, i) { return 0; })
                    .transition().duration(duration)
                    .attr("cx", function(d, i, j){ return calcX(d.value, 0, i); })
                    .attr("cy", function(d, i, j){ return calcY(d.value, 0, i); })

                update_radarCircle.exit().remove();

                update_radarCircle
                    .transition().duration(duration)
                    .style("fill", function(d, i, j) { return setColor(d, d._i, _data[j].key); })
                    .style("fill-opacity", function(d, i, j) {
                        var key = _data.map(function(m) {return m.key})[j];
                        return options.areas.filter.indexOf(key) >= 0 ? 0 : 0.8;
                    })
                    .attr("r", options.areas.dotRadius)
                    .attr("cx", function(d, i){ return calcX(d.value, 0, i); })
                    .attr("cy", function(d, i){ return calcY(d.value, 0, i); })

                var update_blobCircleWrapper = hover_node.selectAll("." + options.classed + "RadarCircleWrapper")
                    .data(_data, get_key)

                update_blobCircleWrapper.enter()
                    .append("g")
                    .attr("class", options.classed + "RadarCircleWrapper")
                    .attr("key", function(d) { return d.key; });

                update_blobCircleWrapper.exit()
                    .transition().duration(duration)
                    .style('opacity', 0)
                    .remove()

                update_radarInvisibleCircle = update_blobCircleWrapper.selectAll("." + options.classed + "RadarInvisibleCircle")
                    .data(function(d, i) { return add_index(d._i, d.key, d.values); });

                update_radarInvisibleCircle.enter()
                    .append("circle")
                    .attr("class", options.classed + "RadarInvisibleCircle")
                    .attr("r", options.areas.dotRadius * 1.5)
                    .attr("cx", function(d, i){ return calcX(d.value, 0, i); })
                    .attr("cy", function(d, i){ return calcY(d.value, 0, i); })
                    .style("fill", "none")
                    .style("pointer-events", "all")
                    .on('mouseover', function(d, i) {
                        if (events.radarInvisibleCircle.mouseover) events.radarInvisibleCircle.mouseover(d, i, this);
                    })
                    .on("mouseout", function(d, i) {
                        if (events.radarInvisibleCircle.mouseout) events.radarInvisibleCircle.mouseout(d, i, this);
                    })

                update_radarInvisibleCircle.exit().remove();

                update_radarInvisibleCircle
                    .attr("cx", function(d, i){ return calcX(d.value, 0, i); })
                    .attr("cy", function(d, i){ return calcY(d.value, 0, i); })

                if (options.legend.display) {
                    var shape = d4.svg.symbol().type(options.legend.symbol).size(150)();
                    var colorScale = d4.scale.ordinal()
                        .domain(_data.map(function(m) { return m._i; }))
                        .range(_data.map(function(m) { return setColor(m); }));

                    if (d4.legend) {
                        var legendOrdinal = d4.legend.color()
                            .shape("path", shape)
                            .shapePadding(10)
                            .scale(colorScale)
                            .labels(colorScale.domain().map(function(m) { return keyScale(m); } ))
                            .on("cellclick", function(d, i) {
                                if (events.legend.mouseclick) events.legend.mouseclick(d, i, this);
                            })
                            .on("cellover", function(d, i) {
                                if (events.legend.mouseover) events.legend.mouseover(d, i, this);
                            })
                            .on("cellout", function(d, i) {
                                if (events.legend.mouseout) events.legend.mouseout(d, i, this);
                            });

                        legend_node
                            .call(legendOrdinal);

                        legend_node.selectAll('.cell')
                            .attr('gen', function(d, i) {
                                if (legend_toggles[d] == true) {
                                    var shape = d4.svg.symbol().type(options.legend.toggle).size(150)()
                                } else {
                                    var shape = d4.svg.symbol().type(options.legend.symbol).size(150)()
                                }
                                d4.select(this).select('path').attr('d', function() { return shape; });
                                return legend_toggles[d];
                            });

                    }
                }
*/
            }
        });
    }

    // REUSABLE FUNCTIONS
    // ------------------
    // calculate average for sorting, add unique indices for color
    // accounts for data updates and assigns unique colors when possible

    function getAxisLabels(dataArray) {
        return dataArray.length ?
            dataArray[0].values.map(function(i, j) { return i.axis;})
            : [];
    }

    function modifyList(list, values, valid_list) {

        if ( values.constructor === Array ) {
            values.forEach(function(e) { checkType(e); });
        } else if (typeof values != "object") {
            checkType(values);
        } else {
            return chart;
        }

        function checkType(v) {
            if (!isNaN(v) && (function(x) { return (x | 0) === x; })(parseFloat(v))) {
                checkValue(parseInt(v));
            } else if (typeof v == "string") {
                checkValue(v);
            }
        }

        function checkValue(val) {
            if ( valid_list.indexOf(val) >= 0 ) {
                modify(val);
            } else if ( val >= 0 && val < valid_list.length ) {
                modify(valid_list[val]);
            }
        }

        function modify(index) {
            if (list.indexOf(index) >= 0) {
                remove(list, index);
            } else {
                list.push(index);
            }
        }

        function remove(arr, item) {
            for (var i = arr.length; i--;) { if (arr[i] === item) { arr.splice(i, 1); } }
        }
    }

    function calcX(value, scale, index) {
        return radial_calcs.rScale(value ?
            value
            : radial_calcs.maxValue * scale) * Math.cos(radial_calcs.angleSlice * index - Math.PI/2);
    }

    function calcY(value, scale, index) {
        return radial_calcs.rScale(value ?
            value
            : radial_calcs.maxValue * scale) * Math.sin(radial_calcs.angleSlice * index - Math.PI/2);
    }

    function setColor(d, index, key) {
        index = index ? index : d._i;
        key = key ? key : d.key;
        return options.areas.colors[key] ? options.areas.colors[key] : options.color[index];
    }
    // END REUSABLE FUNCTIONS

    // ACCESSORS
    // ---------
    chart.nodes = function() {
        return { svg: svg, chart: chart_node, hover: hover_node, tooltip: tooltip_node, legend: legend_node };
    }

    chart.events = function(functions) {
        if (!arguments.length) return events;
        var fKeys = Object.keys(functions);
        var eKeys = Object.keys(events);
        for (var k=0; k < fKeys.length; k++) {
            if (eKeys.indexOf(fKeys[k]) >= 0) events[fKeys[k]] = functions[fKeys[k]];
        }
        return chart;
    }

    chart.width = function(value) {
        if (!arguments.length) return options.width;
        if (options.resize) {
            options.widthMax = value;
        } else {
            options.width = value;
        }
        scaleChart();
        return chart;
    };

    chart.height = function(value) {
        if (!arguments.length) return options.height;
        if (options.resize) {
            options.heightMax = value;
        } else {
            options.height = value;
        }
        scaleChart();
        return chart;
    };

    chart.duration = function(value) {
        if (!arguments.length) return transition_time;
        transition_time = value;
        return chart;
    }

    chart.update = function() {
        if (events.update.begin) events.update.begin(_data);
        if (typeof update === 'function') update();
        setTimeout(function() {
            if (events.update.end) events.update.end(_data);
        }, transition_time);
    }

    chart.data = function(value) {
        if (!arguments.length) return data;
        if (legend_toggles.length) {
            var keys = _data.map(function(m) {return m.key});
            legend_toggles.forEach(function (e, i) { chart.filterAreas(keys[i]); })
        }
        legend_toggles = [];
        data = value;
        return chart;
    };

    chart.pop = function() {
        var row = data.pop()
        if (typeof update === 'function') update();
        return row;
    };

    chart.push = function(row) {
        if ( row && row.constructor === Array ) {
            for (var i=0; i < row.length; i++) {
                check_key(row[i]);
            }
        } else {
            check_key(row);
        }

        function check_key(one_row) {
            if (one_row.key && data.map(function(m) { return m.key }).indexOf(one_row.key) < 0) {
                data.push(one_row);
            }
        }

        return chart;
    };

    chart.shift = function() {
        var row = data.shift();
        if (typeof update === 'function') update();
        return row;
    };

    chart.unshift = function(row) {
        if ( row && row.constructor === Array ) {
            for (var i=0; i < row.length; i++) {
                check_key(row[i]);
            }
        } else {
            check_key(row);
        }

        function check_key(one_row) {
            if (one_row.key && data.map(function(m) { return m.key }).indexOf(one_row.key) < 0) {
                data.unshift(one_row);
            }
        }

        return chart;
    };

    chart.slice = function(begin, end) {
        return data.slice(begin, end);
    };

    // allows updating individual options and suboptions
    // while preserving state of other options
    chart.options = function(values) {
        if (!arguments.length) return options;
        var vKeys = Object.keys(values);
        var oKeys = Object.keys(options);
        for (var k=0; k < vKeys.length; k++) {
            if (oKeys.indexOf(vKeys[k]) >= 0) {
                if (typeof(options[vKeys[k]]) == 'object') {
                    var sKeys = Object.keys(values[vKeys[k]]);
                    var osKeys = Object.keys(options[vKeys[k]]);
                    for (var sk=0; sk < sKeys.length; sk++) {
                        if (osKeys.indexOf(sKeys[sk]) >= 0) {
                            options[vKeys[k]][sKeys[sk]] = values[vKeys[k]][sKeys[sk]];
                        }
                    }
                } else {
                    options[vKeys[k]] = values[vKeys[k]];
                }
            }
        }
        return chart;
    }

    chart.margins = function(value) {
        if (!arguments.length) return options.margins;
        var vKeys = Object.keys(values);
        var mKeys = Object.keys(options.margins);
        for (var k=0; k < vKeys.length; k++) {
            if (mKeys.indexOf(vKeys[k]) >= 0) options.margins[vKeys[k]] = values[vKeys[k]];
        }
        return chart;
    }

    chart.levels = function(value) {
        if (!arguments.length) return options.circles.levels;
        options.circles.levels = value;
        return chart;
    }

    chart.maxValue = function(value) {
        if (!arguments.length) return options.circles.maxValue;
        options.circles.maxValue = value;
        return chart;
    }

    chart.opacity = function(value) {
        if (!arguments.length) return options.areas.opacity;
        options.areas.opacity = value;
        return chart;
    }

    chart.borderWidth = function(value) {
        if (!arguments.length) return options.areas.borderWidth;
        options.areas.borderWidth = value;
        return chart;
    }

    chart.rounded = function(value) {
        if (!arguments.length) return options.areas.rounded;
        options.areas.rounded = value;
        return chart;
    }

    // range of colors to set color based on index
    chart.color = function(value) {
        if (!arguments.length) return options.color;
        options.color = value;
        return chart;
    }

    // colors set according to data keys
    chart.colors = function(colores) {
        if (!arguments.length) return options.areas.colors;
        options.areas.colors = colores;
        return chart;
    }

    chart.keys = function() {
        return data.map(function(m) {return m.key});
    }

    chart.axes = function() {
        return getAxisLabels(data);
    }

    // add or remove keys (or key indices) to filter axes
    chart.filterAxes = function(values) {
        if (!arguments.length) return options.axes.filter;
        var axes = getAxisLabels(data);
        modifyList(options.axes.filter, values, axes);
        return chart;
    }

    // add or remove keys (or key indices) to filter areas
    chart.filterAreas = function(values) {
        if (!arguments.length) return options.areas.filter;
        var keys = data.map(function(m) {return m.key});
        modifyList(options.areas.filter, values, keys);
        return chart;
    }

    // add or remove keys (or key indices) to invert
    chart.invert = function(values) {
        if (!arguments.length) return options.axes.invert;
        var axes = getAxisLabels(data);
        modifyList(options.axes.invert, values, axes);
        return chart;
    }

    // add or remove ranges for keys
    chart.ranges = function(values) {
        if (!arguments.length) return options.axes.ranges;
        if (typeof values == "string") return chart;

        var axes = getAxisLabels(data);

        if ( values && values.constructor === Array ) {
            values.forEach(function(e) { checkRange(e); } );
        } else {
            checkRange(values);
        }

        function checkRange(range_declarations) {
            var keys = Object.keys(range_declarations);
            for (var k=0; k < keys.length; k++) {
                if ( axes.indexOf(keys[k]) >= 0       // is valid axis
                    && range_declarations[keys[k]]    // range array not undefined
                    && range_declarations[keys[k]].constructor === Array
                    && checkValues(keys[k], range_declarations[keys[k]]) ) {
                    options.axes.ranges[keys[k]] = range_declarations[keys[k]];
                }
            }
        }

        function checkValues(key, range) {
            if (range.length == 2 && !isNaN(range[0]) && !isNaN(range[1])) {
                return true;
            } else if (range.length == 0) {
                delete options.axes.ranges[key];
            }
            return false;
        }

        return chart;
    }
    // END ACCESSORS

    // DEFAULT EVENTS
    // --------------
    function areaMouseover(d, i, self) {
        if (legend_toggles[d._i]) return;
        //Dim all blobs
        chart_node.selectAll("." + options.classed + "RadarArea")
            .transition().duration(200)
            .style("fill-opacity", function(d, i, j) {
                return options.areas.filter.indexOf(d.key) >= 0 ? 0 : 0.1;
            })
        //Bring back the hovered over blob
        d4.select(self)
            .transition().duration(200)
            .style("fill-opacity", function(d, i, j) {
                return options.areas.filter.indexOf(d.key) >= 0 ? 0 : 0.7;
            });
        tooltip_show(d, i, self);
    }

    function areaMouseout(d, i, self) {
        //Bring back all blobs
        chart_node.selectAll("." + options.classed + "RadarArea")
            .transition().duration(200)
            .style("fill-opacity", function(d, i, j) {
                return options.areas.filter.indexOf(d.key) >= 0 ? 0 : options.areas.opacity;
            });
        tooltip_hide(d, i, self);
    }

    // on mouseover for the legend symbol
    function legendMouseover(d, i, self) {
        if (legend_toggles[d]) return;
        var area = keys.indexOf(d) >= 0 ? d : keyScale(d);

        //Dim all blobs
        chart_node.selectAll("." + options.classed + "RadarArea")
            .transition().duration(200)
            .style("fill-opacity", function(d, i, j) {
                return options.areas.filter.indexOf(d.key) >= 0 ? 0 : 0.1;
            });
        //Bring back the hovered over blob
        chart_node.selectAll("." + options.classed + "RadarArea." + area.replace(/\s+/g, ''))
            .transition().duration(200)
            .style("fill-opacity", function(d, i, j) {
                return options.areas.filter.indexOf(d.key) >= 0 ? 0 : 0.7;
            });
    }

    function legendClick(d, i, self) {
        var keys = _data.map(function(m) {return m.key});
        modifyList(options.areas.filter, keys[d], keys);
        legend_toggles[d] = legend_toggles[d] ? false : true;
        update();
    }

    function tooltip_show(d, i, self) {
        if (legend_toggles[d._i]) return;
        if (options.width > 200) {
            var labels = getAxisLabels(_data);
            chart_node.select('[key="'+d.axis+'"]').select('foreignObject').style('opacity', 1);

            newX =  - ((options.width-250) / 10) - 40 - (options.width / 2);
            newY =  ((options.height-250) / 20) - (options.height / 10) - (options.height / 2);
            var val = d.key.replace(/ - /gi, '<br/>');

            tooltip
                .attr('x', newX)
                .attr('y', newY)
                .html('<span style="float:left;text-align:left;">' + val + '</span>')
                .transition().duration(200)
                .style('opacity', 1);
        }
    }

    function tooltip_hide(d, i, self) {
        chart_node.select('[key="'+d.axis+'"]').select('foreignObject')
            .style('opacity', options.axes.display && radial_calcs.radius > options.axes.threshold ? 1 : 0);
        tooltip
            .transition().duration(200)
            .style('opacity', 0);
    }


    // Helper Functions
    // ----------------

    function add_index(index, key, values) {
        for (var v=0; v<values.length; v++) {
            values[v]['_i'] = index;
            values[v]['key'] = key;
        }
        return values;
    }

    var get_key = function(d) { return d && d.key; };
    var get_axis = function(d) { return d && d.axis; };

    // Wraps SVG text
    // modification of: http://bl.ocks.org/mbostock/7555321
    function wrap(text, width) {
        text.each(function(d, i, j) {
            var text = d4.select(this);
            var words = d.axis.split(/\s+/).reverse();
            var word;
            var line = [];
            var lineNumber = 0;
            var lineHeight = 1.4; // ems
            var x = calcX(null, options.circles.labelFactor, j);
            var y = calcY(null, options.circles.labelFactor, j);
            var dy = parseFloat(text.attr("dy"));
            var tspan = text.text(null).append("tspan").attr("dy", dy + "em");

            while (word = words.pop()) {
                line.push(word);
                tspan.text(line.join(" "));
                if (tspan.node().getComputedTextLength() > width) {
                    line.pop();
                    tspan.text(line.join(" "));
                    line = [word];
                    tspan = text.append("tspan").attr("dy", ++lineNumber * lineHeight + dy + "em").text(word);
                }
            }
        });
    }

    window.addEventListener( 'resize', scaleChart, false );

    function scaleChart() {
        if (!options.resize || !dom_parent) return;
        var width_offset = dom_parent.node().getBoundingClientRect().left;
        var height_offset = dom_parent.node().getBoundingClientRect().top;
        var width = Math.min(options.widthMax, document.documentElement.clientWidth - width_offset);
        var height = Math.min(options.heightMax, document.documentElement.clientHeight - height_offset);
        options.height = height;
        options.width = width;
        chart.update();
    }

    return chart;

}