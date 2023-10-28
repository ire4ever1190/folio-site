<?php
// TODO: Just make the modtime be the current date
// Like the CI just stats the HTML file which will be the current date
// anyways, so like whats the point?


// Too lazy to make a variable
// Just enough energy to read a file
define("DOMAIN_NAME", "https://" . file_get_contents("CNAME"));

function getPage(int $index): string {
  global $argv;
  $file = preg_replace("/^site/", "", $argv[$index]);
  if ($file === "/index.html") {
    // We don't want index.html for the root
    $file = "/";
  }
  return DOMAIN_NAME . $file;
}

?>
<?= "<?" ?>xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- Files are passed in as command args, so iterate through them -->
  <?php for ($i = 1; $i < $argc; $i++): ?>
    <url>
      <loc><?= getPage($i) ?></loc>
      <lastmod><?= date("Y-m-d", filemtime($argv[$i])) ?></lastmod>
    </url>
  <?php endfor ?>
</urlset>
