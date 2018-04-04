<?php

namespace Util;

abstract class Db {

    private $db = null;
    
    public function __construct($filepath) {
        $this->db = new \SQLite3($filepath);
    }
    
    public function __destruct() {
        if ($this->transactionId !== null) {
            error_log('Database closed before commit');
        }
        if ($this->db !== null) {
            $this->db->close();
        }
    }
    
    private $transactionId = null;
    
    public function startTransaction() {
        $newTransactionId = false;
        if($this->transactionId === null) {
            $this->transactionId = $newTransactionId = uniqid();
            
            $this->exec('BEGIN;');
        }
        
        return $newTransactionId;
    }
    
    public function commitTransaction($id) {
        if($this->transactionId !== null && $this->transactionId === $id) {
            $this->transactionId = null;

            $this->exec('COMMIT;');
        }
    }

    public function rollbackTransaction($id) {
        if($this->transactionId !== null && $this->transactionId === $id) {
            $this->transactionId = null;

            $this->exec('ROLLBACK;');
        }
    }

    public function delete($table, array $filters) {
        $sqlFilters = $sqlUpdates = [];
        foreach($filters as $column => $value) {
            $sqlFilters[] = sprintf('%s = %s', $column, ':'.$column);
        }
        
        $sql = sprintf(
            'DELETE FROM %s WHERE (%s)',
            $table,
            implode(' AND ', $sqlFilters)
        );
        
        error_log($sql);
        $stmt = $this->db->prepare($sql);

        if( $stmt === FALSE ) {
            throw new Db\PreparedStatementException(
                    $this->db->lastErrorCode(),
                    $this->db->lastErrorMsg(),
                    $sql
                );
        }

        foreach($filters as $column => $value) {
            if($stmt->bindValue(':'.$column, $value, \SQLITE3_TEXT) === FALSE) {
                throw new Db\PreparedStatementException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $sql
                    );
            }
        }
        
        if( ($result = $stmt->execute()) === FALSE) {
                throw new Db\ExecutionException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $sql
                    );
        }
        
        $stmt->close();
    }
    public function insert($table, array $inserts) {
        $sqlInserts = [];
        foreach($inserts as $column => $value) {
            $sqlInserts[] = sprintf('%s', ':'.$column);
        }
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', array_keys($inserts)),
            implode(', ', $sqlInserts)
        );

        error_log($sql . ' ' . implode(':',$inserts));
        $stmt = $this->db->prepare($sql);
        
        if( $stmt === FALSE ) {
            throw new Db\PreparedStatementException(
                    $this->db->lastErrorCode(),
                    $this->db->lastErrorMsg(),
                    $sql
                );
        }

        foreach($inserts as $column => $value) {
            if($stmt->bindValue(':'.$column, strval($value), \SQLITE3_TEXT) === FALSE) {
                throw new Db\PreparedStatementException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $sql
                    );
            }
        }
        
        if( ($result = $stmt->execute()) === FALSE) {
                throw new Db\PreparedStatementException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $sql
                    );
        }
        
        $stmt->close();
        
        return $this->db->lastInsertRowID();
    }
    public function update($table, array $filters, array $updates) {
        $sqlFilters = $sqlUpdates = [];
        foreach($filters as $column => $value) {
            $sqlFilters[] = sprintf('%s = %s', $column, ':'.$column);
        }
        foreach($updates as $column => $value) {
            $sqlUpdates[] = sprintf('%s = %s', $column, ':'.$column);
        }
        $sql = sprintf(
            'UPDATE %s SET %s WHERE (%s)',
            $table,
            implode(', ', $sqlUpdates),
            implode(' AND ', $sqlFilters)
        );

        error_log($sql);
        $stmt = $this->db->prepare($sql);

        if( $stmt === FALSE ) {
            throw new Db\PreparedStatementException(
                    $this->db->lastErrorCode(),
                    $this->db->lastErrorMsg(),
                    $sql
                );
        }

        foreach($filters as $column => $value) {
            if($stmt->bindValue(':'.$column, $value, \SQLITE3_TEXT) === FALSE) {
                throw new Db\PreparedStatementException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $sql
                    );
            }
        }
        foreach($updates as $column => $value) {
            if($stmt->bindValue(':'.$column, $value, \SQLITE3_TEXT) === FALSE) {
                throw new Db\PreparedStatementException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $sql
                    );
            }
        }

        if( ($result = $stmt->execute()) === FALSE) {
                throw new Db\PreparedStatementException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $sql
                    );
        }
        
        $affected = $this->db->changes();
        $stmt->close();
        
                
        if($affected < 1) {
            throw new Exception('Nothing to update');
        }
    }
    
    public function query($query, array $params) {
        $stmt = $this->db->prepare($query);
        
        if( $stmt === FALSE ) {
            throw new Db\PreparedStatementException(
                    $this->db->lastErrorCode(),
                    $this->db->lastErrorMsg(),
                    $query
                );
        }
        
        foreach($params as $key => $value) {
            if($stmt->bindValue(':'.$key, $value, \SQLITE3_TEXT) === FALSE) {
                throw new Db\PreparedStatementException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $query
                    );
            }
        }
        
        if( ($result = $stmt->execute()) === FALSE) {
                throw new Db\PreparedStatementException(
                        $this->db->lastErrorCode(),
                        $this->db->lastErrorMsg(),
                        $query
                    );
        }
        
        $array = array();
        while ( $row = $result->fetchArray(\SQLITE3_ASSOC) ) {
            $array[] = $row;
        }
        $stmt->close();
        
        return $array;
    }
    
    public function exec($sql) {
        return $this->db->exec($sql);
    }
}
