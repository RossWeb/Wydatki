<?php


if (isset ( $_REQUEST )) {
	// print_r($_REQUEST);
	
	Main::getInstance ()->check ();
}
final class Main {
	private $expenses; // obiekt listy definicji wydatki
	private $income; // obiekt listy definicji przychody
	private $dataExpenses; // obiekt danych wydatki
	private $dataIncome; // obiekt danych przychody
	private static $instance = false; // pole logiczne instancji klasy domyslnie falsz
	private $crud; // obiekt klasy dostepu do bazy danych
	private $expensesListBuff; //tablica listy definicji wydatki
	private $incomeListBuff; //tablica listy definicji przychody
	private $expensesDataBuff; //tablica danych wydatki 
	private $incomeDataBuff; //tablica danych przychody 
	private $expensesScore; //suma wydatkow
	private $incomeScore; //suma przychodow 
	private $expensesFilterScore; //suma wydatkow filtra
	private $incomeFilterScore;//suma przychodow filtra
	private $oldDate; //pole najstarszej daty z bazy danych
	private $filterDate; //data filtra 
	private $filterExpensesList = 0; //filtr listy wydatkow
	private $filterIncomeList = 0;//filtr listy przychodow
	private $error; //pole bledow
	//metoda utworzenia instancji klasy 
	public static function getInstance() {
		if (self::$instance == false)
			self::$instance = new Main ();
		
		return self::$instance;
	}
	//konstruktor klasy ustawiajacy obiekty list definicji, obiekty danych,
	// obiekt daty oraz poszczegolne pola i tworzacy obiekt bazy danych i poszczegolne tabele 
	private function __construct() {
		$this->crud = CRUD::getInstance ();
		
		$this->expenses = new NameList ( "Expenses" );
		$this->income = new NameList ( "Income" );
		$this->dataExpenses = new Data ( "ExpensesData", $this->expenses->getNameTable () );
		$this->dataIncome = new Data ( "IncomeData", $this->income->getNameTable () );
		$this->filterDate = date ( "Y-m" );
		$this->crud->create ( $this->expenses );
		$this->crud->create ( $this->income );
		$this->crud->create ( $this->dataExpenses );
		$this->crud->create ( $this->dataIncome );
		$this->setOldDate ();
		$this->incomeScore = 0;
		$this->expensesScore = 0;
	}
	//metoda zarzadzajaca sterowaniem aplikacji w zaleznosci od wartosci zadania
	public function check() {
		// print_r ( $_REQUEST ); // debug
		if (isset ( $_REQUEST ['button'] )) { // sprawdz czy wcisnieto jakis przycisk dotyczacy list def
		                                      // dodanie pozycji
			if ($_REQUEST ['button'] == "addExpList") { // wydatki
				$this->expenses->setName ( $_REQUEST ['newValue'] );
				$this->crud->insert ( $this->expenses );
			} else if ($_REQUEST ['button'] == "addIncList") { // przychody
				$this->income->setName ( $_REQUEST ['newValue'] );
				$this->crud->insert ( $this->income );
			} else if ($_REQUEST ['button'] == "addExpData") { // wydatki dane
				$this->dataExpenses->setNameListKey ( $_REQUEST ['id'] );
				$this->dataExpenses->setValue ( $_REQUEST ['newValue'] );
				$this->crud->insert ( $this->dataExpenses );
			} else if ($_REQUEST ['button'] == "addIncData") { // przychody dane
				$this->dataIncome->setNameListKey ( $_REQUEST ['id'] );
				$this->dataIncome->setValue ( $_REQUEST ['newValue'] );
				$this->crud->insert ( $this->dataIncome );
			}
			// edytowanie pozycji
			if ($_REQUEST ['button'] == "editExpList") { // wydatki
				$this->expenses->setId ( $_REQUEST ['id'] );
				$this->expenses->setName ( $_REQUEST ['newValue'] );
				$this->crud->update ( $this->expenses );
			} else if ($_REQUEST ['button'] == "editIncList") { // przychody
				$this->income->setId ( $_REQUEST ['id'] );
				$this->income->setName ( $_REQUEST ['newValue'] );
				$this->crud->update ( $this->income );
			} else if ($_REQUEST ['button'] == "editExpData") { // wydatki dane
				$this->dataExpenses->setId ( $_REQUEST ['id'] );
				$this->dataExpenses->setNameListKey ( $_REQUEST ['idList'] );
				$this->dataExpenses->setValue ( $_REQUEST ['newValue'] );
				$this->crud->update ( $this->dataExpenses );
			} else if ($_REQUEST ['button'] == "editIncData") { // przychody dane
				$this->dataIncome->setId ( $_REQUEST ['id'] );
				$this->dataIncome->setNameListKey ( $_REQUEST ['idList'] );
				$this->dataIncome->setValue ( $_REQUEST ['newValue'] );
				$this->crud->update ( $this->dataIncome );
			}
			// usuwanie pozycji
			if ($_REQUEST ['button'] == "delExpList") { // wydatki
				$this->expenses->setId ( $_REQUEST ['id'] );
				$this->crud->delete ( $this->expenses );
			} else if ($_REQUEST ['button'] == "delIncList") { // przychody
				$this->income->setId ( $_REQUEST ['id'] );
				$this->crud->delete ( $this->income );
			} else if ($_REQUEST ['button'] == "delExpData") { // wydatki dane
				$checkArray = $_REQUEST ['id'];
				if (! empty ( $checkArray )) {
					for($i = 0; $i < count ( $checkArray ); $i ++) {
						$this->dataExpenses->setId ( $checkArray [$i] );
						$this->crud->delete ( $this->dataExpenses );
					}
				}
			} else if ($_REQUEST ['button'] == "delIncData") { // przychody dane
				$checkArray = $_REQUEST ['id'];
				if (! empty ( $checkArray )) {
					for($i = 0; $i < count ( $checkArray ); $i ++) {
						$this->dataIncome->setId ( $checkArray [$i] );
						$this->crud->delete ( $this->dataIncome );
					}
				}
			}
		}
		if (isset ( $_REQUEST ['value'] )) { // zaladowanie listy daty
			print $this->getDate ();
		}
		if (isset ( $_REQUEST ['loadDefList'] )) { // zaladowanie listy definicji
			
			$this->setList ();
			print $this->getList ( $_REQUEST ['loadDefList'] );
		}
		if (isset ( $_REQUEST ['loadData'] )) { // zaladowanie danych do tabeli
			if ($_REQUEST ['button'] == "dateButton") {
				$date = $_REQUEST ['filter'];
				if(!empty($date))
				$this->setFilterDate ( $date );
			} else if ($_REQUEST ['button'] == "expensesButtonFilter") {
				$this->filterExpensesList = $_REQUEST ['filter'];
			} else if ($_REQUEST ['button'] == "incomeButtonFilter") {
				$this->filterIncomeList = $_REQUEST ['filter'];
			}
			
			$score = "";
			$this->setData ();
			
			if ( $this->filterExpensesList != null || $this->filterIncomeList != null)
				$score .= $this->getFilterScore ( $_REQUEST ['loadData'] );
			$score .= $this->getScore ( $_REQUEST ['loadData'] );
			$account = $this->incomeScore - $this->expensesScore;
			if($account < 0)
			$account = "<font color=\"red\">" . $account . "</font>";
			$account = "Stan konta : ".$account." zł";
			$array = array (
					$this->getData ( $_REQUEST ['loadData'] ),
					$score, $account  
			);
			echo json_encode ( $array );
		}
	}
	//metoda ustawiajaca obiekty listy lub danych wartosciami z bazy danych 
	private function getObjectList($object, $where = array()) {
		switch ($object) {
			case "Expenses" :
				return $this->crud->read ( $this->expenses, 'NAME', $where );
			case "Income" :
				return $this->crud->read ( $this->income, 'NAME', $where );
			case "ExpensesData" :
				$expensesDataList = $this->crud->read ( $this->dataExpenses, 'Expenses.Name', $where, array (
						"Expenses" => "ID",
						"ExpensesData" => "NAMELIST" 
				) );
				
				if (! empty ( $expensesDataList )) {
					foreach ( $expensesDataList as &$e )
						$this->expensesScore += $e->getValue ();
				}
				return $expensesDataList;
			case "IncomeData" :
				$incomeDataList = $this->crud->read ( $this->dataIncome, 'Income.Name', $where, array (
						"Income" => "ID",
						"IncomeData" => "NAMELIST" 
				) );
				if (! empty ( $incomeDataList )) {
					foreach ( $incomeDataList as &$i )
						$this->incomeScore += $i->getValue ();
				}
				return $incomeDataList;
			case "Date" :
				return $expensesDataList = $this->crud->read ( $this->dataExpenses, 'ID', null, null, 1 );
		}
	}
	// metoda ustawiajaca listy definicji
	private function setList() {
		$this->expensesListBuff = $this->getObjectList ( "Expenses" );
		$this->incomeListBuff = $this->getObjectList ( "Income" );
	}
	//metoda wyswietlajaca wartosci definicji list  w postaci kodu HTML
	private function getList($list) {
		$htmlList = "";
		
		switch ($list) {
			case "Expenses" :
				
				foreach ( $this->expensesListBuff as $e )
					$htmlList .= "<option value=\"" . $e->getId () . "\">" . $e->getName () . "</option>";
				
				break;
			case "Income" :
				
				foreach ( $this->incomeListBuff as $i )
					$htmlList .= "<option value=\"" . $i->getId () . "\">" . $i->getName () . "</option>";
				
				break;
		}
		return $htmlList;
	}
	/// metoda ustawiajaca najstarsza date na postawie wartosci z bazy danych
	private function setOldDate() {
		$expensesDataTemp = $this->getObjectList ( "Date" );
		if (empty ( $expensesDataTemp ))
			$this->oldDate = date ( "Y-m-d" );
		else {
			$this->oldDate = date ( $expensesDataTemp [0]->getDate () );
		}
	}
	// metoda ustawiajaca dane
	private function setData() {
		$this->expensesDataBuff = $this->getObjectList ( "ExpensesData", array (
				"strftime('%Y-%m',DATE)" => $this->filterDate 
		) );
		
		$this->incomeDataBuff = $this->getObjectList ( "IncomeData", array (
				"strftime('%Y-%m',DATE)" => $this->filterDate 
		) );
		if ($this->filterExpensesList != 0)
			$this->setFilter ( "expensesFilter" );
		if ($this->filterIncomeList != 0)
			$this->setFilter ( "incomeFilter" );
	}
	//metoda ustawiajaca filtr danych na postawie ustalonej daty
	private function setFilterDate($date) {
		$this->filterDate = $date;
	}
	//metoda wyswietlajaca wartosci danych w postaci kodu HTML 
	private function getData($data) {
		$html = "";
		$index = 1;
		switch ($data) {
			case "ExpensesData" :
				if ($this->expensesDataBuff != null)
					foreach ( $this->expensesDataBuff as &$ed ) {
						$html .= "<tr id=\"remove\"><td>" . $index . "</td>";
						$html .= "<td>" . $ed->getNameListObject ()->getName () . "</td>";
						$html .= "<td name=\"value\">" . $ed->getValue () . " zł </td>";
						$html .= "<td><div class=\"check\" id=\"editCheckExpData\"><input type=\"radio\" value=\"" . $ed->getId () . "\"  name=\"editCheckExpData\"/></div></td>";
						$html .= "<td><div class=\"check\" id=\"delCheckExpData\"><input type=\"checkbox\" value=\"" . $ed->getId () . "\" name=\"delCheckExpData[]\"/></div></td></tr>";
						$index ++;
					}
				break;
			case "IncomeData" :
				if ($this->incomeDataBuff != null)
					foreach ( $this->incomeDataBuff as &$id ) {
						$html .= "<tr id=\"remove\"><td>" . $index . "</td>";
						$html .= "<td>" . $id->getNameListObject ()->getName () . "</td>";
						$html .= "<td>" . $id->getValue () . " zł </td>";
						$html .= "<td><div class=\"check\" id=\"editCheckIncData\"><input type=\"radio\" value=\"" . $id->getId () . "\" name=\"editCheckIncData\"/></td>";
						$html .= "<td><div class=\"check\" id=\"delCheckIncData\"><input type=\"checkbox\" value=\"" . $id->getId () . "\" name=\"delCheckIncData[]\"/></td></tr>";
						
						$index ++;
					}
				
				break;
		}
		return $html;
	}
	//metoda ustawiajaca filtr danych na podstawie pozycji definicji listy
	private function setFilter($filterData) {
		$expensesData = count ( $this->expensesDataBuff );
		$incomeData = count ( $this->incomeDataBuff );
		switch ($filterData) {
			
			case "expensesFilter" :
				for($i = 0; $i < $expensesData; $i ++) {
					if ($this->expensesDataBuff [$i]->getNameListKey () != $this->filterExpensesList) {
						unset ( $this->expensesDataBuff [$i] );
					} else
						$this->expensesFilterScore += $this->expensesDataBuff [$i]->getValue ();
				}
				break;
			case "incomeFilter" :
				for($i = 0; $i < $incomeData; $i ++) {
					if ($this->incomeDataBuff [$i]->getNameListKey () != $this->filterIncomeList) {
						unset ( $this->incomeDataBuff [$i] );
					} else
						$this->incomeFilterScore += $this->incomeDataBuff [$i]->getValue ();
				}
				
				break;
		}
	}
	//metoda zwracajaca filtr pozycji definicji listy
	private function getFilter($filterData) {
		switch ($filterData) {
			case "ExpensesFilterData" :
				return $this->filterExpensesList;
			case "IncomeFilterData" :
				return $this->filterIncomeList;
		}
	}
	//metoda zwracajaca sume wartosci danych
	private function getScore($nameData) {
		$score = "Suma : ";
		switch ($nameData) {
			case "ExpensesData" :
				$score .= $this->expensesScore;
				break;
			case "IncomeData" :
				$score .= $this->incomeScore;
				break;
		}
		return $score . " zł";
	}
	//metoda zwracajaca sume wartosci danych filtra
	private function getFilterScore($nameData) {
		$score = "Suma filtra : ";
		switch ($nameData) {
			case "ExpensesData" :
				$score .= $this->expensesFilterScore;
				break;
			case "IncomeData" :
				$score .= $this->incomeFilterScore;
				break;
		}
		return $score . " zł  ";
	}
	//metoda zwracajaca miesiac jako nazwe
	private function getMonth($value) {
		$month = array (
				'null',
				'Styczeń',
				'Luty',
				'Marzec',
				'Kwiecień',
				'Maj',
				'Czerwiec',
				'Lipiec',
				'Sierpień',
				'Pazdziernik',
				'Listopad',
				'Grudzień' 
		);
		
		return $month [$value];
	}
	//metoda zwracajaca liste wartosci daty
	private function getDate() {
		$html = "<select class=\"dateChild\" id=\"month\">";
		for($i = 1; $i < 12; $i ++) {
			$monthNumber = $i;
			if ($monthNumber < 10)
				$monthNumber = sprintf ( "%02d", $monthNumber );
			$html .= "<option value=\"" . $monthNumber;
			if (date ( 'n' ) == $i)
				$html .= "\" selected=\"true";
			$html .= "\">" . $this->getMonth ( $i ) . "</option>";
		}
		$html .= "</select><select class=\"dateChild\" id=\"year\">";
		$year = $this->getYear ();
		for($i = 1; $i < count ( $year ); $i ++) {
			
			$html .= "<option value=\"" . $year [$i];
			if (date ( 'o' ) == $i)
				$html .= "\" selected=\"true";
			$html .= "\">" . $year [$i] . "</option>";
		}
		$html .= "</select>";
		return $html;
	}
	//metoda zwracajaca liste wartoci roku liczac od najstarszej daty
	private function getYear() {
		$year = array ();
		
		$year [] = 'null';
		$i = date ( "Y", strtotime ( $this->oldDate ) );
		for($i; $i <= date ( "o" ); $i ++) {
			$year [] = $i;
		}
		return $year;
	}
	private function getError() {
		return $this->error;
	}
}
// klasa dostepowa do bazy
final class CRUD {
	private $db; // obiekt modulu SQLite - otwiera połaczenie z baza
	private static $instance = false; // pole logiczne instancji klasy domyslnie falsz
	//konstruktor klasy, ustawia obiekt modulu SQLite
	private function __construct() {
		$this->db = new SQLite3 ( '..\database\base.db' );
	}
	//destruktor klasy, zamyka polaczenie z baza
	public function __destruct() {
		$this->db->close ();
	}
	//metoda utworzenia instancji klasy
	public static function getInstance() {
		if (self::$instance == false)
			self::$instance = new CRUD ();
		
		return self::$instance;
	}
	//metoda utworzenia tabeli bazy danych
	public function create($table) {
		$st = $this->db->exec ( $table->getSqlCreate () );
	}
	//metoda odczytu wartosci z tabeli bazy danych. Co ciekawe umożliwa laczenie tabel powiazanych 
	//oraz wyswietlanie wartosci na postawie warunkow
	public function read($table, $order, $where = array(), $join = array(), $limit = 0) {
		$sql = 'select * from ' . $table->getNameTable ();
		if (! empty ( $join )) {
			$key = array_keys ( $join );
			$sql .= ' JOIN ' . $key [0] . " ON " . $key [0] . "." . $join [$key [0]] . " = " . $key [1] . "." . $join [$key [1]] . " ";
		}
		
		if (! empty ( $where ))
			foreach ( array_keys ( $where ) as $key )
				$sql .= ' WHERE ' . $key . ' = \'' . $where [$key] . '\'';
		
		$sql .= ' ORDER BY ' . $order . ' ASC';
		if (! empty ( $limit ))
			
			$sql .= ' LIMIT ' . $limit;
		
		$st = $this->db->prepare ( $sql );
		if ($st) {
			$row = $st->execute ();
			while ( $list = $row->fetchArray ( SQLITE3_NUM ) ) {
				// print_r($list); debug
				$tableList [] = $table->createInstance ( $list );
			}
			
			return $tableList;
		} else {
			print $this->db->lastErrorMsg ();
			return null;
		}
	}
	//metoda wstawiajaca rekord do tabeli bazy danych
	public function insert($table) {
		$st = $this->db->prepare ( $table->getSqlInsert () );
		if ($st)
			
			$st->execute ();
		
		else
			print $this->db->lastErrorMsg ();
	}
	//metoda aktualizujaca rekord w tabeli bazy danych
	public function update($table) {
		$st = $this->db->exec ( $table->getSqlUpdate () );
	}
	//metoda usuwająca rekord z tabeli bazy danych
	public function delete($table) {
		$st = $this->db->exec ( 'DELETE FROM ' . $table->getNameTable () . ' WHERE ID = \'' . $table->getId () . '\'' );
	}
	//metoda usuwajaca tabele
	public function deleteTable($table) {
		$st = $this->db->exec ( 'DROP TABLE IF EXISTS ' . $table->getNameTable () );
	}
}
class NameList {
	private $nameTable; // nazwa tabeli bazy danych
	private $name; //nazwa pozycji
	private $id; //numer klucza w tabeli bazy danych
	
	//konstruktor ustawiajacy wartosci obiektu
	function __construct($nameTable, $array = array()) {
		$this->nameTable = $nameTable;
		
		if (! empty ( $array )) {
			$this->id = $array [0];
			$this->name = $array [1];
		}
	}
	//metoda tworzaca nowa instacje klasy
	function createInstance($array) {
		return new NameList ( $this->nameTable, $array );
	}
	//metoda zwracajaca wartosc klucza 
	function getId() {
		return $this->id;
	}
	//metoda zwracajaca nazwe obiektu 
	function getName() {
		return $this->name;
	}
	//metoda ustawiajaca klucz obiektu
	function setId($id) {
		$this->id = $id;
	}
	//metoda ustawiajaca nazwe obiektu
	function setName($name) {
		$name [0] = strtoupper ( $name [0] );
		$this->name = $name;
	}
	//metoda zwracajaca kod SQL do utworzenia tabeli w bazie danych
	function getSqlCreate() {
		$this->sqlCreate = <<<EOF
		CREATE TABLE IF NOT EXISTS {$this->nameTable}
		(ID INTEGER PRIMARY KEY NOT NULL,
		NAME TEXT UNIQUE NOT NULL);
EOF;
		return $this->sqlCreate;
	}
	//metoda zwracajaca nazwe tabeli bazy danych
	function getNameTable() {
		return $this->nameTable;
	}
	//metoda zwracajaca kod SQL do aktualizacji rekordu tabeli w bazie danych
	function getSqlUpdate() {
		$this->sqlUpdate = 'UPDATE ' . $this->nameTable . ' SET NAME = \'' . $this->name . '\' WHERE ID = ' . $this->id;
		
		return $this->sqlUpdate;
	}
	//metoda zwracajaca kod SQL do wstawienia rekordu tabeli w bazie danych
	function getSqlInsert() {
		$this->sqlInsert = 'INSERT INTO ' . $this->nameTable . ' ( NAME ) VALUES ( \'' . $this->name . '\' );';
		
		return $this->sqlInsert;
	}
}
class Data {
	private $id; //numer klucza danych w tabeli bazy danych
	private $nameListKey; //numer klucza pozycji z listy definicji w tabeli bazy danych
	private $value; //wartosc okreslonej pozycji listy definicji  
	private $date; //data
	private $nameTable; //nazwa tabeli bazy danych
	private $foreignNameTable; //nazwa tabeli powiazanej
	private $nameListObject; //obiekt listy definicji powiazany z rekordem danych
	//konstruktor ustawiajacy nazwe, tabele powiazana oraz poszczegolne wartosci
	function __construct($nameTable, $foreignNameTable, $array = array()) {
		$this->nameTable = $nameTable;
		$this->foreignNameTable = $foreignNameTable;
		if (! empty ( $array )) {
			$this->id = $array [0];
			$this->nameListKey = $array [1];
			$this->value = $array [2];
			$this->date = $array [3];
			
			$this->nameListObject = new NameList ( $this->foreignNameTable, array (
					$array [4],
					$array [5] 
			) );
		}
	}
	//metoda zwracajaca kod SQL do utworzenia tabeli w bazie danych
	function getSqlCreate() {
		$sqlCreate = <<<EOF
		CREATE TABLE IF NOT EXISTS {$this->nameTable}
		(ID INTEGER PRIMARY KEY NOT NULL,
		NAMELIST INT NOT NULL,
		VALUE REAL NOT NULL,
		DATE TEXT NOT NULL, FOREIGN KEY (NAMELIST)
		REFERENCES {$this->foreignNameTable} ON UPDATE CASCADE ON DELETE NO ACTION);
EOF;
		return $sqlCreate;
	}
	//metoda zwracajaca kod SQL do wstawienia rekordu tabeli w bazie danych
	function getSqlInsert() {
		$sqlInsert = 'INSERT INTO ' . $this->nameTable . ' ( NAMELIST, VALUE, DATE )
				 VALUES ( \'' . $this->nameListKey . '\', \'' . $this->value . '\',\'' . Date ( "Y-m-d" ) . '\');';
		
		return $sqlInsert;
	}
	//metoda zwracajaca kod SQL do aktualizacji rekordu tabeli w bazie danych
	function getSqlUpdate() {
		$sqlUpdate = 'UPDATE ' . $this->nameTable . ' SET NAMELIST = \'' . $this->nameListKey . '\', VALUE = \'' . $this->value . '\' WHERE ID = \'' . $this->id . '\'';
		
		return $sqlUpdate;
	}
	//metoda zwracajaca nazwe tabeli
	function getNameTable() {
		return $this->nameTable;
	}
	//metoda zwracajaca klucz danych rekordu w bazie danych
	function getId() {
		return $this->id;
	}
	//metoda zwracaja powiazany klucz pozycji listy defincji
	function getNameListKey() {
		return $this->nameListKey;
	}
	//metoda zwracajaca wartosc okreslonej pozycji list definicji
	function getValue() {
		return $this->value;
	}
	//metoda zwracajaca ustalona date
	function getDate() {
		return $this->date;
	}
	//metoda ustawiajaca klucz danych w bazie danych
	function setId($id) {
		$this->id = $id;
	}
	//metoda ustawiajaca klucz powiazany pozycji list definicji
	function setNameListKey($nameListKey) {
		$this->nameListKey = $nameListKey;
	}
	//metoda ustawiajaca wartosc okreslonej pozycji list definicji
	function setValue($value) {
		$this->value = $value;
	}
	//metoda tworzaca nowa instancje klasy
	function createInstance($array) {
		// print_r($array); // debug
		return new Data ( $this->nameTable, $this->foreignNameTable, $array );
	}
	//metoda zwracajaca obiekt powiazany listy definicji
	function getNameListObject() {
		return $this->nameListObject;
	}
}

?>