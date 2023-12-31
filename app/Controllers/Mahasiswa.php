<?php

namespace App\Controllers;

use App\Models\ModelMahasiswa;
use App\Models\ModelProdi;
use App\Models\ModelTa;
use App\Models\ModelAksesFitur;


class Mahasiswa extends BaseController
{
	public function __construct()
	{
		helper('form');
		$this->ModelMahasiswa = new ModelMahasiswa();
		$this->ModelProdi = new ModelProdi();
		$this->ModelTa = new ModelTa();
		$this->ModelAksesFitur = new ModelAksesFitur();
	}
	public function index()
	{
		$data = array(
			'title' =>    'Data Mahasiswa',
			'mhs' => $this->ModelMahasiswa->allData(),
			'isi'    =>    'admin/mahasiswa/v_index'
		);
		return view('layout/v_wrapper', $data);
	}
	public function add()
	{
		$data = array(
			'title' =>    'Add Mahasiswa',
			'prodi' => $this->ModelProdi->allData(),
			'isi'    =>    'admin/mahasiswa/v_add'
		);
		return view('layout/v_wrapper', $data);
	}

	public function akses_fitur_mhs()
	{
		$data = array(
			'title' =>    'Data Mahasiswa',
			'akses_fitur_mhs' => $this->ModelMahasiswa->akses_fitur_mhs(),
			'mhs' => $this->ModelMahasiswa->allData(),
			'data_mhs' => $this->ModelMahasiswa->data_mhs(),
			'isi'    =>    'admin/mahasiswa/v_index_fitur_mahasiswa'
		);
		return view('layout/v_wrapper', $data);
	}

	public function processForm()
	{
		// Memeriksa apakah data POST telah dikirimkan

		if ($this->request->getPost()) {
			$id_mhs = $this->request->getPost('id_mhs');
			print_r($id_mhs);


			// Memeriksa apakah data POST tidak kosong
			if (!empty($id_mhs)) {
				$namesString = implode(', ', $id_mhs);
				// Lakukan operasi lainnya, seperti menyimpan data ke database
				// ...

				return "Data POST telah benar.";
			} else {
				return "Data POST tidak lengkap.";
			}
		} else {
			return "Tidak ada data POST yang diterima";
		}
	}

	public function add_mhs_akses()
	{
		$mhs = $this->ModelMahasiswa->DataMhs();
		$data = [
			$id_mhs       = $mhs['id_mhs'],
		];

		$id_mhs = $this->request->getPost('id_mhs');

		if (!empty($id_mhs)) {
			$ModelMahasiswa = new ModelMahasiswa();

			foreach ($id_mhs as $id_mhs) {
				// Ambil data mahasiswa berdasarkan ID
				$ModelMahasiswa = new ModelMahasiswa();
				$mahasiswa = $ModelMahasiswa->find($id_mhs);

				// Simpan data mahasiswa ke tabel status
				$ta = $this->ModelTa->ta_aktif();
				$data = [
					'id_mhs'       => $mhs['id_mhs'],
					'id_ta' => $ta['id_ta'],
					// tambahkan kolom lain yang ingin Anda masukkan ke tabel status
				];

				$ModelMahasiswa->Tambah_mhs_status($data);
			}

			// Redirect ke halaman sebelumnya atau tampilkan pesan berhasil jika diperlukan
			return redirect()->back()->with('pesan', 'Data berhasil disimpan ke tabel status.');
		} else {
			// Redirect ke halaman sebelumnya atau tampilkan pesan error jika diperlukan
			return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
		}
	}

	public function edit($id_mhs)
	{
		$data = array(
			'title' =>    'Edit Mahasiswa',
			'ta_aktif' => $this->ModelTa->ta_aktif(),
			'mhs' => $this->ModelMahasiswa->detailData($id_mhs),
			'prodi' => $this->ModelProdi->allData(),
			'isi'    =>    'admin/mahasiswa/v_edit'
		);
		return view('layout/v_wrapper', $data);
	}



	public function update($id_mhs)
	{
		if ($this->validate([
			'nim' => [
				'label' => 'NIM',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'nama_mhs' => [
				'label' => 'Nama Mahasiswa',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'id_prodi' => [
				'label' => 'Program Studi',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'password' => [
				'label' => 'password',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'foto_mhs' => [
				'label' => 'Foto Mahasiswa',
				'rules' => 'max_size[foto_mhs,1024]|mime_in[foto_mhs,image/jpg,image/png, image/ico]', //documentasi Codeigniter Upload validasi "Rules for File Uploads"
				'errors' => [
					'max_size' => '{field} Max 1024 KB !!!',
					'mime_in' => 'Format {field} Wajib PNG, JPG, ICO !!!'
				]
			],

		])) {
			$foto = $this->request->getFile('foto_mhs'); //documentasi Codeigniter =>Working with Uploaded Files=>Simplest usage ""
			if ($foto->getError() == 4) {
				$data = array(
					'id_mhs' => $id_mhs,
					'nim' => $this->request->getPost('nim'),
					'nama_mhs' => $this->request->getPost('nama_mhs'),
					'id_prodi' => $this->request->getPost('id_prodi'),
					'password' => $this->request->getPost('password'),
				);
				$this->ModelMahasiswa->edit($data);
			} else {

				//menghapus foto lama
				$mhs = $this->ModelMahasiswa->detailData($id_mhs);
				if ($mhs['foto_mhs'] != "") {
					unlink('fotomahasiswa/' . $mhs['foto_mhs']);
				}
				//mengambil nama foto

				//merubah nama foto
				$nama_file = $foto->getRandomName(); //documentasi Codeigniter =>Working with Uploaded Files=>Moving Files"
				//jika valid
				$data = array(
					'id_mhs' => $id_mhs,
					'nim' => $this->request->getPost('nim'),
					'nama_mhs' => $this->request->getPost('nama_mhs'),
					'id_prodi' => $this->request->getPost('id_prodi'),
					'password' => $this->request->getPost('password'),
					'foto_mhs' => $nama_file,
				);
				//memindahkan file foto dari form input ke  direktori
				$foto->move('fotomahasiswa', $nama_file); //documentasi Codeigniter =>Working with Uploaded Files=>Moving Files
				$this->ModelMahasiswa->edit($data);
			}


			session()->setFlashdata('pesan', 'Data Berhasil Di Tambahkan !!!');
			return redirect()->to('/mahasiswa');
		} else {
			//jika tidak valid
			session()->setFlashdata('errors', \Config\Services::validation()->getErrors());
			return redirect()->to(base_url('mahasiswa/edit/' . $id_mhs));
		}
	}

	public function delete($id_mhs)
	{
		//menghapus foto lama
		$mhs = $this->ModelMahasiswa->detailData($id_mhs);
		if ($mhs['foto_mhs'] != "") {
			unlink('fotomahasiswa/' . $mhs['foto_mhs']);
		}
		$data = [
			'id_mhs' => $id_mhs,
		];
		$this->ModelMahasiswa->delete_data($data);
		session()->setFlashdata('pesan', 'Data Berhasil Di Hapus !!!');
		return redirect()->to('/mahasiswa');
	}
	public function insert()
	{

		if ($this->validate([
			'nim' => [
				'label' => 'NIM',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'nama_mhs' => [
				'label' => 'Nama Mahasiswa',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'id_prodi' => [
				'label' => 'Program Studi',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'password' => [
				'label' => 'password',
				'rules' => 'required',
				'errors' => [
					'required' => '{field} Wajib Diisi !!!'
				]
			],
			'foto_mhs' => [
				'label' => 'Foto Mahasiswa',
				'rules' => 'uploaded[foto_mhs]|max_size[foto_mhs,1024]|mime_in[foto_mhs,image/jpg,image/png, image/ico]', //documentasi Codeigniter Upload validasi "Rules for File Uploads"
				'errors' => [
					'uploaded' => '{field} Wajib Diisi !!!',
					'max_size' => '{field} Max 1024 KB !!!',
					'mime_in' => 'Format {field} Wajib PNG, JPG, ICO !!!'
				]
			],

		])) {

			//mengambil nama foto
			$foto = $this->request->getFile('foto_mhs'); //documentasi Codeigniter =>Working with Uploaded Files=>Simplest usage ""
			//merubah nama foto
			$nama_file = $foto->getRandomName(); //documentasi Codeigniter =>Working with Uploaded Files=>Moving Files"
			//jika valid
			$data = array(
				'nim' => $this->request->getPost('nim'),
				'nama_mhs' => $this->request->getPost('nama_mhs'),
				'id_prodi' => $this->request->getPost('id_prodi'),
				'password' => $this->request->getPost('password'),
				'foto_mhs' => $nama_file,

			);

			//memindahkan file foto dari form input ke  direktori
			$foto->move('fotomahasiswa', $nama_file); //documentasi Codeigniter =>Working with Uploaded Files=>Moving Files
			$this->ModelMahasiswa->add($data);
			session()->setFlashdata('pesan', 'Data Berhasil Di Tambahkan !!!');
			return redirect()->to('/mahasiswa');
		} else {
			//jika tidak valid
			session()->setFlashdata('errors', \Config\Services::validation()->getErrors());
			return redirect()->to(base_url('mahasiswa/add'));
		}
	}
}


	//-------------------------------------------------------------------
