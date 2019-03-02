<script type="text/javascript">
    function vrTopTenClient(result, element,div){
        resultTopTC=result;
        google.setOnLoadCallback();
        var data = new google.visualization.DataTable(result);
        var options = {
            fontSize: 9,
            legend: { position: 'none' },
            hAxis: {
                textPosition: 'none'
            },
        };
        var formatter = new google.visualization.NumberFormat({pattern: '###.#M'});
        formatter.format(data, 2);
        var chart = new google.visualization.BarChart(document.getElementById(div));
        var view = new google.visualization.DataView(data);
        view.setColumns([0,1,
            {
                calc: "stringify",
                sourceColumn: 2,
                type: "string",
                role: "annotation",
            } ]);

        chart.draw(view, options);

    }
</script>