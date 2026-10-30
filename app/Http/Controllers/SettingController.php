<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        // جلب الإعدادات (أو إنشاء صف فارغ إذا كانت قاعدة البيانات جديدة)
        $setting = Setting::firstOrCreate(['id' => 1]);
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::firstOrFail();
        $locales = config('translatable.locales');
        
        $rules = [
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'logo'          => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'favicon'       => 'nullable|image|mimes:ico,png|max:1024',
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.site_name"] = 'nullable|string|max:255';
            $rules["{$locale}.site_description"] = 'nullable|string';
            $rules["{$locale}.address"] = 'nullable|string';
        }

        $request->validate($rules);

        $data = $request->except(['logo', 'favicon', '_token', '_method']);

        // معالجة رفع الشعار (Logo)
        if ($request->hasFile('logo')) {
            if ($setting->logo) Storage::disk('public')->delete($setting->logo);
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        // معالجة رفع الأيقونة (Favicon)
        if ($request->hasFile('favicon')) {
            if ($setting->favicon) Storage::disk('public')->delete($setting->favicon);
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        $setting->update($data);

        return redirect()->back()->with('success', 'تم تحديث الإعدادات بنجاح.');
    }
}