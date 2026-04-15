<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapsController extends Controller
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_api_key') ?? env('GOOGLE_MAPS_API_KEY');
    }

    /**
     * Proxy for Google Places Autocomplete API
     */
    public function autocomplete(Request $request)
    {
        $input = $request->query('input');
        $components = $request->query('components', 'country:sy');
        $language = $request->query('language', 'en');

        if (empty($input)) {
            return response()->json(['predictions' => [], 'status' => 'INVALID_REQUEST']);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
            'input' => $input,
            'key' => $this->apiKey,
            'components' => $components,
            'language' => $language,
        ]);

        if ($response->failed()) {
            Log::error("Google Maps Autocomplete Proxy Failed: " . $response->body());
            return response()->json(['error' => 'Proxy error'], 500);
        }

        return $response->json();
    }

    /**
     * Proxy for Google Place Details API
     */
    public function placeDetails(Request $request)
    {
        $placeId = $request->query('place_id');
        $fields = $request->query('fields', 'geometry');

        if (empty($placeId)) {
            return response()->json(['error' => 'place_id is required'], 400);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => $fields,
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            Log::error("Google Maps Place Details Proxy Failed: " . $response->body());
            return response()->json(['error' => 'Proxy error'], 500);
        }

        return $response->json();
    }

    public function directions(Request $request)
    {
        $origin = $request->query('origin');
        $destination = $request->query('destination');
        $mode = $request->query('mode', 'driving');

        if (empty($origin) || empty($destination)) {
            return response()->json(['error' => 'origin and destination are required'], 400);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => $origin,
            'destination' => $destination,
            'mode' => $mode,
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            Log::error("Google Maps Directions Proxy Failed: " . $response->body());
            return response()->json(['error' => 'Proxy error'], 500);
        }

        return $response->json();
    }

    /**
     * Proxy for Google Reverse Geocoding API
     */
    public function reverseGeocode(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $language = $request->query('language', 'en');

        if (empty($lat) || empty($lng)) {
            return response()->json(['error' => 'lat and lng are required'], 400);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => "$lat,$lng",
            'key' => $this->apiKey,
            'language' => $language,
        ]);

        if ($response->failed()) {
            Log::error("Google Maps Reverse Geocoding Proxy Failed: " . $response->body());
            return response()->json(['error' => 'Proxy error'], 500);
        }

        return $response->json();
    }
}
