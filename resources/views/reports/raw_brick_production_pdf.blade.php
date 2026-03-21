<!DOCTYPE html>
<html lang="bn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Raw Brick Production Report</title>
    <style>
        body {
            font-family: 'solaimanlipi', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header-container { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; text-align: center; }
        .company-name { font-size: 20px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .company-info { margin: 2px 0; font-size: 12px; }
        .report-title { font-size: 16px; font-weight: bold; text-decoration: underline; margin: 10px 0; text-align: center; }
        .meta-info { display: table; width: 100%; margin-bottom: 10px; font-size: 11px; }
        .meta-left { display: table-cell; text-align: left; }
        .meta-right { display: table-cell; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; vertical-align: middle; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; color: #000; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; height: 30px; border-top: 1px solid #ddd; padding-top: 5px; font-size: 10px; color: #666; text-align: center; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header-container">
        @if(isset($business_store))
            <h1 class="company-name">{{ $business_store->store_name }}</h1>
            <p class="company-info">{{ $business_store->address }}</p>
            <p class="company-info">Mobile: {{ $business_store->phone }}</p>
        @endif
    </div>

    <div class="report-title">কাঁচা ইট প্রোডাকশন রিপোর্ট</div>

    <div class="meta-info">
        <div class="meta-left">
            @if(isset($filters['date']) && $filters['date'])
                <strong>তারিখ:</strong> {{ $filters['date'] }} |
            @endif
            @if(isset($filters['month']) && $filters['month'])
                <strong>মাস:</strong> {{ $filters['month'] }} |
            @endif
            @if(isset($filters['season']) && $filters['season'])
                <strong>সিজন:</strong> {{ $filters['season'] }}
            @endif
        </div>
        <div class="meta-right">
            Print Date: {{ date('d-m-Y h:i A') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">SL</th>
                <th style="width: 15%">তারিখ</th>
                <th style="width: 40%">মাঠ</th>
                <th style="width: 20%">পরিমাণ</th>
                <th style="width: 20%">সিজন</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @foreach($productions as $index => $p)
            @php $totalQty += $p->quantity; @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $p->product_date ? \Carbon\Carbon::parse($p->product_date)->format('d-m-Y') : '' }}</td>
                <td>{{ $p->field->name ?? '-' }}</td>
                <td class="text-center">{{ $p->quantity }}</td>
                <td class="text-center">{{ $p->season }}</td>
            </tr>
            @endforeach
            @if(empty($productions) || count($productions) === 0)
            <tr>
                <td colspan="5" class="text-center">কোনো ডাটা পাওয়া যায়নি</td>
            </tr>
            @else
            <tr style="background-color: #e8e8e8; font-weight: bold;">
                <td colspan="3" class="text-right">মোট পরিমাণ:</td>
                <td class="text-center">{{ $totalQty }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Software Generated Report | Page <span class="page-number"></span></p>
    </div>
</body>
</html>

