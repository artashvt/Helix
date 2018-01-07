@extends('layouts.main')

@section('content')
    <div class="container">

        <div class="edit-page">
            <div class="clearfix text-center">
                <a href="{{ route('articles.index') }}" class="pull-left back-txt"><i class="glyphicon glyphicon-arrow-left"></i> Back</a>
                <h2>Edit Article</h2>

            </div>

            <form method="POST" action="{{ route('articles.update',$article->id) }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                {{ method_field('PATCH') }}

                <div class="form-group {{ $errors->has('title') ? ' has-error' : '' }}">
                    <label for="edit-title">Title</label>
                    <input type="text" name="title" id="edit-title" class="form-control" value="{{old('title') ? old('title') : $article['title']}}">
                    @if ($errors->has('title'))
                        <span class="help-block">
                            <strong>{{ $errors->first('title') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                    <label for="edit-descript">Description</label>
                    <textarea name="description" id="edit-descript" class="form-control">{{old('description') ? old('description') : $article['description']}}</textarea>
                    @if ($errors->has('description'))
                        <span class="help-block">
                            <strong>{{ $errors->first('description') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group {{ $errors->has('image') ? ' has-error' : '' }}">
                    <label for="edit-image">Image</label>
                    <div>
                        <label class="btn-bs-file btn btn-default" id="edit-image-label" style="background-image: url({{$article['image']}});">
                            <input name="image" type="file" id="edit-image" accept=".jpg, .jpeg, .png">
                        </label>
                    </div>
                    @if ($errors->has('image'))
                        <span class="help-block">
                            <strong>{{ $errors->first('image') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group {{ $errors->has('date') ? ' has-error' : '' }}">
                    <label for="edit-date">Date</label>
                    <div class='input-group date' id='datetimepicker1'>
                        <span class="input-group-addon">
                          <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                        <input name="date" type="text" id="edit-date" class="form-control" value="{{old('date') ? old('date') : date('Y-m-d H:i', strtotime($article['date']))}}"/>
                    </div>
                    @if ($errors->has('date'))
                        <span class="help-block">
                            <strong>{{ $errors->first('date') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group {{ $errors->has('url') ? ' has-error' : '' }}">
                    <label for="edit-url">Url</label>
                    <input name="url" type="url" id="edit-url" class="form-control" value="{{old('url') ? old('url') : $article['url']}}">
                    @if ($errors->has('url'))
                        <span class="help-block">
                            <strong>{{ $errors->first('url') }}</strong>
                        </span>
                    @endif
                </div>


                <div class="text-center" style="margin-top:15px;">
                    <button type="submit" class="btn btn-primary btn-sx"><i class="glyphicon glyphicon-save"></i> Save </button>
                </div>
            </form>
        </div>
    </div>


@endsection