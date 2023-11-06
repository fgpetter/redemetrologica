<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Edita post
     *
     * @param Post $post
     * @return View
     **/
    public function edit(Post $post)
    {
        dd($post);
    }
}
