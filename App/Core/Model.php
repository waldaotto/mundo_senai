<?php

namespace App\Core;

use PDO;
use App\Core\Database\Connection;
use PDOException;

/**
 * Classe abstrata para geração de Models.
 * 
 * Permite a manipulação de tabelas no banco de dados.
 */
abstract class Model{

    protected PDO $cursor;
    protected Connection $connection;
    protected $primaryKey = "id";
    /**
     * @var String deve ser sobrescrita na classe filha.
     */
    protected String $table;

    public function __construct() {

            $this->cursor = new Connection()->connect();
        }

     /**
     * Realiza a inserção de novos dados dentro da tabela.
     * 
     * @example $this->create(['nome'=>$usuario,'senha"=>12345]);
     * @return int Valor do ID da ultima inserção.
     */
    public function create(array $data): int
    {
       
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "
            INSERT INTO {$this->table}
            ({$columns})
            VALUES ({$placeholders})
        ";

        try{

            $stmt = $this->cursor->prepare($sql);
            $stmt->execute($data);

        } catch (PDOException $error){
            die("Erro durante conexão com banco de dados: $error");
        }

        /**
         * @return int Valor do ID da ultima inserção.
         */
        return (int) $this->cursor->lastInsertId();
    }

    /**
     * Reazliza a busca de todos os itens dentro de uma tabela.
     */
    public function find_all():array {

        $stmt = $this->cursor->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();

    }

    /**
     * Realiza a busca de um valor especifico pela primary key da tabela.
     * @param int $id primary key.
     */
    public function find(int $id):mixed {

        try{

            $stmt = $this->cursor->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
            $stmt->execute([':id' => $id]);

        } catch (PDOException $error) {
            die("Erro durante conexão com banco de dados: $error");
        }

        return $stmt->fetch();
    }

    public function find_by_field(string $field, mixed $value):mixed {

        try{

            $stmt = $this->cursor->prepare("SELECT * FROM {$this->table} WHERE {$field} = :valor");
            $stmt->execute([':valor' => $value]);
            
        } catch (PDOException $error) {
            die("Erro durante conexão com banco de dados: $error");
        }

        return $stmt->fetch();
    }

    public function update(int $id, array $data) {

        $fields = [];

        foreach (array_keys($data) as $key) {
            $fields[] = "{$key} = :{$key}";
        }

        $fields = implode(', ', $fields);

            $sql = "UPDATE {$this->table} SET {$fields} WHERE {$this->primaryKey} = :id";
            
            $stmt = $this->cursor->prepare($sql);
            $data['id'] = $id; // Adiciona o ID aos dados para o bind
            
            return $stmt->execute($data);
        }

         public function delete(int $id){

            $stmt = $this->cursor->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
            
            return $stmt->execute([':id' => $id]);
        }


}