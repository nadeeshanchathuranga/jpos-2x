<?php

namespace App\Http\Controllers;

use App\Models\BillSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Inertia\Inertia;

class BillSettingController extends Controller
{
    public function index()
    {
        $setting = BillSetting::first();
        return Inertia::render('BillSettings/Index', [
            'setting' => $setting,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'mobile_1' => 'required|string|max:30',
            'mobile_2' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website_url' => 'nullable|string|max:255',
            'footer_description' => 'nullable|string',
            'print_size' => 'required|in:58mm,80mm,112mm,210mm',
            'logo' => 'nullable|image|max:2048',
        ]);

        $setting = BillSetting::first() ?? new BillSetting();

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $imageFile = $request->file('logo');
            $extension = $imageFile->getClientOriginalExtension();
            $filename = 'bill_logos/' . uniqid('logo_') . '.' . $extension;
            
            // Native GD Implementation for Strict B&W (Thresholding)
            $imageContent = file_get_contents($imageFile->getRealPath());
            $srcImg = @imagecreatefromstring($imageContent);

            if ($srcImg) {
                // 1. Get dimensions
                $oldW = imagesx($srcImg);
                $oldH = imagesy($srcImg);
                
                // 2. Resize if too large (Max width 500px for receipt logos, improves loop performance)
                $maxW = 500;
                if ($oldW > $maxW) {
                    $newW = $maxW;
                    $newH = ($oldH / $oldW) * $newW;
                } else {
                    $newW = $oldW;
                    $newH = $oldH;
                }

                // 3. Create canvas with White background (handles transparency)
                $newImg = imagecreatetruecolor($newW, $newH);
                $white = imagecolorallocate($newImg, 255, 255, 255);
                imagefilledrectangle($newImg, 0, 0, $newW, $newH, $white);
                
                // Copy and resize
                imagecopyresampled($newImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $oldW, $oldH);
                imagedestroy($srcImg); // Free source

                // 4. Manual Thresholding (Strict Black and White)
                // Iterate pixels to force 0x000000 or 0xFFFFFF
                // This is 100% monochrome.
                
                // First convert to grayscale to make calculation easier
                imagefilter($newImg, IMG_FILTER_GRAYSCALE);

                $blackColor = imagecolorallocate($newImg, 0, 0, 0);
                $whiteColor = imagecolorallocate($newImg, 255, 255, 255);

                for ($y = 0; $y < $newH; $y++) {
                    for ($x = 0; $x < $newW; $x++) {
                        $rgb = imagecolorat($newImg, $x, $y);
                        // Since it's grayscale, R=G=B. Just pick red channel.
                        $gray = ($rgb >> 16) & 0xFF;
                        
                        if ($gray < 128) { // Darker -> Black
                            imagesetpixel($newImg, $x, $y, $blackColor);
                        } else { // Lighter -> White
                            imagesetpixel($newImg, $x, $y, $whiteColor);
                        }
                    }
                }

                // Capture output
                ob_start();
                imagepng($newImg);
                $imageData = ob_get_clean();
                imagedestroy($newImg);
                
                Storage::disk('public')->put($filename, $imageData);
            } else {
                // Fallback
                $path = $imageFile->storeAs('bill_logos', uniqid('logo_') . '.' . $extension, 'public');
                $filename = $path; 
            }
            $data['logo_path'] = $filename;
        }

        $setting->fill($data);
        $setting->save();

        return redirect()->back()->with('success', 'Bill settings updated successfully.');
    }
}
