<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Feedback received</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;color:#222}
    .section{margin:14px 0;padding:10px;border:1px solid #e6e6e6;background:#fbfbfb}
    .section h3{margin:0 0 8px 0;font-size:15px}
    table{border-collapse:collapse;width:100%;}
    td,th{border:1px solid #ddd;padding:6px;font-size:13px}
    th{background:#f5f5f5;text-align:left}
    .small{font-size:12px;color:#555}
  </style>
</head>
<body>
  <h2>Feedback received for trip #{{ $trip->id }}</h2>

  <div class="section">
    <h3>Traveler</h3>
    <p class="small">Name: {{ optional($trip->traveler)->full_name ?? optional($trip->traveler)->name ?? 'N/A' }} &nbsp; | &nbsp; Email: {{ optional($trip->traveler)->email ?? 'N/A' }}</p>
  </div>

  @if(!empty($payload['trip']) )
    <div class="section">
      <h3>Trip Ratings</h3>
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th>Rating (1-5)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payload['trip'] as $k => $v)
            <tr>
              <td>{{ ucwords(str_replace(['_','-'], ' ', $k)) }}</td>
              <td>{{ $v ?? '-' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  <div class="section">
    <h3>Overall Rating</h3>
    <p><strong>Stars:</strong> {{ $payload['overall_rating'] ?? '-' }} &nbsp; <span class="small">(1=Poor, 5=Excellent)</span></p>
  </div>

  <div class="section">
    <h3>Trip Comments</h3>
    <p><strong>How did you hear about us?</strong><br>{{ $payload['hear_about_us'] ?? ($payload['how_did_you_hear'] ?? 'N/A') }}</p>
    <p><strong>Other comments:</strong><br>{{ $payload['trip_comments'] ?? ($payload['overall_review'] ?? '') }}</p>
  </div>

  @if(!empty($payload['accommodations']))
    <div class="section">
      <h3>Accommodation Reviews</h3>
      @foreach($payload['accommodations'] as $abId => $acc)
        <h4 style="margin:8px 0 4px 0">Accommodation: {{ $acc['name'] ?? ('Accommodation #' . $abId) }}</h4>
        <table>
          <tbody>
            @foreach($acc as $key => $val)
              @if(in_array($key, ['id','review','name']))
                @continue
              @endif
              <tr>
                <td>{{ ucwords(str_replace(['_','-'], ' ', $key)) }}</td>
                <td>{{ $val ?? '-' }}</td>
              </tr>
            @endforeach
            <tr>
              <td>Comments</td>
              <td>{{ $acc['review'] ?? '-' }}</td>
            </tr>
          </tbody>
        </table>
      @endforeach
    </div>
  @endif

  @if(!empty($payload['activities']))
    <div class="section">
      <h3>Activity Reviews</h3>
      @foreach($payload['activities'] as $actId => $act)
        <h4 style="margin:8px 0 4px 0">Activity: {{ $act['name'] ?? ('Activity #' . $actId) }}</h4>
        <table>
          <tbody>
            @foreach($act as $key => $val)
              @if(in_array($key, ['id','review','name']))
                @continue
              @endif
              <tr>
                <td>{{ ucwords(str_replace(['_','-'], ' ', $key)) }}</td>
                <td>{{ $val ?? '-' }}</td>
              </tr>
            @endforeach
            <tr>
              <td>Comments</td>
              <td>{{ $act['review'] ?? '-' }}</td>
            </tr>
          </tbody>
        </table>
      @endforeach
    </div>
  @endif

  <div class="section">
    <h3>Raw Payload (debug)</h3>
    <pre style="font-size:12px;white-space:pre-wrap;">{{ print_r($payload, true) }}</pre>
  </div>

</body>
</html>
