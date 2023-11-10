<?php
require_once "utils.php";

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
    "og:image" => "https://leahy.dev/imgs/banner.gif",
    "og:image:type" => "image/gif",
    "og:image:width" => "200",
    "og:image:height" => "200",
    "og:image:alt" => "Dots moving in a figure of 8",
    "og:description" => "A random site with random stuff (about Jake (Me))"
];

// Render all the metatags
foreach ($PROPERTIES as $name => $content) {
    echo "<meta name='$name' content=\"$content\" />";
}
?>
<meta charset="UTF-8"/>
<link rel="icon" href="<?= makePHP("/favicon.svg") ?>" type="image/svg+xml" sizes="any">
