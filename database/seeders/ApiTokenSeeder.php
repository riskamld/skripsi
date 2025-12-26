<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ApiToken;

class ApiTokenSeeder extends Seeder
{
    public function run(): void
    {
        // Generate a secure random token
        $plainToken = Str::random(64);

        // Hash it for storage
        $hashedToken = hash('sha256', $plainToken);

        ApiToken::create([
            'name' => 'Chrome Extension Default',
            'token' => $hashedToken,
            'is_active' => true,
        ]);

        // Output the plain token so user can copy it
        echo "Generated API Token for Chrome Extension: {$plainToken}\n";
        echo "Please copy this token and paste it into your Chrome extension settings.\n";
    }
}
