<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteSettingController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManageWebsite(), 403);
    }

    public function edit()
    {
        return view('website_admin.settings.edit', [
            'settings' => SiteSetting::allWithDefaults(),
        ]);
    }

    public function update(Request $request)
    {
        foreach (array_keys(SiteSetting::DEFAULTS) as $key) {
            if ($request->has($key)) {
                SiteSetting::set($key, $request->input($key));
            }
        }

        return redirect()->route('website-admin.settings.edit')->with('status', 'Site settings updated successfully.');
    }
}
