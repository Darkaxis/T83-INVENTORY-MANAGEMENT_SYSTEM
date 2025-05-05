<!-- filepath: d:\WST\inventory-management-system\resources\views\reports\pdf\sales.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $store->name }} - Sales Report</title>
    <style>
        :root {
            --primary-color: {{ $storeBranding['accent_color'] ?? '#4e73df' }};
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background-color: {{ $storeBranding['accent_color'] ?? '#4e73df' }};
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 5px;
        }
        
        .store-info {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .store-logo {
            max-width: 60px;
            max-height: 60px;
            margin-right: 15px;
        }
        
        h1 {
            font-size: 20px;
            margin: 0 0 5px;
            color: white;
        }
        
        h2 {
            font-size: 16px;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid {{ $storeBranding['accent_color'] ?? '#4e73df' }};
            color: {{ $storeBranding['accent_color'] ?? '#4e73df' }};
        }
        
        .subtitle {
            font-size: 14px;
            margin: 0 0 15px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .summary-box {
            border: 1px solid #ddd;
            border-left: 5px solid {{ $storeBranding['accent_color'] ?? '#4e73df' }};
            padding: 15px;
            width: 30%;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .summary-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: {{ $storeBranding['accent_color'] ?? '#4e73df' }};
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: {{ $storeBranding['accent_color'] ?? '#4e73df' }};
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        
        td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .report-badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: {{ $storeBranding['accent_color'] ?? '#4e73df' }};
            color: white;
            font-size: 10px;
            border-radius: 10px;
            margin-left: 5px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="store-info">
            @if(!empty($storeBranding['logo_binary']))
                <img src="data:{{ $storeBranding['logo_mime_type'] }};base64,{{ base64_encode($storeBranding['logo_binary']) }}" 
                     alt="{{ $store->name }}" class="store-logo">
            @endif
            <div>
                <h1>{{ $store->name }}</h1>
                <div class="subtitle">Sales Report | {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</div>
            </div>
        </div>
    </div>
    
    <div class="summary-row">
        <div class="summary-box">
            <div class="summary-label">Total Sales</div>
            <div class="summary-value">PHP {{ number_format($totalSales, 2) }}</div>
        </div>
        
        <div class="summary-box">
            <div class="summary-label">Total Orders</div>
            <div class="summary-value">{{ number_format($orderCount) }}</div>
        </div>
        
        <div class="summary-box">
            <div class="summary-label">Average Order</div>
            <div class="summary-value">
                PHP {{ number_format($orderCount > 0 ? $totalSales / $orderCount : 0, 2) }}
            </div>
        </div>
    </div>
    
    <h2>
        @if(!empty($storeBranding['logo_binary']))
            <img src="data:{{ $storeBranding['logo_mime_type'] }};base64,{{ base64_encode($storeBranding['logo_binary']) }}" 
                 alt="{{ $store->name }}" style="height: 20px; margin-right: 5px; vertical-align: middle;">
        @endif
        Sales Summary by {{ ucfirst($groupBy) }}
    </h2>
    
    <table>
        <thead>
            <tr>
                <th>{{ ucfirst($groupBy) }}</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Sales</th>
                <th class="text-right">Avg. Order</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $data)
            <tr>
                <td>{{ $data->label }}</td>
                <td class="text-right">{{ number_format($data->order_count) }}</td>
                <td class="text-right">PHP {{ number_format($data->total_amount, 2) }}</td>
                <td class="text-right">
                    PHP {{ number_format($data->order_count > 0 ? $data->total_amount / $data->order_count : 0, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">No sales data available for this period</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Order Details Section -->
    <div class="page-break"></div>
    <div class="header" style="padding: 10px;">
        @if(!empty($storeBranding['logo_binary']))
            <img src="data:{{ $storeBranding['logo_mime_type'] }};base64,{{ base64_encode($storeBranding['logo_binary']) }}" 
                 alt="{{ $store->name }}" style="height: 20px; vertical-align: middle;">
        @endif
        <span style="vertical-align: middle;">{{ $store->name }} - Order List</span>
        <span class="report-badge">PRO</span>
    </div>
    
    <h2>
        @if(!empty($storeBranding['logo_binary']))
            <img src="data:{{ $storeBranding['logo_mime_type'] }};base64,{{ base64_encode($storeBranding['logo_binary']) }}" 
                 alt="{{ $store->name }}" style="height: 20px; margin-right: 5px; vertical-align: middle;">
        @endif
        Order List ({{ $orderDetails->count() }} orders)
    </h2>
    
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Items</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orderDetails as $order)
            <tr>
                <td>{{ $order->order_id }}</td>
                <td>{{ $order->order_date_formatted }}</td>
                <td>{{ $order->cashier_name }}</td>
                <td class="text-right">{{ $order->item_count }}</td>
                <td class="text-right">PHP {{ number_format($order->total_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">No orders found for this period</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold; background-color: #f2f2f2;">Total Sales</td>
                <td class="text-right" style="font-weight: bold; background-color: #f2f2f2;">PHP {{ number_format($totalSales, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    
    <!-- Product Performance Section -->
    <div class="page-break"></div>
    <div class="header" style="padding: 10px;">
        @if(!empty($storeBranding['logo_binary']))
            <img src="data:{{ $storeBranding['logo_mime_type'] }};base64,{{ base64_encode($storeBranding['logo_binary']) }}" 
                 alt="{{ $store->name }}" style="height: 20px; vertical-align: middle;">
        @endif
        <span style="vertical-align: middle;">{{ $store->name }} - Product Performance</span>
        <span class="report-badge">PRO</span>
    </div>

    <div class="summary-row">
        <div class="summary-box">
            <div class="summary-label">Products Sold</div>
            <div class="summary-value">{{ number_format($totalProducts) }}</div>
        </div>
        
        <div class="summary-box">
            <div class="summary-label">Total Units</div>
            <div class="summary-value">{{ number_format($totalUnits) }}</div>
        </div>
        
        <div class="summary-box">
            <div class="summary-label">Average Per Product</div>
            <div class="summary-value">
                PHP {{ number_format($totalProducts > 0 ? $totalSales / $totalProducts : 0, 2) }}
            </div>
        </div>
    </div>

    <h2>
        @if(!empty($storeBranding['logo_binary']))
            <img src="data:{{ $storeBranding['logo_mime_type'] }};base64,{{ base64_encode($storeBranding['logo_binary']) }}" 
                 alt="{{ $store->name }}" style="height: 20px; margin-right: 5px; vertical-align: middle;">
        @endif
        Product Sales Detail ({{ $productDetails->count() }} products)
    </h2>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Category</th>
                <th class="text-right">Qty Sold</th>
                <th class="text-right">Avg Price</th>
                <th class="text-right">Total</th>
                <th class="text-right">% of Sales</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productDetails as $product)
            <tr>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->category_name }}</td>
                <td class="text-right">{{ number_format($product->quantity_sold) }}</td>
                <td class="text-right">PHP {{ number_format($product->average_price, 2) }}</td>
                <td class="text-right">PHP {{ number_format($product->total_amount, 2) }}</td>
                <td class="text-right">
                    {{ number_format(($product->total_amount / $totalSales) * 100, 1) }}%
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">No product sales data available for this period</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold; background-color: #f2f2f2;">
                    Total
                </td>
                <td class="text-right" style="font-weight: bold; background-color: #f2f2f2;">
                    {{ number_format($totalUnits) }}
                </td>
                <td style="background-color: #f2f2f2;"></td>
                <td class="text-right" style="font-weight: bold; background-color: #f2f2f2;">
                    PHP {{ number_format($totalSales, 2) }}
                </td>
                <td class="text-right" style="font-weight: bold; background-color: #f2f2f2;">
                    100%
                </td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
        <p style="color: {{ $storeBranding['accent_color'] ?? '#4e73df' }};">
            {{ $store->name }} - Inventory Management System - Professional Plan
        </p>
    </div>
</body>
</html>