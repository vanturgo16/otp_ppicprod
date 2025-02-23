<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode</title>
    <style>
        .barcode-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="barcode-container">
        @for ($i = 1; $i <= $qty; $i++)
            <div>
                {!! $barcode !!}
                <p>{!! $lotNumber !!} - {{ $i }}</p>
            </div>
        @endfor
    </div>
</body>
</html>
