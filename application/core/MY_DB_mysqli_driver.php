<?php

class MY_DB_mysqli_driver extends CI_DB_mysqli_driver {



	/**
	 * Execute the query
	 *
	 * Accepts an SQL string as input and returns a result object upon
	 * successful execution of a "read" type query. Returns boolean TRUE
	 * upon successful execution of a "write" type query. Returns boolean
	 * FALSE upon failure, and if the $db_debug variable is set to TRUE
	 * will raise an error.
	 *
	 * @param	string	$sql
	 * @param	array	$binds = FALSE		An array of binding data
	 * @param	bool	$return_object = NULL
	 * @return	mixed
	 */
	public function query($sql, $binds = FALSE, $return_object = NULL)
	{
		if ($sql === '')
		{
			log_message('error', 'Invalid query: '.$sql);
			return ($this->db_debug) ? $this->display_error('db_invalid_query') : FALSE;
		}
		elseif ( ! is_bool($return_object))
		{
			$return_object = ! $this->is_write_type($sql);
		}

		// Verify table prefix and replace if necessary
		if ($this->dbprefix !== '' && $this->swap_pre !== '' && $this->dbprefix !== $this->swap_pre)
		{
			$sql = preg_replace('/(\W)'.$this->swap_pre.'(\S+?)/', '\\1'.$this->dbprefix.'\\2', $sql);
		}

		// Compile binds if needed
		if ($binds !== FALSE)
		{
			$sql = $this->compile_binds($sql, $binds);
		}

		// Is query caching enabled? If the query is a "read type"
		// we will load the caching class and return the previously
		// cached query if it exists
		if ($this->cache_on === TRUE && $return_object === TRUE && $this->_cache_init())
		{
			if (FALSE !== ($cache = $this->CACHE->read($sql)))
			{
				return $cache;
			}
		}

		// Save the query for debugging
		if ($this->save_queries === TRUE)
		{
			$this->queries[] = $sql;
		}

		// Start the Query Timer
		$time_start = microtime(TRUE);

		// Run the Query
		if (FALSE === ($this->result_id = $this->simple_query($sql)))
		{
			if ($this->save_queries === TRUE)
			{
				$this->query_times[] = 0;
			}

			// This will trigger a rollback if transactions are being used
			if ($this->_trans_depth !== 0)
			{
				$this->_trans_status = FALSE;
			}

			// Grab the error now, as we might run some additional queries before displaying the error
			$error = $this->error();

			// Log errors
			log_message('error', 'Query error: '.$error['message'].' - Invalid query: '.$sql);

			if ($this->db_debug)
			{
				// We call this function in order to roll-back queries
				// if transactions are enabled. If we don't call this here
				// the error message will trigger an exit, causing the
				// transactions to remain in limbo.
				while ($this->_trans_depth !== 0)
				{
					$trans_depth = $this->_trans_depth;
					$this->trans_complete();
					if ($trans_depth === $this->_trans_depth)
					{
						log_message('error', 'Database: Failure during an automated transaction commit/rollback!');
						break;
					}
				}

				// Display errors
				return $this->display_error(array('Error Number: '.$error['code'], $error['message'], $sql));
			}

			return FALSE;
		}

		// Stop and aggregate the query time results
		$time_end = microtime(TRUE);
		$this->benchmark += $time_end - $time_start;

		if ($this->save_queries === TRUE)
		{
			$this->query_times[] = $time_end - $time_start;
		}

		// Increment the query counter
		$this->query_count++;

		// Will we have a result object instantiated? If not - we'll simply return TRUE
		if ($return_object !== TRUE)
		{
			// If caching is enabled we'll auto-cleanup any existing files related to this particular URI
			if ($this->cache_on === TRUE && $this->cache_autodel === TRUE && $this->_cache_init())
			{
				$this->CACHE->delete();
			}

			return TRUE;
		}

		// Instantiate the driver-specific result class
		$driver	= 'MY_DB_'.$this->dbdriver.'_result';
		$RES    = new $driver($this);

		// Is query caching enabled? If so, we'll serialize the
		// result object and save it to a cache file.
		if ($this->cache_on === TRUE && $this->_cache_init())
		{
			// We'll create a new instance of the result object
			// only without the platform specific driver since
			// we can't use it with cached data (the query result
			// resource ID won't be any good once we've cached the
			// result object, so we'll have to compile the data
			// and save it)
			$CR = new MY_DB_result($this);
			$CR->result_object	= $RES->result_object();
			$CR->result_array	= $RES->result_array();
			$CR->num_rows		= $RES->num_rows();

			// Reset these since cached objects can not utilize resource IDs.
			$CR->conn_id		= NULL;
			$CR->result_id		= NULL;

			$this->CACHE->write($sql, $CR);
		}

		return $RES;
	}



    function load_driver()
    {
        $driver = 'CI_DB_'.$this->dbdriver.'_result';

        if ( ! class_exists($driver))
        {
            include_once(BASEPATH.'database/DB_result'.".php");
            include_once(BASEPATH.'database/drivers/'.$this->dbdriver.'/'.$this->dbdriver.'_result'.".php");
        }

        $CI =& get_instance();

        $my_driver = config_item('subclass_prefix').'DB_'.$this->dbdriver.'_result';
        $my_driver_file = APPPATH.'core/'.$my_driver.".php";

        if (file_exists($my_driver_file))
        {
            require_once($my_driver_file);
            return $my_driver;
        }
        else
        {
            return $driver;
        }
    }
}