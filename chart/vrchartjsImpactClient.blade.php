
<script >

    function drawChart(result, element, div) {
        resultImpactClient=result;
        var arrayData = result;
        var sortArray = [];
        for (var i = 0; i <= 10; i++) {
            sortArray.push(arrayData['rows'][i].c[3].v);
        }
        sortArray.sort(function(a, b){return b - a});
        var maxData=sortArray[0]; var minData=sortArray[10];
        maxData+= (maxData/50);
        minData-= (minData/50);

        var dataChart = new google.visualization.DataTable(result);

        var waterFallChart = new google.visualization.ChartWrapper({
            theme: 'material',
            chartType: 'CandlestickChart',
            containerId: div,
            dataTable: dataChart,
            options: {
                animation: {
                    duration: 800,
                    easing: 'inAndOut',
                    startup: true
                },
                backgroundColor: 'transparent',
                bar: {
                    groupWidth: '85%'
                },
                chartArea: {
                    backgroundColor: 'transparent',
                    left: 60,
                    top: 24,
                    width: '100%'
                },
                hAxis: {
                    slantedText: false,
                    textStyle: {
                        color: '#616161',
                        fontSize: 9
                    }
                },
                tooltip: {
                    isHtml: true,
                    trigger: 'both'
                },
                vAxis: {
                    format: 'short',
                    gridlines: {
                        count: -1
                    },
                    textStyle: {
                        color: '#616161'
                    },
                    viewWindow: {
                        max: maxData,
                        min: minData
                    }
                },
                tooltip: {isHtml: true},
                width: '100%'
            }
        });

        google.visualization.events.addOneTimeListener(waterFallChart, 'ready', function () {
            google.visualization.events.addListener(waterFallChart.getChart(), 'animationfinish', function () {
                var annotation;
                var chartLayout;
                var container;
                var numberFormatShort;
                var positionY;
                var positionX;
                var rowBalance;
                var rowBottom;
                var rowFormattedValue;
                var rowIndex;
                var rowTop;
                var rowValue;
                var rowWidth;

                container = document.getElementById(waterFallChart.getContainerId());
                chartLayout = waterFallChart.getChart().getChartLayoutInterface();
                numberFormatShort = new google.visualization.NumberFormat({
                    pattern: 'short'
                });
                rowIndex = 0;
                Array.prototype.forEach.call(container.getElementsByTagName('rect'), function(rect) {
                    switch (rect.getAttribute('fill')) {
                        // use colors to identify bars
                        case '#fe0b1b':
                        case '#2ea232':
                        case '#f39c12':
                        case '#2ea9b2':
                            rowWidth = parseFloat(rect.getAttribute('width'));
                            if (rowWidth > 2) {
                                rowBottom = waterFallChart.getDataTable().getValue(rowIndex, 1);
                                rowTop = waterFallChart.getDataTable().getValue(rowIndex, 3);
                                rowValue = rowTop - rowBottom;
                                rowBalance = Math.max(rowBottom, rowTop);
                                positionY = chartLayout.getYLocation(rowBalance) - 6;
                                positionX = parseFloat(rect.getAttribute('x'));
                                rowFormattedValue = numberFormatShort.formatValue(rowValue);
                                if (rowValue < 0) {
                                    rowFormattedValue = rowFormattedValue.replace('-', '');
                                    rowFormattedValue = '(' + rowFormattedValue + ')';
                                }
                                annotation = container.getElementsByTagName('svg')[0].appendChild(container.getElementsByTagName('text')[0].cloneNode(true));
                                $(annotation).text(rowFormattedValue);
                                annotation.setAttribute('x', (positionX + (rowWidth / 2)));
                                annotation.setAttribute('y', positionY);
                                annotation.setAttribute('font-weight', 'bold');
                                rowIndex++;
                            }
                            break;
                    }
                });
            });
        });

        $(window).resize(function() {
            waterFallChart.draw();
        });
        waterFallChart.draw();
    }
</script>