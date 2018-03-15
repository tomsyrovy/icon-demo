<?php

namespace Repository;

use Nette;

/**
 * Provádí operace nad databázovou tabulkou.
 */
abstract class Repository extends Nette\Object{
	/** @var Nette\Database\Connection */
	protected $connection;

	public static $connectionStatic;

	public function __construct(Nette\Database\Connection $db){
		$this->connection = $db;
		self::$connectionStatic = $db;
	}


	/**
	 * Vrací objekt reprezentující databázovou tabulku.
	 * @return Nette\Database\Table\Selection
	 */
	protected function getTable($tableName = null){
		if($tableName === null){
			// název tabulky odvodíme z názvu třídy
			preg_match('#(\w+)Repository$#', get_class($this), $m);
			return $this->connection->table(lcfirst($m[1]));
		}else{
			return $this->connection->table($tableName);
		}
	}

}