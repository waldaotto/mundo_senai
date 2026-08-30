
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tags</title>
            <link rel="stylesheet" href="<?= __DIR__."/../../Public/Assets/tags.css" ?>">
        </head>
        <body>
            <pre>
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
                        <td><?= $tag['destino'] ?></td>
                        <td><?= $tag['status_tag'] ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </table>
            </pre>
        </body>
        </html>
