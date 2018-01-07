@extends('layouts.main')

@section('content')

    <div class="container">
        <div class="show-page">
            <div class="show-date text-right">
                <a href="{{ route('articles.index') }}" class="pull-left"><i class="glyphicon glyphicon-arrow-left"></i> Back </a>

                {{$article['date']}}

            </div>

            <h2 class="title text-center">
                <a href=" {{$article['url']}}">{{$article['title']}}</a>
            </h2>

            <div class="show-img">
                <img src="{{$article['image']}}" alt="{{$article['original_image']}}">
            </div>

            <aside>
                {{$article['description']}}
            </aside>
        </div>
    </div>

@endsection