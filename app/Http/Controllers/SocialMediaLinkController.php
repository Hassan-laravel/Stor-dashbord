<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaLink;
use Illuminate\Http\Request;

class SocialMediaLinkController extends Controller
{
    public function index()
    {
        $links = SocialMediaLink::latest()->get();
        return view('admin.social-media-links.index', compact('links'));
    }

    public function create()
    {
        return view('admin.social-media-links.create');
    }

    public function store(Request $request)
    {
        $locales = config('translatable.locales');
        
        $rules = [
            'icon'      => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.name"] = 'nullable|string|max:255';
            $rules["{$locale}.url"]  = 'nullable|url|max:255';
        }

        $request->validate($rules);

        $linkData = [
            'icon'      => $request->icon,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ];

        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.name")) || !empty($request->input("{$locale}.url"))) {
                $linkData[$locale] = $request->input($locale);
            }
        }

        SocialMediaLink::create($linkData);

        return redirect()->route('admin.social-media-links.index')->with('success', 'Social media link created.');
    }

    public function edit(SocialMediaLink $socialMediaLink)
    {
        return view('admin.social-media-links.edit', compact('socialMediaLink'));
    }

    public function update(Request $request, SocialMediaLink $socialMediaLink)
    {
        $locales = config('translatable.locales');
        
        $rules = [
            'icon'      => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];

        foreach ($locales as $locale) {
            $rules["{$locale}.name"] = 'nullable|string|max:255';
            $rules["{$locale}.url"]  = 'nullable|url|max:255';
        }

        $request->validate($rules);

        $linkData = [
            'icon'      => $request->icon,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ];

        foreach ($locales as $locale) {
            if (!empty($request->input("{$locale}.name")) || !empty($request->input("{$locale}.url"))) {
                $linkData[$locale] = $request->input($locale);
            } else {
                if ($socialMediaLink->hasTranslation($locale)) {
                    $socialMediaLink->deleteTranslation($locale);
                }
            }
        }

        $socialMediaLink->update($linkData);

        return redirect()->route('admin.social-media-links.index')->with('success', 'Social media link updated.');
    }

    public function destroy(SocialMediaLink $socialMediaLink)
    {
        $socialMediaLink->delete();
        return redirect()->route('admin.social-media-links.index')->with('success', 'Social media link deleted.');
    }
}