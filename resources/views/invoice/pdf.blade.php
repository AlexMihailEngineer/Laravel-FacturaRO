<!DOCTYPE html>
<html lang="ro">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Factura {{ $series }} {{ $number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header-table border, .header-table td {
            border: none;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 40%;
            float: right;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .page-break {
            page-break-after: always;
        }
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <h1>FACTURA</h1>
                <p>Seria: <strong>{{ $series }}</strong></p>
                <p>Număr: <strong>{{ $number }}</strong></p>
                <p>Data: <strong>{{ $issue_date }}</strong></p>
            </td>
            <td style="width: 50%; text-align: right;">
                <!-- Logo placeholder -->
                <div style="font-size: 24px; font-weight: bold; color: #4a5568;">Laravel-FacturaRO</div>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <th style="width: 50%;">FURNIZOR</th>
            <th style="width: 50%;">CLIENT</th>
        </tr>
        <tr>
            <td>
                <strong>{{ $supplier['name'] }}</strong><br>
                CUI/CIF: {{ $supplier['cui'] }}<br>
                {{ $supplier['address'] ?? '' }}
            </td>
            <td>
                <strong>{{ $customer['name'] }}</strong><br>
                CUI/CIF: {{ $customer['cui'] }}<br>
                {{ $customer['address'] ?? '' }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Nr. crt.</th>
                <th>Denumire produs/serviciu</th>
                <th>U.M.</th>
                <th>Cantitate</th>
                <th>Preț unitar (fără TVA)</th>
                <th>Valoare</th>
                <th>TVA (%)</th>
                <th>Valoare TVA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                @php
                    $net = bcmul($item['quantity'], $item['unit_price'], 2);
                    $vat = bcmul($net, bcdiv($item['vat_rate'], '100', 4), 2);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>buc</td>
                    <td>{{ number_format($item['quantity'], 2) }}</td>
                    <td>{{ number_format($item['unit_price'], 2) }}</td>
                    <td>{{ number_format($net, 2) }}</td>
                    <td>{{ $item['vat_rate'] }}%</td>
                    <td>{{ number_format($vat, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <table class="totals-table">
            <tr>
                <th>Total (fără TVA)</th>
                <td class="text-right">
                    @php
                        $totalNet = '0.00';
                        $totalVat = '0.00';
                        foreach($items as $item) {
                            $net = bcmul($item['quantity'], $item['unit_price'], 2);
                            $totalNet = bcadd($totalNet, $net, 2);
                            $totalVat = bcadd($totalVat, bcmul($net, bcdiv($item['vat_rate'], '100', 4), 2), 2);
                        }
                    @endphp
                    {{ number_format($totalNet, 2) }} RON
                </td>
            </tr>
            <tr>
                <th>Total TVA</th>
                <td class="text-right">{{ number_format($totalVat, 2) }} RON</td>
            </tr>
            <tr>
                <th style="font-size: 14px;">TOTAL GENERAL</th>
                <td class="text-right" style="font-size: 14px; font-weight: bold;">
                    {{ number_format(bcadd($totalNet, $totalVat, 2), 2) }} RON
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 50px; font-size: 10px; color: #777;">
        Factura circulă fără semnătură și ștampilă conform Codului Fiscal, art. 319, alin. 29.
    </div>
</body>
</html>
