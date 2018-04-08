function Windrose() {

    var data = [];
    var options = {
        size: 600,
        legend: false,
        fixed: false,
        scale: 'linear',
        classed: 'lws',
        color: false
    }

    var chart_node;
    var hover_node;
    var tooltip_node;
    var legend_node;
    var axisGrid;

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

    var events = {
        'update': { 'begin': null, 'end': null },
        'gridCircle': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'axisLabel': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'line': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'legend': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'axisLegend': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'radarArea': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'radarInvisibleCircle': { 'mouseover': null, 'mouseout': null, 'mouseclick': null }
    };

    // functions which should be accessible via ACCESSORS
    var update;

    // helper functions
    var tooltip;


    // programmatic
    var _data = [];
    var standard = ["#4242f4", "#42c5f4", "#42f4ce", "#42f456", "#adf442", "#f4e242", "#f4a142", "#f44242"];
    var series = [];
    var legend = [];
    var showLegend = options.legend;
    var chartSize = options.size - 40;
    var innerRadius = options.size/30;
    var outerRadius = (chartSize/2);
    var angle = d4.scaleLinear().range([0, 2 * Math.PI]);
    var radius = d4.scaleLinear().range([innerRadius, outerRadius]);
    var x = d4.scaleBand().range([0, 2 * Math.PI]).align(0);
    var y = d4.scaleLinear().range([innerRadius, outerRadius]);
    var z = d4.scaleOrdinal().range(standard);
    var dom_parent;
    var transition_time = 0;


    var legend_toggles = [];
    var radial_calcs = {};
    var delay = 0;
    var keys;
    var keyScale;
    var colorScale;


    if (options.scale != 'linear') {
        y = d4.scaleRadial().range([innerRadius, outerRadius]);
    }

    function chart(selection) {
        selection.each(function () {
            dom_parent = d4.select(this);
            series = data.series;
            legend = data.legend;
            var svg = dom_parent.append('svg')
                .attr('overflow', 'visible')
                .attr('width', options.size)
                .attr('height', options.size)
                .attr('transform', 'translate(' + options.size / 2 + ',' + options.size / 2 + ')');

            chart_node = svg.append('g').attr('class', options.classed + 'WindroseNode');
            axisGrid = svg.append("g").attr("class", options.classed + "AxisWrapper");
            legend_node = svg.append("g").attr("class", options.classed + "Legend");
            hover_node = svg.append('g').attr('class', options.classed + 'HoverNode');
            tooltip_node = svg.append('g').attr('class', options.classed + 'TooltipNode');




            tooltip = tooltip_node.append('foreignObject')
                .attr('class', options.classed + 'Tooltip')
                .style("opacity", 0)
                .style("padding-top", '10px')
                .style("padding-left", '20px')
                .style("width", "200")
                .style("height", "200");

            // update
            update = function() {

                series = data.series;
                legend = data.legend;
                showLegend = options.legend;
                chartSize = options.size - 40;
                innerRadius = options.size/30;
                outerRadius = (chartSize/2);
                angle = d4.scaleLinear().range([0, 2 * Math.PI]);
                radius = d4.scaleLinear().range([innerRadius, outerRadius]);
                x = d4.scaleBand().range([0, 2 * Math.PI]).align(0);
                y = d4.scaleLinear().range([innerRadius, outerRadius]);
                if (options.scale != 'linear') {
                    y = d4.scaleRadial().range([innerRadius, outerRadius]);
                }
                if (options.color) {
                    z = d4.scaleOrdinal().range(options.color);
                }
                else {
                    z = d4.scaleOrdinal().range(standard.slice(0, series[0].values.length));
                }
                x.domain(series.map(function(d) { return d.axis; }));
                z.domain(legend);
                if (options.fixed) {
                    y.domain([0, 0.5]);
                }
                else {
                    max=0;
                    series.forEach(function(serie) {
                        m = 0;
                        serie.values.forEach(function(value) {
                            m += value;
                        });
                        if (m > max) {
                            max = m;
                        }
                    });
                    y.domain([0, max]);
                }

                angle.domain([0, d4.max(series, function(d,i) { return i + 1; })]);
                radius.domain([0, d4.max(series, function(d) { return d.y0 + d.y; })]);
                angleOffset = -360.0/series.length/2.0;

                svg .attr('width', options.size)
                    .attr('height', options.size)
                    .attr('transform', 'translate(' + options.size / 2 + ',' + options.size / 2 + ')');

                // Axis
                axisGrid.selectAll('.axis')
                    .data(d4.range(angle.domain()[1]))
                    .enter().append('g')
                    .attr('class', options.classed + 'Axis')
                    .attr('transform', function(d) { return 'rotate(' + angle(d) * 180 / Math.PI + ')'; })
                    .call(d4.axisLeft().tickSize(options.size/75).scale(radius.copy().range([-innerRadius, -(outerRadius+8)])));

                var yAxis = axisGrid.append('g')
                    .attr('text-anchor', 'middle');

                var yTick = yAxis
                    .selectAll('g')
                    .data(y.ticks(5))
                    .enter().append('g');

                yTick.append('circle')
                    .attr('fill', 'none')
                    .attr('stroke-dasharray', '4,4')
                    .attr('r', y);

                // Labels
                var label = legend_node.append('g')
                    .selectAll('g')
                    .data(series)
                    .enter().append('g')
                    .attr('text-anchor', 'middle')
                    .attr('transform', function(d) { return 'rotate(' + ((x(d.axis) + x.bandwidth() / 2) * 180 / Math.PI - (90-angleOffset)) + ')translate(' + (outerRadius+30) + ',0)'; });

                label.append('text')
                    .attr('transform', function(d) { return (x(d.axis) + x.bandwidth() / 2 + Math.PI / 2) % (2 * Math.PI) < Math.PI ? 'rotate(90)translate(0,16)' : 'rotate(-90)translate(0,-9)'; })
                    .attr('alignment-baseline', 'baseline')
                    .text(function(d) { return d.axis; });

                // Legend
                if (showLegend) {
                    var rect = outerRadius/30;
                    var shift = rect + 4;
                    var font = rect + 2;
                    var fontx = rect * 1.7 ;
                    var fonty = fontx / 3.4 ;
                    var line = legend_node.append('g')
                        .selectAll('g')
                        .data(legend)
                        .enter().append('g')
                        .attr('transform', function(d, i) { return 'translate(' + (-outerRadius - 30) + ',' + (outerRadius + 18 - i * shift) + ')'; });

                    line.append('rect')
                        .attr('width', rect)
                        .attr('height', rect)
                        .attr('fill', z);

                    line.append('text')
                        .attr('x', fontx)
                        .attr('y', fonty)
                        .attr('dy', '0.35em')
                        .text(function(d) { return d; })
                        .style('font-size', font);
                }

                // Sectors

                var root = chart_node.append("g")
                    .attr('class', options.classed + 'PieWrapper');


                for (var index = 0; index < series[0].values.length; index++) {
                    var pie = root.append('g')
                        .attr('class', options.classed + 'Pie');

                    for (var ang = 0; ang < series.length; ang++) {
                        if (index === 0) {
                            innerY = y(0);
                            outerY = y(series[ang].values[0]);
                        }
                        else {
                            var cpt = 0;
                            for (var i = 0; i < index; i++) {
                                cpt = cpt + series[ang].values[i]
                            }
                            innerY = y(cpt);
                            cpt = 0;
                            for (var j = 0; j <= index; j++) {
                                cpt = cpt + series[ang].values[j]
                            }
                            outerY = y(cpt);
                        }
                        pie.append("path")
                            .attr("fill", z(index))
                            .attr("d", d4.arc()
                                .innerRadius(innerY)
                                .outerRadius(outerY)
                                .startAngle(x(series[ang].axis))
                                .endAngle(x(series[ang].axis) + x.bandwidth())
                                .padAngle(0.01)
                                .padRadius(innerRadius))


                            .attr("transform", "rotate("+ angleOffset + ")");
                    }
                }
            }
        });
    }

    // REUSABLE FUNCTIONS
    // ------------------
    // calculate average for sorting, add unique indices for color
    // accounts for data updates and assigns unique colors when possible
/*
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
*/
    // ACCESSORS
    // ---------
  /*  chart.nodes = function() {
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
*/
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
/*
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
    };*/

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
/*
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
    }*/
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

 /*   function add_index(index, key, values) {
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
    }*/

    return chart;

}