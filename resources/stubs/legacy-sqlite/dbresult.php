<?php

require_once __DIR__ . '/../general/dbresult.php';

class CDBResultSqlite extends CAllDBResult
{
    protected function FetchRow()
    {
        if (is_object($this->result) && method_exists($this->result, 'fetch')) {
            $row = $this->result->fetch();

            return $row === null ? false : $row;
        }

        return false;
    }

    public function SelectedRowsCount()
    {
        if ($this->nSelectedCount !== false) {
            return $this->nSelectedCount;
        }

        if (is_object($this->result) && method_exists($this->result, 'getSelectedRowsCount')) {
            return $this->result->getSelectedRowsCount();
        }

        return 0;
    }

    public function AffectedRowsCount()
    {
        return 0;
    }

    public function FieldsCount()
    {
        if (is_object($this->result) && method_exists($this->result, 'getFieldsCount')) {
            return $this->result->getFieldsCount();
        }

        return 0;
    }

    public function FieldName($iCol)
    {
        if (is_object($this->result) && method_exists($this->result, 'getFields')) {
            $names = array_keys($this->result->getFields());

            return $names[$iCol] ?? '';
        }

        return '';
    }

    protected function GetRowsCount(): ?int
    {
        if (is_object($this->result) && method_exists($this->result, 'getSelectedRowsCount')) {
            return $this->result->getSelectedRowsCount();
        }

        return null;
    }

    protected function Seek(int $offset): void
    {
    }
}

class CDBResult extends CDBResultSqlite
{
}
