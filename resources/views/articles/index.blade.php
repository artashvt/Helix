@extends('layouts.main')

@section('content')
    <div class="container-fluid">
        <div class="article-box">
            <a href="{{ route('articles.create') }}" class="btn btn-success pull-right" style="margin:20px 0;"><i class="glyphicon glyphicon-plus"></i> Create New Article</a>
            @if(!count($articles))
                <h1 class="text-center">There is no data to display</h1>
            @else
                <div class="article-option">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close"><i class="glyphicon glyphicon-remove"></i> </a>
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-inbox table-hover">
                            <thead>
                            <tr class="unread">
                                <th class="dont-show">Title</th>
                                <th>Description</th>
                                <th>Image</th>
                                <th>Date</th>
                                <th>Url</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($articles as $article)
                                <tr>
                                    <td>
                                        <div class="element-to-truncate" title="{{$article['title']}}">{{$article['title']}}</div>
                                    </td>
                                    <td>
                                        <div class="element-to-truncate" title="{{$article['description']}}">{{$article['description']}}</div>
                                    </td>
                                    <td>
                                        <img src="{{$article['image']}}" alt="{{$article['original_image']}}" class="img-responsive main-img">
                                    </td>
                                    <td>
                                        <div class="date-title">{{$article['date']}}</div>
                                    </td>
                                    <td>
                                        <a class="url-title" title="{{$article['url']}}" href="{{$article['url']}}" target="_blank">{{$article['url']}}</a>
                                    </td>
                                    <td>
                                        <span class="btn-group btn-list">
                                            <a href="{{ route('articles.show',$article->id) }}" data-toggle="tooltip" data-placement="top" title="Show" class="btn btn-info btn-xs">
                                                <i class="glyphicon glyphicon-eye-open"></i>
                                            </a>
                                            <a href="{{ route('articles.edit',$article->id) }}" data-toggle="tooltip" data-placement="top" title="Edit" class="btn btn-primary btn-xs">
                                                <i class="glyphicon glyphicon-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('articles.destroy',$article->id) }}" class="form-del-article">
                                                {{csrf_field()}}
                                                {{ method_field('DELETE') }}
                                                <button class="btn btn-danger btn-xs  btn-del-article" data-toggle="tooltip" data-placement="top" title="Delete">
                                                    <i class="glyphicon glyphicon-remove"></i>
                                                </button>
                                            </form>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {{ $articles->links() }}
            @endif
        </div>
    </div>
@endsection

