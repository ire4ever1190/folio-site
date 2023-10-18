<?php

$PROPERTIES = [
  // Basic metadata
  "author" => "Jake Leahy",
  "generator" => "PHP + Makefile",
  "description" => "Portfolio website showing some of the garbage I have gifted to the world",
  "keywords" => "potato, Jake Leahy, portfolio",
  // Open graph stuff
  "og:title" => "Jake's site thingy",
  "og:type" => "website",
  "og:url" => "https://leahy.dev",
  "og:image" => "https://leahy.dev/imgs/construction.png",
  "og:image:width" => "1000",
  "og:image:height" => "1000",
  "og:image:alt" => "Bunch of random emojis surronding text saying 'under construction'"
];

// Render all the metatags
foreach ($PROPERTIES as $name => $content) {
  echo "<meta name='$name' content=\"$content\" />";
}
