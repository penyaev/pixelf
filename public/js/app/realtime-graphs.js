var realtimeGraphs = function () {
    var stats_size = 300;
    var stats_step = 1000;
    var stats_height = 30;
    var stats_extent = null;
    var sites_ids = [];
    var stats_url = null;
    var values = [];
    var values_since = 0;
    var cubism_initialized = false;
    var context;

    function site_load(site_id) {
        return context.metric(function(start, stop, step, callback) {
            start = (+start)/1000; stop = (+stop)/1000;
            var offset = start - values_since;
            if (offset < 0)
                offset = 0;

            callback(null, values[site_id] ? values[site_id].slice(offset, offset + (stop-start)) : [] );
        }, site_id);
    }


    function cubism_initialize() {
        cubism_initialized=true;
        context = cubism.context()
                        .serverDelay(750)
                        .clientDelay(0)
                        .step(stats_step)
                        .size(stats_size);


        d3.select(".realtime-graph-axis-rule").call(function(div) {
            div.append("div")
                .attr("class", "rule")
                .call(context.rule());

        });
        d3.select(".realtime-graph-axis").call(function(div) {
            div.append("div")
            .attr("class", "axis")
            .call(context.axis().ticks(d3.time.minutes, 1).orient("top"));



        });
        $('.realtime-graph').each(function () {
            var site_load_values = site_load($(this).attr('data-id'));
            d3.select('#'+$(this).attr('id')).call(function(div) {
                div.selectAll(".horizon")
                    .data([site_load_values])
                    .enter().append("div")
                    .attr("class", "horizon")
                    .call(context.horizon()
                                    .height(stats_height)
                                    .colors(["#bdd7e7","#bae4b3"])
                                    .extent(stats_extent ? [0, stats_extent] : null));
            });
        });

        setInterval(function () {
            fetch_data();
        }, stats_step);
    }

    function fetch_data() {
//        var since = Math.round((new Date()).getTime()/1000)-stats_size*stats_step/1000;
        var steps_to_load = 10;
        if (!cubism_initialized) {
            steps_to_load = stats_size; // в первый раз загружаем все данные что есть, потом маленькими кусочками
        }
        var since = -steps_to_load*stats_step/1000;
        $.getJSON(stats_url, {
            since: since,
            step: stats_step/1000,
            sites_ids: sites_ids
        }, function (json) {
            values = json;
            values_since = Math.round((new Date()).getTime()/1000)+since;

            if (!cubism_initialized) {
                cubism_initialize();
            }
        }).fail(function () {
//                alert('Ошибка при загрузке данных с сервера')
            });
    }

    return {
        init: function (sites_ids_, stats_url_, stats_size_, stats_height_, stats_extent_) {
            if (stats_size_)
                stats_size = stats_size_;
            if (stats_height_)
                stats_height = stats_height_;
            if (stats_extent_)
                stats_extent = stats_extent_;
            if (sites_ids_)
                sites_ids = sites_ids_;
            if (stats_url_)
                stats_url = stats_url_;

            fetch_data();

        }
    };
}();