<?php
// Very simple hot reload system.
// Client opens a connection using SSE, we then listen
// for file system changes and tell the page to reload if any
// Thank MDN for the example
// https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events/Using_server-sent_events#sending_events_from_the_server

header("Cache-Control: no-store");
header("Content-Type: text/event-stream");

// Should I just glob it?
$files = [
  "index.php" => 0,
  "meta.php" => 0,
  "css/components.css" => 0,
  "css/main.css" => 0
];

while (true) {
  $needsReload = false;
  echo "event: ping\n";
  $curDate = date(DATE_ISO8601);
  echo 'data: {"time": "' . $curDate . '"}';
  echo "\n\n";
  // See if any file has changed
  foreach ($files as $file => $mtime) {
    $newMTime = filemtime($file);
    $files[$file] = $newMTime;
    # See if it changes, but ignore the initial state
    if ($mtime < $newMTime && $mtime != 0) {
      $needsReload = true;
    }
  }
  // If so, send an event
  if ($needsReload) {
    echo "event: reload\ndata:\n\n";
  }
  while (ob_get_level() > 0) {
    ob_end_flush();
  }
  flush();

  if (connection_aborted()) break;
  sleep(1);
}
