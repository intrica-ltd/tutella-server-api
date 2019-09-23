<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
</head>
<style>
    body {
        font-family: Source Sans Pro, sans-serif;
    }

    h1 {
        font-size: 14px;
        font-weight: lighter;
    }

    h2 {
        font-size: 10px;
        font-weight: 400
    }

    p {
        font-size: 10px;
        font-weight: lighter;
        margin-block-end: 0px;
        margin-block-start: 0px;
    }

    strong {
        font-weight: 400;
    }

    .margin-top-50 {
        margin-top: 50px;
    }

    .margin-top-30 {
        margin-top: 30px;
    }

    .column {
        float: left;
        width: 25%;
    }

    .row:after {
        content: "";
        display: table;
        clear: both;
    }

    tr, th {
        border-bottom: 1px solid #2c2c2c;
        padding-bottom: 5px;
        height: 30px;
    }

    td,
    th {
        display: table-cell;
        vertical-align: inherit;
    }

    th {
        font-weight: 400;
        font-size: 12px;
        text-align: left;
    }

    tr, td {
        border-bottom: 0.5px solid #dadada;
        font-weight: 400;
        font-size: 10px;
        text-align: left;
        min-height: 30px !important;
    }

    section.footer {
        margin-top: 30px;
        width: 30%;
        float: right;
    }

    .footer, .column-half {
        float: left;
        width: 50%;
    }

    .footer.row:after {
        content: "";
        display: table;
        clear: both;
    }
</style>
<body>
    <section>
    <img src="https://login.tutella.io/static/media/logo_tutella_text.67268a15.svg" alt="">
        <h1>Tutella, 7th Floor Dashwood House, Old Broad Street, London, United Kingdom EC2M 1QS, VAT: 305 2920 33</h1>
    </section>
    <section class="row margin-top-30">
        <div class="column">
            <h2>BILLED TO</h2>
            <p>{{$school_data['school_name']}}</p>
            <p>{{$school_data['address']}}</p>
            <p>{{$school_data['email']}}</p>
        </div>
        <div class="column">
            <h2>INVOICE NUMBER</h2>
            <p>{{$payment->invoice_number}}</p>
        </div>
        <div class="column">
            <h2>ISSUED DATE</h2>
            <p>{{date('d F, Y', strtotime($payment->created_at))}}</p>
        </div>
        <div class="column">
            <h2>FOR PERIOD</h2>
            <p>{{$payment->month.'.'.$payment->year}}</p>
        </div>
    </section>

    <section class="margin-top-50">
        <p>
            <strong>{{date('F', mktime(0, 0, 0, $payment->month, 10))}} summary for {{$school_data['school_name']}}</strong>
        </p>
        <table style="width:100%; border-collapse: collapse;">
            <tr>
                <th>Description</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Amount</th>
            </tr>
            @foreach($invoices as $invoice)
            <tr>
                <td style="height:30px;">Tutella subscription for {{$invoice->name}} {{$invoice->min_users}} - {{$invoice->max_users}} users/month.<br>{{date('d', strtotime($invoice->start_date))}} - {{date('d', strtotime($invoice->end_date))}} {{date('F', strtotime($invoice->end_date))}} subscription.</td>
                <td style="height:30px;">£{{$invoice->price}}</td>
                <td style="height:30px;">{{$invoice->qty}}</td>
                <td style="height:30px;">£{{$invoice->value}}</td>
            </tr>
            @endforeach
            <tr>
                <td style="border-bottom: none;"></td>
                <td style="border-bottom: none;"></td>
                <td style="text-align:right; border-bottom: none; padding-right:30px">Subtotal<br>VAT(20%)<br>Payment</td>
                <td style="border-bottom: none;">£{{$subtotal}}<br>£{{$vat}}<br>£{{$payed_total}}</td>
            </tr>
        </table>
    </section>
</body>

</html>