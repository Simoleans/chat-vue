<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Post;
use App\Comment;
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'pusher', 'middleware' => ['auth']], function() {
  Route::get('posts/{id}', function($id){
    //dd($id);
    $post = Post::findOrfail($id);

    return view('chat',['post' => $post]);
  });

  Route::post('posts/{id}', function($id, \Illuminate\Http\Request $request){

      $comment = new Comment([
        'comment' => $request->comment,
        'user_id' => auth()->user()->id,
        'post_id' => $id
      ]);

      $comment->save();

      broadcast(new \App\Events\FireComment($comment))->toOthers();
  })->name('comments.create');

  Route::get('comments/{id}', function($id){
    $comments= Comment::where('post_id',$id)->with('user')->get();

    return response()->json($comments);
  })->name('comments.list');
});
