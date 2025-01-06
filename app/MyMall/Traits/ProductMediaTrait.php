<?php

namespace App\MyMall\Traits;

trait ProductMediaTrait
{
    public function convertAvifImageToJpg($imagePath, $quality)
    {
        $avif_image = imagecreatefromavif($imagePath);
        imagejpeg($avif_image, $imagePath, 80);
        imagedestroy($avif_image);
    }

    public function convertWebpImageToJpg($imagePath, $quality)
    {
        $webp_image = imagecreatefromwebp($imagePath);
        $jpg_image = imagecreatetruecolor(imagesx($webp_image), imagesy($webp_image));
        imagecopy($jpg_image, $webp_image, 0, 0, 0, 0, imagesx($webp_image), imagesy($webp_image));
        imagejpeg($jpg_image, $imagePath, 100);
        imagedestroy($webp_image);
        imagedestroy($jpg_image);
    }
}
