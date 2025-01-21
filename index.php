<?php include "header.php"; ?>

<!-- start banner Area -->
<section class="banner-area relative">
  <div class="overlay overlay-bg"></div>
  <div class="container">
    <div class="row fullscreen align-items-center justify-content-between">
      <div class="col-lg-6 col-md-6 banner-left">
        <h2 class="text-white">SISTEM INFORMASI GEOGRAFIS PEMETAAN PENYEBARAN KASUS HIV/AIDS DI KOTA KUPANG</h2>
        <p class="text-white">
          Sistem informasi ini merupakan aplikasi pemetaan geografis kasus HIV/AIDS di Kota Kupang. Aplikasi ini memuat informasi dan titik lokasi per Kecamatan dari kasus HIV/AIDS di Kota Kupang.
        </p>
        <a href="#peta_wisata" class="primary-btn text-uppercase">Lihat Peta</a>
      </div>
    </div>
  </div>
</section>
<!-- End banner Area -->

<main id="main">
  <!-- Start about-info Area -->
  <section class="price-area section-gap">
    <section id="peta_wisata" class="about-info-area section-gap">
      <div class="container">
        <div class="title text-center">
          <h1 class="mb-10">Peta Persebaran HIV/AIDS Per Kecamatan di Kota Kupang</h1>
          <br>
        </div>
        <div class="row align-items-center">
          <div id="map" style="width:100%;height:480px;"></div>
          <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDFWho0fbNDWi9KTMuP4wJyBQY1lualbjM"></script>
          
              <style>  
                        .info-content p span { 
                            display: inline-block; 
                            width: 150px; /* Sesuaikan dengan panjang teks terpanjang */ 
                            color: black;
                        } 
                        .info-content p {
                             display: flex;
                        }

                        .info-content p span:first-child {
                            min-width: 150px; /* Menyesuaikan jarak tetap */
                            display: inline-block;
                            color: black;
                        }
                        .map-control {
                            background-color: rgba(255, 255, 255, 0.9); /* Warna latar belakang dengan transparansi */
                            border: 1px solid #ccc;
                            padding: 10px;
                            border-radius: 5px;
                            margin: 10px;
                            display: flex;
                            flex-direction: column; /* Mengatur kontrol agar ditampilkan secara vertikal */
                        }

                    .map-control div {
                        margin: 5px 0; /* Jarak antar tombol */
                        color: black;
                    }

                    .map-control button {
                        margin: 5px 0;
                    }

                    .map-control button:hover {
                        background-color: #e0e0e0; /* Warna saat hover */
                    }

                    ul {
                        list-style-type: disc; /* Menampilkan titik hitam */
                        padding-left: 20px; /* Memberikan jarak dari tepi */
                    }

                    li {
                        margin: 10px 0; /* Memberikan jarak antar item */
                    }

                </style>
          
          <script>
            var map;
            var allMarkers = [];
            var puskesmasMarkers = [];
            var kecamatanInfowindow = new google.maps.InfoWindow();
            var puskesmasInfowindow = new google.maps.InfoWindow();
            
            function initMap() {
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 12,
                    center: { lat: -10.1747717, lng: 123.5323147 } // Pusat Kota Kupang
                });

                // Menambahkan kontrol kustom ke dalam peta
                var controlDiv = document.createElement('div');
                var controlUI = document.createElement('div');
                controlUI.className = 'map-control';
                controlUI.innerHTML = '<h5>Ubah Jenis Peta</h5>';
                controlDiv.appendChild(controlUI);

                // Fungsi untuk menambahkan tombol ke kontrol
                function createButton(label, type) {
                    var button = document.createElement('button');
                    button.innerHTML = label;
                    button.onclick = function() {
                        map.setMapTypeId(type);
                    };
                    controlUI.appendChild(button);
                }

                // Menambahkan tombol ke kontrol
                createButton('Peta Biasa', 'roadmap');
                createButton('Peta Satelit', 'satellite');

                // Menempatkan kontrol di dalam peta
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(controlDiv);

                // Ambil data kecamatan
                fetch('ambil_kecamatan.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log("Data Kecamatan:", data); // Log data kecamatan
                        if (data.results && data.results.length > 0) {
                            data.results.forEach(function (kecamatan) {
                                var marker = new google.maps.Marker({ position: { lat: parseFloat(kecamatan.latitude), lng: parseFloat(kecamatan.longitude) },
                                    map: map,
                                    title: kecamatan.nama_kecamatan // Akses atribut nama_kecamatan dengan benar
                                });

                                allMarkers.push(marker);

                                marker.addListener('click', function () {
                                    // Tampilkan informasi kecamatan
                                    var contentString = `
                                        <div class="info-content"> 
                                            <h5>Kecamatan ${kecamatan.nama_kecamatan}</h5> 
                                            <br>
                                            <p><span>Jumlah Penduduk</span> <span>: ${kecamatan.jumlah_penduduk}</span></p> 
                                            <p><span>Kasus HIV</span> <span>: ${kecamatan.kasus_hiv}</span></p> 
                                            <p><span>Pengobatan ARV</span> <span>: ${kecamatan.pengobatan_arv}</span></p> 
                                            <p><span>Puskesmas</span> <span>: ${kecamatan.puskesmas}</span></p> 
                                            <p><span>Jumlah Kelurahan</span> <span>: ${kecamatan.jumlah_kelurahan}</span></p> 
                                            <button onclick="showPuskesmas(${kecamatan.id_kecamatan})">Lihat Puskesmas</button>
                                        </div>
                                    `;
                                    kecamatanInfowindow.setContent(contentString);
                                    kecamatanInfowindow.open(map, marker);
                                });
                            });
                        } else {
                            console.error("Tidak ada kecamatan ditemukan.");
                        }
                    })
                    .catch(error => {
                        console.error("Gagal mengambil data kecamatan: " + error);
                    });
            }

            function showPuskesmas(id_kecamatan) {
            // Sembunyikan semua marker kecamatan
            allMarkers.forEach(function (marker) {
                marker.setMap(null);
            });

            // Ambil data puskesmas terkait
            fetch('ambil_puskesmas.php?id_kecamatan=' + id_kecamatan)
                .then(response => response.json())
                .then(data => {
                    console.log("Data Puskesmas:", data); // Log data puskesmas
                    if (data.results && data.results.length > 0) {
                        data.results.forEach(function (puskesmas) {
                            console.log("Latitude:", puskesmas.latitude, "Longitude:", puskesmas.longitude); // Log koordinat

                            var puskesmasMarker = new google.maps.Marker({
                                position: { lat: parseFloat(puskesmas.latitude), lng: parseFloat(puskesmas.longitude) },
                                map: map,
                                title: puskesmas.nama_puskesmas,
                                icon: {
                                    url: 'img/puss.png', // Ganti dengan path ke gambar Anda
                                    scaledSize: new google.maps.Size(32, 32), // Ukuran gambar
                                    origin: new google.maps.Point(0, 0), // Titik awal gambar
                                    anchor: new google.maps.Point(16, 16) // Titik anchor gambar
                                }
                            });

                            console.log("Puskesmas Marker:", puskesmasMarker); // Log marker

                            puskesmasMarkers.push(puskesmasMarker);

                            puskesmasMarker.addListener('click', function () {
                                // Tutup info window sebelumnya
                                puskesmasInfowindow.close();

                                var contentString = `
                                <div class="info-content"> 
                                    <h5>${puskesmas.nama_puskesmas}</h5>
                                    <br>
                                    <p><span>Kasus HIV</span> <span>: ${puskesmas.kasus_hiv}</span></p>
                                    <p><span>Pasien</span> <span>: ${puskesmas.pasien}</span></p>
                                    <p><span>Alamat</span> <span>: ${puskesmas.alamat}</span></p>
                                    <button onclick="showKecamatan()">Lihat Kecamatan</button>
                                </div>
                                `;

                                puskesmasInfowindow.setContent(contentString);
                                puskesmasInfowindow.open(map, puskesmasMarker);
                            });
                        });
                    } else {
                        console.error("Tidak ada puskesmas ditemukan.");
                    }
                })
                .catch(error => {
                    console.error("Gagal mengambil data puskesmas: " + error);
                });
        }



            function showKecamatan() {
                // Sembunyikan semua marker puskesmas
                puskesmasMarkers.forEach(function (marker) {
                    marker.setMap(null);
                });

                // Tampilkan kembali semua marker kecamatan
                allMarkers.forEach(function (marker) {
                    marker.setMap(map);
                });

                // Tutup info window
                puskesmasInfowindow.close();
            }

            function lihatPolygon(id_kecamatan) {
                            // Ganti URL di bawah ini dengan link Google Drive yang sesuai
                            var linkGoogleDrive = `https://www.google.com/maps/d/embed?mid=197u1d56cfHXAQ6b5lFouOy73oHB3lj0&hl=id&ehbc=2E312F" width="460" height="380"${id_kecamatan}`;
                            window.open(linkGoogleDrive, '_blank');
                        }

            window.onload = initMap;
          </script>
        </div>
      </div>

      <!-- ======= Counts Section ======= -->
    <section id="counts" >
      <div class="container">

        <div class="row counters">

          <div class="col-lg-3 col-6 text-center" style="opacity: 0;">
            <span data-toggle="counter-up">232</span>
            <p>Clients</p>
          </div>

          <?php
          include_once "countsma.php";
          $obj = json_decode($data);
          $sman = "";
          foreach ($obj->results as $item) {
            $sman .= $item->sma;
          }
          ?>
          <div class="col-lg-3 col-6 text-center">
            <h1 data-toggle="counter-up"><?php echo $sman; ?></h1>
            <br>
            <h1>Kecamatan</h1>
          </div>
          <?php
          include_once "countsmk.php";
          $obj2 = json_decode($data);
          $smkn = "";
          foreach ($obj2->results as $item2) {
            $smkn .= $item2->smk;
          }
          ?>
          <div class="col-lg-3 col-6 text-center">
            <h1 data-toggle="counter-up"><?php echo $smkn; ?></h1>
            <br>
            <h1>Puskesmas</h1>
          </div>

          <div class="col-lg-3 col-6 text-center" style="opacity: 0;">
            <span data-toggle="counter-up">15</span>
            <p>Hard Workers</p>
          </div>

        </div>

      </div>
    </section><!-- End Counts Section -->
    </section>

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
  <div class="container">
    <div class="row content">
      <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
        <h1><em>Human Immunodeficiency Virus</em> (HIV)</h1>
        <h3 style="text-align: justify;">Adalah virus yang merusak sistem kekebalan tubuh dengan menginfeksi dan menghancurkan sel CD4. Jika makin banyak sel CD4 yang hancur, daya tahan tubuh akan makin melemah sehingga rentan diserang berbagai penyakit.</h3>
        <br><br><br>
        <a href="index.php"><img src="admin/img/hiv.png"/></a>
      </div>
      <div class="col-lg-6 pt-4 pt-lg-0" data-aos="fade-left" data-aos-delay="200">
        <h4>Pengertian AIDS</h4>
        <p style="text-align: justify;">
          HIV yang tidak segera ditangani akan berkembang menjadi kondisi serius yang disebut AIDS <em>(acquired immunodeficiency syndrome).</em> AIDS adalah stadium akhir dari infeksi HIV. Pada tahap ini, kemampuan tubuh untuk melawan infeksi sudah hilang sepenuhnya.
        </p>
        <p style="text-align: justify;">
            Penularan HIV terjadi melalui kontak dengan cairan tubuh penderita, seperti darah, sperma, cairan vagina, cairan anus, serta ASI. Perlu diketahui, HIV tidak menular melalui udara, air, keringat, air mata, air liur, gigitan nyamuk, atau sentuhan fisik.
        </p>
        <p style="text-align: justify;">
            HIV adalah penyakit seumur hidup. Dengan kata lain, virus HIV akan menetap di dalam tubuh penderita seumur hidupnya. Meski belum ada metode pengobatan untuk mengatasi HIV, tetapi ada obat yang bisa memperlambat perkembangan penyakit ini dan dapat meningkatkan harapan hidup penderita. (Sumber : Halodoc.com)
        </p>        
        <br>
        <h4>Gejala HIV dan AIDS</h4>
        <p style="text-align: justify;">
            Kebanyakan penderita mengalami flu ringan pada 2 - 6 minggu setelah terinfeksi HIV. Flu bisa disertai dengan gejala lain dan dapat bertahan selama 1 - 2 minggu. Setelah flu membaik, gejala lain mungkin tidak akan terlihat selama bertahun-tahun meski virus HIV terus merusak kekebalan tubuh penderitanya, sampai HIV berkembang ke stadium lanjut menjadi AIDS.
            <br>
            Pada kebanyakan kasus, seseorang baru mengetahui bahwa dirinya terserang HIV setelah memeriksakan diri ke dokter akibat terkena penyakit parah yang disebabkan oleh melemahnya daya tahan tubuh. Penyakit parah yang dimaksud antara lain diare kronis, pneumonia, penurunan berat badan secara drastis <em>(cachexia),</em> atau toksoplasmosis otak. (Sumber : Halodoc.com)
        </p>
        <br>
        <h4>Penyebab dan Faktor Risiko HIV dan AIDS</h4>
        <p style="text-align: justify;">
            Penyakit HIV disebabkan oleh human immunodeficiency virus atau HIV, sesuai dengan nama penyakitnya. Bila tidak diobati, HIV dapat makin memburuk dan berkembang menjadi AIDS.

            Penularan HIV dapat terjadi melalui hubungan seks vaginal atau anal, penggunaan jarum suntik, dan transfusi darah. Meskipun jarang, HIV juga dapat menular dari ibu ke anak selama masa kehamilan, melahirkan, dan menyusui. (Sumber : Halodoc.com)
        </p>
        <br>
        <h4>Pengobatan HIV dan AIDS</h4>
        <p style="text-align: justify;">
            Penderita yang telah terdiagnosis HIV harus segera mendapatkan pengobatan berupa terapi antiretroviral (ARV). ARV bekerja mencegah virus HIV bertambah banyak sehingga tidak menyerang sistem kekebalan tubuh. (Sumber : Halodoc.com)
        </p>
        <br>
        <h4>Pencegahan HIV dan AIDS</h4>
        <p style="text-align: justify;">Berikut adalah beberapa cara yang dapat dilakukan untuk menghindari dan meminimalkan penularan HIV:</p>
        <ul style="text-align: justify; list-style-type: disc; padding-left: 20px;">
            <li>Tidak melakukan hubungan seks sebelum menikah</li>
            <li>Tidak berganti - ganti pasangan seksual</li>
            <li>Menggunakan kondom saat berhubungan seksual</li>
            <li>Menghindari penggunaan narkoba, terutama jenis suntik</li>
            <li>Mendapatkan informasi yang benar terkait HIV, cara penularan, pencegahan dan pengobatannya terutama bagi anak remaja</li>
        </ul>
        <p>Ingin mengetahui lebih banyak mengenai HIV/AIDS atau ingin konsultasi dengan dokter, dapat mengunjungi website <a href="https://www.halodoc.com/kesehatan/hiv-dan-aids?srsltid=AfmBOooQRoSnCfVcB5G_Sfs4p9xTAc9EMy5SXvLBuNxQnqQ4Q5OFNdoK">halodoc.com</a></p>
      </div>
    </div>
  </div>
    </section><!-- End About Section -->

  
</main>
<!-- End testimonial Area -->

<?php include "footer.php"; ?>

    



                                            <!-- menambahkan opsi pilih tahun pada keterangan kecamatan
                
                                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                                <p><span>Pilih Tahun</span>
                                                <span>
                                                    <select id="tahun" onchange="updateKecamatanData(${kecamatan.id_kecamatan}, this.value)">
                                                        <option value="2021">2021</option>
                                                        <option value="2022">2022</option>
                                                        <option value="2023">2023</option>
                                                    </select>
                                                </span>
                                                </p>
                                            </div>

                                             -->
