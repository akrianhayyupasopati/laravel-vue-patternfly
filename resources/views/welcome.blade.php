<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel</title>
    
    <link href="{{ asset("/css/app.css") }}" rel="stylesheet">

</head>
<body>
    <div class="container">
        <pf-layout id="app" :icons="true" :disabled="true"></pf-layout>
    </div>
<script src="/js/app.js"></script>
</body>
</html>