<!DOCTYPE html>
<html lang="ro">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Factura {{ $series }} {{ $number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header-table td { border: none; }
        .text-right { text-align: right; }
        .totals-table { width: 40%; float: right; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .footer { margin-top: 50px; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <h1 style="margin: 0; color: #2d3748;">FACTURA</h1>
                <p>Seria: <strong>{{ $series }}</strong> | Număr: <strong>{{ $number }}</strong></p>
                <p>Data emiterii: <strong>{{ $issue_date }}</strong></p>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top;">
                <div style="font-size: 20px; font-weight: bold; color: #4a5568;">Laravel-FacturaRO</div>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <th style="width: 50%;">FURNIZOR</th>
            <th style="width: 50%;">CLIENT</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <strong>{{ $supplier['name'] }}</strong><br>
                CIF/CUI: {{ $supplier['cui'] }}<br>
                Reg. Com: {{ $supplier['reg_com'] }}<br>
                Adresă: {{ $supplier['address'] }}
            </td>
            <td style="vertical-align: top;">
                <strong>{{ $customer['name'] }}</strong><br>
                CIF/CUI: {{ $customer['cui'] }}<br>
                @if(!empty($customer['reg_com']))
                    Reg. Com: {{ $customer['reg_com'] }}<br>
                @endif
                Adresă: {{ $customer['address'] }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Nr.</th>
                <th style="width: 45%;">Denumire produs/serviciu</th>
                <th style="width: 10%;">Cant.</th>
                <th style="width: 10%;">Preț Unit.</th>
                <th style="width: 10%;">Valoare</th>
                <th style="width: 10%;">TVA (%)</th>
                <th style="width: 10%;">Valoare TVA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ number_format($item['quantity'], 2) }}</td>
                    <td class="text-right">{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="text-right">{{ number_format($item['net_total'], 2) }}</td>
                    <td>{{ $item['vat_rate'] }}%</td>
                    <td class="text-right">{{ number_format($item['vat_amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <table class="totals-table">
            <tr>
                <th>Total (fără TVA)</th>
                <td class="text-right">{{ number_format($totals['net'], 2) }} RON</td>
            </tr>
            <tr>
                <th>Total TVA</th>
                <td class="text-right">{{ number_format($totals['vat'], 2) }} RON</td>
            </tr>
            <tr style="background-color: #edf2f7;">
                <th style="font-size: 13px;">TOTAL GENERAL</th>
                <td class="text-right" style="font-size: 13px; font-weight: bold;">
                    {{ number_format($totals['gross'], 2) }} RON
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Factura circulă fără semnătură și ștampilă conform Codului Fiscal, art. 319, alin. 29.
        <br>Generat de sistemul modular Laravel-FacturaRO.
    </div>
</body>
</html>
