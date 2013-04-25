<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	public function index()
	{
		echo "ok";
	}

	public function countries() {
		$db = $this->db->query(
		" SELECT 
			countryId,
			name
			FROM countries
			ORDER BY name
		");

		$this->output
	    ->set_content_type('application/json')
	    ->set_output(json_encode($db->result()));
	}

	public function heat($latitude, $longitude, $fromdte = false, $todate = false) {

	}

	public function setlocation($uuid, $latitude, $longitude) {
		$db = $this->db->query(
		" SELECT 
			countryId
			FROM users
			WHERE uuid = " . $this->db->escape($uuid) . "
		");
		if($db->row()) {
			// just do it
		} else {
			// ERROR
		}
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */