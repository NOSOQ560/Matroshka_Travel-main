<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
</head>
<body>
<h1>Hello, {{ $userName }}</h1>
<p>Your OTP code is: <strong>{{ $otp }}</strong></p>
<p>This code is valid for {{ $otpTime }} minutes.</p>
</body>
</html>
