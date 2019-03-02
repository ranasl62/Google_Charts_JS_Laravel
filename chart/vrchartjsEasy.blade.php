<script>
    function vrEasyNoneasyChart(result,element,div){
        resultEasyCC=result;
        google.setOnLoadCallback();
        var data = new google.visualization.DataTable(result);
        var options = {
            sliceVisibilityThreshold: 0,
            'chartArea':{'width':'100%', 'height':'80%'},
            fontSize: 9,
            pieHole: 0.6,
            legend: { position: 'bottom' },
            colors: ['#FF7F50', '#FF8C00', '#808080', '#778899', '#2F4F4F']
        };
        var chart = new google.visualization.PieChart(document.getElementById(div));

        chart.draw(data, options);
        google.visualization.events.addListener(chart, 'select', selectHandler);


        function selectHandler(e) {

            var selection = chart.getSelection();
            $("#tableDataBox").hide();
            if(selection.length){
                $(".loading").html("<div class=\""+"overlay\""+"><i class=\""+"fa fa-refresh fa-spin\""+"></i></div>");
                var item = selection[0];
                operatorName = data.getFormattedValue(item.row, 0);
                easy=operatorName;
                //alert(operatorName);
                operatorOperation=0;
                clientName="";
                year="";
                month="";
                quarter="";
                department="";
                department1="";
                xtittles="year";
                tittles= 'Amount by Year: '+operatorName;
                showUser("transactionamount","all");
            }

        }
    }
</script>