<?php 

class MY_DB_mysqli_result extends CI_DB_mysqli_result {

	protected function _fetch_fields() {
		return $this->result_id->fetch_all();
    }
    
    protected function _fetch_field_direct($id) {
        return $this->result_id->fetch_field_direct($id);
    }
	    
    public function result_array_with_tables()
    {
        if (count($this->result_array) > 0) {
            return $this->result_array;
        }

        // In the event that query caching is on, the result_id variable
        // will not be a valid resource so we'll simply return an empty
        // array.
        if (! $this->result_id or $this->num_rows === 0) {
            return array();
        }

        if (($c = count($this->result_object)) > 0) {
            for ($i = 0; $i < $c; $i++) {
                $this->result_array[$i] = (array) $this->result_object[$i];
            }

            return $this->result_array;
        }

        is_null($this->row_data) or $this->data_seek(0);
        while ($rows = $this->_fetch_fields()) {
            foreach ($rows as $row) {
                foreach ($row as $field =>$value) {
                    $fields = $this->_fetch_field_direct($field);
                    $table[$fields->table][$fields->orgname] = $value;
                }
                $this->result_array[] = $table;
            }
        }

        return $this->result_array;
    }


	/**
	 * Returns a single result row - array version
	 *
	 * @param	int	$n
	 * @return	array
	 */
	public function row_array_with_tables($n = 0)
	{
		$result = $this->result_array_with_tables();
		if (count($result) === 0)
		{
			return NULL;
		}

		if ($n !== $this->current_row && isset($result[$n]))
		{
			$this->current_row = $n;
		}

		return $result[$this->current_row];
	}
}