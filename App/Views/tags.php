
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tags</title>
            <link rel="stylesheet" href="<?='Assets\\tags.css' ?>">
        </head>
        <body>
            <div>
                <form action="" method="post">
                    <input type="text" class="searchtag" id="searchtag" name="searchtag" placeholder="Pesquisar por TAGs">
                    <input type="submit" value="Pesquisar" class="btn-searchtag">
          
                </form>
            
                <table>
                    <tr>
                        <th>RFID</th>
                        <th>DESTINO</th>
                        <th>STATUS</th>
                    </tr>
                    <?php 
                
                    foreach ($tags as $tag){
                    ?>
                    <tr>
                        <td><?= $tag['rfid'] ?></td>
                        <td><?= $tag['destino'] == null ? "Não definido" : $tag['destino']?></td>
                        <td><?= $tag['status_tag'] ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </table>
            </div>
        </body>
        </html>
