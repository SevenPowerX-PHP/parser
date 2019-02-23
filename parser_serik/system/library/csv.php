<?php
	class CSV {
		private $_csv_file = null;

		/**
		 * @param string $csv_file  - путь до csv-файла
		 */

		public function __construct($csv_file) {
			if (file_exists($csv_file)) { //Если файл существует
				$this->_csv_file = $csv_file; //Записываем путь к файлу в переменную
			}
			else { //Если файл не найден то вызываем исключение
				throw new Exception("Файл " . $csv_file . " не найден");
	        }
		}

		function setCSV(array &$array) {
			if (count($array) == 0) {
				return null;
			}

			$df = fopen($this->_csv_file, 'w');

			foreach ($array as $row) {
				fputcsv($df, $row, ";");
			}

			fclose($df);
		}

		/**
		 * Метод для чтения из csv-файла. Возвращает массив с данными из csv
		 * @return array;
		 */
		public function getCSV() {
			$handle = fopen($this->_csv_file, "r"); //Открываем csv для чтения

			$array_line_full = array(); //Массив будет хранить данные из csv
			//Проходим весь csv-файл, и читаем построчно. 3-ий параметр разделитель поля
			while (($line = fgetcsv($handle, 0, ";")) !== FALSE) {
				$array_line_full[] = $line; //Записываем строчки в массив
			}
			fclose($handle); //Закрываем файл
			return $array_line_full; //Возвращаем прочтенные данные
		}

	}
?>