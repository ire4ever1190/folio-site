<?php
require_once "utils.php";


const CSS_FILES = ["components.css", "main.css", "variables.css", "utils.css"];

const SOCIALS = [
    "https://github.com/ire4ever1190" => "github-mark.svg",
    "https://www.linkedin.com/in/yoda-/" => "linkedin.svg",
    "mailto:business@leahy.dev" => "email.svg"
];


?>
<!doctype html>
<html lang="en">
<head>
    <?php include 'meta.php' ?>
    <title>Teehee</title>
    <!--
        Should minimise time before text is shown.
        Should maybe also use the swap? So that the
        font is just swapped in
    -->
    <link rel="preload" href="css/slabo.ttf" as="font"/>
    <?php if (IS_DEBUG): ?>
        <!-- Load each style sheet when developing -->
        <?php foreach (CSS_FILES as $file): ?>
            <link rel='stylesheet' href='css/<?= $file ?>'/>
        <?php endforeach ?>
    <?php else: ?>
        <!-- Load single minified sheet in production -->
        <!-- Not minified yet, but this saves loading multiple sheets -->
        <link rel="stylesheet" href="css/site.css"/>
    <?php endif ?>
</head>
<body>
<canvas id="stars"></canvas>
<section class="card centre" style="width: 10vw">
    <h1 class="align-centre">Hello</h1>
    <img id="handWave" src="imgs/hand.svg" alt="Yellow splayed hand, palm facing forward"/>
    <p class="align-centre">I'm Jake</p>
    <div id="icons">
        <?php foreach (SOCIALS as $link => $icon): ?>
            <a href="<?= $link ?>"><img style="height: 1em" src="/imgs/<?= $icon ?>"/></a>
        <?php endforeach ?>
    </div>
</section>
<section class="card centre mt1" style="width: 30vw">
    <h2>About Me</h2>
    <p>
        I'm a normal human <span class="spoiler">(not a robot 🤖)</span> who programs professional and as a hobby.
        Not much else to say, but please check out my <abbr title="It's linked at the top">github</abbr> to see my personal projects
    </p>
</section>
</body>
<?php if (IS_DEBUG): ?>
    <!-- Put anything debug related here. Won't be loaded in production -->
    <!-- We only want hot reload when developing -->
    <script src='scripts/reload.js'></script>
<?php endif ?>
<script>
    console.log("Look at the source code here (https://github.com/ire4ever1190/folio-site/) instead of attempting to look at the minified stuff")
</script>
<script src="scripts/stars.js"></script>
</html>
