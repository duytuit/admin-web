<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Frontend\Controller;
use Intervention\Image\ImageManagerStatic as Image;

class ThumbController extends Controller
{
    public function create(int $width, int $height, string $image)
    {
        $public_path = storage_path();

        $src = $public_path . '/' . $image;
        $dest = $public_path . "/thumb/{$width}x{$height}/" . $image;

        if ($width && $height && is_file($src)) {
            $path = pathinfo($image);
            extract($path);

            $dir = $public_path . "/thumb/{$width}x{$height}/" . $dirname;

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $img = Image::make($src)->fit($width, $height)->save($dest);
            
            return $img->response();
        }
        
        abort(404);
    }
}
