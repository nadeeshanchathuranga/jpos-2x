<?php

namespace App\Http\Controllers;

use App\Models\BillSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
            // Enforce exactly 10 digits for mobile numbers
            'mobile_1' => 'required|digits:10',
            'mobile_2' => 'nullable|digits:10',
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

                // 3. Create canvas with Transparent background
                $newImg = imagecreatetruecolor($newW, $newH);
                imagealphablending($newImg, false);
                imagesavealpha($newImg, true);
                $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
                imagefilledrectangle($newImg, 0, 0, $newW, $newH, $transparent);

                // Copy and resize onto transparent canvas
                // Note: imagecopyresampled might blend alpha incorrectly if source has no alpha,
                // but we are about to overwrite pixels anyway in the loop.
                // Actually to preserve source details before thresholding, we just copy.
                imagecopyresampled($newImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $oldW, $oldH);
                imagedestroy($srcImg); // Free source

                // 4. Manual Thresholding (Strict Black and White -> Black and Transparent)
                // Iterate pixels. Dark -> Black. Light OR Transparent -> Transparent.

                $blackColor = imagecolorallocate($newImg, 0, 0, 0);
                // $transparent is already allocated

                for ($y = 0; $y < $newH; $y++) {
                    for ($x = 0; $x < $newW; $x++) {
                        $rgb = imagecolorat($newImg, $x, $y);

                        // Check Alpha first (0-127 in GD, 127 is transparent)
                        $alpha = ($rgb >> 24) & 0x7F;

                        if ($alpha > 110) {
                            // If source is already transparent, keep it transparent
                            imagesetpixel($newImg, $x, $y, $transparent);
                            continue;
                        }

                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;

                        // Simple Grayscale brightness
                        $gray = ($r + $g + $b) / 3;

                        if ($gray < 128) { // Darker -> Black
                            imagesetpixel($newImg, $x, $y, $blackColor);
                        } else { // Lighter -> Transparent
                            imagesetpixel($newImg, $x, $y, $transparent);
                        }
                    }
                }

                // Capture output
                ob_start();
                imagesavealpha($newImg, true); // Ensure alpha is saved
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

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'save',
            'module' => 'bill setting',
            'details' => json_encode([
                'company_name' => $setting->company_name,
                'address' => $setting->address,
                'mobile_1' => $setting->mobile_1,
                'mobile_2' => $setting->mobile_2,
                'email' => $setting->email,
                'website_url' => $setting->website_url,
                'footer_description' => $setting->footer_description,
                'print_size' => $setting->print_size,
                'logo_path' => $setting->logo_path,
            ]),
        ]);

        return redirect()->back()->with('success', 'Bill settings updated successfully.');
    }
}
