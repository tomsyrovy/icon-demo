<?php

namespace Repository;

use Nette;

class RepositoryNonAbstract extends Repository{

    /** Přepíná hodnoty 0 a 1 v konkrétní tabulce, v konkrétním sloupci a pro konkrétní řádek
     * @param $table - název tabulky
     * @param $column - název sloupce, ve kterém se mění hodnota
     * @param $primary_key - primární klíč (identifikace záznamu)
     * @return int Počet ovlivněných řádků
     */
    public function changeStatus($table, $column, $primary_key) {
		$row = $this->getTable($table)->select($column)->get($primary_key);
		if($row[$column] == 0){
			$newValue = 1;
		}else{
			$newValue = 0;
		}
		return $this->getTable($table)->get($primary_key)->update(array($column => $newValue));
	}

}