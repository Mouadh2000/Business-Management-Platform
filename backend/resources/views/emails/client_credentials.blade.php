<!DOCTYPE html>
<html>
<head>
    <title>Your Account Credentials</title>
</head>
<body>
    <h1>Your Account Information</h1>
    <p>Here are your login credentials:</p>
    
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Password:</strong> {{ $password }}</p>
    
    <p>Please keep this information secure.</p>
    
    <p>Thank you,<br>
    {{ config('app.name') }}</p>
</body>
</html>