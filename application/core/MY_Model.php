<?php

class MY_Model extends CI_Model {


    public function getAll($tables = false) {
        if($tables) {            
            return $this->db->get($this->table)->result_array_with_tables();
            
        }
        return $this->db->get($this->table)->result_array();
    }

    public function getOne($id, $tables = false) {
        if($tables) {
            return $this->db->where($this->table.'.id', $id)->get($this->table)->row_array_with_tables();
        }
        return $this->db->where($this->table.'.id', $id)->get($this->table)->row_array();
    }

    public function select($fields = array()) {
        if(!empty($fields)) {
            foreach($fields as $field) {
                $this->db->select($this->table.'.'.$field);
            }
        }
        return $this;
    }

    public function create($data=array()) {
        $this->db->set(array('id'=>null)+$data);
        $this->db->insert($this->table);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        if(empty($data)) return;
        $this->db->where('id', $id);
        $this->db->set($data);
        $this->db->update($this->table);
        return $this;
    }

    public function order_by($field, $or) {
        $this->db->order_by($field, $or);
        return $this;
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
    }

    public function filter($query=array()) {
        foreach((array)$query as $field=>$value) {
            if(empty($value)) continue;
            $this->db->like($field, $value);
        }
        return $this;
    }

    public function where($field, $value) {
        $this->db->where($this->table.'.'.$field, $value);
        return $this;
    }

    public function where_in($field, $values) {
        $this->db->where_in($this->table.'.'.$field, $values);
        return $this;
    }

    public function join_price() {
        $this->db->select('products.*, p.price, p.unit_id as p_unit_id');
        $this->db->join('prices p', 'p.id = products.price_id', 'left');
        return $this;
    }

    public function join_identificator() {
        $this->db->select('pi.name as pi_name');
        $this->db->join('product_identificators as pi', 'pi.id = products.product_identificator_id', 'left');
        return $this;
    }

    public function group_concat() {
        $this->db->select('group_concat(pis.item_id) as pi_items');
        $this->db->join('product_items pis', 'pis.product_id = products.id and pis.product_category_id = 1', 'left');
        $this->db->group_by('products.id');
        return $this;
    }

    public function fields($table) {
        $list =  $this->db->list_fields($table);
        $return = array();
        foreach($list as $l) {
            $return[$l] = $l;
        }
        return $return;
    }
}