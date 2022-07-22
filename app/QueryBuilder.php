<?php
namespace App;
use Aura\SqlQuery\QueryFactory;
use \PDO;

class QueryBuilder
{
    private $pdo;
    private $queryFactory;

    public function __construct(PDO $pdo, QueryFactory $queryFactory)
    {

        $this->pdo = $pdo;
        $this->queryFactory = $queryFactory;
    }

    public function getAll($table) {

        $select = $this->queryFactory ->newSelect();

        $select->cols(['*'])
            ->from($table);

        $sth = $this->pdo->prepare($select->getStatement());

        $sth->execute($select->getBindValues());

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }


    public function insert($data, $table)
    {

        $insert = $this->queryFactory->newInsert();

        $insert
            ->into($table)                   // INTO this table
            ->cols($data);


        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());

        $name = $insert->getLastInsertIdName('id');

        $id = $this->pdo->lastInsertId($name);

        return $id;
    }


    public function update($data, $id, $table)
    {
        $update = $this->queryFactory->newUpdate();

        $update
            ->table($table)                  // update this
            ->cols($data)
        ->where('id = :id')
        ->bindValue('id', $id);

        $sth = $this->pdo->prepare($update->getStatement());

        $sth->execute($update->getBindValues());

    }


    public function delete($table, $id)
    {
        $delete = $this->queryFactory->newDelete();

        $delete
            ->from($table)
            ->where('id = :id')
            ->bindValue('id', $id);

        $sth = $this->pdo->prepare($delete->getStatement());

        $sth->execute($delete->getBindValues());

    }


    public function findOne($table, $id)
    {
        $select = $this->queryFactory ->newSelect();

        $select->cols(['*'])
            ->from($table)
        ->where('id = :id')
        ->bindValue('id', $id);

        $sth = $this->pdo->prepare($select->getStatement());

        $sth->execute($select->getBindValues());

        return $sth->fetch(PDO::FETCH_ASSOC);
    }


    public function findOneByEmail($table, $email)
    {
        $select = $this->queryFactory ->newSelect();

        $select->cols(['*'])
            ->from($table)
            ->where('email = :email')
            ->bindValue('email', $email);

        $sth = $this->pdo->prepare($select->getStatement());

        $sth->execute($select->getBindValues());

        return $sth->fetch(PDO::FETCH_ASSOC);
    }
    
    
}