$(function () {
    var x_values = [];
    var y_values = [];
    var switch1 = true;

    $.get('http://192.168.1.37/pv/web/app_dev.php/show/diagram', function(data) {
        data = data.split('/');

        for (var i in data)
        {
            if (switch1 == true)
            {
                var ts = data[i];
                x_values.push(ts);
                switch1 = false;
            }
            else
            {
                y_values.push(parseFloat(data[i]));
                switch1 = true;
            }
 
        }
        x_values.pop();

        $('#chart').highcharts({
           chart: {
               type: 'spline',
               zoomType: 'x'
           },
           title: {
               text: 'Tagesverlauf'
           },
           subtitle: {
               text: 'Gäbrisstrasse 43a'
           },
           xAxis : {
               title : {
                   text : 'Time'
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
               name: 'Bezug Netz',
               data: y_values
//           }
//          , {
//               name: 'Rückspeisung Netz',
//               data: [
//                [Date.UTC(1970, 9, 29), 0],
//               ]
//           }, {
//               name: 'Produktion',
//                [Date.UTC(1970, 10, 25), 0],
//               data: [
//               ]
           }]
       });
    });
});

function timeConverter(){
  var a = new Date(UNIX_timestamp * 1000);
  var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes() < 10 ? '0' + a.getMinutes() : a.getMinutes();
  var sec = a.getSeconds() < 10 ? '0' + a.getSeconds() : a.getSeconds();
  var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
  return time;
}
