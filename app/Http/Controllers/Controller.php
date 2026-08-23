<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
  public function uploadFile($request, $fname,$param = 'image',$model = null){
    $image = null;
    $disk = app()->environment('local')?'public':'s3';
    try{
        if ($request->hasFile($param)) {
            // Generate a unique filename
            $fileName = $fname.'/' . uniqid() . '.' . $request->file($param)->extension();

            if (!empty($model)) {
              Storage::disk($disk)->delete($model->$param);
            }

            $request->file($param)->storeAs('', $fileName, $disk);

            // Get the public URL of the file
            $image = $fileName;
        }
    }catch(\Exception $e){
        \Log::info(['file error' => $e->getMessage()]);
    }
    return $image;
  }

  public function deleteFile($file){
    if (app()->environment('local')) {
        Storage::disk('s3')->delete($file);
    }else{
        Storage::disk('s3')->delete($file);
    }
  } 
}