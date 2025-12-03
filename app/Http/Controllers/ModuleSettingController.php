<?php

namespace App\Http\Controllers;

use App\Models\ModuleSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleSettingController extends Controller
{
    /**
     * Display the module settings page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        // Get the first (and only) module settings record, or create default if not exists
        $moduleSetting = ModuleSetting::first();
        
        if (!$moduleSetting) {
            $moduleSetting = ModuleSetting::create([]);
        }

        return Inertia::render('Settings/ModuleSetting', [
            'moduleSetting' => $moduleSetting
        ]);
    }

    /**
     * Store or update module settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Core Modules - Supplier/Purchase group
            'supplier' => 'boolean',
            'purchase_order' => 'boolean',
            'grn' => 'boolean',
            'grn_return' => 'boolean',
            
            // Stock Transfer group
            'stock_transfer_request' => 'boolean',
            'stock_transfer_receive' => 'boolean',
            
            // Brand/Type group
            'brand' => 'boolean',
            'type' => 'boolean',
            
            // Individual modules
            'tax' => 'boolean',
            'discount' => 'boolean',
            'sales_return' => 'boolean',
            
            // Optional Modules
            'barcode' => 'boolean',
            'email_notification' => 'boolean',
        ]);

        $moduleSetting = ModuleSetting::first();

        if ($moduleSetting) {
            $moduleSetting->update($validated);
        } else {
            ModuleSetting::create($validated);
        }

        return redirect()->back()->with('success', 'Module settings updated successfully!');
    }
}
