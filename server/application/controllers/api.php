<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	var $retorno;

	public function __construct() {
		parent::__construct();
		$this->retorno = new stdClass();
		$this->retorno->error = false;
		$this->retorno->data = "success";
	}

	public function index() {
		echo "daub";
	}

	public function getheat($countryId, $latitude, $longitude, $distance = 30, $fromdate = '2013-01-31', $todate = false) {
		if($countryId && $latitude && $longitude) {
			$db = $this->db->query(
			" SELECT
				latitude,
				longitude,
				num AS count

				FROM (
					SELECT
					latitude,
					longitude,
					COUNT(*) AS num,
					ROUND( 
						( 
							6371 * acos( 
								cos( radians(" . floatval($latitude) . ") ) 
								* cos( radians(latitude ) ) 
								* cos( radians(longitude) - radians(" . floatval($longitude) . ") ) 
								+ sin( radians(" . floatval($latitude) . ") ) 
								* sin( radians(latitude) ) 
							) 
						)
					, 2) AS distance

					FROM locations
					JOIN users USING(uuid)

					WHERE users.countryId = " . intval($countryId) . "
					AND locations.creation BETWEEN " . $this->db->escape($fromdate). " AND " . ($todate ? $this->db->escape($todate) : "NOW()") . " 

					GROUP BY distance
					ORDER BY distance ASC
				) a

				WHERE distance < " . floatval($distance) . "
			");

			$this->retorno->data = $db->result();

			foreach ($this->retorno->data as $row) {
				$row->latitude = floatval($row->latitude);
				$row->longitude = floatval($row->longitude);
				$row->count = intval($row->count);
			}

		} else {
			$this->retorno->data = "missing parameters";
			$this->retorno->error = true;
		}

		$this->output
	    ->set_content_type('application/json')
	    ->set_output(json_encode($this->retorno));
		
	}

	public function getuserheat($uuid, $latitude, $longitude, $distance = 30, $fromdate = '2013-01-31', $todate = false) {
		if($uuid && $latitude && $longitude) {
			$db = $this->db->query(
			" SELECT
				latitude,
				longitude,
				num AS count

				FROM (
					SELECT
					latitude,
					longitude,
					COUNT(*) AS num,
					ROUND( 
						( 
							6371 * acos( 
								cos( radians(" . floatval($latitude) . ") ) 
								* cos( radians(latitude ) ) 
								* cos( radians(longitude) - radians(" . floatval($longitude) . ") ) 
								+ sin( radians(" . floatval($latitude) . ") ) 
								* sin( radians(latitude) ) 
							) 
						)
					, 2) AS distance

					FROM locations

					WHERE uuid = " . intval($uuid) . "
					AND creation BETWEEN " . $this->db->escape($fromdate). " AND " . ($todate ? $this->db->escape($todate) : "NOW()") . " 

					GROUP BY distance
					ORDER BY distance ASC
				) a

				WHERE distance < " . floatval($distance) . "
			");

			$this->retorno->data = $db->result();

			foreach ($this->retorno->data as $row) {
				$row->latitude = floatval($row->latitude);
				$row->longitude = floatval($row->longitude);
				$row->count = intval($row->count);
			}

		} else {
			$this->retorno->data = "missing parameters";
			$this->retorno->error = true;
		}

		$this->output
	    ->set_content_type('application/json')
	    ->set_output(json_encode($this->retorno));
	}

	public function getcountries() {
		$db = $this->db->query(
		" SELECT 
			countryId,
			name
			FROM countries
			ORDER BY name
		");

		$this->retorno->data = $db->result();

		$this->output
	    ->set_content_type('application/json')
	    ->set_output(json_encode($this->retorno));
	}

	public function getusers($uuid = false) {
		$db = $this->db->query(
		" SELECT 
			uuid,
			countryId
			FROM users
			" . ($uuid ? " WHERE uuid = " . $this->db->escape($uuid) : "" ) . "
		");

		$this->retorno->data = $db->result();

		foreach ($this->retorno->data as $user) {
			$user->countryId = intval($user->countryId);
		}

		$this->output
	    ->set_content_type('application/json')
	    ->set_output(json_encode($this->retorno));
	}

	public function setuser($uuid, $countryId) {
		$db = $this->db->query(
		" SELECT 
			countryId
			FROM users
			WHERE uuid = " . $this->db->escape($uuid) . "
		");

		if($db->row()) {
			$db = $this->db->query(
			"	UPDATE users 
				SET countryId = " . intval($countryId) . "
				WHERE uuid = " . $this->db->escape($uuid) . "
			");
		} else {
			$db = $this->db->query(
			"	INSERT INTO 
				users (uuid, countryId)
				VALUES (" . $this->db->escape($uuid) . ", " . intval($countryId) . ")
			");
		}

		$this->output
	    ->set_content_type('application/json')
	    ->set_output(json_encode($this->retorno));
	}

	public function setposition($uuid, $latitude, $longitude) {
		if($latitude && $longitude) {
			$db = $this->db->query(
			" SELECT 
				countryId
				FROM users
				WHERE uuid = " . $this->db->escape($uuid) . "
			");

			if($db->row()) {
				$db = $this->db->query(
				"	INSERT INTO 
					locations (uuid, latitude, longitude)
					VALUES (" . $this->db->escape($uuid) . ", " . floatval($latitude) . ", " . floatval($longitude) . ")
				");
			} else {
				$this->retorno->data = "invalid user";
				$this->retorno->error = true;
			}
		} else {
			$this->retorno->data = "missing parameters";
			$this->retorno->error = true;
		}

		$this->output
	    ->set_content_type('application/json')
	    ->set_output(json_encode($this->retorno));
	}

	public function genpositions($latitude = false, $longitude = false){
		if($latitude && $longitude) {
			$db = $this->db->query("SELECT uuid FROM users");
			foreach ($db->result() as $row) {
				for ($i=0; $i < 100; $i++) { 
					$rLat = $latitude + ( (rand(0,100000)-50000) * 0.000001 );
					$rLng = $longitude + ( (rand(0,100000)-50000) * 0.000001 );
					$this->setposition($row->uuid, $rLat, $rLng);
				}
			}
		}
		
	}

}
