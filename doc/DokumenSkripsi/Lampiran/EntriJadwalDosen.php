<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class EntriJadwalDosen extends CI_Controller {

    public function __construct() {
        parent::__construct();
        try {
            $this->Auth_model->checkModuleAllowed(get_class());
        } catch (Exception $ex) {
            $this->session->set_flashdata('error', $ex->getMessage());
            header('Location: /');
        }
        $this->load->library('bluetape');
        $this->load->model('JadwalDosen_model');
        $this->load->model('Auth_model');
        $this->load->database();
    }

    public function index() {
        // Retrieve logged in user data
        $userInfo = $this->Auth_model->getUserInfo();
        $dataJadwal = $this->JadwalDosen_model->getJadwalByUsername($userInfo['email']);
        $namaHari = $this->JadwalDosen_model->getNamaHari();
		$namaBulan = $this->JadwalDosen_model->getNamaBulan();
        $this->load->view('EntriJadwalDosen/main', array(
            'currentModule' => get_class(),
            'request_add_jadwal' => $this->session->userdata('request_add_jadwal'),
            'dataJadwal' => $dataJadwal,
            'namaHari' => $namaHari,
			'namaBulan'=> $namaBulan
        ));
    }

    public function add() {
        $userInfo = $this->Auth_model->getUserInfo();
		
		$jam_mulai = $this->input->post('jam_mulai');
		$durasi = $this->input->post('durasi');
		$jam_akhir = $jam_mulai + $durasi ;
		$hari = $this->input->post('hari');
		$bisaMasuk = TRUE;
		for($i=$jam_mulai ; $i<$jam_akhir ; $i++){
			//memeriksa apakah ada jadwal lain di antara jam mulai dan jam akhir pada jadwal yang dimasukan oleh user
			if($this->JadwalDosen_model->cekJadwalByJamMulai($i,$hari)){  // jika TRUE berarti ada jadwal lain dan user tidak dapat memasukan jadwal baru
				$bisaMasuk = FALSE;
				break;
			}
		}
		if($bisaMasuk){
			$data = array(
            'user' => $userInfo['email'],
            'hari' => $this->input->post('hari'),
            'jam_mulai' => $this->input->post('jam_mulai'),
            'durasi' => $this->input->post('durasi'),
            'jenis_jadwal' => $this->input->post('jenis_jadwal'),
            'label_jadwal' => $this->input->post('label_jadwal')
			);
			$this->JadwalDosen_model->addJadwal($data);
		}
		else{
			echo 'konfirmasi2();';
;			sleep(3);
		?>
			<script>
				function konfirmasi2()
				{
					yakin = confirm("Anda yakin mau menghapus semua data jadwal? Aksi ini tidak dapat dibatalkan");
					if(yakin!=true)
					{
						return false;
					}
				}
			</script>
			<?php
		}
        header('Location: /EntriJadwalDosen');
    }

    public function update($id_jadwal) {
        $userInfo = $this->Auth_model->getUserInfo();
        $data = array(
            'hari' => $this->input->post('hari'),
            'jam_mulai' => $this->input->post('jam_mulai'),
            'durasi' => $this->input->post('durasi'),
            'jenis' => $this->input->post('jenis_jadwal'),
            'label' => $this->input->post('label_jadwal'),
			'lastUpdate' => date('Y-m-d H:i:s')
        );
        $this->JadwalDosen_model->updateJadwal($id_jadwal, $data);
        header('Location: /EntriJadwalDosen');
    }
	
	public function delete($id_jadwal) {
        $this->JadwalDosen_model->deleteJadwal($id_jadwal);
        header('Location: /EntriJadwalDosen');
    }
	
	public function deleteAll(){
		$userInfo = $this->Auth_model->getUserInfo();
		$this->JadwalDosen_model->deleteByUsername($userInfo['email']);
        header('Location: /EntriJadwalDosen');
	}

    public function getDataJadwal($id_jadwal) {
        echo $this->JadwalDosen_model->getJadwalByIdJadwal($id_jadwal);
    }
}
