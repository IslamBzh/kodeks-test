<p>Получена новая заявка:</p>

<ul>
    @foreach($data as $key => $value)
        <li><strong>{{ $key }}:</strong> {{ $value }}</li>
    @endforeach
</ul>
