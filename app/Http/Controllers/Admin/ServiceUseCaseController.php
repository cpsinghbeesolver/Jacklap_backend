<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceUseCase;
use Yajra\DataTables\Facades\DataTables;

class ServiceUseCaseController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $useCases = ServiceUseCase::orderBy('id', 'DESC');

            return DataTables::eloquent($useCases)
                ->addIndexColumn()

                ->addColumn('is_full_day', function ($useCase) {
                    return $useCase->is_full_day
                        ? '<span class="badge bg-label-success">Yes</span>'
                        : '<span class="badge bg-label-secondary">No</span>';
                })

                ->addColumn('actions', function ($useCase) {
                    return '
                        <a href="'.route('view-service-use-case', $useCase->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>

                        <a href="'.route('edit-service-use-case', $useCase->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-edit-2-line"></i>
                        </a>

                        <button class="btn btn-sm btn-outline-danger delete-service-use-case"
                            data-id="'.$useCase->id.'">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    ';
                })

                ->rawColumns(['is_full_day', 'actions'])
                ->make(true);
        }

        return view('content.service-use-case.list');
    }

    public function create()
    {
        return view('content.service-use-case.add-service-use-case');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'is_full_day' => 'nullable|boolean',
        ]);

        ServiceUseCase::create([
            'title' => $request->title,
            'is_full_day' => $request->has('is_full_day') ? 1 : 0,
        ]);

        return redirect()->route('service-use-case')
            ->with('success', 'Service use case added successfully');
    }

    public function delete(Request $request)
    {
        $useCase = ServiceUseCase::findOrFail($request->id);
        $useCase->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service use case deleted successfully'
        ]);
    }

    public function edit($id)
    {
        $useCase = ServiceUseCase::findOrFail($id);

        return view('content.service-use-case.edit-service-use-case', compact('useCase'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:255',
            'is_full_day' => 'nullable|boolean',
        ]);

        $useCase = ServiceUseCase::findOrFail($id);

        $useCase->update([
            'title' => $request->title,
            'is_full_day' => $request->has('is_full_day') ? 1 : 0,
        ]);

        return redirect()->route('service-use-case')
            ->with('success', 'Service use case updated successfully');
    }

    public function view($id)
    {
        $useCase = ServiceUseCase::findOrFail($id);

        return view('content.service-use-case.view', compact('useCase'));
    }
}