<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

        .receipt-container {
            max-width: 800px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f30051;
        }

        .header h1 {
            margin: 0;
            color: #f30051;
        }

        .company-info {
            text-align: center;
            margin-bottom: 40px;
        }

        .company-info p {
            margin: 5px 0;
        }

        .company-info .bold {
            font-weight: bold;
        }

        .receipt-details {
            margin-bottom: 20px;
        }

        .receipt-details h2 {
            color: #f30051;
            margin-bottom: 10px;
        }

        .receipt-details p {
            margin: 5px 0;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .item-table th,
        .item-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .item-table th {
            background-color: #f30051;
            color: #ffffff;
            text-align: left;
        }

        .total {
            text-align: right;
            padding-top: 20px;
        }

        .total h3 {
            margin: 0;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .paid {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            border: 2px solid #f30051;
            color: #f30051;
            font-size: 18px;
            font-weight: bold;
        }

    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Receipt</h1>
        </div>
        <div class="receipt-details">
            <h2>Receipt Details</h2>
            <p><strong>Receipt Number:</strong> PP-INV-{{ $receiptData['receipt_date'].'-'. $receiptData['receipt_no']}}</p>
            <p><strong>Receipt Date:</strong> {{$receiptData['receipt_date']}}</p>
            <p><strong>Client Name:</strong> {{$receiptData['user_name']}}</p>
        </div>
        <div class="paid">PAID</div>
        <table class="item-table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example row (duplicate this block for each item) -->
                <tr>
                    <td>{{$receiptData['plan_name']}}</td>
                    <td>R {{$receiptData['amount']}}</td>
                    <td>R {{$receiptData['amount']}}</td>
                </tr>
                <!-- End of example row -->
            </tbody>
        </table>
        <div class="total">
            <h3>Total Amount Paid: R {{$receiptData['amount']}}</h3>
        </div>
        <div class="footer">
            <p>Thank you for choosing PocketProperty!</p>
        </div>
        <div class="company-info">
            <p class="bold">PocketProperty</p>
            <p>Claremont</p>
            <p>Cape Town</p>
            <p><a style="color:#000;text-decoration:none" href="mailto:services@pocketproperty.app">services@pocketproperty.app</a></p>
            <p><a style="color:#000;text-decoration:none" href="tel:27 79 338 9178">+27 79 338 9178</a></p>
        </div>
    </div>
</body>
</html>
