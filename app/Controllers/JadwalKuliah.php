<?php

namespace App\Controllers;

use App\Models\ModelTa;
use App\Models\ModelProdi;
use App\Models\ModelJadwalKuliah;
use App\Models\ModelDosen;
use App\Models\ModelRuangan;


class JadwalKuliah extends BaseController
{
	public function __construct()
	{
		helper('form');
		$this->ModelTa = new ModelTa();
		$this->ModelProdi = new ModelProdi();
		$this->ModelJadwalKuliah = new ModelJadwalKuliah();
		$this->ModelDosen = new ModelDosen();
		$this->ModelRuangan = new ModelRuangan();
	}
	public function index()
	{
		$data = array(
			'title' =>    'Jadwal Kuliah',
			'ta_aktif' => $this->ModelTa->ta_aktif(),
			'prodi' => $this->ModelProdi->allData(),
			'isi'    =>    'admin/jadwalkuliah/v_index'
		);
		return view('layout/v_wrapper', $data);
	}
	public function detailjadwal($id_prodi)
	{
		$ta = $this->ModelTa->ta_aktif(); //sesuiakan dengan tahun akademik krs
		$data = [
			'title' 	=>  'Jadwal Kuliah',
			'ta_aktif' 	=> 	$this->ModelTa->ta_aktif(),
			'ta' 	=> 	$this->ModelTa->allData(),
			'prodi' 	=> 	$this->ModelProdi->detail_Data($id_prodi),
			'dosen' 	=> 	$this->ModelDosen->allData($id_prodi),
			'ruangan' 	=> 	$this->ModelRuangan->allData(),
			'jadwal' 	=> 	$this->ModelJadwalKuliah->allData($ta['id_ta'], $id_prodi),
			'kelas_perkuliahan' 	=> 	$this->ModelJadwalKuliah->allData_kelas_perkuliahan($ta['id_ta'], $id_prodi), // filter berdasarkan prodis
			'isi' 		=> 'admin/jadwalkuliah/v_detail'
		]; 
		return view('layout/v_wrapper', $data);
	}
	public function kelas($id_prodi)
	{
		return $this->db->table('tbl_kelas')
			->where('id_prodi', $id_prodi)
			->orderBy('nama_kelas.id_prodi ', 'ASC')
			->get()->getResultArray();
	}

	public function add($id_prodi)
	{
		if ($this->validate([
			'id_dosen' => [
				'label' => 'Dosen',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Dipilih !!!'
				]
			],
			'id_kelas_perkuliahan' => [
				'label' => 'Kelas Perkuliahan',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Dipilih !!!'
				]
			],
			'hari' => [
				'label' => 'Hari',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'waktu' => [
				'label' => 'Waktu',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'id_ruangan' => [
				'label' => 'Ruangan',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Dipilh !!!'
				]
			],
			'quota' => [
				'label' => 'Quota',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
		])) {
			//jika valid
			$ta = $this->ModelTa->ta_aktif();
			$data = [
				'id_prodi' => $id_prodi,
				'id_ta' => $ta['id_ta'],
				'id_kelas_perkuliahan' => $this->request->getPost('id_kelas_perkuliahan'),
				'id_dosen' => $this->request->getPost('id_dosen'),
				'hari' => $this->request->getPost('hari'),
				'waktu' => $this->request->getPost('waktu'),
				'id_ruangan' => $this->request->getPost('id_ruangan'),
				'quota' => $this->request->getPost('quota'),
			];
			$this->ModelJadwalKuliah->add($data);
			session()->setFlashdata('pesan', 'Data Berhasil Di Tambahkan !!!');
			return redirect()->to(base_url('jadwalkuliah/detailjadwal/' . $id_prodi));
		} else {
			//jika tidak valid
			session()->setFlashdata('errors', \Config\Services::validation()->getErrors());
			return redirect()->to(base_url('jadwalkuliah/detailjadwal/' . $id_prodi));
		}
	}
	public function delete($id_jadwal, $id_prodi)
	{
		$data = [
			'id_jadwal' => $id_jadwal,
		];
		$this->ModelJadwalKuliah->delete_data($data);
		session()->setFlashdata('pesan', ' Berhasil Di Hapus !!!');
		return redirect()->to(base_url('jadwalkuliah/detailjadwal/' . $id_prodi));
	}

	public function gagal_hapus_jadwal($id_jadwal, $id_prodi)
	{
		$data = [
			'id_jadwal' => $id_jadwal,
		];
		session()->setFlashdata('gagal', 'Mata kuliah telah di kontrak oleh mahasiswa!!!');
		return redirect()->to(base_url('jadwalkuliah/detailjadwal/' . $id_prodi));
		return view('layout/v_wrapper', $data);
	}



	//--------------------------------------------------------------------

}
