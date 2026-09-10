<!DOCTYPE html>
<head>
    <script src="<?=JS_PATH.'leituras.js'?>"></script>
</head>
<body>
    <div>
        <?php
        if (isset($empty)){
          ?>
          <h2>Nenhuma tag.</h2>
          <?php
        }
        else {
        ?>
        <h2>ID: <?=$id?></h2>
        <h2>rfid: <?=$rfid?></h2>
        <h2>data_hora: <?=$data_hora?></h2>
        <?php
        }
        ?>
    </div>
</body>
</html>