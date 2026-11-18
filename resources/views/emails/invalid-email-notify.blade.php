@extends('layouts.email')

@section('content')
<h3> Invalid Email at {{ now()->toDateTimeString() }}</h3>

<table>
  <thead>
    <tr>
      <th>
        <h3>
          <span>(1/1)</span>
          <span><span title="InvalidEmailException">InvalidEmail</span></span>
        </h3>
        <p>Pessoa: {{ $inscrito->nome_razao ?? '' }} </p>
        <p>Interlab: {{ $interlab->interlab->nome ?? '' }}</p>
        <p>Interlab UID: {{ $interlab->uid ?? '' }}</p>
        <p>User: {{ $user->name ?? '' }} - {{ $user->email ?? '' }} - ID:{{ $user->id ?? '' }} </p>
      </th>
    </tr>
  </thead>
</table>
@endsection
