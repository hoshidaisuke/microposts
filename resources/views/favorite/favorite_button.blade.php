@if (Auth::user()->is_favorite($micropost->id))
    {{-- お気に入り登録外すボタンのフォーム --}}
    {!! Form::open(['route' => ['favorites.unfavorite', $micropost->id], 'method' => 'delete']) !!}
        {!! Form::submit('お気に入り解除', ['class' => "btn btn-danger btn-block"]) !!}
    {!! Form::close() !!}
@else
    {{-- お気に入り登録ボタンのフォーム --}}
    {!! Form::open(['route' => ['favorites.favorite', $micropost->id]]) !!}
        {!! Form::submit('お気に入り登録', ['class' => "btn btn-primary btn-block"]) !!}
    {!! Form::close() !!}
@endif
