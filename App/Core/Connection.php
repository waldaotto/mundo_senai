<?php

namespace App\Core;
use PDO;
use PDOException;

/**
 * Estabelece a coneção com o banco de dados.
 * 
 * Realiza uma coneção via PDO com banco de dados, para posteriores maniipulações.
 * **/
class Connection {

    private array $config;   
    private ?PDO $pdo = null;

    public function __construct() {
        // Carrega as configurações durante a instanciação da classe
        $this->config = require __DIR__ . '/env.php';
    }

    /**
     * Cria a conexão com o banco de dados.
     * 
     * @return pdo $pdo
     */
    public function connect(): PDO {
        // Chaves {} para interpolar o array dentro da string
        $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['db']};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            ];

        try{
            $this->pdo = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['password'],
                $options
            );
        }catch(PDOException $e){
            die("Erro na conexão com servidor. {$e->getMessage()}");
        }

        return $this->pdo;
    }

    /**
     * Fecha a conexão com o banco de dados.
     */
    public function disconnect():void{

        $this->pdo = null;
        return;

    }
}