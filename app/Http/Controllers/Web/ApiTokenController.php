<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ApiToken;

class ApiTokenController extends Controller
{
    public function index()
    {
        // Handle AJAX requests for infinite scroll
        if (request()->ajax()) {
            $tokens = ApiToken::latest()->paginate(10); // Smaller chunks for infinite scroll
            return response()->json([
                'tokens' => $tokens->items(),
                'has_more' => $tokens->hasMorePages(),
                'next_page' => $tokens->hasMorePages() ? $tokens->currentPage() + 1 : null
            ]);
        }

        $tokens = ApiToken::latest()->paginate(20)->onEachSide(2);

        return view('api-tokens.index', compact('tokens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Generate a secure random token
        $plainToken = Str::random(64);

        // Hash it for storage
        $hashedToken = hash('sha256', $plainToken);

        $token = ApiToken::create([
            'name' => $request->name,
            'token' => $hashedToken,
            'is_active' => true,
        ]);

        return redirect()->route('api-tokens.index')
            ->with('success', 'API token created successfully!')
            ->with('new_token', $plainToken);
    }

    public function show($id)
    {
        $token = ApiToken::findOrFail($id);

        return view('api-tokens.show', compact('token'));
    }

    public function update(Request $request, $id)
    {
        $token = ApiToken::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token->update([
            'name' => $request->name,
        ]);

        return redirect()->route('api-tokens.index')
            ->with('success', 'API token updated successfully!');
    }

    public function toggleStatus($id)
    {
        $token = ApiToken::findOrFail($id);

        $token->update([
            'is_active' => !$token->is_active,
        ]);

        $status = $token->is_active ? 'activated' : 'deactivated';

        return redirect()->route('api-tokens.index')
            ->with('success', "API token {$status} successfully!");
    }

    public function destroy($id)
    {
        $token = ApiToken::findOrFail($id);

        $token->delete();

        return redirect()->route('api-tokens.index')
            ->with('success', 'API token deleted successfully!');
    }

    public function regenerate($id)
    {
        $token = ApiToken::findOrFail($id);

        // Generate a new secure random token
        $plainToken = Str::random(64);

        // Hash it for storage
        $hashedToken = hash('sha256', $plainToken);

        $token->update([
            'token' => $hashedToken,
        ]);

        return redirect()->route('api-tokens.index')
            ->with('success', 'API token regenerated successfully!')
            ->with('new_token', $plainToken);
    }
}
