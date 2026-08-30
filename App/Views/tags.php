<?php
namespace App\Views;
use App\Controllers\TagsController;

class Tags {

    public static function view(TagsController $tagcntrl){

        $tagcntrl->render_tags();

        ?>
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tags</title>
        </head>
        <body>
            <?php
            foreach ($tagcntrl->tags as $tag) {
            ?>
            <div>
                <pre>
                    <h2>
                        <?= $tag['rfid'] ?>
                    </h2>
                    <span>
                        <?= $tag['destino'] ?>
                    </span>
                    <span>
                        <?= $tag['status_tag'] ?>
                    </span>
                </pre>
            </div>
            <?php
            }
            ?>
        </body>
        </html>
        <?
    }
}