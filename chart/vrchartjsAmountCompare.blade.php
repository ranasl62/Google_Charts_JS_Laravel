<script>

    function all(result,element,div){
        boolfordynamic = 'a';
        resultYearWR=result;
        document.getElementById("dynamicChartTitle").innerHTML=tittles;
        $('#chartModalTitle').html($('#dynamicChartTitle').html());
        google.setOnLoadCallback();
        var jsonResult = result;
        var x, x2, data, data2, diffData;
        var x = jsonResult.data[0];
        var data = new google.visualization.DataTable(x);

        var chart = new google.visualization.ColumnChart(document.getElementById(div));
        var formatter = new google.visualization.NumberFormat({pattern: '###.#M'});


        var options = {
            colors: ['#FAC40F'],
            animation: {
                duration: 500,
                easing: 'inAndOut',
                startup: true
            },
            'chartArea':{'width':'100%'},
            fontSize: 9,
            legend: { position: 'none' },
            diff: { oldData: { title: 'hello', opacity: 1, color: '#F9E79F',
                    tooltip:{
                        prefix:'total amount'
                    }
                },
                newData: { opacity: 1, widthFactor: 1,
                    tooltip:{
                        prefix: operatorName+' amount'
                    }
                }
            },
            hAxis: {
                title: xtittles,
            },
            vAxis: {
                minValue: 0,
                textPosition: 'none'
            }
        };

        var options1 = {
            'width':'970',
            'chartArea':{'width':'100%'},
            colors: ['#FAC40F'],
            fontSize: 14,
            legend: { position: 'none' },
            diff: { oldData: { title: 'hello', opacity: 1, color: '#F9E79F',
                    tooltip:{
                        prefix:'total amount'
                    }
                },
                newData: { opacity: 1, widthFactor: 1,
                    tooltip:{
                        prefix: operatorName+department+' amount'
                    }
                }
            },
            hAxis: {
                title: xtittles,
            },
            vAxis: {
                minValue: 0,
                textPosition: 'none'
            }
        };

        if(operatorName!="" || department!="" && department1==""){
            x2 = jsonResult.data[1];
            data2 = new google.visualization.DataTable(x2);
            formatter.format(data2, 2);
            diffData = chart.computeDiff(data, data2);
            var view = new google.visualization.DataView(diffData);
            view.setColumns([0,1, 2]);


            chart.draw(view, options);
        }else{
            formatter.format(data, 2);
            var view = new google.visualization.DataView(data);
            view.setColumns([0,1,
                    {
                        calc: "stringify",
                        sourceColumn: 2,
                        type: "string",
                        role: "annotation"
                    }
                ,  3
            ]);

            chart.draw(view, options);
        }

        google.visualization.events.addListener(chart, 'select', selectHandler);


        function selectHandler(e) {
            document.getElementById("tableData").innerHTML="<table id=\""+"dataTable"+"\" class=\""+"table table-bordered table-hover"+"\"></table>";
            if(month==""){
                $(".loading").html("<div class=\""+"overlay\""+"><i class=\""+"fa fa-refresh fa-spin\""+"></i></div>");
            }
            var selection = chart.getSelection();
            if(selection.length){

                var item = selection[0];
                var str0;
                if(operatorName!="" || department!="" && department1==""){
                    if(item.row%2!=0)
                        str0 = data.getFormattedValue((item.row-1)/2, 0);
                    else
                        str0 = data.getFormattedValue((item.row)/2, 0);
                }
                else{
                    str0 = data.getFormattedValue(item.row, 0);
                }
                operatorOperation=(operatorOperation+upDown)%5;
                if(operatorOperation==0){
                    year="";
                    month="";
                    quarter="";
                    tittles= operatorName+clientName+department+' Amount by Year';
                    xtittles= 'year';

                }
                //Total Ammount of Selected Year's quarter Month  VS Selected Year's quarter month

                else if(operatorOperation==1){
                    year=str0;
                    month="";
                    quarter="YES";
                    tittles= operatorName+clientName+department+' Amount by Quarter: '+year;
                    xtittles= 'quarters of : '+year;
                }
                //Total Ammount of Selected Year's  Month  VS Selected Year's  month
                else if(operatorOperation==2){
                    month="";
                    quarter="";
                    tittles= operatorName+clientName+department+' Amount by Month: '+year;
                    xtittles= 'months of :' + year;
                }
                //Total Ammount of Selected Year => Month => Day VS Selected Year => Month => Day
                else if(operatorOperation==3){
                    month=str0;
                    quarter="";
                    tittles= operatorName+clientName+department+' Amount by Day: '+mL[str0-1]+' '+year;
                    xtittles= 'days of : '+mL[str0-1]+', '+ year;
                }
                else if(operatorOperation==4 && operatorName!="Easy" && operatorName!="Others"){
                    year="";
                    month="";
                    quarter="";
                    tittles= operatorName+clientName+department+' Amount by Year';
                    xtittles= 'year';
                    operatorOperation=0;
                }
                else {
                    year="";
                    month="";
                    quarter="";
                    tittles= operatorName+clientName+department+' Amount by Year';
                    xtittles= 'year';
                    operatorOperation=0;
                }
                if(operatorOperation!=4){
                    $("#tableDataBox").hide();
                    operators=operatorName;
                    showUser(element,div);
                }
            }
        }



        function selectHandler1(e) {
            document.getElementById("tableData").innerHTML="<table id=\""+"dataTable"+"\" class=\""+"table table-bordered table-hover"+"\"></table>";
            if(month!=""){
                $(".loading").html("<div class=\""+"overlay\""+"><i class=\""+"fa fa-refresh fa-spin\""+"></i></div>");
            }
            var selection = chart1.getSelection();
            if(selection.length){
                var item = selection[0];
                var str0;
                if(operatorName!=""){
                    if(item.row%2!=0)
                        str0 = data.getFormattedValue((item.row-1)/2, 0);
                    else
                        str0 = data.getFormattedValue((item.row)/2, 0);
                }
                else{
                    str0 = data.getFormattedValue(item.row, 0);
                }
                operatorOperation=(operatorOperation+upDown)%5;
                if(operatorOperation==0){
                    year="";
                    month="";
                    quarter="";
                    tittles= operatorName+clientName+' Amount by Year';
                    xtittles= 'year';

                }
                //Total Ammount of Selected Year's quarter Month  VS Selected Year's quarter month

                if(operatorOperation==1){
                    if(upDown>0)
                        year=str0;
                    month="";
                    quarter="YES";
                    tittles= operatorName+clientName+' Amount by Quarter : '+year;
                    xtittles= 'quarter';
                }
                //Total Ammount of Selected Year's  Month  VS Selected Year's  month
                else if(operatorOperation==2){
                    month="";
                    quarter="";
                    tittles= operatorName+clientName+' Amount by Month : '+year;
                    xtittles= 'month';
                }
                //Total Ammount of Selected Year => Month => Day VS Selected Year => Month => Day
                else if(operatorOperation==3){
                    month=str0;
                    quarter="";
                    tittles= operatorName+clientName+' Amount by Day: '+mL[str0-1]+' '+year;
                    xtittles= 'day';
                }
                else if(operatorOperation==4 && operatorName!="Easy" && operatorName!="Others"){
                    $("#tableDataBox").show();
                    $('html,body').animate({
                        scrollTop: $("#tableDataBox").offset().top},'slow'
                    );
                    quarter=str0;
                    showUser("vrTable",div);
                }
                else {
                    year="";
                    month="";
                    quarter="";
                    tittles= operatorName+clientName+' Amount by Year';
                    xtittles= 'year';
                    operatorOperation=0;

                }
                if(operatorOperation!=4){
                    $("#tableDataBox").hide();
                    operators=operatorName;
                    showUser(element,div);
                }
            }
        }


    }






    function alls(result,element, div){
        boolfordynamic = 'b';
        resultYearWR = result;
        console.log(result,xtittles);
        document.getElementById("dynamicChartTitle").innerHTML=xtittlesAlls;
        // document.getElementById("title2").innerHTML=xtittlesAlls;
        google.setOnLoadCallback();
        var data = new google.visualization.DataTable(result);
        var chart = new google.visualization.ColumnChart(document.getElementById(div));
        // var chart1 = new google.visualization.ColumnChart(document.getElementById('abc'));
        var formatter = new google.visualization.NumberFormat({pattern: '###.#M'});

        //alert(result);
        var options = {
            'chartArea':{'width':'100%'},
            fontSize: 8,
            legend: { position: 'none' },
            diff: { oldData: { title: 'hello', opacity: 1, color: '#F9E79F',
                    tooltip:{
                        prefix:'total amount'
                    }
                },
                newData: { opacity: 1, widthFactor: 1,
                    tooltip:{
                        prefix: operatorName+department+' amount'
                    }
                }
            },
            hAxis: {

            },
            vAxis: {
                minValue: 0,

                // title: 'amount',
                textPosition: 'none'
            }
        };

        var options1 = {
            'width':'970',
            'chartArea':{'width':'100%'},
            fontSize: 14,
            legend: { position: 'none' },
            diff: { oldData: { title: 'hello', opacity: 1, color: '#F9E79F',
                    tooltip:{
                        prefix:'total amount'
                    }
                },
                newData: { opacity: 1, widthFactor: 1,
                    tooltip:{
                        prefix: operatorName+department+' amount'
                    }
                }
            },
            hAxis: {

            },
            vAxis: {
                // title: 'amount',
                minValue: 0,
                textPosition: 'none'
            }
        };
        // diffData = data;
        var view = new google.visualization.DataView(data);
        // if(view.getColumnLabel(9)==none){

        // }


        if(view.getNumberOfColumns()==25){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            formatter.format(data, 10);
            formatter.format(data, 12);
            formatter.format(data, 14);
            formatter.format(data, 16);
            formatter.format(data, 18);
            formatter.format(data, 20);
            formatter.format(data, 22);
            formatter.format(data, 24);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,9,
                {
                    calc: "stringify",
                    sourceColumn: 10,
                    type: "string",
                    role: "annotation" }
                ,11,
                {
                    calc: "stringify",
                    sourceColumn: 12,
                    type: "string",
                    role: "annotation" }
                ,13,
                {
                    calc: "stringify",
                    sourceColumn: 14,
                    type: "string",
                    role: "annotation" }
                ,15,
                {
                    calc: "stringify",
                    sourceColumn: 16,
                    type: "string",
                    role: "annotation" }
                ,17,
                {
                    calc: "stringify",
                    sourceColumn: 18,
                    type: "string",
                    role: "annotation" }
                ,19,
                {
                    calc: "stringify",
                    sourceColumn: 20,
                    type: "string",
                    role: "annotation" }
                ,21,
                {
                    calc: "stringify",
                    sourceColumn: 22,
                    type: "string",
                    role: "annotation" }
                ,23,
                {
                    calc: "stringify",
                    sourceColumn: 24,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==23){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            formatter.format(data, 10);
            formatter.format(data, 12);
            formatter.format(data, 14);
            formatter.format(data, 16);
            formatter.format(data, 18);
            formatter.format(data, 20);
            formatter.format(data, 22);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,9,
                {
                    calc: "stringify",
                    sourceColumn: 10,
                    type: "string",
                    role: "annotation" }
                ,11,
                {
                    calc: "stringify",
                    sourceColumn: 12,
                    type: "string",
                    role: "annotation" }
                ,13,
                {
                    calc: "stringify",
                    sourceColumn: 14,
                    type: "string",
                    role: "annotation" }
                ,15,
                {
                    calc: "stringify",
                    sourceColumn: 16,
                    type: "string",
                    role: "annotation" }
                ,17,
                {
                    calc: "stringify",
                    sourceColumn: 18,
                    type: "string",
                    role: "annotation" }
                ,19,
                {
                    calc: "stringify",
                    sourceColumn: 20,
                    type: "string",
                    role: "annotation" }
                ,21,
                {
                    calc: "stringify",
                    sourceColumn: 22,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==21){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            formatter.format(data, 10);
            formatter.format(data, 12);
            formatter.format(data, 14);
            formatter.format(data, 16);
            formatter.format(data, 18);
            formatter.format(data, 20);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,9,
                {
                    calc: "stringify",
                    sourceColumn: 10,
                    type: "string",
                    role: "annotation" }
                ,11,
                {
                    calc: "stringify",
                    sourceColumn: 12,
                    type: "string",
                    role: "annotation" }
                ,13,
                {
                    calc: "stringify",
                    sourceColumn: 14,
                    type: "string",
                    role: "annotation" }
                ,15,
                {
                    calc: "stringify",
                    sourceColumn: 16,
                    type: "string",
                    role: "annotation" }
                ,17,
                {
                    calc: "stringify",
                    sourceColumn: 18,
                    type: "string",
                    role: "annotation" }
                ,19,
                {
                    calc: "stringify",
                    sourceColumn: 20,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==19){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            formatter.format(data, 10);
            formatter.format(data, 12);
            formatter.format(data, 14);
            formatter.format(data, 16);
            formatter.format(data, 18);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,9,
                {
                    calc: "stringify",
                    sourceColumn: 10,
                    type: "string",
                    role: "annotation" }
                ,11,
                {
                    calc: "stringify",
                    sourceColumn: 12,
                    type: "string",
                    role: "annotation" }
                ,13,
                {
                    calc: "stringify",
                    sourceColumn: 14,
                    type: "string",
                    role: "annotation" }
                ,15,
                {
                    calc: "stringify",
                    sourceColumn: 16,
                    type: "string",
                    role: "annotation" }
                ,17,
                {
                    calc: "stringify",
                    sourceColumn: 18,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==17){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            formatter.format(data, 10);
            formatter.format(data, 12);
            formatter.format(data, 14);
            formatter.format(data, 16);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,9,
                {
                    calc: "stringify",
                    sourceColumn: 10,
                    type: "string",
                    role: "annotation" }
                ,11,
                {
                    calc: "stringify",
                    sourceColumn: 12,
                    type: "string",
                    role: "annotation" }
                ,13,
                {
                    calc: "stringify",
                    sourceColumn: 14,
                    type: "string",
                    role: "annotation" }
                ,15,
                {
                    calc: "stringify",
                    sourceColumn: 16,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==15){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            formatter.format(data, 10);
            formatter.format(data, 12);
            formatter.format(data, 14);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,9,
                {
                    calc: "stringify",
                    sourceColumn: 10,
                    type: "string",
                    role: "annotation" }
                ,11,
                {
                    calc: "stringify",
                    sourceColumn: 12,
                    type: "string",
                    role: "annotation" }
                ,13,
                {
                    calc: "stringify",
                    sourceColumn: 14,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==13){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            formatter.format(data, 10);
            formatter.format(data, 12);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,9,
                {
                    calc: "stringify",
                    sourceColumn: 10,
                    type: "string",
                    role: "annotation" }
                ,11,
                {
                    calc: "stringify",
                    sourceColumn: 12,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==11){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            formatter.format(data, 10);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,9,
                {
                    calc: "stringify",
                    sourceColumn: 10,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==9){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            formatter.format(data, 8);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,7,
                {
                    calc: "stringify",
                    sourceColumn: 8,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==7){
            formatter.format(data, 2);
            formatter.format(data, 4);
            formatter.format(data, 6);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,5,
                {
                    calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else if(view.getNumberOfColumns()==5){
            formatter.format(data, 2);
            formatter.format(data, 4);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,3,
                {
                    calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" }
                ,]);
        }else{
            formatter.format(data, 2);
            view.setColumns([0,1,
                {
                    calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" }
                ,]);
        }

        chart.draw(view, options);
        // chart1.draw(view, options1);
        // google.visualization.events.addListener(chart, 'select', selectHandler);
        //
        //
        // function selectHandler(e) {
        //     alert("There is no event-handler");
        // }


    }
</script>
