<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $events = Event::select('event_id', 'start_datetime')
            ->where('status_id', 1)
            ->orderBy('start_datetime', 'desc')
            ->get();

        $venues = Venue::select('venue_id')->get();

        $categories = Category::select('id')->get();

        $xml = view('sitemap', compact('events', 'venues', 'categories'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
