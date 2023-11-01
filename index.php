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
    <p>You could probably tell by the tagline before but I'm Jake. I'm a normal human <span class="spoiler">(not a robot 🤖)</span>  who
       enjoys walks, <span title="Mainly Sci-Fi">reading</span>, watching <span title="TODO: Pay cat tax">my cats</span> loaf
       and of course, programming.</p>
     <p>I got into programming as a hobby around the middle of highschool when I used it for small tasks like</p>
     <ul>
         <li>Automated messages for when data usage was too high</li>
         <li>Displaying my timetable in a <a href="https://www.rainmeter.net/">Rainmeter</a> widget</li>
         <li>Calculating my average school results</li>
     </ul>
     <p>And from there I just kept programming and doing random stuff until eventually I decided I
        wanted to make a career out of it</p>
</section>
<section class="card centre mt1" style="width: 30vw">
    <h2>Education</h2>
    <p>I do <abbr title="Computer Science">CS</abbr> at <abbr title="Royal Melbourne Insitute of Technology">RMIT</abbr> (TODO: make this past tense when I graduate) and have worked on assignments that
       have introduced me to technologies such as</p>
    <ul>
        <li>Java</li>
        <li>JavaScript (and Typescript)</li>
        <li>Assorted web (CSS, HTML)</li>
        <li>PHP</li>
        <li>C++</li>
        <li>SQL (And sadly also <a href="https://en.wikipedia.org/wiki/PL/SQL">PL/SQL</a>)
        <li>LaTeX (secret bullet for reports)</li>
    </ul>
    <p>And skills such as</p>
    <ul>
      <li>SDLC techniques such as agile and SCRUM</li>
      <li>Team work 🫂</li>
      <li>Git</li>
      <li>How to test/debug</li>
    </ul>
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
