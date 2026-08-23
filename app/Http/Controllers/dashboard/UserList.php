<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SweetAlert2\Laravel\Swal;
use Yajra\DataTables\Facades\DataTables;

class UserList extends Controller{
    
    public function userList(Request $request){
        if ($request->ajax()) {
            // $users = User::whereDoesntHave('roles', function ($query) {
            //     $query->where('name', 'admin');
            // })
            // ->with('roles')->orderBy('id','DESC');
            $users = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            })
            ->with('roles')
            ->orderByDesc('id');

            return DataTables::eloquent($users)
                ->addIndexColumn()
                ->addColumn('avatar', function ($user) {
                    $image = $user->image
                    ? asset($user->image)
                    : asset('assets/img/avatars/' . rand(1, 7) . '.png');

                    if(!$user->image || !Storage::disk('s3')->exists($image) ){
                        $image = asset('assets/img/avatars/' . rand(1, 7) . '.png');
                    }else{
                        $image = Storage::disk('s3')->response($image);
                    }

                    return '
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-4">
                                <img src="'.$image.'" class="rounded-circle">
                            </div>
                            <div>
                                <h6 class="mb-0 text-truncate">'.ucfirst($user->name).'</h6>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('role', function ($user) {
                    return ucfirst($user->getRoleNames()->first() ?? 'N/A');
                })
                ->editColumn('phone', function ($user) {
                    return $user->phone ?? 'No Phone Number';
                })
                ->addColumn('actions', function ($user) {
                    return '
                        <div class="d-flex gap-2">
                            <a href="'.route('view-user', [
                                'id' => $user->id,
                                'role' => request('role')
                            ]).'"
                            class="btn btn-sm btn-outline-primary view-user" title="View User">
                                <i class="ri-eye-fill"></i>
                            </a>

                            <a href="'.route('get-user', [
                                'id' => $user->id,
                                'role' => request('role')
                            ]).'"
                            class="btn btn-sm btn-outline-primary edit-user" title="Edit User">
                                <i class="ri-edit-2-line"></i>
                            </a>

                            <button class="btn btn-sm btn-outline-danger delete-user"
                                    data-id="'.$user->id.'" title="Delete User">
                                <i class="ri-delete-bin-6-line"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['avatar', 'actions']) 
                ->make(true);
        }
        
        return view('content.userlist.user-list');
    }


    function deleteUser(Request $request){
        $userId = $request->id;
        $user = User::findOrFail($userId);
        if($user){
            $user->update([
                'email' => $user->email."_".now(),
                'phone' => $user->phone."_".now()
            ]);

            if($userId == Auth::id()){    

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Swal::fire([
                    'title' => 'Success',
                    'text' => 'Profile was deleted successfully',
                    'icon' => 'success',
                    'confirmButtonText' => 'Okay'
                ]);   

                return redirect()->route('login');
            }

            // $rawImage = $user->getRawOriginal('image'); 
            // if ($rawImage && Storage::disk('public')->exists($rawImage)) {
            //     Storage::disk('public')->delete($rawImage);
            // }
            // $user->delete();
            $user->delete();
            if(!$request->ajax()){
                Swal::fire([
                    'title' => 'Success',
                    'text' => 'Profile was deleted successfully',
                    'icon' => 'success',
                    'confirmButtonText' => 'Okay'
                ]);   
                return redirect()->route('user-list');
            }

            return response()->json(['status' => 'success', 'message' => 'User Deleted Successfully']);
        }
        return response()->json(['status' => 'failed', 'message' => 'Unable to delete the user']);
    }


    function getUser(Request $request){
        $userId = $request->id;
        $user = User::findOrFail($userId);
        if($user['image'] && Storage::disk('s3')->exists($user['image']) ){
            $user['image'] = Storage::disk('s3')->response($user['image']);
        }
        
        return view('content.userlist.updateuser')
                ->with(['user' => $user]);
    }


    function updateUser(Request $request, $id){

        $request->validate([
                'name' => 'required|string|max:128',
                'phone_number' => 'regex:/^[0-9]+$/',
                'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:800',
                'address' => 'max:255',
                //'organization' => 'max:255'
            ],
            [
                'avatar.mimes' => 'Only JPG, JPEG and PNG images are allowed.',
                'avatar.max' => 'Image size must not exceed 800KB.',
        ]);

        

        $user = User::findOrFail($id);

        if ($request->hasFile('avatar')) {

            $rawImage = $user->getRawOriginal('image'); 

            if ($rawImage && Storage::disk('s3')->exists($rawImage)) {
                Storage::disk('s3')->delete($rawImage);
            }

            $filename = Str::uuid() . '.' . $request->avatar->extension();

            $path = $request->avatar->storeAs(
                'avatars',
                $filename,
                's3'
            );

            $user->image = $path;
        }

        $user->name = $request->name;
        $user->phone = $request->phone_number;
        $user->address = $request->address;
        //$user->country = $request->country;
        //$user->organization = $request->organization;
        $user->save();
        
        if($user->id == Auth::id()){
            return redirect(route('pages-account-settings-account'))
                ->with(['success' => 'The update was successfull']);
        }

        return redirect()->route('user-list', [
            'role' => $request->role,
        ])->with('success', 'The update was successful');
    }


    function viewUser(Request $request){
        $userId = $request->id;
        $user = User::with([
            'professionalDetail.serviceCategory',
            'bankDetail',
            'media.identityType',
            'media.files'
        ])->findOrFail($request->id);       
        // $country = Country::where('code', $user->country)->get()->first();

        return view('content.userlist.view-user')
            ->with(['user' => $user]);
    }

}
