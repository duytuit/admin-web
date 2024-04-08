<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap-theme.min.css') }}" />
</head>
<body>
    <div class="container">
         {!! $view !!}
    </div>
</body>
{{-- <style type="text/css">
    @media print {
        .pagebreak {
            page-break-before: always;
            clear: both;
            page-break-after: always;
        }
        tr td{
            page-break-inside: avoid;
        }
        /* page-break-after works, as well */
    }
   .content_text{
    line-height: 1.8;
    margin-bottom: 5px;
   }
    * {
        font-size: 10px;
    }

    @page {
        margin: 0px;
    }

    body {
        margin: 0px;
    }


    * {
        font-family: sans-serif !important;
    }

    .padding-tb {
        padding-top: 1px;
        padding-bottom: 1px;
        padding-left: 5px;
    }

    .text-building {
        padding-left: 5px;
    }

    td.text-header {
        font-weight: bold;
        margin-bottom: 0px;
        width: 40%;
    }

    p.text-invoice {
        text-transform: uppercase;
        font-weight: bold;
        font-size: 18px;
    }

    p.date-invoice {
        font-style: italic;
    }

    .list_content_text {
        padding-left: 10px !important;
        width: 100%;
    }

    a {
        color: #fff;
        text-decoration: none;
    }

    table {
        font-size: 15px;
    }

    tfoot tr td {
        font-weight: bold;
        font-size: 15px;
    }

    .list_content_text tbody td {
        padding-left: 25px;
    }

    .invoice table {
        margin: 15px;
    }

    .invoice h3 {
        margin-left: 15px;
    }

    .information table {
        padding-left: 60px;
        padding-right: 60px;
        padding-top: 20px;
    }

    .footer {
        display: inline-block;
        width: 100%;
        margin: 20px 0;
    }

    .list_service table {
        width: 100%
    }

    .list_service table td {
        border-collapse: collapse;
        border: 1px solid black;
    }

    .list_service thead td {
        font-weight: bold;
    }

    .list_service td {
        padding: 10px;
    }
    .img_logo_rivera{
       width: 100px;
       height: 70px;;
    }
</style> --}}
</html>
