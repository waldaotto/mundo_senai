<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <link rel="stylesheet" href="<?= CSS_PATH.'header.css' ?>">
</head>
    <header>
        <ul>
            <li><a href="<?=url_to('/tags')?>">Tags</a></li>
            <li><a href="<?=url_to('/')?>">Home</a></li>
            <li><a href="<?=url_to('/tags')?>">Extra</a></li>
        </ul>

        <form action="logout" method="post">
            <button type="submit" name="logout" id="logout">Logout</button>
        </form>
    </header>
