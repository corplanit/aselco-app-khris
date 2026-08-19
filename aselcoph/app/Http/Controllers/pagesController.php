<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\postModel;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class pageSController extends Controller
{

    public function index()
    {
        return view('pages.page.index');
    }

    public function api_pages()
    {
        try {
            $pages = MenuItem::with(['menu:id,name', 'parent:id,label'])
                ->select(
                    'id',
                    'menu_id',
                    'parent_id',
                    'label',
                    'icon',
                    'link_type',
                    'custom_url',
                    'route_name',
                    'route_params',
                    'target',
                    'order',
                    'is_active'
                )
                ->orderBy('order')
                ->get()
                ->map(function ($i) {
                    return [
                        'id'           => $i->id,
                        'label'        => $i->label,
                        'icon'         => $i->icon,
                        'link_type'    => Str::slug($i->label),                 // 'route' | 'url'
                        'link'         =>  Str::slug($i->label),
                        'route_params' => $i->route_params,
                        'target'       => $i->target,                    // '_self' | '_blank'
                        'order'        => $i->order,
                        'is_active'    => (bool) $i->is_active,
                        'menu'         => optional($i->menu)->name,       // friendly menu name
                        'parent'       => optional($i->parent)->label,    // friendly parent label
                    ];
                });

            return response()->json($pages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function trash(Request $request)
    {
        $filter = $request->input('filter');

        if ($filter) {
            $pages = postModel::where('post_isDeleted', 0)
                ->where('post_category', 'LIKE', '%"' . $filter . '"%')
                ->get();
        } else {
            $pages = postModel::where('post_isDeleted', 0)->get();
        }

        //return view('cms.post.trash')->with(['pages' => $pages]);
    }

    public static function create()
    {
        return view('cms.create')->with([]);
    }

    public static function content($id)
    {
        $info = postModel::find($id);
        //return view('cms.post.content')->with(['info' => $info]);
    }

    public static function save(Request $request)
    {
        date_default_timezone_set('Asia/Manila');

        $request->validate([
            'inp_title' => 'required|string|max:255',
            'content_blog' => 'required|string',
            'inp_thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'inp_attachment.*' => 'nullable|mimes:pdf|max:5120', // Validate each PDF
            'post_menu' => 'required|string',
        ]);

        // Upload thumbnail
        $file = $request->file('inp_thumbnail');
        $uploadPath = 'uploads/';
        $thumbName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path($uploadPath), $thumbName);
        $uploadThumb = $uploadPath . $thumbName;

        // Upload PDF attachments (optional)
        $pdfPaths = [];

        if ($request->hasFile('inp_attachment')) {
            foreach ($request->file('inp_attachment') as $pdfFile) {
                $pdfName =  time() . '_' . $pdfFile->getClientOriginalName();
                $pdfFile->move(public_path($uploadPath), $pdfName);
                $pdfPaths[] = $uploadPath . $pdfName;
            }
        }

        // Save post
        postModel::create([
            'post_title' => $request->inp_title,
            'post_content' => $request->content_blog,
            'post_isActive' => 'on',
            'post_thumbnail' => $uploadThumb,
            'post_by' => Auth::id() ?? 1,
            'post_menu' => $request->post_menu,
            'post_attachment' => json_encode($pdfPaths),
        ]);

        return redirect()->back()->with('success', 'New blog created successfully.');
    }

    public static function edit($id)
    {
        return view('cms.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        date_default_timezone_set('Asia/Manila');

        $request->validate([
            'inp_title' => 'required|string|max:255',
            'content_blog' => 'required|string',
            'inp_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'inp_attachment.*' => 'nullable|mimes:pdf|max:5120',
            'post_menu' => 'required|string',
        ]);

        $post = postModel::findOrFail($id);

        // 🔹 Step 1: Existing attachments retained
        $existingAttachments = $request->input('existing_attachments', []);
        if (!is_array($existingAttachments)) {
            $existingAttachments = [];
        }

        $mergedAttachments = $existingAttachments;

        // 🔹 Step 2: Add newly uploaded PDFs
        if ($request->hasFile('inp_attachment')) {
            foreach ($request->file('inp_attachment') as $pdfFile) {
                $timestamp = time();
                $filename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $pdfFile->getClientOriginalExtension();
                $newFileName = $filename . '_' . $timestamp . '.' . $extension;
                $pdfFile->move(public_path('uploads/'), $newFileName);
                $mergedAttachments[] = 'uploads/' . $newFileName;
            }
        }

        // 🔹 Step 3: Handle optional thumbnail change
        if ($request->hasFile('inp_thumbnail')) {
            $thumb = $request->file('inp_thumbnail');
            $thumbName = pathinfo($thumb->getClientOriginalName(), PATHINFO_FILENAME) . '_' . time() . '.' . $thumb->getClientOriginalExtension();
            $thumb->move(public_path('uploads/'), $thumbName);
            $post->post_thumbnail = 'uploads/' . $thumbName;
        }

        // 🔹 Step 4: Update post
        $post->update([
            'post_title' => $request->inp_title,
            'post_content' => $request->content_blog,
            'post_menu' => $request->post_menu,
            'post_attachment' => json_encode($mergedAttachments),
        ]);

        return response()->json(['success' => true, 'message' => 'Blog updated successfully.']);
    }

    public function removePdf(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:t_post,post_id',
            'file' => 'required|string',
        ]);

        $post = postModel::findOrFail($request->post_id);
        $attachments = is_array($post->post_attachment) ? $post->post_attachment : json_decode($post->post_attachment, true);

        $updated = array_filter($attachments, fn($item) => $item !== $request->file);

        // Delete the file from disk
        $filePath = public_path($request->file);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $post->post_attachment = json_encode(array_values($updated));
        $post->save();

        return response()->json(['success' => true]);
    }

    public static function trash_bin(Request $request)
    {
        if ($request->has('restore_post')) {
            postModel::where('post_id', $request->input('inp_id'))->update([
                'post_isDeleted' => null,
            ]);
            return redirect('/post/content/' . $request->input('inp_id'))->with(['restore' => 'success']);
        } else {
            postModel::where('post_id', $request->input('inp_id'))->delete();
            return redirect('/post/trash')->with(['status' => $request->input('inp_title')]);
        }
    }

    public function datatable()
    {
        $posts = postModel::with('author:id,name')
            ->where('isDeleted', 0)
            ->select('*');

        return datatables()->of($posts)
            ->addColumn('author_name', fn($post) => $post->author->name ?? 'N/A')
            ->editColumn('created_at', fn($post) => \Carbon\Carbon::parse($post->created_at)->format('M d, Y'))
            ->toJson();
    }
}
