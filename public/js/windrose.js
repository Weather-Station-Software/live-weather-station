function Windrose() {

    var data = [];
    var options = {
        size: 0,
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
    var events = {
        'update': { 'begin': null, 'end': null },
        'gridCircle': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'axisLabel': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'line': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'legend': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'axisLegend': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
        'pieArea': { 'mouseover': null, 'mouseout': null, 'mouseclick': null },
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
    var ticksNumber = 5;
    var angle = d4.scaleLinear().range([0, 2 * Math.PI]);
    var radius = d4.scaleLinear().range([innerRadius, outerRadius]);
    var x = d4.scaleBand().range([0, 2 * Math.PI]).align(0);
    var y = d4.scaleLinear().range([innerRadius, outerRadius]);
    var z = d4.scaleOrdinal().range(standard);
    var dom_parent;
    var transition_time = 0;
    var legend_toggles = [];


    if (options.scale != 'linear') {
        y = d4.scaleRadial().range([innerRadius, outerRadius]);
    }

    function chart(selection) {
        selection.each(function () {
            dom_parent = d4.select(this);


            // update
            update = function() {

                if (!data || !data.series || !data.series.length || !data.series[0].values) {
                    return;
                }
                series = data.series;
                legend = data.legend;
                showLegend = options.legend;
                chartSize = options.size - 40;
                innerRadius = options.size/30;
                ticksNumber = 5;
                if (options.size < 300) {
                    ticksNumber = 3;
                }
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
                    var max = 0;
                    series.forEach(function(serie) {
                        var m = 0;
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
                var angleOffset = -360.0/series.length/2.0;

                var masterRoot = dom_parent.append('svg')
                    .attr('overflow', 'visible')
                    .attr('width', options.size)
                    .attr('height', options.size);
                var svg = masterRoot.append('g')
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
                    .data(y.ticks(ticksNumber).slice(1))
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
                    //.attr('alignment-baseline', 'baseline')
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

                    var innerY, outerY;
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
                            .attr('class', options.classed + 'PieArea')
                            .attr("transform", "rotate("+ angleOffset + ")");
                    }
                }



            }
        });
    }

    // ACCESSORS
    // ---------
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

    return chart;

}