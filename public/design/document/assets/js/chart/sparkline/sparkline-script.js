(function($) {
    "use strict";
    var sparkline_chart = {
      init: function() {
        setTimeout(function(){
            $("#simple-line-chart-sparkline").sparkline([5, 10, 20, 14, 17, 21, 20, 10, 4, 13,0, 10, 30, 40, 10, 15, 20], {
                type: 'line',
                width: '100%',
                height: '150',
                tooltipClassname: 'chart-sparkline',
                lineColor: '#2b5e5e',
                fillColor: 'transparent',
                highlightLineColor: '#2b5e5e',
                highlightSpotColor: '#2b5e5e',
                targetColor: '#2b5e5e',
                performanceColor: '#2b5e5e',
                boxFillColor: '#2b5e5e',
                medianColor: '#2b5e5e',
                minSpotColor: '#2b5e5e'
            });
      })
    }
};
  sparkline_chart.init()
})(jQuery);
