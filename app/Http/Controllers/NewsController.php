<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::where('is_active', true)->with('attachments')->orderBy('created_at', 'desc')->paginate(20);
        
        return view('home', compact('news'));
    }
}
