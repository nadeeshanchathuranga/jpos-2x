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
            $data['logo_path'] = $request->file('logo')->store('bill_logos', 'public');
        }

        $setting->fill($data);
        $setting->save();

        return redirect()->back()->with('success', 'Bill settings updated successfully.');
    }
}
