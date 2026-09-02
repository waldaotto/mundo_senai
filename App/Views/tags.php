
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <link rel="stylesheet" href="<?=CSS_PATH.'tags.css' ?>">
        </head>
        <body>
            <br>
            <div>
                <form action="" method="post">
                    <input type="text" class="searchtag" id="searchtag" name="searchtag" placeholder="Pesquisar por TAGs">
                    <input type="submit" value="Pesquisar" class="btn-searchtag">
          
                </form>
            
                <table>
                    <tr>
                        <th>id</th>
                        <th>DESTINO</th>
                        <th>STATUS</th>
                    </tr>
                    <?php 
                
                    foreach ($tags as $tag){
                    ?>
                    <tr>
                        <td><?= $tag['id'] ?></td>
                        <td><?= $tag['tag_uid'] == null ? "Não definido" : $tag['tag_uid']?></td>
                        <td><?= $tag['data_hora'] ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </table>
            </div>
        </body>
        </html>
