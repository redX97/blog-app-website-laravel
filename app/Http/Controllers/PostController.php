<?php

namespace App\Http\Controllers;


use App\Http\Requests\PostNewRequest;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
{
    $this->middleware('auth', ['except' => ['index', 'show']]);
}

    public function index()
    {
        //$posts = Post::all();
        //$posts = Post::orderBy('id', 'desc')->get();
        $posts = Post::orderBy('id', 'desc')->paginate(5);
        return view('posts.index')->with('posts', $posts);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostNewRequest $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999'
        ]);

        //handle File upload
        if ($request->hasFile('cover_image')) {

        //getting the original file
        $fileNameWithExt = $request->file('cover_image')->getClientOriginalName();
        //getting only name of the file
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
        //getting only extention of the file
        $extention = $request->file('cover_image')->getClientOriginalExtension();
        //file name to store
        $fileNameToStore = $fileName . '-' . time() . '_' . $extention;
        //uploading the file
        $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);

        }   else {
            $fileNameToStore = 'noimage.jpg';
        }
        $post = new Post;
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->cover_image = $fileNameToStore;
        $post->user_id = auth()->user()->id;
        $post->save();

        return redirect('/posts')->with('success', 'Post Created');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        return view('posts.show')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);
        if (Auth::user()->id !== $post->user_id) {
            return view('posts')->with('success', 'Unauthorized Page');
        }
        return view('posts.edit')->with('post', $post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->hasFile('cover_image')) {

            //getting the original file
            $fileNameWithExt = $request->file('cover_image')->getClientOriginalName();
            //getting only name of the file
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //getting only extention of the file
            $extention = $request->file('cover_image')->getClientOriginalExtension();
            //file name to store
            $fileNameToStore = $fileName . '-' . time() . '_' . $extention;
            //uploading the file
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
        } else {
            $fileNameToStore = 'noimage.jpg';
        }
        $post = Post::find($id);
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->cover_image = $fileNameToStore;
        $post->save();

        return redirect('/posts')->with('success', 'Post Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
//confirmation delete function and view reload

//    public function confirmDelete($id)
//    {
//        $post = Post::find($id);
//        return view('posts.delete')->with('post', $post);
//    }

    public function destroy($id)
    {
        $post = Post::find($id);

        if (Auth::user()->id !== $post->user_id) {
            return view('posts')->with('success', 'Unauthorized Page');
        }

        if ($post->cover_image != 'noimage.jpg') {
            // Delete Image
            Storage::delete('public/cover_images/' . $post->cover_image);
        }
        $post->delete();
        return redirect('/posts')->with('success', 'Post Deleted');
    }
}

