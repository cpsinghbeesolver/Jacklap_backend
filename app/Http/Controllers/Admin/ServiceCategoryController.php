<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use SweetAlert2\Laravel\Swal;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $categories = ServiceCategory::orderBy('sort_order','ASC');

            return DataTables::eloquent($categories)
                ->addIndexColumn()

                ->addColumn('image', function ($cat) {
                    $image = $cat->image
                        ? asset($cat->image)
                        : asset('assets/img/avatars/' . rand(1, 7) . '.png');

                    return '<img src="'.$image.'" width="40" class="rounded">';
                })

                ->editColumn('status', function ($cat) {
                    return $cat->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->addColumn('actions', function ($cat) {
                
                        // <button class="btn btn-sm btn-outline-danger delete-category"
                        //     data-id="'.$cat->id.'">
                        //     <i class="ri-delete-bin-6-line"></i>
                        // </button>    
                return '
                        <a href="'.route('view-category', $cat->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>

                        <a href="'.route('edit-category', $cat->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-edit-2-line"></i>
                        </a>
                    ';
                })

                ->rawColumns(['image', 'status', 'actions'])
                ->make(true);
        }

        return view('content.service-category.list');
    }

    public function create()
    {
        return view('content.service-category.add-category');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:800',
        ]);

        $data = $request->only(['name', 'price', 'status', 'description']);

        if ($request->hasFile('image')) {
            $filename = Str::uuid() . '.' . $request->image->extension();
            $data['image'] = $request->image->storeAs('categories', $filename, 's3');
        }

        ServiceCategory::create($data);

        return redirect()->route('category-list')
            ->with('success', 'Category added successfully');
    }

    public function delete(Request $request)
    {
        $category = ServiceCategory::findOrFail($request->id);

        // delete image
        if ($category->image && Storage::disk('s3')->exists($category->image)) {
            Storage::disk('s3')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ]);
    }

    public function edit($id)
    {
        $category = ServiceCategory::findOrFail($id);

        return view('content.service-category.edit-category', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:800',
        ]);

        $category = ServiceCategory::findOrFail($id);

        if ($request->hasFile('image')) {

            if ($category->image && Storage::disk('s3')->exists($category->image)) {
                Storage::disk('s3')->delete($category->image);
            }

            $filename = Str::uuid() . '.' . $request->image->extension();

            $path = $request->image->storeAs('categories', $filename, 's3');

            $category->image = $path;
        }

        $category->update([
            'name' => $request->name,
            'price' => $request->price,
            'status' => $request->status,
            'description' => $request->description
        ]);

        return redirect()->route('category-list')
            ->with('success', 'Category updated successfully');
    }

    public function view($id)
    {
        $category = ServiceCategory::findOrFail($id);

        return view('content.service-category.view', compact('category'));
    }
}