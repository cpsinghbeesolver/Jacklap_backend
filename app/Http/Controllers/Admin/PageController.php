<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $pages = Page::orderBy('created_at', 'DESC');

            return DataTables::eloquent($pages)
                ->addIndexColumn()

                ->editColumn('is_active', function ($row) {
                    return $row->getAttributes()['is_active']
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">In-Active</span>';
                })

                ->addColumn('actions', function ($row) {
                    return '
                        <a href="' . route('page.view', $row->id) . '" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>
                        <a href="' . route('page.edit', $row->id) . '" class="btn btn-sm btn-outline-primary">
                            <i class="ri-edit-2-line"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger delete-page"
                            data-id="' . $row->id . '">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    ';
                })

                ->rawColumns(['is_active', 'actions'])
                ->make(true);
        }

        return view('content.page.list');
    }

    public function create()
    {
        return view('content.page.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'             => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:pages,slug',
            'content'           => 'nullable|string',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
            'is_active'         => 'required|boolean',
        ]);

        $data = $request->all();
        $data['slug'] = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        // Ensure slug uniqueness even if auto-generated from a duplicate title
        $originalSlug = $data['slug'];
        $count = 1;
        while (Page::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $count++;
        }

        Page::create($data);

        return redirect()->route('page')
            ->with('success', 'Page created successfully');
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);

        return view('content.page.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'title'             => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'content'           => 'nullable|string',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
            'is_active'         => 'required|boolean',
        ]);

        $data = $request->all();
        $data['slug'] = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        $page->update($data);

        return redirect()->route('page')
            ->with('success', 'Page updated successfully');
    }

    public function delete(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Deleted successfully',
        ]);
    }

    public function view($id)
    {
        $page = Page::findOrFail($id);

        return view('content.page.view', compact('page'));
    }
}