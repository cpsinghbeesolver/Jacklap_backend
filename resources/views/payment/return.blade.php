<!DOCTYPE html>
<html>
<head>
    <title>Payment Status</title>
</head>
<body>

@if($isPaid)
    <h2>Payment Successful</h2>
    <p>Your booking has been paid successfully.</p>
@else
    <h2>Payment Failed</h2>
    <p>Please try again.</p>
@endif

</body>
</html>