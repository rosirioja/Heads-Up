<?php

namespace App\Contracts;

interface BaseInterface
{
	public function exists(array $fieldValue, $id = 0);

	public function get($id = 0, $columns = array('*'));
	public function getBy(array $where, $select = ['*'], $join = []);
	public function getByName($name);
	public function getRecord(array $params);

	public function getList(array $args);
	public function getListWhere(array $fieldValue, $columns = array('*'));
	public function getTotalBy($where = array(), $join = []);

	public function getAIStatus();

	public function store(array $data);

	public function update($id = 0, array $data);
	public function updateBy(array $fieldValue, array $data);

	public function delete($id = 0);

}
