@extends('layouts.app')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::to('css/alpha_demo.css') }}">
<script type="application/javascript" src="{{ URL::to('js/jquery-3.3.1.min.js') }}"></script>
<div class="row justify-content-center">
    <div class="col-md-12 cls-welcome-title" >
        <h2> A L P H A   +   D E M O</h2><br>
        <h5>Real Info @ Real Time</h5>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-md-3 cls-float-left" >
        <div class="card">
            <div class="card-header">History Table</div>
            <div class="card-body">
                <div id="divTable">
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <th>Price</th>
                            <th>Date & Time</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="cls-card-description">
                <i class="material-icons">access_time</i> updated 4 minutes ago
        </div>
    </div>


    <div class="col-md-5 cls-float-left" >
        <div class="card">
            <div class="card-header">Price/Time</div>
            <div class="card-body">
                <div>
                    <canvas id="priceLineChart"></canvas>
                </div>
            </div>
        </div>
        <div class="cls-card-description">
            <i class="material-icons">access_time</i> updated 4 minutes ago
        </div>
    </div>

    <div class="col-md-3 cls-float-left" >
        <div class="card">
            <div class="card-header">Up Or Down</div>
            <div class="card-body">
                <canvas id="priceDoughnutChart"></canvas>
            </div>
        </div>
        <div class="cls-card-description">
            <i class="material-icons">access_time</i> updated 4 minutes ago
        </div>
    </div>

</div>
</script><script type="application/javascript" src="{{ URL::to('js/Chart.bundle.min.js') }}"></script>
<script>
    
    // Vars & Consts
    let untQtyPoints    = 5;
    let untUpCounter    = 1;
    let untDownCounter  = 1;

    // Input info
    let prmDblPrevPrice = 0;
    let prmDblPrice     = 0;
    let prmTimestamp    = 0;

    $(document).ready(function() {

        // Price Line ChartJs
        let contextPriceLineChartJs = $("#priceLineChart");
        let priceLineChart = new Chart(contextPriceLineChartJs, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'PRICE EUR/USD',
                    data: [], // Data values array
                    fill: false,
                    borderColor: '#2196f3',
                    backgroundColor: '#2196f3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // Price Doughnut ChartJs
        let contextPriceDoughnutChartJs = $("#priceDoughnutChart");
        let priceDoughnutChart = new Chart(contextPriceDoughnutChartJs, {
            type: 'doughnut',
            data: {
                labels: ["DESCENDENTE", "ASCENDENTE"],
                datasets: [{
                    data: [],
                    borderColor: ['#fff', '#fff', ],
                    backgroundColor: ['#ff0033', '#33ff00'],
                    borderWidth: 7
                }]},
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });


        // Ajax to receive an updated info in Json format from a remote API
        function retrieveNewInfo() {

            // Retrieve remote API info
            $.ajax({
                type: "GET",
                crossDomain: true,
                async: false,
                url: "https://forex.1forge.com/1.0.3/quotes?pairs=EURUSD&api_key=gvBA2JMNsehFr8JqrlSWQs4XheBzPzdo",
                dataType: 'json',
                success: function (data) {
                    if (data[0].hasOwnProperty('bid')) {

                        // Input info
                        prmDblPrice = data[0].bid;
                        prmTimestamp = data[0].timestamp;

                        // Paint & Refresh priceLineChart
                        priceLineChart.data.labels.push(new Date(prmTimestamp*1000).toISOString().substr(11,8));
                        priceLineChart.data.datasets[0].data.push(prmDblPrice);
                        priceLineChart.update();

                        // Paint & Refresh priceDoughnutChart
                        if(0 != prmDblPrevPrice && prmDblPrevPrice < prmDblPrice){
                            untUpCounter++;
                        } else if(0 != prmDblPrevPrice && prmDblPrevPrice > prmDblPrice) {
                            untDownCounter++;
                        }
                        priceDoughnutChart.data.datasets[0].data = [untUpCounter, untDownCounter];
                        priceDoughnutChart.update();
                        prmDblPrevPrice = prmDblPrice;

                        // Save the new data into MySql
                        $.ajax({
                            type: "POST",
                            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                            crossDomain: true,
                            async: false,
                            url: "/prices",
                            data: {'price': prmDblPrice.toString(), 'created_at': prmTimestamp.toString()},
                            dataType: 'json',
                            success: function () {
                            },
                        });
                    }
                },
            });

            // Paint & Refresh Data Table from MySql [for demo purpose in every new pulse]
            $.ajax({
                type: "GET",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                crossDomain: true,

                url: "/prices",
                dataType: 'html',
                success: function (data) {
                    $("#divTable").html(data);
                },
            });
        }

        // Retrieve new data every 3 seconds
        let i;
        for (i = 0; i <= untQtyPoints; i++) {
            setTimeout(retrieveNewInfo, i * 3000);
        }

    })

</script>
@endsection