<!DOCTYPE html>
<html>
<head>
    <title>Feriados</title>
</head>
<body>
<h1>Feriados em Portugal</h1>
<ul>
    @foreach ($holidays as $holiday)
        <li>{{ $holiday['date'] }} - {{ $holiday['name'] }}</li>
    @endforeach
</ul>
</body>
</html>
