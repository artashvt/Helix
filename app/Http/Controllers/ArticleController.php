<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Article;

class ArticleController extends Controller
{

	/**
	 * @var int
	 */
    protected $perPage = 10;

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
    public function index()
    {
    	$articles = Article::orderBy('date','desc')->paginate($this->perPage);
		return view('articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		return view('articles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$rules = [
			'title' => 'required',
			'description' => 'required',
			'date' => 'required|date_format:"Y-m-d H:i"',
			'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
			'url' => 'required|url',
		];
		$request->validate($rules);
		$requestData = $request->all();
		$requestData['image'] = '/img/default.png';
		$requestData['original_image'] = '/img/default.png';
		if ($request->hasFile('image')) {
			$path ='/news_images/'.date('Y').'/'.date('m').'/'.date('d') . '/';
			if(!File::exists($path)) {
				File::makeDirectory(public_path($path), $mode = 0777, true, true);
			}
			$filename =  microtime(true). '.' . request()->image->getClientOriginalExtension();
			request()->image->move(public_path($path), $filename);
			$requestData['image'] = $path . $filename;
			$requestData['original_image'] = $path . $filename;
		}

		Article::create($requestData);
		return redirect()->route('articles.index')->with('success','Article created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
		$article = Article::find($id);
		return view('articles.show',compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
		$article = Article::find($id);
		return view('articles.edit',compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$article = Article::find($id);
    	$rules = [
			'title' => 'required',
			'description' => 'required',
			'date' => 'required|date_format:"Y-m-d H:i"',
			'url' => 'required|url',
		];
		if ($request->has('image')) {
			$rules['image'] = 'image|mimes:jpeg,jpg,png|max:2048';
		}

		$request->validate($rules);
		$requestData = $request->all();
		if ($request->hasFile('image')) {
			$filename = basename($article['image']);
			$path = str_replace($filename,'',$article['image']);
			request()->image->move(public_path($path), $filename);
			unset($requestData['image']);
		}

		$article->update($requestData);
		return redirect()->route('articles.index')->with('success','Article updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		Article::find($id)->delete();
		return redirect()->route('articles.index')->with('success','Article deleted successfully.');
    }
}
