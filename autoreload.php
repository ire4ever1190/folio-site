<?php
// Very simple hot reload system.
// Client opens a connection using SSE, we then listen
// for file system changes and tell the page to reload if any
// Thank MDN for the example
// https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events/Using_server-sent_events#sending_events_from_the_server

header("Cache-Control: no-store");
header("Content-Type: text/event-stream");

// All input files
$patterns = [
  "**.php",
  "css/**.css",
  "imgs/**",
  "scripts/**.js"
];

/**
 * Returns fileName => mtime mapping of all files
 * that match the patterns provided
 * @param $patterns array<string>
 * @returns array<string, int>
 */
function getFiles(array $patterns): array {
  $result = [];
  foreach ($patterns as $pattern) {
    foreach (glob($pattern) as $file) {
      $result[$file] = filemtime($file);
    }
  }
  return $result;
}

// Build initial state
$files = getFiles($patterns);

while (true) {
  $needsReload = false;
  echo "event: ping\n";
  $curDate = date(DATE_ISO8601);
  echo 'data: {"time": "' . $curDate . '"}';
  echo "\n\n";
  // See if any file has changed
  foreach (getFiles($patterns) as $file => $mtime) {
    // Makes it reload if the file is new
    $mtime = $files[$file] ?? 0;
    $newMTime = filemtime($file);
    if ($mtime < $newMTime) {
      $needsReload = true;
      // We don't break so that we update all the modification times.
      // This way we don't run into constant reloads if a few
      // files were changed
    }
    $files[$file] = $newMTime;
  }
  // If so, send an event
  if ($needsReload) {
    echo "event: reload\ndata:\n\n";
  }
  ob_flush();
  flush();

  if (connection_aborted()) exit();
  sleep(3);
}
