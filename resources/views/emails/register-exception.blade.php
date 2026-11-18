@extends('layouts.email')

@section('content')
<h3> Register error with email: {{ $content['email'] ?? '' }}</h3>

<table>
  <thead>
    <tr>
      <th>
        <h3>
          <span>(1/1)</span>
          <span><span title="InvalidEmailRegisterException">InvalidEmailRegisterException</span></span>
        </h3>
        <p>{!! $content['request'] ?? '' !!}</p>
        <p>URL: {{ $content['url'] ?? '' }}</p>
        <p>IP: {{ $content['ip'] ?? '' }}</p>
        <p>Pessoa: {{ $content['pessoa_uid'] ?? '' }}</p>
      </th>
    </tr>
  </thead>
</table>
@endsection
