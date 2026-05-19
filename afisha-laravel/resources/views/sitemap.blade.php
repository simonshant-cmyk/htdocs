<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <url>
    <loc>{{ url('/') }}</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <url>
    <loc>{{ url('/venues') }}</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>

  <url>
    <loc>{{ url('/map') }}</loc>
    <changefreq>weekly</changefreq>
    <priority>0.5</priority>
  </url>

  @foreach($events as $event)
  <url>
    <loc>{{ url('/event/' . $event->event_id) }}</loc>
    <lastmod>{{ \Carbon\Carbon::parse($event->start_datetime)->toDateString() }}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
  @endforeach

  @foreach($venues as $venue)
  <url>
    <loc>{{ url('/venue/' . $venue->venue_id) }}</loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
  @endforeach

  @foreach($categories as $category)
  <url>
    <loc>{{ url('/category/' . $category->id) }}</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
  @endforeach

</urlset>
