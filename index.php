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
    <?php if (IS_DEBUG): ?>
        <!-- Load each style sheet when developing -->
        <?php foreach (CSS_FILES as $file): ?>
            <link rel='stylesheet' href='css/<?= $file ?>'/>
        <?php endforeach ?>
    <?php else: ?>
        <!-- Load single minified sheet in production -->
        <!-- Not minified yet, but this saves loading multiple sheets -->
        <link rel="stylesheet" href="css/site.css"/>
        <!--
            Should minimise time before text is shown.
            Should maybe also use the swap? So that the
            font is just swapped in
        -->
        <link rel="preload" href="css/slabo.ttf" as="font"/>
    <?php endif ?>
</head>
<body>
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
    <p>I enjoy most programming (my lack of design skills makes me hate frontend a lil 😔). But I still do it because the
        web is pretty neat</p>
    <p>I do CS at RMIT (TODO: make this past tense when I graduate) and during that time have worked with lots of
        technologies such as</p>
    <ul>
        <li>Java</li>
        <li>JavaScript (and Typescript)</li>
        <li>Assorted web (CSS, HTML)</li>
        <li>PHP</li>
        <li>C++</li>
        <li>SQL (And sadly also <a href="https://en.wikipedia.org/wiki/PL/SQL">PL/SQL</a>)
    </ul>
    <p>These were all learnt via actual projects (With some theory) that I will eventually list here</p>
</section>
</body>
<!-- We only want hot reload when developing -->
<?= IS_DEBUG ? "<script src='scripts/reload.js'></script>" : "" ?>
<script>
    console.log("Look at the source code here (https://github.com/ire4ever1190/folio-site/) instead of attempting to look at the minified stuff")
</script>
</html>
