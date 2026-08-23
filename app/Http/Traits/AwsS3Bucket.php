<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;

trait AwsS3Bucket
{
    public function getUrl(){
        if (app()->environment('local')) {
            return $this->defaultUrl();
        } else {
            return config('filesystems.disks.s3.url');
        }
    }

    public function defaultUrl(){
        return Request::root().'/storage/';
    }

    public function checkFile($file){
        if (app()->environment('local')) {
            return Storage::disk('public')->exists($file);
        } else {
            return Storage::disk('s3')->exists($file);
        }
    }
}