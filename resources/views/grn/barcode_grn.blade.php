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
            justify-content: flex-start; /* Pusatkan barcode horizontal */
            gap: 20px; /* Jarak antara barcode */
        }
    </style>
</head>
<body>

{{-- <div class="barcode-container">
@php
    $counter = 1;
@endphp
 @while ($counter <= $qty)   
        <div>
            {!! $barcode !!}
            <p>{!! $lot_number !!} - {{ $counter }}</p>
        </div>
        @php
        $counter++; 
    @endphp
@endwhile
</div> --}}
<div class="barcode-container">
    @php
        $counter = 1;
    @endphp
    @while ($counter <= $qty)   
        <div>
            {!! $barcode !!}
            <p>{!! $lot_number !!} - {{ $counter }}</p>
        </div>
        @php
        $counter++;
        @endphp
    @endwhile
    </div>
    

</body>
</html>
