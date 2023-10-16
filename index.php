<?php
$CSS_FILES = ["components.css", "main.css", "variables.css", "utils.css"]
?>
<html>
  <head>
    <?php include 'meta.php'?>
    <title>Teehee</title>
    <?php foreach ($CSS_FILES as $file): ?>
      <link rel='stylesheet' href='css/<?=$file?>'/>
    <?php endforeach ?>
  </head>
  <body>
      <section class="card centre" style="width: 10em">
        <h1 class="centre">Hello</h1>
        <img id="handWave" src="imgs/hand.svg" alt="Yellow splayed hand, palm facing forward"/>
      </section>
  </body>
    <!-- We only want hot reload when developing -->
    <?= php_sapi_name() !== 'cli' ? "<script src='scripts/reload.js'></script>" : "" ?>
</html>
