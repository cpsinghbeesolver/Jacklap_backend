<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdentityType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class IdentityTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $types = IdentityType::orderBy('is_required', "DESC");

            return DataTables::eloquent($types)
                ->addIndexColumn()

                ->editColumn('is_required', function ($row) {
                    return $row->is_required
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-secondary">No</span>';
                })

                ->addColumn('actions', function ($row) {
                    return '
                        <a href="' . route('document-type.view', $row->id) . '" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>
                        <a href="' . route('document-type.edit', $row->id) . '" class="btn btn-sm btn-outline-primary">
                            <i class="ri-edit-2-line"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger delete-type"
                            data-id="' . $row->id . '">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    ';
                })

                ->rawColumns(['is_required', 'actions'])
                ->make(true);
        }

        return view('content.document-type.list');
    }

    public function create()
    {
        return view('content.document-type.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'total_documents'  => 'required|integer|min:1',
            'is_required'      => 'required|boolean',
        ]);

        IdentityType::create($request->all());

        return redirect()->route('document-type')
            ->with('success', 'Document type created successfully');
    }

    public function edit($id)
    {
        $type = IdentityType::findOrFail($id);

        return view('content.document-type.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = IdentityType::findOrFail($id);

        $request->validate([
            'name'             => 'required|string|max:255',
            'total_documents'  => 'required|integer|min:1',
            'is_required'      => 'required|boolean',
        ]);

        $type->update($request->all());

        return redirect()->route('document-type')
            ->with('success', 'Document type updated successfully');
    }

    public function delete(Request $request, $id)
    {
        $type = IdentityType::findOrFail($id);
        $type->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Deleted successfully',
        ]);
    }

    public function view($id)
    {
        $type = IdentityType::findOrFail($id);

        return view('content.document-type.view', compact('type'));
    }
}