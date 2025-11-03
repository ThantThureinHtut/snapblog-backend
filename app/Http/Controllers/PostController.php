<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Image;
use ImageKit\ImageKit;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function posts(Request $request){
        $post = Post::select('posts.*' , 'users.name' , 'users.email' ,'users.second_name' , 'users.profile_photo_path')
        ->join('users' , 'users.id' , '=' , 'posts.user_id')
        ->where(function($query) use ($request){
            if($request->search){
                $query->where('posts.title' , 'like' , '%'.$request->search.'%');
            }
        })
        ->get();
        $images = Image::select('images.*' , 'posts.id as post_id')
        ->join('posts' , 'posts.id' , '=' , 'images.post_id')
        ->get()->groupBy('post_id');
        

        return response()->json([
            'posts' => $post,
            'images' => $images
        ]);
    }
    public function store(Request $request){
        $this->validateFun($request);
        $post = Post::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        $imageKit = new ImageKit(
            env('IMAGEKIT_PUBLIC_KEY'),
            env('IMAGEKIT_PRIVATE_KEY'),
            env('IMAGEKIT_URL_ENDPOINT')
        );

        foreach($request->file('images') as $img){
            $upload = $imageKit->uploadFile([
            'file' => fopen($img->getPathname(), 'r'),
            'fileName' => $img->getClientOriginalName(),
            'folder' => '/snapblog_images/' // 👈 store in your folder
            ]);

            $image = Image::create([
                'name' => $img->getClientOriginalName(),
                'image_url' => $upload->result->url,
                'post_id' => $post->id,
                'file_id' => $upload->result->fileId
            ]);
        }
    }

    public function delete(Request $request){
       $post = Post::find($request->post_id);
       $image = Image::where('post_id' , $request->post_id)->get();
       logger($image);
    }
    private function validateFun($request){
        return $request->validate([
            'title' => 'required|string|max:255',
            'images' => 'required|max:2048',
            'description' => 'required|string',
        ]);
    }
}
