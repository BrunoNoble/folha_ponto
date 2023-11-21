<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class HolidaysController extends Controller
{
    public function getHolidays()
    {
        $apiKey = 'YOUR_API_KEY';
        $countryCode = 'PT'; // Código do país para Portugal

        $client = new Client();
        $response = $client->get("https://date.nager.at/Api/v2/PublicHolidays/2023/{$countryCode}?apikey={$apiKey}");

        $holidays = json_decode($response->getBody(), true);

        return view('holidays', compact('holidays'));
    }
}
