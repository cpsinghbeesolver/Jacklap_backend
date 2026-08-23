<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterService;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $services = MasterService::with('category')->orderBy('id','DESC');

            return DataTables::eloquent($services)
                ->addIndexColumn()

                ->addColumn('category', function ($row) {
                    return $row->category?->name ?? '-';
                })

                ->editColumn('type', function ($row) {
                    return ucfirst($row->type);
                })

                ->editColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->addColumn('actions', function ($row) {
                    return '
                        <a href="'.route('master-service.view', $row->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>

                        <a href="'.route('master-service.edit', $row->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-edit-2-line"></i>
                        </a>

                        <button class="btn btn-sm btn-outline-danger delete-service"
                            data-id="'.$row->id.'">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    ';
                })

                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('content.master-service.list');
    }

    public function pending(Request $request)
    {
        if ($request->ajax()) {

            $services = MasterService::with('category')
                ->where('status', 0)
                ->orderBy('id', 'DESC');

            return DataTables::eloquent($services)
                ->addIndexColumn()

                ->addColumn('category', function ($row) {
                    return $row->category?->name ?? '-';
                })

                ->editColumn('type', function ($row) {
                    return ucfirst($row->type);
                })

                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';

                    return '
                        <div class="form-check form-switch mb-2">
                            <input 
                                class="form-check-input toggle-status" 
                                type="checkbox" 
                                data-id="'.$row->id.'" 
                                '.$checked.'
                            >
                            <label class="form-check-label"> </label>
                        </div>
                    ';
                })

                ->addColumn('actions', function ($row) {
                    return '
                        <a href="'.route('master-service.view', $row->id).'" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>
                    ';
                })

                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('content.master-service.pending');
    }

    public function toggleStatus(Request $request)
    {
        $service = MasterService::findOrFail($request->id);

        $service->status = $request->status;
        $service->save();

        return response()->json([
            'success' => true,
            'message' => 'Service approved successfully'
        ]);
    }

    public function create()
    {
        $categories = ServiceCategory::where('status',1)->get();
        return view('content.master-service.add', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'service_category_id' => 'required',
            'type' => 'required',
            'input_type' => 'required',
            'price_limit' => 'required|numeric'
        ]);

        MasterService::create($request->all());

        return redirect()->route('master-service')
            ->with('success', 'Service created successfully');
    }

    public function edit($id)
    {
        $service = MasterService::findOrFail($id);
        $categories = ServiceCategory::where('status',1)->get();

        return view('content.master-service.edit', compact('service','categories'));
    }

    public function update(Request $request, $id)
    {
        $service = MasterService::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'service_category_id' => 'required',
            'type' => 'required',
            'input_type' => 'required',
            'price_limit' => 'required|numeric'
        ]);
        $service->update($request->all());

        return redirect()->route('master-service')
            ->with('success', 'Service updated successfully');
    }

    public function delete(Request $request, $id)
    {
        $service = MasterService::findOrFail($id);
        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully'
        ]);
    }

    public function view($id)
    {
        $service = MasterService::with('category')->findOrFail($id);

        return view('content.master-service.view', compact('service'));
    }
}