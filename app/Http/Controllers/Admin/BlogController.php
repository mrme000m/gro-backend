<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Blog;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function __construct(
        private Blog $blog
    ) {}

    /**
     * Display a listing of blogs.
     */
    public function index(Request $request): View|Factory|Application
    {
        $queryParam = [];
        $search = $request['search'];
        
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $blogs = $this->blog->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%")
                      ->orWhere('content', 'like', "%{$value}%")
                      ->orWhere('category', 'like', "%{$value}%");
                }
            })->orderBy('id', 'desc');
            $queryParam = ['search' => $request['search']];
        } else {
            $blogs = $this->blog->orderBy('id', 'desc');
        }
        
        $blogs = $blogs->paginate(15)->appends($queryParam);
        
        return view('admin-views.blog.index', compact('blogs', 'search'));
    }

    /**
     * Show the form for creating a new blog.
     */
    public function create(): View|Factory|Application
    {
        return view('admin-views.blog.create');
    }

    /**
     * Store a newly created blog.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $blog = new Blog();
        $blog->title = $request->title;
        $blog->slug = Blog::generateSlug($request->title);
        $blog->content = $request->content;
        $blog->excerpt = $request->excerpt;
        $blog->category = $request->category;
        $blog->tags = $request->tags ? explode(',', $request->tags) : [];
        $blog->meta_title = $request->meta_title;
        $blog->meta_description = $request->meta_description;
        $blog->status = $request->has('status');
        $blog->published_at = $request->published_at ? $request->published_at : now();
        $blog->author_id = auth('admin')->id();

        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/blog', $imageName);
            $blog->featured_image = $imageName;
        }

        $blog->save();

        Toastr::success(translate('Blog created successfully!'));
        return redirect()->route('admin.blog.index');
    }

    /**
     * Show the form for editing a blog.
     */
    public function edit($id): View|Factory|Application
    {
        $blog = $this->blog->findOrFail($id);
        return view('admin-views.blog.edit', compact('blog'));
    }

    /**
     * Update the specified blog.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $blog = $this->blog->findOrFail($id);
        $blog->title = $request->title;
        $blog->slug = Blog::generateSlug($request->title, $id);
        $blog->content = $request->content;
        $blog->excerpt = $request->excerpt;
        $blog->category = $request->category;
        $blog->tags = $request->tags ? explode(',', $request->tags) : [];
        $blog->meta_title = $request->meta_title;
        $blog->meta_description = $request->meta_description;
        $blog->status = $request->has('status');
        $blog->published_at = $request->published_at ? $request->published_at : $blog->published_at;

        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($blog->featured_image) {
                Storage::delete('public/blog/' . $blog->featured_image);
            }
            
            $image = $request->file('featured_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/blog', $imageName);
            $blog->featured_image = $imageName;
        }

        $blog->save();

        Toastr::success(translate('Blog updated successfully!'));
        return redirect()->route('admin.blog.index');
    }

    /**
     * Remove the specified blog.
     */
    public function destroy($id): RedirectResponse
    {
        $blog = $this->blog->findOrFail($id);
        
        // Delete featured image
        if ($blog->featured_image) {
            Storage::delete('public/blog/' . $blog->featured_image);
        }
        
        $blog->delete();

        Toastr::success(translate('Blog deleted successfully!'));
        return redirect()->route('admin.blog.index');
    }

    /**
     * Toggle blog status.
     */
    public function status($id): RedirectResponse
    {
        $blog = $this->blog->findOrFail($id);
        $blog->status = !$blog->status;
        $blog->save();

        Toastr::success(translate('Blog status updated successfully!'));
        return back();
    }
}
