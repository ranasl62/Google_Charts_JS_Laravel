<script type="text/javascript">
    function vrHourWiseChart(result,element){
        resultHourWVR=result;
        google.setOnLoadCallback();
        var data = new google.visualization.DataTable(result);
        var options = {
            'chartArea':{'width':'100%'},
            fontSize: 9,
            vAxis: {
                minValue: 0,
                textPosition: 'none'
            },
            hAxis: {
                title: 'hour',
            },
            legend: { position: 'bottom' },
            seriesType: 'line',
            series: {3: {type: 'bars'}},
            colors: ['#00c0ef', '#00a65a', '#f39c12', '#008080']
        };
        var options1 = {
            width: 970,
            height: 280,
            'chartArea':{'width':'100%'},
            fontSize: 14,
            vAxis: {
                minValue: 0,
                textPosition: 'none'
            },
            hAxis: {
                title: 'hour',
            },
            legend: { position: 'bottom'},
            seriesType: 'line',
            series: {3: {type: 'bars'}}
        };
        var chart = new google.visualization.ComboChart(document.getElementById("vrHourWiseChart"));

        chart.draw(data, options);

    }

</script>