<script>
    function vrDepartmentChart(result,element,div){
        resultDepartmentWC=result;
        google.setOnLoadCallback();
        var data = new google.visualization.DataTable(result);
        var options = {
            sliceVisibilityThreshold: 0,
            'chartArea':{'width':'100%', 'height':'80%'},
            fontSize: 9,
            pieHole: 0.6,
            legend: { position: 'bottom' },
            colors: ['#5F9EA0', '#4682B4', '#00BFFF', '#6495ED', '#2F4F4F','#696969','#778899','#0000CD','#8FBC8F'],
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
                operatorOperation=0;
                operatorName = "";
                clientName="";
                year="";
                month="";
                quarter="";
                xtittles="year";
                department= data.getFormattedValue(item.row, 0);
                departments=department;
                department1 = "";
                tittles= 'Amount by Year: '+department;
                showUser("transactionamount","all");
            }

        }
    }
</script>