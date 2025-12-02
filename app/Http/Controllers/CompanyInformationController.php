<?php

namespace App\Http\Controllers;

use App\Models\CompanyInformation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class CompanyInformationController extends Controller
{
    public function index()
    {
        $companyInfo = CompanyInformation::first();

        return Inertia::render('Settings/CompanyInformation', [
            'companyInfo' => $companyInfo,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'currency' => 'required|string|max:10',
        ]);

        // Check if company info exists
        $companyInfo = CompanyInformation::first();

        // Handle logo upload only if a new file is provided
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
            
            // Delete old logo if new one is uploaded
            if ($companyInfo && $companyInfo->logo) {
                Storage::disk('public')->delete($companyInfo->logo);
            }
        } else {
            // Remove logo from validated data if no new file is uploaded
            // This prevents the logo field from being set to null
            unset($validated['logo']);
        }

        if ($companyInfo) {
            $companyInfo->update($validated);
        } else {
            CompanyInformation::create($validated);
        }

        return redirect()->route('settings.company')
            ->with('success', 'Company information saved successfully.');
    }
}
