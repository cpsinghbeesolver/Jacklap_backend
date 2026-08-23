<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LicenseType;
use Yajra\DataTables\Facades\DataTables;

class LicenseTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $licenseTypes = LicenseType::orderBy('id', 'DESC');

            return DataTables::eloquent($licenseTypes)
                ->addIndexColumn()

                ->addColumn('actions', function ($type) {
                    return '
                        <a href="'.route('view-license-type', $type->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>

                        <a href="'.route('edit-license-type', $type->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-edit-2-line"></i>
                        </a>

                        <button class="btn btn-sm btn-outline-danger delete-license-type"
                            data-id="'.$type->id.'">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    ';
                })

                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('content.license-type.list');
    }

    public function create()
    {
        return view('content.license-type.add-license-type');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|string',
        ]);

        LicenseType::create($request->only(['name', 'description']));

        return redirect()->route('license-type-list')
            ->with('success', 'License type added successfully');
    }

    public function delete(Request $request)
    {
        $licenseType = LicenseType::findOrFail($request->id);
        $licenseType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'License type deleted successfully'
        ]);
    }

    public function edit($id)
    {
        $licenseType = LicenseType::findOrFail($id);

        return view('content.license-type.edit-license-type', compact('licenseType'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|string',
        ]);

        $licenseType = LicenseType::findOrFail($id);

        $licenseType->update($request->only(['name', 'description']));

        return redirect()->route('license-type-list')
            ->with('success', 'License type updated successfully');
    }

    public function view($id)
    {
        $licenseType = LicenseType::findOrFail($id);

        return view('content.license-type.view', compact('licenseType'));
    }
}