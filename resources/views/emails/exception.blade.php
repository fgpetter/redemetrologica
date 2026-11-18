@extends('layouts.email')

@section('content')
<h3>{{ $content['message'] ?? '' }}</h3>

<table>
  <thead>
    <tr>
      <th>
        <h3>
          <span>(1/1)</span>
          <span><span title="ErrorException">ErrorException</span></span>
        </h3>
        <p>{{ $content['message'] ?? '' }}</p>
        <p> <b>$request: </b> {{ $content['body'] ?? '' }}</p>
        <p>URL: {{ $content['url'] ?? '' }}</p>
        <p>IP: {{ $content['ip'] ?? '' }}</p>
        <p>User: {{ $content['user'] ?? '' }}</p>
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
          <span>in <span
            title="{{ $content['file'] ?? '' }}"><strong>{{ $content['file'] ?? '' }}</strong>
            line {{ $content['line'] ?? '' }}</span></span>
      </td>
    </tr>
    @foreach ($content['trace'] ?? [] as $value)
      <tr>
        <td>
          at <span><span
                title="{{ $value['class'] ?? '' }}">{{ basename($value['class'] ?? '') }}</span></span><span>-></span><span>{{ $value['function'] ?? '' }}</span>(<span></span>)<span>in <span
                title=""><strong>{{ $value['file'] ?? '' }}</strong> line
                {{ $value['line'] ?? '' }}</span>
              </span>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
