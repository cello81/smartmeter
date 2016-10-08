$(document).ready(function () {
    var x_values = [];
    var y_values = [];
    var y2_values = [];
    var iterator = 1;

    $.get('../../show/monthdays/2016-09', function(data) {
        data = data.split('/');
        for (var i in data)
        {
            if (iterator == 1)
            {
                var ts = data[i];
                x_values.push(ts);
                iterator++;
            }
            else if (iterator == 2)
            {
                y_values.push(parseFloat(data[i]));
                iterator++;
            } else if (iterator == 3)
            {
		y2_values.push(parseFloat(data[i]));
		iterator = 1;
	   }	
         }
        x_values.pop();

        $('#monthchart').highcharts({
           chart: {
               type: 'column'
           },
           title: {
               text: 'Monatsübersicht'
           },
           subtitle: {
               text: 'Gäbrisstrasse 43a'
           },
           xAxis : {
               title : {
                   text : 'Datum'
               },
               categories : x_values,
               type: 'datetime'
           },
           yAxis: {
               title: {
                   text: 'Leistung [Watt]'
               },
               min: 0
           },

           plotOptions: {
               spline: {
                   marker: {
                       enabled: true
                   }
               }
           },

           series: [{
               name: 'Bezug [Wh]',
               data: y_values
           }
          , {
               name: 'Kosten [Rp.]',
               data: y2_values
//           }, {
//               name: 'Produktion',
//                [Date.UTC(1970, 10, 25), 0],
//               data: [
//               ]
           }]
       });
    });
});

