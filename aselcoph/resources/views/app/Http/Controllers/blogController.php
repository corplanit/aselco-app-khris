<?php

namespace App\Http\Controllers;

use App\Models\blogModel;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class blogController extends Controller
{
    public static function create(Request $request){
        return view('pages.admin.cms.forms.create');
    }

    public static function store(Request $request){
        $request->validate([
            'inp_title' => 'required|string|max:255',
            'inp_category' => 'nullable|array',
            'inp_content' => 'required|string',
            'inp_comments' => 'nullable|string',
            'inp_thumbnail' => 'required|image|max:2048',
        ]);
    
        if ($request->hasFile('inp_thumbnail')) {
            $file = $request->file('inp_thumbnail');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename, 'public');
    
            blogModel::create([
                'post_title' => $request->input('inp_title'),
                'post_category' => json_encode($request->input('inp_category', [])),
                'post_content' => $request->input('inp_content'),
                'post_comment' => $request->input('inp_comments'),
                'post_isActive' => 'on',
                'post_thumbnail' => 'storage/' . $path,
                'post_by' => 1 // Ideally get this from auth: auth()->id()
            ]);
    
            return redirect('/post/list')->with('success', 'Post created successfully!');
        }
    
        return redirect('/404')->withErrors(['Thumbnail upload failed.']);
    }
}
