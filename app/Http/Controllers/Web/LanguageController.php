<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends \App\Http\Controllers\Controller
{
    public function switchLanguage(Request $request)
    {
        $language = $request->input('language', 'id');

        // Validate language
        if (!in_array($language, ['id', 'en'])) {
            $language = 'id'; // Default to Indonesian
        }

        // Set language in session
        Session::put('locale', $language);

        // Set app locale
        App::setLocale($language);

        return response()->json([
            'success' => true,
            'message' => 'Language switched successfully',
            'locale' => $language
        ]);
    }
}
