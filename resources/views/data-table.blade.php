<!DOCTYPE html>
<html>

<head>
    <title>DELAYED QUOTES</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.0/css/bootstrap.min.css">
    <style>
        body.light-theme {
            background-color: #ffffff;
            color: #000000;
        }

        table.light-theme {
            border-color: #dee2e6;
        }

        body.dark-theme {
            background-color: #333333;
            color: #ffffff;
        }

        table.dark-theme {
            border-color: #555555;
        }

        .sortable {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            white-space: nowrap;
        }

        .header-links {
            display: flex;
            justify-content: space-between;
        }

        .sortable.active {
            font-weight: bold;
            color: #007bff;
            background-color: #e0f7ff;
            text-decoration: none;
        }

        .sortable:not(.active) {
            color: #6c757d;
            background-color: #f8f9fa;
            text-decoration: none;
        }

        table {
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #dee2e6;
        }

        td {
            border-right: 1px solid #dee2e6;
        }

        td:last-child {
            border-right: none;
        }

        thead th {
            border-bottom: 2px solid #dee2e6;
        }

        tbody tr td {
            border-bottom: 1px solid #dee2e6;
        }

        .top-row th {
            border-top: none;
            border-left: none;
            border-right: none;
        }

        .top-row th:last-child {
            border-right: 1px solid #dee2e6;
        }

        .top-row th:first-child {
            border-left: 1px solid #dee2e6;
        }

        .positive {
            color: green;
        }

        .negative {
            color: red;
        }

        body.dark-theme .sortable.active {
            color: #007bff;
            background-color: #444;
        }

        body.dark-theme .sortable:not(.active) {
            color: #ccc;
            background-color: #222;
        }

        body.dark-theme th,
        body.dark-theme td {
            border-color: #555555;
            color: #ffffff;
        }

        body.dark-theme .positive {
            color: lightgreen;
        }

        body.dark-theme .negative {
            color: lightcoral;
        }

        .header-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-container .form-group {
            margin-bottom: 0;
        }

        .header-container h1 {
            margin: 0;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <div class="header-container">
            <h1>DELAYED QUOTES</h1>
            <div class="dropdown ms-3">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="market-dropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Select
                </button>
                <ul class="dropdown-menu" aria-labelledby="market-dropdown">
                    <li><a class="dropdown-item" href="#" data-market="0">SGX</a></li>
                    <li><a class="dropdown-item" href="#" data-market="2">Bursa</a></li>
                    <li><a class="dropdown-item" href="#" data-market="3">Nasdaq</a></li>
                </ul>
            </div>
            <button id="theme-toggle" class="btn btn-primary">Toggle Theme</button>

        </div>
        <table class="table table-bordered mt-3">
            <thead>
                <tr class="header-links">
                    <th><a href="#" class="sortable active" data-list="0">Top Volume</a></th>
                    <th><a href="#" class="sortable" data-list="1">Top Gainers</a></th>
                    <th><a href="#" class="sortable" data-list="2">Top Losers</a></th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>Stock</th>
                    <th>Code</th>
                    <th>Buy</th>
                    <th>Buy Vol</th>
                    <th>Sell</th>
                    <th>Sell Vol</th>
                    <th>Open</th>
                    <th>High</th>
                    <th>Low</th>
                    <th>Last</th>
                    <th>Previous</th>
                    <th>Vol</th>
                    <th>+/-</th>
                    <th>%chg</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
            </tbody>
        </table>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({

            });

            $('#market-id').change(function() {
                var selectedMarketId = $(this).val();
                fetchData(selectedMarketId, $('.sortable.active').data('list') || 0);
            });

            $(document).on('click', '.sortable', function(e) {
                e.preventDefault();
                var listType = $(this).data('list');
                fetchData($('#market-id').val(), listType);
                $('.sortable').removeClass('active');
                $(this).addClass('active');
            });
            var defaultMarketId = 0;

            $('#market-dropdown').text($('.dropdown-item[data-market="' + defaultMarketId + '"]').text());

            fetchData(defaultMarketId, $('.sortable.active').data('list') || 0);

            $(document).on('click', '.dropdown-item', function() {
                var marketId = $(this).data('market');
                $('#market-dropdown').text($(this).text()); // Update button text
                fetchData(marketId, $('.sortable.active').data('list') || 0);
            });

            $(document).on('click', '.sortable', function(e) {
                e.preventDefault();
                var listType = $(this).data('list');
                fetchData($('#market-dropdown').data('market') || defaultMarketId, listType);
                $('.sortable').removeClass('active');
                $(this).addClass('active');
            });

            function fetchData(marketId, listType) {
                $.ajax({
                    url: 'https://livefeed3.chartnexus.com/Dummy/quotes',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        market_id: marketId,
                        list: listType
                    },
                    success: function(data) {
                        console.log(Array.isArray(data));
                        console.log(data);

                        if (!Array.isArray(data)) {
                            console.error('Expected an array but got:', data);
                            $('#data-table-body').html(
                                '<tr><td colspan="10">Invalid data format</td></tr>');
                            return;
                        }

                        var rows = '';
                        data.forEach(function(item) {
                            var change = item.last - item.previous;
                            var percentChange = (item.last - item.previous) / item.previous *
                                100;
                            var changeClass = change < 0 ? 'negative' : 'positive';
                            var percentChangeClass = percentChange < 0 ? 'negative' :
                                'positive';

                            rows += '<tr>';
                            rows += '<td>' + item.name + '</td>';
                            rows += '<td>' + item.stockcode + '</td>';
                            rows += '<td>' + item.buy_price + '</td>';
                            rows += '<td>' + item.buy_volume + '</td>';
                            rows += '<td>' + item.sell_price + '</td>';
                            rows += '<td>' + item.sell_volume + '</td>';
                            rows += '<td>' + item.open + '</td>';
                            rows += '<td>' + item.high + '</td>';
                            rows += '<td>' + item.low + '</td>';
                            rows += '<td>' + item.last + '</td>';
                            rows += '<td>' + item.previous + '</td>';
                            rows += '<td>' + item.volume + '</td>';
                            rows += '<td class="' + changeClass + '">' + change + '</td>';
                            rows += '<td class="' + percentChangeClass + '">' + percentChange +
                                '%</td>';

                            rows += '</tr>';
                        });
                        $('#data-table-body').html(rows);
                    },
                    error: function() {
                        $('#data-table-body').html(
                            '<tr><td colspan="10">Failed to load data</td></tr>');
                    }
                });
            }


            fetchData($('#market-id').val(), $('.sortable.active').data('list') || 0);
        });

        document.addEventListener('DOMContentLoaded', (event) => {
            const toggleButton = document.getElementById('theme-toggle');
            const body = document.body;

            const savedTheme = localStorage.getItem('theme') || 'light-theme';
            body.classList.add(savedTheme);

            toggleButton.addEventListener('click', () => {
                if (body.classList.contains('light-theme')) {
                    body.classList.remove('light-theme');
                    body.classList.add('dark-theme');
                    localStorage.setItem('theme', 'dark-theme');
                } else {
                    body.classList.remove('dark-theme');
                    body.classList.add('light-theme');
                    localStorage.setItem('theme', 'light-theme');
                }
                $('table').removeClass('light-theme dark-theme').addClass(body.classList.contains(
                    'light-theme') ? 'light-theme' : 'dark-theme');
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.0/js/bootstrap.min.js"></script>

</body>

</html>
