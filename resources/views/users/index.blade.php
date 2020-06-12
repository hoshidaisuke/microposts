@extends('layouts.app')

@section('content')
    <div class="center jumbotron">
        <div class="text-center">
            <h1>ユーザー一覧</h1>
            @include('users.users')
        </div>
    </div>
@endsection