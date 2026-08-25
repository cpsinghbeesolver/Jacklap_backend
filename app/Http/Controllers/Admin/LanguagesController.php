<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use Yajra\DataTables\Facades\DataTables;

class LanguagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $Languages = Language::orderBy('id', 'DESC');

            return DataTables::eloquent($Languages)
                ->addIndexColumn()

                ->addColumn('actions', function ($type) {
                    return '
                        <a href="'.route('view-language', $type->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>

                        <a href="'.route('edit-language', $type->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-edit-2-line"></i>
                        </a>

                        <button class="btn btn-sm btn-outline-danger delete-language"
                            data-id="'.$type->id.'">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    ';
                })

                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('content.language.list');
    }

    public function create()
    {
        return view('content.language.add-language');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        Language::create($request->only(['name']));

        return redirect()->route('language')
            ->with('success', 'Languages added successfully');
    }

    public function delete(Request $request)
    {
        $Language = Language::findOrFail($request->id);
        $Language->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Language deleted successfully'
        ]);
    }

    public function edit($id)
    {
        $language = Language::findOrFail($id);

        return view('content.language.edit-language', compact('language'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|string',
        ]);

        $language = Language::findOrFail($id);

        $language->update($request->only(['name']));

        return redirect()->route('language')
            ->with('success', 'Language updated successfully');
    }

    public function view($id)
    {
        $language = Language::findOrFail($id);

        return view('content.language.view', compact('language'));
    }
}
