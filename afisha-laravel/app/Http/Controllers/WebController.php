<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function event(int $id)
    {
        $event = Event::with('venue')->find($id);
        $ogData = $event ? [
            'title'       => $event->title,
            'description' => $event->description
                ? mb_substr(strip_tags($event->description), 0, 160)
                : 'Событие на АфишаКолыма',
            'image'       => $event->image,
            'url'         => url("/event/{$id}"),
        ] : null;

        return view('event', ['id' => $id, 'ogData' => $ogData]);
    }

    public function venue(int $id)
    {
        $venue = Venue::find($id);
        $ogData = $venue ? [
            'title'       => $venue->name,
            'description' => $venue->description
                ? mb_substr(strip_tags($venue->description), 0, 160)
                : 'Площадка на АфишаКолыма',
            'image'       => $venue->image,
            'url'         => url("/venue/{$id}"),
        ] : null;

        return view('venue', ['id' => $id, 'ogData' => $ogData]);
    }

    public function login()          { return view('login'); }
    public function forgotPassword() { return view('forgot-password'); }
    public function resetPassword()  { return view('reset-password'); }
    public function cabinet()  { return view('cabinet'); }
    public function orgCabinet() { return view('org-cabinet'); }
    public function moderator()  { return view('moderator'); }
    public function cart()       { return view('cart'); }
    public function favorites()  { return view('favorites'); }
    public function tickets()    { return view('tickets'); }
    public function venues()     { return view('venues'); }
    public function map()        { return view('map'); }
    public function history()    { return view('history'); }
    public function organizations() { return view('organizations'); }
    public function org(int $id)      { return view('org', ['id' => $id]); }
    public function category(int $id) { return view('category', ['id' => $id]); }
}
