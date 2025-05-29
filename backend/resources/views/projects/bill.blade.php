<!DOCTYPE html>
<html>
<head>
    <title>Project Bill - {{ $project->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .bill-info { margin-bottom: 20px; }
        .project-details { margin-bottom: 30px; }
        .total { font-weight: bold; font-size: 1.2em; }
        .footer { margin-top: 50px; text-align: center; font-size: 0.8em; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Project Bill</h1>
        <p>Date: {{ $date }}</p>
        <p>Bill #: {{ str_pad($project->id, 6, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="bill-info">
        <h3>Client Information</h3>
        <p>Client: {{ $project->client->company_name }}</p>
        <p>Email: {{ $project->client->email }}</p>
    </div>

    <div class="project-details">
        <h3>Project Details</h3>
        <p><strong>Name:</strong> {{ $project->name }}</p>
        <p><strong>Description:</strong> {{ $project->description }}</p>
        <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
        <p><strong>Deadline:</strong> {{ $project->deadline }}</p>
    </div>

    <div class="total">
        <p>Total Amount: ${{ number_format($project->price, 2) }}</p>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This is an automatically generated bill.</p>
    </div>
</body>
</html>