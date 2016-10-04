<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->database();
		// $this->output->enable_profiler(true);
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{

die;
		if($this->input->post()){
			echo '<pre>';
			// echo getcwd(); die;
			// $this->load->view('welcome_message');

			// $this->db->from('user_location');
			// $res = $this->db->get()->result();

			$table = 'user_location';

			$i = (string)$this->input->post('number');

			$file = "csv/QueryResults{$i}.csv";

			$csv = array_map('str_getcsv', file($file));

			// unset column names
			unset($csv[0]);

			foreach ($csv as &$value) {
				// var_dump($value);
				$value = array_combine(array('so_id', 'so_acc_id', 'loc'), array_values($value));
			}

			// $csv = str_getcsv(, "\n" , '"');
	// print_r($csv);
// die;
			// $this->db->query("set {$table} utf8");
			$this->db->insert_batch($table, $csv);
			echo 'done</pre>';
	// die;
			// print_r($csv);
		}
		else{
			$this->load->helper('cookie');
			$c = get_cookie('index');
			// var_dump($c);
			set_cookie('index', $c ? ($c+1) : 1, 28800);
			$datap['cc'] = $c;
			$this->load->view('form', $datap);
		}
	}

	function check(){
		die;
		$table = 'user_location';

		$i = (string)18;

		$file = "csv/QueryResults{$i}.csv";

		$csv = array_map('str_getcsv', file($file));

		// unset column names
		unset($csv[0]);
echo '<Pre>';
		foreach ($csv as $k => &$value) {
			
			if(count($value) != 3){
				print_r($k);
				print_r($value);
			}
			$value = array_combine(array('so_id', 'so_acc_id', 'loc'), array_values($value));
		}
		echo '</Pre>';
	}

	function curl(){
		echo '<pre>';

// 		$db_res = $this->db->get_where('user_location', array(
// 			'loc !='=>'',
// 			'status'=>1,

// 			), 50)->result();
// print_r($db_res);
		$get = http_build_query(array('key'=>'AIzaSyCU09e0zNuWJjk3a9cjV_U5PR9TDXbP1OI',
								'address'=>'Bangalore'));	

		$f = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?'.$get);

		$array_resp = json_decode($f);

		if(isset($array_resp->status)){



			print_r($array_resp->results);
		}
		echo '</pre>';
	}

	function single_word(){
		echo '<pre>';
		// die;
		$samples = array('Chicago, IL','Berlin, Germany','Toronto, Canada');
		// $samples = array('Moscow');
// 'Ukraine','California','Belgium'
		$final_loc = [];

		foreach ($samples as $value) {
			$new = [];
			$new['place'] = $value;

			$get = http_build_query(array('key'=>'AIzaSyCU09e0zNuWJjk3a9cjV_U5PR9TDXbP1OI',
								'address'=>$value));	

			$f = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?'.$get);

			$array_resp = json_decode($f);

			if(isset($array_resp->status)){
				if($array_resp->status == 'OK'){
					// print_r($array_resp); die;
					if(count($array_resp->results) ==1){
						$array_resp->results = $array_resp->results[0];

						$new['loc'] = $array_resp->results->geometry->location;
						$this->db->where('loc', $value);
						$this->db->update('user_location', array('lat'=>$new['loc']->lat,
							'lng'=>$new['loc']->lng));
					}
				}
			}

			$final_loc[] = $new;

		}

		print_r($final_loc);
	}

	function group_by(){
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
		$q  = "SELECT loc, count(loc) as loc_count FROM `user_location` WHERE lat=0 and lng =0 and `status`=1 group by loc ORDER BY `loc_count` DESC limit 10";

		$res = $this->db->query($q)->result();

		echo '<pre>';
		print_r($res);

		foreach ($res as $value) {
			$new = [];
			// if(in_array($value->loc, array('New York, United States', 'Toronto, Canada','Sydney, Australia', 'Melbourne, Australia'))){
			// 	continue;
			// }
			$new['place'] = $value->loc;

			$get = http_build_query(array('key'=>'AIzaSyCU09e0zNuWJjk3a9cjV_U5PR9TDXbP1OI',
								'address'=>$value->loc));	

			$f = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?'.$get);

			$array_resp = json_decode($f);

			if(isset($array_resp->status)){
				if($array_resp->status == 'OK'){
					// print_r($array_resp); die;
					// if(count($array_resp->results) ==1){
						$array_resp->results = $array_resp->results[0];

						$new['loc'] = $array_resp->results->geometry->location;
						$this->db->where('loc', $value->loc);
						$this->db->update('user_location', array('lat'=>$new['loc']->lat,
							'lng'=>$new['loc']->lng));
						echo $value->loc.': '.$this->db->affected_rows().'<br>';
					// }

				}
				else{
					echo $array_resp->status.'<br>';
				}
			}

			$final_loc[] = $new;

		}
	}

	function json(){
		ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
		$this->db->select('lat, lng')->from('user_location')
				->where(array('status'=>1,'lat !='=>0,'lng !='=>0));
		$res = $this->db->get()->result();

		$render = [];

		foreach ($res as $value) {
			$render[] = (float)number_format ($value->lat,2);
			$render[] = (float)number_format ($value->lng,2);
			$render[] = 0.001;
		}

		$this->output
        ->set_status_header(200)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode(array(array('locationseriesSO', $render)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        ->_display();
exit;

		// echo json_encode(array(array('locationseriesSO', $render)));
	}
}
