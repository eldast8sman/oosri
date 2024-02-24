<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaFileController extends Controller
{
    public static function upload_file($file, $disk='public'){
        if(!$file instanceof UploadedFile){
            return false;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::random(20).time().'.'.$extension;
        if(($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'png') || ($extension == 'gif')){
            $path = 'images';
        } elseif(($extension == 'pdf') || ($extension == 'docx') || ($extension == 'csv') || ($extension == 'xlsx') || ($extension == 'doc')){
            $path = 'documents';
        } elseif(($extension == 'mp3') || ($extension == 'mpeg3') || ($extension == 'wav') || ($extension == 'aac')){
            $path = 'audios';
        } elseif(($extension == 'mp4') || ($extension == 'mpeg4') || ($extension == 'avi') || ($extension == 'mov') || ($extension == 'mkv')){
            $path = 'videos';
        } else {
            $path = 'others';
        }
        $upload = Storage::disk($disk)->putFileAs($path, $file, $filename);
        if(!$upload){
            return false;
        }

        $media_file = MediaFile::create([
            'disk' => $disk,
            'path' => $upload,
            'url' => Storage::disk($disk)->url($upload),
            'size' => Storage::disk($disk)->size($upload),
            'extension' => $extension,
            'filename' => $filename
        ]);

        return $media_file;
    }

    public static function fetch_file($file){
        if(empty($file = MediaFile::find($file))){
            return false;
        }

        return $file;
    }

    public static function destroy($file) : bool
    {
        if(empty($file = MediaFile::find($file))){
            return false;
        }
        if(!Storage::disk($file->disk)->exists($file->path)){
            return false;
        }

        Storage::disk($file->disk)->delete($file->path);
        $file->delete();

        return true;
    }
}
