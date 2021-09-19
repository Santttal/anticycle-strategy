/* globals Chart:false, feather:false */

(function () {
  'use strict'

  feather.replace()

  // Graphs
  var ctx = document.getElementById('myChart')


  var queryString = window.location.search;
  var urlParams = new URLSearchParams(queryString);
  var category = urlParams.get('category');
  console.log(category);

  var GetChartData = function (category) {
    $.ajax({
      url: '/blog?category=' + category,
      method: 'GET',
      dataType: 'json',
      success: function (d) {

        var myChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: d.labels,
            datasets: [{
              label: 'USD',
              data: d.usd,
              fill: true,
              lineTension: .6,
              backgroundColor: 'rgba(14, 107, 00, 0.1)',
              borderColor: '#0e6a00',
              borderWidth: -1,
              pointBackgroundColor: '#0e6a00',
              pointRadius: 0,
            },
              {
                data: d.rub,
                label: 'RUB',
                lineTension: .6,
                backgroundColor: 'rgba(0, 124, 255, 0.1)',
                borderColor: '#007bff',
                borderWidth: -1,
                pointBackgroundColor: '#007bff',
                pointRadius: 0,
              }
            ]
          },
          options: {
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: false
                },
              }]
            },
            legend: {
              display: false
            }
          }
        })

      }
    });
  };

  GetChartData(category);

}())
