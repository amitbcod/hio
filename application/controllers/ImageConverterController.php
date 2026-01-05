<?php
ini_set('display_errors', 0); // Change to 0 for production
defined('BASEPATH') OR exit('No direct script access allowed');

class ImageConverterController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Image_model');
    }

    private function save_image_with_size_check($image, $webpPath, $minSize = 3072, $maxSize = 10240) {
        $quality = 90; // Starting quality level
        $minQuality = 10; // Minimum quality threshold

        do {
            imagewebp($image, $webpPath, $quality);
            $fileSize = filesize($webpPath);

            if ($fileSize > $maxSize) {
                $quality -= 10;
            } elseif ($fileSize < $minSize) {
                $quality += 5;
            }

            if ($quality < $minQuality) {
                break;
            }

        } while ($fileSize > $maxSize || $fileSize < $minSize);

        return $fileSize;
    }

    private function convert_images_to_webp($imagesDir, $updateFunction) {

        //after testing uncomment this 

        // $testImages = scandir($imagesDir);


        // Define specific test images
         $testImages = [
            '7d7e2587c6e1595e256aa2d98dffdaca.jpg',
            '94d3f8ee1da749478188381196b11047.jpg'
        ];
      
        $errors = []; // To store error messages
    
        foreach ($testImages as $file) {
            $imagePath = $imagesDir . '/' . $file;
     
            if (file_exists($imagePath)) { 
          
                $webpPath = $imagesDir . '/' . pathinfo($file, PATHINFO_FILENAME) . '.webp';
                echo "Processing: $webpPath\n"; // Debug statement
               
                // Create image resource with error handling
                $image = @imagecreatefromstring(file_get_contents($imagePath));
                if ($image) {
          
                    $finalSize = $this->save_image_with_size_check($image, $webpPath);
                  
                    // Update the database and check for success
                    if (!$updateFunction($file, pathinfo($webpPath, PATHINFO_BASENAME))) {
                  
                        $errors[] = "Failed to update database for: $file";
                    } else {
                  
                        echo "Converted and updated: $file to " . pathinfo($webpPath, PATHINFO_BASENAME) . " with size: " . ($finalSize / 1024) . " KB\n";
                    }
               
                    imagedestroy($image);
                } else {
                    $errors[] = "Failed to create image from: $file\n";
                }
            } else {
                echo "Image file does not exist: $imagePath\n"; // Notify if the image does not exist
            }
        }
        return $errors; // Return collected errors
    }
    

    public function convert() {
      
        $productImagesDir = '/var/www/parkmapped/newdesign/uploads/products/thumb'; // Navigate up one level to reach 'uploads'
        $bannerImagesDir = '/var/www/parkmapped/newdesign/uploads/banners'; // Adjust this if needed
        // echo  $productImagesDir ;die();

        // Convert product images and collect errors
        $productErrors = $this->convert_images_to_webp($productImagesDir, function($oldImage, $newImage) {
           return $this->Image_model->update_product_image($oldImage, $newImage);
        });

        // Convert banner images and collect errors
        $bannerErrors = $this->convert_images_to_webp($bannerImagesDir, function($oldImage, $newImage) {
           return $this->Image_model->update_banner_image($oldImage, $newImage);
        });

        if (!empty($productErrors) || !empty($bannerErrors)) {
            echo "Image conversion completed with errors:\n";
            echo implode("\n", array_merge($productErrors, $bannerErrors));
        } else {
            echo "Image conversion completed successfully.\n";
        }
    }
}
